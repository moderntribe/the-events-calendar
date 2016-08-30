<?php

class Tribe__Events__Aggregator__Record__Queue {
	public static $in_progress_key = 'tribe_aggregator_queue_';
	public static $queue_key = 'queue';
	public static $activity_key = 'activity_log';
	public $record_id;
	public $record;

	protected $fetching = false;
	protected $importer;
	protected $total = 0;
	protected $updated = 0;
	protected $created = 0;
	protected $skipped = 0;
	protected $category = 0;
	protected $images = 0;
	protected $venues = 0;
	protected $organizers = 0;
	protected $remaining = array();

	public function __construct( $record_id, $items = array() ) {
		$this->record_id = $record_id;
		$this->record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $this->record_id );

		if ( ! empty( $items ) ) {
			if ( 'fetch' === $items ) {
				$this->fetching = true;
				$this->remaining = 'fetch';
			} else {
				$this->init_queue( $items );
			}

			$this->save();
		} else {
			$this->load_queue();
		}
	}

	/**
	 * Initializes the queue vars and computes initial counts
	 *
	 * @param array $items Items to add to the queue
	 */
	public function init_queue( $items ) {
		if ( 'csv' === $this->record->origin ) {
			$this->record->reset_tracking_options();
			$this->importer = $items;
			$this->total = $this->importer->get_line_count();
			$this->remaining = array_fill( 0, $this->total, true );
		} else {
			$this->remaining = $items;
			$this->total = count( $this->remaining );
		}
	}

	/**
	 * Fetches queue data and assigns it into class properties
	 */
	public function load_queue() {
		$activity = empty( $this->record->meta[ self::$activity_key ] ) ? array() : $this->record->meta[ self::$activity_key ];
		$queue = empty( $this->record->meta[ self::$queue_key ] ) ? array() : $this->record->meta[ self::$queue_key ];

		if ( 'fetch' === $queue ) {
			$this->fetching = true;
		} else {
			$queue = (array) $queue;
		}

		$this->total     = empty( $activity['total'] ) ? 0 : $activity['total'];
		$this->updated   = empty( $activity['updated'] ) ? 0 : $activity['updated'];
		$this->created   = empty( $activity['created'] ) ? 0 : $activity['created'];
		$this->skipped   = empty( $activity['skipped'] ) ? 0 : $activity['skipped'];
		$this->category    = empty( $activity['category'] ) ? 0 : $activity['category'];
		$this->images    = empty( $activity['images'] ) ? 0 : $activity['images'];
		$this->venues    = empty( $activity['venues'] ) ? 0 : $activity['venues'];
		$this->organizers = empty( $activity['organizers'] ) ? 0 : $activity['organizers'];
		$this->remaining = empty( $queue ) ? array() : $queue;
	}

	/**
	 * Returns whether or not the queue is empty
	 *
	 * @return bool
	 */
	public function is_empty() {
		return empty( $this->remaining );
	}

	/**
	 * Returns the quantity of items remaining in the queue
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->remaining );
	}

	/**
	 * Returns the total number of items that have been and will be processed in the queue
	 *
	 * @return int
	 */
	public function total() {
		return $this->total;
	}

	/**
	 * Returns the number of items that have been updated
	 *
	 * @return int
	 */
	public function updated() {
		return $this->updated;
	}

	/**
	 * Returns the number of items that have been created
	 *
	 * @return int
	 */
	public function created() {
		return $this->created;
	}

	/**
	 * Returns the number of items that have been skipped
	 *
	 * @return int
	 */
	public function skipped() {
		return $this->skipped;
	}

	/**
	 * Returns the number of categories imported along with events
	 *
	 * @return int
	 */
	public function category() {
		return $this->category;
	}

	/**
	 * Returns the number of images imported along with events
	 *
	 * @return int
	 */
	public function images() {
		return $this->images;
	}

	/**
	 * Returns the number of venues imported along with events
	 *
	 * @return int
	 */
	public function venues() {
		return $this->venues;
	}

	/**
	 * Returns the number of organizers imported along with events
	 *
	 * @return int
	 */
	public function organizers() {
		return $this->organizers;
	}

	/**
	 * Returns relevant class properties as an activity array
	 *
	 * @return array
	 */
	public function activity() {
		return array(
			'total'     => $this->total,
			'updated'   => $this->updated,
			'created'   => $this->created,
			'skipped'   => $this->skipped,
			'category'  => $this->category,
			'images'    => $this->images,
			'venues'    => $this->venues,
			'organizers' => $this->organizers,
			'remaining' => count( $this->remaining ),
		);
	}

	/**
	 * Saves queue data to relevant meta keys on the post
	 */
	public function save() {
		$activity = $this->activity();

		$this->record->update_meta( self::$activity_key, $activity );

		if ( empty( $this->remaining ) ) {
			$this->record->delete_meta( self::$queue_key );
		} else {
			$this->record->update_meta( self::$queue_key, $this->remaining );
		}
	}

	/**
	 * Processes a batch for the queue
	 *
	 * @return array|WP_Error
	 */
	public function process( $batch_size = null ) {
		if ( $this->fetching ) {
			$data = $this->record->prep_import_data();

			if (
				'fetch' === $data
				|| ! is_array( $data )
				|| is_wp_error( $data )
			) {
				$activity = $this->activity();
				$activity['batch_process'] = 0;
				return $activity;
			}

			$this->init_queue( $data );
			$this->save();
		}

		$items = array();

		if ( ! $batch_size ) {
			$batch_size = apply_filters( 'tribe_aggregator_batch_size', Tribe__Events__Aggregator__Record__Queue_Processor::$batch_size );
		}

		for ( $i = 0; $i < $batch_size; $i++ ) {
			if ( empty( $this->remaining ) ) {
				break;
			}

			$items[] = array_shift( $this->remaining );
		}

		if ( 'csv' === $this->record->origin ) {
			$this->record->continue_import();
			$results = get_option( 'tribe_events_import_log' );
		} else {
			$results = $this->record->insert_posts( $items );
		}

		// grab the results from THIS batch
		$updated = empty( $results['updated'] ) ? 0 : $results['updated'];
		$created = empty( $results['created'] ) ? 0 : $results['created'];
		$skipped = empty( $results['skipped'] ) ? 0 : $results['skipped'];
		$category = empty( $results['category'] ) ? 0 : $results['category'];
		$images = empty( $results['images'] ) ? 0 : $results['images'];
		$venues = empty( $results['venues'] ) ? 0 : $results['venues'];
		$organizers = empty( $results['organizers'] ) ? 0 : $results['organizers'];

		if ( 'csv' === $this->record->origin ) {
			// update the running total across all batches
			$this->updated = $updated;
			$this->created = $created;
			$this->skipped = $skipped;
			$this->category = $category;
			$this->images = $images;
			// note: organizers and venues are imported differently for CSV
			$this->venues = $venues;
			$this->organizers = $organizers;
		} else {
			// update the running total across all batches
			$this->updated += $updated;
			$this->created += $created;
			$this->skipped += $skipped;
			$this->category += $category;
			$this->images += $images;
			$this->venues += $venues;
			$this->organizers += $organizers;
		}

		$this->save();

		$activity = $this->activity();

		$activity['batch_process'] = $activity['updated'] + $activity['created'] + $activity['skipped'];

		if ( empty( $this->remaining ) ) {
			$this->record->complete_import( $activity );
		}

		return $activity;
	}

	/**
	 * Returns the total progress made on processing the queue so far as a percentage.
	 *
	 * @return int
	 */
	public function progress_percentage() {
		if ( 0 === $this->total ) {
			return 0;
		}

		$complete = $this->total - $this->count();
		$percent = ( $complete / $this->total ) * 100;
		return (int) $percent;
	}

	/**
	 * Sets a flag to indicate that update work is in progress for a specific event:
	 * this can be useful to prevent collisions between cron-based updated and realtime
	 * updates.
	 *
	 * The flag naturally expires after an hour to allow for recovery if for instance
	 * execution hangs half way through the processing of a batch.
	 */
	public function set_in_progress_flag() {
		set_transient( self::$in_progress_key . $this->record_id, true, HOUR_IN_SECONDS );
	}

	/**
	 * Clears the in progress flag.
	 */
	public function clear_in_progress_flag() {
		delete_transient( self::$in_progress_key . $this->record_id );
	}

	/**
	 * Indicates if the queue for the current event is actively being processed.
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		return (bool) get_transient( self::$in_progress_key . $this->record_id );
	}

}
