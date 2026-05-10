<?php
/**
 * FluentCRM public directory shortcode.
 *
 * @package PlaidAct_Breves_Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PlaidAct_FluentCRM_Directory {
	private const SHORTCODE = 'fluentcrm_directory';
	private const NONCE_ACTION_SUBSCRIBE = 'plaidact_fluentcrm_subscribe';
	private const NONCE_ACTION_DOWNLOAD  = 'plaidact_fluentcrm_download';
	private const DOWNLOAD_ACTION        = 'plaidact_fluentcrm_download_csv';
	private const OPTION_PUBLIC_LISTS    = 'plaidact_fcd_public_lists';
	private const PROSPECT_LIST_ID       = 0;

	private static ?PlaidAct_FluentCRM_Directory $instance = null;

	public static function init(): PlaidAct_FluentCRM_Directory {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_shortcode( self::SHORTCODE, array( $this, 'render_shortcode' ) );
		add_shortcode( 'plaidact_fluentcrm_directory', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_plaidact_fluentcrm_subscribe', array( $this, 'handle_subscribe' ) );
		add_action( 'wp_ajax_nopriv_plaidact_fluentcrm_subscribe', array( $this, 'handle_subscribe' ) );
		add_action( 'admin_post_' . self::DOWNLOAD_ACTION, array( $this, 'handle_csv_download' ) );
		add_action( 'admin_post_nopriv_' . self::DOWNLOAD_ACTION, array( $this, 'handle_csv_download' ) );
	}

	public function register_assets(): void {
		wp_register_style( 'plaidact-fluentcrm-directory', PLAIDACT_BREVES_FEED_URL . 'assets/css/fluentcrm-directory.css', array(), PLAIDACT_BREVES_FEED_VERSION );
		wp_register_script( 'plaidact-fluentcrm-directory', PLAIDACT_BREVES_FEED_URL . 'assets/js/fluentcrm-directory.js', array(), PLAIDACT_BREVES_FEED_VERSION, true );
	}

	public function register_admin_page(): void {
		add_submenu_page(
			'options-general.php',
			esc_html__( 'Listes publiques annuaire', 'plaidact-breves-feed' ),
			esc_html__( 'Annuaire FluentCRM', 'plaidact-breves-feed' ),
			'manage_options',
			'plaidact-fcd-settings',
			array( $this, 'render_admin_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'plaidact_fcd_settings',
			self::OPTION_PUBLIC_LISTS,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_public_lists' ),
				'default'           => array(),
			)
		);
	}

	public function sanitize_public_lists( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $value as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$list_id = isset( $item['list_id'] ) ? absint( $item['list_id'] ) : 0;
			if ( $list_id < 1 ) {
				continue;
			}

			$sanitized[] = array(
				'list_id'      => $list_id,
				'is_public'    => ! empty( $item['is_public'] ),
				'allow_export' => ! empty( $item['allow_export'] ),
				'description'  => isset( $item['description'] ) ? sanitize_textarea_field( (string) $item['description'] ) : '',
				'image_url'    => isset( $item['image_url'] ) ? esc_url_raw( (string) $item['image_url'] ) : '',
			);
		}

		return $sanitized;
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$lists     = $this->get_fluentcrm_lists();
		$config_by = $this->get_lists_config_by_id();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Annuaire FluentCRM', 'plaidact-breves-feed' ); ?></h1>
			<p><?php echo esc_html__( 'Choisissez les listes publiques, le téléchargement CSV et les contenus (image/description).', 'plaidact-breves-feed' ); ?></p>
			<p><?php echo esc_html__( 'Pour rendre une liste publique : cochez "Publique" sur la ligne de la liste puis cliquez sur "Enregistrer les modifications".', 'plaidact-breves-feed' ); ?></p>
			<p><?php echo esc_html__( 'Le répertoire est affiché via le shortcode [plaidact_fluentcrm_directory].', 'plaidact-breves-feed' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'plaidact_fcd_settings' ); ?>
				<table class="widefat striped">
					<thead><tr><th>Liste</th><th>Publique</th><th>Téléchargeable</th><th>Image</th><th>Description</th></tr></thead>
					<tbody>
					<?php if ( empty( $lists ) ) : ?>
						<tr><td colspan="5"><?php echo esc_html__( 'Aucune liste FluentCRM trouvée. Créez d’abord une liste dans FluentCRM.', 'plaidact-breves-feed' ); ?></td></tr>
					<?php endif; ?>
					<?php foreach ( $lists as $index => $list ) : ?>
						<?php $list_id = isset( $list->id ) ? absint( $list->id ) : 0; if ( $list_id < 1 ) { continue; }
						$cfg = $config_by[ $list_id ] ?? array(); ?>
						<tr>
							<td>
								<strong><?php echo esc_html( (string) ( $list->title ?? ( 'Liste #' . $list_id ) ) ); ?></strong>
								<input type="hidden" name="<?php echo esc_attr( self::OPTION_PUBLIC_LISTS ); ?>[<?php echo esc_attr( (string) $index ); ?>][list_id]" value="<?php echo esc_attr( (string) $list_id ); ?>" />
							</td>
							<td><input type="checkbox" name="<?php echo esc_attr( self::OPTION_PUBLIC_LISTS ); ?>[<?php echo esc_attr( (string) $index ); ?>][is_public]" value="1" <?php checked( ! empty( $cfg['is_public'] ) ); ?> /></td>
							<td><input type="checkbox" name="<?php echo esc_attr( self::OPTION_PUBLIC_LISTS ); ?>[<?php echo esc_attr( (string) $index ); ?>][allow_export]" value="1" <?php checked( ! empty( $cfg['allow_export'] ) ); ?> /></td>
							<td><input type="url" class="regular-text" name="<?php echo esc_attr( self::OPTION_PUBLIC_LISTS ); ?>[<?php echo esc_attr( (string) $index ); ?>][image_url]" value="<?php echo esc_attr( isset( $cfg['image_url'] ) ? (string) $cfg['image_url'] : '' ); ?>" placeholder="https://..." /></td>
							<td><textarea class="large-text" rows="2" name="<?php echo esc_attr( self::OPTION_PUBLIC_LISTS ); ?>[<?php echo esc_attr( (string) $index ); ?>][description]"><?php echo esc_textarea( isset( $cfg['description'] ) ? (string) $cfg['description'] : '' ); ?></textarea></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}


	private function get_fluentcrm_lists(): array {
		if ( ! function_exists( 'FluentCrmApi' ) ) {
			return array();
		}

		$api = FluentCrmApi( 'lists' );
		$lists = array();

		if ( is_object( $api ) ) {
			if ( method_exists( $api, 'all' ) ) {
				$lists = $api->all();
			} elseif ( method_exists( $api, 'get' ) ) {
				$lists = $api->get();
			}
		}

		// Compatibilité FluentCRM: certains contextes ne renvoient plus les listes via FluentCrmApi('lists').
		if ( empty( $lists ) && class_exists( '\\FluentCrm\\App\\Models\\Lists' ) ) {
			$lists = \FluentCrm\App\Models\Lists::query()->orderBy( 'id', 'asc' )->get();
		}

		if ( is_object( $lists ) && method_exists( $lists, 'toArray' ) ) {
			$lists = $lists->toArray();
		}

		if ( is_array( $lists ) && isset( $lists['data'] ) && is_array( $lists['data'] ) ) {
			$lists = $lists['data'];
		}

		if ( is_array( $lists ) && isset( $lists['lists'] ) && is_array( $lists['lists'] ) ) {
			$lists = $lists['lists'];
		}

		if ( ! is_iterable( $lists ) ) {
			return array();
		}

		$normalized = array();
		foreach ( $lists as $list ) {
			if ( is_array( $list ) ) {
				$list = (object) $list;
			}
			if ( is_object( $list ) ) {
				$normalized[] = $list;
			}
		}

		return $normalized;
	}

	private function get_contacts_for_list( int $list_id ): array {
		if ( $list_id < 1 || ! function_exists( 'FluentCrmApi' ) ) {
			return array();
		}

		$api = FluentCrmApi( 'contacts' );
		if ( ! is_object( $api ) ) {
			return array();
		}

		$queries = array();

		if ( method_exists( $api, 'filterByListIds' ) ) {
			$queries[] = $api->filterByListIds( array( $list_id ) );
		}

		if ( method_exists( $api, 'whereHas' ) ) {
			$queries[] = $api->whereHas(
				'lists',
				function ( $query_builder ) use ( $list_id ) {
					if ( is_object( $query_builder ) && method_exists( $query_builder, 'where' ) ) {
						$query_builder->where( 'lists.id', $list_id );
					}
				}
			);
		}

		foreach ( $queries as $query ) {
			if ( ! is_object( $query ) || ! method_exists( $query, 'get' ) ) {
				continue;
			}
			$contacts = $query->get();
			if ( is_iterable( $contacts ) ) {
				$contacts_array = (array) $contacts;
				if ( ! empty( $contacts_array ) ) {
					return $contacts_array;
				}
			}
		}

		return array();
	}

	public function render_shortcode(): string {
		if ( ! function_exists( 'FluentCrmApi' ) ) {
			return '<p>' . esc_html__( 'FluentCRM est requis pour afficher l\'annuaire.', 'plaidact-breves-feed' ) . '</p>';
		}

		$list_id = isset( $_GET['list_id'] ) ? absint( wp_unslash( $_GET['list_id'] ) ) : 0;
		wp_enqueue_style( 'plaidact-fluentcrm-directory' );
		if ( $list_id < 1 ) {
			return $this->render_lists_index();
		}

		$config = $this->get_public_list_config( $list_id );
		if ( null === $config ) {
			return '<p>' . esc_html__( 'Cette liste n’est pas publique.', 'plaidact-breves-feed' ) . '</p>';
		}

		wp_enqueue_script( 'plaidact-fluentcrm-directory' );
		$download_url = '';
		if ( ! empty( $config['allow_export'] ) ) {
			$download_url = add_query_arg( array( 'action' => self::DOWNLOAD_ACTION, 'list_id' => $list_id, 'nonce' => wp_create_nonce( self::NONCE_ACTION_DOWNLOAD . '_' . $list_id ) ), admin_url( 'admin-post.php' ) );
		}

		wp_localize_script( 'plaidact-fluentcrm-directory', 'PlaidactFluentcrmDirectory', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'subscribeNonce' => wp_create_nonce( self::NONCE_ACTION_SUBSCRIBE ), 'downloadUrl' => esc_url_raw( $download_url ) ) );

		return $this->render_list_members( $list_id, $download_url, ! empty( $config['allow_export'] ) );
	}

	private function render_lists_index(): string {
		$items  = $this->get_public_lists_with_details();
		$output = '<div class="plaidact-fcd plaidact-fcd-index"><div class="plaidact-fcd-directory__lead"><h3>' . esc_html__( 'Listes publiques', 'plaidact-breves-feed' ) . '</h3><p>' . esc_html__( 'Parcourez les répertoires de contacts disponibles publiquement.', 'plaidact-breves-feed' ) . '</p></div>';

		if ( empty( $items ) ) {
			return $output . '<p class="plaidact-fcd-empty">' . esc_html__( 'Aucune liste publique disponible pour le moment.', 'plaidact-breves-feed' ) . '</p></div>';
		}

		$output .= '<div class="plaidact-fcd-list-grid">';
		foreach ( $items as $item ) {
			$url = add_query_arg( 'list_id', (int) $item['list_id'] );
			$output .= '<article class="plaidact-fcd-list-card">';
			$output .= '<a class="plaidact-fcd-list-card__media" href="' . esc_url( $url ) . '">';
			if ( ! empty( $item['image_url'] ) ) {
				$output .= '<img class="plaidact-fcd-list-image" src="' . esc_url( (string) $item['image_url'] ) . '" alt="" loading="lazy" />';
			} else {
				$output .= '<span class="plaidact-fcd-list-image plaidact-fcd-list-image--placeholder" aria-hidden="true">PLAID·ACT</span>';
			}
			$output .= '</a><div class="plaidact-fcd-list-card__body"><h4><a href="' . esc_url( $url ) . '">' . esc_html( (string) $item['title'] ) . '</a></h4>';
			if ( ! empty( $item['description'] ) ) {
				$output .= '<p>' . esc_html( (string) $item['description'] ) . '</p>';
			}
			$output .= '<a class="plaidact-fcd-btn" href="' . esc_url( $url ) . '">' . esc_html__( 'Voir la liste', 'plaidact-breves-feed' ) . '</a>';
			$output .= '</div></article>';
		}
		$output .= '</div></div>';
		return $output;
	}


	private function render_list_members( int $list_id, string $download_url, bool $can_download ): string {
		$contacts = $this->get_contacts_for_list( $list_id );
		ob_start(); ?>
		<div class="plaidact-fcd plaidact-fcd-table-wrap"><table class="plaidact-fcd-table"><thead><tr><th><?php echo esc_html__( 'Prénom', 'plaidact-breves-feed' ); ?></th><th><?php echo esc_html__( 'Nom', 'plaidact-breves-feed' ); ?></th></tr></thead><tbody><?php foreach ( $contacts as $contact ) : ?><tr><td><?php echo esc_html( isset( $contact->first_name ) ? (string) $contact->first_name : '' ); ?></td><td><?php echo esc_html( isset( $contact->last_name ) ? (string) $contact->last_name : '' ); ?></td></tr><?php endforeach; ?></tbody></table><?php if ( $can_download ) : ?><button type="button" class="plaidact-fcd-download-btn" data-download-url="<?php echo esc_url( $download_url ); ?>"><?php echo esc_html__( 'Télécharger en CSV', 'plaidact-breves-feed' ); ?></button><?php endif; ?></div>
		<?php if ( $can_download ) : ?><div class="plaidact-fcd-modal" data-fcd-modal hidden><div class="plaidact-fcd-modal-content"><button type="button" class="plaidact-fcd-close" data-fcd-close>&times;</button><h3><?php echo esc_html__( 'Débloquez le téléchargement', 'plaidact-breves-feed' ); ?></h3><form class="plaidact-fcd-form" data-fcd-form><label for="plaidact-fcd-email"><?php echo esc_html__( 'Votre email', 'plaidact-breves-feed' ); ?></label><input type="email" id="plaidact-fcd-email" name="email" required /><button type="submit"><?php echo esc_html__( 'S’inscrire et télécharger', 'plaidact-breves-feed' ); ?></button><p class="plaidact-fcd-message" data-fcd-message></p></form></div></div><?php endif; ?>
		<?php return (string) ob_get_clean();
	}

	private function get_public_lists_with_details(): array {
		$config_by = $this->get_lists_config_by_id();
		$lists     = $this->get_fluentcrm_lists();
		$public    = array();
		foreach ( $lists as $list ) {
			$list_id = isset( $list->id ) ? absint( $list->id ) : 0;
			if ( $list_id < 1 || empty( $config_by[ $list_id ]['is_public'] ) ) { continue; }
			$public[] = array_merge( $config_by[ $list_id ], array( 'list_id' => $list_id, 'title' => (string) ( $list->title ?? ( 'Liste #' . $list_id ) ) ) );
		}
		return $public;
	}

	private function get_public_list_config( int $list_id ): ?array {
		$config_by = $this->get_lists_config_by_id();
		if ( empty( $config_by[ $list_id ]['is_public'] ) ) {
			return null;
		}
		return $config_by[ $list_id ];
	}

	private function get_lists_config_by_id(): array {
		$items = get_option( self::OPTION_PUBLIC_LISTS, array() );
		if ( ! is_array( $items ) ) { return array(); }
		$map = array();
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['list_id'] ) ) { continue; }
			$map[ absint( $item['list_id'] ) ] = $item;
		}
		return $map;
	}

	public function handle_subscribe(): void { /* unchanged */
		check_ajax_referer( self::NONCE_ACTION_SUBSCRIBE, 'nonce' );
		if ( ! function_exists( 'FluentCrmApi' ) ) { wp_send_json_error( array( 'message' => 'FluentCRM indisponible.' ), 500 ); }
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) { wp_send_json_error( array( 'message' => 'Email invalide.' ), 400 ); }
		$contact = FluentCrmApi( 'contacts' )->createOrUpdate( array( 'email' => $email, 'status' => 'subscribed' ) );
		if ( self::PROSPECT_LIST_ID > 0 && isset( $contact->id ) ) { FluentCrmApi( 'contacts' )->attachLists( $contact->id, array( self::PROSPECT_LIST_ID ) ); }
		wp_send_json_success( array( 'message' => 'Inscription validée.' ) );
	}

	public function handle_csv_download(): void {
		$list_id = isset( $_GET['list_id'] ) ? absint( wp_unslash( $_GET['list_id'] ) ) : 0;
		$nonce   = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
		if ( null === $this->get_public_list_config( $list_id ) || empty( $this->get_public_list_config( $list_id )['allow_export'] ) ) {
			wp_die( esc_html__( 'Téléchargement non autorisé pour cette liste.', 'plaidact-breves-feed' ) );
		}
		if ( $list_id < 1 || ! wp_verify_nonce( $nonce, self::NONCE_ACTION_DOWNLOAD . '_' . $list_id ) ) {
			wp_die( esc_html__( 'Lien de téléchargement invalide.', 'plaidact-breves-feed' ) );
		}
		$this->output_csv( $list_id );
	}

	private function output_csv( int $list_id ): void {
		if ( ! function_exists( 'FluentCrmApi' ) ) { wp_die( esc_html__( 'FluentCRM indisponible.', 'plaidact-breves-feed' ) ); }
		$contacts = $this->get_contacts_for_list( $list_id );
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=annuaire-liste-' . $list_id . '.csv' );
		$output = fopen( 'php://output', 'w' ); if ( false === $output ) { wp_die( esc_html__( 'Impossible de générer le CSV.', 'plaidact-breves-feed' ) ); }
		fputcsv( $output, array( 'Prénom', 'Nom' ) );
		foreach ( $contacts as $contact ) { fputcsv( $output, array( isset( $contact->first_name ) ? (string) $contact->first_name : '', isset( $contact->last_name ) ? (string) $contact->last_name : '' ) ); }
		fclose( $output ); exit;
	}
}
