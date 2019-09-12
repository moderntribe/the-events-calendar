<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;

class Month_ViewTest extends ViewTestCase {
	use MatchesSnapshots;

	/**
	 * The mock rendering context.
	 *
	 * @var \Tribe__Context|\WP_UnitTest_Factory|null
	 */
	protected $context;

	public function setUp() {
		parent::setUp();

		$now = new \DateTime( $this->mock_date_value );

		$this->context = tribe_context()->alter(
			[
				'today'      => $this->mock_date_value,
				'now'        => $this->mock_date_value,
				'event_date' => $now->format( 'Y-m-d' ),
			]
		);
	}

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		$month_view = View::make( Month_View::class, $this->context );

		$this->assertEmpty( $month_view->found_post_ids() );

		$this->assertMatchesSnapshot( $month_view->get_html() );
	}

	/**
	 * Test render with events
	 */
	public function test_render_with_events() {
		$timezone_string = 'Europe/Paris';
		$timezone        = new \DateTimeZone( $timezone_string );
		update_option( 'timezone_string', $timezone_string );

		$now = new \DateTimeImmutable( $this->mock_date_value, $timezone );

		$events    = array_map(
			static function ( $i ) use ( $now, $timezone ) {
				return tribe_events()->set_args(
					[
						'start_date' => $now->setTime( 10 + $i, 0 ),
						'timezone'   => $timezone,
						'duration'   => 3 * HOUR_IN_SECONDS,
						'title'      => 'Test Event - ' . $i,
						'status'     => 'publish',
					]
				)->create();
			},
			range( 1, 3 )
		);
		$event_ids = wp_list_pluck($events,'ID') ;
		$remapped_post_ids = $this->remap_posts( $events, [
			'events/featured/1.json',
			'events/single/1.json',
			'events/single/2.json'
		] );
		add_filter(
			'tribe_events_views_v2_view_data',
			function ( array $data ) use ( $remapped_post_ids ) {
				foreach ( $data['events'] as &$day_events_ids ) {
					$day_events_ids = $this->remap_post_id_array( $day_events_ids, $remapped_post_ids );
				}

				return $data;
			}
		);

		/** @var Month_View $month_view */
		$month_view      = View::make( Month_View::class, $this->context );
		$html = $month_view->get_html();

		$this->assertEquals( $event_ids, $month_view->found_post_ids() );

		foreach ( $month_view->get_grid_days( $now->format( 'Y-m' ) ) as $date => $found_day_ids ) {
			$day          = new \DateTimeImmutable( $date, $timezone );
			$expected_ids = tribe_events()
				->where(
					'date_overlaps',
					$day->setTime( 0, 0 ),
					$day->setTime( 23, 59, 59 ),
					$timezone
				)->get_ids();

			$this->assertEquals(
				$expected_ids,
				$found_day_ids,
				sprintf(
					'Day %s event IDs mismatch, expected %s, got %s',
					$day->format( 'Y-m-d' ),
					json_encode( $expected_ids ),
					json_encode( $found_day_ids )
				)
			);
		}

		 $this->assertMatchesSnapshot( $html );
	}

	public function today_url_data_sets() {
		$event_dates    = [
			'lt' => '2019-02-01',
			'eq' => '2019-02-02',
			'gt' => '2019-02-03',
		];
		$now_times      = [
			'eq' => '2019-02-02 00:00:00',
			'gt' => '2019-02-02 09:00:00',
		];
		$event_displays = [
			'no'   => '/events/month/',
			'past' => '/events/month/',
		];
		$today          = '2019-02-02 00:00:00';

		foreach ( $now_times as $now_key => $now ) {
			foreach ( $event_dates as $event_date_key => $event_date ) {
				foreach ( $event_displays as $event_display => $expected ) {
					$set_name      = "event_date_{$event_date_key}_today_w_{$now_key}_time_w_{$event_display}_display_mode";
					$event_display = 'no' === $event_display ? '' : $event_display;

					yield $set_name => [ $today, $now, $event_date, $event_display, $expected ];
				}
			}
		}
	}

	/**
	 * It should correctly build today_url
	 *
	 * @test
	 * @dataProvider today_url_data_sets
	 */
	public function should_correctly_build_today_url( $today, $now, $event_date, $event_display_mode, $expected ) {
		$values  = [
			'today'              => $today,
			'now'                => $now,
			'event_date'         => $event_date,
			'event_display_mode' => $event_display_mode,
		];
		$context = $this->get_mock_context()->alter( array_filter( $values ) );
		$mock_repository = $this->makeEmpty(
			\Tribe__Repository__Interface::class,
			[
				'count' => 23
			]
		);

		$view = View::make( Month_View::class, $context );
		$view->set_repository( $mock_repository );

		$this->assertEquals( home_url( $expected ), $view->get_today_url( true ) );
	}
}
