<?php
/**
 * View: Day View Nav Disabled Next Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/nav/next-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1aiy
 *
 * @version 5.0.1
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--next">
	<button
		class="tribe-events-c-nav__next tribe-common-b2 tribe-common-b1--min-medium"
		aria-label="<?php esc_attr_e( 'Next Day', 'the-events-calendar' ); ?>"
		title="<?php esc_attr_e( 'Next Day', 'the-events-calendar' ); ?>"
		disabled
	>
		<?php esc_html_e( 'Next Day', 'the-events-calendar' ); ?>
	</button>
</li>
