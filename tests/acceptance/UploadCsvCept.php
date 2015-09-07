<?php

use Tribe\Events\Test\AcceptanceTester;

$scenario->group('upload');

/**
 * Tests event creation functionality
 *
 * @group   core
 *
 * @package AccceptanceTester
 */
class UploadCsvCept extends AcceptanceTester {

	// Acceptable $type: venues, organizers, events
	public function tribe_upload_csv ($type, $file) {

		// Upload CSV
		$this->amOnPage('/wp-admin/edit.php?post_type=tribe_events&page=events-importer');

		// Might be an insufficient permissions page or some such
		$this->see('Events Import (CSV)', 'h2');

		$this->selectOption('import_type', $type);
		$this->attachFile('import_file', $file);
		//$this->checkOption('import_header');
		// @todo Wish there was something more specific to click on, patch core give element a name
		$this->click('.tribe_settings input[type=submit]');

		// Import CSV
		$this->see('Column Mapping', 'h3');

		// For each column mapping element select its corresponding header from CSV file
		// IMPORTANT: Reliant on CSV Headers matching <option> headers, should one change this will fail
		// They will look something like this:
		// <select name="column_map[0]">
		// <option>CSV Header</option>

		$csv_file = fopen('./tests/codeception/_data/'.$file, 'r');
		$csv_headers = fgetcsv($csv_file, 0, ',');

		for($i = 0; $i < count($csv_headers); $i++) {
			$this->selectOption('column_map['.$i.']', $csv_headers[$i]);
		}

		$this->seeInField('#submit', 'Perform Import');
		$this->click('#submit');


		// Check that it imported
		$this->see('Import Result');
	}

}


$I = new UploadCsvCept($scenario);
$I->am('administrator');
$I->wantTo('Test CSV import');

// Init self-awareness
$I->loginAsAdmin();
$I->amOnPluginsPage();
$I->activatePlugin('the-events-calendar');
$I->see('Thanks for Updating');
$I->lookForwardTo('Seeing them events, organizers, and venues in the database');

// Upload Organizer
$I->tribe_upload_csv('organizers', 'csv-test-organizers.csv');
// I pity the fool who maintains this
$I->amOnPage('/wp-admin/post.php?post=4&action=edit');  //TODO Helper
$I->seeInField('post_title', 'George-Michael Bluth');
$I->seeInField('#OrganizerPhone', '+1-987-555-1238');
$I->seeInField('#OrganizerWebsite', 'http://fakeblock.com');
$I->seeInField('#OrganizerEmail', 'boygeorge@halliburtonteen.com');

// Upload Venues
$I->tribe_upload_csv('venues', 'csv-test-venues.csv');
$I->amOnPage('/wp-admin/post.php?post=12&action=edit'); //TODO Helper

$I->seeInField('post_title', 'Soup Kitchen International');
$I->seeInField('venue[Address]', '259-A West 55th Street');
$I->seeInField('venue[City]', 'New York');
//$I->seeOptionIsSelected('venue[Country]', 'United States'); 	//hidden using Chosen - build helper
//$I->seeOptionIsSelected('venue[State]', 'New York'); 			//hidden using Chosen - build helper
$I->seeInField('venue[Zip]', '10019');
$I->seeInField('venue[Phone]', '+1 (800) 555-8234');
//@todo Test the following once it's importable
//$I->seeInField('venue[URL]', '');
//$I->seeCheckboxIsChecked('venue[ShowMap]');
//$I->seeCheckboxIsChecked('venue[ShowMapLink]');

// Upload Events
$I->tribe_upload_csv('events', 'csv-test-events.csv');
$I->amOnPage('/wp-admin/post.php?post=17&action=edit'); //TODO Helper

$I->seeInField('post_title', 'Ankh-Sto Associates');
$I->switchToIFrame('content_ifr');
$I->see('Ankh-Sto Associates description goes here.', '.content');
$I->switchToIFrame();

//$I->seeCheckboxIsChecked('EventAllDay'); @todo this lines relies on the CSV importer understanding the value "Yes", as per our docs: http://tri.be/using-the-events-calendars-csv-importer/
$I->seeInField('EventStartDate', '2014-11-25');
//$I->seeInField('EventStartHour', '01');
//$I->seeInField('EventStartMinute', '00');
//$I->seeInField('EventStartMeridian', 'am');
$I->seeInField('EventEndDate', '2014-11-25');
//$I->seeInField('EventEndHour', '01');
//$I->seeInField('EventEndMinute', '00');
//$I->seeInField('EventEndMeridian', 'am');

//$I->seeOptionIsSelected('venue[VenueID]', 'The Shire');  //hidden using Chosen - build helper
$I->seeCheckboxIsChecked('venue[EventShowMap]');
//$I->seeCheckboxIsChecked('venue[EventShowMapLink]'); @todo this lines relies on the CSV importer understanding the value "1", as per our docs: http://tri.be/using-the-events-calendars-csv-importer/
//$I->seeOptionIsSelected('organizer[OrganizerID]', 'Elvis'); //hidden using CHosen - build helper
$I->seeInField('EventURL', 'https://ankh-sto-associates.gov');

// Codeception errors our when you try to check for blank fields
//$I->seeInField('EventCurrencySymbol', '');
//$I->seeInField('EventCurrencyPosition', '');
//$I->seeInField('EventCost', '');

$I->seeCheckboxIsChecked('#in-tribe_events_cat-2'); // Convention
$I->dontSeeCheckboxIsChecked('#in-tribe_events_cat-4'); // Concert
$I->dontSeeCheckboxIsChecked('#in-tribe_events_cat-3'); // Conference
