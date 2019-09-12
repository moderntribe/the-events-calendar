<?php

namespace Tribe\Events\Views\V2\Partials\Month;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Calendar_HeaderTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/calendar-header';

	/**
	 * Test static render
	 */
	public function test_static_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
