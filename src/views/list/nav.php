<?php
/**
 * List View Nav Template
 * This file loads the list view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/list/nav.php
 *
 * @package TribeEventsCalendar
 * @version 4.2
 *
 */
global $wp_query;

write_log( '======= WP QUERY FROM TEMPLATE ========= ');

$term_name = get_query_var( Tribe__Events__Main::instance()->get_event_taxonomy() );
// $term_obj  = get_term_by( 'name', $term_name,  );

// write_log( $wp_query );
write_log( get_query_var( Tribe__Events__Main::instance()->get_event_taxonomy() )  );

// POSSIBLE STEPS FORWARD
// - Grab tribe_events_cat value from wp_query query vars
// - pass this term (as an ID int) as the currently-unused $term arg in tribe_get_listview_prev_link and tribe_get_listview_next_link funcs below
// - ensure the term is then passed along through generaed links as page forward and back through Cat-Filtered List View

$events_label_plural = tribe_get_event_label_plural();

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<h3 class="screen-reader-text" tabindex="0"><?php echo esc_html( sprintf( esc_html__( '%s List Navigation', 'the-events-calendar' ), $events_label_plural ) ); ?></h3>
<ul class="tribe-events-sub-nav">
	<!-- Left Navigation -->

	<?php if ( tribe_has_previous_event() ) : ?>
		<li class="<?php echo esc_attr( tribe_left_navigation_classes() ); ?>" aria-label="previous events link">
			<a href="<?php echo esc_url( tribe_get_listview_prev_link( $term_name ) ); ?>" rel="prev"><?php printf( '<span>&laquo;</span> ' . esc_html__( 'Previous %s', 'the-events-calendar' ), $events_label_plural ); ?></a>

		</li><!-- .tribe-events-nav-left -->
	<?php endif; ?>

	<!-- Right Navigation -->
	<?php if ( tribe_has_next_event() ) : ?>
		<li class="<?php echo esc_attr( tribe_right_navigation_classes() ); ?>" aria-label="next events link">
			<a href="<?php echo esc_url( tribe_get_listview_next_link( $term_name ) ); ?>" rel="next"><?php printf( esc_html__( 'Next %s', 'the-events-calendar' ), $events_label_plural . ' <span>&raquo;</span>' ); ?></a>
		</li><!-- .tribe-events-nav-right -->
	<?php endif; ?>
</ul>
