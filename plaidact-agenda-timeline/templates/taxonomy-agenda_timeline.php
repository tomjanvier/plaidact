<?php

use PlaidAct\AgendaTimeline\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$term = get_queried_object();
$payload = $term instanceof WP_Term ? Plugin::build_timeline_data( $term->slug ) : [ 'years' => [], 'term' => null ];
?>
<main id="site-content" class="pa-timeline-archive">
	<?php
	Plugin::render_template(
		'timeline.php',
		[
			'data'           => $payload,
			'title_override' => $term instanceof WP_Term ? $term->name : '',
		]
	);
	?>
</main>
<?php get_footer(); ?>
