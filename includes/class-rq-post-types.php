<?php
/**
 * Quiz post type, Quiz Category taxonomy and the /{base}/{category}/{quiz}/{step}/ permalinks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RQ_Post_Types {

	const POST_TYPE = 'rev_quiz';
	const TAXONOMY  = 'rev_quiz_category';
	const FALLBACK_CATEGORY = 'uncategorized';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'init', array( __CLASS__, 'maybe_flush' ), 99 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_filter( 'post_type_link', array( __CLASS__, 'post_link' ), 10, 2 );
		add_filter( 'term_link', array( __CLASS__, 'term_link' ), 10, 3 );
		add_filter( 'post_type_archive_link', array( __CLASS__, 'archive_link' ), 10, 2 );
		add_filter( 'redirect_canonical', array( __CLASS__, 'redirect_canonical' ), 10, 2 );
		add_action( 'wp_after_insert_post', array( __CLASS__, 'ensure_category' ), 10, 2 );
	}

	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => array(
					'name'               => __( 'Quizzes', 'rev-quiz' ),
					'singular_name'      => __( 'Quiz', 'rev-quiz' ),
					'menu_name'          => __( 'Rev Quiz', 'rev-quiz' ),
					'add_new'            => __( 'Add Quiz', 'rev-quiz' ),
					'add_new_item'       => __( 'Add New Quiz', 'rev-quiz' ),
					'edit_item'          => __( 'Edit Quiz', 'rev-quiz' ),
					'new_item'           => __( 'New Quiz', 'rev-quiz' ),
					'view_item'          => __( 'View Quiz', 'rev-quiz' ),
					'all_items'          => __( 'All Quizzes', 'rev-quiz' ),
					'search_items'       => __( 'Search Quizzes', 'rev-quiz' ),
					'not_found'          => __( 'No quizzes found.', 'rev-quiz' ),
					'not_found_in_trash' => __( 'No quizzes found in Trash.', 'rev-quiz' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-forms',
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'has_archive'  => true,
				'rewrite'      => false,
				'query_var'    => self::POST_TYPE,
			)
		);

		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Quiz Categories', 'rev-quiz' ),
					'singular_name' => __( 'Quiz Category', 'rev-quiz' ),
					'menu_name'     => __( 'Categories', 'rev-quiz' ),
					'add_new_item'  => __( 'Add New Category', 'rev-quiz' ),
					'edit_item'     => __( 'Edit Category', 'rev-quiz' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => false,
				'query_var'         => self::TAXONOMY,
			)
		);

		self::add_rules();
	}

	public static function add_rules() {
		$base = preg_quote( RQ_Settings::base_slug(), '#' );

		add_rewrite_rule( "^{$base}/?$", 'index.php?post_type=' . self::POST_TYPE, 'top' );
		add_rewrite_rule( "^{$base}/page/([0-9]+)/?$", 'index.php?post_type=' . self::POST_TYPE . '&paged=$matches[1]', 'top' );
		add_rewrite_rule( "^{$base}/([^/]+)/page/([0-9]+)/?$", 'index.php?' . self::TAXONOMY . '=$matches[1]&paged=$matches[2]', 'top' );
		add_rewrite_rule( "^{$base}/([^/]+)/([^/]+)/([0-9]+)/?$", 'index.php?' . self::POST_TYPE . '=$matches[2]&rq_cat=$matches[1]&rq_step=$matches[3]', 'top' );
		add_rewrite_rule( "^{$base}/([^/]+)/([^/]+)/?$", 'index.php?' . self::POST_TYPE . '=$matches[2]&rq_cat=$matches[1]', 'top' );
		add_rewrite_rule( "^{$base}/([^/]+)/?$", 'index.php?' . self::TAXONOMY . '=$matches[1]', 'top' );
	}

	/**
	 * Rules are flushed once after activation or a base slug change.
	 */
	public static function maybe_flush() {
		if ( get_option( 'rq_flush_rewrite' ) ) {
			delete_option( 'rq_flush_rewrite' );
			flush_rewrite_rules( false );
		}
	}

	public static function query_vars( $vars ) {
		$vars[] = 'rq_step';
		$vars[] = 'rq_cat';
		return $vars;
	}

	/**
	 * The category shown in a quiz URL: the lowest-level first assigned term.
	 */
	public static function primary_term( $post ) {
		$terms = get_the_terms( $post, self::TAXONOMY );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return null;
		}
		usort(
			$terms,
			function ( $a, $b ) {
				return $a->term_id - $b->term_id;
			}
		);
		return $terms[0];
	}

	public static function category_slug( $post ) {
		$term = self::primary_term( $post );
		return $term ? $term->slug : self::FALLBACK_CATEGORY;
	}

	/**
	 * The "Uncategorized" quiz category, created on demand.
	 */
	public static function default_term_id() {
		$term = get_term_by( 'slug', self::FALLBACK_CATEGORY, self::TAXONOMY );
		if ( $term ) {
			return (int) $term->term_id;
		}
		$new = wp_insert_term( __( 'Uncategorized', 'rev-quiz' ), self::TAXONOMY, array( 'slug' => self::FALLBACK_CATEGORY ) );
		return is_wp_error( $new ) ? 0 : (int) $new['term_id'];
	}

	/**
	 * A quiz saved without a category gets "Uncategorized", so its URL always
	 * contains a real category slug. Runs after the block editor has saved terms.
	 */
	public static function ensure_category( $post_id, $post ) {
		if ( self::POST_TYPE !== $post->post_type || wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) {
			return;
		}
		$terms = wp_get_object_terms( $post_id, self::TAXONOMY, array( 'fields' => 'ids' ) );
		if ( empty( $terms ) && ! is_wp_error( $terms ) ) {
			$default = self::default_term_id();
			if ( $default ) {
				wp_set_object_terms( $post_id, array( $default ), self::TAXONOMY );
			}
		}
	}

	public static function post_link( $link, $post ) {
		if ( self::POST_TYPE !== $post->post_type ) {
			return $link;
		}
		if ( ! get_option( 'permalink_structure' ) || in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ), true ) && ! $post->post_name ) {
			return $link;
		}
		$slug = $post->post_name ? $post->post_name : sanitize_title( $post->post_title );
		return home_url( user_trailingslashit( RQ_Settings::base_slug() . '/' . self::category_slug( $post ) . '/' . $slug ) );
	}

	public static function step_link( $post, $step ) {
		$link = get_permalink( $post );
		if ( $step <= 1 ) {
			return $link;
		}
		if ( ! get_option( 'permalink_structure' ) ) {
			return add_query_arg( 'rq_step', (int) $step, $link );
		}
		return trailingslashit( $link ) . user_trailingslashit( (string) (int) $step );
	}

	public static function term_link( $link, $term, $taxonomy ) {
		if ( self::TAXONOMY !== $taxonomy || ! get_option( 'permalink_structure' ) ) {
			return $link;
		}
		return home_url( user_trailingslashit( RQ_Settings::base_slug() . '/' . $term->slug ) );
	}

	public static function archive_link( $link, $post_type ) {
		if ( self::POST_TYPE !== $post_type || ! get_option( 'permalink_structure' ) ) {
			return $link;
		}
		return home_url( user_trailingslashit( RQ_Settings::base_slug() ) );
	}

	/**
	 * WordPress would otherwise "correct" step URLs back to the first page.
	 */
	public static function redirect_canonical( $redirect, $requested ) {
		if ( get_query_var( 'rq_step' ) && is_singular( self::POST_TYPE ) ) {
			return false;
		}
		return $redirect;
	}
}
