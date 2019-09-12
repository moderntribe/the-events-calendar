<?php

namespace Tribe\Events\Views\V2\Partials\Day_View;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class NavTest extends HtmlPartialTestCase
{

	protected $partial_path = 'day/nav';

	/**
	 * Test static render
	 * @todo remove this static HTML test once the partial is dynamic.
	 */
	public function test_static_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'prev_url' => '#',
			'next_url' => '#',
		] ) );
	}
}
