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
		add_action( 'admin_menu', array( $this, 'register_import_page' ) );
		add_action( 'admin_post_plaidact_import_breves', array( $this, 'handle_import' ) );
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

		wp_register_script(
			'plaidact-breves-ticker',
			PLAIDACT_BREVES_FEED_URL . 'assets/js/plaidact-breves-ticker.js',
			array(),
			PLAIDACT_BREVES_FEED_VERSION,
			true
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
		wp_enqueue_script( 'plaidact-breves-ticker' );

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

	public function register_import_page(): void {
		add_submenu_page(
			'edit.php?post_type=breves',
			__( 'Import brèves', 'plaidact-breves-feed' ),
			__( 'Import CSV', 'plaidact-breves-feed' ),
			'manage_options',
			'plaidact-breves-import',
			array( $this, 'render_import_page' )
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

	public function render_import_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$status = isset( $_GET['status'] ) ? sanitize_key( (string) $_GET['status'] ) : '';
		$count  = isset( $_GET['count'] ) ? absint( $_GET['count'] ) : 0;
		$dupes  = isset( $_GET['dupes'] ) ? absint( $_GET['dupes'] ) : 0;

		$template_headers = implode( ',', array( 'title', 'slug', 'date', 'content', 'thematique_libre', 'url_externe' ) );
		$template_row     = 'Lancement de campagne,lancement-campagne,2026-03-15,Texte de la brève,Droits humains,https://example.org/article';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import des brèves', 'plaidact-breves-feed' ); ?></h1>
			<?php if ( 'ok' === $status ) : ?>
				<div class="notice notice-success"><p><?php echo esc_html( sprintf( __( '%d brèves importées/mises à jour (%d doublons ignorés).', 'plaidact-breves-feed' ), $count, $dupes ) ); ?></p></div>
			<?php endif; ?>
			<p><a class="button" href="data:text/csv;charset=utf-8,<?php echo rawurlencode( $template_headers . "\n" . $template_row ); ?>" download="modele-import-breves.csv"><?php esc_html_e( 'Télécharger un modèle CSV', 'plaidact-breves-feed' ); ?></a></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<?php wp_nonce_field( 'plaidact_import_breves' ); ?>
				<input type="hidden" name="action" value="plaidact_import_breves" />
				<input type="file" name="breves_csv" accept=".csv,text/csv" required />
				<?php submit_button( __( 'Importer', 'plaidact-breves-feed' ) ); ?>
			</form>
		</div>
		<?php
	}

	public function handle_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'plaidact-breves-feed' ) );
		}

		check_admin_referer( 'plaidact_import_breves' );
		if ( empty( $_FILES['breves_csv']['tmp_name'] ) ) {
			$this->redirect_import( 0, 0 );
		}

		$handle = fopen( (string) $_FILES['breves_csv']['tmp_name'], 'rb' );
		if ( false === $handle ) {
			$this->redirect_import( 0, 0 );
		}

		$first_line = (string) fgets( $handle );
		rewind( $handle );
		$delimiter = substr_count( $first_line, ';' ) > substr_count( $first_line, ',' ) ? ';' : ',';
		$headers = fgetcsv( $handle, 0, $delimiter );
		if ( ! is_array( $headers ) ) {
			fclose( $handle );
			$this->redirect_import( 0, 0 );
		}
		$headers = array_map( static fn( $header ) => sanitize_key( (string) $header ), $headers );

		$count = 0;
		$dupes = 0;
		$seen  = array();

		while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
			$data = array();
			foreach ( $headers as $index => $header ) {
				$data[ $header ] = isset( $row[ $index ] ) ? $this->sanitize_import_value( $header, (string) $row[ $index ] ) : '';
			}

			$title = (string) ( $data['title'] ?? '' );
			if ( '' === $title ) {
				continue;
			}

			$signature = $this->normalize_dedupe_key( $title . '|' . (string) ( $data['date'] ?? '' ) );
			if ( isset( $seen[ $signature ] ) ) {
				$dupes++;
				continue;
			}
			$seen[ $signature ] = true;

			$post_id = $this->upsert_breve_post( $data );
			if ( $post_id <= 0 ) {
				continue;
			}

			if ( isset( $data['thematique_libre'] ) ) {
				update_field( 'thematique_libre', (string) $data['thematique_libre'], $post_id );
			}
			if ( isset( $data['url_externe'] ) ) {
				update_field( 'url_externe', (string) $data['url_externe'], $post_id );
			}
			$count++;
		}

		fclose( $handle );
		$this->redirect_import( $count, $dupes );
	}

	/** @param array<string,string> $data */
	private function upsert_breve_post( array $data ): int {
		$slug = sanitize_title( (string) ( $data['slug'] ?? '' ) );
		$post = '' !== $slug ? get_page_by_path( $slug, OBJECT, 'breves' ) : null;

		$postarr = array(
			'post_type'    => 'breves',
			'post_status'  => 'publish',
			'post_title'   => sanitize_text_field( (string) ( $data['title'] ?? '' ) ),
			'post_content' => isset( $data['content'] ) ? wp_kses_post( (string) $data['content'] ) : '',
		);

		if ( ! empty( $data['date'] ) ) {
			$timestamp = strtotime( (string) $data['date'] );
			if ( false !== $timestamp ) {
				$postarr['post_date']     = gmdate( 'Y-m-d H:i:s', $timestamp );
				$postarr['post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $timestamp );
			}
		}

		if ( $post instanceof WP_Post ) {
			$postarr['ID'] = $post->ID;
			return (int) wp_update_post( $postarr );
		}

		if ( '' !== $slug ) {
			$postarr['post_name'] = $slug;
		}

		return (int) wp_insert_post( $postarr );
	}

	private function sanitize_import_value( string $header, string $value ): string {
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}

		if ( in_array( $header, array( 'url_externe' ), true ) ) {
			return esc_url_raw( $value );
		}

		if ( in_array( $header, array( 'content' ), true ) ) {
			return wp_kses_post( $value );
		}

		if ( in_array( $header, array( 'date' ), true ) ) {
			$timestamp = strtotime( $value );
			return false === $timestamp ? '' : gmdate( 'Y-m-d', $timestamp );
		}

		return sanitize_text_field( $value );
	}

	private function normalize_dedupe_key( string $value ): string {
		$value = remove_accents( strtolower( trim( $value ) ) );
		$value = preg_replace( '/\s+/', ' ', $value );
		if ( ! is_string( $value ) ) {
			return '';
		}
		return $value;
	}

	private function redirect_import( int $count, int $dupes ): void {
		wp_safe_redirect(
			add_query_arg(
				array(
					'post_type' => 'breves',
					'page'      => 'plaidact-breves-import',
					'status'    => 'ok',
					'count'     => $count,
					'dupes'     => $dupes,
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}

	private function get_current_page( string $query_var = 'paged' ): int {
		$page = absint( get_query_var( $query_var ) );

		if ( $page < 1 && isset( $_GET[ $query_var ] ) ) {
			$page = absint( wp_unslash( $_GET[ $query_var ] ) );
		}

		return max( 1, $page );
	}
}
