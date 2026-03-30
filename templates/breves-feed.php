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
		<?php if ( ! empty( $args['is_ticker'] ) ) : ?>
			<div class="plaidact-breves-ticker" aria-live="polite">
				<div class="plaidact-breves-ticker__track">
					<?php
					for ( $loop = 0; $loop < 2; $loop++ ) :
						$query->rewind_posts();
						?>
						<div class="plaidact-breves-ticker__group" aria-hidden="<?php echo 1 === $loop ? 'true' : 'false'; ?>">
							<?php while ( $query->have_posts() ) : ?>
								<?php
								$query->the_post();
								$post_id   = get_the_ID();
								$link_data = $self->get_link_data( $post_id );
								?>
								<a
									class="plaidact-breves-ticker__item"
									href="<?php echo esc_url( (string) $link_data['url'] ); ?>"
									target="<?php echo esc_attr( (string) $link_data['target'] ); ?>"
									<?php if ( ! empty( $link_data['rel'] ) ) : ?>rel="<?php echo esc_attr( (string) $link_data['rel'] ); ?>"<?php endif; ?>
								>
									<time class="plaidact-breves-ticker__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>"><?php echo esc_html( get_the_date( 'j/m/Y', $post_id ) ); ?></time>
									<span class="plaidact-breves-ticker__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
								</a>
							<?php endwhile; ?>
						</div>
					<?php endfor; ?>
				</div>
			</div>
		<?php else : ?>
			<div class="plaidact-breves-list" role="list">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$self->load_template( 'breve-item.php', array( 'self' => $self ) );
				endwhile;
				?>
			</div>

			<?php echo wp_kses_post( $self->pagination( $query, $args ) ); ?>
		<?php endif; ?>
	<?php else : ?>
		<p class="plaidact-breves-feed__empty"><?php esc_html_e( 'Aucune brève disponible pour le moment.', 'plaidact-breves-feed' ); ?></p>
	<?php endif; ?>
</section>
