<?php
$field              = (object) array();
$field->label       = __( 'Import Type', 'the-events-calendar' );
$field->placeholder = __( 'Select Import Type', 'the-events-calendar' );
$field->help        = __( 'One-time imports include all currently listed events, while scheduled imports automatically grab new events and updates from the feed on a set schedule. Single events can be added via a one-time import.', 'the-events-calendar' );
$field->source      = 'ical_import_type';

$frequency              = (object) array();
$frequency->placeholder = __( 'Select Frequency', 'the-events-calendar' );
$frequency->help        = __( 'Select how often you would like events to be automatically imported.', 'the-events-calendar' );
$frequency->source      = 'ical_import_frequency';

$cron = Tribe__Events__Aggregator__Cron::instance();
$frequencies = $cron->get_frequency();
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="ical">
	<th scope="row">
		<label for="tribe-ea-field-import_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<select
			name="aggregator[ical][import_type]"
			id="tribe-ea-field-ical_import_type"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
			data-hide-search
		>
			<option value=""></option>
			<option value="manual">One-Time Import</option>
			<option value="schedule">Scheduled Import</option>
		</select>
		<select
			name="aggregator[ical][import_frequency]"
			id="tribe-ea-field-ical_import_frequency"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-dependent"
			placeholder="<?php echo esc_attr( $frequency->placeholder ); ?>"
			data-hide-search
			data-depends="#tribe-ea-field-ical_import_type"
			data-condition="schedule"
		>
			<option value=""></option>
			<?php foreach ( $frequencies as $frequency_object ) : ?>
				<option value="<?php echo esc_attr( $frequency_object->id ); ?>" <?php selected( 'daily', $frequency_object->id ); ?>><?php echo esc_html( $frequency_object->text ); ?></option>
			<?php endforeach; ?>
		</select>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $field->help ); ?>"
			data-depends="#tribe-ea-field-ical_import_type"
			data-condition-not="schedule"
			data-condition-empty
		></span>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $frequency->help ); ?>"
			data-depends="#tribe-ea-field-ical_import_type"
			data-condition="schedule"
		></span>
	</td>
</tr>
<?php
$field              = (object) array();
$field->label       = __( 'URL', 'the-events-calendar' );
$field->placeholder = __( 'example.com/url.ics', 'the-events-calendar' );
$field->help        = __( 'Enter the url for the iCalendar feed you wish to import, e.g. https://central.wordcamp.org/calendar.ics', 'the-events-calendar' );
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-ical_import_type" data-condition-not-empty>
	<th scope="row">
		<label for="tribe-ea-field-file"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<input
			name="aggregator[ical][source]"
			type="text"
			id="tribe-ea-field-ical_source"
			class="tribe-ea-field tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
		>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>"></span>
	</td>
</tr>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-ical_import_type" data-condition-not-empty>
	<td colspan="2" class="tribe-button-row">
		<button type="submit" class="button button-primary tribe-preview">
			<?php
			esc_html_e( 'Preview', 'the-events-calendar' );
			?>
		</button>
	</td>
</tr>
