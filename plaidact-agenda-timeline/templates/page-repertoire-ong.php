<?php

use PlaidAct\AgendaTimeline\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="site-content" class="plaidact-ong-page-template">
	<?php while ( have_posts() ) : the_post(); ?>
		<header class="plaidact-ong-header">
			<h1><?php the_title(); ?></h1>
			<?php if ( get_the_content() ) : ?>
				<div class="plaidact-ong-intro"><?php the_content(); ?></div>
			<?php endif; ?>
		</header>
	<?php endwhile; ?>
	<?php Plugin::render_template( 'ong-directory-loop.php', [ 'posts_per_page' => 9 ] ); ?>
</main>
<?php get_footer(); ?>
