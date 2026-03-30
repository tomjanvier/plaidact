<?php
/**
 * Plugin Name: PlaidAct Actualités (Brèves + Timeline + Répertoire Asso)
 * Plugin URI:  https://plaidact.example
 * Description: Plugin unifié PlaidAct pour les brèves, la timeline agenda et le répertoire des associations.
 * Version:     3.0.0
 * Author:      PlaidAct
 * Text Domain: plaidact-breves-feed
 * Domain Path: /languages
 * Requires PHP: 8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PLAIDACT_BREVES_FEED_VERSION', '3.0.0' );
define( 'PLAIDACT_BREVES_FEED_FILE', __FILE__ );
define( 'PLAIDACT_BREVES_FEED_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLAIDACT_BREVES_FEED_URL', plugin_dir_url( __FILE__ ) );

require_once PLAIDACT_BREVES_FEED_PATH . 'includes/class-plaidact-breves-feed.php';
require_once PLAIDACT_BREVES_FEED_PATH . 'includes/class-plaidact-agenda-suite.php';

PlaidAct_Breves_Feed::init();
\PlaidAct\AgendaSuite\Plugin::init();
