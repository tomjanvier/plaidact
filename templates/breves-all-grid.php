<?php
/**
 * Brèves : grille 3 colonnes + pagination.
 *
 * @var WP_Query              $query
 * @var PlaidAct_Breves_Feed  $self
 * @var int                   $paged
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="plaidact-breves-grid-wrap" aria-label="<?php esc_attr_e( 'Toutes les brèves', 'plaidact-breves-feed' ); ?>">
	<?php if ( $query->have_posts() ) : ?>
		<div class="plaidact-breves-grid-3">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php
				$link = $self->get_link_data( get_the_ID() );
				?>
				<article class="plaidact-breve-grid-item">
					<h3><a href="<?php echo esc_url( (string) $link['url'] ); ?>" target="<?php echo esc_attr( (string) $link['target'] ); ?>" rel="<?php echo esc_attr( (string) $link['rel'] ); ?>"><?php the_title(); ?></a></h3>
					<p class="plaidact-breve-grid-date"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></p>
					<div class="plaidact-breve-grid-excerpt"><?php echo wp_kses_post( wpautop( wp_trim_words( wp_strip_all_tags( get_the_content() ), 32 ) ) ); ?></div>
				</article>
			<?php endwhile; ?>
		</div>
		<div class="plaidact-breves-feed">
			<?php
			echo wp_kses_post(
				$self->pagination(
					$query,
					array(
						'paged'          => $paged,
						'pagination_var' => 'breves_all_page',
					)
				)
			);
			?>
		</div>
	<?php else : ?>
		<p class="plaidact-breves-feed__empty"><?php esc_html_e( 'Aucune brève disponible pour le moment.', 'plaidact-breves-feed' ); ?></p>
	<?php endif; ?>
</section>
