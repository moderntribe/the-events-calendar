<?php

namespace Tribe\Events\Views\V2\Partials\Month\Calendar_Body\Day;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class More_EventsTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/calendar-body/day/more-events';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day'   => 0,
			'month' => [],
		] ) );
	}
}
