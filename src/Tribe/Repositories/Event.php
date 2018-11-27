<?php
/**
 * The main ORM/Repository class for events.
 *
 * @since TBD
 */

/**
 * Class Tribe__Events__Repositories__Event
 *
 *
 * @since TBD
 */
class Tribe__Events__Repositories__Event extends Tribe__Repository {

	/**
	 * The unique fragment that will be used to identify this repository filters.
	 *
	 * @var string
	 */
	protected $filter_name = 'events';

	/**
	 * The menu_order override used in pre_get_posts to support negative menu_order lookups for Sticky Events.
	 *
	 * @var int
	 */
	protected $menu_order = 0;

	/**
	 * Tribe__Events__Repositories__Event constructor.
	 *
	 * Sets up the repository default parameters and schema.
	 *
	 * @since TBD
	 */
	public function __construct() {
		parent::__construct();

		$this->create_args['post_type'] = Tribe__Events__Main::POSTTYPE;
		$this->taxonomies               = array(
			Tribe__Events__Main::TAXONOMY,
			'post_tag',
		);

		// Add event specific aliases.
		$this->update_fields_aliases = array_merge( $this->update_fields_aliases, array(
			'start_date'         => '_EventStartDate',
			'end_date'           => '_EventEndDate',
			'start_date_utc'     => '_EventStartDateUTC',
			'end_date_utc'       => '_EventEndDateUTC',
			'duration'           => '_EventDuration',
			'all_day'            => '_EventAllDay',
			'timezone'           => '_EventTimezone',
			'venue'              => '_EventVenueID',
			'organizer'          => '_EventOrganizerID',
			'category'           => Tribe__Events__Main::TAXONOMY,
			'cost'               => '_EventCost',
			'currency_symbol'    => '_EventCurrencySymbol',
			'currency_position'  => '_EventCurrencyPosition',
			'show_map'           => '_EventShowMap',
			'show_map_link'      => '_EventShowMapLink',
			'url'                => '_EventURL',
			'hide_from_upcoming' => '_EventHideFromUpcoming',
			// Where is "sticky"? It's handled in the meta filtering by setting `menu_order`.
			'featured'           => '_tribe_featured',
		) );

		$this->default_args = array(
			'post_type'                    => Tribe__Events__Main::POSTTYPE,
			'order'                        => 'ASC',
			'order_by'                     => 'event_date',
			// We'll be handling the dates, let's mark the query as a non-filtered one.
			'tribe_suppress_query_filters' => true,
		);

		$this->schema = array_merge( $this->schema, array(
			'starts_before'           => array( $this, 'filter_by_starts_before' ),
			'starts_after'            => array( $this, 'filter_by_starts_after' ),
			'starts_between'          => array( $this, 'filter_by_starts_between' ),
			'ends_before'             => array( $this, 'filter_by_ends_before' ),
			'ends_after'              => array( $this, 'filter_by_ends_after' ),
			'ends_between'            => array( $this, 'filter_by_ends_between' ),
			'starts_and_ends_between' => array( $this, 'filter_by_starts_and_ends_between' ),
			'runs_between'            => array( $this, 'filter_by_runs_between' ),
			'all_day'                 => array( $this, 'filter_by_all_day' ),
			'multiday'                => array( $this, 'filter_by_multiday' ),
			'on_calendar_grid'        => array( $this, 'filter_by_on_calendar_grid' ),
			'timezone'                => array( $this, 'filter_by_timezone' ),
			'featured'                => array( $this, 'filter_by_featured' ),
			'hidden'                  => array( $this, 'filter_by_hidden' ),
			'linked_post'             => array( $this, 'filter_by_linked_post' ),
			'organizer'               => array( $this, 'filter_by_organizer' ),
			'sticky'                  => array( $this, 'filter_by_sticky' ),
			'venue'                   => array( $this, 'filter_by_venue' ),
			'cost_currency_symbol'    => array( $this, 'filter_by_cost_currency_symbol' ),
			'cost'                    => array( $this, 'filter_by_cost' ),
			'cost_between'            => array( $this, 'filter_by_cost_between' ),
			'cost_less_than'          => array( $this, 'filter_by_cost_less_than' ),
			'cost_greater_than'       => array( $this, 'filter_by_cost_greater_than' ),
		) );

		// Add backcompat aliases.
		$this->schema['hide_upcoming'] = array( $this, 'filter_by_hidden' );
		$this->schema['start_date']    = array( $this, 'filter_by_starts_after' );
		$this->schema['end_date']      = array( $this, 'filter_by_ends_before' );

		$this->add_simple_meta_schema_entry( 'website', '_EventURL' );

		$this->add_simple_tax_schema_entry( 'event_category', Tribe__Events__Main::TAXONOMY );
		$this->add_simple_tax_schema_entry( 'event_category_not_in', Tribe__Events__Main::TAXONOMY, 'term_not_in' );
		$this->add_simple_tax_schema_entry( 'tag', 'post_tag' );
		$this->add_simple_tax_schema_entry( 'tag_not_in', 'post_tag', 'term_not_in' );
	}

	/**
	 * Filters the event by their all-day status.
	 *
	 * @since TBD
	 *
	 * @param bool $all_day Whether the events should be all-day or not.
	 *
	 * @return array|null An array of query arguments or null if modified with internal methods.
	 */
	public function filter_by_all_day( $all_day = true ) {
		if ( (bool) $all_day ) {
			$this->by( 'meta_equals', '_EventAllDay', 'yes' );

			return null;
		}

		return array(
			'meta_query' => array(
				'by-all-day' => array(
					'not-exists' => array(
						'key'     => '_EventAllDay',
						'compare' => 'NOT EXISTS',
						'value'   => '#',
					),
					'relation'   => 'OR',
					'is-not-yes' => array(
						'key'     => '_EventAllDay',
						'compare' => '!=',
						'value'   => 'yes',
					),
				),
			),
		);
	}

	/**
	 * Filters events whose start date occurs before the provided date; fetch is not inclusive.
	 *
	 * @since TBD
	 *
	 * @param string|DateTime|int $datetime A `strtotime` parse-able string, a DateTime object or
	 *                                      a timestamp.
	 * @param string|DateTimeZone $timezone A timezone string, UTC offset or DateTimeZone object;
	 *                                      defaults to the site timezone; this parameter is ignored
	 *                                      if the `$datetime` parameter is a DatTime object.
	 *
	 * @return array An array of arguments that should be added to the WP_Query object.
	 */
	public function filter_by_starts_before( $datetime, $timezone = null ) {
		$date = Tribe__Date_Utils::build_date_object( $datetime, $timezone )
		                         ->setTimezone( new DateTimeZone( 'UTC' ) );

		return array(
			'meta_query' => array(
				'ends-before' => array(
					'key'     => '_EventStartDateUTC',
					'compare' => '<',
					'value'   => $date->format( 'Y-m-d H:i:s' ),
					'type'    => 'DATETIME',
				),
			),
		);
	}

	/**
	 * Filters events whose end date occurs before the provided date; fetch is not inclusive.
	 *
	 * @since TBD
	 *
	 * @param string|DateTime|int $datetime A `strtotime` parse-able string, a DateTime object or
	 *                                      a timestamp.
	 * @param string|DateTimeZone $timezone A timezone string, UTC offset or DateTimeZone object;
	 *                                      defaults to the site timezone; this parameter is ignored
	 *                                      if the `$datetime` parameter is a DatTime object.
	 *
	 * @return array An array of arguments that should be added to the WP_Query object.
	 */
	public function filter_by_ends_before( $datetime, $timezone = null ) {
		$date = Tribe__Date_Utils::build_date_object( $datetime, $timezone )
		                         ->setTimezone( new DateTimeZone( 'UTC' ) );

		return array(
			'meta_query' => array(
				'ends-before' => array(
					'key'     => '_EventEndDateUTC',
					'compare' => '<',
					'value'   => $date->format( 'Y-m-d H:i:s' ),
					'type'    => 'DATETIME',
				),
			),
		);
	}

	/**
	 * Filters events whose start date occurs after the provided date; fetch is not inclusive.
	 *
	 * @since TBD
	 *
	 * @param string|DateTime|int $datetime A `strtotime` parse-able string, a DateTime object or
	 *                                      a timestamp.
	 * @param string|DateTimeZone $timezone A timezone string, UTC offset or DateTimeZone object;
	 *                                      defaults to the site timezone; this parameter is ignored
	 *                                      if the `$datetime` parameter is a DatTime object.
	 *
	 * @return array An array of arguments that should be added to the WP_Query object.
	 */
	public function filter_by_starts_after( $datetime, $timezone = null ) {
		$date = Tribe__Date_Utils::build_date_object( $datetime, $timezone )
		                         ->setTimezone( new DateTimeZone( 'UTC' ) );

		return array(
			'meta_query' => array(
				'ends-after' => array(
					'key'     => '_EventStartDateUTC',
					'compare' => '>',
					'value'   => $date->format( 'Y-m-d H:i:s' ),
					'type'    => 'DATETIME',
				),
			),
		);
	}

	/**
	 * Filters events whose end date occurs after the provided date; fetch is not inclusive.
	 *
	 * @since TBD
	 *
	 * @param string|DateTime|int $datetime A `strtotime` parse-able string, a DateTime object or
	 *                                      a timestamp.
	 * @param string|DateTimeZone $timezone A timezone string, UTC offset or DateTimeZone object;
	 *                                      defaults to the site timezone; this parameter is ignored
	 *                                      if the `$datetime` parameter is a DatTime object.
	 *
	 * @return array An array of arguments that should be added to the WP_Query object.
	 */
	public function filter_by_ends_after( $datetime, $timezone = null ) {
		$date = Tribe__Date_Utils::build_date_object( $datetime, $timezone )
		                         ->setTimezone( new DateTimeZone( 'UTC' ) );

		return array(
			'meta_query' => array(
				'ends-after' => array(
					'key'     => '_EventEndDateUTC',
					'compare' => '>',
					'value'   => $date->format( 'Y-m-d H:i:s' ),
					'type'    => 'DATETIME',
				),
			),
		);
	}

	/**
	 * Filters events whose start date occurs between a set of dates; fetch is inclusive.
	 *
	 * @since TBD
	 *
	 * @param string|DateTime|int $start_datetime A `strtotime` parse-able string, a DateTime object or
	 *                                            a timestamp.
	 * @param string|DateTime|int $end_datetime   A `strtotime` parse-able string, a DateTime object or
	 *                                            a timestamp.
	 * @param string|DateTimeZone $timezone       A timezone string, UTC offset or DateTimeZone object;
	 *                                            defaults to the site timezone; this parameter is ignored
	 *                                            if the `$datetime` parameter is a DatTime object.
	 */
	public function filter_by_starts_between( $start_datetime, $end_datetime, $timezone = null ) {
		$utc = new DateTimeZone( 'UTC' );

		$lower = Tribe__Date_Utils::build_date_object( $start_datetime, $timezone )->setTimezone( $utc );
		$upper = Tribe__Date_Utils::build_date_object( $end_datetime, $timezone )->setTimezone( $utc );

		$this->by( 'meta_between', '_EventStartDateUTC', array(
			$lower->format( 'Y-m-d H:i:s' ),
			$upper->format( 'Y-m-d H:i:s' ),
		), 'DATETIME' );
	}

	/**
	 * Filters events whose end date occurs between a set of dates; fetch is inclusive.
	 *
	 * @since TBD
	 *
	 * @param string|DateTime|int $start_datetime A `strtotime` parse-able string, a DateTime object or
	 *                                            a timestamp.
	 * @param string|DateTime|int $end_datetime   A `strtotime` parse-able string, a DateTime object or
	 *                                            a timestamp.
	 * @param string|DateTimeZone $timezone       A timezone string, UTC offset or DateTimeZone object;
	 *                                            defaults to the site timezone; this parameter is ignored
	 *                                            if the `$datetime` parameter is a DatTime object.
	 */
	public function filter_by_ends_between( $start_datetime, $end_datetime, $timezone = null ) {
		$utc = new DateTimeZone( 'UTC' );

		$lower = Tribe__Date_Utils::build_date_object( $start_datetime, $timezone )->setTimezone( $utc );
		$upper = Tribe__Date_Utils::build_date_object( $end_datetime, $timezone )->setTimezone( $utc );

		$this->by( 'meta_between', '_EventEndDateUTC', array(
			$lower->format( 'Y-m-d H:i:s' ),
			$upper->format( 'Y-m-d H:i:s' ),
		), 'DATETIME' );
	}

	/**
	 * Filters events to include only those that match the provided multi day state.
	 *
	 * Please note that an event might be multi-day in its timezone but not in another;
	 * this filter will make the check on the event times localized to the event timezone.
	 * Furthermore the end of day cutoff is taken into account so, given a cutoff of 10PM
	 * an event starting at 10:30PM and ending at 11AM is not multi-day.
	 *
	 * @since TBD
	 *
	 * @param bool $multiday Whether to filter by events that are or not multi-day.
	 */
	public function filter_by_multiday( $multiday = true ) {
		global $wpdb;

		$this->filter_query->join( "LEFT JOIN {$wpdb->postmeta} multiday_start_date
			ON ( {$wpdb->posts}.ID = multiday_start_date.post_id 
			AND multiday_start_date.meta_key = '_EventStartDate' )" );
		$this->filter_query->join( "LEFT JOIN {$wpdb->postmeta} multiday_end_date 
			ON ( {$wpdb->posts}.ID = multiday_end_date.post_id
			AND multiday_end_date.meta_key = '_EventEndDate' )" );

		// We're interested in the time only.
		$end_of_day_cutoff = tribe_end_of_day( 'today', 'H:i:s' );

		if ( '23:59:59' === $end_of_day_cutoff ) {
			/*
			 * An event is considered multi-day when the end date is not the same as the start date when
			 * using the "natural" end-of-day cutoff.
			 */
			$compare = $multiday ? '!=' : '=';
			$this->filter_query->where(
				"DATE( multiday_end_date.meta_value ) {$compare} DATE( multiday_start_date.meta_value )"
			);
		} else {
			/*
			 * An event is considered multi-day when the end date is after the end-of-day cutoff of the start date.
			 * Since the cut-off moves forward from midnight add 1 day to the start date.
			 */
			$compare = $multiday ? '>' : '<=';
			$this->filter_query->where(
				"multiday_end_date.meta_value {$compare} DATE_FORMAT( DATE_ADD( multiday_start_date.meta_value, INTERVAL 1 DAY ) , '%Y-%m-%d {$end_of_day_cutoff}' )"
			);
		}
	}

	/**
	 * Filters events to include only those events that appear on the given month’s calendar grid.
	 *
	 * @since TBD
	 *
	 * @param int $month The month to display.
	 * @param int $year  The year to display.
	 *
	 * @return array|null An array of arguments that should be added to the query or `null`
	 *                    if the arguments are not valid (thus the filter will be ignored).
	 */
	public function filter_by_on_calendar_grid( $month, $year ) {
		$year_month_string = "{$year}-{$month}";

		if ( ! Tribe__Date_Utils::is_valid_date( $year_month_string ) ) {
			/*
			 * Months and years are known but, at runtime, the client code might get, or pass,
			 * them wrong. In that case this filter will not be applied.
			 */
			return null;
		}

		$start = Tribe__Events__Template__Month::calculate_first_cell_date( $year_month_string );
		$end   = Tribe__Events__Template__Month::calculate_final_cell_date( $year_month_string );

		return $this->filter_by_runs_between( $start, tribe_end_of_day( $end ) );
	}

	/**
	 * Filters events to include only those events that are running between two dates.
	 *
	 * An event is running between two dates when its start date or end date are between
	 * the two dates.
	 *
	 * @since TBD
	 *
	 * @param string|DateTime|int $start_datetime A `strtotime` parse-able string, a DateTime object or
	 *                                            a timestamp.
	 * @param string|DateTime|int $end_datetime   A `strtotime` parse-able string, a DateTime object or
	 *                                            a timestamp.
	 * @param string|DateTimeZone $timezone       A timezone string, UTC offset or DateTimeZone object;
	 *                                            defaults to the site timezone; this parameter is ignored
	 *                                            if the `$datetime` parameter is a DatTime object.
	 *
	 * @return array An array of arguments that should be added to the WP_Query object.
	 */
	public function filter_by_runs_between( $start_datetime, $end_datetime, $timezone = null ) {
		$start_date = Tribe__Date_Utils::build_date_object( $start_datetime, $timezone )
		                               ->setTimezone( new DateTimeZone( 'UTC' ) )
		                               ->format( 'Y-m-d H:i:s' );
		$end_date   = Tribe__Date_Utils::build_date_object( $end_datetime, $timezone )
		                               ->setTimezone( new DateTimeZone( 'UTC' ) )
		                               ->format( 'Y-m-d H:i:s' );

		return array(
			'meta_query' => array(
				'runs-between' => array(
					'starts'   => array(
						'after-the-start' => array(
							'key'     => '_EventStartDateUTC',
							'value'   => $start_date,
							'compare' => '>=',
							'type'    => 'DATETIME',
						),
						'relation'        => 'AND',
						'before-the-end'  => array(
							'key'     => '_EventStartDateUTC',
							'value'   => $end_date,
							'compare' => '<=',
							'type'    => 'DATETIME',
						),
					),
					'relation' => 'OR',
					'ends'     => array(
						'after-the-start' => array(
							'key'     => '_EventEndDateUTC',
							'value'   => $start_date,
							'compare' => '>=',
							'type'    => 'DATETIME',
						),
						'relation'        => 'AND',
						'before-the-end'  => array(
							'key'     => '_EventEndDateUTC',
							'value'   => $end_date,
							'compare' => '<=',
							'type'    => 'DATETIME',
						),
					),
				),
			),
		);
	}

	/**
	 * Filters events the given timezone.
	 *
	 * UTC, UTC+0, and UTC-0 should be parsed as the same timezone.
	 *
	 * @since TBD
	 *
	 * @param string|DateTimeZone $timezone A timezone string or object.
	 *
	 * @return array An array of arguments to apply to the query.
	 */
	public function filter_by_timezone( $timezone ) {
		if ( $timezone instanceof DateTimeZone ) {
			$timezone = $timezone->getName();
		}

		$is_utc = preg_match( '/^UTC((\\+|-)0)*$/i', $timezone );

		if ( $is_utc ) {
			return array(
				'meta_query' => array(
					'by-timezone' => array(
						'key'     => '_EventTimezone',
						'compare' => 'IN',
						'value'   => array( 'UTC', 'UTC+0', 'UTC-0' ),
					),
				),
			);
		}

		return array(
			'meta_query' => array(
				'by-timezone' => array(
					'key'   => '_EventTimezone',
					'value' => $timezone,
				),
			),
		);
	}

	/**
	 * Filters events whose start and end dates occur between a set of dates.
	 *
	 * Fetch is inclusive.
	 *
	 * @since TBD
	 *
	 * @param string|DateTime|int $start_datetime A `strtotime` parse-able string, a DateTime object or
	 *                                            a timestamp.
	 * @param string|DateTime|int $end_datetime   A `strtotime` parse-able string, a DateTime object or
	 *                                            a timestamp.
	 * @param string|DateTimeZone $timezone       A timezone string, UTC offset or DateTimeZone object;
	 *                                            defaults to the site timezone; this parameter is ignored
	 *                                            if the `$datetime` parameter is a DatTime object.
	 *
	 * @return array An array of arguments that should be added to the WP_Query object.
	 */
	public function filter_by_starts_and_ends_between( $start_datetime, $end_datetime, $timezone = null ) {
		$start_date = Tribe__Date_Utils::build_date_object( $start_datetime, $timezone )
		                               ->setTimezone( new DateTimeZone( 'UTC' ) )
		                               ->format( 'Y-m-d H:i:s' );
		$end_date   = Tribe__Date_Utils::build_date_object( $end_datetime, $timezone )
		                               ->setTimezone( new DateTimeZone( 'UTC' ) )
		                               ->format( 'Y-m-d H:i:s' );

		$interval = array( $start_date, $end_date );

		return array(
			'meta_query' => array(
				'starts-ends-between' => array(
					'starts-between' => array(
						'key'     => '_EventStartDateUTC',
						'value'   => $interval,
						'compare' => 'BETWEEN',
						'type'    => 'DATETIME',
					),
					'relation'       => 'AND',
					'ends-between'   => array(
						'key'     => '_EventEndDateUTC',
						'value'   => $interval,
						'compare' => 'BETWEEN',
						'type'    => 'DATETIME',
					),
				),
			),
		);
	}

	/**
	 * Filters events to include only those that match the provided featured state.
	 *
	 * @since TBD
	 *
	 * @param bool $featured Whether the events should be featured or not.
	 */
	public function filter_by_featured( $featured = true ) {
		$this->by( (bool) $featured ? 'meta_exists' : 'meta_not_exists', Tribe__Events__Featured_Events::FEATURED_EVENT_KEY, '#' );
	}

	/**
	 * Filters events to include only those that match the provided hidden state.
	 *
	 * @since TBD
	 *
	 * @param bool $hidden Whether the events should be hidden or not.
	 */
	public function filter_by_hidden( $hidden = true ) {
		$this->by( (bool) $hidden ? 'meta_exists' : 'meta_not_exists', '_EventHideFromUpcoming', '#' );
	}

	/**
	 * Filters events by specific event organizer(s).
	 *
	 * @since TBD
	 *
	 * @param int|WP_Post|array $organizer Organizer(s).
	 */
	public function filter_by_organizer( $organizer ) {
		$this->filter_by_linked_post( '_EventOrganizerID', $organizer );
	}

	/**
	 * Filters events to include only those that match the provided hidden state.
	 *
	 * @since TBD
	 *
	 * @param string            $linked_post_meta_key The linked post type meta key.
	 * @param int|WP_Post|array $linked_post          Linked post(s).
	 */
	public function filter_by_linked_post( $linked_post_meta_key, $linked_post ) {
		$linked_posts = (array) $linked_post;

		$post_ids = array_map( array( 'Tribe__Main', 'post_id_helper' ), $linked_posts );
		$post_ids = array_filter( $post_ids );
		$post_ids = array_unique( $post_ids );

		$this->by( 'meta_in', $linked_post_meta_key, $post_ids );
	}

	/**
	 * Filters events to include only those that match the provided sticky state.
	 *
	 * @since TBD
	 *
	 * @param bool $sticky Whether the events should be sticky or not.
	 */
	public function filter_by_sticky( $sticky = true ) {
		// Support negative menu_order lookups.
		add_action( 'pre_get_posts', array( $this, 'support_negative_menu_order' ) );

		$this->menu_order = (bool) $sticky ? - 1 : 0;

		$this->by( 'menu_order', $this->menu_order );
	}

	/**
	 * Filters events by specific event venue(s).
	 *
	 * @since TBD
	 *
	 * @param int|WP_Post|array $venue Venue(s).
	 */
	public function filter_by_venue( $venue ) {
		$this->filter_by_linked_post( '_EventVenueID', $venue );
	}

	/**
	 * Hook into WP_Query pre_get_posts and support negative menu_order values.
	 *
	 * @param WP_Query $query Query object.
	 */
	public function support_negative_menu_order( $query ) {
		// Send in the unmodified menu_order.
		$query->query_vars['menu_order'] = (int) $this->menu_order;

		// Remove hook.
		remove_action( 'pre_get_posts', array( $this, 'support_negative_menu_order' ) );
	}

	/**
	 * Filters events that have a cost relative to the given value based on the $comparator.
	 * If Event Tickets is active, rather than looking at the event cost, all tickets attached
	 * to the event should used to reference cost; the event cost meta will be ignored.
	 *
	 * Providing the symbol parameter should limit event results to only those events whose cost is relative to
	 * the value AND the currency symbol matches. This way you can select posts that have a cost of 5 USD and
	 * not accidentally get events with 5 EUR.
	 *
	 * @since TBD
	 *
	 * @param float|array $value       The cost to use for the comparison; in the case of `BETWEEN`, `NOT BETWEEN`,
	 *                                 `IN` and `NOT IN` operators this value should be an array.
	 * @param string      $operator    Teh comparison operator to use for the comparison, one of `<`, `<=`, `>`, `>=`,
	 *                                 `=`, `BETWEEN`, `NOT BETWEEN`, `IN`, `NOT IN`.
	 * @param string      $symbol      The desired currency symbol or symbols; this symbol can be a currency ISO code,
	 *                                 e.g. "USD" for U.S. dollars, or a currency symbol, e.g. "$".
	 *                                 In the latter case results will include any event with the matching currency
	 *                                 symbol, this might lead to ambiguous results.
	 *
	 * @return array An array of query arguments that will be added to the main query.
	 *
	 * @throws Tribe__Repository__Usage_Error If the comparison operator is not supported of is using the `BETWEEN`,
	 *                                        `NOT BETWEEN` operators without passing a two element array `$value`.
	 */
	public function filter_by_cost( $value, $operator = '=', $symbol = null ) {
		if ( ! in_array( $operator, array(
			'<',
			'<=',
			'>',
			'>=',
			'=',
			'!=',
			'BETWEEN',
			'NOT BETWEEN',
			'IN',
			'NOT IN',
		) ) ) {
			throw Tribe__Repository__Usage_Error::because_this_comparison_operator_is_not_supported( $operator, 'filter_by_cost' );
		}

		if ( in_array( $operator, array(
				'BETWEEN',
				'NOT BETWEEN',
			) ) && ! ( is_array( $value ) && 2 === count( $value ) ) ) {
			throw Tribe__Repository__Usage_Error::because_this_comparison_operator_requires_an_value_of_type( $operator, 'filter_by_cost', 'array' );
		}

		if ( in_array( $operator, array( 'IN', 'NOT IN' ) ) ) {
			$value = array_map( 'floatval', (array) $value );
		}

		$operator_name  = Tribe__Utils__Array::get( self::$comparison_operators, $operator, '' );
		$meta_query_key = 'by-cost-' . $operator_name;

		// Do not add ANY spacing in the type: WordPress will only accept this format!
		$meta_query_entry = array(
			$meta_query_key => array(
				'key'     => '_EventCost',
				'value'   => is_array( $value ) ? $value : (float) $value,
				'compare' => $operator,
				'type'    => 'DECIMAL(10,5)',
			),
		);

		if ( null !== $symbol ) {
			$meta_query_entry = array_merge( $meta_query_entry, $this->filter_by_cost_currency_symbol( $symbol )['meta_query'] );
		}

		return array( 'meta_query' => $meta_query_entry );
	}

	/**
	 * Filters events that have a specific cost currency symbol.
	 *
	 * Events with a cost of `0` but a currency symbol set will be fetched when fetching
	 * by their symbols.
	 *
	 * @since TBD
	 *
	 * @param string|array $symbol One or more currency symbols or currency ISO codes. E.g.
	 *                             "$" and "USD".
	 *
	 * @return array An array of arguments that will be added to the current query.
	 */
	public function filter_by_cost_currency_symbol( $symbol ) {
		return array(
			'meta_query' => array(
				'by-cost-currency-symbol' => array(
					'key'     => '_EventCurrencySymbol',
					'value'   => array_unique( (array) $symbol ),
					'compare' => 'IN',
				),
			),
		);
	}

	/**
	 * Filters events that have a cost between two given values.
	 *
	 * Cost search is inclusive.
	 *
	 * @since TBD
	 *
	 * @param      float $low    The lower value of the search interval.
	 * @param      float $high   The high value of the search interval.
	 * @param string     $symbol The desired currency symbol or symbols; this symbol can be a currency ISO code,
	 *                           e.g. "USD" for U.S. dollars, or a currency symbol, e.g. "$".
	 *                           In the latter case results will include any event with the matching currency symbol,
	 *                           this might lead to ambiguous results.
	 *
	 * @return array An array of query arguments that will be added to the main query.
	 */
	public function filter_by_cost_between( $low, $high, $symbol = null ) {
		return $this->by( 'cost', array( $low, $high ), 'BETWEEN', $symbol );
	}

	/**
	 * Filters events that have a cost greater than the given value.
	 *
	 * Cost search is NOT inclusive.
	 *
	 * @since TBD
	 *
	 * @param float  $value      The cost to use for the comparison.
	 * @param string $symbol     The desired currency symbol or symbols; this symbol can be a currency ISO code,
	 *                           e.g. "USD" for U.S. dollars, or a currency symbol, e.g. "$".
	 *                           In the latter case results will include any event with the matching currency symbol,
	 *                           this might lead to ambiguous results.
	 *
	 * @return array An array of query arguments that will be added to the main query.
	 */
	public function filter_by_cost_greater_than( $value, $symbol = null ) {
		return $this->by( 'cost', $value, '>', $symbol );
	}

	/**
	 * Filters events that have a cost less than the given value.
	 *
	 * Cost search is NOT inclusive.
	 *
	 * @since TBD
	 *
	 * @param float  $value      The cost to use for the comparison.
	 * @param string $symbol     The desired currency symbol or symbols; this symbol can be a currency ISO code,
	 *                           e.g. "USD" for U.S. dollars, or a currency symbol, e.g. "$".
	 *                           In the latter case results will include any event with the matching currency symbol,
	 *                           this might lead to ambiguous results.
	 *
	 * @return array An array of query arguments that will be added to the main query.
	 */
	public function filter_by_cost_less_than( $value, $symbol = null ) {
		$this->by( 'cost', $value, '<', $symbol );
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter_postarr_for_update( array $postarr, $post_id ) {
		if ( isset( $postarr['meta_input'] ) ) {
			$postarr = $this->filter_meta_input( $postarr, $post_id );
		}

		return parent::filter_postarr_for_update( $postarr, $post_id );
	}

	/**
	 * Filters and updates the event meta to make sure it makes sense.
	 *
	 * @since TBD
	 *
	 * @param array $postarr The update post array, passed entirely for context purposes.
	 * @param  int  $post_id The ID of the event that's being updated.
	 *
	 * @return array The filtered postarr array.
	 */
	protected function filter_meta_input( array $postarr, $post_id = null ) {
		$postarr = $this->update_date_meta( $postarr, $post_id );
		$postarr = $this->update_linked_post_meta( $postarr );
		$postarr = $this->update_accessory_meta( $postarr, $post_id );

		return $postarr;
	}

	/**
	 *
	 *
	 * @since TBD
	 *
	 * @param array $postarr
	 * @param       $post_id
	 *
	 * @return array
	 */
	protected function update_date_meta( array $postarr, $post_id = null ) {
		set_error_handler( array( $this, 'cast_error_to_exception' ) );

		$was_all_day = (bool) get_post_meta( $post_id, '_EventAllDay', true );
		$is_all_day  = false;
		if ( isset( $postarr['meta_input']['_EventAllDay'] ) && tribe_is_truthy( $postarr['meta_input']['_EventAllDay'] ) ) {
			$postarr['meta_input']['_EventAllDay'] = 'yes';
			$is_all_day                            = true;
		} else {
			unset( $postarr['meta_input']['_EventAllDay'] );
		}

		try {
			$meta                          = $postarr['meta_input'];
			$current_event_timezone_string = Tribe__Events__Timezones::get_event_timezone_string( $post_id );
			$input_timezone                = Tribe__Utils__Array::get(
				$meta,
				'_EventTimezone',
				$current_event_timezone_string
			);
			$timezone                      = Tribe__Timezones::build_timezone_object( $input_timezone );
			$timezone_changed              = $input_timezone !== $current_event_timezone_string;
			$utc                           = new DateTimeZone( 'UTC' );
			$dates_changed                 = array();

			/**
			 * If both local date/time and UTC date/time are provided then the local one overrides the UTC one.
			 * If only one is provided the other one will be calculated and updated.
			 */
			$datetime_format = 'Y-m-d H:i:s';
			foreach ( array( 'Start', 'End' ) as $check ) {
				if ( isset( $meta[ "_Event{$check}Date" ] ) ) {
					$meta_value = $meta[ "_Event{$check}Date" ];

					if ( $meta_value instanceof DateTime || $meta_value instanceof DateTimeImmutable ) {
						$meta_value                 = $meta_value->format( 'Y-m-d H:i:s' );
						$postarr[ 'meta_input' ][ "_Event{$check}Date" ] = $meta_value;
					}

					$date = new DateTimeImmutable( $meta_value, $timezone );

					$utc_date = $date->setTimezone( $utc );
					// Set the UTC date/time from local date/time and timezone; if provided override it.
					$postarr[ 'meta_input' ][ "_Event{$check}DateUTC" ] = $utc_date->format( $datetime_format );
					$dates_changed[ $check ]                        = $utc_date;
				}

				/*
				 * If the UTC date is provided in place of the local date/time then build the
				 * local date/time.
				 */
				if ( isset( $meta[ "_Event{$check}DateUTC" ] ) && empty( $utc_date ) ) {
					$utc_date                                    = new DateTimeImmutable( $meta[ "_Event{$check}DateUTC" ], $utc );
					$postarr[ 'meta_input' ][ "_Event{$check}Date" ] = $utc_date->setTimezone( $timezone )->format( $datetime_format );
					$dates_changed[ $check ]                     = $utc_date;
				}
			}

			if ( $timezone_changed && ! count( $dates_changed ) ) {
				$start_string                                = get_post_meta( $post_id, '_EventStartDate', true );
				$end_string                                  = get_post_meta( $post_id, '_EventEndDate', true );
				$start_date                                  = Tribe__Date_Utils::build_date_object( $start_string, $timezone );
				$end_date                                    = Tribe__Date_Utils::build_date_object( $end_string, $timezone );
				$postarr['meta_input']['_EventStartDateUTC'] = $start_date->setTimezone( $utc )->format( $datetime_format );
				$postarr['meta_input']['_EventEndDateUTC']   = $end_date->setTimezone( $utc )->format( $datetime_format );
			}

			// Sanity check, an event should end after its start.
			$start = $this->get_from_postarr_or_meta( $postarr, '_EventStartDate', $post_id );
			$end   = $this->get_from_postarr_or_meta( $postarr, '_EventEndDate', $post_id );

			$dates_make_sense = true;

			if ( Tribe__Date_Utils::build_date_object( $end ) <= Tribe__Date_Utils::build_date_object( $start ) ) {
				unset(
					$postarr['meta_input']['_EventStartDate'],
					$postarr['meta_input']['_EventStartDateUTC'],
					$postarr['meta_input']['_EventEndDate'],
					$postarr['meta_input']['_EventEndDateUTC'],
					$postarr['meta_input']['_EventDuration'],
					$postarr['meta_input']['_EventTimezone']
				);
				$dates_make_sense = false;
			}

			if ( $dates_make_sense && 2 === count( $dates_changed ) ) {
				/*
				 * If the dates are changed then update the duration to the new one; if the duration is set
				 * in the postarr it will be overridden.
				 */
				list( $start, $end ) = array_values( $dates_changed );
				$postarr['meta_input']['_EventDuration'] = $end->getTimestamp() - $start->getTimestamp();
			} elseif ( isset( $meta['_EventDuration'] ) ) {
				if ( isset( $dates_changed['Start'] ) ) {
					// If we have a duration and the start changed update the end.
					$date_interval                             = new DateInterval( 'PTS' . $meta['_EventDuration'] );
					$postarr['meta_input']['_EventEndDate']    = $dates_changed['Start']
						->add( $date_interval )
						->format( $datetime_format );
					$postarr['meta_input']['_EventEndDateUTC'] = $dates_changed['Start']
						->add( $date_interval )
						->format( $datetime_format );
				} elseif ( isset( $dates_changed['End'] ) ) {
					// If we have a duration and the end changed update the start.
					$date_interval                               = new DateInterval( 'PTS' . $meta['_EventDuration'] );
					$postarr['meta_input']['_EventStartDate']    = $dates_changed['End']
						->sub( $date_interval )
						->format( $datetime_format );
					$postarr['meta_input']['_EventStartDateUTC'] = $dates_changed['End']
						->sub( $date_interval )
						->format( $datetime_format );
				}
			}

			// After all this, if the event is all day recalculate start and end.
			if ( $is_all_day && ! $was_all_day ) {
				// Create the start date object and set it to the end of day.
				$event_start_date = $this->get_from_postarr_or_meta( $postarr, '_EventStartDate', $post_id );
				$event_end_date   = $this->get_from_postarr_or_meta( $postarr, '_EventEndDate', $post_id );
				$start            = new DateTime( tribe_end_of_day( $event_start_date ), $timezone );
				// Then subtract one day from it to set it correctly.
				$one_day    = new DateInterval( 'P1D' );
				$one_second = new DateInterval( 'PT1S' );
				$start->sub( $one_day )->add( $one_second );
				$postarr['meta_input']['_EventStartDate']    = $start->format( $datetime_format );
				$postarr['meta_input']['_EventStartDateUTC'] = $start->setTimezone( $utc )->format( $datetime_format );
				$end                                         = new DateTime( tribe_end_of_day( $event_end_date ), $timezone );
				$postarr['meta_input']['_EventEndDate']      = $end->format( $datetime_format );
				$postarr['meta_input']['_EventEndDateUTC']   = $end->setTimezone( $utc )->format( $datetime_format );
			}

			$postarr['meta_input']['_EventTimezoneAbbr'] = Tribe__Timezones::abbr(
				$this->get_from_postarr_or_meta( $postarr, '_EventStartDate' ),
				$timezone->getName()
			);
		} catch ( Exception $e ) {
			tribe( 'logger' )->log(
				'There was an error updating the dates for event ' . $post_id . ': ' . $e->getMessage(),
				Tribe__Log::ERROR,
				__CLASS__
			);
			// Something went wrong, let's not update dates at all.
			unset(
				$postarr['meta_input']['_EventStartDate'],
				$postarr['meta_input']['_EventStartDateUTC'],
				$postarr['meta_input']['_EventEndDate'],
				$postarr['meta_input']['_EventEndDateUTC'],
				$postarr['meta_input']['_EventDuration'],
				$postarr['meta_input']['_EventTimezone'],
				$postarr['meta_input']['_EventAllDay']
			);
		}

		restore_error_handler();

		return $postarr;
	}

	/**
	 * Filters the post array to make sure linked posts meta makes sense.
	 *
	 * @since TBD
	 *
	 * @param array $postarr The update post array.
	 *
	 * @return array The filtered event post array.
	 */
	protected function update_linked_post_meta( array $postarr ) {
		// @todo crete linked posts here?! Using ORM?
		if ( isset( $postarr['meta_input']['_EventVenueID'] ) && ! tribe_is_venue( $postarr['meta_input']['_EventVenueID'] ) ) {
			unset( $postarr['meta_input']['_EventVenueID'] );
		}

		if ( isset( $postarr['meta_input']['_EventOrganizerID'] ) ) {
			$postarr['meta_input']['_EventOrganizerID'] = (array) $postarr['meta_input']['_EventOrganizerID'];
			$valid                                      = array();
			foreach ( $postarr['meta_input']['_EventOrganizerID'] as $organizer ) {
				if ( ! tribe_is_organizer( $organizer ) ) {
					continue;
				}
				$valid[] = $organizer;
			}
			if ( ! count( $valid ) ) {
				unset( $postarr['meta_input']['_EventOrganizerID'] );
			} else {
				$postarr['meta_input']['_EventOrganizerID'] = $valid;
			}
		}

		return $postarr;
	}

	/**
	 * Updates an event accessory meta and attributes.
	 *
	 * @since TBD
	 *
	 * @param array $postarr The candidate post array for the update or insertion.
	 * @param int   $post_id The ID of the event that is being updated.
	 *
	 * @return array The updated post array for update or insertion.
	 */
	protected function update_accessory_meta( array $postarr, $post_id ) {
		$postarr['meta_input']['_EventOrigin'] = 'events-calendar';

		// Set the map-related settings, default to `true` for new events.
		foreach ( array( '_EventShowMap', '_EventShowMapLink' ) as $meta_key ) {
			$new_value = tribe_is_truthy( $this->get_from_postarr_or_meta( $postarr, $meta_key, $post_id, true ) );
			if ( $new_value !== tribe_is_truthy( get_post_meta( $post_id, $meta_key, true ) ) ) {
				$postarr['meta_input'][ $meta_key ] = $new_value;
			}
		}

		$currency_symbol_positions = array( 'prefix', 'postfix' );
		if ( isset( $postarr['meta_input']['_EventCurrencyPosition'] )
		     && ! in_array( $postarr['meta_input']['_EventCurrencyPosition'], $currency_symbol_positions, true )
		) {
			$postarr['meta_input']['_EventCurrencyPosition'] = 'prefix';
		}

		if ( isset( $postarr['meta_input']['_EventHideFromUpcoming'] ) ) {
			if ( tribe_is_truthy( $postarr['meta_input']['_EventHideFromUpcoming'] ) ) {
				$postarr['meta_input']['_EventHideFromUpcoming'] = 'yes';
			} else {
				unset( $postarr['meta_input']['_EventHideFromUpcoming'] );
			}
		}

		if ( isset( $postarr['meta_input']['sticky'] ) ) {
			if ( tribe_is_truthy( $postarr['meta_input']['sticky'] ) ) {
				$postarr['menu_order'] = - 1;
			} else {
				$postarr['menu_order'] = 0;
			}
			unset( $postarr['meta_input']['sticky'] );
		}

		if ( isset( $postarr['meta_input']['_tribe_featured'] ) ) {
			if ( tribe_is_truthy( $postarr['meta_input']['_tribe_featured'] ) ) {
				$postarr['meta_input']['_tribe_featured'] = true;
			} else {
				unset( $postarr['meta_input']['_tribe_featured'] );
			}
		}

		return $postarr;
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter_postarr_for_create( array $postarr ) {
		// Require some minimum fields.
		if ( ! isset(
			$postarr['post_title'],
			$postarr['meta_input']['_EventEndDate']
		) ) {
			return false;
		}

		return parent::filter_postarr_for_create( $this->filter_meta_input( $postarr ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function order_by( $order_by ) {
		/** @var \wpdb $wpdb */
		global $wpdb;

		$check_orderby = $order_by;

		if ( ! is_array( $check_orderby ) ) {
			$check_orderby = explode( ' ', $check_orderby );
		}

		$timestamp_key = 'TIMESTAMP(mt1.meta_value)';

		if ( isset( $check_orderby['event_date'] ) || in_array( 'event_date', $check_orderby, true ) ) {
			$check_orderby  = 'event_date';
			$postmeta_table = "orderby_{$check_orderby}_meta";

			$meta_key = '_EventStartDate';

			/**
			 * When the "Use site timezone everywhere" option is checked in events settings,
			 * the UTC time for event start and end times will be used. This filter allows the
			 * disabling of that in certain contexts, so that local (not UTC) event times are used.
			 *
			 * @since 4.6.10
			 *
			 * @param boolean $force_local_tz Whether to force the local TZ.
			 */
			$force_local_tz = apply_filters( 'tribe_events_query_force_local_tz', false );

			if ( ! $force_local_tz && Tribe__Events__Timezones::is_mode( 'site' ) ) {
				$event_start_key .= 'UTC';
			}

			$this->filter_query->join( $wpdb->prepare( "
				LEFT JOIN {$wpdb->postmeta} AS {$postmeta_table}
					ON (
						{$postmeta_table}.post_id = {$wpdb->posts}.ID
						AND {$postmeta_table}.meta_key = %s
					)
			", $meta_key ) );
			$this->filter_query->orderby( $check_orderby );
			$this->filter_query->fields( "MIN( {$postmeta_table}.meta_value ) AS {$check_orderby}" );
		} elseif ( isset( $check_orderby['organizer'] ) || in_array( 'organizer', $check_orderby, true ) ) {
			$check_orderby  = 'organizer';
			$postmeta_table = "orderby_{$check_orderby}_meta";
			$posts_table    = "orderby_{$check_orderby}_posts";

			$meta_key = '_EventOrganizerID';

			$this->filter_query->join( $wpdb->prepare( "
				LEFT JOIN {$wpdb->postmeta} AS {$postmeta_table}
					ON (
						{$postmeta_table}.post_id = {$wpdb->posts}.ID 
						AND {$postmeta_table}.meta_key = %s
					)
				LEFT JOIN {$wpdb->posts} AS {$posts_table}
					ON {$wpdb->posts}.ID = {$postmeta_table}.meta_value
			", $meta_key ) );
			$this->filter_query->orderby( $check_orderby );
			$this->filter_query->fields( "{$posts_table}.post_title AS {$check_orderby}" );
		} elseif ( isset( $check_orderby['venue'] ) || in_array( 'venue', $check_orderby, true ) ) {
			$check_orderby  = 'venue';
			$postmeta_table = "orderby_{$check_orderby}_meta";
			$posts_table    = "orderby_{$check_orderby}_posts";

			$meta_key = '_EventVenueID';

			$this->filter_query->join( $wpdb->prepare( "
				LEFT JOIN {$wpdb->postmeta} AS {$postmeta_table}
					ON (
						{$postmeta_table}.post_id = {$wpdb->posts}.ID 
						AND {$postmeta_table}.meta_key = %s
					)
				LEFT JOIN {$wpdb->posts} AS {$posts_table}
					ON {$wpdb->posts}.ID = {$postmeta_table}.meta_value
			", $meta_key ) );
			$this->filter_query->orderby( $check_orderby );
			$this->filter_query->fields( "{$posts_table}.post_title AS {$check_orderby}" );
		} elseif ( isset( $check_orderby[ $timestamp_key ] ) || in_array( $timestamp_key, $check_orderby, true ) ) {
			$this->filter_query->orderby( $timestamp_key );
		}

		return parent::order_by( $order_by );
	}

	/**
	 * Returns a filtered list of filters that are leveraging the event start and/or
	 * end dates.
	 *
	 * @since TBD
	 *
	 * @return array The filtered list of filters that are leveraging the event start and/or end dates
	 */
	public function get_date_filters() {
		$date_filters = array(
			'starts_before',
			'starts_after',
			'starts_between',
			'ends_before',
			'ends_after',
			'ends_between',
			'starts_and_ends_between',
			'runs_between',
			'on_calendar_grid',
		);

		/**
		 * Filters the list of filters that should be considered related to an event start and/or end
		 * dates.
		 *
		 * @since TBD
		 *
		 * @param array $date_filters The list of filters that should be considered related to an event start and/or end
		 *                            dates.
		 * @param Tribe__Events__Repositories__Event This repository instance.
		 */
		return apply_filters( "tribe_repository_{$this->filter_name}_date_filters", $date_filters, $this );
	}

	/**
	 * Whether the repository read operations have any kind of date-related filter
	 * applied or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the repository read operations have any kind of date-related filter applied or not.
	 */
	public function has_date_filters() {
		foreach ( $this->get_date_filters() as $filter ) {
			if ( $this->has_filter( $filter ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns an array of the date filters applied by the repository and their values.
	 *
	 * @since TBD
	 *
	 * @return array A map of applied date filters and their values.
	 */
	public function get_applied_date_filters() {
		$applied = array();
		foreach ( $this->get_date_filters() as $filter ) {
			if ( $this->has_filter( $filter ) ) {
				$applied[ $filter ] = $this->current_filters[ $filter ];
			}
		}

		return $applied;
	}

	/**
	 * Overrides the base method to attach the `date_filters` property to the query
	 * object.
	 *
	 * @since TBD
	 *
	 * @param bool $use_query_builder Whether to use the query builder or not.
	 *
	 * @return WP_Query The built query object with the `date_filters` property set.
	 */
	public function build_query( $use_query_builder = true ) {
		$query = parent::build_query( $use_query_builder );

		if ( ! $use_query_builder || $this->query_builder === null ) {
			$query->date_filters = $this->get_applied_date_filters();
		}

		return $query;
	}
}
