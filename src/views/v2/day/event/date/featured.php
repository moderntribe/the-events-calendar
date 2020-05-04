<?php
/**
 * View: Featured Icon.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/featured.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTICLE_LINK_HERE}
 *
 * @since TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( ! empty( $event->featured ) ) : ?>
	<em
		class="tribe-events-calendar-day__event-datetime-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
		aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
		title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
	>
	</em>
	<span class="tribe-events-calendar-day__event-datetime-featured-text tribe-common-a11y-visual-hide">
		<?php esc_html_e( 'Featured', 'the-events-calendar' ); ?>
	</span>
<?php endif;