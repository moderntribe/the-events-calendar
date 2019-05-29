<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month;

use Tribe\Events\Views\V2\TestHtmlCase;

class MonthDayTest extends TestHtmlCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$template = $this->template->template( 'month/day', [ 'day' => 1, 'week' => 1 ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__day' )->count(),
			1,
			'Month Day HTML needs to contain one ".tribe-events-calendar-month__day" element'
		);

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__day-date' )->count(),
			1,
			'Month Day HTML needs to contain one ".tribe-events-calendar-month__day-date" element'
		);
	}

	/**
	 * @test
	 */
	public function it_should_contain_a11y_attributes() {
		$template = $this->template->template( 'month/day', [ 'day' => 1, 'week' => 1 ] );
		$html = $this->document->html( $template );
		$day = $html->find( '.tribe-events-calendar-month__day' );


		$this->assertTrue(
			$day->is( '[role="gridcell"]' ),
			'Month Day needs to be role="gridcell"'
		);

	}
}
