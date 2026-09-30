<?php
/**
 * REW QUIZZ > Settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RQ_Settings_Page {

	const SLUG = 'rev-quiz-settings';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( RQ_FILE ), array( __CLASS__, 'action_links' ) );
	}

	public static function url() {
		return admin_url( 'edit.php?post_type=' . RQ_Post_Types::POST_TYPE . '&page=' . self::SLUG );
	}

	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( self::url() ) . '">' . esc_html__( 'Settings', 'rev-quiz' ) . '</a>' );
		return $links;
	}

	public static function menu() {
		add_submenu_page(
			'edit.php?post_type=' . RQ_Post_Types::POST_TYPE,
			__( 'REW QUIZZ Settings', 'rev-quiz' ),
			__( 'Settings', 'rev-quiz' ),
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'render' )
		);
	}

	public static function register() {
		register_setting(
			'rev_quiz',
			RQ_Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => RQ_Settings::defaults(),
			)
		);
	}

	private static function checkboxes() {
		return array( 'admin_bypass', 'ads_for_blocked', 'rewarded_box', 'translate_enabled' );
	}

	public static function sanitize( $input ) {
		$old   = RQ_Settings::all();
		$input = is_array( $input ) ? $input : array();
		$out   = RQ_Settings::defaults();

		$valid_sources          = array_keys( RQ_Settings::sources() );
		$out['traffic_sources'] = array_values( array_intersect( $valid_sources, isset( $input['traffic_sources'] ) ? (array) $input['traffic_sources'] : array() ) );
		$out['custom_sources']  = isset( $input['custom_sources'] ) ? sanitize_textarea_field( $input['custom_sources'] ) : '';
		$out['fallback_mode']   = isset( $input['fallback_mode'] ) && 'redirect' === $input['fallback_mode'] ? 'redirect' : 'content';
		$out['fallback_url']    = isset( $input['fallback_url'] ) ? esc_url_raw( $input['fallback_url'] ) : '';
		$out['remember_hours']  = isset( $input['remember_hours'] ) ? max( 1, absint( $input['remember_hours'] ) ) : 24;

		foreach ( self::checkboxes() as $key ) {
			$out[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
		}

		$out['base_slug'] = isset( $input['base_slug'] ) ? sanitize_title( $input['base_slug'] ) : 'quiz';
		if ( '' === $out['base_slug'] ) {
			$out['base_slug'] = 'quiz';
		}

		$out['default_instructions'] = isset( $input['default_instructions'] ) ? wp_kses_post( $input['default_instructions'] ) : '';
		foreach ( array( 'rewarded_text', 'rewarded_button', 'translate_page_lang', 'translate_languages' ) as $key ) {
			$out[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
		}
		$out['onesignal_app_id'] = isset( $input['onesignal_app_id'] ) ? preg_replace( '/[^a-f0-9-]/i', '', $input['onesignal_app_id'] ) : '';

		if ( $out['base_slug'] !== $old['base_slug'] ) {
			update_option( 'rq_flush_rewrite', 1 );
		}
		RQ_Settings::flush_cache();
		return $out;
	}

	/* ---------- Field renderers ---------- */

	private static function name( $key ) {
		return RQ_Settings::OPTION . '[' . $key . ']';
	}

	private static function text( $key, $class = 'regular-text', $type = 'text', $attrs = '' ) {
		printf(
			'<input type="%s" class="%s" id="rq-%s" name="%s" value="%s" %s>',
			esc_attr( $type ),
			esc_attr( $class ),
			esc_attr( $key ),
			esc_attr( self::name( $key ) ),
			esc_attr( RQ_Settings::get( $key ) ),
			$attrs // phpcs:ignore WordPress.Security.EscapeOutput -- static attributes.
		);
	}

	private static function checkbox( $key, $label ) {
		printf(
			'<label><input type="checkbox" id="rq-%s" name="%s" value="1" %s> %s</label>',
			esc_attr( $key ),
			esc_attr( self::name( $key ) ),
			checked( RQ_Settings::get( $key ), 1, false ),
			esc_html( $label )
		);
	}

	private static function textarea( $key, $rows = 5, $class = 'large-text code' ) {
		printf(
			'<textarea class="%s" rows="%d" id="rq-%s" name="%s">%s</textarea>',
			esc_attr( $class ),
			(int) $rows,
			esc_attr( $key ),
			esc_attr( self::name( $key ) ),
			esc_textarea( RQ_Settings::get( $key ) )
		);
	}

	private static function row( $label, $key, $callback, $help = '' ) {
		echo '<tr><th scope="row"><label for="rq-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
		call_user_func( $callback );
		if ( $help ) {
			echo '<p class="description">' . wp_kses_post( $help ) . '</p>';
		}
		echo '</td></tr>';
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$selected = (array) RQ_Settings::get( 'traffic_sources' );
		$filter   = RQ_Cloak::is_filter_active();
		$sample   = get_posts( array( 'post_type' => RQ_Post_Types::POST_TYPE, 'posts_per_page' => 1 ) );
		?>
		<div class="wrap rq-settings">
			<h1><?php esc_html_e( 'REW QUIZZ Settings', 'rev-quiz' ); ?></h1>
			<?php settings_errors( RQ_Settings::OPTION ); ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'rev_quiz' ); ?>

				<h2><?php esc_html_e( 'Traffic source filter (cloaking)', 'rev-quiz' ); ?></h2>
				<p class="rq-status <?php echo $filter ? '' : 'is-open'; ?>">
					<?php
					echo $filter
						? esc_html__( 'On: only visitors referred from the selected sources see the quiz. Everyone else gets the fallback below.', 'rev-quiz' )
						: esc_html__( 'Off: no source is selected, so every visitor sees the quiz.', 'rev-quiz' );
					?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Allowed social networks', 'rev-quiz' ); ?></th>
						<td>
							<div class="rq-source-tools">
								<button type="button" class="button-link" onclick="document.querySelectorAll('.rq-sources input').forEach(function(i){i.checked=true})"><?php esc_html_e( 'Select all', 'rev-quiz' ); ?></button> |
								<button type="button" class="button-link" onclick="document.querySelectorAll('.rq-sources input').forEach(function(i){i.checked=false})"><?php esc_html_e( 'Select none', 'rev-quiz' ); ?></button>
							</div>
							<div class="rq-sources">
								<?php foreach ( RQ_Settings::sources() as $key => $source ) : ?>
									<label title="<?php echo esc_attr( implode( ', ', $source['domains'] ) ); ?>">
										<input type="checkbox" name="<?php echo esc_attr( self::name( 'traffic_sources' ) ); ?>[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $selected, true ) ); ?>>
										<?php echo esc_html( $source['label'] ); ?>
									</label>
								<?php endforeach; ?>
							</div>
							<p class="description"><?php esc_html_e( 'Leave everything unchecked to show the quiz to all visitors. A visitor matches when the referring site, the in-app browser (Facebook, Instagram, TikTok ...), a utm_source value or the network\'s click ID (fbclid, ttclid ...) belongs to a selected network.', 'rev-quiz' ); ?></p>
						</td>
					</tr>
					<?php
					self::row(
						__( 'Other allowed sources', 'rev-quiz' ),
						'custom_sources',
						function () {
							self::textarea( 'custom_sources', 3, 'large-text' );
						},
						__( 'Extra referrer domains, one per line (e.g. news.example.com). The name also works as a utm_source value.', 'rev-quiz' )
					);
					?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Other visitors', 'rev-quiz' ); ?></th>
						<td>
							<fieldset>
								<label><input type="radio" name="<?php echo esc_attr( self::name( 'fallback_mode' ) ); ?>" value="content" <?php checked( RQ_Settings::get( 'fallback_mode' ), 'content' ); ?>> <?php esc_html_e( 'Show a text-only page (title, instructions and content, no questions)', 'rev-quiz' ); ?></label><br>
								<label><input type="radio" name="<?php echo esc_attr( self::name( 'fallback_mode' ) ); ?>" value="redirect" <?php checked( RQ_Settings::get( 'fallback_mode' ), 'redirect' ); ?>> <?php esc_html_e( 'Redirect to this URL:', 'rev-quiz' ); ?></label>
								<?php self::text( 'fallback_url', 'regular-text', 'url', 'placeholder="https://"' ); ?>
							</fieldset>
						</td>
					</tr>
					<?php
					self::row(
						__( 'Remember allowed visitors', 'rev-quiz' ),
						'remember_hours',
						function () {
							self::text( 'remember_hours', 'small-text', 'number', 'min="1"' );
							echo ' ' . esc_html__( 'hours', 'rev-quiz' );
						},
						__( 'Later quiz steps no longer carry the social referrer, so an allowed visitor is remembered with a cookie.', 'rev-quiz' )
					);
					self::row(
						__( 'Editors', 'rev-quiz' ),
						'admin_bypass',
						function () {
							self::checkbox( 'admin_bypass', __( 'Logged-in editors and admins always see the quiz (for previewing)', 'rev-quiz' ) );
						}
					);
					self::row(
						__( 'Ads for other visitors', 'rev-quiz' ),
						'ads_for_blocked',
						function () {
							self::checkbox( 'ads_for_blocked', __( 'Also load ad code for visitors who do not match a source', 'rev-quiz' ) );
						}
					);
					?>
				</table>

				<h2><?php esc_html_e( 'Permalinks', 'rev-quiz' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::row(
						__( 'Quiz base', 'rev-quiz' ),
						'base_slug',
						function () {
							echo '<code>' . esc_html( home_url( '/' ) ) . '</code>';
							self::text( 'base_slug', 'regular-text' );
						},
						sprintf(
							/* translators: %s: example URL */
							__( 'Quiz URLs include the category slug: %s. Each next question adds /2/, /3/ and so on.', 'rev-quiz' ),
							'<code>' . esc_html( home_url( RQ_Settings::base_slug() . '/{category}/{quiz}/' ) ) . '</code>'
						)
					);
					?>
				</table>

				<h2><?php esc_html_e( 'Quiz page content', 'rev-quiz' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="rq_default_instructions"><?php esc_html_e( 'Default instructions', 'rev-quiz' ); ?></label></th>
						<td>
							<?php
							wp_editor(
								RQ_Settings::get( 'default_instructions' ),
								'rq_default_instructions',
								array(
									'textarea_name' => self::name( 'default_instructions' ),
									'textarea_rows' => 5,
									'media_buttons' => false,
									'teeny'         => true,
								)
							);
							?>
							<p class="description"><?php esc_html_e( 'Shown on every quiz page that has no instructions of its own.', 'rev-quiz' ); ?></p>
						</td>
					</tr>
					<?php
					self::row(
						__( 'Rewarded box', 'rev-quiz' ),
						'rewarded_box',
						function () {
							self::checkbox( 'rewarded_box', __( 'Show the "Redeem rewards" box on the first step', 'rev-quiz' ) );
							echo '<br><br>';
							self::text( 'rewarded_text', 'regular-text' );
							echo ' ';
							self::text( 'rewarded_button', 'small-text' );
						}
					);
					?>
				</table>

				<h2><?php esc_html_e( 'Ads and custom code', 'rev-quiz' ); ?></h2>
				<p>
					<?php
					/* translators: %s: link to the Ad blocks screen */
					printf( esc_html__( 'Ad code, AdSense, rewarded and interstitial ads, header and footer code and ads.txt are managed in %s, which works like the Ad Inserter plugin.', 'rev-quiz' ), '<a href="' . esc_url( RQ_Ads_Page::url() ) . '">' . esc_html__( 'Ad blocks', 'rev-quiz' ) . '</a>' );
					?>
				</p>

				<h2><?php esc_html_e( 'Push notifications and translation', 'rev-quiz' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::row( __( 'OneSignal App ID', 'rev-quiz' ), 'onesignal_app_id', function () { self::text( 'onesignal_app_id', 'regular-text', 'text', 'placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"' ); } );
					self::row(
						__( 'Google Translate', 'rev-quiz' ),
						'translate_enabled',
						function () {
							self::checkbox( 'translate_enabled', __( 'Translate quiz pages into the language picked in a "sets the page language" question', 'rev-quiz' ) );
							echo '<br><br>';
							esc_html_e( 'Page language', 'rev-quiz' );
							echo ' ';
							self::text( 'translate_page_lang', 'small-text' );
							echo ' &nbsp; ';
							esc_html_e( 'Languages', 'rev-quiz' );
							echo ' ';
							self::text( 'translate_languages', 'regular-text' );
						}
					);
					?>
				</table>

				<?php submit_button(); ?>
			</form>

			<?php if ( $sample ) : ?>
				<p><?php esc_html_e( 'Tip: to test the filter, log out or use a private window and open a quiz with ?utm_source=facebook (allowed) and without it (not allowed).', 'rev-quiz' ); ?>
					<a href="<?php echo esc_url( get_permalink( $sample[0] ) ); ?>" target="_blank"><?php esc_html_e( 'Open a quiz', 'rev-quiz' ); ?></a></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
