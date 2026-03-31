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
	private static ?PlaidAct_Breves_Feed $instance = null;

	public static function init(): PlaidAct_Breves_Feed {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_breves_post_type' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_shortcode( 'plaidact_breves', array( $this, 'render_shortcode' ) );
		add_shortcode( 'plaidact_breves_latest_dropdown', array( $this, 'render_latest_dropdown_shortcode' ) );
		add_shortcode( 'plaidact_breves_timeline', array( $this, 'render_timeline_shortcode' ) );
		add_shortcode( 'plaidact_breves_all', array( $this, 'render_all_breves_grid_shortcode' ) );
		add_filter( 'template_include', array( $this, 'register_archive_template' ) );
		add_filter( 'single_template', array( $this, 'register_single_template' ) );
		add_action( 'admin_menu', array( $this, 'register_export_page' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'plaidact-breves-feed', false, dirname( plugin_basename( PLAIDACT_BREVES_FEED_FILE ) ) . '/languages' );
	}

	public function register_breves_post_type(): void {
		register_post_type(
			'breves',
			array(
				'labels' => array(
					'name'          => __( 'Brèves', 'plaidact-breves-feed' ),
					'singular_name' => __( 'Brève', 'plaidact-breves-feed' ),
					'menu_name'     => __( 'Brèves', 'plaidact-breves-feed' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'has_archive'  => 'breves',
				'rewrite'      => array( 'slug' => 'breves', 'with_front' => false ),
				'menu_icon'    => 'dashicons-megaphone',
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author' ),
			)
		);
	}

	public function register_assets(): void {
		wp_register_style(
			'plaidact-breves-feed',
			PLAIDACT_BREVES_FEED_URL . 'assets/css/breves-feed.css',
			array(),
			PLAIDACT_BREVES_FEED_VERSION
		);
	}

	public function render_shortcode( array $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'posts_per_page' => '12',
				'title'          => '',
			),
			$atts,
			'plaidact_breves'
		);

		return $this->render_feed(
			array(
				'posts_per_page' => absint( $atts['posts_per_page'] ),
				'paged'          => 1,
				'pagination_var' => 'breves_page',
				'feed_title'     => (string) $atts['title'],
				'is_ticker'      => false,
			)
		);
	}

	public function render_timeline_shortcode( array $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'posts_per_page' => '12',
				'title'          => '',
			),
			$atts,
			'plaidact_breves_timeline'
		);

		return $this->render_feed(
			array(
				'posts_per_page' => absint( $atts['posts_per_page'] ),
				'paged'          => 1,
				'pagination_var' => 'breves_page',
				'feed_title'     => (string) $atts['title'],
				'is_ticker'      => true,
			)
		);
	}

	public function render_all_breves_grid_shortcode( array $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'posts_per_page' => '5',
			),
			$atts,
			'plaidact_breves_all'
		);

		$paged = $this->get_current_page( 'breves_all_page' );

		$query = new WP_Query(
			array(
				'post_type'              => 'breves',
				'post_status'            => 'publish',
				'posts_per_page'         => max( 1, absint( $atts['posts_per_page'] ) ),
				'paged'                  => $paged,
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
			'breves-all-grid.php',
			array(
				'query' => $query,
				'self'  => $this,
				'paged' => $paged,
			)
		);
		wp_reset_postdata();
		return (string) ob_get_clean();
	}

	public function render_latest_dropdown_shortcode( array $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'limit' => '20',
				'label' => __( 'Dernières actualités', 'plaidact-breves-feed' ),
			),
			$atts,
			'plaidact_breves_latest_dropdown'
		);
		$limit = max( 1, absint( $atts['limit'] ) );
		$query = new WP_Query(
			array(
				'post_type'              => 'breves',
				'post_status'            => 'publish',
				'posts_per_page'         => $limit,
				'orderby'                => 'date',
				'order'                  => 'DESC',
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( ! $query->have_posts() ) {
			return '';
		}

		wp_enqueue_style( 'plaidact-breves-feed' );

		ob_start();
		?>
		<div class="plaidact-breves-dropdown-wrap">
			<label class="screen-reader-text" for="plaidact-breves-dropdown"><?php echo esc_html( (string) $atts['label'] ); ?></label>
			<select id="plaidact-breves-dropdown" class="plaidact-breves-dropdown" onchange="if(this.value){window.open(this.value,'_self');}">
				<option value=""><?php echo esc_html( (string) $atts['label'] ); ?></option>
				<?php while ( $query->have_posts() ) : ?>
					<?php
					$query->the_post();
					$post_id = get_the_ID();
					$link    = $this->get_link_data( $post_id );
					?>
					<option value="<?php echo esc_url( (string) $link['url'] ); ?>">
						<?php echo esc_html( get_the_date( 'd/m/Y', $post_id ) . ' — ' . get_the_title( $post_id ) ); ?>
					</option>
				<?php endwhile; ?>
			</select>
		</div>
		<?php
		wp_reset_postdata();
		return (string) ob_get_clean();
	}

	public function render_feed( array $args = array() ): string {
		$defaults = array(
			'posts_per_page' => 12,
			'paged'          => $this->get_current_page(),
			'pagination_var' => 'paged',
			'feed_title'     => '',
			'container_class'=> '',
			'is_ticker'      => false,
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
				'no_found_rows'          => ! empty( $args['is_ticker'] ),
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

	public function load_template( string $template_name, array $args = array() ): void {
		$theme_path = locate_template( 'plaidact-breves/' . $template_name );
		$template   = $theme_path ? $theme_path : PLAIDACT_BREVES_FEED_PATH . 'templates/' . $template_name;

		if ( ! file_exists( $template ) ) {
			return;
		}

		extract( $args, EXTR_SKIP );
		require $template;
	}

	public function register_export_page(): void {
		add_submenu_page(
			'edit.php?post_type=breves',
			__( 'Export newsletter', 'plaidact-breves-feed' ),
			__( 'Export newsletter', 'plaidact-breves-feed' ),
			'manage_options',
			'plaidact-breves-export',
			array( $this, 'render_export_page' )
		);
	}

	public function render_export_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$days  = 35;
		$posts = get_posts(
			array(
				'post_type'              => 'breves',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'orderby'                => 'date',
				'order'                  => 'DESC',
				'date_query'             => array(
					array(
						'after'     => gmdate( 'Y-m-d', strtotime( '-' . $days . ' days' ) ),
						'inclusive' => true,
					),
				),
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		$lines = array();
		foreach ( $posts as $post ) {
			$permalink = get_permalink( $post );
			if ( ! is_string( $permalink ) ) {
				$permalink = '';
			}
			$lines[] = sprintf(
				"- %s — %s\n%s",
				get_the_date( 'd/m/Y', $post ),
				wp_strip_all_tags( get_the_title( $post ) ),
				$permalink
			);
		}
		$payload = implode( "\n\n", $lines );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Export newsletter (35 derniers jours)', 'plaidact-breves-feed' ); ?></h1>
			<p><?php esc_html_e( 'Copiez/collez ce texte dans votre newsletter.', 'plaidact-breves-feed' ); ?></p>
			<textarea readonly rows="18" style="width:100%;font-family:monospace;"><?php echo esc_textarea( $payload ); ?></textarea>
		</div>
		<?php
	}

	private function get_current_page( string $query_var = 'paged' ): int {
		$page = absint( get_query_var( $query_var ) );

		if ( $page < 1 && isset( $_GET[ $query_var ] ) ) {
			$page = absint( wp_unslash( $_GET[ $query_var ] ) );
		}

		return max( 1, $page );
	}
}
