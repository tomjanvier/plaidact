<?php

use PlaidAct\AgendaSuite\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$term = get_queried_object();
$payload = $term instanceof WP_Term ? Plugin::build_timeline_data( $term->slug ) : [ 'years' => [], 'term' => null ];
$layout = isset( $_GET['layout'] ) ? sanitize_key( wp_unslash( (string) $_GET['layout'] ) ) : 'vertical';
$columns = isset( $_GET['columns'] ) ? absint( wp_unslash( (string) $_GET['columns'] ) ) : 3;
?>
<main id="site-content" class="pa-timeline-archive">
	<?php
	Plugin::render_template(
		'timeline.php',
		[
			'data'           => $payload,
			'title_override' => $term instanceof WP_Term ? $term->name : '',
			'layout'         => in_array( $layout, [ 'vertical', 'horizontal' ], true ) ? $layout : 'vertical',
			'columns'        => max( 1, $columns ),
		]
	);
	?>
</main>
<?php get_footer(); ?>
