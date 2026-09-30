<?php
/**
 * Quiz editor meta boxes, category fields and list columns.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RQ_Admin {

	const NONCE = 'rq_quiz_meta';

	public static function init() {
		add_action( 'add_meta_boxes_' . RQ_Post_Types::POST_TYPE, array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . RQ_Post_Types::POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );

		add_action( RQ_Post_Types::TAXONOMY . '_add_form_fields', array( __CLASS__, 'term_fields_add' ) );
		add_action( RQ_Post_Types::TAXONOMY . '_edit_form_fields', array( __CLASS__, 'term_fields_edit' ) );
		add_action( 'created_' . RQ_Post_Types::TAXONOMY, array( __CLASS__, 'term_save' ) );
		add_action( 'edited_' . RQ_Post_Types::TAXONOMY, array( __CLASS__, 'term_save' ) );

		add_filter( 'manage_' . RQ_Post_Types::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . RQ_Post_Types::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
	}

	public static function assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || ( RQ_Post_Types::POST_TYPE !== $screen->post_type ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'rev-quiz-admin', RQ_URL . 'assets/css/rev-quiz-admin.css', array(), RQ_VERSION );
		wp_enqueue_script( 'rev-quiz-admin', RQ_URL . 'assets/js/rev-quiz-admin.js', array( 'jquery', 'jquery-ui-sortable' ), RQ_VERSION, true );
		wp_localize_script(
			'rev-quiz-admin',
			'RevQuizAdmin',
			array(
				'assetsUrl'    => RQ_URL . 'assets/images/',
				'chooseImage'  => __( 'Choose image', 'rev-quiz' ),
				'confirmQuestion' => __( 'Remove this question and its options?', 'rev-quiz' ),
			)
		);
	}

	public static function meta_boxes() {
		add_meta_box( 'rq_questions', __( 'Quiz Questions (one step per question)', 'rev-quiz' ), array( __CLASS__, 'box_questions' ), null, 'normal', 'high' );
		add_meta_box( 'rq_instructions', __( 'Instructions (shown on every quiz step)', 'rev-quiz' ), array( __CLASS__, 'box_instructions' ), null, 'normal', 'high' );
		add_meta_box( 'rq_result', __( 'Result Screen', 'rev-quiz' ), array( __CLASS__, 'box_result' ), null, 'normal', 'default' );
	}

	public static function box_questions( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );
		$questions = RQ_Quiz::questions( $post->ID );
		?>
		<p class="description">
			<?php esc_html_e( 'Each question is its own page (step). Visitors tap an option to move to the next step. The text in the main editor above is shown under the quiz on every step.', 'rev-quiz' ); ?>
		</p>
		<p class="description">
			<?php
			printf(
				/* translators: %s: URL pattern */
				esc_html__( 'Pick the category in the Quiz Categories panel. Its slug becomes part of the address: %s', 'rev-quiz' ),
				'<code>' . esc_html( home_url( RQ_Settings::base_slug() . '/{category}/{quiz}/' ) ) . '</code>'
			);
			if ( 'publish' === $post->post_status ) {
				echo '<br>' . esc_html__( 'Current address:', 'rev-quiz' ) . ' <a href="' . esc_url( get_permalink( $post ) ) . '" target="_blank">' . esc_html( get_permalink( $post ) ) . '</a>';
			}
			?>
		</p>
		<div class="rq-questions" data-next="<?php echo esc_attr( count( $questions ) ); ?>">
			<?php
			foreach ( $questions as $qi => $question ) {
				self::question_row( $qi, $question );
			}
			?>
		</div>
		<p><button type="button" class="button button-primary rq-add-question"><?php esc_html_e( '+ Add question', 'rev-quiz' ); ?></button></p>

		<script type="text/html" id="tmpl-rq-question">
			<?php self::question_row( '__Q__', array() ); ?>
		</script>
		<script type="text/html" id="tmpl-rq-option">
			<?php self::option_row( '__Q__', '__O__', array() ); ?>
		</script>
		<?php
	}

	private static function question_row( $qi, $question ) {
		$question = wp_parse_args(
			$question,
			array(
				'title'         => '',
				'subtitle'      => '',
				'sets_language' => 0,
				'options'       => array(),
			)
		);
		$name = 'rq_questions[' . $qi . ']';
		?>
		<div class="rq-q" data-q="<?php echo esc_attr( $qi ); ?>">
			<div class="rq-q-head">
				<span class="rq-handle dashicons dashicons-move" title="<?php esc_attr_e( 'Drag to reorder', 'rev-quiz' ); ?>"></span>
				<strong class="rq-q-num"></strong>
				<input type="text" class="rq-q-title large-text" name="<?php echo esc_attr( $name ); ?>[title]" value="<?php echo esc_attr( $question['title'] ); ?>" placeholder="<?php esc_attr_e( 'Question, e.g. Select Your Country', 'rev-quiz' ); ?>">
				<button type="button" class="button-link rq-q-toggle" aria-label="<?php esc_attr_e( 'Show or hide', 'rev-quiz' ); ?>"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
				<button type="button" class="button-link button-link-delete rq-q-remove"><?php esc_html_e( 'Remove', 'rev-quiz' ); ?></button>
			</div>
			<div class="rq-q-body">
				<p>
					<input type="text" class="large-text" name="<?php echo esc_attr( $name ); ?>[subtitle]" value="<?php echo esc_attr( $question['subtitle'] ); ?>" placeholder="<?php esc_attr_e( 'Short helper text under the question (optional)', 'rev-quiz' ); ?>">
				</p>
				<p>
					<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[sets_language]" value="1" <?php checked( $question['sets_language'] ); ?>> <?php esc_html_e( 'This question sets the page language (Google Translate). Put the language code, e.g. hi, in each option\'s Value.', 'rev-quiz' ); ?></label>
				</p>
				<table class="widefat rq-options-table">
					<thead><tr><th class="rq-col-handle"></th><th><?php esc_html_e( 'Option', 'rev-quiz' ); ?></th><th><?php esc_html_e( 'Image', 'rev-quiz' ); ?></th><th><?php esc_html_e( 'Value (optional)', 'rev-quiz' ); ?></th><th title="<?php esc_attr_e( 'Tick the right answer to turn the quiz into trivia with a score', 'rev-quiz' ); ?>"><?php esc_html_e( 'Correct', 'rev-quiz' ); ?></th><th></th></tr></thead>
					<tbody class="rq-options" data-next="<?php echo esc_attr( count( $question['options'] ) ); ?>">
						<?php
						foreach ( array_values( $question['options'] ) as $oi => $option ) {
							self::option_row( $qi, $oi, $option );
						}
						?>
					</tbody>
				</table>
				<p><button type="button" class="button rq-add-option"><?php esc_html_e( '+ Add option', 'rev-quiz' ); ?></button></p>
			</div>
		</div>
		<?php
	}

	private static function option_row( $qi, $oi, $option ) {
		$option  = wp_parse_args( $option, array( 'label' => '', 'image' => '', 'value' => '', 'correct' => 0 ) );
		$name    = 'rq_questions[' . $qi . '][options][' . $oi . ']';
		$preview = RQ_Quiz::image_url( $option['image'] );
		?>
		<tr class="rq-o">
			<td class="rq-col-handle"><span class="rq-handle dashicons dashicons-menu"></span></td>
			<td><input type="text" class="widefat" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( $option['label'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. India', 'rev-quiz' ); ?>"></td>
			<td class="rq-image-cell">
				<img class="rq-preview" src="<?php echo esc_url( $preview ); ?>" alt="" <?php echo $preview ? '' : 'hidden'; ?>>
				<input type="text" class="rq-image" name="<?php echo esc_attr( $name ); ?>[image]" value="<?php echo esc_attr( $option['image'] ); ?>" placeholder="https://">
				<button type="button" class="button rq-pick"><?php esc_html_e( 'Choose', 'rev-quiz' ); ?></button>
			</td>
			<td><input type="text" class="widefat" name="<?php echo esc_attr( $name ); ?>[value]" value="<?php echo esc_attr( $option['value'] ); ?>"></td>
			<td class="rq-col-correct"><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[correct]" value="1" <?php checked( $option['correct'] ); ?>></td>
			<td><button type="button" class="button-link button-link-delete rq-o-remove" aria-label="<?php esc_attr_e( 'Remove option', 'rev-quiz' ); ?>"><span class="dashicons dashicons-no-alt"></span></button></td>
		</tr>
		<?php
	}

	public static function box_instructions( $post ) {
		$value = (string) get_post_meta( $post->ID, RQ_Quiz::META_INSTRUCTIONS, true );
		echo '<p class="description">' . esc_html__( 'Leave empty to use the default instructions from REW QUIZZ > Settings.', 'rev-quiz' ) . '</p>';
		wp_editor(
			$value,
			'rq_instructions',
			array(
				'textarea_name' => 'rq_instructions',
				'textarea_rows' => 5,
				'media_buttons' => false,
				'teeny'         => true,
			)
		);
	}

	public static function box_result( $post ) {
		$result = array(
			'title'  => get_post_meta( $post->ID, RQ_Quiz::META_RESULT_TITLE, true ),
			'text'   => get_post_meta( $post->ID, RQ_Quiz::META_RESULT_TEXT, true ),
			'url'    => get_post_meta( $post->ID, RQ_Quiz::META_RESULT_URL, true ),
			'button' => get_post_meta( $post->ID, RQ_Quiz::META_RESULT_BTN, true ),
		);
		$target = RQ_Quiz::result( $post->ID )['target'];
		?>
		<p class="description"><?php esc_html_e( 'Shown after the last question, together with the visitor\'s answers.', 'rev-quiz' ); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="rq_result_title"><?php esc_html_e( 'Heading', 'rev-quiz' ); ?></label></th>
				<td><input type="text" class="large-text" id="rq_result_title" name="rq_result_title" value="<?php echo esc_attr( $result['title'] ); ?>" placeholder="<?php esc_attr_e( 'Thank you! Your answers are saved.', 'rev-quiz' ); ?>"></td>
			</tr>
			<tr>
				<th><label for="rq_result_text"><?php esc_html_e( 'Text', 'rev-quiz' ); ?></label></th>
				<td><textarea class="large-text" rows="4" id="rq_result_text" name="rq_result_text"><?php echo esc_textarea( $result['text'] ); ?></textarea></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Button goes to', 'rev-quiz' ); ?></th>
				<td>
					<label><input type="radio" name="rq_result_target" value="category" <?php checked( $target, 'category' ); ?>> <?php esc_html_e( "This quiz's category page", 'rev-quiz' ); ?></label><br>
					<label><input type="radio" name="rq_result_target" value="all" <?php checked( $target, 'all' ); ?>> <?php esc_html_e( 'All categories (category picker)', 'rev-quiz' ); ?></label><br>
					<label><input type="radio" name="rq_result_target" value="url" <?php checked( $target, 'url' ); ?>> <?php esc_html_e( 'This URL:', 'rev-quiz' ); ?></label>
					<input type="url" class="regular-text" id="rq_result_url" name="rq_result_url" value="<?php echo esc_attr( $result['url'] ); ?>" placeholder="https://">
				</td>
			</tr>
			<tr>
				<th><label for="rq_result_button"><?php esc_html_e( 'Button text', 'rev-quiz' ); ?></label></th>
				<td><input type="text" class="regular-text" id="rq_result_button" name="rq_result_button" value="<?php echo esc_attr( $result['button'] ); ?>" placeholder="<?php esc_attr_e( 'Continue', 'rev-quiz' ); ?>"></td>
			</tr>
		</table>
		<?php
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$questions = isset( $_POST['rq_questions'] ) ? wp_unslash( $_POST['rq_questions'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized below.
		update_post_meta( $post_id, RQ_Quiz::META_QUESTIONS, RQ_Quiz::sanitize_questions( $questions ) );

		$fields = array(
			'rq_instructions'  => array( RQ_Quiz::META_INSTRUCTIONS, 'wp_kses_post' ),
			'rq_result_title'  => array( RQ_Quiz::META_RESULT_TITLE, 'sanitize_text_field' ),
			'rq_result_text'   => array( RQ_Quiz::META_RESULT_TEXT, 'wp_kses_post' ),
			'rq_result_url'    => array( RQ_Quiz::META_RESULT_URL, 'esc_url_raw' ),
			'rq_result_button' => array( RQ_Quiz::META_RESULT_BTN, 'sanitize_text_field' ),
			'rq_result_target' => array( RQ_Quiz::META_RESULT_TARGET, 'sanitize_key' ),
		);
		foreach ( $fields as $field => $spec ) {
			$value = isset( $_POST[ $field ] ) ? call_user_func( $spec[1], wp_unslash( $_POST[ $field ] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			update_post_meta( $post_id, $spec[0], $value );
		}
	}

	/* ---------- Category fields: card image and colour, as on the original category page ---------- */

	public static function term_fields_add() {
		?>
		<div class="form-field">
			<label for="rq_image"><?php esc_html_e( 'Card image URL', 'rev-quiz' ); ?></label>
			<input type="text" id="rq_image" name="rq_image" value="">
		</div>
		<div class="form-field">
			<label for="rq_icon"><?php esc_html_e( 'Card icon (emoji, used when there is no image)', 'rev-quiz' ); ?></label>
			<input type="text" id="rq_icon" name="rq_icon" value="" placeholder="🎬">
		</div>
		<div class="form-field">
			<label for="rq_color"><?php esc_html_e( 'Card colour', 'rev-quiz' ); ?></label>
			<input type="text" id="rq_color" name="rq_color" value="" placeholder="#FDF3DD">
		</div>
		<?php
		wp_nonce_field( 'rq_term', 'rq_term_nonce' );
	}

	public static function term_fields_edit( $term ) {
		?>
		<tr class="form-field">
			<th><label for="rq_image"><?php esc_html_e( 'Card image URL', 'rev-quiz' ); ?></label></th>
			<td><input type="text" id="rq_image" name="rq_image" value="<?php echo esc_attr( get_term_meta( $term->term_id, 'rq_image', true ) ); ?>"></td>
		</tr>
		<tr class="form-field">
			<th><label for="rq_icon"><?php esc_html_e( 'Card icon (emoji)', 'rev-quiz' ); ?></label></th>
			<td><input type="text" id="rq_icon" name="rq_icon" value="<?php echo esc_attr( get_term_meta( $term->term_id, 'rq_icon', true ) ); ?>" placeholder="🎬"></td>
		</tr>
		<tr class="form-field">
			<th><label for="rq_color"><?php esc_html_e( 'Card colour', 'rev-quiz' ); ?></label></th>
			<td>
				<input type="text" id="rq_color" name="rq_color" value="<?php echo esc_attr( get_term_meta( $term->term_id, 'rq_color', true ) ); ?>" placeholder="#FDF3DD">
				<?php wp_nonce_field( 'rq_term', 'rq_term_nonce' ); ?>
			</td>
		</tr>
		<?php
	}

	public static function term_save( $term_id ) {
		if ( ! isset( $_POST['rq_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['rq_term_nonce'] ) ), 'rq_term' ) ) {
			return;
		}
		if ( isset( $_POST['rq_image'] ) ) {
			$image = trim( sanitize_text_field( wp_unslash( $_POST['rq_image'] ) ) );
			update_term_meta( $term_id, 'rq_image', 0 === strpos( $image, '{rq_assets}' ) ? $image : esc_url_raw( $image ) );
		}
		if ( isset( $_POST['rq_icon'] ) ) {
			update_term_meta( $term_id, 'rq_icon', sanitize_text_field( wp_unslash( $_POST['rq_icon'] ) ) );
		}
		if ( isset( $_POST['rq_color'] ) ) {
			update_term_meta( $term_id, 'rq_color', (string) sanitize_hex_color( wp_unslash( $_POST['rq_color'] ) ) );
		}
	}

	/* ---------- List columns ---------- */

	public static function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['rq_steps'] = __( 'Questions', 'rev-quiz' );
			}
		}
		return $new;
	}

	public static function column( $column, $post_id ) {
		if ( 'rq_steps' === $column ) {
			echo (int) count( RQ_Quiz::questions( $post_id ) );
		}
	}
}
