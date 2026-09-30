<?php
/**
 * REW QUIZZ > Ad blocks: the Ad Inserter style settings screen.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RQ_Ads_Page {

	const SLUG = 'rev-quiz-ads';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 11 );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function url() {
		return admin_url( 'edit.php?post_type=' . RQ_Post_Types::POST_TYPE . '&page=' . self::SLUG );
	}

	public static function menu() {
		add_submenu_page(
			'edit.php?post_type=' . RQ_Post_Types::POST_TYPE,
			__( 'Ad blocks', 'rev-quiz' ),
			__( 'Ad blocks', 'rev-quiz' ),
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'render' )
		);
	}

	public static function register() {
		register_setting(
			'rq_ads',
			RQ_Ads::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
			)
		);
	}

	public static function assets( $hook ) {
		if ( false === strpos( (string) $hook, self::SLUG ) ) {
			return;
		}
		$editor = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
		wp_enqueue_style( 'rev-quiz-admin', RQ_URL . 'assets/css/rev-quiz-admin.css', array(), RQ_VERSION );
		wp_enqueue_script( 'rev-quiz-ads-admin', RQ_URL . 'assets/js/rev-quiz-ads-admin.js', array( 'jquery' ), RQ_VERSION, true );
		wp_localize_script( 'rev-quiz-ads-admin', 'rqAdsAdmin', array( 'editor' => $editor ? $editor : false ) );
	}

	/* ---------- Saving ---------- */

	private static function code( $value ) {
		$value = (string) $value;
		return current_user_can( 'unfiltered_html' ) ? $value : wp_kses_post( $value );
	}

	private static function pick( $value, $allowed, $default ) {
		return in_array( $value, $allowed, true ) ? $value : $default;
	}

	public static function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array(); // options.php has already unslashed it.
		$out   = RQ_Ads::defaults();

		$positions = array();
		foreach ( RQ_Ads::positions() as $group ) {
			$positions = array_merge( $positions, array_keys( $group ) );
		}

		for ( $i = 1; $i <= RQ_Ads::BLOCKS; $i++ ) {
			$in = isset( $input['blocks'][ $i ] ) ? (array) $input['blocks'][ $i ] : array();
			$b  = RQ_Ads::block_defaults();

			$b['name']           = isset( $in['name'] ) ? sanitize_text_field( $in['name'] ) : '';
			$b['code']           = isset( $in['code'] ) ? self::code( $in['code'] ) : '';
			$b['position']       = self::pick( isset( $in['position'] ) ? $in['position'] : '', $positions, 'disabled' );
			$b['numbers']        = isset( $in['numbers'] ) ? preg_replace( '/[^0-9,+% ]/', '', $in['numbers'] ) : '';
			$b['align']          = self::pick( isset( $in['align'] ) ? $in['align'] : '', array_keys( RQ_Ads::alignments() ), 'default' );
			$b['css']            = isset( $in['css'] ) ? sanitize_text_field( $in['css'] ) : '';
			$b['pages']          = array_values( array_intersect( array_keys( RQ_Ads::page_types() ), isset( $in['pages'] ) ? (array) $in['pages'] : array() ) );
			$b['quiz_steps']     = self::pick( isset( $in['quiz_steps'] ) ? $in['quiz_steps'] : '', array_keys( RQ_Ads::quiz_steps() ), 'all' );
			$b['devices']        = array_values( array_intersect( array( 'desktop', 'tablet', 'phone' ), isset( $in['devices'] ) ? (array) $in['devices'] : array() ) );
			$b['users']          = self::pick( isset( $in['users'] ) ? $in['users'] : '', array( 'all', 'guests', 'logged_in' ), 'all' );
			$b['max']            = isset( $in['max'] ) ? absint( $in['max'] ) : 0;
			$b['min_paragraphs'] = isset( $in['min_paragraphs'] ) ? absint( $in['min_paragraphs'] ) : 0;
			$b['popup_delay']    = isset( $in['popup_delay'] ) ? absint( $in['popup_delay'] ) : 0;
			foreach ( array( 'label', 'popup_unfilled', 'manual_shortcode', 'manual_widget', 'manual_php' ) as $key ) {
				$b[ $key ] = empty( $in[ $key ] ) ? 0 : 1;
			}
			foreach ( array_keys( RQ_Ads::lists() ) as $list ) {
				$b[ 'list_' . $list ]           = isset( $in[ 'list_' . $list ] ) ? sanitize_textarea_field( $in[ 'list_' . $list ] ) : '';
				$b[ 'list_' . $list . '_mode' ] = isset( $in[ 'list_' . $list . '_mode' ] ) && 'white' === $in[ 'list_' . $list . '_mode' ] ? 'white' : 'black';
			}
			$out['blocks'][ $i ] = $b;
		}

		foreach ( array( 'header', 'footer' ) as $section ) {
			$in              = isset( $input[ $section ] ) ? (array) $input[ $section ] : array();
			$out[ $section ] = array(
				'enabled'  => empty( $in['enabled'] ) ? 0 : 1,
				'code'     => isset( $in['code'] ) ? self::code( $in['code'] ) : '',
				'sitewide' => empty( $in['sitewide'] ) ? 0 : 1,
			);
		}

		$in             = isset( $input['ads_txt'] ) ? (array) $input['ads_txt'] : array();
		$out['ads_txt'] = array(
			'enabled' => empty( $in['enabled'] ) ? 0 : 1,
			'content' => isset( $in['content'] ) ? sanitize_textarea_field( $in['content'] ) : '',
		);

		$in        = isset( $input['general'] ) ? (array) $input['general'] : array();
		$g         = $out['general'];
		$publisher = isset( $in['publisher_id'] ) ? trim( sanitize_text_field( $in['publisher_id'] ) ) : '';
		$publisher = preg_match( '/^\d+$/', $publisher ) ? 'ca-pub-' . $publisher : $publisher;
		if ( $publisher && ! preg_match( '/^ca-pub-\d+$/', $publisher ) ) {
			add_settings_error( RQ_Ads::OPTION, 'publisher', __( 'The AdSense publisher ID should look like ca-pub-1234567890123456. The AdSense library is not loaded until it does.', 'rev-quiz' ) );
		}
		$g['publisher_id'] = $publisher;
		foreach ( array( 'enabled', 'load_adsense', 'rewarded', 'rewarded_once', 'interstitial', 'hide_for_admins' ) as $key ) {
			$g[ $key ] = empty( $in[ $key ] ) ? 0 : 1;
		}
		foreach ( array( 'label_text', 'rewarded_name', 'interstitial_name' ) as $key ) {
			$g[ $key ] = isset( $in[ $key ] ) ? sanitize_text_field( $in[ $key ] ) : '';
		}
		$g['block_class']       = isset( $in['block_class'] ) ? sanitize_html_class( $in['block_class'] ) : 'rq-ad';
		$g['block_class']       = $g['block_class'] ? $g['block_class'] : 'rq-ad';
		$g['anchor_position']   = self::pick( isset( $in['anchor_position'] ) ? $in['anchor_position'] : '', array( 'account', 'bottom', 'collapsed-bottom' ), 'account' );
		$g['interstitial_type'] = self::pick( isset( $in['interstitial_type'] ) ? $in['interstitial_type'] : '', array( 'next', 'browse', 'start', 'pause' ), 'next' );
		$out['general']         = $g;

		RQ_Ads::flush_cache();
		return $out;
	}

	/* ---------- Field helpers ---------- */

	private static function name( $path ) {
		return RQ_Ads::OPTION . $path;
	}

	private static function checkbox( $path, $checked, $label, $value = '1' ) {
		printf(
			'<label class="rq-ai-check"><input type="checkbox" name="%s" value="%s" %s> %s</label>',
			esc_attr( self::name( $path ) ),
			esc_attr( $value ),
			checked( $checked, true, false ),
			esc_html( $label )
		);
	}

	private static function select( $path, $current, $options, $class = '' ) {
		printf( '<select name="%s" class="%s">', esc_attr( self::name( $path ) ), esc_attr( $class ) );
		foreach ( $options as $value => $label ) {
			if ( is_array( $label ) ) {
				echo '<optgroup label="' . esc_attr( $value ) . '">';
				foreach ( $label as $v => $l ) {
					echo '<option value="' . esc_attr( $v ) . '" ' . selected( $current, $v, false ) . '>' . esc_html( $l ) . '</option>';
				}
				echo '</optgroup>';
			} else {
				echo '<option value="' . esc_attr( $value ) . '" ' . selected( $current, $value, false ) . '>' . esc_html( $label ) . '</option>';
			}
		}
		echo '</select>';
	}

	private static function block_active( $block ) {
		return 'disabled' !== $block['position'] && '' !== trim( $block['code'] );
	}

	/* ---------- Screen ---------- */

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$all = RQ_Ads::all();
		?>
		<div class="wrap rq-ai">
			<h1><?php esc_html_e( 'Ad blocks', 'rev-quiz' ); ?></h1>
			<?php settings_errors( RQ_Ads::OPTION ); ?>
			<?php if ( ! $all['general']['enabled'] ) : ?>
				<div class="notice notice-warning inline"><p><?php esc_html_e( 'Ads are switched off in the General tab, so no block is inserted.', 'rev-quiz' ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'rq_ads' ); ?>

				<nav class="rq-ai-tabs" role="tablist">
					<?php for ( $i = 1; $i <= RQ_Ads::BLOCKS; $i++ ) : ?>
						<?php $b = $all['blocks'][ $i ]; ?>
						<button type="button" role="tab" class="rq-ai-tab <?php echo self::block_active( $b ) ? 'is-active-block' : ''; ?>" data-tab="block-<?php echo (int) $i; ?>" title="<?php echo esc_attr( RQ_Ads::block_name( $i, $b ) ); ?>"><?php echo (int) $i; ?></button>
					<?php endfor; ?>
					<button type="button" role="tab" class="rq-ai-tab rq-ai-tab-wide <?php echo $all['header']['enabled'] && trim( $all['header']['code'] ) ? 'is-active-block' : ''; ?>" data-tab="header"><?php esc_html_e( 'Header', 'rev-quiz' ); ?></button>
					<button type="button" role="tab" class="rq-ai-tab rq-ai-tab-wide <?php echo $all['footer']['enabled'] && trim( $all['footer']['code'] ) ? 'is-active-block' : ''; ?>" data-tab="footer"><?php esc_html_e( 'Footer', 'rev-quiz' ); ?></button>
					<button type="button" role="tab" class="rq-ai-tab rq-ai-tab-wide <?php echo $all['ads_txt']['enabled'] ? 'is-active-block' : ''; ?>" data-tab="ads-txt">ads.txt</button>
					<button type="button" role="tab" class="rq-ai-tab rq-ai-tab-wide" data-tab="general"><?php esc_html_e( 'General', 'rev-quiz' ); ?></button>
				</nav>

				<?php
				for ( $i = 1; $i <= RQ_Ads::BLOCKS; $i++ ) {
					self::block_panel( $i, $all['blocks'][ $i ] );
				}
				self::code_panel( 'header', $all['header'], __( 'Code printed inside <head> on every page it applies to: the AdSense auto-ads script, analytics, pixels, site verification tags.', 'rev-quiz' ) );
				self::code_panel( 'footer', $all['footer'], __( 'Code printed before </body>.', 'rev-quiz' ) );
				self::ads_txt_panel( $all['ads_txt'] );
				self::general_panel( $all['general'] );
				?>

				<?php submit_button( __( 'Save all settings', 'rev-quiz' ) ); ?>
			</form>
		</div>
		<?php
	}

	private static function block_panel( $i, $b ) {
		$p        = '[blocks][' . $i . ']';
		$numbered = in_array( $b['position'], RQ_Ads::numbered_positions(), true );
		?>
		<section class="rq-ai-panel" data-panel="block-<?php echo (int) $i; ?>" hidden>
			<div class="rq-ai-head">
				<strong><?php echo (int) $i; ?></strong>
				<input type="text" class="regular-text" name="<?php echo esc_attr( self::name( $p . '[name]' ) ); ?>" value="<?php echo esc_attr( $b['name'] ); ?>" placeholder="<?php echo esc_attr( RQ_Ads::block_name( $i, RQ_Ads::block_defaults() ) ); ?>">
				<span class="rq-ai-state"><?php echo self::block_active( $b ) ? esc_html__( 'Active', 'rev-quiz' ) : esc_html__( 'Not inserted', 'rev-quiz' ); ?></span>
			</div>

			<textarea class="large-text code rq-ai-code" rows="10" name="<?php echo esc_attr( self::name( $p . '[code]' ) ); ?>" placeholder="<?php esc_attr_e( 'Paste ad code, HTML, JavaScript or shortcodes', 'rev-quiz' ); ?>"><?php echo esc_textarea( $b['code'] ); ?></textarea>

			<div class="rq-ai-row">
				<label><?php esc_html_e( 'Insertion', 'rev-quiz' ); ?>
					<?php self::select( $p . '[position]', $b['position'], RQ_Ads::positions(), 'rq-ai-position' ); ?>
				</label>
				<label class="rq-ai-numbers" <?php echo $numbered ? '' : 'hidden'; ?>><span class="rq-ai-numbers-label"><?php esc_html_e( 'Numbers', 'rev-quiz' ); ?></span>
					<input type="text" class="small-text" name="<?php echo esc_attr( self::name( $p . '[numbers]' ) ); ?>" value="<?php echo esc_attr( $b['numbers'] ); ?>" placeholder="1">
				</label>
				<label><?php esc_html_e( 'Alignment', 'rev-quiz' ); ?>
					<?php self::select( $p . '[align]', $b['align'], RQ_Ads::alignments(), 'rq-ai-align' ); ?>
				</label>
				<label class="rq-ai-css" <?php echo 'custom' === $b['align'] ? '' : 'hidden'; ?>><?php esc_html_e( 'CSS', 'rev-quiz' ); ?>
					<input type="text" class="regular-text code" name="<?php echo esc_attr( self::name( $p . '[css]' ) ); ?>" value="<?php echo esc_attr( $b['css'] ); ?>" placeholder="margin: 10px auto; width: 300px;">
				</label>
			</div>
			<p class="description rq-ai-numbers-help" <?php echo $numbered ? '' : 'hidden'; ?>><?php esc_html_e( 'Which paragraph, image, post or quiz option: a list like 2, 5; "3+" for every one from the 3rd; "%3" for every 3rd. For "after option", a question with fewer options gets the block under the list.', 'rev-quiz' ); ?></p>
			<div class="rq-ai-popup" <?php echo 'popup' === $b['position'] ? '' : 'hidden'; ?>>
				<?php esc_html_e( 'Popup delay (ms)', 'rev-quiz' ); ?>
				<input type="number" min="0" class="small-text" name="<?php echo esc_attr( self::name( $p . '[popup_delay]' ) ); ?>" value="<?php echo esc_attr( $b['popup_delay'] ); ?>">
				<?php self::checkbox( $p . '[popup_unfilled]', (bool) $b['popup_unfilled'], __( 'Close the popup when the ad is not filled', 'rev-quiz' ) ); ?>
			</div>

			<div class="rq-ai-cols">
				<fieldset>
					<legend><?php esc_html_e( 'Display on', 'rev-quiz' ); ?></legend>
					<?php
					foreach ( RQ_Ads::page_types() as $type => $label ) {
						self::checkbox( $p . '[pages][]', in_array( $type, (array) $b['pages'], true ), $label, $type );
						echo '<br>';
					}
					?>
					<label class="rq-ai-sub"><?php esc_html_e( 'Quiz steps', 'rev-quiz' ); ?>
						<?php self::select( $p . '[quiz_steps]', $b['quiz_steps'], RQ_Ads::quiz_steps() ); ?>
					</label>
				</fieldset>

				<fieldset>
					<legend><?php esc_html_e( 'Devices and users', 'rev-quiz' ); ?></legend>
					<?php
					foreach ( array( 'desktop' => __( 'Desktop', 'rev-quiz' ), 'tablet' => __( 'Tablet', 'rev-quiz' ), 'phone' => __( 'Phone', 'rev-quiz' ) ) as $device => $label ) {
						self::checkbox( $p . '[devices][]', in_array( $device, (array) $b['devices'], true ), $label, $device );
						echo '<br>';
					}
					?>
					<label class="rq-ai-sub"><?php esc_html_e( 'Users', 'rev-quiz' ); ?>
						<?php self::select( $p . '[users]', $b['users'], array( 'all' => __( 'All users', 'rev-quiz' ), 'guests' => __( 'Logged out only', 'rev-quiz' ), 'logged_in' => __( 'Logged in only', 'rev-quiz' ) ) ); ?>
					</label>
				</fieldset>

				<fieldset>
					<legend><?php esc_html_e( 'Misc', 'rev-quiz' ); ?></legend>
					<label><?php esc_html_e( 'Max insertions', 'rev-quiz' ); ?> <input type="number" min="0" class="small-text" name="<?php echo esc_attr( self::name( $p . '[max]' ) ); ?>" value="<?php echo esc_attr( $b['max'] ); ?>"></label><br>
					<label><?php esc_html_e( 'Min paragraphs', 'rev-quiz' ); ?> <input type="number" min="0" class="small-text" name="<?php echo esc_attr( self::name( $p . '[min_paragraphs]' ) ); ?>" value="<?php echo esc_attr( $b['min_paragraphs'] ); ?>"></label><br>
					<?php self::checkbox( $p . '[label]', (bool) $b['label'], __( 'Show the ad label', 'rev-quiz' ) ); ?>
				</fieldset>
			</div>

			<details class="rq-ai-lists" <?php echo self::has_lists( $b ) ? 'open' : ''; ?>>
				<summary><?php esc_html_e( 'Lists (white list / black list)', 'rev-quiz' ); ?></summary>
				<table class="form-table" role="presentation">
					<?php foreach ( RQ_Ads::lists() as $list => $label ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $label ); ?></th>
							<td>
								<input type="text" class="large-text" name="<?php echo esc_attr( self::name( $p . '[list_' . $list . ']' ) ); ?>" value="<?php echo esc_attr( $b[ 'list_' . $list ] ); ?>" placeholder="<?php echo esc_attr( self::list_placeholder( $list ) ); ?>">
							</td>
							<td class="rq-ai-mode">
								<?php self::select( $p . '[list_' . $list . '_mode]', $b[ 'list_' . $list . '_mode' ], array( 'black' => __( 'Black list', 'rev-quiz' ), 'white' => __( 'White list', 'rev-quiz' ) ) ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<p class="description"><?php esc_html_e( 'Comma separated. An empty list is ignored. White list: insert only when something matches; black list: never insert when something matches. "*" is a wildcard. Referrer "#" means a direct visit.', 'rev-quiz' ); ?></p>
			</details>

			<details class="rq-ai-manual">
				<summary><?php esc_html_e( 'Manual insertion', 'rev-quiz' ); ?></summary>
				<p><?php self::checkbox( $p . '[manual_shortcode]', (bool) $b['manual_shortcode'], __( 'Shortcode', 'rev-quiz' ) ); ?> <code>[rq_ad block="<?php echo (int) $i; ?>"]</code></p>
				<p><?php self::checkbox( $p . '[manual_widget]', (bool) $b['manual_widget'], __( 'Widget', 'rev-quiz' ) ); ?> <span class="description"><?php esc_html_e( 'Appearance > Widgets > "REW QUIZZ ad block"', 'rev-quiz' ); ?></span></p>
				<p><?php self::checkbox( $p . '[manual_php]', (bool) $b['manual_php'], __( 'PHP function call', 'rev-quiz' ) ); ?> <code>&lt;?php if ( function_exists( 'rq_ad_block' ) ) rq_ad_block( <?php echo (int) $i; ?> ); ?&gt;</code></p>
				<p class="description"><?php esc_html_e( 'Manual insertion ignores "Display on" but still checks devices, users and lists.', 'rev-quiz' ); ?></p>
			</details>
		</section>
		<?php
	}

	private static function has_lists( $b ) {
		foreach ( array_keys( RQ_Ads::lists() ) as $list ) {
			if ( '' !== trim( $b[ 'list_' . $list ] ) ) {
				return true;
			}
		}
		return false;
	}

	private static function list_placeholder( $list ) {
		$examples = array(
			'categories' => 'movies-cinema, sports',
			'tags'       => 'featured',
			'ids'        => '12, 34',
			'urls'       => '/quiz/gaming/*, /about/',
			'params'     => 'utm_source=facebook, fbclid',
			'referrers'  => 'facebook.com, #',
		);
		return isset( $examples[ $list ] ) ? $examples[ $list ] : '';
	}

	private static function code_panel( $section, $s, $help ) {
		$p = '[' . $section . ']';
		?>
		<section class="rq-ai-panel" data-panel="<?php echo esc_attr( $section ); ?>" hidden>
			<p><?php self::checkbox( $p . '[enabled]', (bool) $s['enabled'], __( 'Enabled', 'rev-quiz' ) ); ?></p>
			<textarea class="large-text code rq-ai-code" rows="12" name="<?php echo esc_attr( self::name( $p . '[code]' ) ); ?>"><?php echo esc_textarea( $s['code'] ); ?></textarea>
			<p class="description"><?php echo esc_html( $help ); ?></p>
			<p><?php self::checkbox( $p . '[sitewide]', (bool) $s['sitewide'], __( 'Whole site (otherwise only quiz pages)', 'rev-quiz' ) ); ?></p>
		</section>
		<?php
	}

	private static function ads_txt_panel( $s ) {
		$url = home_url( '/ads.txt' );
		?>
		<section class="rq-ai-panel" data-panel="ads-txt" hidden>
			<p><?php self::checkbox( '[ads_txt][enabled]', (bool) $s['enabled'], __( 'Serve ads.txt from these settings', 'rev-quiz' ) ); ?></p>
			<textarea class="large-text code" rows="10" name="<?php echo esc_attr( self::name( '[ads_txt][content]' ) ); ?>" placeholder="google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0"><?php echo esc_textarea( $s['content'] ); ?></textarea>
			<p class="description">
				<?php
				/* translators: %s: ads.txt URL */
				printf( esc_html__( 'Served at %s. A real ads.txt file in the site root takes priority, and ad networks only read ads.txt at the root of the domain.', 'rev-quiz' ), '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $url ) . '</a>' );
				?>
			</p>
		</section>
		<?php
	}

	private static function general_panel( $g ) {
		$p = '[general]';
		?>
		<section class="rq-ai-panel" data-panel="general" hidden>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Ads', 'rev-quiz' ); ?></th>
					<td>
						<?php self::checkbox( $p . '[enabled]', (bool) $g['enabled'], __( 'Insert ad blocks', 'rev-quiz' ) ); ?><br>
						<?php self::checkbox( $p . '[hide_for_admins]', (bool) $g['hide_for_admins'], __( 'Hide ads from administrators', 'rev-quiz' ) ); ?>
						<p class="description">
							<?php
							/* translators: %s: settings link */
							printf( esc_html__( 'On quiz pages, visitors filtered out by the traffic source filter get no ads unless "Ads for other visitors" is on in %s.', 'rev-quiz' ), '<a href="' . esc_url( RQ_Settings_Page::url() ) . '">' . esc_html__( 'Settings', 'rev-quiz' ) . '</a>' );
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'AdSense publisher ID', 'rev-quiz' ); ?></th>
					<td>
						<input type="text" class="regular-text" name="<?php echo esc_attr( self::name( $p . '[publisher_id]' ) ); ?>" value="<?php echo esc_attr( $g['publisher_id'] ); ?>" placeholder="ca-pub-0000000000000000"><br>
						<?php self::checkbox( $p . '[load_adsense]', (bool) $g['load_adsense'], __( 'Load the AdSense library once in <head> (then blocks only need the <ins> unit)', 'rev-quiz' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Anchor ads', 'rev-quiz' ); ?></th>
					<td>
						<?php self::select( $p . '[anchor_position]', $g['anchor_position'], array( 'account' => __( 'Use AdSense account setting', 'rev-quiz' ), 'bottom' => __( 'Bottom', 'rev-quiz' ), 'collapsed-bottom' => __( 'Collapsed bottom', 'rev-quiz' ) ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Block wrapper', 'rev-quiz' ); ?></th>
					<td>
						<?php esc_html_e( 'CSS class', 'rev-quiz' ); ?> <input type="text" class="regular-text code" name="<?php echo esc_attr( self::name( $p . '[block_class]' ) ); ?>" value="<?php echo esc_attr( $g['block_class'] ); ?>"><br>
						<?php esc_html_e( 'Ad label', 'rev-quiz' ); ?> <input type="text" class="regular-text" name="<?php echo esc_attr( self::name( $p . '[label_text]' ) ); ?>" value="<?php echo esc_attr( $g['label_text'] ); ?>">
						<p class="description"><?php esc_html_e( 'Each block is wrapped in <div class="rq-ad rq-ad-N"> unless its alignment is "No wrapping".', 'rev-quiz' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Rewarded ad', 'rev-quiz' ); ?></th>
					<td>
						<?php self::checkbox( $p . '[rewarded]', (bool) $g['rewarded'], __( 'Show a rewarded ad from the "Redeem rewards" button on the first quiz step', 'rev-quiz' ) ); ?><br>
						<?php esc_html_e( 'Placement name', 'rev-quiz' ); ?> <input type="text" class="regular-text" name="<?php echo esc_attr( self::name( $p . '[rewarded_name]' ) ); ?>" value="<?php echo esc_attr( $g['rewarded_name'] ); ?>"><br>
						<?php self::checkbox( $p . '[rewarded_once]', (bool) $g['rewarded_once'], __( 'Once per browser tab', 'rev-quiz' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Interstitial', 'rev-quiz' ); ?></th>
					<td>
						<?php self::checkbox( $p . '[interstitial]', (bool) $g['interstitial'], __( 'Show an interstitial between quiz steps', 'rev-quiz' ) ); ?><br>
						<?php esc_html_e( 'Placement name', 'rev-quiz' ); ?> <input type="text" class="regular-text" name="<?php echo esc_attr( self::name( $p . '[interstitial_name]' ) ); ?>" value="<?php echo esc_attr( $g['interstitial_name'] ); ?>">
						<?php self::select( $p . '[interstitial_type]', $g['interstitial_type'], array_combine( array( 'next', 'browse', 'start', 'pause' ), array( 'next', 'browse', 'start', 'pause' ) ) ); ?>
						<p class="description"><?php esc_html_e( 'Rewarded and interstitial use the Google H5 Ad Placement API and need the publisher ID above.', 'rev-quiz' ); ?></p>
					</td>
				</tr>
			</table>
		</section>
		<?php
	}
}
