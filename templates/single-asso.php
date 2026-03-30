<?php

use PlaidAct\AgendaSuite\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="site-content" class="plaidact-asso-single-wrap">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'plaidact-asso-single' ); ?>>
			<header class="plaidact-asso-single__header">
				<div class="plaidact-asso-single__media">
					<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?>
				</div>
				<div>
					<h1><?php the_title(); ?></h1>
					<?php if ( get_field( 'zone_dengagement' ) ) : ?><p class="plaidact-asso-badge"><?php echo esc_html( (string) get_field( 'zone_dengagement' ) ); ?></p><?php endif; ?>
					<div class="plaidact-asso-tags">
						<?php foreach ( ( get_the_terms( get_the_ID(), 'cause' ) ?: [] ) as $term ) : ?><span><?php echo esc_html( $term->name ); ?></span><?php endforeach; ?>
					</div>
				</div>
			</header>

			<div class="plaidact-asso-single__content"><?php the_content(); ?></div>

			<section class="plaidact-asso-single__meta-grid">
				<?php if ( get_field( 'comment_agir' ) ) : ?>
				<div>
					<h2><?php esc_html_e( 'Comment agir ?', 'plaidact-breves-feed' ); ?></h2>
					<?php echo wp_kses_post( (string) get_field( 'comment_agir' ) ); ?>
				</div>
				<?php endif; ?>
				<div>
					<h2><?php esc_html_e( 'Causes', 'plaidact-breves-feed' ); ?></h2>
					<div class="plaidact-asso-tags">
						<?php foreach ( ( get_the_terms( get_the_ID(), 'cause' ) ?: [] ) as $term ) : ?><span><?php echo esc_html( $term->name ); ?></span><?php endforeach; ?>
					</div>
					<div class="plaidact-asso-card__actions">
						<?php if ( get_field( 'url_web' ) ) : ?><a class="plaidact-btn" href="<?php echo esc_url( (string) get_field( 'url_web' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Site web', 'plaidact-breves-feed' ); ?></a><?php endif; ?>
						<?php if ( get_field( 'url_don' ) ) : ?><a class="plaidact-btn plaidact-btn--ghost" href="<?php echo esc_url( (string) get_field( 'url_don' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Faire un don', 'plaidact-breves-feed' ); ?></a><?php endif; ?>
					</div>
				</div>
			</section>

			<?php $similar = Plugin::get_similar_asso( get_the_ID(), 3 ); ?>
			<?php if ( ! empty( $similar ) ) : ?>
			<section class="plaidact-asso-related">
				<h2><?php esc_html_e( 'Associations qui travaillent sur la même cause', 'plaidact-breves-feed' ); ?></h2>
				<div class="plaidact-asso-grid">
					<?php foreach ( $similar as $item ) : ?>
						<?php Plugin::render_template( 'parts/asso-card.php', [ 'card' => Plugin::get_asso_card_data( (int) $item['post_id'] ) ] ); ?>
					<?php endforeach; ?>
				</div>
			</section>
			<?php endif; ?>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
