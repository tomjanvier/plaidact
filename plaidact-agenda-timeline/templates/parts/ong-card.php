<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$card = $card ?? [];
?>
<article class="plaidact-ong-card">
	<a class="plaidact-ong-card__media" href="<?php echo esc_url( (string) $card['permalink'] ); ?>">
		<?php if ( has_post_thumbnail( (int) $card['post_id'] ) ) : ?>
			<?php echo get_the_post_thumbnail( (int) $card['post_id'], 'medium_large', [ 'loading' => 'lazy' ] ); ?>
		<?php else : ?>
			<span class="plaidact-ong-card__placeholder" aria-hidden="true">PLAID·ACT</span>
		<?php endif; ?>
	</a>
	<div class="plaidact-ong-card__body">
		<h3><a href="<?php echo esc_url( (string) $card['permalink'] ); ?>"><?php echo esc_html( (string) $card['title'] ); ?></a></h3>
		<?php if ( ! empty( $card['zone_dengagement'] ) ) : ?>
			<p class="plaidact-ong-badge"><?php echo esc_html( (string) $card['zone_dengagement'] ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $card['excerpt'] ) ) : ?>
			<p class="plaidact-ong-card__excerpt"><?php echo esc_html( (string) $card['excerpt'] ); ?></p>
		<?php endif; ?>
		<div class="plaidact-ong-tags">
			<?php foreach ( array_slice( $card['cause_terms'], 0, 3 ) as $term ) : ?>
				<span><?php echo esc_html( $term->name ); ?></span>
			<?php endforeach; ?>
		</div>
		<div class="plaidact-ong-card__actions">
			<a class="plaidact-btn" href="<?php echo esc_url( (string) $card['permalink'] ); ?>"><?php esc_html_e( 'Voir la fiche', 'plaidact-timeline' ); ?></a>
			<?php if ( ! empty( $card['site_url'] ) ) : ?>
				<a class="plaidact-btn plaidact-btn--ghost" href="<?php echo esc_url( (string) $card['site_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Site web', 'plaidact-timeline' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</article>
