<?php
/**
 * Plugin archive template for breves CPT.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$feed = PlaidAct_Breves_Feed::init();

get_header();
?>
<main id="site-content" class="plaidact-page-actualites">
	<?php
	echo wp_kses_post(
		$feed->render_feed(
			array(
				'posts_per_page' => 12,
				'paged'          => max( 1, absint( get_query_var( 'paged' ) ) ),
				'pagination_var' => 'paged',
				'feed_title'     => post_type_archive_title( '', false ),
			)
		)
	);
	?>
</main>
<?php
get_footer();
