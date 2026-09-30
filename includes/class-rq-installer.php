<?php
/**
 * Activation and upgrades: seeds the demo categories and quizzes from includes/demo-quizzes.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RQ_Installer {

	/** Post/term meta holding the demo key of seeded content. */
	const SEED_KEY = '_rq_seed';

	/** Option listing every demo item ever created, so edited, renamed or deleted ones are never re-created. */
	const SEEDED_OPTION = 'rq_seeded_items';

	private static function was_seeded( $type, $key ) {
		$items = get_option( self::SEEDED_OPTION, array() );
		return is_array( $items ) && in_array( $type . ':' . $key, $items, true );
	}

	private static function remember( $type, $key ) {
		$items = get_option( self::SEEDED_OPTION, array() );
		$items = is_array( $items ) ? $items : array();
		if ( ! in_array( $type . ':' . $key, $items, true ) ) {
			$items[] = $type . ':' . $key;
			update_option( self::SEEDED_OPTION, $items, false );
		}
	}

	/** A demo category, found by its demo key (survives renaming) or, for older installs, by slug. */
	private static function seeded_term( $slug ) {
		$terms = get_terms(
			array(
				'taxonomy'   => RQ_Post_Types::TAXONOMY,
				'hide_empty' => false,
				'number'     => 1,
				'meta_key'   => self::SEED_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value' => $slug, // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0];
		}
		$term = get_term_by( 'slug', $slug, RQ_Post_Types::TAXONOMY );
		if ( $term ) {
			update_term_meta( $term->term_id, self::SEED_KEY, $slug );
			self::remember( 'term', $slug );
		}
		return $term;
	}
	public static function activate() {
		RQ_Post_Types::register();
		self::seed();
		update_option( 'rq_seeded', RQ_VERSION );
		flush_rewrite_rules( false );
	}

	public static function deactivate() {
		flush_rewrite_rules( false );
	}

	/**
	 * Sites that ran an older version get any new demo content once.
	 */
	public static function maybe_upgrade() {
		if ( version_compare( (string) get_option( 'rq_seeded', '0' ), RQ_VERSION, '>=' ) ) {
			return;
		}
		self::seed();
		update_option( 'rq_seeded', RQ_VERSION );
		update_option( 'rq_flush_rewrite', 1 );
	}

	/**
	 * Creates the "Uncategorized" fallback plus any missing demo categories and quizzes.
	 * Existing ones (matched by slug) are left alone.
	 */
	public static function seed() {
		RQ_Post_Types::default_term_id();
		self::seed_catalog();
	}

	/**
	 * The entertainment catalogue: 10 categories with 5 quizzes each (includes/demo-quizzes.php).
	 */
	public static function seed_catalog() {
		$catalog = require RQ_DIR . 'includes/demo-quizzes.php';

		foreach ( $catalog as $slug => $cat ) {
			$term = self::seeded_term( $slug );
			if ( $term ) {
				$term_id = (int) $term->term_id;
			} elseif ( self::was_seeded( 'term', $slug ) ) {
				// The site owner deleted this demo category; leave it (and its quizzes) out.
				continue;
			} else {
				$new = wp_insert_term( $cat['name'], RQ_Post_Types::TAXONOMY, array( 'slug' => $slug, 'description' => $cat['description'] ) );
				if ( is_wp_error( $new ) ) {
					continue;
				}
				$term_id = (int) $new['term_id'];
				update_term_meta( $term_id, self::SEED_KEY, $slug );
				self::remember( 'term', $slug );
				update_term_meta( $term_id, 'rq_icon', $cat['icon'] );
				update_term_meta( $term_id, 'rq_color', $cat['color'] );
			}

			foreach ( $cat['quizzes'] as $quiz ) {
				$trivia = isset( $quiz['questions'][0][2] );
				$questions = array();
				foreach ( $quiz['questions'] as $q ) {
					$options = array();
					foreach ( $q[1] as $i => $label ) {
						$options[] = array(
							'label'   => $label,
							'image'   => '',
							'value'   => '',
							'correct' => isset( $q[2] ) && $q[2] === $i ? 1 : 0,
						);
					}
					$questions[] = self::question( $q[0], '', $options );
				}

				self::insert_quiz(
					$slug,
					array( $slug => $term_id ),
					array(
						'slug'          => $quiz['slug'],
						'title'         => $quiz['title'],
						'excerpt'       => $quiz['description'],
						'questions'     => $questions,
						'instructions'  => $trivia
							? '<p>Read each question and tap the answer you think is right. You move to the next question straight away, and your score appears at the end.</p><p>There is no time limit, so take your time. You can play again as often as you like.</p>'
							: '<p>This is a just-for-fun personality quiz. There are no right or wrong answers: tap the option that feels most like you and the next question opens automatically.</p><p>Your answers stay in your browser and are shown again on the result screen.</p>',
						'content'       => '<p>' . esc_html( $quiz['description'] ) . '</p><h2>About ' . esc_html( $cat['name'] ) . ' quizzes</h2><p>' . esc_html( $cat['description'] ) . '</p>',
						'result_title'  => $trivia ? 'Quiz complete!' : 'Here are your answers',
						'result_text'   => $trivia ? 'Share your score with friends and see if they can beat it.' : 'Every answer says something about you. Try another quiz from this category next!',
						'result_button' => 'More ' . $cat['name'] . ' quizzes',
					)
				);
			}
		}
	}

	/**
	 * @return int New post ID, or 0 when the quiz already exists or could not be created.
	 */
	private static function insert_quiz( $cat_slug, $term_ids, $quiz ) {
		if ( self::was_seeded( 'quiz', $quiz['slug'] ) ) {
			return 0;
		}
		// Quizzes seeded before 1.3.0 are recognised by their original slug.
		$existing = get_page_by_path( $quiz['slug'], OBJECT, RQ_Post_Types::POST_TYPE );
		if ( $existing ) {
			update_post_meta( $existing->ID, self::SEED_KEY, $quiz['slug'] );
			self::remember( 'quiz', $quiz['slug'] );
			return 0;
		}
		$post_id = wp_insert_post(
			array(
				'post_type'    => RQ_Post_Types::POST_TYPE,
				'post_status'  => 'publish',
				'post_title'   => $quiz['title'],
				'post_name'    => $quiz['slug'],
				'post_excerpt' => $quiz['excerpt'],
				'post_content' => $quiz['content'],
			)
		);
		if ( ! $post_id || is_wp_error( $post_id ) ) {
			return 0;
		}
		update_post_meta( $post_id, self::SEED_KEY, $quiz['slug'] );
		self::remember( 'quiz', $quiz['slug'] );
		if ( isset( $term_ids[ $cat_slug ] ) ) {
			wp_set_object_terms( $post_id, array( (int) $term_ids[ $cat_slug ] ), RQ_Post_Types::TAXONOMY );
		}
		update_post_meta( $post_id, RQ_Quiz::META_QUESTIONS, $quiz['questions'] );
		update_post_meta( $post_id, RQ_Quiz::META_INSTRUCTIONS, $quiz['instructions'] );
		$result_meta = array(
			'result_title'  => RQ_Quiz::META_RESULT_TITLE,
			'result_text'   => RQ_Quiz::META_RESULT_TEXT,
			'result_button' => RQ_Quiz::META_RESULT_BTN,
		);
		foreach ( $result_meta as $key => $meta ) {
			if ( ! empty( $quiz[ $key ] ) ) {
				update_post_meta( $post_id, $meta, $quiz[ $key ] );
			}
		}
		return (int) $post_id;
	}

	private static function question( $title, $subtitle, $options ) {
		return array(
			'title'         => $title,
			'subtitle'      => $subtitle,
			'sets_language' => 0,
			'options'       => $options,
		);
	}
}
