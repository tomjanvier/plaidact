<?php

use PlaidAct\AgendaSuite\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts_per_page = isset( $posts_per_page ) ? max( 1, absint( $posts_per_page ) ) : 9;
$filters = Plugin::get_asso_filters_from_request();
$query   = new WP_Query( Plugin::get_asso_query_args( $filters, $posts_per_page, isset( $fixed_cause ) ? (string) $fixed_cause : '' ) );
$causes  = get_terms( [ 'taxonomy' => 'cause', 'hide_empty' => false ] );
$fixed_cause = isset( $fixed_cause ) ? sanitize_title( (string) $fixed_cause ) : '';
$pagination_key = isset( $pagination_key ) && '' !== $pagination_key ? sanitize_key( (string) $pagination_key ) : 'asso_page';
?>
<section class="plaidact-asso-directory" aria-label="<?php esc_attr_e( 'Répertoire des associations', 'plaidact-breves-feed' ); ?>">
	<div class="plaidact-asso-directory__lead">
		<h2><?php esc_html_e( 'Répertoire des associations', 'plaidact-breves-feed' ); ?></h2>
		<p><?php esc_html_e( 'Trouvez une association par nom et par cause en quelques secondes.', 'plaidact-breves-feed' ); ?></p>
	</div>
	<form method="get" class="plaidact-asso-filters">
		<div class="plaidact-asso-filter-grid">
			<label>
				<span><?php esc_html_e( 'Recherche', 'plaidact-breves-feed' ); ?></span>
				<input type="search" name="asso_s" value="<?php echo esc_attr( (string) $filters['s'] ); ?>" placeholder="<?php esc_attr_e( 'Nom, mot-clé…', 'plaidact-breves-feed' ); ?>" />
			</label>
			<label>
				<span><?php esc_html_e( 'Cause', 'plaidact-breves-feed' ); ?></span>
				<select name="asso_cause" <?php disabled( '' !== $fixed_cause ); ?>>
					<option value=""><?php esc_html_e( 'Toutes les causes', 'plaidact-breves-feed' ); ?></option>
					<?php foreach ( $causes as $cause ) : ?>
						<option value="<?php echo esc_attr( $cause->slug ); ?>" <?php selected( $filters['cause'], $cause->slug ); ?>><?php echo esc_html( $cause->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php if ( '' !== $fixed_cause ) : ?>
				<input type="hidden" name="asso_cause" value="<?php echo esc_attr( $fixed_cause ); ?>" />
			<?php endif; ?>
		</div>
		<div class="plaidact-asso-filter-actions">
			<button type="submit"><?php esc_html_e( 'Filtrer', 'plaidact-breves-feed' ); ?></button>
			<a href="<?php echo esc_url( remove_query_arg( [ 'asso_s', 'asso_cause', $pagination_key, 'paged' ] ) ); ?>"><?php esc_html_e( 'Réinitialiser', 'plaidact-breves-feed' ); ?></a>
		</div>
	</form>

	<?php if ( $query->have_posts() ) : ?>
		<div class="plaidact-asso-grid">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php Plugin::render_template( 'parts/asso-card.php', [ 'card' => Plugin::get_asso_card_data( get_the_ID() ) ] ); ?>
			<?php endwhile; ?>
		</div>
		<div class="plaidact-asso-pagination">
			<?php
			echo wp_kses_post(
				paginate_links(
					[
						'base'      => esc_url( add_query_arg( $pagination_key, '%#%' ) ),
						'format'    => '',
						'current'   => max( 1, (int) $filters['paged'] ),
						'total'     => $query->max_num_pages,
						'add_args'  => [
							'asso_s' => (string) $filters['s'],
							'asso_cause' => '' !== $fixed_cause ? $fixed_cause : (string) $filters['cause'],
						],
					]
				)
			);
			?>
		</div>
	<?php else : ?>
		<p class="plaidact-asso-empty"><?php esc_html_e( 'Aucune association ne correspond à votre recherche.', 'plaidact-breves-feed' ); ?></p>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
</section>
