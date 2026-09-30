<?php
/**
 * Ad blocks, modelled on Ad Inserter: numbered code blocks, each with an
 * insertion position, alignment, page-type and list conditions, devices and
 * manual insertion (shortcode, widget, PHP function). Plus header, footer and
 * ads.txt code, and the Google H5 formats (rewarded, interstitial).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RQ_Ads {

	const OPTION = 'rq_ads';
	const BLOCKS = 16;

	private static $cache = null;

	/** Insertions made so far per block on this request (for "max insertions"). */
	private static $counts = array();

	/** Posts printed so far in the main loop (for "between posts"). */
	private static $loop_index = 0;

	/** Blocks already printed on this request for positions that must run once. */
	private static $done = array();

	/** True when a popup block was printed on this request. */
	private static $popup_printed = false;

	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'head' ), 1 );
		add_action( 'wp_body_open', array( __CLASS__, 'body_open' ), 5 );
		add_action( 'wp_footer', array( __CLASS__, 'footer' ), 5 );
		add_filter( 'the_content', array( __CLASS__, 'content' ), 20 );
		add_filter( 'the_excerpt', array( __CLASS__, 'excerpt' ), 20 );
		add_action( 'loop_start', array( __CLASS__, 'loop_start' ) );
		add_action( 'loop_end', array( __CLASS__, 'loop_end' ) );
		add_action( 'the_post', array( __CLASS__, 'between_posts' ) );
		add_filter( 'comments_template', array( __CLASS__, 'before_comments' ) );
		add_action( 'rq_ad_position', array( __CLASS__, 'position' ), 10, 2 );
		add_action( 'template_redirect', array( __CLASS__, 'ads_txt' ), 1 );
		add_action( 'widgets_init', array( __CLASS__, 'widgets' ) );
		add_shortcode( 'rq_ad', array( __CLASS__, 'shortcode' ) );
		add_shortcode( 'adinserter', array( __CLASS__, 'shortcode' ) );
	}

	/* ---------- Settings ---------- */

	public static function positions() {
		return array(
			__( 'General', 'rev-quiz' )     => array(
				'disabled'         => __( 'Disabled', 'rev-quiz' ),
				'above_header'     => __( 'Above header (after <body>)', 'rev-quiz' ),
				'before_post'      => __( 'Before post', 'rev-quiz' ),
				'after_post'       => __( 'After post', 'rev-quiz' ),
				'before_content'   => __( 'Before content', 'rev-quiz' ),
				'after_content'    => __( 'After content', 'rev-quiz' ),
				'before_paragraph' => __( 'Before paragraph', 'rev-quiz' ),
				'after_paragraph'  => __( 'After paragraph', 'rev-quiz' ),
				'before_image'     => __( 'Before image', 'rev-quiz' ),
				'after_image'      => __( 'After image', 'rev-quiz' ),
				'before_excerpt'   => __( 'Before excerpt', 'rev-quiz' ),
				'after_excerpt'    => __( 'After excerpt', 'rev-quiz' ),
				'between_posts'    => __( 'Between posts', 'rev-quiz' ),
				'before_comments'  => __( 'Before comments', 'rev-quiz' ),
				'footer'           => __( 'Footer', 'rev-quiz' ),
				'popup'            => __( 'Popup (modal with close button)', 'rev-quiz' ),
				'popunder'         => __( 'Popunder', 'rev-quiz' ),
			),
			__( 'Quiz pages', 'rev-quiz' )  => array(
				'quiz_top'            => __( 'Quiz: under the top bar', 'rev-quiz' ),
				'quiz_before_title'   => __( 'Quiz: before question title', 'rev-quiz' ),
				'quiz_after_title'    => __( 'Quiz: after question title', 'rev-quiz' ),
				'quiz_after_option'   => __( 'Quiz: after option', 'rev-quiz' ),
				'quiz_after_options'  => __( 'Quiz: after all options', 'rev-quiz' ),
				'quiz_result_top'     => __( 'Quiz: result screen, top', 'rev-quiz' ),
				'quiz_result_buttons' => __( 'Quiz: result screen, after buttons', 'rev-quiz' ),
				'quiz_bottom'         => __( 'Quiz: page bottom', 'rev-quiz' ),
			),
			__( 'Quiz lists', 'rev-quiz' ) => array(
				'list_top'        => __( 'Quiz list: under the header', 'rev-quiz' ),
				'list_after_grid' => __( 'Quiz home: after the category grid', 'rev-quiz' ),
				'list_bottom'     => __( 'Quiz list: bottom', 'rev-quiz' ),
			),
		);
	}

	/** Positions that take a number list ("2, 4" or "3+" etc.). */
	public static function numbered_positions() {
		return array( 'before_paragraph', 'after_paragraph', 'before_image', 'after_image', 'between_posts', 'quiz_after_option', 'before_excerpt', 'after_excerpt' );
	}

	public static function alignments() {
		return array(
			'default'     => __( 'Default', 'rev-quiz' ),
			'left'        => __( 'Left', 'rev-quiz' ),
			'center'      => __( 'Center', 'rev-quiz' ),
			'right'       => __( 'Right', 'rev-quiz' ),
			'float_left'  => __( 'Float left', 'rev-quiz' ),
			'float_right' => __( 'Float right', 'rev-quiz' ),
			'no_wrapping' => __( 'No wrapping (code only)', 'rev-quiz' ),
			'custom'      => __( 'Custom CSS', 'rev-quiz' ),
		);
	}

	public static function page_types() {
		return array(
			'quiz'      => __( 'Quiz pages', 'rev-quiz' ),
			'quiz_list' => __( 'Quiz home and category pages', 'rev-quiz' ),
			'post'      => __( 'Posts', 'rev-quiz' ),
			'page'      => __( 'Static pages', 'rev-quiz' ),
			'home'      => __( 'Homepage', 'rev-quiz' ),
			'archive'   => __( 'Category, tag and archive pages', 'rev-quiz' ),
			'search'    => __( 'Search pages', 'rev-quiz' ),
		);
	}

	public static function quiz_steps() {
		return array(
			'all'       => __( 'All steps', 'rev-quiz' ),
			'first'     => __( 'First question only', 'rev-quiz' ),
			'questions' => __( 'Question steps', 'rev-quiz' ),
			'later'     => __( 'Questions after the first', 'rev-quiz' ),
			'result'    => __( 'Result screen only', 'rev-quiz' ),
		);
	}

	public static function lists() {
		return array(
			'categories' => __( 'Categories', 'rev-quiz' ),
			'tags'       => __( 'Tags', 'rev-quiz' ),
			'ids'        => __( 'Post IDs', 'rev-quiz' ),
			'urls'       => __( 'URLs', 'rev-quiz' ),
			'params'     => __( 'URL parameters', 'rev-quiz' ),
			'referrers'  => __( 'Referrers', 'rev-quiz' ),
		);
	}

	public static function block_defaults() {
		$block = array(
			'name'            => '',
			'code'            => '',
			'position'        => 'disabled',
			'numbers'         => '',
			'align'           => 'default',
			'css'             => '',
			'pages'           => array( 'quiz', 'quiz_list', 'post', 'page' ),
			'quiz_steps'      => 'all',
			'devices'         => array( 'desktop', 'tablet', 'phone' ),
			'users'           => 'all', // all | guests | logged_in.
			'max'             => 0,
			'min_paragraphs'  => 0,
			'label'           => 0,
			'popup_delay'     => 1000,
			'popup_unfilled'  => 1,
			'pop_click'       => 1,  // Popunder opens on the visitor's Nth click (0 = on page load, network scripts only).
			'pop_hours'       => 24, // At most once per this many hours per visitor (0 = every page view).
			'manual_shortcode' => 1,
			'manual_widget'   => 1,
			'manual_php'      => 1,
		);
		foreach ( array_keys( self::lists() ) as $list ) {
			$block[ 'list_' . $list ]         = '';
			$block[ 'list_' . $list . '_mode' ] = 'black';
		}
		return $block;
	}

	public static function defaults() {
		$blocks = array();
		for ( $i = 1; $i <= self::BLOCKS; $i++ ) {
			$blocks[ $i ] = self::block_defaults();
		}
		return array(
			'blocks'  => $blocks,
			'header'  => array(
				'enabled'  => 1,
				'code'     => '',
				'sitewide' => 0,
			),
			'footer'  => array(
				'enabled'  => 1,
				'code'     => '',
				'sitewide' => 0,
			),
			'ads_txt' => array(
				'enabled' => 0,
				'content' => '',
			),
			'general' => array(
				'enabled'           => 1,
				'publisher_id'      => '',
				'load_adsense'      => 1,
				'anchor_position'   => 'account',
				'label_text'        => 'Advertisement',
				'block_class'       => 'rq-ad',
				'rewarded'          => 0,
				'rewarded_name'     => 'game_started',
				'rewarded_once'     => 1,
				'interstitial'      => 0,
				'interstitial_name' => 'between_sections',
				'interstitial_type' => 'next',
				'hide_for_admins'   => 0,
			),
		);
	}

	public static function all() {
		if ( null === self::$cache ) {
			$saved = get_option( self::OPTION, null );
			if ( ! is_array( $saved ) ) {
				$saved = self::migrate();
			}
			self::$cache = self::merge( $saved );
		}
		return self::$cache;
	}

	public static function merge( $saved ) {
		$defaults = self::defaults();
		$out      = $defaults;
		foreach ( array( 'header', 'footer', 'ads_txt', 'general' ) as $section ) {
			$out[ $section ] = wp_parse_args( isset( $saved[ $section ] ) ? (array) $saved[ $section ] : array(), $defaults[ $section ] );
		}
		for ( $i = 1; $i <= self::BLOCKS; $i++ ) {
			$out['blocks'][ $i ] = wp_parse_args( isset( $saved['blocks'][ $i ] ) ? (array) $saved['blocks'][ $i ] : array(), $defaults['blocks'][ $i ] );
		}
		return $out;
	}

	public static function flush_cache() {
		self::$cache = null;
	}

	public static function general( $key ) {
		$all = self::all();
		return isset( $all['general'][ $key ] ) ? $all['general'][ $key ] : null;
	}

	public static function block( $n ) {
		$all = self::all();
		return isset( $all['blocks'][ $n ] ) ? $all['blocks'][ $n ] : null;
	}

	public static function block_name( $n, $block = null ) {
		$block = $block ? $block : self::block( $n );
		/* translators: %d: block number */
		return '' !== trim( $block['name'] ) ? $block['name'] : sprintf( __( 'Block %d', 'rev-quiz' ), $n );
	}

	/** AdSense display unit, used by the migration and as the "insert AdSense unit" helper. */
	public static function adsense_unit( $publisher, $slot, $style = 'display:block' ) {
		return '<ins class="adsbygoogle" style="' . $style . '" data-ad-client="' . $publisher . '" data-ad-slot="' . $slot . '"></ins>' . "\n"
			. '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
	}

	/**
	 * First load after the update: turn the old "Ad code (AdSense)" and
	 * "Custom code" settings into blocks so the same ads keep showing.
	 */
	public static function migrate() {
		$old  = get_option( RQ_Settings::OPTION, array() );
		$old  = is_array( $old ) ? $old : array();
		$get  = function ( $key, $default ) use ( $old ) {
			return isset( $old[ $key ] ) ? $old[ $key ] : $default;
		};
		$data = self::defaults();

		$publisher = (string) $get( 'publisher_id', 'ca-pub-8026299207830445' );
		$on        = (int) $get( 'ads_enabled', 1 );
		$banner    = preg_replace( '/\D/', '', (string) $get( 'slot_banner', '7009281033' ) );
		$popup     = preg_replace( '/\D/', '', (string) $get( 'slot_popup', '7009281033' ) );
		$after     = (int) $get( 'banner_after', 4 );
		$ok        = preg_match( '/^ca-pub-\d+$/', $publisher );

		$data['general'] = array_merge(
			$data['general'],
			array(
				'enabled'           => $on,
				'publisher_id'      => $publisher,
				'anchor_position'   => (string) $get( 'anchor_position', 'account' ),
				'rewarded'          => (int) $get( 'fmt_rewarded', 1 ),
				'rewarded_name'     => (string) $get( 'rewarded_placement', 'game_started' ),
				'rewarded_once'     => (int) $get( 'rewarded_once', 1 ),
				'interstitial'      => (int) $get( 'fmt_interstitial', 0 ),
				'interstitial_name' => (string) $get( 'interstitial_placement', 'between_sections' ),
				'interstitial_type' => (string) $get( 'interstitial_type', 'next' ),
			)
		);

		if ( $ok && $banner ) {
			$banner_on = (int) $get( 'fmt_banner', 1 );
			$data['blocks'][1] = array_merge(
				$data['blocks'][1],
				array(
					'name'       => 'Quiz banner (between options)',
					'code'       => self::adsense_unit( $publisher, $banner, 'display:block;width:100%;height:350px' ),
					'position'   => $banner_on ? ( $after ? 'quiz_after_option' : 'quiz_after_options' ) : 'disabled',
					'numbers'    => $after ? (string) $after : '',
					'align'      => 'center',
					'pages'      => array( 'quiz' ),
					'quiz_steps' => 'questions',
				)
			);
			$data['blocks'][2] = array_merge(
				$data['blocks'][2],
				array(
					'name'       => 'Quiz result banner',
					'code'       => self::adsense_unit( $publisher, $banner, 'display:block;width:100%;height:300px' ),
					'position'   => $banner_on ? 'quiz_result_buttons' : 'disabled',
					'align'      => 'center',
					'pages'      => array( 'quiz' ),
					'quiz_steps' => 'result',
				)
			);
			$data['blocks'][3] = array_merge(
				$data['blocks'][3],
				array(
					'name'     => 'Quiz list banner',
					'code'     => self::adsense_unit( $publisher, $banner, 'display:block;width:100%;height:300px' ),
					'position' => $banner_on ? 'list_top' : 'disabled',
					'align'    => 'center',
					'pages'    => array( 'quiz_list' ),
				)
			);
		}
		if ( $ok && $popup ) {
			$data['blocks'][4] = array_merge(
				$data['blocks'][4],
				array(
					'name'           => 'Quiz popup',
					'code'           => self::adsense_unit( $publisher, $popup, 'display:block;width:300px;height:600px' ),
					'position'       => (int) $get( 'fmt_popup', 1 ) ? 'popup' : 'disabled',
					'pages'          => array( 'quiz' ),
					'quiz_steps'     => 'first',
					'popup_delay'    => (int) $get( 'popup_delay', 1 ),
					'popup_unfilled' => (int) $get( 'popup_hide_unfilled', 1 ),
				)
			);
		}

		$data['header']['code']     = (string) $get( 'header_code', '' );
		$data['footer']['code']     = (string) $get( 'footer_code', '' );
		$data['header']['sitewide'] = (int) $get( 'code_sitewide', 0 );
		$data['footer']['sitewide'] = (int) $get( 'code_sitewide', 0 );

		update_option( self::OPTION, $data, true );
		return $data;
	}

	/* ---------- Conditions ---------- */

	/** Master switch, admin option and the traffic filter. */
	public static function ads_allowed() {
		if ( ! self::general( 'enabled' ) || is_admin() || is_feed() ) {
			return false;
		}
		if ( self::general( 'hide_for_admins' ) && current_user_can( 'manage_options' ) ) {
			return false;
		}
		if ( RQ_Frontend::is_quiz_page() ) {
			return RQ_Cloak::visitor_allowed() || RQ_Settings::get( 'ads_for_blocked' );
		}
		return true;
	}

	public static function device() {
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) $_SERVER['HTTP_USER_AGENT'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( preg_match( '/iPad|Tablet|PlayBook|Silk|Kindle|Android(?!.*Mobile)/i', $ua ) ) {
			return 'tablet';
		}
		if ( wp_is_mobile() ) {
			return 'phone';
		}
		return 'desktop';
	}

	public static function page_type() {
		if ( is_singular( RQ_Post_Types::POST_TYPE ) ) {
			return 'quiz';
		}
		if ( is_tax( RQ_Post_Types::TAXONOMY ) || is_post_type_archive( RQ_Post_Types::POST_TYPE ) ) {
			return 'quiz_list';
		}
		if ( is_front_page() || is_home() ) {
			return 'home';
		}
		if ( is_search() ) {
			return 'search';
		}
		if ( is_page() ) {
			return 'page';
		}
		if ( is_singular() ) {
			return 'post';
		}
		if ( is_archive() ) {
			return 'archive';
		}
		return '';
	}

	/** Splits a list setting into lower-case items (comma or newline separated). */
	private static function items( $text ) {
		$parts = preg_split( '/[\r\n,]+/', strtolower( (string) $text ) );
		return array_values( array_filter( array_map( 'trim', $parts ), 'strlen' ) );
	}

	/** Wildcard match: "*" matches anything, e.g. /quiz/* or *facebook*. */
	private static function matches( $value, $pattern ) {
		$regex = '#^' . str_replace( '\*', '.*', preg_quote( $pattern, '#' ) ) . '$#i';
		return (bool) preg_match( $regex, $value );
	}

	/** Values the lists are checked against, for the current request. */
	private static function list_values( $list ) {
		$object = get_queried_object();
		switch ( $list ) {
			case 'categories':
				$values = array();
				if ( is_singular() && $object ) {
					foreach ( array( 'category', RQ_Post_Types::TAXONOMY ) as $tax ) {
						$terms = get_the_terms( $object, $tax );
						foreach ( is_array( $terms ) ? $terms : array() as $term ) {
							$values[] = $term->slug;
							$values[] = strtolower( $term->name );
						}
					}
				} elseif ( ( is_category() || is_tax( RQ_Post_Types::TAXONOMY ) ) && $object ) {
					$values[] = $object->slug;
					$values[] = strtolower( $object->name );
				}
				return $values;
			case 'tags':
				$values = array();
				if ( is_singular() && $object ) {
					$terms = get_the_terms( $object, 'post_tag' );
					foreach ( is_array( $terms ) ? $terms : array() as $term ) {
						$values[] = $term->slug;
						$values[] = strtolower( $term->name );
					}
				} elseif ( is_tag() && $object ) {
					$values[] = $object->slug;
				}
				return $values;
			case 'ids':
				return is_singular() ? array( (string) get_queried_object_id() ) : array();
			case 'urls':
				$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '/'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
				$rel  = '/' . ltrim( 0 === strpos( (string) $path, $home ) ? substr( (string) $path, strlen( $home ) ) : (string) $path, '/' );
				return array( strtolower( $rel ), strtolower( (string) $path ) );
			case 'params':
				$values = array();
				foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
					$key      = strtolower( sanitize_key( $key ) );
					$values[] = $key;
					if ( is_scalar( $value ) ) {
						$values[] = $key . '=' . strtolower( sanitize_text_field( wp_unslash( $value ) ) );
					}
				}
				return $values;
			case 'referrers':
				$ref  = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$host = $ref ? strtolower( (string) wp_parse_url( $ref, PHP_URL_HOST ) ) : '';
				$host = preg_replace( '/^www\./', '', $host );
				return $host ? array( $host ) : array( '#' ); // "#" = direct visit, as in Ad Inserter.
		}
		return array();
	}

	private static function list_ok( $block, $list ) {
		$items = self::items( $block[ 'list_' . $list ] );
		if ( ! $items ) {
			return true;
		}
		$hit = false;
		foreach ( self::list_values( $list ) as $value ) {
			foreach ( $items as $item ) {
				if ( self::matches( $value, $item ) || ( 'referrers' === $list && self::matches( $value, '*.' . $item ) ) ) {
					$hit = true;
					break 2;
				}
			}
		}
		return 'white' === $block[ 'list_' . $list . '_mode' ] ? $hit : ! $hit;
	}

	/** Every condition except the position itself. */
	public static function block_ok( $n, $block, $manual = false ) {
		if ( '' === trim( $block['code'] ) || ! self::ads_allowed() ) {
			return false;
		}
		if ( ! $manual ) {
			$type = self::page_type();
			if ( ! $type || ! in_array( $type, (array) $block['pages'], true ) ) {
				return false;
			}
			if ( 'quiz' === $type && ! self::step_ok( $block['quiz_steps'] ) ) {
				return false;
			}
		}
		if ( ! in_array( self::device(), (array) $block['devices'], true ) ) {
			return false;
		}
		if ( ( 'guests' === $block['users'] && is_user_logged_in() ) || ( 'logged_in' === $block['users'] && ! is_user_logged_in() ) ) {
			return false;
		}
		foreach ( array_keys( self::lists() ) as $list ) {
			if ( ! self::list_ok( $block, $list ) ) {
				return false;
			}
		}
		$max = (int) $block['max'];
		if ( $max && isset( self::$counts[ $n ] ) && self::$counts[ $n ] >= $max ) {
			return false;
		}
		return true;
	}

	private static function step_ok( $rule ) {
		if ( RQ_Frontend::$safe ) {
			return 'all' === $rule;
		}
		$post  = get_queried_object();
		$total = $post ? count( RQ_Quiz::questions( $post->ID ) ) : 0;
		$step  = RQ_Frontend::$step;
		switch ( $rule ) {
			case 'first':
				return 1 === $step;
			case 'questions':
				return $step <= $total;
			case 'later':
				return $step > 1 && $step <= $total;
			case 'result':
				return $step > $total;
		}
		return true;
	}

	/** Parses "2, 4, 6+" or "3%" style number lists. Returns [numbers, from, every]. */
	private static function numbers( $text ) {
		$numbers = array();
		$from    = 0;
		$every   = 0;
		foreach ( self::items( $text ) as $item ) {
			if ( preg_match( '/^(\d+)\+$/', $item, $m ) ) {
				$from = (int) $m[1];
			} elseif ( preg_match( '/^%(\d+)$/', $item, $m ) ) {
				$every = (int) $m[1];
			} elseif ( ctype_digit( $item ) ) {
				$numbers[] = (int) $item;
			}
		}
		if ( ! $numbers && ! $from && ! $every ) {
			$numbers = array( 1 );
		}
		return array( $numbers, $from, $every );
	}

	private static function number_hit( $text, $n ) {
		list( $numbers, $from, $every ) = self::numbers( $text );
		return in_array( $n, $numbers, true ) || ( $from && $n >= $from ) || ( $every && 0 === $n % $every );
	}

	/* ---------- Output ---------- */

	/** The block's code in its wrapper, and counts the insertion. */
	public static function render( $n, $block = null ) {
		$block = $block ? $block : self::block( $n );
		self::$counts[ $n ] = isset( self::$counts[ $n ] ) ? self::$counts[ $n ] + 1 : 1;

		$code = do_shortcode( $block['code'] );
		if ( 'no_wrapping' === $block['align'] ) {
			return $code;
		}
		$styles = array(
			'default'     => '',
			'left'        => 'text-align:left;',
			'center'      => 'text-align:center;margin-left:auto;margin-right:auto;',
			'right'       => 'text-align:right;',
			'float_left'  => 'float:left;margin:0 12px 8px 0;',
			'float_right' => 'float:right;margin:0 0 8px 12px;',
			'custom'      => (string) $block['css'],
		);
		$style = isset( $styles[ $block['align'] ] ) ? $styles[ $block['align'] ] : '';
		$class = sanitize_html_class( self::general( 'block_class' ) );
		$class = $class ? $class : 'rq-ad';
		$label = $block['label'] ? '<div class="rq-ad-label">' . esc_html( self::general( 'label_text' ) ) . '</div>' : '';

		return sprintf(
			'<div class="%1$s %1$s-%2$d"%3$s>%4$s%5$s</div>',
			esc_attr( $class ),
			(int) $n,
			$style ? ' style="' . esc_attr( $style ) . '"' : '',
			$label,
			$code
		);
	}

	/** Blocks that use a position and pass their conditions, as [n => block]. */
	private static function blocks_for( $position ) {
		$out = array();
		foreach ( self::all()['blocks'] as $n => $block ) {
			if ( $block['position'] === $position && self::block_ok( $n, $block ) ) {
				$out[ $n ] = $block;
			}
		}
		return $out;
	}

	private static function print_position( $position ) {
		$html = '';
		foreach ( self::blocks_for( $position ) as $n => $block ) {
			$html .= self::render( $n, $block );
		}
		return $html;
	}

	/**
	 * Theme-independent positions printed by the quiz templates:
	 * do_action( 'rq_ad_position', 'quiz_after_option', 3 ).
	 */
	public static function position( $position, $number = 0 ) {
		if ( in_array( $position, array( 'before_post', 'after_post' ), true ) ) {
			if ( isset( self::$done[ $position ] ) ) {
				return;
			}
			self::$done[ $position ] = true;
		}
		foreach ( self::blocks_for( $position ) as $n => $block ) {
			if ( 'quiz_after_option' === $position ) {
				if ( $number > 0 ) {
					// $number is the option just printed.
					if ( ! self::number_hit( $block['numbers'], $number ) ) {
						continue;
					}
					self::$done[ 'option_' . $n ] = true;
				} elseif ( ! empty( self::$done[ 'option_' . $n ] ) ) {
					// After the list: only when the question had fewer options than the number set.
					continue;
				}
			}
			if ( 'between_posts' === $position && ! self::number_hit( $block['numbers'], (int) $number ) ) {
				continue;
			}
			echo self::render( $n, $block ); // phpcs:ignore WordPress.Security.EscapeOutput -- admin-supplied ad code.
		}
	}

	public static function head() {
		if ( ! self::ads_allowed() && ! self::code_here( 'header' ) ) {
			return;
		}
		if ( self::ads_allowed() ) {
			$publisher = (string) self::general( 'publisher_id' );
			if ( preg_match( '/^ca-pub-\d+$/', $publisher ) && self::general( 'load_adsense' ) ) {
				$overlay = in_array( self::general( 'anchor_position' ), array( 'bottom', 'collapsed-bottom' ), true ) ? ' data-overlays="' . esc_attr( self::general( 'anchor_position' ) ) . '"' : '';
				echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . esc_attr( $publisher ) . '" crossorigin="anonymous"' . $overlay . '></script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput, WordPress.WP.EnqueuedResources
			}
			$class = sanitize_html_class( self::general( 'block_class' ) );
			echo '<style>.' . esc_attr( $class ? $class : 'rq-ad' ) . '{margin:12px 0;clear:both;max-width:100%;overflow:hidden}ins.adsbygoogle[data-ad-status=unfilled]{display:none!important}.' . esc_attr( $class ? $class : 'rq-ad' ) . ':has(ins.adsbygoogle[data-ad-status=unfilled]){display:none}.rq-ad-label{font-size:11px;color:#888;text-align:center;margin-bottom:4px;text-transform:uppercase;letter-spacing:.04em}</style>' . "\n";
		}
		if ( self::code_here( 'header' ) ) {
			echo self::all()['header']['code'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- admin-supplied code.
		}
	}

	private static function code_here( $section ) {
		$s = self::all()[ $section ];
		if ( ! $s['enabled'] || '' === trim( $s['code'] ) || is_admin() ) {
			return false;
		}
		return RQ_Frontend::is_quiz_page() || $s['sitewide'];
	}

	public static function body_open() {
		echo self::print_position( 'above_header' ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	public static function footer() {
		echo self::print_position( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput
		self::popups();
		self::popunders();
		if ( self::code_here( 'footer' ) ) {
			echo self::all()['footer']['code'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- admin-supplied code.
		}
		if ( self::ads_allowed() && ( self::$popup_printed || ( RQ_Frontend::is_quiz_page() && ( self::general( 'rewarded' ) || self::general( 'interstitial' ) ) ) ) ) {
			wp_enqueue_script( 'rev-quiz-ads', RQ_URL . 'assets/ads-manager.js', array(), RQ_VERSION, true );
			wp_add_inline_script( 'rev-quiz-ads', 'window.SiteAdsConfig = ' . wp_json_encode( self::js_config() ) . ';', 'before' );
		}
	}

	private static function popups() {
		foreach ( self::blocks_for( 'popup' ) as $n => $block ) {
			self::$popup_printed = true;
			printf(
				'<div class="rq-modal hidden" data-rq-popup data-delay="%d" data-hide-unfilled="%d"><button type="button" class="rq-modal-close" aria-label="%s">&times;</button><div class="rq-modal-body"></div><template>%s</template></div>',
				(int) $block['popup_delay'],
				(int) $block['popup_unfilled'],
				esc_attr__( 'Close', 'rev-quiz' ),
				self::render( $n, $block ) // phpcs:ignore WordPress.Security.EscapeOutput -- admin-supplied ad code.
			);
		}
	}

	/**
	 * Popunder blocks. The code is either a network's popunder script (Adsterra, PropellerAds,
	 * Monetag ...) or just the URL to open. Either way it waits in a <template> until the
	 * visitor's Nth click and runs at most once per "pop_hours" per visitor.
	 */
	private static function popunders() {
		foreach ( self::blocks_for( 'popunder' ) as $n => $block ) {
			self::$popup_printed = true;
			self::$counts[ $n ]  = isset( self::$counts[ $n ] ) ? self::$counts[ $n ] + 1 : 1;
			$code                = trim( $block['code'] );
			$url                 = preg_match( '#^https?://\S+$#i', $code ) ? esc_url( $code ) : '';
			printf(
				'<template data-rq-popunder="%d" data-url="%s" data-click="%d" data-hours="%d">%s</template>' . "\n",
				(int) $n,
				esc_attr( $url ),
				(int) $block['pop_click'],
				(int) $block['pop_hours'],
				$url ? '' : do_shortcode( $code ) // phpcs:ignore WordPress.Security.EscapeOutput -- admin-supplied ad code.
			);
		}
	}

	public static function js_config() {
		$g = self::all()['general'];
		return array(
			'enabled'      => (bool) $g['enabled'],
			'publisherId'  => (string) $g['publisher_id'],
			'formats'      => array(
				'rewarded'     => (bool) $g['rewarded'],
				'interstitial' => (bool) $g['interstitial'],
			),
			'rewarded'     => array(
				'placementName' => (string) $g['rewarded_name'],
				'oncePerTab'    => (bool) $g['rewarded_once'],
				'sessionKey'    => 'adShown',
			),
			'interstitial' => array(
				'placementName' => (string) $g['interstitial_name'],
				'placementType' => (string) $g['interstitial_type'],
			),
		);
	}

	/* ---------- Content positions ---------- */

	private static function in_main_content() {
		return in_the_loop() && is_main_query();
	}

	public static function content( $content ) {
		if ( is_admin() || is_feed() || ! self::ads_allowed() ) {
			return $content;
		}
		// Quiz templates print their content outside the loop, so accept the queried quiz too.
		$quiz = is_singular( RQ_Post_Types::POST_TYPE ) && get_the_ID() === get_queried_object_id();
		if ( ! $quiz && ( ! is_singular() || ! self::in_main_content() ) ) {
			return $content;
		}
		foreach ( self::all()['blocks'] as $n => $block ) {
			if ( ! in_array( $block['position'], array( 'before_content', 'after_content', 'before_paragraph', 'after_paragraph', 'before_image', 'after_image' ), true ) ) {
				continue;
			}
			if ( ! self::block_ok( $n, $block ) ) {
				continue;
			}
			switch ( $block['position'] ) {
				case 'before_content':
					$content = self::render( $n, $block ) . $content;
					break;
				case 'after_content':
					$content .= self::render( $n, $block );
					break;
				case 'before_paragraph':
				case 'after_paragraph':
					$content = self::paragraphs( $content, $n, $block );
					break;
				default:
					$content = self::images( $content, $n, $block );
			}
		}
		return $content;
	}

	private static function paragraphs( $content, $n, $block ) {
		$before = 'before_paragraph' === $block['position'];
		$parts  = preg_split( '#(</p>)#i', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
		$count  = (int) floor( count( $parts ) / 2 );
		if ( $count < max( 1, (int) $block['min_paragraphs'] ) ) {
			return $content;
		}
		$out = '';
		$p   = 0;
		for ( $i = 0; $i < count( $parts ); $i += 2 ) {
			$chunk = $parts[ $i ];
			$close = isset( $parts[ $i + 1 ] ) ? $parts[ $i + 1 ] : '';
			if ( '' === $close ) {
				$out .= $chunk;
				continue;
			}
			$p++;
			$hit = self::number_hit( $block['numbers'], $p ) && self::under_max( $n, $block );
			if ( $before && $hit && preg_match( '#^(.*?)(<p[\s>].*)$#is', $chunk, $m ) ) {
				$out .= $m[1] . self::render( $n, $block ) . $m[2] . $close;
			} else {
				$out .= $chunk . $close . ( ! $before && $hit ? self::render( $n, $block ) : '' );
			}
		}
		return $out;
	}

	private static function under_max( $n, $block ) {
		$max = (int) $block['max'];
		return ! $max || ! isset( self::$counts[ $n ] ) || self::$counts[ $n ] < $max;
	}

	private static function images( $content, $n, $block ) {
		$before = 'before_image' === $block['position'];
		$i      = 0;
		return preg_replace_callback(
			'#(<figure\b[^>]*>.*?</figure>|<img\b[^>]*>)#is',
			function ( $m ) use ( &$i, $n, $block, $before ) {
				$i++;
				if ( ! self::number_hit( $block['numbers'], $i ) || ! self::under_max( $n, $block ) ) {
					return $m[0];
				}
				return $before ? self::render( $n, $block ) . $m[0] : $m[0] . self::render( $n, $block );
			},
			$content
		);
	}

	public static function excerpt( $excerpt ) {
		if ( is_admin() || is_singular() || ! self::ads_allowed() ) {
			return $excerpt;
		}
		foreach ( self::all()['blocks'] as $n => $block ) {
			if ( ! in_array( $block['position'], array( 'before_excerpt', 'after_excerpt' ), true ) || ! self::block_ok( $n, $block ) ) {
				continue;
			}
			if ( ! self::number_hit( $block['numbers'], self::$loop_index ) ) {
				continue;
			}
			$excerpt = 'before_excerpt' === $block['position'] ? self::render( $n, $block ) . $excerpt : $excerpt . self::render( $n, $block );
		}
		return $excerpt;
	}

	public static function loop_start( $query ) {
		if ( ! $query->is_main_query() || is_admin() ) {
			return;
		}
		self::$loop_index = 0;
		if ( $query->is_singular() ) {
			self::position( 'before_post' );
		}
	}

	public static function loop_end( $query ) {
		if ( ! $query->is_main_query() || is_admin() ) {
			return;
		}
		if ( $query->is_singular() ) {
			self::position( 'after_post' );
		}
	}

	/** "Between posts" on theme archives: before post N+1 means after post N. */
	public static function between_posts( $post ) {
		global $wp_query;
		if ( is_admin() || is_singular() || ! in_the_loop() || ! $wp_query->is_main_query() ) {
			return;
		}
		self::$loop_index++;
		if ( self::$loop_index > 1 ) {
			self::position( 'between_posts', self::$loop_index - 1 );
		}
	}

	public static function before_comments( $template ) {
		echo self::print_position( 'before_comments' ); // phpcs:ignore WordPress.Security.EscapeOutput
		return $template;
	}

	/* ---------- Manual insertion ---------- */

	public static function manual( $n, $type ) {
		$n     = (int) $n;
		$block = self::block( $n );
		if ( ! $block || empty( $block[ 'manual_' . $type ] ) || ! self::block_ok( $n, $block, true ) ) {
			return '';
		}
		return self::render( $n, $block );
	}

	/** [rq_ad block="3"], [rq_ad name="Sidebar"], and [adinserter block="3"] for pasted Ad Inserter shortcodes. */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'block' => 0,
				'name'  => '',
			),
			$atts
		);
		$n = (int) $atts['block'];
		if ( ! $n && '' !== $atts['name'] ) {
			foreach ( self::all()['blocks'] as $i => $block ) {
				if ( 0 === strcasecmp( trim( $block['name'] ), trim( $atts['name'] ) ) ) {
					$n = $i;
					break;
				}
			}
		}
		return self::manual( $n, 'shortcode' );
	}

	public static function widgets() {
		register_widget( 'RQ_Ad_Widget' );
	}

	/* ---------- ads.txt ---------- */

	public static function ads_txt() {
		$s = self::all()['ads_txt'];
		if ( ! $s['enabled'] ) {
			return;
		}
		$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		if ( rtrim( $home, '/' ) . '/ads.txt' !== $path ) {
			return;
		}
		status_header( 200 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo esc_html( $s['content'] );
		exit;
	}
}

class RQ_Ad_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct( 'rq_ad_widget', __( 'REW QUIZZ ad block', 'rev-quiz' ), array( 'description' => __( 'Shows one of the ad blocks from REW QUIZZ > Ad blocks.', 'rev-quiz' ) ) );
	}

	public function widget( $args, $instance ) {
		$html = RQ_Ads::manual( isset( $instance['block'] ) ? (int) $instance['block'] : 0, 'widget' );
		if ( '' === $html ) {
			return;
		}
		echo $args['before_widget'] . $html . $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	public function form( $instance ) {
		$current = isset( $instance['block'] ) ? (int) $instance['block'] : 1;
		echo '<p><label for="' . esc_attr( $this->get_field_id( 'block' ) ) . '">' . esc_html__( 'Block', 'rev-quiz' ) . '</label> ';
		echo '<select class="widefat" id="' . esc_attr( $this->get_field_id( 'block' ) ) . '" name="' . esc_attr( $this->get_field_name( 'block' ) ) . '">';
		for ( $i = 1; $i <= RQ_Ads::BLOCKS; $i++ ) {
			echo '<option value="' . (int) $i . '" ' . selected( $current, $i, false ) . '>' . (int) $i . ' - ' . esc_html( RQ_Ads::block_name( $i ) ) . '</option>';
		}
		echo '</select></p>';
	}

	public function update( $new, $old ) {
		return array( 'block' => max( 1, min( RQ_Ads::BLOCKS, (int) $new['block'] ) ) );
	}
}

/** PHP function call, like Ad Inserter's adinserter( 3 ). */
function rq_ad_block( $block ) {
	echo RQ_Ads::manual( $block, 'php' ); // phpcs:ignore WordPress.Security.EscapeOutput
}
