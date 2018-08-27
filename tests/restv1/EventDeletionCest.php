<?php

use Step\Restv1\RestGuy as Tester;
use Tribe__Image__Uploader as Image;
use Tribe__Timezones as Timezones;

class EventDeletionCest extends BaseRestCest {
	/**
	 * It should return 400 if trying to delete event passing bad event ID
	 * @test
	 */
	public function it_should_return_400_if_trying_to_delete_event_passing_bad_event_id( Tester $I ) {
		// pass an ID that does not exist
		$I->sendDELETE( $this->events_url . "/23" );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 401 if user cannot delete events
	 * @test
	 */
	public function it_should_return_401_if_user_cannot_delete_events( Tester $I ) {
		$id = $I->haveEventInDatabase();

		$I->sendDELETE( $this->events_url . "/{$id}" );

		$I->seeResponseCodeIs( 401 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 200 when deleting event
	 * @test
	 */
	public function it_should_return_200_when_deleting_event( Tester $I ) {
		$id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendDELETE( $this->events_url . "/{$id}" );

		$I->seeResponseCodeIs( 200 );

		$I->seeResponseContainsJson( [ 'id' => $id ] );
	}

	/**
	 * It should return 410 when re-deleting a deleted event
	 * @test
	 */
	public function it_should_return_410_when_re_deleting_a_deleted_event( Tester $I ) {
		$id = $I->haveEventInDatabase( [ 'post_status' => 'trash' ] );

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendDELETE( $this->events_url . "/{$id}" );

		$I->seeResponseCodeIs( 410 );
		$I->seeResponseIsJson();
	}
}
