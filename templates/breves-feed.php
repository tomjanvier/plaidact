<?php
/**
 * Breves feed wrapper template.
 *
 * @var WP_Query              $query Query instance.
 * @var array<string,mixed>   $args  Render arguments.
 * @var PlaidAct_Breves_Feed  $self  Plugin instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="plaidact-breves-feed <?php echo esc_attr( (string) $args['container_class'] ); ?>" aria-label="<?php esc_attr_e( 'Fil d’actualité des brèves', 'plaidact-breves-feed' ); ?>">
	<?php if ( ! empty( $args['feed_title'] ) ) : ?>
		<header class="plaidact-breves-feed__header">
			<h2 class="plaidact-breves-feed__title"><?php echo esc_html( (string) $args['feed_title'] ); ?></h2>
		</header>
	<?php endif; ?>

	<?php if ( $query->have_posts() ) : ?>
		<div class="plaidact-breves-list" role="list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$self->load_template( 'breve-item.php', array( 'self' => $self ) );
			endwhile;
			?>
		</div>

		<?php echo wp_kses_post( $self->pagination( $query, $args ) ); ?>
	<?php else : ?>
		<p class="plaidact-breves-feed__empty"><?php esc_html_e( 'Aucune brève disponible pour le moment.', 'plaidact-breves-feed' ); ?></p>
	<?php endif; ?>
</section>
