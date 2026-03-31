<?php
/**
 * Single breve card.
 *
 * @var PlaidAct_Breves_Feed $self Plugin instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id   = get_the_ID();
$link_data = $self->get_link_data( $post_id );
$theme     = function_exists( 'get_field' ) ? (string) get_field( 'thematique_libre', $post_id ) : '';
$excerpt   = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post_id ) ), 24, '…' );
?>
<article class="plaidact-breve" role="listitem">
	<a
		class="plaidact-breve__link"
		href="<?php echo esc_url( (string) $link_data['url'] ); ?>"
		target="<?php echo esc_attr( (string) $link_data['target'] ); ?>"
		<?php if ( ! empty( $link_data['rel'] ) ) : ?>rel="<?php echo esc_attr( (string) $link_data['rel'] ); ?>"<?php endif; ?>
	>
		<p class="plaidact-breve__meta">
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>">
				<?php echo esc_html( get_the_date( 'j F Y', $post_id ) ); ?>
			</time>
			<?php if ( '' !== trim( $theme ) ) : ?>
				<span class="plaidact-breve__theme"><?php echo esc_html( $theme ); ?></span>
			<?php endif; ?>
		</p>

		<h3 class="plaidact-breve__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>

		<?php if ( '' !== $excerpt ) : ?>
			<p class="plaidact-breve__excerpt"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $link_data['is_external'] ) ) : ?>
			<span class="plaidact-breve__external"><?php esc_html_e( 'En savoir plus', 'plaidact-breves-feed' ); ?></span>
		<?php endif; ?>
	</a>
</article>
