<?php
/**
 * View: Month View - Calendar Event Featured Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/multiday-events/date/featured-hidden.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @since 5.1.1
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( $event->featured ) : ?>
	<em
		class="tribe-events-calendar-month__multiday-event-hidden-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
		aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
		title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
	></em>
<?php endif;