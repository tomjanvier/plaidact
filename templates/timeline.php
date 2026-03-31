<?php

use PlaidAct\AgendaSuite\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data           = $data ?? [];
$title_override = $title_override ?? '';
$layout         = isset( $layout ) && 'horizontal' === $layout ? 'horizontal' : 'vertical';
$columns        = isset( $columns ) ? max( 1, absint( $columns ) ) : 3;
$years          = $data['years'] ?? [];
$term           = $data['term'] ?? null;

$title = $title_override ?: ( $term instanceof WP_Term ? $term->name : __( 'Agenda', 'plaidact-breves-feed' ) );
$slug  = $term instanceof WP_Term ? $term->slug : 'timeline';
?>
<section class="pa-timeline pa-timeline--<?php echo esc_attr( $layout ); ?>" id="pa-timeline-<?php echo esc_attr( $slug ); ?>" aria-label="<?php echo esc_attr( $title ); ?>" style="--pa-timeline-columns:<?php echo esc_attr( (string) $columns ); ?>">
	<h2 class="pa-timeline-title"><?php echo esc_html( $title ); ?></h2>

	<?php if ( count( $years ) > 1 ) : ?>
		<nav class="pa-years-nav" aria-label="<?php esc_attr_e( 'Navigation par année', 'plaidact-breves-feed' ); ?>">
			<ul role="list">
				<?php foreach ( $years as $year_data ) : ?>
					<li><a href="#tl-<?php echo esc_attr( $slug . '-' . $year_data['year'] ); ?>"><?php echo esc_html( (string) $year_data['year'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<div class="pa-timeline-body">
		<?php foreach ( $years as $year_data ) : ?>
			<div class="pa-year-block" id="tl-<?php echo esc_attr( $slug . '-' . $year_data['year'] ); ?>" data-year="<?php echo esc_attr( (string) $year_data['year'] ); ?>">
				<h3 class="pa-year-heading"><span><?php echo esc_html( (string) $year_data['year'] ); ?></span></h3>
				<div class="pa-months-wrapper">
					<?php foreach ( $year_data['months'] as $month_data ) : ?>
						<div class="pa-month-block">
							<h4 class="pa-month-heading">
								<span class="pa-month-dot" aria-hidden="true"></span>
								<span class="pa-month-label"><?php echo esc_html( $month_data['month_name'] ); ?></span>
								<span class="pa-month-count"><?php echo esc_html( (string) count( $month_data['events'] ) ); ?></span>
							</h4>
							<ul class="pa-event-list" role="list">
								<?php foreach ( $month_data['events'] as $event ) : ?>
									<?php
									$classes = [ 'pa-event' ];
									foreach ( $event['config'] as $cfg ) {
										$classes[] = 'pa-event--' . sanitize_html_class( $cfg );
									}
									if ( ! empty( $event['is_continuation'] ) ) {
										$classes[] = 'pa-event--continuation';
									}
									$period = ( $event['date_fin'] instanceof DateTimeImmutable && $event['date_fin']->format( 'Ymd' ) !== $event['date_debut']->format( 'Ymd' ) ) ? '→ ' . Plugin::format_date_short( $event['date_fin'] ) : '';
									?>
									<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
										<div class="pa-event-date" aria-hidden="true">
											<?php if ( ! empty( $event['is_continuation'] ) ) : ?>
												<span class="pa-date-cont">↻</span>
											<?php else : ?>
												<span class="pa-date-day"><?php echo esc_html( $event['date_debut']->format( 'j' ) ); ?></span>
												<span class="pa-date-month"><?php echo esc_html( Plugin::month_abbr( (int) $event['date_debut']->format( 'n' ) ) ); ?></span>
											<?php endif; ?>
										</div>
										<div class="pa-event-body">
											<a href="<?php echo esc_url( $event['url'] ); ?>" class="pa-event-title-link" <?php echo $event['is_external'] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
												<span class="pa-event-title"><?php echo esc_html( $event['title'] ); ?></span>
											</a>
											<div class="pa-event-meta">
												<?php if ( ! empty( $event['lieu'] ) ) : ?><span class="pa-event-lieu">📍 <?php echo esc_html( $event['lieu'] ); ?></span><?php endif; ?>
												<?php if ( '' !== $period ) : ?><span class="pa-event-period"><?php echo esc_html( $period ); ?></span><?php endif; ?>
											</div>
										</div>
										<?php if ( ! empty( $event['mini_logo'] ) ) : ?><div class="pa-event-logo"><img src="<?php echo esc_url( $event['mini_logo'] ); ?>" alt="" width="40" height="40" loading="lazy" decoding="async" /></div><?php endif; ?>
										<div class="pa-event-action"><a href="<?php echo esc_url( $event['url'] ); ?>" class="pa-event-cta" <?php echo $event['is_external'] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>→</a></div>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
