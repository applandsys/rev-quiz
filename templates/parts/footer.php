<?php
/**
 * Shared page shell (bottom).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	<footer class="rq-footer">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
		<?php if ( get_privacy_policy_url() ) : ?>
			&middot; <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Privacy Policy', 'rev-quiz' ); ?></a>
		<?php endif; ?>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
