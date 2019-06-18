<?php
/**
 * View: Day View
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/day.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

use Tribe\Events\Views\V2\Rest_Endpoint;

$events = $this->get( 'events' );
?>
<div
	class="tribe-common tribe-events tribe-events-view"
	data-js="tribe-events-view"
	data-view-rest-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
	data-view-rest-url="<?php echo esc_url( tribe( Rest_Endpoint::class )->get_url() ); ?>"
>
	<div class="tribe-common-l-container tribe-events-l-container">
		<?php $this->template( 'loader', [ 'text' => 'Loading...' ] ); ?>

		<?php
		$this->template( 'data', [] );
		?>

		<?php $this->template( 'events-bar' ); ?>

		<?php $this->template( 'top-bar' ); ?>

		<div class="tribe-events-calendar-day">

			<?php foreach ( $events as $event ) : ?>

				<?php // @todo: include day event markup here. ?>

			<?php endforeach; ?>

		</div>

		<?php // @todo: include day navigation here. ?>
	</div>

</div>
