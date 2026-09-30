<?php
/**
 * Single quiz: one question per step, then a result screen.
 * Filtered-out visitors (traffic filter) get the text-only version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rq_post      = get_queried_object();
setup_postdata( $GLOBALS['post'] = $rq_post ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
$rq_questions = RQ_Quiz::questions( $rq_post->ID );
$rq_total     = count( $rq_questions );
$rq_step      = RQ_Frontend::$step;
$rq_term      = RQ_Post_Types::primary_term( $rq_post );
$rq_back      = $rq_step > 1 ? RQ_Post_Types::step_link( $rq_post, $rq_step - 1 ) : ( $rq_term ? get_term_link( $rq_term ) : get_post_type_archive_link( RQ_Post_Types::POST_TYPE ) );
$rq_content   = apply_filters( 'the_content', get_the_content() );

require RQ_DIR . 'templates/parts/header.php';
?>
<main class="rq-main" data-quiz="<?php echo esc_attr( $rq_post->ID ); ?>" data-step="<?php echo esc_attr( $rq_step ); ?>" data-total="<?php echo esc_attr( $rq_total ); ?>" data-page-lang="<?php echo esc_attr( RQ_Settings::get( 'translate_page_lang' ) ); ?>">

	<header class="rq-topbar">
		<?php if ( ! RQ_Frontend::$safe ) : ?>
			<a class="rq-back" href="<?php echo esc_url( $rq_back ); ?>" aria-label="<?php esc_attr_e( 'Back', 'rev-quiz' ); ?>">
				<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
		<?php endif; ?>
		<div class="rq-topbar-title">
			<?php if ( $rq_term ) : ?>
				<a class="rq-crumb" href="<?php echo esc_url( get_term_link( $rq_term ) ); ?>"><?php echo esc_html( $rq_term->name ); ?></a>
			<?php endif; ?>
			<span class="rq-quiz-name"><?php the_title(); ?></span>
		</div>
	</header>

	<?php do_action( 'rq_ad_position', 'before_post' ); ?>
	<?php do_action( 'rq_ad_position', 'quiz_top' ); ?>

	<?php if ( RQ_Frontend::$safe ) : ?>

		<article class="rq-article rq-article-safe">
			<h1 class="rq-title"><?php the_title(); ?></h1>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="rq-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>
			<?php if ( has_excerpt() ) : ?>
				<p class="rq-description"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<div class="rq-instructions"><?php echo wp_kses_post( wpautop( RQ_Quiz::instructions( $rq_post->ID ) ) ); ?></div>
			<div class="rq-content"><?php echo $rq_content; // phpcs:ignore WordPress.Security.EscapeOutput -- filtered post content. ?></div>
		</article>

	<?php else : ?>

		<?php if ( $rq_total ) : ?>
			<div class="rq-progress" role="progressbar" aria-valuemin="1" aria-valuemax="<?php echo esc_attr( $rq_total ); ?>" aria-valuenow="<?php echo esc_attr( min( $rq_step, $rq_total ) ); ?>">
				<div class="rq-progress-bar" style="width: <?php echo esc_attr( round( min( $rq_step - 1, $rq_total ) / $rq_total * 100 ) ); ?>%"></div>
			</div>
			<p class="rq-progress-label">
				<?php
				if ( $rq_step <= $rq_total ) {
					/* translators: 1: current question, 2: number of questions */
					printf( esc_html__( 'Question %1$d of %2$d', 'rev-quiz' ), (int) $rq_step, (int) $rq_total );
				} else {
					esc_html_e( 'Completed', 'rev-quiz' );
				}
				?>
			</p>
		<?php endif; ?>

		<?php if ( has_excerpt() ) : ?>
			<p class="rq-description"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<?php if ( $rq_step <= $rq_total ) : ?>
			<?php $rq_q = $rq_questions[ $rq_step - 1 ]; ?>
			<section class="rq-question">
				<?php do_action( 'rq_ad_position', 'quiz_before_title' ); ?>
				<h1 class="rq-question-title" id="text-container"><?php echo esc_html( $rq_q['title'] ); ?></h1>
				<?php if ( $rq_q['subtitle'] ) : ?>
					<p class="rq-question-sub"><?php echo esc_html( $rq_q['subtitle'] ); ?></p>
				<?php endif; ?>
				<?php do_action( 'rq_ad_position', 'quiz_after_title' ); ?>

				<?php
				if ( 1 === $rq_step ) {
					RQ_Frontend::rewarded_box( '#rq-content' );
				}
				?>

				<details class="rq-instructions" open>
					<summary><?php esc_html_e( 'How this quiz works', 'rev-quiz' ); ?></summary>
					<div><?php echo wp_kses_post( wpautop( RQ_Quiz::instructions( $rq_post->ID ) ) ); ?></div>
				</details>

				<div class="rq-options">
					<?php foreach ( $rq_q['options'] as $rq_i => $rq_opt ) : ?>
						<a class="rq-option" href="<?php echo esc_url( RQ_Post_Types::step_link( $rq_post, $rq_step + 1 ) ); ?>"
							data-label="<?php echo esc_attr( $rq_opt['label'] ); ?>"
							data-value="<?php echo esc_attr( '' !== $rq_opt['value'] ? $rq_opt['value'] : $rq_opt['label'] ); ?>"
							<?php echo $rq_q['sets_language'] && '' !== $rq_opt['value'] ? 'data-lang="' . esc_attr( $rq_opt['value'] ) . '"' : ''; ?>
						<?php echo ! empty( $rq_opt['correct'] ) ? 'data-correct="1"' : ''; ?>>
							<?php if ( $rq_opt['image'] ) : ?>
								<img src="<?php echo esc_url( RQ_Quiz::image_url( $rq_opt['image'] ) ); ?>" alt="" width="50" height="50" loading="lazy">
							<?php endif; ?>
							<span class="rq-option-label"><?php echo esc_html( $rq_opt['label'] ); ?></span>
							<?php echo RQ_Frontend::arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?>
						</a>
						<?php do_action( 'rq_ad_position', 'quiz_after_option', $rq_i + 1 ); ?>
					<?php endforeach; ?>
				</div>
				<?php
				// Blocks set "after option N" land here when the question has fewer options.
				do_action( 'rq_ad_position', 'quiz_after_option', -count( $rq_q['options'] ) );
				do_action( 'rq_ad_position', 'quiz_after_options' );
				?>
			</section>

		<?php else : ?>
			<?php $rq_result = RQ_Quiz::result( $rq_post->ID ); ?>
			<section class="rq-result">
				<?php do_action( 'rq_ad_position', 'quiz_result_top' ); ?>
				<h1 class="rq-question-title" id="text-container"><?php echo esc_html( $rq_result['title'] ); ?></h1>
				<?php $rq_scored = RQ_Quiz::scored_count( $rq_questions ); ?>
				<?php if ( $rq_scored ) : ?>
					<p class="rq-score" data-scored="<?php echo esc_attr( $rq_scored ); ?>" hidden>
						<?php
						/* translators: 1: correct answers, 2: scored questions */
						printf( esc_html__( 'You scored %1$s out of %2$s', 'rev-quiz' ), '<strong class="rq-score-value">0</strong>', '<strong>' . (int) $rq_scored . '</strong>' );
						?>
					</p>
				<?php endif; ?>
				<?php if ( $rq_result['text'] ) : ?>
					<div class="rq-result-text"><?php echo wp_kses_post( wpautop( $rq_result['text'] ) ); ?></div>
				<?php endif; ?>

				<div class="rq-answers" hidden>
					<h2><?php esc_html_e( 'Your answers', 'rev-quiz' ); ?></h2>
					<ul class="rq-answers-list">
						<?php foreach ( $rq_questions as $rq_i => $rq_q ) : ?>
							<li data-step="<?php echo esc_attr( $rq_i + 1 ); ?>"><span><?php echo esc_html( $rq_q['title'] ); ?></span> <strong></strong></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<?php
				$rq_next = RQ_Quiz::result_link( $rq_post, $rq_result );
				?>
				<a class="rq-button" href="<?php echo esc_url( $rq_next ); ?>"><?php echo esc_html( $rq_result['button'] ); ?></a>
				<a class="rq-restart" href="<?php echo esc_url( get_permalink( $rq_post ) ); ?>"><?php esc_html_e( 'Start again', 'rev-quiz' ); ?></a>

				<?php do_action( 'rq_ad_position', 'quiz_result_buttons' ); ?>

				<?php
				$rq_more = get_posts(
					array(
						'post_type'      => RQ_Post_Types::POST_TYPE,
						'posts_per_page' => 4,
						'post__not_in'   => array( $rq_post->ID ),
						'tax_query'      => $rq_term ? array( array( 'taxonomy' => RQ_Post_Types::TAXONOMY, 'terms' => $rq_term->term_id ) ) : array(), // phpcs:ignore WordPress.DB.SlowDBQuery
					)
				);
				if ( $rq_more ) :
					?>
					<h2 class="rq-more-title"><?php esc_html_e( 'More quizzes', 'rev-quiz' ); ?></h2>
					<div class="rq-options">
						<?php foreach ( $rq_more as $rq_m ) : ?>
							<a class="rq-option rq-option-plain" href="<?php echo esc_url( get_permalink( $rq_m ) ); ?>">
								<span class="rq-option-label"><?php echo esc_html( get_the_title( $rq_m ) ); ?></span>
								<?php echo RQ_Frontend::arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<article class="rq-article" id="rq-content">
			<?php if ( $rq_content ) : ?>
				<div class="rq-content"><?php echo $rq_content; // phpcs:ignore WordPress.Security.EscapeOutput -- filtered post content. ?></div>
			<?php endif; ?>
		</article>

	<?php endif; ?>

	<?php do_action( 'rq_ad_position', 'after_post' ); ?>
	<?php do_action( 'rq_ad_position', 'quiz_bottom' ); ?>
</main>
<?php
require RQ_DIR . 'templates/parts/footer.php';
