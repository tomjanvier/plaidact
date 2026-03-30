<?php
/**
 * Plugin Name: PlaidAct Actualités (Brèves + Timeline + Répertoire ONG)
 * Plugin URI:  https://plaidact.example
 * Description: Plugin unifié PlaidAct pour les brèves, la timeline agenda et le répertoire ONG.
 * Version:     2.0.0
 * Author:      PlaidAct
 * Text Domain: plaidact-breves-feed
 * Domain Path: /languages
 * Requires PHP: 8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PLAIDACT_BREVES_FEED_VERSION', '2.0.0' );
define( 'PLAIDACT_BREVES_FEED_FILE', __FILE__ );
define( 'PLAIDACT_BREVES_FEED_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLAIDACT_BREVES_FEED_URL', plugin_dir_url( __FILE__ ) );

/**
 * Constants conservées pour compatibilité avec le module timeline.
 */
define( 'PLAIDACT_TIMELINE_VERSION', PLAIDACT_BREVES_FEED_VERSION );
define( 'PLAIDACT_TIMELINE_FILE', PLAIDACT_BREVES_FEED_FILE );
define( 'PLAIDACT_TIMELINE_PATH', PLAIDACT_BREVES_FEED_PATH . 'plaidact-agenda-timeline/' );
define( 'PLAIDACT_TIMELINE_URL', PLAIDACT_BREVES_FEED_URL . 'plaidact-agenda-timeline/' );

require_once PLAIDACT_BREVES_FEED_PATH . 'includes/class-plaidact-breves-feed.php';
require_once PLAIDACT_TIMELINE_PATH . 'inc/class-plaidact-agenda-timeline.php';

PlaidAct_Breves_Feed::init();
\PlaidAct\AgendaTimeline\Plugin::init();
