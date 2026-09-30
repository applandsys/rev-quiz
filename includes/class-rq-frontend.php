<?php
/**
 * Front end: routing checks, the traffic filter, templates, assets and injected code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RQ_Frontend {

	/** Current step (1-based). questions + 1 is the result screen. */
	public static $step = 1;

	/** True when the visitor is filtered out and gets the text-only page. */
	public static $safe = false;

	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ), 5 );
		add_filter( 'template_include', array( __CLASS__, 'template_include' ), 99 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_theme_styles' ), 100 );
		add_action( 'wp_head', array( __CLASS__, 'head_code' ), 20 );
		add_action( 'wp_footer', array( __CLASS__, 'footer_code' ), 20 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
	}

	public static function is_quiz_page() {
		return is_singular( RQ_Post_Types::POST_TYPE ) || is_tax( RQ_Post_Types::TAXONOMY ) || is_post_type_archive( RQ_Post_Types::POST_TYPE );
	}

	/** Ad code is printed only for visitors who pass the traffic filter (unless allowed in settings). */
	public static function ads_allowed() {
		if ( ! RQ_Settings::get( 'ads_enabled' ) ) {
			return false;
		}
		return RQ_Cloak::visitor_allowed() || RQ_Settings::get( 'ads_for_blocked' );
	}

	public static function template_redirect() {
		if ( ! self::is_quiz_page() ) {
			return;
		}

		if ( RQ_Cloak::is_filter_active() ) {
			// The page differs per visitor, so it must never be cached.
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			nocache_headers();
		}

		$allowed = RQ_Cloak::visitor_allowed();

		if ( ! is_singular( RQ_Post_Types::POST_TYPE ) ) {
			return;
		}

		$post  = get_queried_object();
		$total = count( RQ_Quiz::questions( $post->ID ) );
		$step  = max( 1, (int) get_query_var( 'rq_step' ) );

		// Keep the category slug in the URL correct, e.g. after a quiz changes category.
		$requested_cat = (string) get_query_var( 'rq_cat' );
		if ( get_option( 'permalink_structure' ) && $requested_cat !== RQ_Post_Types::category_slug( $post ) ) {
			wp_safe_redirect( RQ_Post_Types::step_link( $post, $step ), 301 );
			exit;
		}

		if ( ! $allowed ) {
			if ( 'redirect' === RQ_Settings::get( 'fallback_mode' ) && RQ_Settings::get( 'fallback_url' ) ) {
				wp_redirect( esc_url_raw( RQ_Settings::get( 'fallback_url' ) ), 302 ); // phpcs:ignore WordPress.Security.SafeRedirect -- admin-chosen external URL.
				exit;
			}
			self::$safe = true;
			if ( $step > 1 ) {
				wp_safe_redirect( get_permalink( $post ), 302 );
				exit;
			}
			return;
		}

		if ( $step > $total + 1 ) {
			wp_safe_redirect( get_permalink( $post ), 302 );
			exit;
		}
		self::$step = $step;
	}

	public static function template_include( $template ) {
		if ( is_singular( RQ_Post_Types::POST_TYPE ) ) {
			return self::locate( 'single-quiz.php' );
		}
		if ( is_tax( RQ_Post_Types::TAXONOMY ) || is_post_type_archive( RQ_Post_Types::POST_TYPE ) ) {
			return self::locate( 'quiz-list.php' );
		}
		return $template;
	}

	/** Themes can override a template by copying it to {theme}/rev-quiz/. */
	public static function locate( $name ) {
		$theme = locate_template( array( 'rev-quiz/' . $name ) );
		return $theme ? $theme : RQ_DIR . 'templates/' . $name;
	}

	public static function enqueue() {
		if ( ! self::is_quiz_page() ) {
			return;
		}
		wp_enqueue_style( 'rev-quiz', RQ_URL . 'assets/css/rev-quiz.css', array(), RQ_VERSION );
		wp_enqueue_script( 'rev-quiz', RQ_URL . 'assets/js/rev-quiz.js', array(), RQ_VERSION, true );

		if ( self::ads_allowed() ) {
			wp_enqueue_script( 'rev-quiz-ads', RQ_URL . 'assets/ads-manager.js', array(), RQ_VERSION, false );
			wp_add_inline_script( 'rev-quiz-ads', 'window.SiteAdsConfig = ' . wp_json_encode( RQ_Settings::ads_config() ) . ';', 'before' );
		}
	}

	/** The quiz pages have their own layout; theme CSS would only fight it. */
	public static function dequeue_theme_styles() {
		if ( ! self::is_quiz_page() ) {
			return;
		}
		$theme_urls = array( get_template_directory_uri(), get_stylesheet_directory_uri() );
		foreach ( wp_styles()->queue as $handle ) {
			$src = isset( wp_styles()->registered[ $handle ] ) ? (string) wp_styles()->registered[ $handle ]->src : '';
			foreach ( $theme_urls as $url ) {
				if ( $src && 0 === strpos( $src, $url ) ) {
					wp_dequeue_style( $handle );
				}
			}
		}
	}

	public static function body_class( $classes ) {
		if ( self::is_quiz_page() ) {
			$classes[] = 'rq-body';
			if ( self::$safe ) {
				$classes[] = 'rq-safe';
			}
		}
		return $classes;
	}

	/** Step and result screens are thin duplicates of the quiz; keep them out of search. */
	public static function robots( $robots ) {
		if ( is_singular( RQ_Post_Types::POST_TYPE ) && self::$step > 1 ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}

	private static function code_here() {
		return self::is_quiz_page() || RQ_Settings::get( 'code_sitewide' );
	}

	public static function head_code() {
		if ( self::is_quiz_page() ) {
			$app_id = trim( (string) RQ_Settings::get( 'onesignal_app_id' ) );
			if ( $app_id ) {
				echo "\n<script src=\"https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js\" defer></script>\n";
				echo '<script>window.OneSignalDeferred = window.OneSignalDeferred || []; OneSignalDeferred.push(async function (OneSignal) { await OneSignal.init({ appId: ' . wp_json_encode( $app_id ) . ' }); });</script>' . "\n";
			}
			if ( RQ_Settings::get( 'translate_enabled' ) && ! self::$safe ) {
				$config = array(
					'pageLanguage'      => (string) RQ_Settings::get( 'translate_page_lang' ),
					'includedLanguages' => (string) RQ_Settings::get( 'translate_languages' ),
				);
				echo '<script>function rqTranslateInit(){ if (window.google && google.translate) { new google.translate.TranslateElement(Object.assign(' . wp_json_encode( $config ) . ', { autoDisplay: false }), "rq-translate"); } }</script>' . "\n";
				echo '<script src="https://translate.google.com/translate_a/element.js?cb=rqTranslateInit" defer></script>' . "\n";
			}
		}
		if ( self::code_here() ) {
			echo RQ_Settings::get( 'header_code' ); // phpcs:ignore WordPress.Security.EscapeOutput -- admin-supplied code, saved only with unfiltered_html.
		}
	}

	public static function footer_code() {
		if ( self::code_here() ) {
			echo RQ_Settings::get( 'footer_code' ); // phpcs:ignore WordPress.Security.EscapeOutput -- admin-supplied code, saved only with unfiltered_html.
		}
	}

	/* ---------- Template helpers ---------- */

	public static function banner( $height = 300 ) {
		if ( ! self::ads_allowed() || ! RQ_Settings::get( 'fmt_banner' ) ) {
			return;
		}
		printf(
			'<div class="banner rq-banner"><ins class="adsbygoogle" data-site-ad="banner" style="display:block; width:100%% !important; height:%dpx !important;"></ins></div>',
			(int) $height
		);
	}

	public static function popup() {
		if ( ! self::ads_allowed() || ! RQ_Settings::get( 'fmt_popup' ) ) {
			return;
		}
		?>
		<div id="ads-modal" class="hidden rq-modal">
			<button type="button" id="close_modal" aria-label="<?php esc_attr_e( 'Close', 'rev-quiz' ); ?>">&times;</button>
			<div class="rq-modal-body">
				<ins class="adsbygoogle" data-site-ad="popup" style="display:block; width:300px !important; height:600px !important;"></ins>
			</div>
		</div>
		<?php
	}

	public static function rewarded_box( $target ) {
		if ( ! self::ads_allowed() || ! RQ_Settings::get( 'fmt_rewarded' ) || ! RQ_Settings::get( 'rewarded_box' ) ) {
			return;
		}
		?>
		<div class="rq-reward">
			<div class="rq-reward-text"><?php echo esc_html( RQ_Settings::get( 'rewarded_text' ) ); ?></div>
			<a href="<?php echo esc_attr( $target ); ?>" class="rq-reward-btn" onclick="return window.SiteAds ? SiteAds.showRewardedAd(<?php echo esc_attr( wp_json_encode( $target ) ); ?>) : true;"><?php echo esc_html( RQ_Settings::get( 'rewarded_button' ) ); ?></a>
		</div>
		<?php
	}

	public static function arrow() {
		return '<svg class="rq-arrow" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><circle cx="12" cy="12" r="11" fill="currentColor"/><path d="M10 7l5 5-5 5" stroke="#fff" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}
}
