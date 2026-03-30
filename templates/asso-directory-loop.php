<?php

use PlaidAct\AgendaSuite\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts_per_page = isset( $posts_per_page ) ? max( 1, absint( $posts_per_page ) ) : 9;
$filters = Plugin::get_asso_filters_from_request();
$query   = new WP_Query( Plugin::get_asso_query_args( $filters, $posts_per_page, isset( $fixed_cause ) ? (string) $fixed_cause : '' ) );
$causes  = get_terms( [ 'taxonomy' => 'cause', 'hide_empty' => false ] );
?>
<section class="plaidact-asso-directory" aria-label="<?php esc_attr_e( 'Répertoire des associations', 'plaidact-breves-feed' ); ?>">
	<form method="get" class="plaidact-asso-filters">
		<div class="plaidact-asso-filter-grid">
			<label>
				<span><?php esc_html_e( 'Recherche', 'plaidact-breves-feed' ); ?></span>
				<input type="search" name="asso_s" value="<?php echo esc_attr( (string) $filters['s'] ); ?>" placeholder="<?php esc_attr_e( 'Nom, mot-clé…', 'plaidact-breves-feed' ); ?>" />
			</label>
			<label>
				<span><?php esc_html_e( 'Cause', 'plaidact-breves-feed' ); ?></span>
				<select name="asso_cause">
					<option value=""><?php esc_html_e( 'Toutes les causes', 'plaidact-breves-feed' ); ?></option>
					<?php foreach ( $causes as $cause ) : ?>
						<option value="<?php echo esc_attr( $cause->slug ); ?>" <?php selected( $filters['cause'], $cause->slug ); ?>><?php echo esc_html( $cause->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
		<div class="plaidact-asso-filter-actions">
			<button type="submit"><?php esc_html_e( 'Filtrer', 'plaidact-breves-feed' ); ?></button>
			<a href="<?php echo esc_url( remove_query_arg( [ 'asso_s', 'asso_cause', 'paged' ] ) ); ?>"><?php esc_html_e( 'Réinitialiser', 'plaidact-breves-feed' ); ?></a>
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
						'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
						'format'    => '?paged=%#%',
						'current'   => max( 1, (int) $filters['paged'] ),
						'total'     => $query->max_num_pages,
						'add_args'  => [
							'asso_s' => (string) $filters['s'],
							'asso_cause' => (string) $filters['cause'],
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
