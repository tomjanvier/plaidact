<?php
/**
 * Plugin Name: PlaidAct Agenda Timeline
 * Description: Compatibilité legacy. Les fonctionnalités sont désormais incluses dans le plugin PlaidAct Actualités.
 * Version: 2.0.0
 * Requires at least: 6.2
 * Requires PHP: 8.1
 * Author: PlaidAct
 * Text Domain: plaidact-timeline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'PLAIDACT_BREVES_FEED_FILE' ) ) {
	return;
}

define( 'PLAIDACT_TIMELINE_VERSION', '2.0.0' );
define( 'PLAIDACT_TIMELINE_FILE', __FILE__ );
define( 'PLAIDACT_TIMELINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLAIDACT_TIMELINE_URL', plugin_dir_url( __FILE__ ) );

require_once PLAIDACT_TIMELINE_PATH . 'inc/class-plaidact-agenda-timeline.php';

\PlaidAct\AgendaTimeline\Plugin::init();
