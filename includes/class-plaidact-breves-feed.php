<?php
/**
 * Main plugin class.
 *
 * @package PlaidAct_Breves_Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PlaidAct_Breves_Feed {
	/**
	 * Singleton instance.
	 *
	 * @var PlaidAct_Breves_Feed|null
	 */
	private static ?PlaidAct_Breves_Feed $instance = null;

	/**
	 * Init singleton.
	 *
	 * @return PlaidAct_Breves_Feed
	 */
	public static function init(): PlaidAct_Breves_Feed {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_shortcode( 'plaidact_breves', array( $this, 'render_shortcode' ) );
		add_filter( 'template_include', array( $this, 'register_archive_template' ) );
		add_filter( 'single_template', array( $this, 'register_single_template' ) );
	}

	/**
	 * Load plugin translations.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'plaidact-breves-feed', false, dirname( plugin_basename( PLAIDACT_BREVES_FEED_FILE ) ) . '/languages' );
	}

	/**
	 * Register plugin styles.
	 */
	public function register_assets(): void {
		wp_register_style(
			'plaidact-breves-feed',
			PLAIDACT_BREVES_FEED_URL . 'assets/css/breves-feed.css',
			array(),
			PLAIDACT_BREVES_FEED_VERSION
		);
	}

	/**
	 * Shortcode handler.
	 *
	 * @param array<string,string> $atts Shortcode attrs.
	 * @return string
	 */
	public function render_shortcode( array $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'posts_per_page' => '12',
				'title'          => '',
			),
			$atts,
			'plaidact_breves'
		);

		$paged = $this->get_current_page( 'breves_page' );

		return $this->render_feed(
			array(
				'posts_per_page' => absint( $atts['posts_per_page'] ),
				'paged'          => $paged,
				'pagination_var' => 'breves_page',
				'feed_title'     => (string) $atts['title'],
			)
		);
	}

	/**
	 * Render breves feed HTML.
	 *
	 * @param array<string,mixed> $args Display args.
	 * @return string
	 */
	public function render_feed( array $args = array() ): string {
		$defaults = array(
			'posts_per_page' => 12,
			'paged'          => $this->get_current_page(),
			'pagination_var' => 'paged',
			'feed_title'     => '',
			'container_class'=> '',
		);

		$args = wp_parse_args( $args, $defaults );

		$query = new WP_Query(
			array(
				'post_type'              => 'breves',
				'post_status'            => 'publish',
				'posts_per_page'         => max( 1, absint( $args['posts_per_page'] ) ),
				'paged'                  => max( 1, absint( $args['paged'] ) ),
				'orderby'                => 'date',
				'order'                  => 'DESC',
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		wp_enqueue_style( 'plaidact-breves-feed' );

		ob_start();
		$this->load_template(
			'breves-feed.php',
			array(
				'query' => $query,
				'args'  => $args,
				'self'  => $this,
			)
		);
		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * Get normalized link data for one breve.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string,string|bool>
	 */
	public function get_link_data( int $post_id ): array {
		$external = function_exists( 'get_field' ) ? (string) get_field( 'url_externe', $post_id ) : '';
		$external = trim( $external );

		if ( '' !== $external ) {
			return array(
				'url'         => $external,
				'target'      => '_blank',
				'rel'         => 'noopener noreferrer',
				'is_external' => true,
			);
		}

		return array(
			'url'         => get_permalink( $post_id ),
			'target'      => '_self',
			'rel'         => '',
			'is_external' => false,
		);
	}

	/**
	 * Build paginated links.
	 *
	 * @param WP_Query $query Query instance.
	 * @param array<string,mixed> $args Render args.
	 * @return string
	 */
	public function pagination( WP_Query $query, array $args ): string {
		if ( $query->max_num_pages < 2 ) {
			return '';
		}

		$current = max( 1, absint( $args['paged'] ) );
		$var     = sanitize_key( (string) $args['pagination_var'] );
		$base    = trailingslashit( remove_query_arg( $var ) );

		$links = paginate_links(
			array(
				'base'      => esc_url_raw( add_query_arg( $var, '%#%', $base ) ),
				'format'    => '',
				'current'   => $current,
				'total'     => (int) $query->max_num_pages,
				'mid_size'  => 1,
				'end_size'  => 1,
				'prev_next' => true,
				'type'      => 'list',
				'prev_text' => esc_html__( 'Précédent', 'plaidact-breves-feed' ),
				'next_text' => esc_html__( 'Suivant', 'plaidact-breves-feed' ),
			)
		);

		return is_string( $links ) ? $links : '';
	}

	/**
	 * Template override for archive-breves.
	 *
	 * @param string $template Current template.
	 * @return string
	 */
	public function register_archive_template( string $template ): string {
		if ( is_post_type_archive( 'breves' ) ) {
			$custom = locate_template( 'archive-breves.php' );
			if ( $custom ) {
				return $custom;
			}

			$plugin_template = PLAIDACT_BREVES_FEED_PATH . 'templates/archive-breves.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Template override for single breves.
	 *
	 * @param string $template Current template.
	 * @return string
	 */
	public function register_single_template( string $template ): string {
		if ( 'breves' === get_post_type() ) {
			$custom = locate_template( 'single-breves.php' );
			if ( $custom ) {
				return $custom;
			}

			$plugin_template = PLAIDACT_BREVES_FEED_PATH . 'templates/single-breves.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Load one template file.
	 *
	 * @param string               $template_name Template filename.
	 * @param array<string,mixed>  $args Variables for template.
	 */
	public function load_template( string $template_name, array $args = array() ): void {
		$theme_path = locate_template( 'plaidact-breves/' . $template_name );
		$template   = $theme_path ? $theme_path : PLAIDACT_BREVES_FEED_PATH . 'templates/' . $template_name;

		if ( ! file_exists( $template ) ) {
			return;
		}

		extract( $args, EXTR_SKIP );
		require $template;
	}

	/**
	 * Current page helper.
	 *
	 * @param string $query_var Query variable.
	 * @return int
	 */
	private function get_current_page( string $query_var = 'paged' ): int {
		$page = absint( get_query_var( $query_var ) );

		if ( $page < 1 && isset( $_GET[ $query_var ] ) ) {
			$page = absint( wp_unslash( $_GET[ $query_var ] ) );
		}

		return max( 1, $page );
	}
}
