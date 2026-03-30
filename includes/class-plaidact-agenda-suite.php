<?php

namespace PlaidAct\AgendaSuite;

use DateTimeImmutable;
use WP_Post;
use WP_Query;
use WP_Term;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_taxonomy' ], 0 );
		add_action( 'init', [ __CLASS__, 'register_asso_cpt_and_taxonomies' ], 1 );
		add_action( 'init', [ __CLASS__, 'register_blocks' ], 20 );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
		add_shortcode( 'plaidact_timeline', [ __CLASS__, 'timeline_shortcode' ] );
		add_shortcode( 'plaidact_asso_directory', [ __CLASS__, 'asso_directory_shortcode' ] );
		add_shortcode( 'plaidact_ong_directory', [ __CLASS__, 'asso_directory_shortcode' ] ); // legacy
		add_filter( 'template_include', [ __CLASS__, 'maybe_use_plugin_templates' ] );
		add_filter( 'theme_page_templates', [ __CLASS__, 'register_page_templates' ] );
		add_filter( 'template_include', [ __CLASS__, 'handle_page_template' ], 99 );
		add_action( 'admin_menu', [ __CLASS__, 'register_admin_pages' ] );
		add_action( 'admin_post_plaidact_import_asso_csv', [ __CLASS__, 'handle_asso_csv_import' ] );
	}

	public static function register_taxonomy(): void {
		register_taxonomy(
			'agenda_timeline',
			[ 'agenda' ],
			[
				'labels' => [
					'name'          => _x( 'Timelines Agenda', 'taxonomy general name', 'plaidact-breves-feed' ),
					'singular_name' => _x( 'Timeline Agenda', 'taxonomy singular name', 'plaidact-breves-feed' ),
					'menu_name'     => __( 'Timelines', 'plaidact-breves-feed' ),
				],
				'public'            => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => [
					'slug'       => 'agenda-timeline',
					'with_front' => false,
				],
			]
		);
	}

	public static function register_asso_cpt_and_taxonomies(): void {
		register_post_type(
			'ong',
			[
				'labels' => [
					'name'          => __( 'Associations', 'plaidact-breves-feed' ),
					'singular_name' => __( 'Association', 'plaidact-breves-feed' ),
					'menu_name'     => __( 'Répertoire Asso', 'plaidact-breves-feed' ),
				],
				'public'             => true,
				'has_archive'        => 'asso',
				'rewrite'            => [ 'slug' => 'asso', 'with_front' => false ],
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-groups',
				'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
				'publicly_queryable' => true,
			]
		);

		register_taxonomy(
			'cause',
			[ 'ong' ],
			[
				'labels' => [
					'name'          => __( 'Causes', 'plaidact-breves-feed' ),
					'singular_name' => __( 'Cause', 'plaidact-breves-feed' ),
				],
				'public'            => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => [ 'slug' => 'cause', 'with_front' => false ],
			]
		);
	}

	public static function register_blocks(): void {
		wp_register_script(
			'plaidact-blocks',
			PLAIDACT_BREVES_FEED_URL . 'assets/js/plaidact-blocks.js',
			[ 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-server-side-render', 'wp-block-editor' ],
			PLAIDACT_BREVES_FEED_VERSION,
			true
		);

		register_block_type(
			'plaidact/timeline',
			[
				'api_version'     => 2,
				'editor_script'   => 'plaidact-blocks',
				'render_callback' => [ __CLASS__, 'render_timeline_block' ],
				'attributes'      => [
					'term' => [ 'type' => 'string', 'default' => '' ],
					'fillEmptyMonths' => [ 'type' => 'boolean', 'default' => false ],
				],
			]
		);

			register_block_type(
			'plaidact/asso-cause-list',
			[
				'api_version'     => 2,
				'editor_script'   => 'plaidact-blocks',
				'render_callback' => [ __CLASS__, 'render_asso_block' ],
				'attributes'      => [
					'cause'       => [ 'type' => 'string', 'default' => '' ],
					'postsToShow' => [ 'type' => 'number', 'default' => 9 ],
				],
			]
			);

		register_block_type(
			'plaidact/asso-directory',
			[
				'api_version'     => 2,
				'editor_script'   => 'plaidact-blocks',
				'render_callback' => [ __CLASS__, 'render_asso_directory_block' ],
				'attributes'      => [
					'postsToShow' => [ 'type' => 'number', 'default' => 9 ],
				],
			]
		);
	}

	public static function render_timeline_block( array $attributes ): string {
		$term = isset( $attributes['term'] ) ? sanitize_title( (string) $attributes['term'] ) : '';
		$fill = isset( $attributes['fillEmptyMonths'] ) && $attributes['fillEmptyMonths'] ? '1' : '0';
		return self::timeline_shortcode( [ 'term' => $term, 'fill_empty_months' => $fill ] );
	}

	public static function render_asso_block( array $attributes ): string {
		wp_enqueue_style( 'plaidact-asso-directory', PLAIDACT_BREVES_FEED_URL . 'assets/css/asso-directory.css', [], PLAIDACT_BREVES_FEED_VERSION );
		return self::asso_directory_shortcode(
			[
				'cause'          => isset( $attributes['cause'] ) ? sanitize_title( (string) $attributes['cause'] ) : '',
				'posts_per_page' => isset( $attributes['postsToShow'] ) ? (string) absint( $attributes['postsToShow'] ) : '9',
			]
		);
	}

	public static function render_asso_directory_block( array $attributes ): string {
		wp_enqueue_style( 'plaidact-asso-directory', PLAIDACT_BREVES_FEED_URL . 'assets/css/asso-directory.css', [], PLAIDACT_BREVES_FEED_VERSION );
		return self::asso_directory_shortcode(
			[
				'posts_per_page' => isset( $attributes['postsToShow'] ) ? (string) absint( $attributes['postsToShow'] ) : '9',
			]
		);
	}

	public static function enqueue_assets(): void {
		global $post;
		$load_timeline = is_tax( 'agenda_timeline' );
		$load_asso     = is_post_type_archive( 'ong' ) || is_singular( 'ong' );

		if ( $post instanceof WP_Post ) {
			$load_timeline = $load_timeline || has_shortcode( $post->post_content, 'plaidact_timeline' );
			$load_asso     = $load_asso || has_shortcode( $post->post_content, 'plaidact_asso_directory' ) || has_shortcode( $post->post_content, 'plaidact_ong_directory' );
		}

		if ( $load_timeline ) {
			wp_enqueue_style( 'plaidact-agenda-timeline', PLAIDACT_BREVES_FEED_URL . 'assets/css/agenda-timeline.css', [], PLAIDACT_BREVES_FEED_VERSION );
			wp_enqueue_script( 'plaidact-agenda-timeline', PLAIDACT_BREVES_FEED_URL . 'assets/js/agenda-timeline.js', [], PLAIDACT_BREVES_FEED_VERSION, true );
		}

		if ( $load_asso ) {
			wp_enqueue_style( 'plaidact-asso-directory', PLAIDACT_BREVES_FEED_URL . 'assets/css/asso-directory.css', [], PLAIDACT_BREVES_FEED_VERSION );
		}
	}

	public static function register_page_templates( array $templates ): array {
		$templates['plaidact-asso-directory-template.php'] = __( 'Répertoire des associations (PlaidAct)', 'plaidact-breves-feed' );
		return $templates;
	}

	public static function handle_page_template( string $template ): string {
		if ( ! is_singular( 'page' ) ) {
			return $template;
		}
		if ( 'plaidact-asso-directory-template.php' === get_page_template_slug() ) {
			return PLAIDACT_BREVES_FEED_PATH . 'templates/page-repertoire-asso.php';
		}

		return $template;
	}

	public static function maybe_use_plugin_templates( string $template ): string {
		if ( is_tax( 'agenda_timeline' ) ) {
			return PLAIDACT_BREVES_FEED_PATH . 'templates/taxonomy-agenda_timeline.php';
		}
		if ( is_post_type_archive( 'ong' ) ) {
			return PLAIDACT_BREVES_FEED_PATH . 'templates/archive-asso.php';
		}
		if ( is_singular( 'ong' ) ) {
			return PLAIDACT_BREVES_FEED_PATH . 'templates/single-asso.php';
		}

		return $template;
	}

	public static function asso_directory_shortcode( array $atts ): string {
		$atts = shortcode_atts(
			[
				'posts_per_page' => 9,
				'cause'          => '',
			],
			$atts,
			'plaidact_asso_directory'
		);

		ob_start();
		self::render_template(
			'asso-directory-loop.php',
			[
				'posts_per_page' => max( 1, absint( $atts['posts_per_page'] ) ),
				'is_shortcode'   => true,
				'fixed_cause'    => sanitize_title( (string) $atts['cause'] ),
			]
		);
		return (string) ob_get_clean();
	}

	public static function timeline_shortcode( array $atts ): string {
		$atts = shortcode_atts(
			[
				'term'  => '',
				'title' => '',
				'fill_empty_months' => '0',
			],
			$atts,
			'plaidact_timeline'
		);

		$term_slug = sanitize_title( (string) $atts['term'] );
		if ( '' === $term_slug ) {
			return '<p class="pa-timeline-error">' . esc_html__( 'Shortcode [plaidact_timeline] : paramètre "term" manquant.', 'plaidact-breves-feed' ) . '</p>';
		}

		$payload = self::build_timeline_data( $term_slug, '1' === (string) $atts['fill_empty_months'] );
		if ( empty( $payload['years'] ) ) {
			return '<p class="pa-timeline-empty">' . esc_html__( 'Aucun événement à afficher pour cette timeline.', 'plaidact-breves-feed' ) . '</p>';
		}

		ob_start();
		self::render_template(
			'timeline.php',
			[
				'data'           => $payload,
				'title_override' => sanitize_text_field( (string) $atts['title'] ),
			]
		);
		return (string) ob_get_clean();
	}

	/** @return array<string,mixed> */
	public static function get_asso_filters_from_request(): array {
		return [
			's'     => isset( $_GET['asso_s'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['asso_s'] ) ) : '',
			'cause' => isset( $_GET['asso_cause'] ) ? sanitize_title( wp_unslash( (string) $_GET['asso_cause'] ) ) : '',
			'paged' => max( 1, get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : ( isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 ) ),
		];
	}

	/** @param array<string,mixed> $filters */
	public static function get_asso_query_args( array $filters, int $posts_per_page = 9, string $fixed_cause = '' ): array {
		$args = [
			'post_type'      => 'ong',
			'post_status'    => 'publish',
			'posts_per_page' => $posts_per_page,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'paged'          => (int) $filters['paged'],
			's'              => (string) $filters['s'],
		];

		$target_cause = '' !== $fixed_cause ? $fixed_cause : (string) $filters['cause'];
		if ( '' !== $target_cause ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'cause',
					'field'    => 'slug',
					'terms'    => $target_cause,
				],
			];
		}

		return $args;
	}

	/** @return array<string,mixed> */
	public static function get_asso_card_data( int $post_id ): array {
		$site_url = trim( (string) get_field( 'url_web', $post_id ) );
		$excerpt  = trim( (string) get_field( 'resume_court', $post_id ) );
		if ( '' === $excerpt ) {
			$excerpt = wp_strip_all_tags( get_the_excerpt( $post_id ) );
		}
		return [
			'post_id'          => $post_id,
			'title'            => get_the_title( $post_id ),
			'permalink'        => get_permalink( $post_id ),
			'zone_dengagement' => (string) get_field( 'zone_dengagement', $post_id ),
			'excerpt'          => wp_trim_words( $excerpt, 24, '…' ),
			'site_url'         => $site_url,
			'cause_terms'      => get_the_terms( $post_id, 'cause' ) ?: [],
		];
	}

	/** @return array<int,array{post_id:int,title:string,permalink:string}> */
	public static function get_similar_asso( int $post_id, int $limit = 3 ): array {
		$terms = get_the_terms( $post_id, 'cause' );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return [];
		}
		$term_ids = wp_list_pluck( $terms, 'term_id' );
		$query    = new WP_Query(
			[
				'post_type'      => 'ong',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'post__not_in'   => [ $post_id ],
				'orderby'        => 'rand',
				'tax_query'      => [
					[
						'taxonomy' => 'cause',
						'field'    => 'term_id',
						'terms'    => $term_ids,
					],
				],
			]
		);
		$items    = [];
		while ( $query->have_posts() ) {
			$query->the_post();
			$items[] = [
				'post_id'   => get_the_ID(),
				'title'     => get_the_title(),
				'permalink' => get_the_permalink(),
			];
		}
		wp_reset_postdata();

		return $items;
	}

	/** @return array{years: array<int,array{year:int,months:array<int,array{month:int,month_name:string,events:array}>}>, term: WP_Term|null} */
	public static function build_timeline_data( string $term_slug, bool $fill_empty_months = false ): array {
		$term = get_term_by( 'slug', $term_slug, 'agenda_timeline' );

		$query = new WP_Query(
			[
				'post_type'      => 'agenda',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_key'       => 'date_debut',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'tax_query'      => [
					[
						'taxonomy' => 'agenda_timeline',
						'field'    => 'slug',
						'terms'    => $term_slug,
					],
				],
			]
		);

		$events = [];
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();

			$start = self::parse_acf_date( (string) get_post_meta( $post_id, 'date_debut', true ) );
			if ( ! $start ) {
				continue;
			}
			$end = self::parse_acf_date( (string) get_post_meta( $post_id, 'date_fin', true ) );
			$config = get_field( 'configuration', $post_id );

			$external_link = (string) get_field( 'lien_evenement', $post_id );

			$events[] = [
				'id'          => $post_id,
				'title'       => get_the_title(),
				'date_debut'  => $start,
				'date_fin'    => $end,
				'type'        => (string) get_post_meta( $post_id, 'type_evenement', true ),
				'config'      => is_array( $config ) ? $config : [],
				'lieu'        => (string) get_post_meta( $post_id, 'lieu', true ),
				'mini_logo'   => (string) get_field( 'mini_logo', $post_id ),
				'url'         => '' !== $external_link ? $external_link : get_permalink( $post_id ),
				'is_external' => '' !== $external_link,
			];
		}
		wp_reset_postdata();

		$grouped = [];
		foreach ( $events as $event ) {
			if ( 'mensuels' === $event['type'] && $event['date_fin'] instanceof DateTimeImmutable ) {
				$cursor    = $event['date_debut']->modify( 'first day of this month' );
				$end_month = $event['date_fin']->modify( 'first day of this month' );
				$first     = $cursor;

				while ( $cursor <= $end_month ) {
					$slot                    = $event;
					$slot['is_continuation'] = $cursor > $first;
					$grouped[ (int) $cursor->format( 'Y' ) ][ (int) $cursor->format( 'n' ) ][] = $slot;
					$cursor = $cursor->modify( '+1 month' );
				}
			} else {
				$event['is_continuation'] = false;
				$grouped[ (int) $event['date_debut']->format( 'Y' ) ][ (int) $event['date_debut']->format( 'n' ) ][] = $event;
			}
		}

		if ( $fill_empty_months && ! empty( $grouped ) ) {
			$years_keys = array_keys( $grouped );
			$min_year = (int) min( $years_keys );
			$max_year = (int) max( $years_keys );
			for ( $y = $min_year; $y <= $max_year; $y++ ) {
				if ( ! isset( $grouped[ $y ] ) ) {
					$grouped[ $y ] = [];
				}
				for ( $m = 1; $m <= 12; $m++ ) {
					if ( ! isset( $grouped[ $y ][ $m ] ) ) {
						$grouped[ $y ][ $m ] = [];
					}
				}
			}
		}

		ksort( $grouped );
		$years = [];
		foreach ( $grouped as $year => $months ) {
			ksort( $months );
			$month_data = [];
			foreach ( $months as $month => $month_events ) {
				if ( empty( $month_events ) && ! $fill_empty_months ) {
					continue;
				}
				usort(
					$month_events,
					static fn( array $a, array $b ) => $a['date_debut']->getTimestamp() <=> $b['date_debut']->getTimestamp()
				);
				$month_data[] = [
					'month'      => (int) $month,
					'month_name' => self::month_name( (int) $month ),
					'events'     => $month_events,
				];
			}
			if ( empty( $month_data ) ) {
				continue;
			}
			$years[] = [
				'year'   => (int) $year,
				'months' => $month_data,
			];
		}

		return [
			'years' => $years,
			'term'  => $term instanceof WP_Term ? $term : null,
		];
	}

	public static function parse_acf_date( string $raw ): ?DateTimeImmutable {
		$raw = trim( $raw );
		if ( '' === $raw ) {
			return null;
		}

		foreach ( [ 'Ymd', 'd/m/Y', 'Y-m-d' ] as $format ) {
			$date = DateTimeImmutable::createFromFormat( $format, $raw );
			if ( false !== $date ) {
				return $date->setTime( 0, 0, 0 );
			}
		}
		return null;
	}

	public static function month_name( int $month ): string {
		$months = [
			1 => __( 'Janvier', 'plaidact-breves-feed' ),
			2 => __( 'Février', 'plaidact-breves-feed' ),
			3 => __( 'Mars', 'plaidact-breves-feed' ),
			4 => __( 'Avril', 'plaidact-breves-feed' ),
			5 => __( 'Mai', 'plaidact-breves-feed' ),
			6 => __( 'Juin', 'plaidact-breves-feed' ),
			7 => __( 'Juillet', 'plaidact-breves-feed' ),
			8 => __( 'Août', 'plaidact-breves-feed' ),
			9 => __( 'Septembre', 'plaidact-breves-feed' ),
			10 => __( 'Octobre', 'plaidact-breves-feed' ),
			11 => __( 'Novembre', 'plaidact-breves-feed' ),
			12 => __( 'Décembre', 'plaidact-breves-feed' ),
		];
		return $months[ $month ] ?? '';
	}

	public static function month_abbr( int $month ): string {
		$months = [ 1 => 'jan.', 2 => 'fév.', 3 => 'mars', 4 => 'avr.', 5 => 'mai', 6 => 'juin', 7 => 'juil.', 8 => 'août', 9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.' ];
		return $months[ $month ] ?? '';
	}

	public static function format_date_short( DateTimeImmutable $date ): string {
		return $date->format( 'j' ) . ' ' . self::month_abbr( (int) $date->format( 'n' ) ) . ' ' . $date->format( 'Y' );
	}

	/** @param array<string,mixed> $vars */
	public static function render_template( string $template, array $vars = [] ): void {
		$file = PLAIDACT_BREVES_FEED_PATH . 'templates/' . ltrim( $template, '/' );
		if ( ! file_exists( $file ) ) {
			return;
		}
		extract( $vars, EXTR_SKIP );
		require $file;
	}

	public static function get_social_platforms(): array {
		return [
			'facebook'  => [ 'label' => 'Facebook', 'icon' => 'f' ],
			'instagram' => [ 'label' => 'Instagram', 'icon' => 'ig' ],
			'x'         => [ 'label' => 'X', 'icon' => 'x' ],
			'linkedin'  => [ 'label' => 'LinkedIn', 'icon' => 'in' ],
			'youtube'   => [ 'label' => 'YouTube', 'icon' => '▶' ],
			'tiktok'    => [ 'label' => 'TikTok', 'icon' => '♪' ],
			'threads'   => [ 'label' => 'Threads', 'icon' => '@' ],
			'telegram'  => [ 'label' => 'Telegram', 'icon' => '✈' ],
			'whatsapp'  => [ 'label' => 'WhatsApp', 'icon' => 'wa' ],
			'discord'   => [ 'label' => 'Discord', 'icon' => 'dc' ],
			'bluesky'   => [ 'label' => 'Bluesky', 'icon' => 'bs' ],
		];
	}

	public static function get_asso_social_links( int $post_id ): array {
		$links = [];
		foreach ( self::get_social_platforms() as $key => $meta ) {
			$url = trim( (string) get_field( 'social_' . $key, $post_id ) );
			if ( '' !== $url ) {
				$links[] = [
					'key'   => $key,
					'label' => $meta['label'],
					'icon'  => $meta['icon'],
					'url'   => $url,
				];
			}
		}

		return $links;
	}

	public static function register_admin_pages(): void {
		add_management_page(
			__( 'Import Asso CSV', 'plaidact-breves-feed' ),
			__( 'Import Asso CSV', 'plaidact-breves-feed' ),
			'manage_options',
			'plaidact-import-asso',
			[ __CLASS__, 'render_import_page' ]
		);
	}

	public static function render_import_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import des associations (CSV)', 'plaidact-breves-feed' ); ?></h1>
			<p><?php esc_html_e( 'Colonnes supportées: title, content, excerpt, zone_dengagement, comment_agir, url_web, url_don, cause, logo_url, social_facebook, social_instagram, social_x, social_linkedin, social_youtube, social_tiktok, social_threads, social_telegram, social_whatsapp, social_discord, social_bluesky.', 'plaidact-breves-feed' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<?php wp_nonce_field( 'plaidact_import_asso_csv', 'plaidact_import_asso_nonce' ); ?>
				<input type="hidden" name="action" value="plaidact_import_asso_csv">
				<input type="file" name="asso_csv" accept=".csv,text/csv" required>
				<?php submit_button( __( 'Importer', 'plaidact-breves-feed' ) ); ?>
			</form>
		</div>
		<?php
	}

	public static function handle_asso_csv_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'plaidact-breves-feed' ) );
		}
		check_admin_referer( 'plaidact_import_asso_csv', 'plaidact_import_asso_nonce' );

		if ( empty( $_FILES['asso_csv']['tmp_name'] ) ) {
			wp_safe_redirect( admin_url( 'tools.php?page=plaidact-import-asso' ) );
			exit;
		}

		$handle = fopen( $_FILES['asso_csv']['tmp_name'], 'r' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( false === $handle ) {
			wp_safe_redirect( admin_url( 'tools.php?page=plaidact-import-asso' ) );
			exit;
		}

		$headers = fgetcsv( $handle, 0, ',' );
		if ( ! is_array( $headers ) ) {
			fclose( $handle );
			wp_safe_redirect( admin_url( 'tools.php?page=plaidact-import-asso' ) );
			exit;
		}

		$headers = array_map( 'trim', $headers );
		while ( ( $row = fgetcsv( $handle, 0, ',' ) ) !== false ) {
			$data = array_combine( $headers, $row );
			if ( ! is_array( $data ) || empty( $data['title'] ) ) {
				continue;
			}

			$post_id = wp_insert_post(
				[
					'post_type'    => 'ong',
					'post_status'  => 'publish',
					'post_title'   => sanitize_text_field( (string) $data['title'] ),
					'post_content' => wp_kses_post( (string) ( $data['content'] ?? '' ) ),
					'post_excerpt' => sanitize_textarea_field( (string) ( $data['excerpt'] ?? '' ) ),
				]
			);

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}

			$acf_fields = [
				'zone_dengagement',
				'comment_agir',
				'url_web',
				'url_don',
				'social_facebook',
				'social_instagram',
				'social_x',
				'social_linkedin',
				'social_youtube',
				'social_tiktok',
				'social_threads',
				'social_telegram',
				'social_whatsapp',
				'social_discord',
				'social_bluesky',
			];
			foreach ( $acf_fields as $field ) {
				if ( isset( $data[ $field ] ) && function_exists( 'update_field' ) ) {
					update_field( $field, sanitize_text_field( (string) $data[ $field ] ), $post_id );
				}
			}

			if ( ! empty( $data['cause'] ) ) {
				$terms = array_filter( array_map( 'trim', explode( '|', (string) $data['cause'] ) ) );
				wp_set_object_terms( $post_id, $terms, 'cause' );
			}

			if ( ! empty( $data['logo_url'] ) ) {
				self::sideload_featured_image( (string) $data['logo_url'], $post_id );
			}
		}
		fclose( $handle );

		wp_safe_redirect( admin_url( 'tools.php?page=plaidact-import-asso&import=done' ) );
		exit;
	}

	private static function sideload_featured_image( string $url, int $post_id ): void {
		if ( '' === trim( $url ) ) {
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = download_url( esc_url_raw( $url ) );
		if ( is_wp_error( $tmp ) ) {
			return;
		}
		$file = [
			'name'     => wp_basename( wp_parse_url( $url, PHP_URL_PATH ) ?: 'logo.jpg' ),
			'tmp_name' => $tmp,
		];
		$attachment_id = media_handle_sideload( $file, $post_id );
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return;
		}
		set_post_thumbnail( $post_id, $attachment_id );
	}
}
