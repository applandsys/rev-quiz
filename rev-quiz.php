<?php
/**
 * Plugin Name:       REW QUIZZ
 * Description:       Step-by-step quizzes with categories in the permalink, per-quiz instruction text, a social traffic-source filter (cloaking) and one place to manage AdSense, OneSignal and custom code.
 * Version:           1.3.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            REW QUIZZ
 * License:           GPL-2.0-or-later
 * Text Domain:       rev-quiz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RQ_VERSION', '1.3.0' );
define( 'RQ_FILE', __FILE__ );
define( 'RQ_DIR', plugin_dir_path( __FILE__ ) );
define( 'RQ_URL', plugin_dir_url( __FILE__ ) );

require_once RQ_DIR . 'includes/class-rq-settings.php';
require_once RQ_DIR . 'includes/class-rq-post-types.php';
require_once RQ_DIR . 'includes/class-rq-cloak.php';
require_once RQ_DIR . 'includes/class-rq-quiz.php';
require_once RQ_DIR . 'includes/class-rq-frontend.php';
require_once RQ_DIR . 'includes/class-rq-installer.php';

if ( is_admin() ) {
	require_once RQ_DIR . 'includes/class-rq-admin.php';
	require_once RQ_DIR . 'includes/class-rq-settings-page.php';
}

RQ_Post_Types::init();
RQ_Frontend::init();
add_action( 'init', array( 'RQ_Installer', 'maybe_upgrade' ), 20 );

if ( is_admin() ) {
	RQ_Admin::init();
	RQ_Settings_Page::init();
}

register_activation_hook( __FILE__, array( 'RQ_Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'RQ_Installer', 'deactivate' ) );
