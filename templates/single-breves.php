<?php
/**
 * Plugin single template for breves CPT.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'plaidact-breves-feed' );

get_header();
?>
<main id="site-content" class="plaidact-single-breve">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'plaidact-single-breve__article' ); ?>>
			<header class="plaidact-single-breve__header">
				<p class="plaidact-single-breve__meta">
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time>
					<?php
					$theme = function_exists( 'get_field' ) ? (string) get_field( 'thematique_libre' ) : '';
					if ( '' !== trim( $theme ) ) :
						?>
						<span class="plaidact-single-breve__theme"><?php echo esc_html( $theme ); ?></span>
					<?php endif; ?>
				</p>
				<h1 class="plaidact-single-breve__title"><?php the_title(); ?></h1>
			</header>
			<div class="plaidact-single-breve__content"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
