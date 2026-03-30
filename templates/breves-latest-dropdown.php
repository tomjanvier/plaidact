<?php
/**
 * Brèves : 20 dernières en dropdown.
 *
 * @var WP_Query $query
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="plaidact-breves-dropdown-wrap">
	<label for="plaidact-breves-dropdown" class="plaidact-breves-dropdown-label"><?php esc_html_e( 'Consulter une brève récente', 'plaidact-breves-feed' ); ?></label>
	<select id="plaidact-breves-dropdown" class="plaidact-breves-dropdown" onchange="if(this.value){window.location.href=this.value;}">
		<option value=""><?php esc_html_e( 'Sélectionner une brève…', 'plaidact-breves-feed' ); ?></option>
		<?php while ( $query->have_posts() ) : $query->the_post(); ?>
			<option value="<?php echo esc_url( get_permalink() ); ?>">
				<?php echo esc_html( get_the_date( 'd/m/Y' ) . ' — ' . get_the_title() ); ?>
			</option>
		<?php endwhile; ?>
	</select>
</div>
