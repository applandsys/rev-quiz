<?php
/**
 * Quiz home (/{base}/) with the category grid, and category pages (/{base}/{category}/).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rq_term = is_tax( RQ_Post_Types::TAXONOMY ) ? get_queried_object() : null;

require RQ_DIR . 'templates/parts/header.php';
?>
<main class="rq-main">
	<header class="rq-hero">
		<?php if ( $rq_term ) : ?>
			<a class="rq-back rq-back-light" href="<?php echo esc_url( get_post_type_archive_link( RQ_Post_Types::POST_TYPE ) ); ?>" aria-label="<?php esc_attr_e( 'All categories', 'rev-quiz' ); ?>">
				<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
			<h1><?php echo esc_html( trim( get_term_meta( $rq_term->term_id, 'rq_icon', true ) . ' ' . $rq_term->name ) ); ?></h1>
			<?php if ( $rq_term->description ) : ?>
				<p><?php echo esc_html( $rq_term->description ); ?></p>
			<?php endif; ?>
		<?php else : ?>
			<h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
			<p><?php esc_html_e( 'Pick a category and start a quiz.', 'rev-quiz' ); ?></p>
		<?php endif; ?>
	</header>

	<?php RQ_Frontend::banner( 300 ); ?>

	<?php if ( ! $rq_term ) : ?>
		<?php
		$rq_cats = get_terms(
			array(
				'taxonomy'   => RQ_Post_Types::TAXONOMY,
				'hide_empty' => false,
				'parent'     => 0,
				'orderby'    => 'term_id',
			)
		);
		?>
		<?php if ( $rq_cats && ! is_wp_error( $rq_cats ) ) : ?>
			<div class="rq-grid">
				<?php foreach ( $rq_cats as $rq_cat ) : ?>
					<?php
					if ( RQ_Post_Types::FALLBACK_CATEGORY === $rq_cat->slug && ! $rq_cat->count ) {
						continue;
					}
					?>
					<?php
					$rq_img   = RQ_Quiz::image_url( get_term_meta( $rq_cat->term_id, 'rq_image', true ) );
					$rq_color = sanitize_hex_color( get_term_meta( $rq_cat->term_id, 'rq_color', true ) );
					?>
					<a class="rq-card" href="<?php echo esc_url( get_term_link( $rq_cat ) ); ?>" <?php echo $rq_color ? 'style="background-color:' . esc_attr( $rq_color ) . '"' : ''; ?>>
						<?php $rq_icon = get_term_meta( $rq_cat->term_id, 'rq_icon', true ); ?>
						<?php if ( $rq_img ) : ?>
							<img src="<?php echo esc_url( $rq_img ); ?>" alt="" width="50" height="50" loading="lazy">
						<?php elseif ( $rq_icon ) : ?>
							<span class="rq-card-icon" aria-hidden="true"><?php echo esc_html( $rq_icon ); ?></span>
						<?php endif; ?>
						<span><?php echo esc_html( $rq_cat->name ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<h2 class="rq-more-title"><?php esc_html_e( 'Latest quizzes', 'rev-quiz' ); ?></h2>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<div class="rq-options">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<a class="rq-option" href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'thumbnail', array( 'alt' => '' ) ); ?>
					<?php endif; ?>
					<span class="rq-option-label">
						<?php the_title(); ?>
						<?php if ( has_excerpt() ) : ?>
							<small><?php echo esc_html( get_the_excerpt() ); ?></small>
						<?php endif; ?>
					</span>
					<?php echo RQ_Frontend::arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?>
				</a>
			<?php endwhile; ?>
		</div>
		<nav class="rq-pagination"><?php echo wp_kses_post( paginate_links( array( 'type' => 'plain' ) ) ); ?></nav>
	<?php else : ?>
		<p class="rq-empty"><?php esc_html_e( 'No quizzes here yet.', 'rev-quiz' ); ?></p>
	<?php endif; ?>

	<?php if ( $rq_term && $rq_term->description ) : ?>
		<article class="rq-article">
			<div class="rq-content"><?php echo wp_kses_post( wpautop( $rq_term->description ) ); ?></div>
		</article>
	<?php endif; ?>
</main>
<?php
require RQ_DIR . 'templates/parts/footer.php';
