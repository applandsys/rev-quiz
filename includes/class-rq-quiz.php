<?php
/**
 * Read/write access to a quiz's questions and page texts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RQ_Quiz {

	const META_QUESTIONS    = '_rq_questions';
	const META_INSTRUCTIONS = '_rq_instructions';
	const META_RESULT_TITLE = '_rq_result_title';
	const META_RESULT_TEXT  = '_rq_result_text';
	const META_RESULT_URL   = '_rq_result_url';
	const META_RESULT_BTN   = '_rq_result_button';
	const META_RESULT_TARGET = '_rq_result_target';

	/**
	 * Image fields may use {rq_assets} so bundled images survive a site move.
	 */
	public static function image_url( $value ) {
		$value = (string) $value;
		if ( '' === $value ) {
			return '';
		}
		if ( 0 === strpos( $value, '{rq_assets}' ) ) {
			$file = substr( $value, strlen( '{rq_assets}' ) );
			return RQ_URL . 'assets/images/' . str_replace( '%2F', '/', rawurlencode( ltrim( $file, '/' ) ) );
		}
		return $value;
	}

	/**
	 * @return array[] Each: title, subtitle, sets_language, options[label, image, value, correct].
	 */
	public static function questions( $post_id ) {
		$questions = get_post_meta( $post_id, self::META_QUESTIONS, true );
		return is_array( $questions ) ? array_values( $questions ) : array();
	}

	public static function sanitize_questions( $raw ) {
		$clean = array();
		if ( ! is_array( $raw ) ) {
			return $clean;
		}
		foreach ( $raw as $question ) {
			$title = isset( $question['title'] ) ? sanitize_text_field( $question['title'] ) : '';
			if ( '' === $title ) {
				continue;
			}
			$options = array();
			if ( ! empty( $question['options'] ) && is_array( $question['options'] ) ) {
				foreach ( $question['options'] as $option ) {
					$label = isset( $option['label'] ) ? sanitize_text_field( $option['label'] ) : '';
					if ( '' === $label ) {
						continue;
					}
					$image = isset( $option['image'] ) ? trim( $option['image'] ) : '';
					if ( 0 !== strpos( $image, '{rq_assets}' ) ) {
						$image = esc_url_raw( $image );
					} else {
						$image = sanitize_text_field( $image );
					}
					$options[] = array(
						'label' => $label,
						'image' => $image,
						'value' => isset( $option['value'] ) ? sanitize_text_field( $option['value'] ) : '',
						'correct' => empty( $option['correct'] ) ? 0 : 1,
					);
				}
			}
			$clean[] = array(
				'title'         => $title,
				'subtitle'      => isset( $question['subtitle'] ) ? sanitize_text_field( $question['subtitle'] ) : '',
				'sets_language' => empty( $question['sets_language'] ) ? 0 : 1,
				'options'       => $options,
			);
		}
		return $clean;
	}

	/**
	 * Where the result button goes: the custom URL, all categories, or the quiz's own category.
	 */
	public static function result_link( $post, $result ) {
		if ( 'url' === $result['target'] && $result['url'] ) {
			return $result['url'];
		}
		if ( 'all' === $result['target'] ) {
			return get_post_type_archive_link( RQ_Post_Types::POST_TYPE );
		}
		$term = RQ_Post_Types::primary_term( $post );
		return $term ? get_term_link( $term ) : get_post_type_archive_link( RQ_Post_Types::POST_TYPE );
	}

	/** Number of questions that have a correct answer (trivia questions). */
	public static function scored_count( $questions ) {
		$count = 0;
		foreach ( $questions as $question ) {
			foreach ( $question['options'] as $option ) {
				if ( ! empty( $option['correct'] ) ) {
					$count++;
					break;
				}
			}
		}
		return $count;
	}

	public static function instructions( $post_id ) {
		$text = (string) get_post_meta( $post_id, self::META_INSTRUCTIONS, true );
		if ( '' === trim( wp_strip_all_tags( $text ) ) ) {
			$text = (string) RQ_Settings::get( 'default_instructions' );
		}
		return $text;
	}

	public static function result( $post_id ) {
		$title = (string) get_post_meta( $post_id, self::META_RESULT_TITLE, true );
		$text  = (string) get_post_meta( $post_id, self::META_RESULT_TEXT, true );
		$btn   = (string) get_post_meta( $post_id, self::META_RESULT_BTN, true );
		$url   = (string) get_post_meta( $post_id, self::META_RESULT_URL, true );
		$target = (string) get_post_meta( $post_id, self::META_RESULT_TARGET, true );
		if ( ! in_array( $target, array( 'category', 'all', 'url' ), true ) ) {
			$target = $url ? 'url' : 'category';
		}
		return array(
			'title'  => '' !== $title ? $title : __( 'Thank you! Your answers are saved.', 'rev-quiz' ),
			'text'   => $text,
			'url'    => $url,
			'target' => $target,
			'button' => '' !== $btn ? $btn : __( 'Continue', 'rev-quiz' ),
		);
	}
}
