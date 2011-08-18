<?php
/**
 * Exception handling for third-party plugins dealing with the post edit view.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeEventsPostException' ) ) {
	class TribeEventsPostException extends Exception {
		/**
		* Display the exception message in the div #tec-post-error
		* @param int $post->ID
		*/
		public function displayMessage( $postId ) {
			if( $error = get_post_meta( $postId, TribeEvents::EVENTSERROROPT, true ) ) : ?>
				<script type="text/javascript">jQuery('#tec-post-error').append('<h3>Error</h3><p>' + '<?php echo $error; ?>' + '</p>').show();</script>
			<?php endif;
		}
	} // end TribeEventsPostException
} // end if !class_exists TribeEventsPostException

/**
 * Exception handling for third-party plugins dealing with the Wordpress options view.
 */
if( !class_exists( 'TribeEventsOptionsException' ) ) {
	class TribeEventsOptionsException extends Exception {
		/**
		* Display the exception message in the div #tec-options-error
		*/
		public function displayMessage() {
			$eventsOptions = get_option(TribeEvents::OPTIONNAME, array() );
			if( $eventsOptions['error'] ) : ?>
				<script type="text/javascript">jQuery('#tec-options-error').append('<h3>Error</h3><p>' + '<?php echo $eventsOptions['error']; ?>' + '</p>').show();</script>
			<?php endif;
	    }
	} // end TribeEventsOptionsException
} // end if !class_exists TribeEventsOptionsException
?>