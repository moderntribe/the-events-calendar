<?php


class EventByTagArchiveCest extends BaseRestCest {

	/**
	 * @test
	 * it should return 400 if hitting non existing tag
	 */
	public function it_should_return_400_if_hitting_non_existing_tag( Restv1Tester $I ) {
		$I->sendGET( $this->events_url, [ 'tags' => [ 'tag1' ] ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return 200 if hitting empty tag archive
	 */
	public function it_should_return_200_if_hitting_empty_tag_archive( Restv1Tester $I ) {
		$I->haveTermInDatabase( 'tag1', 'post_tag', [ 'slug' => 'tag1' ] );

		$I->sendGET( $this->events_url, [ 'tags' => [ 'tag1' ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );

		$I->assertCount( 0, $response->events );
		$I->assertEquals( 0, $response->total );
		$I->assertEquals( 0, $response->total_pages );
		$I->seeHttpHeader( 'X-TEC-Total', 0 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 0 );
		$I->assertArrayNotHasKey( 'previous_rest_url', (array) $response );
		$I->assertArrayNotHasKey( 'next_rest_url', (array) $response );
	}

	/**
	 * @test
	 * it should return events for the tag if existing
	 */
	public function it_should_return_events_for_the_tag_if_existing( Restv1Tester $I ) {
		$I->haveManyEventsInDatabase( 3, [ 'tags' => [ 'tag1' ] ] );
		$I->haveManyEventsInDatabase( 3 );

		$I->sendGET( $this->events_url, [ 'tags' => [ 'tag1' ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->events );
	}

	/**
	 * @test
	 * it should allow pagination on tag archive
	 */
	public function it_should_allow_pagination_on_tag_archive( Restv1Tester $I ) {
		$I->haveManyEventsInDatabase( 4, [ 'tags' => [ 'tag1' ] ] );
		$I->haveManyEventsInDatabase( 4 );

		$I->sendGET( $this->events_url, [ 'tags' => [ 'tag1' ], 'per_page' => 3, 'page' => 2 ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 1, $response->events );
	}

	/**
	 * It should allow selecting events by an array of categories
	 *
	 * A logic OR
	 *
	 * @test
	 */
	public function should_allow_selecting_events_by_an_array_of_categories( Restv1Tester $I ) {
		$tag_1_events = $I->haveManyEventsInDatabase( 2, [ 'tags' => [ 'tag1' ] ] );
		$tag_2_events = $I->haveManyEventsInDatabase( 2, [ 'tags' => [ 'tag2' ] ] );
		$I->haveManyEventsInDatabase( 2 );

		$I->sendGET( $this->events_url, [ 'tags' => [ 'tag1', 'tag2' ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 4, $response['events'] );
		$I->assertEquals( array_merge( $tag_1_events, $tag_2_events ), array_column( $response['events'], 'id' ) );
	}

	/**
	 * It should allow selecting events by comma separated list of tags
	 *
	 * A logic OR
	 *
	 * @test
	 */
	public function should_allow_selecting_events_by_a_comma_separated_list_of_tags( Restv1Tester $I ) {
		list( $tag_1_term_id, $_ ) = $I->haveTermInDatabase( 'tag1', 'post_tag', [ 'slug' => 'tag1' ] );
		list( $tag_2_term_id, $_ ) = $I->haveTermInDatabase( 'tag2', 'post_tag', [ 'slug' => 'tag2' ] );
		$tag_1_events = $I->haveManyEventsInDatabase( 2, [ 'tags' => [ 'tag1' ] ] );
		$tag_2_events = $I->haveManyEventsInDatabase( 2, [ 'tags' => [ 'tag2' ] ] );
		$I->haveManyEventsInDatabase( 2 );

		$I->sendGET( $this->events_url, [ 'tags' => "{$tag_1_term_id},{$tag_2_term_id}" ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 4, $response['events'] );
		$I->assertEquals( array_merge( $tag_1_events, $tag_2_events ), array_column( $response['events'], 'id' ) );
	}
}
