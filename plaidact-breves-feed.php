<?php
/**
 * Plugin Name: PlaidAct Breves Feed
 * Plugin URI:  https://plaidact.example
 * Description: Fil d'actualité compact pour le CPT breves (ACF, shortcode, templates et CSS responsive sans dépendances).
 * Version:     1.1.0
 * Author:      PlaidAct
 * Text Domain: plaidact-breves-feed
 * Domain Path: /languages
 * Requires PHP: 8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PLAIDACT_BREVES_FEED_VERSION', '1.1.0' );
define( 'PLAIDACT_BREVES_FEED_FILE', __FILE__ );
define( 'PLAIDACT_BREVES_FEED_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLAIDACT_BREVES_FEED_URL', plugin_dir_url( __FILE__ ) );

require_once PLAIDACT_BREVES_FEED_PATH . 'includes/class-plaidact-breves-feed.php';

PlaidAct_Breves_Feed::init();
