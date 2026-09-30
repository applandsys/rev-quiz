<?php
/**
 * Plugin settings: defaults, access and the traffic-source catalogue.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RQ_Settings {

	const OPTION = 'rev_quiz_settings';

	private static $cache = null;

	/**
	 * Defaults mirror the values of the original assets/ads-config.js.
	 */
	public static function defaults() {
		return array(
			// Traffic filter (cloaking).
			'traffic_sources'        => array(),
			'custom_sources'         => '',
			'fallback_mode'          => 'content', // content | redirect.
			'fallback_url'           => '',
			'remember_hours'         => 24,
			'admin_bypass'           => 1,
			'ads_for_blocked'        => 0,

			// Permalinks.
			'base_slug'              => 'quiz',

			// Content.
			'default_instructions'   => '<p>Answer each question by tapping the option that fits you best. Every step saves your choice and takes you to the next question automatically. There are no right or wrong answers, and you can go back at any time.</p>',
			'rewarded_box'           => 1,
			'rewarded_text'          => 'Redeem rewards to unlock the full guide',
			'rewarded_button'        => "Let's Play",
			'banner_after'           => 4,

			// AdSense ("add code" from assets/ads-config.js).
			'ads_enabled'            => 1,
			'publisher_id'           => 'ca-pub-8026299207830445',
			'slot_banner'            => '7009281033',
			'slot_popup'             => '7009281033',
			'fmt_banner'             => 1,
			'fmt_popup'              => 1,
			'fmt_rewarded'           => 1,
			'fmt_interstitial'       => 0,
			'popup_delay'            => 1,
			'popup_hide_unfilled'    => 1,
			'popup_timeout'          => 8000,
			'rewarded_placement'     => 'game_started',
			'rewarded_once'          => 1,
			'rewarded_session_key'   => 'adShown',
			'interstitial_placement' => 'between_sections',
			'interstitial_type'      => 'next',
			'anchor_position'        => 'account',

			// Push notifications and translation.
			'onesignal_app_id'       => '',
			'translate_enabled'      => 1,
			'translate_page_lang'    => 'en',
			'translate_languages'    => 'hi,es,fr,pt,ru,tr,mr,te,vi',

			// Custom code.
			'header_code'            => '',
			'footer_code'            => '',
			'code_sitewide'          => 0,
		);
	}

	public static function all() {
		if ( null === self::$cache ) {
			$saved       = get_option( self::OPTION, array() );
			self::$cache = wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
		}
		return self::$cache;
	}

	public static function get( $key ) {
		$all = self::all();
		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	public static function flush_cache() {
		self::$cache = null;
	}

	public static function base_slug() {
		$slug = sanitize_title( self::get( 'base_slug' ) );
		return $slug ? $slug : 'quiz';
	}

	/**
	 * Social networks a visitor can be referred from.
	 *
	 * domains: referrer hosts (a trailing ".*" matches any TLD).
	 * utm:     utm_source / ref / source query values.
	 * ua:      in-app browser user agent tokens.
	 */
	public static function sources() {
		return array(
			'facebook'  => array(
				'label'   => 'Facebook',
				'domains' => array( 'facebook.com', 'fb.com', 'fb.me', 'fb.watch' ),
				'utm'     => array( 'facebook', 'fb', 'facebook.com', 'meta' ),
				'ua'      => array( 'FBAN', 'FBAV', 'FB_IAB', 'FBIOS' ),
			),
			'instagram' => array(
				'label'   => 'Instagram',
				'domains' => array( 'instagram.com' ),
				'utm'     => array( 'instagram', 'ig', 'instagram.com' ),
				'ua'      => array( 'Instagram' ),
			),
			'messenger' => array(
				'label'   => 'Messenger',
				'domains' => array( 'messenger.com', 'm.me' ),
				'utm'     => array( 'messenger' ),
				'ua'      => array( 'MessengerForiOS', 'FB_IAB/MESSENGER', 'Orca-Android' ),
			),
			'threads'   => array(
				'label'   => 'Threads',
				'domains' => array( 'threads.net', 'threads.com' ),
				'utm'     => array( 'threads' ),
				'ua'      => array( 'Barcelona' ),
			),
			'twitter'   => array(
				'label'   => 'X (Twitter)',
				'domains' => array( 'twitter.com', 't.co', 'x.com' ),
				'utm'     => array( 'twitter', 'x', 'twitter.com', 'x.com' ),
				'ua'      => array( 'Twitter' ),
			),
			'tiktok'    => array(
				'label'   => 'TikTok',
				'domains' => array( 'tiktok.com' ),
				'utm'     => array( 'tiktok', 'tt', 'tiktok.com' ),
				'ua'      => array( 'musical_ly', 'BytedanceWebview', 'TikTok', 'trill_' ),
			),
			'youtube'   => array(
				'label'   => 'YouTube',
				'domains' => array( 'youtube.com', 'youtu.be' ),
				'utm'     => array( 'youtube', 'yt', 'youtube.com' ),
				'ua'      => array(),
			),
			'pinterest' => array(
				'label'   => 'Pinterest',
				'domains' => array( 'pinterest.*', 'pin.it' ),
				'utm'     => array( 'pinterest', 'pin', 'pinterest.com' ),
				'ua'      => array( 'Pinterest' ),
			),
			'snapchat'  => array(
				'label'   => 'Snapchat',
				'domains' => array( 'snapchat.com' ),
				'utm'     => array( 'snapchat', 'snap' ),
				'ua'      => array( 'Snapchat' ),
			),
			'linkedin'  => array(
				'label'   => 'LinkedIn',
				'domains' => array( 'linkedin.com', 'lnkd.in' ),
				'utm'     => array( 'linkedin', 'linkedin.com' ),
				'ua'      => array( 'LinkedInApp' ),
			),
			'reddit'    => array(
				'label'   => 'Reddit',
				'domains' => array( 'reddit.com', 'redd.it' ),
				'utm'     => array( 'reddit', 'reddit.com' ),
				'ua'      => array( 'Reddit' ),
			),
			'whatsapp'  => array(
				'label'   => 'WhatsApp',
				'domains' => array( 'whatsapp.com', 'wa.me' ),
				'utm'     => array( 'whatsapp', 'wa' ),
				'ua'      => array( 'WhatsApp' ),
			),
			'telegram'  => array(
				'label'   => 'Telegram',
				'domains' => array( 't.me', 'telegram.me', 'telegram.org' ),
				'utm'     => array( 'telegram', 'tg' ),
				'ua'      => array( 'Telegram' ),
			),
			'quora'     => array(
				'label'   => 'Quora',
				'domains' => array( 'quora.com' ),
				'utm'     => array( 'quora' ),
				'ua'      => array(),
			),
			'tumblr'    => array(
				'label'   => 'Tumblr',
				'domains' => array( 'tumblr.com' ),
				'utm'     => array( 'tumblr' ),
				'ua'      => array(),
			),
			'vk'        => array(
				'label'   => 'VK',
				'domains' => array( 'vk.com', 'vk.ru' ),
				'utm'     => array( 'vk' ),
				'ua'      => array(),
			),
			'discord'   => array(
				'label'   => 'Discord',
				'domains' => array( 'discord.com', 'discord.gg', 'discordapp.com' ),
				'utm'     => array( 'discord' ),
				'ua'      => array( 'Discord' ),
			),
		);
	}

	/**
	 * window.SiteAdsConfig for assets/ads-manager.js, built from the settings.
	 */
	public static function ads_config() {
		$s = self::all();
		return array(
			'enabled'      => (bool) $s['ads_enabled'],
			'publisherId'  => (string) $s['publisher_id'],
			'slots'        => array(
				'banner' => (string) $s['slot_banner'],
				'popup'  => (string) $s['slot_popup'],
			),
			'formats'      => array(
				'banner'       => (bool) $s['fmt_banner'],
				'popup'        => (bool) $s['fmt_popup'],
				'rewarded'     => (bool) $s['fmt_rewarded'],
				'interstitial' => (bool) $s['fmt_interstitial'],
			),
			'popup'        => array(
				'delayMs'          => (int) $s['popup_delay'],
				'hideWhenUnfilled' => (bool) $s['popup_hide_unfilled'],
				'loadTimeoutMs'    => (int) $s['popup_timeout'],
			),
			'rewarded'     => array(
				'placementName' => (string) $s['rewarded_placement'],
				'oncePerTab'    => (bool) $s['rewarded_once'],
				'sessionKey'    => (string) $s['rewarded_session_key'],
			),
			'interstitial' => array(
				'placementName' => (string) $s['interstitial_placement'],
				'placementType' => (string) $s['interstitial_type'],
			),
			'anchor'       => array(
				'position' => (string) $s['anchor_position'],
			),
		);
	}
}
