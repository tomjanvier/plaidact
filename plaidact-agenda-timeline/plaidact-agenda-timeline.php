<?php
/**
 * Plugin Name: PlaidAct Agenda Timeline
 * Description: Timeline Agenda + Répertoire ONG (CPT, taxonomies, templates, filtres GET, ACF JSON).
 * Version: 1.2.0
 * Requires at least: 6.2
 * Requires PHP: 8.1
 * Author: PlaidAct
 * Text Domain: plaidact-timeline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PLAIDACT_TIMELINE_VERSION', '1.2.0' );
define( 'PLAIDACT_TIMELINE_FILE', __FILE__ );
define( 'PLAIDACT_TIMELINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLAIDACT_TIMELINE_URL', plugin_dir_url( __FILE__ ) );

require_once PLAIDACT_TIMELINE_PATH . 'inc/class-plaidact-agenda-timeline.php';

\PlaidAct\AgendaTimeline\Plugin::init();
