<?php
/**
 * Public contacts directory (no FluentCRM dependency).
 *
 * @package PlaidAct_Breves_Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PlaidAct_FluentCRM_Directory {
	private const SHORTCODE             = 'plaidact_contact_directory';
	private const LEGACY_SHORTCODE      = 'plaidact_fluentcrm_directory';
	private const OPTION_CONTACT_LISTS  = 'plaidact_contact_directory_lists';
	private const NONCE_IMPORT          = 'plaidact_contact_directory_import';
	private const NONCE_DOWNLOAD_PREFIX = 'plaidact_contact_directory_download_';
	private const DOWNLOAD_ACTION       = 'plaidact_contact_directory_download';
	private const OPTION_VISIBLE_COLUMNS = 'plaidact_contact_directory_visible_columns';

	private static ?PlaidAct_FluentCRM_Directory $instance = null;

	public static function init(): PlaidAct_FluentCRM_Directory {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( self::SHORTCODE, array( $this, 'render_shortcode' ) );
		add_shortcode( self::LEGACY_SHORTCODE, array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_post_' . self::DOWNLOAD_ACTION, array( $this, 'handle_csv_download' ) );
	}

	public function register_assets(): void {
		wp_register_style( 'plaidact-fluentcrm-directory', PLAIDACT_BREVES_FEED_URL . 'assets/css/fluentcrm-directory.css', array(), PLAIDACT_BREVES_FEED_VERSION );
		wp_register_script( 'plaidact-contact-directory', PLAIDACT_BREVES_FEED_URL . 'assets/js/contact-directory.js', array(), PLAIDACT_BREVES_FEED_VERSION, true );
	}

	public function register_admin_page(): void {
		add_menu_page(
			esc_html__( 'Répertoire contacts', 'plaidact-breves-feed' ),
			esc_html__( 'Répertoire contacts', 'plaidact-breves-feed' ),
			'manage_options',
			'plaidact-contact-directory',
			array( $this, 'render_admin_page' ),
			'dashicons-id-alt',
			58
		);
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->handle_admin_postbacks();
		$lists = $this->get_lists();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Répertoire de contacts', 'plaidact-breves-feed' ); ?></h1>
			<p><?php echo esc_html__( 'Créez des listes, importez vos CSV et affichez-les sur le site avec le shortcode [plaidact_contact_directory].', 'plaidact-breves-feed' ); ?></p>
			<?php $visible_columns = $this->get_visible_columns(); ?>
			<h2><?php echo esc_html__( 'Colonnes à afficher (front office)', 'plaidact-breves-feed' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( self::NONCE_IMPORT ); ?>
				<input type="hidden" name="plaidact_contact_action" value="update_visible_columns" />
				<p>
					<label><input type="checkbox" name="visible_columns[]" value="groupe" <?php checked( in_array( 'groupe', $visible_columns, true ) ); ?> /> <?php echo esc_html__( 'Groupe politique', 'plaidact-breves-feed' ); ?></label><br />
					<label><input type="checkbox" name="visible_columns[]" value="commission" <?php checked( in_array( 'commission', $visible_columns, true ) ); ?> /> <?php echo esc_html__( 'Commission', 'plaidact-breves-feed' ); ?></label><br />
					<label><input type="checkbox" name="visible_columns[]" value="custom" <?php checked( in_array( 'custom', $visible_columns, true ) ); ?> /> <?php echo esc_html__( 'Fonction', 'plaidact-breves-feed' ); ?></label><br />
					<label><input type="checkbox" name="visible_columns[]" value="social" <?php checked( in_array( 'social', $visible_columns, true ) ); ?> /> <?php echo esc_html__( 'Réseaux sociaux', 'plaidact-breves-feed' ); ?></label>
				</p>
				<?php submit_button( __( 'Enregistrer les colonnes', 'plaidact-breves-feed' ), 'secondary' ); ?>
			</form>

			<h2><?php echo esc_html__( 'Nouvelle liste', 'plaidact-breves-feed' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( self::NONCE_IMPORT ); ?>
				<input type="hidden" name="plaidact_contact_action" value="create_list" />
				<table class="form-table"><tbody>
					<tr><th scope="row"><?php echo esc_html__( 'Nom de la liste', 'plaidact-breves-feed' ); ?></th><td><input type="text" class="regular-text" name="list_name" required /></td></tr>
					<tr><th scope="row"><?php echo esc_html__( 'Libellé colonne personnalisée', 'plaidact-breves-feed' ); ?></th><td><input type="text" class="regular-text" name="column_label" placeholder="Fonction ou Groupe politique" required /></td></tr>
					<tr><th scope="row"><?php echo esc_html__( 'Description', 'plaidact-breves-feed' ); ?></th><td><textarea class="large-text" rows="2" name="description"></textarea></td></tr>
					<tr><th scope="row"><?php echo esc_html__( 'Image (URL)', 'plaidact-breves-feed' ); ?></th><td><input type="url" class="regular-text" name="image_url" placeholder="https://..." /></td></tr>
				</tbody></table>
				<?php submit_button( __( 'Créer la liste', 'plaidact-breves-feed' ) ); ?>
			</form>

			<h2><?php echo esc_html__( 'Listes existantes', 'plaidact-breves-feed' ); ?></h2>
			<?php if ( empty( $lists ) ) : ?>
				<p><?php echo esc_html__( 'Aucune liste pour le moment.', 'plaidact-breves-feed' ); ?></p>
			<?php else : ?>
				<?php foreach ( $lists as $list ) : ?>
					<div style="background:#fff;border:1px solid #ddd;padding:16px;margin-bottom:14px;">
						<h3><?php echo esc_html( $list['name'] ); ?></h3>
						<p><strong><?php echo esc_html__( 'Colonne personnalisée :', 'plaidact-breves-feed' ); ?></strong> <?php echo esc_html( $list['column_label'] ); ?></p>
						<p><?php echo esc_html( $list['description'] ); ?></p>
						<p><strong><?php echo esc_html__( 'Contacts :', 'plaidact-breves-feed' ); ?></strong> <?php echo esc_html( (string) count( $list['contacts'] ) ); ?></p>
						<p><strong><?php echo esc_html__( 'Dernière mise à jour :', 'plaidact-breves-feed' ); ?></strong> <?php echo esc_html( ! empty( $list['updated_at'] ) ? wp_date( 'd/m/Y H:i', (int) $list['updated_at'] ) : '—' ); ?></p>
						<p><a class="button button-secondary" href="<?php echo esc_url( add_query_arg( array( 'action' => self::DOWNLOAD_ACTION, 'list_id' => (int) $list['id'], 'nonce' => wp_create_nonce( self::NONCE_DOWNLOAD_PREFIX . $list['id'] ) ), admin_url( 'admin-post.php' ) ) ); ?>"><?php echo esc_html__( 'Télécharger cette liste (CSV)', 'plaidact-breves-feed' ); ?></a></p>
						<form method="post" style="margin-top:8px;">
							<?php wp_nonce_field( self::NONCE_IMPORT ); ?>
							<input type="hidden" name="plaidact_contact_action" value="update_list_meta" />
							<input type="hidden" name="list_id" value="<?php echo esc_attr( (string) $list['id'] ); ?>" />
							<p><label><strong><?php echo esc_html__( 'Nom de la liste', 'plaidact-breves-feed' ); ?></strong><br /><input type="text" class="regular-text" name="list_name" value="<?php echo esc_attr( (string) $list['name'] ); ?>" required /></label></p>
							<p><label><strong><?php echo esc_html__( 'Libellé colonne personnalisée', 'plaidact-breves-feed' ); ?></strong><br /><input type="text" class="regular-text" name="column_label" value="<?php echo esc_attr( (string) $list['column_label'] ); ?>" required /></label></p>
							<p><label><strong><?php echo esc_html__( 'Description', 'plaidact-breves-feed' ); ?></strong><br /><textarea class="large-text" rows="2" name="description"><?php echo esc_textarea( (string) ( $list['description'] ?? '' ) ); ?></textarea></label></p>
							<p><label><strong><?php echo esc_html__( 'Image (URL)', 'plaidact-breves-feed' ); ?></strong><br /><input type="url" class="regular-text" name="image_url" value="<?php echo esc_attr( (string) ( $list['image_url'] ?? '' ) ); ?>" placeholder="https://..." /></label></p>
							<?php submit_button( __( 'Mettre à jour la liste', 'plaidact-breves-feed' ), 'secondary', 'submit', false ); ?>
						</form>
						<form method="post" enctype="multipart/form-data" style="margin-top:8px;">
							<?php wp_nonce_field( self::NONCE_IMPORT ); ?>
							<input type="hidden" name="plaidact_contact_action" value="import_csv" />
							<input type="hidden" name="list_id" value="<?php echo esc_attr( (string) $list['id'] ); ?>" />
							<input type="file" name="contacts_csv" accept=".csv,text/csv" required />
							<button type="submit" class="button button-secondary"><?php echo esc_html__( 'Importer CSV', 'plaidact-breves-feed' ); ?></button>
						</form>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	private function handle_admin_postbacks(): void {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['plaidact_contact_action'] ) ) {
			return;
		}
		check_admin_referer( self::NONCE_IMPORT );
		$action = sanitize_key( wp_unslash( $_POST['plaidact_contact_action'] ) );
		if ( 'create_list' === $action ) {
			$this->create_list_from_request();
		}
		if ( 'import_csv' === $action ) {
			$this->import_csv_from_request();
		}
		if ( 'update_list_meta' === $action ) {
			$this->update_list_meta_from_request();
		}
		if ( 'update_visible_columns' === $action ) {
			$this->update_visible_columns_from_request();
		}
	}

	private function create_list_from_request(): void {
		$name         = isset( $_POST['list_name'] ) ? sanitize_text_field( wp_unslash( $_POST['list_name'] ) ) : '';
		$column_label = isset( $_POST['column_label'] ) ? sanitize_text_field( wp_unslash( $_POST['column_label'] ) ) : '';
		$description  = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$image_url    = isset( $_POST['image_url'] ) ? esc_url_raw( wp_unslash( $_POST['image_url'] ) ) : '';
		if ( '' === $name || '' === $column_label ) {
			return;
		}
		$lists   = $this->get_lists();
		$lists[] = array(
			'id'           => time() + wp_rand( 1, 9999 ),
			'name'         => $name,
			'column_label' => $column_label,
			'description'  => $description,
			'image_url'    => $image_url,
			'updated_at'   => time(),
			'contacts'     => array(),
		);
		update_option( self::OPTION_CONTACT_LISTS, $lists, false );
	}


	private function update_list_meta_from_request(): void {
		$list_id      = isset( $_POST['list_id'] ) ? absint( wp_unslash( $_POST['list_id'] ) ) : 0;
		$name         = isset( $_POST['list_name'] ) ? sanitize_text_field( wp_unslash( $_POST['list_name'] ) ) : '';
		$column_label = isset( $_POST['column_label'] ) ? sanitize_text_field( wp_unslash( $_POST['column_label'] ) ) : '';
		$description  = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$image_url    = isset( $_POST['image_url'] ) ? esc_url_raw( wp_unslash( $_POST['image_url'] ) ) : '';
		if ( $list_id < 1 || '' === $name || '' === $column_label ) {
			return;
		}
		$lists = $this->get_lists();
		foreach ( $lists as &$list ) {
			if ( $list_id !== (int) $list['id'] ) {
				continue;
			}
			$list['name']         = $name;
			$list['column_label'] = $column_label;
			$list['description']  = $description;
			$list['image_url']    = $image_url;
			$list['updated_at'] = time();
			break;
		}
		unset( $list );
		update_option( self::OPTION_CONTACT_LISTS, $lists, false );
	}

	private function import_csv_from_request(): void {
		$list_id = isset( $_POST['list_id'] ) ? absint( wp_unslash( $_POST['list_id'] ) ) : 0;
		if ( $list_id < 1 || empty( $_FILES['contacts_csv']['tmp_name'] ) ) {
			return;
		}
		$rows = $this->parse_csv_contacts( $_FILES['contacts_csv']['tmp_name'] );
		if ( empty( $rows ) ) {
			return;
		}
		$lists = $this->get_lists();
		foreach ( $lists as &$list ) {
			if ( $list_id !== (int) $list['id'] ) {
				continue;
			}
			$list['contacts']   = $rows;
			$list['updated_at'] = time();
			break;
		}
		unset( $list );
		update_option( self::OPTION_CONTACT_LISTS, $lists, false );
	}

	private function parse_csv_contacts( string $tmp_path ): array {
		$handle = fopen( $tmp_path, 'r' );
		if ( false === $handle ) {
			return array();
		}
		$rows    = array();
		$headers = fgetcsv( $handle );
		if ( ! is_array( $headers ) ) {
			fclose( $handle );
			return array();
		}
		$headers = array_map( static function( $header ) {
			$header = is_string( $header ) ? trim( $header ) : '';
			$header = preg_replace( '/^\xEF\xBB\xBF/u', '', $header );
			return $header;
		}, $headers );
		while ( ( $line = fgetcsv( $handle ) ) !== false ) {
			$data = array_combine( $headers, $line );
			if ( ! is_array( $data ) ) {
				continue;
			}
			$rows[] = array(
				'nom'          => sanitize_text_field( (string) ( $data['Nom'] ?? $data['nom'] ?? ( $line[0] ?? '' ) ) ),
				'prenom'       => sanitize_text_field( (string) ( $data['Prénom'] ?? $data['Prenom'] ?? $data['prenom'] ?? '' ) ),
				'custom'       => sanitize_text_field( (string) ( $data['Fonction'] ?? $data['Groupe politique'] ?? '' ) ),
				'institution'  => sanitize_text_field( (string) ( $data['Institution'] ?? '' ) ),
				'groupe'       => sanitize_text_field( (string) ( $data['Groupe politique'] ?? '' ) ),
				'commission'   => sanitize_text_field( (string) ( $data['Commission'] ?? '' ) ),
				'social_links' => $this->parse_social_links( $data ),
				'email'        => sanitize_email( (string) ( $data['Email'] ?? '' ) ),
				'notes'        => sanitize_textarea_field( (string) ( $data['Notes'] ?? '' ) ),
			);
		}
		fclose( $handle );
		return $rows;
	}


	private function parse_social_links( array $data ): array {
		$platforms = array(
			'x'         => $data['X'] ?? $data['Twitter'] ?? '',
			'linkedin'  => $data['LinkedIn'] ?? '',
			'facebook'  => $data['Facebook'] ?? '',
			'instagram' => $data['Instagram'] ?? '',
			'youtube'   => $data['YouTube'] ?? '',
		);
		$links = array();
		foreach ( $platforms as $key => $value ) {
			$value = is_string( $value ) ? trim( $value ) : '';
			if ( '' === $value ) {
				continue;
			}
			$links[ $key ] = esc_url_raw( $value );
		}
		return $links;
	}


	private function get_visible_columns(): array {
		$default = array( 'groupe', 'commission', 'custom', 'social' );
		$visible = get_option( self::OPTION_VISIBLE_COLUMNS, $default );
		if ( ! is_array( $visible ) ) {
			return $default;
		}
		$allowed = array( 'groupe', 'commission', 'custom', 'social' );
		$visible = array_values( array_intersect( $allowed, $visible ) );
		return empty( $visible ) ? $default : $visible;
	}

	private function update_visible_columns_from_request(): void {
		$columns = isset( $_POST['visible_columns'] ) ? (array) wp_unslash( $_POST['visible_columns'] ) : array();
		$columns = array_map( 'sanitize_key', $columns );
		$allowed = array( 'groupe', 'commission', 'custom', 'social' );
		$columns = array_values( array_intersect( $allowed, $columns ) );
		if ( empty( $columns ) ) {
			$columns = $allowed;
		}
		update_option( self::OPTION_VISIBLE_COLUMNS, $columns, false );
	}


	public function render_shortcode(): string {
		wp_enqueue_style( 'plaidact-fluentcrm-directory' );
		wp_enqueue_script( 'plaidact-contact-directory' );
		$lists = $this->get_lists();
		if ( empty( $lists ) ) {
			return '<p class="plaidact-fcd-empty">' . esc_html__( 'Aucune liste de contacts disponible.', 'plaidact-breves-feed' ) . '</p>';
		}
		$current         = null;
		$current_slug    = isset( $_GET['list'] ) ? sanitize_title( wp_unslash( $_GET['list'] ) ) : '';
		$current_list_id = isset( $_GET['list_id'] ) ? absint( wp_unslash( $_GET['list_id'] ) ) : 0;
		$show_all_lists  = '' === $current_slug && 0 === $current_list_id;
		if ( '' !== $current_slug ) {
			$current = $this->get_list_by_slug( $current_slug );
		}
		if ( null === $current && $current_list_id > 0 ) {
			$current = $this->get_list_by_id( $current_list_id );
		}
		if ( ! $show_all_lists && null === $current ) {
			$current = $lists[0];
		}

		$displayed_contacts = array();
		if ( $show_all_lists ) {
			foreach ( $lists as $list ) {
				foreach ( $list['contacts'] as $contact ) {
					$contact['list_name'] = $list['name'];
					$displayed_contacts[] = $contact;
				}
			}
		} elseif ( null !== $current ) {
			foreach ( $current['contacts'] as $contact ) {
				$contact['list_name'] = $current['name'];
				$displayed_contacts[] = $contact;
			}
		}

		$custom_values     = $this->get_unique_contact_values( $displayed_contacts, 'custom' );
		$commission_values = $this->get_unique_contact_values( $displayed_contacts, 'commission' );
		$groupe_values     = $this->get_unique_contact_values( $displayed_contacts, 'groupe' );
		$visible_columns   = $this->get_visible_columns();

		$download_url = null;
		if ( ! $show_all_lists && null !== $current ) {
			$download_url = add_query_arg(
			array(
				'action'  => self::DOWNLOAD_ACTION,
				'list_id' => (int) $current['id'],
				'nonce'   => wp_create_nonce( self::NONCE_DOWNLOAD_PREFIX . $current['id'] ),
			),
			admin_url( 'admin-post.php' )
		);
		}

		ob_start(); ?>
		<div class="plaidact-fcd">
			<div class="plaidact-fcd-directory__lead">
				<h3><?php echo esc_html__( 'Répertoire de contacts', 'plaidact-breves-feed' ); ?></h3>
				<p><?php echo esc_html__( 'Choisissez une liste, recherchez un contact et exportez le tableau en CSV.', 'plaidact-breves-feed' ); ?></p>
			</div>
			<?php if ( null !== $download_url ) : ?>
				<a class="plaidact-fcd-btn plaidact-fcd-btn--download" href="<?php echo esc_url( $download_url ); ?>"><?php echo esc_html__( 'Télécharger la liste CSV', 'plaidact-breves-feed' ); ?></a>
			<?php endif; ?>
			<?php if ( ! $show_all_lists ) : ?>
				<a class="plaidact-fcd-btn plaidact-fcd-btn--ghost" href="<?php echo esc_url( remove_query_arg( array( 'list', 'list_id' ) ) ); ?>"><?php echo esc_html__( 'Retour à la liste complète', 'plaidact-breves-feed' ); ?></a>
			<?php endif; ?>
			<div class="plaidact-fcd-list-grid" role="tablist" aria-label="<?php echo esc_attr__( 'Listes de contacts', 'plaidact-breves-feed' ); ?>">
				<?php foreach ( $lists as $list ) : ?>
					<?php $list_is_active = (int) $list['id'] === (int) $current['id']; ?>
					<a class="plaidact-fcd-list-card <?php echo $list_is_active ? 'is-active' : ''; ?>" aria-current="<?php echo $list_is_active ? 'page' : 'false'; ?>" href="<?php echo esc_url( add_query_arg( 'list', sanitize_title( (string) $list['name'] ) ) ); ?>">
						<?php if ( ! empty( $list['image_url'] ) ) : ?>
							<img class="plaidact-fcd-list-image" src="<?php echo esc_url( $list['image_url'] ); ?>" alt="" />
						<?php else : ?>
							<div class="plaidact-fcd-list-image"><?php echo esc_html( mb_substr( (string) $list['name'], 0, 1 ) ); ?></div>
						<?php endif; ?>
						<div class="plaidact-fcd-list-card__body"><h4><?php echo esc_html( $list['name'] ); ?></h4><p><?php echo esc_html( $list['description'] ); ?></p><small><?php echo esc_html__( 'Mise à jour :', 'plaidact-breves-feed' ); ?> <?php echo esc_html( ! empty( $list['updated_at'] ) ? wp_date( 'd/m/Y', (int) $list['updated_at'] ) : '—' ); ?></small></div></a>
				<?php endforeach; ?>
			</div>
			<div class="plaidact-fcd-table-wrap">
			<table class="plaidact-fcd-table">
				<thead>
					<tr>
						<th><?php echo esc_html__( 'Nom', 'plaidact-breves-feed' ); ?></th>
						<th><?php echo esc_html__( 'Prénom', 'plaidact-breves-feed' ); ?></th>
						<th><?php echo esc_html__( 'Liste', 'plaidact-breves-feed' ); ?></th>
						<th><?php echo esc_html__( 'Email', 'plaidact-breves-feed' ); ?></th>
						<?php if ( in_array( 'groupe', $visible_columns, true ) ) : ?><th data-column="groupe"><?php echo esc_html__( 'Groupe politique', 'plaidact-breves-feed' ); ?></th><?php endif; ?>
						<?php if ( in_array( 'commission', $visible_columns, true ) ) : ?><th data-column="commission"><?php echo esc_html__( 'Commission', 'plaidact-breves-feed' ); ?></th><?php endif; ?>
						<?php if ( in_array( 'custom', $visible_columns, true ) ) : ?><th data-column="custom"><?php echo esc_html__( 'Fonction', 'plaidact-breves-feed' ); ?></th><?php endif; ?>
						<?php if ( in_array( 'social', $visible_columns, true ) ) : ?><th data-column="social"><?php echo esc_html__( 'Réseaux sociaux', 'plaidact-breves-feed' ); ?></th><?php endif; ?>
						<th><?php echo esc_html__( 'Notes', 'plaidact-breves-feed' ); ?></th>
					</tr>
					<tr class="plaidact-fcd-filter-row">
						<th><input type="search" class="plaidact-fcd-search" placeholder="<?php echo esc_attr__( 'Rechercher…', 'plaidact-breves-feed' ); ?>" /></th>
						<th></th><th></th><th></th>
						<?php if ( in_array( 'groupe', $visible_columns, true ) ) : ?><th><select class="plaidact-fcd-select-filter" data-filter="groupe"><option value=""><?php echo esc_html__( 'Tous', 'plaidact-breves-feed' ); ?></option><?php foreach ( $groupe_values as $value ) : ?><option value="<?php echo esc_attr( strtolower( $value ) ); ?>"><?php echo esc_html( $value ); ?></option><?php endforeach; ?></select></th><?php endif; ?>
						<?php if ( in_array( 'commission', $visible_columns, true ) ) : ?><th><select class="plaidact-fcd-select-filter" data-filter="commission"><option value=""><?php echo esc_html__( 'Toutes', 'plaidact-breves-feed' ); ?></option><?php foreach ( $commission_values as $value ) : ?><option value="<?php echo esc_attr( strtolower( $value ) ); ?>"><?php echo esc_html( $value ); ?></option><?php endforeach; ?></select></th><?php endif; ?>
						<?php if ( in_array( 'custom', $visible_columns, true ) ) : ?><th><select class="plaidact-fcd-select-filter" data-filter="custom"><option value=""><?php echo esc_html__( 'Toutes', 'plaidact-breves-feed' ); ?></option><?php foreach ( $custom_values as $value ) : ?><option value="<?php echo esc_attr( strtolower( $value ) ); ?>"><?php echo esc_html( $value ); ?></option><?php endforeach; ?></select></th><?php endif; ?>
						<?php if ( in_array( 'social', $visible_columns, true ) ) : ?><th></th><?php endif; ?>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $displayed_contacts as $contact ) : ?>
					<tr data-custom="<?php echo esc_attr( strtolower( (string) ( $contact['custom'] ?? '' ) ) ); ?>" data-groupe="<?php echo esc_attr( strtolower( (string) ( $contact['groupe'] ?? '' ) ) ); ?>" data-commission="<?php echo esc_attr( strtolower( (string) ( $contact['commission'] ?? '' ) ) ); ?>">
						<td><?php echo esc_html( $contact['nom'] ); ?></td><td><?php echo esc_html( $contact['prenom'] ); ?></td><td><?php echo esc_html( $contact['list_name'] ?? '' ); ?></td><td><?php echo esc_html( $contact['email'] ); ?></td><?php if ( in_array( 'groupe', $visible_columns, true ) ) : ?><td><?php echo esc_html( $contact['groupe'] ?? '' ); ?></td><?php endif; ?><?php if ( in_array( 'commission', $visible_columns, true ) ) : ?><td><?php echo esc_html( $contact['commission'] ?? '' ); ?></td><?php endif; ?><?php if ( in_array( 'custom', $visible_columns, true ) ) : ?><td><?php echo esc_html( $contact['custom'] ); ?></td><?php endif; ?><?php if ( in_array( 'social', $visible_columns, true ) ) : ?><td><?php echo $this->render_social_links( $contact['social_links'] ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td><?php endif; ?><td><?php echo esc_html( $contact['notes'] ?? '' ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			</div>
			</div>

		</div>
		<?php
		return (string) ob_get_clean();
	}

	private function render_social_links( array $links ): string {
		if ( empty( $links ) ) {
			return '—';
		}
		$html = '<div class="plaidact-fcd-social">';
		foreach ( $links as $platform => $url ) {
			if ( '' === $url ) {
				continue;
			}
			$html .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( ucfirst( $platform ) ) . '</a>';
		}
		$html .= '</div>';
		return $html;
	}

	public function handle_csv_download(): void {
		$list_id = isset( $_GET['list_id'] ) ? absint( wp_unslash( $_GET['list_id'] ) ) : 0;
		$nonce   = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
		$list    = $this->get_list_by_id( $list_id );
		if ( null === $list || ! wp_verify_nonce( $nonce, self::NONCE_DOWNLOAD_PREFIX . $list_id ) ) {
			wp_die( esc_html__( 'Lien de téléchargement invalide.', 'plaidact-breves-feed' ) );
		}
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=contacts-' . $list_id . '.csv' );
		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'Nom', 'Prénom', $list['column_label'], 'Institution', 'Groupe politique', 'Commission', 'Email', 'Notes' ) );
		foreach ( $list['contacts'] as $contact ) {
			fputcsv( $output, array( $contact['nom'], $contact['prenom'], $contact['custom'], $contact['institution'] ?? '', $contact['groupe'] ?? '', $contact['commission'] ?? '', $contact['email'], $contact['notes'] ) );
		}
		fclose( $output );
		exit;
	}

	private function get_lists(): array {
		$lists = get_option( self::OPTION_CONTACT_LISTS, array() );
		return is_array( $lists ) ? $lists : array();
	}

	private function get_list_by_id( int $list_id ): ?array {
		foreach ( $this->get_lists() as $list ) {
			if ( $list_id === (int) $list['id'] ) {
				return $list;
			}
		}
		return null;
	}

	private function get_list_by_slug( string $list_slug ): ?array {
		foreach ( $this->get_lists() as $list ) {
			if ( $list_slug === sanitize_title( (string) ( $list['name'] ?? '' ) ) ) {
				return $list;
			}
		}
		return null;
	}

	private function get_unique_contact_values( array $contacts, string $key ): array {
		$values = array();
		foreach ( $contacts as $contact ) {
			$value = trim( (string) ( $contact[ $key ] ?? '' ) );
			if ( '' === $value ) {
				continue;
			}
			$values[ $value ] = $value;
		}
		return array_values( $values );
	}
}
