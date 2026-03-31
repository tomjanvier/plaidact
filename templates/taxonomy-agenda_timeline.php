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
$events_per_column = isset( $_GET['events_per_column'] ) ? absint( wp_unslash( (string) $_GET['events_per_column'] ) ) : 0;
$show_title = ! isset( $_GET['show_title'] ) || '0' !== (string) wp_unslash( $_GET['show_title'] );
$show_download = ! isset( $_GET['show_download'] ) || '0' !== (string) wp_unslash( $_GET['show_download'] );
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
			'events_per_column' => $events_per_column,
			'show_title'     => $show_title,
			'show_download'  => $show_download,
		]
	);
	?>
</main>
<?php get_footer(); ?>
