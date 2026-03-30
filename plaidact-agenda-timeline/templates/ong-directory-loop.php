<?php

use PlaidAct\AgendaTimeline\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts_per_page = isset( $posts_per_page ) ? max( 1, absint( $posts_per_page ) ) : 9;
$filters = Plugin::get_ong_filters_from_request();
$query   = new WP_Query( Plugin::get_ong_query_args( $filters, $posts_per_page ) );
$causes  = get_terms( [ 'taxonomy' => 'cause', 'hide_empty' => false ] );
$formes  = get_terms( [ 'taxonomy' => 'forme_engagement', 'hide_empty' => false ] );
?>
<section class="plaidact-ong-directory" aria-label="<?php esc_attr_e( 'Répertoire des ONG', 'plaidact-timeline' ); ?>">
	<form method="get" class="plaidact-ong-filters">
		<div class="plaidact-ong-filter-grid">
			<label>
				<span><?php esc_html_e( 'Recherche', 'plaidact-timeline' ); ?></span>
				<input type="search" name="ong_s" value="<?php echo esc_attr( (string) $filters['s'] ); ?>" placeholder="<?php esc_attr_e( 'Nom, mot-clé…', 'plaidact-timeline' ); ?>" />
			</label>
			<label>
				<span><?php esc_html_e( 'Cause', 'plaidact-timeline' ); ?></span>
				<select name="ong_cause">
					<option value=""><?php esc_html_e( 'Toutes les causes', 'plaidact-timeline' ); ?></option>
					<?php foreach ( $causes as $cause ) : ?>
						<option value="<?php echo esc_attr( $cause->slug ); ?>" <?php selected( $filters['cause'], $cause->slug ); ?>><?php echo esc_html( $cause->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label>
				<span><?php esc_html_e( 'Forme d’engagement', 'plaidact-timeline' ); ?></span>
				<select name="ong_forme_engagement">
					<option value=""><?php esc_html_e( 'Toutes les formes', 'plaidact-timeline' ); ?></option>
					<?php foreach ( $formes as $forme ) : ?>
						<option value="<?php echo esc_attr( $forme->slug ); ?>" <?php selected( $filters['forme_engagement'], $forme->slug ); ?>><?php echo esc_html( $forme->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
		<div class="plaidact-ong-filter-actions">
			<button type="submit"><?php esc_html_e( 'Filtrer', 'plaidact-timeline' ); ?></button>
			<a href="<?php echo esc_url( remove_query_arg( [ 'ong_s', 'ong_cause', 'ong_forme_engagement', 'paged' ] ) ); ?>"><?php esc_html_e( 'Réinitialiser', 'plaidact-timeline' ); ?></a>
		</div>
	</form>

	<?php if ( $query->have_posts() ) : ?>
		<div class="plaidact-ong-grid">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php Plugin::render_template( 'parts/ong-card.php', [ 'card' => Plugin::get_ong_card_data( get_the_ID() ) ] ); ?>
			<?php endwhile; ?>
		</div>
		<div class="plaidact-ong-pagination">
			<?php
			echo wp_kses_post(
				paginate_links(
					[
						'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
						'format'    => '?paged=%#%',
						'current'   => max( 1, (int) $filters['paged'] ),
						'total'     => $query->max_num_pages,
						'add_args'  => [
							'ong_s'                => (string) $filters['s'],
							'ong_cause'            => (string) $filters['cause'],
							'ong_forme_engagement' => (string) $filters['forme_engagement'],
						],
					]
				)
			);
			?>
		</div>
	<?php else : ?>
		<p class="plaidact-ong-empty"><?php esc_html_e( 'Aucune ONG ne correspond à votre recherche.', 'plaidact-timeline' ); ?></p>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
</section>
