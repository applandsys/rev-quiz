<?php
/**
 * Traffic-source filter ("cloaking").
 *
 * No source selected: every visitor sees the quiz.
 * Sources selected: only visitors referred from one of them see the quiz;
 * everyone else gets the fallback (text-only page or a redirect).
 *
 * A qualifying visitor is remembered in a signed cookie, because later quiz
 * steps are reached from this site and no longer carry the social referrer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RQ_Cloak {

	const COOKIE = 'rq_src';

	private static $decision = null;
	private static $matched  = '';

	/**
	 * Selected sources merged with the custom domain list.
	 *
	 * @return array key => definition
	 */
	public static function active_sources() {
		$catalogue = RQ_Settings::sources();
		$active    = array();
		foreach ( (array) RQ_Settings::get( 'traffic_sources' ) as $key ) {
			if ( isset( $catalogue[ $key ] ) ) {
				$active[ $key ] = $catalogue[ $key ];
			}
		}

		$custom = preg_split( '/[\s,]+/', (string) RQ_Settings::get( 'custom_sources' ), -1, PREG_SPLIT_NO_EMPTY );
		foreach ( $custom as $domain ) {
			$domain = strtolower( preg_replace( '#^https?://#i', '', trim( $domain ) ) );
			$domain = preg_replace( '#[/?].*$#', '', $domain );
			$domain = preg_replace( '/^www\./', '', $domain );
			if ( '' === $domain ) {
				continue;
			}
			$label                       = preg_replace( '/\.[a-z*]+$/', '', $domain );
			$active[ 'custom:' . $domain ] = array(
				'label'   => $domain,
				'domains' => array( $domain ),
				'utm'     => array( $domain, $label ),
				'ua'      => array(),
			);
		}
		return $active;
	}

	public static function is_filter_active() {
		return ! empty( self::active_sources() );
	}

	/**
	 * Whether the current visitor may see the quiz. Memoized per request.
	 */
	public static function visitor_allowed() {
		if ( null !== self::$decision ) {
			return self::$decision;
		}

		$sources = self::active_sources();
		if ( empty( $sources ) ) {
			return self::$decision = true;
		}

		if ( RQ_Settings::get( 'admin_bypass' ) && current_user_can( 'edit_posts' ) ) {
			self::$matched = 'admin';
			return self::$decision = true;
		}

		$remembered = self::read_cookie();
		if ( $remembered && isset( $sources[ $remembered ] ) ) {
			self::$matched = $remembered;
			return self::$decision = true;
		}

		$detected = self::detect( $sources );
		if ( $detected ) {
			self::$matched = $detected;
			self::write_cookie( $detected );
			return self::$decision = true;
		}

		return self::$decision = false;
	}

	public static function matched_source() {
		self::visitor_allowed();
		return self::$matched;
	}

	/**
	 * @return string|false Matching source key.
	 */
	public static function detect( $sources ) {
		$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? wp_unslash( $_SERVER['HTTP_REFERER'] ) : '';
		$agent    = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';
		$host     = $referrer ? strtolower( (string) wp_parse_url( $referrer, PHP_URL_HOST ) ) : '';

		$params = array();
		foreach ( array( 'utm_source', 'ref', 'source', 'src' ) as $param ) {
			if ( isset( $_GET[ $param ] ) && is_string( $_GET[ $param ] ) ) {
				$params[] = strtolower( sanitize_text_field( wp_unslash( $_GET[ $param ] ) ) );
			}
		}
		// Click IDs added by the ad platforms themselves.
		$click_ids = array(
			'fbclid'    => array( 'facebook', 'instagram', 'messenger', 'threads' ),
			'ttclid'    => array( 'tiktok' ),
			'twclid'    => array( 'twitter' ),
			'ScCid'     => array( 'snapchat' ),
			'epik'      => array( 'pinterest' ),
			'li_fat_id' => array( 'linkedin' ),
			'rdt_cid'   => array( 'reddit' ),
		);

		foreach ( $sources as $key => $source ) {
			if ( $host && self::host_matches( $host, $source['domains'] ) ) {
				return $key;
			}
			foreach ( $params as $value ) {
				if ( in_array( $value, $source['utm'], true ) ) {
					return $key;
				}
			}
			foreach ( $source['ua'] as $token ) {
				if ( '' !== $token && false !== stripos( $agent, $token ) ) {
					return $key;
				}
			}
		}

		foreach ( $click_ids as $param => $keys ) {
			if ( empty( $_GET[ $param ] ) ) {
				continue;
			}
			foreach ( $keys as $key ) {
				if ( isset( $sources[ $key ] ) ) {
					return $key;
				}
			}
		}

		return false;
	}

	public static function host_matches( $host, $domains ) {
		$host = preg_replace( '/^www\./', '', $host );
		foreach ( $domains as $domain ) {
			$domain = strtolower( $domain );
			if ( '.*' === substr( $domain, -2 ) ) {
				// "pinterest.*" matches pinterest.com, pinterest.co.uk, in.pinterest.com ...
				$label = preg_quote( substr( $domain, 0, -2 ), '/' );
				if ( preg_match( '/(^|\.)' . $label . '\.[a-z.]+$/', $host ) ) {
					return true;
				}
			} elseif ( $host === $domain || substr( $host, -strlen( '.' . $domain ) ) === '.' . $domain ) {
				return true;
			}
		}
		return false;
	}

	private static function read_cookie() {
		if ( empty( $_COOKIE[ self::COOKIE ] ) ) {
			return '';
		}
		$parts = explode( '|', sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) );
		if ( 3 !== count( $parts ) ) {
			return '';
		}
		list( $key, $expires, $sig ) = $parts;
		if ( (int) $expires < time() || ! hash_equals( self::sign( $key . '|' . $expires ), $sig ) ) {
			return '';
		}
		return $key;
	}

	private static function write_cookie( $key ) {
		if ( headers_sent() ) {
			return;
		}
		$hours   = max( 1, (int) RQ_Settings::get( 'remember_hours' ) );
		$expires = time() + $hours * HOUR_IN_SECONDS;
		$value   = $key . '|' . $expires . '|' . self::sign( $key . '|' . $expires );
		setcookie( self::COOKIE, $value, $expires, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
		$_COOKIE[ self::COOKIE ] = $value;
	}

	private static function sign( $data ) {
		return substr( hash_hmac( 'sha256', $data, wp_salt( 'auth' ) ), 0, 32 );
	}
}
