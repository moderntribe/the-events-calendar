<?php
/**
 * View: Recent Past View
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/recent-past.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var array    $events               The array containing the events.
 * @var string   $rest_url             The REST URL.
 * @var string   $rest_nonce           The REST nonce.
 * @var int      $should_manage_url    int containing if it should manage the URL.
 * @var bool     $disable_event_search Boolean on whether to disable the event search.
 * @var string[] $container_classes    Classes used for the container of the view.
 * @var array    $container_data       An additional set of container `data` attributes.
 * @var string   $breakpoint_pointer   String we use as pointer to the current view we are setting up with breakpoints.
 */

$header_classes = [ 'tribe-events-header' ];
if ( empty( $disable_event_search ) ) {
	$header_classes[] = 'tribe-events-header--has-event-search';
}
?>
<div
	<?php tribe_classes( $container_classes ); ?>
	data-js="tribe-events-view"
	data-view-rest-nonce="<?php echo esc_attr( $rest_nonce ); ?>"
	data-view-rest-url="<?php echo esc_url( $rest_url ); ?>"
	data-view-manage-url="<?php echo esc_attr( $should_manage_url ); ?>"
	<?php foreach ( $container_data as $key => $value ) : ?>
		data-view-<?php echo esc_attr( $key ) ?>="<?php echo esc_attr( $value ) ?>"
	<?php endforeach; ?>
	<?php if ( ! empty( $breakpoint_pointer ) ) : ?>
		data-view-breakpoint-pointer="<?php echo esc_attr( $breakpoint_pointer ); ?>"
	<?php endif; ?>
>
	<div class="recent-past-events-container">
		<?php $this->template( 'components/loader', [ 'text' => __( 'Loading...', 'the-events-calendar' ) ] ); ?>

<!--		<?php /*$this->template( 'components/json-ld-data' ); */?>

		<?php /*$this->template( 'components/data' ); */?>

		<?php /*$this->template( 'components/before' ); */?>

		<header <?php /*tribe_classes( $header_classes ); */?>>
			<?php /*$this->template( 'components/messages' ); */?>

			<?php /*$this->template( 'components/breadcrumbs' ); */?>

			<?php /*$this->template( 'components/events-bar' ); */?>

			<?php /*$this->template( 'recent-past/top-bar' ); */?>
		</header>
-->
		<div class="tribe-events-calendar-recent-past">

			<?php $this->template( 'recent-past/heading' ); ?>

			<?php foreach ( $events as $event ) : ?>
				<?php $this->setup_postdata( $event ); ?>

				<?php $this->template( 'recent-past/event', [ 'event' => $event ] ); ?>

			<?php endforeach; ?>

		</div>

		<?php //$this->template( 'components/after' ); ?>

	</div>
</div>

<?php //$this->template( 'components/breakpoints' ); ?>