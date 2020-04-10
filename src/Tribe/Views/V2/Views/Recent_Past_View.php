<?php
/**
 * The Past Recent View.
 *
 * @since   TBD
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Traits\List_Behavior;
use Tribe__Context;

class Recent_Past_View extends View {

	use List_Behavior;
	/**
	 * Slug for this view
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $slug = 'recent-past';

	/**
	 * Visibility for this view.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected static $publicly_visible = true;

	/**
	 * Whitelist of Templates to display when Recent Past Events is Active.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected $whitelist = [
		// Standard View Components.
		'components/loader',
		'components/json-ld-data',
		'components/data',
		'components/before',
		'components/messages',
		'components/breadcrumbs',
		'components/events-bar',
		'components/breadcrumbs',
		'components/top-bar/today',
		'components/top-bar/actions',
		'components/events-bar/search/keyword',
		'components/events-bar/search/submit',
		'components/events-bar/search-button',
		'components/events-bar/tabs',
		'components/events-bar/search',
		'components/events-bar/filters',
		'components/events-bar/views',
		'components/ical-link',

		// Day View
		'day',
		'day/top-bar',
		'day/top-bar/nav',
		'day/top-bar/nav/prev',
		'day/top-bar/nav/next-disabled',
		'day/top-bar/datepicker',

		// List View.
		'components/events-bar/views/list',
		'components/events-bar/views/list/item',
		'list/top-bar',
		'list/top-bar/nav',
		'list/top-bar/nav/prev',
		'list/top-bar/nav/next-disabled',
		'list/top-bar/datepicker',
		'list',

		// Month View
		'month',
		'month/top-bar',
		'month/top-bar/nav',
		'month/top-bar/nav/prev',
		'month/top-bar/nav/next-disabled',
		'month/top-bar/datepicker',


		// Map View
		'map',
		'map/top-bar',
		'map/top-bar/nav',
		'map/top-bar/nav/prev',
		'map/top-bar/nav/next-disabled',
		'map/top-bar/datepicker',

		// Photo View
		'photo',
		'photo/top-bar',
		'photo/top-bar/nav',
		'photo/top-bar/nav/prev',
		'photo/top-bar/nav/next-disabled',
		'photo/top-bar/datepicker',

		// Week View
		'week',
		'week/top-bar',
		'week/top-bar/nav',
		'week/top-bar/nav/prev',
		'week/top-bar/nav/next-disabled',
		'week/top-bar/datepicker',

		// Recent Past Events Views.
		'recent-past',
		'recent-past/heading',
		'recent-past/event',
		'recent-past/event/date',
		'recent-past/event/title',
		'recent-past/event/venue',
		'recent-past/event/description',
		'recent-past/event/cost',
		'recent-past/event/date-tag',
		'recent-past/event/date/meta',
	];

	/**
	 * Indicates Recent Past View supports the date as a query argument appended to its URL, not as part of a "pretty" URL.
	 *
	 * @var bool
	 */
	protected static $date_in_url = false;

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( Tribe__Context $context = null ) {
		$context = null !== $context ? $context : $this->context;
		$this->repository = tribe_events();

		$date                   = $context->get( 'event_date', 'now' );
		//$args['posts_per_page'] = $context->get( 'events_per_page', 3 );

		//todo mockup is to display 3 items, the context above uses a db setting. I think calling it recent_past_per_page with a default of 3 will fix this.
		$args['posts_per_page'] = 3;
		$args['order_by']       = 'event_date';
		$args['order']          = 'DESC';
		$args['ends_before']    = $date;

		return $args;
	}

	/**
	 * Add Filters for Whitelist and Adding View HTML.
	 *
	 * @since TBD
	 */
	public function add_view_filters() {

		add_filter( 'tribe_template_html:events/v2/components/messages', [ $this, 'filter_template_done' ] );
		//add_filter( 'tribe_template_html:events/v2/day', [ $this, 'add_view' ] );


		//todo loading on the ical link goes below the existing view, although the existing view is not whitelisted so no longer shows.
		//todo this also places it in the existing view wrap and prevents display issues with the footer padding.
		//todo this works in all views
		add_filter( 'tribe_template_html:events/v2/components/ical-link', [ $this, 'add_view' ] );
		//add_filter( 'tribe_template_html:events/v2/month', [ $this, 'add_view' ] );

		// PRO Views
		//add_filter( 'tribe_template_html:events-pro/v2/map', [ $this, 'add_view' ] );
		//add_filter( 'tribe_template_html:events-pro/v2/photo', [ $this, 'add_view' ] );
		//add_filter( 'tribe_template_html:events-pro/v2/week', [ $this, 'add_view' ] );
	}

	/**
	 * Connect Whitelist Filter to Tribe Template Done.
	 *
	 * @since TBD
	 */
	public function filter_template_done( $html ) {

		add_filter( 'tribe_template_done', [ $this, 'filter_template_display_by_whitelist' ], 10, 4 );

		return $html;
	}

	/**
	 * Filter the Template Files and Only Return HTML if in Whitelist.
	 *
	 * @since TBD
	 *
	 * @param string  $done    Whether to continue displaying the template or not.
	 * @param array   $name    Template name.
	 * @param array   $context Any context data you need to expose to this file.
	 * @param boolean $echo    If we should also print the Template.
	 *
	 * @return string
	 */
	public function filter_template_display_by_whitelist( $done, $name, $context, $echo ) {

		if ( in_array( $name, $this->whitelist, true ) ) {
			return $done;
		}

		return '';
	}

	/**
	 * Add the HTML for Recent Past Events to the HTML of the View Being Rendered.
	 *
	 * @since TBD
	 *
	 * @param $html string The HTML of the view being rendered.
	 *
	 * @return string The HTML of the View being Rendered and Recent Past Events HTML
	 */
	public function add_view( $html ) {

		return $this->get_html();
	}
}