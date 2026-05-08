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
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'wp_ajax_plaidact_fluentcrm_subscribe', array( $this, 'handle_subscribe' ) );
		add_action( 'wp_ajax_nopriv_plaidact_fluentcrm_subscribe', array( $this, 'handle_subscribe' ) );
		add_action( 'admin_post_' . self::DOWNLOAD_ACTION, array( $this, 'handle_csv_download' ) );
		add_action( 'admin_post_nopriv_' . self::DOWNLOAD_ACTION, array( $this, 'handle_csv_download' ) );
	}

	public function register_assets(): void {
		wp_register_style(
			'plaidact-fluentcrm-directory',
			PLAIDACT_BREVES_FEED_URL . 'assets/css/fluentcrm-directory.css',
			array(),
			PLAIDACT_BREVES_FEED_VERSION
		);

		wp_register_script(
			'plaidact-fluentcrm-directory',
			PLAIDACT_BREVES_FEED_URL . 'assets/js/fluentcrm-directory.js',
			array(),
			PLAIDACT_BREVES_FEED_VERSION,
			true
		);
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

		wp_enqueue_script( 'plaidact-fluentcrm-directory' );
		$download_url = add_query_arg(
			array(
				'action'  => self::DOWNLOAD_ACTION,
				'list_id' => $list_id,
				'nonce'   => wp_create_nonce( self::NONCE_ACTION_DOWNLOAD . '_' . $list_id ),
			),
			admin_url( 'admin-post.php' )
		);

		wp_localize_script(
			'plaidact-fluentcrm-directory',
			'PlaidactFluentcrmDirectory',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'subscribeNonce' => wp_create_nonce( self::NONCE_ACTION_SUBSCRIBE ),
				'downloadUrl'    => esc_url_raw( $download_url ),
			)
		);

		return $this->render_list_members( $list_id, $download_url );
	}

	private function render_lists_index(): string {
		$lists  = FluentCrmApi( 'lists' )->all();
		$output = '<div class="plaidact-fcd plaidact-fcd-index"><h3>' . esc_html__( 'Listes publiques', 'plaidact-breves-feed' ) . '</h3><ul>';

		foreach ( $lists as $list ) {
			$list_id = isset( $list->id ) ? absint( $list->id ) : 0;
			if ( $list_id < 1 ) {
				continue;
			}
			$url    = add_query_arg( 'list_id', $list_id );
			$label  = isset( $list->title ) ? (string) $list->title : '';
			$output .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
		}

		$output .= '</ul></div>';
		return $output;
	}

	private function render_list_members( int $list_id, string $download_url ): string {
		$contacts = FluentCrmApi( 'contacts' )
			->whereHas( 'lists', function ( $query ) use ( $list_id ) {
				$query->where( 'id', $list_id );
			} )
			->get();

		ob_start();
		?>
		<div class="plaidact-fcd plaidact-fcd-table-wrap">
			<table class="plaidact-fcd-table">
				<thead>
					<tr>
						<th><?php echo esc_html__( 'Prénom', 'plaidact-breves-feed' ); ?></th>
						<th><?php echo esc_html__( 'Nom', 'plaidact-breves-feed' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $contacts as $contact ) : ?>
						<tr>
							<td><?php echo esc_html( isset( $contact->first_name ) ? (string) $contact->first_name : '' ); ?></td>
							<td><?php echo esc_html( isset( $contact->last_name ) ? (string) $contact->last_name : '' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<button type="button" class="plaidact-fcd-download-btn" data-download-url="<?php echo esc_url( $download_url ); ?>">
				<?php echo esc_html__( 'Télécharger en CSV', 'plaidact-breves-feed' ); ?>
			</button>
		</div>

		<div class="plaidact-fcd-modal" data-fcd-modal hidden>
			<div class="plaidact-fcd-modal-content">
				<button type="button" class="plaidact-fcd-close" data-fcd-close>&times;</button>
				<h3><?php echo esc_html__( 'Débloquez le téléchargement', 'plaidact-breves-feed' ); ?></h3>
				<form class="plaidact-fcd-form" data-fcd-form>
					<label for="plaidact-fcd-email"><?php echo esc_html__( 'Votre email', 'plaidact-breves-feed' ); ?></label>
					<input type="email" id="plaidact-fcd-email" name="email" required />
					<button type="submit"><?php echo esc_html__( 'S’inscrire et télécharger', 'plaidact-breves-feed' ); ?></button>
					<p class="plaidact-fcd-message" data-fcd-message></p>
				</form>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public function handle_subscribe(): void {
		check_ajax_referer( self::NONCE_ACTION_SUBSCRIBE, 'nonce' );
		if ( ! function_exists( 'FluentCrmApi' ) ) {
			wp_send_json_error( array( 'message' => 'FluentCRM indisponible.' ), 500 );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => 'Email invalide.' ), 400 );
		}

		$contact = FluentCrmApi( 'contacts' )->createOrUpdate(
			array(
				'email'  => $email,
				'status' => 'subscribed',
			)
		);

		if ( self::PROSPECT_LIST_ID > 0 && isset( $contact->id ) ) {
			FluentCrmApi( 'contacts' )->attachLists( $contact->id, array( self::PROSPECT_LIST_ID ) );
		}

		wp_send_json_success( array( 'message' => 'Inscription validée.' ) );
	}

	public function handle_csv_download(): void {
		$list_id = isset( $_GET['list_id'] ) ? absint( wp_unslash( $_GET['list_id'] ) ) : 0;
		$nonce   = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
		if ( $list_id < 1 || ! wp_verify_nonce( $nonce, self::NONCE_ACTION_DOWNLOAD . '_' . $list_id ) ) {
			wp_die( esc_html__( 'Lien de téléchargement invalide.', 'plaidact-breves-feed' ) );
		}

		$this->output_csv( $list_id );
	}

	private function output_csv( int $list_id ): void {
		if ( ! function_exists( 'FluentCrmApi' ) ) {
			wp_die( esc_html__( 'FluentCRM indisponible.', 'plaidact-breves-feed' ) );
		}

		$contacts = FluentCrmApi( 'contacts' )
			->whereHas( 'lists', function ( $query ) use ( $list_id ) {
				$query->where( 'id', $list_id );
			} )
			->get();

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=annuaire-liste-' . $list_id . '.csv' );

		$output = fopen( 'php://output', 'w' );
		if ( false === $output ) {
			wp_die( esc_html__( 'Impossible de générer le CSV.', 'plaidact-breves-feed' ) );
		}

		fputcsv( $output, array( 'Prénom', 'Nom' ) );

		foreach ( $contacts as $contact ) {
			fputcsv(
				$output,
				array(
					isset( $contact->first_name ) ? (string) $contact->first_name : '',
					isset( $contact->last_name ) ? (string) $contact->last_name : '',
				)
			);
		}

		fclose( $output );
		exit;
	}
}
