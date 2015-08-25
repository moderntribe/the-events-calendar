<?php

$template_options = array(
	''        => esc_html__( 'Default Events Template', 'tribe-events-calendar' ),
	'default' => esc_html__( 'Default Page Template', 'tribe-events-calendar' ),
);
$templates        = get_page_templates();
ksort( $templates );
foreach ( array_keys( $templates ) as $template ) {
	$template_options[ $templates[ $template ] ] = $template;
}

/**
 * Filter the array of views that are registered for the tribe bar
 * @param array array() {
 *     Array of views, where each view is itself represented by an associative array consisting of these keys:
 *
 *     @type string $displaying         slug for the view
 *     @type string $anchor             display text (i.e. "List" or "Month")
 *     @type string $event_bar_hook     not used
 *     @type string $url                url to the view
 * }
 * @param boolean
 */
$views = apply_filters( 'tribe-events-bar-views', array(), false );

$views_options = array();
foreach ( $views as $view ) {
	$views_options[ $view['displaying'] ] = $view['anchor'];
}

$sample_date = strtotime( 'January 15 ' . date( 'Y' ) );

$displayTab = array(
	'priority' => 20,
	'fields'   =>
	/**
	 * Filter the fields available on the display settings tab
	 *
	 * @param array $fields a nested associative array of fields & field info passed to Tribe__Events__Field
	 * @see Tribe__Events__Field
	 */
		apply_filters(
		'tribe_display_settings_tab_fields', array(
			'info-start'                         => array(
				'type' => 'html',
				'html' => '<div id="modern-tribe-info">',
			),
			'info-box-title'                     => array(
				'type' => 'html',
				'html' => '<h2>' . esc_html__( 'Display Settings', 'tribe-events-calendar' ) . '</h2>',
			),
			'info-box-description'               => array(
				'type' => 'html',
				'html' => '<p>' . sprintf(
					esc_html__( 'The settings below control the display of your calendar. If things don\'t look right, try switching between the three style sheet options or pick a page template from your theme.%sThere are going to be situations where no out-of-the-box template is 100&#37; perfect. Check out our %sour themer\'s guide%s for instructions on custom modifications.', 'tribe-events-calendar' ),
						'<p></p>',
						'<a href="' . Tribe__Events__Main::$tecUrl . 'knowledgebase/themers-guide/?utm_medium=plugin-tec&utm_source=generaltab&utm_campaign=in-app">',
						'</a>'
					) . '</p>',
			),
			'info-end'                           => array(
				'type' => 'html',
				'html' => '</div>',
			),
			'tribe-form-content-start'           => array(
				'type' => 'html',
				'html' => '<div class="tribe-settings-form-wrap">',
			),
			'tribeEventsBasicSettingsTitle'      => array(
				'type' => 'html',
				'html' => '<h3>' . esc_html__( 'Basic Template Settings', 'tribe-events-calendar' ) . '</h3>',
			),
			'stylesheetOption'                   => array(
				'type'            => 'radio',
				'label'           => esc_html__( 'Default stylesheet used for events templates', 'tribe-events-calendar' ),
				'default'         => 'tribe',
				'options'         => array(
					'skeleton' => esc_html__( 'Skeleton Styles', 'tribe-events-calendar' ) .
								  '<p class=\'description tribe-style-selection\'>' .
								  esc_html__( 'Only includes enough css to achieve complex layouts like calendar and week view.', 'tribe-events-calendar' ) .
								  '</p>',
					'full'     => esc_html__( 'Full Styles', 'tribe-events-calendar' ) .
								  '<p class=\'description tribe-style-selection\'>' .
								  esc_html__( 'More detailed styling, tries to grab styles from your theme.', 'tribe-events-calendar' ) .
								  '</p>',
					'tribe'    => esc_html__( 'Tribe Events Styles', 'tribe-events-calendar' ) .
								  '<p class=\'description tribe-style-selection\'>' .
								  esc_html__( 'A fully designed and styled theme for your events pages.', 'tribe-events-calendar' ) .
								  '</p>',
				),
				'validation_type' => 'options',
			),
			'tribeEventsTemplate'                => array(
				'type'            => 'dropdown_select2',
				'label'           => esc_html__( 'Events template', 'tribe-events-calendar' ),
				'tooltip'         => esc_html__( 'Choose a page template to control the appearance of your calendar and event content.', 'tribe-events-calendar' ),
				'validation_type' => 'options',
				'size'            => 'large',
				'default'         => 'default',
				'options'         => $template_options,
			),
			'tribeEnableViews'                   => array(
				'type'            => 'checkbox_list',
				'label'           => esc_html__( 'Enable event views', 'tribe-events-calendar' ),
				'tooltip'         => esc_html__( 'You must select at least one view.', 'tribe-events-calendar' ),
				'default'         => array_keys( $views_options ),
				'options'         => $views_options,
				'validation_type' => 'options_multi',
			),
			'viewOption'                         => array(
				'type'            => 'dropdown_select2',
				'label'           => esc_html__( 'Default view', 'tribe-events-calendar' ),
				'validation_type' => 'options',
				'size'            => 'large',
				'default'         => 'month',
				'options'         => $views_options,
			),
			'tribeDisableTribeBar'               => array(
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Disable the Event Search Bar', 'tribe-events-calendar' ),
				'tooltip'         => esc_html__( 'Check this to use the classic header.', 'tribe-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			),
			'monthEventAmount'                   => array(
				'type'            => 'text',
				'label'           => esc_html__( 'Month view events per day', 'tribe-events-calendar' ),
				'tooltip'         => sprintf( esc_html__( 'Change the default 3 events per day in month view. Please note there may be performance issues if you set this too high. %sRead more%s.', 'tribe-events-calendar' ), '<a href="http://m.tri.be/rh">', '</a>' ),
				'validation_type' => 'positive_int',
				'size'            => 'small',
				'default'         => '3',
			),
			'enable_month_view_cache' => array(
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Enable the Month View Cache', 'tribe-events-calendar' ),
				'tooltip'         => sprintf( esc_html__( 'Check this to cache your month view HTML in transients, which can help improve calendar speed on sites with many events. %sRead more%s.', 'tribe-events-calendar' ), '<a href="http://m.tri.be/18di">', '</a>' ),
				'default'         => false,
				'validation_type' => 'boolean',
			),
			'tribeEventsDateFormatSettingsTitle' => array(
				'type' => 'html',
				'html' => '<h3>' . esc_html__( 'Date Format Settings', 'tribe-events-calendar' ) . '</h3>',
			),
			'tribeEventsDateFormatExplanation'   => array(
				'type' => 'html',
				'html' => '<p>' . sprintf(  esc_html__( 'The following three fields accept the date format options available to the php date() function. %sLearn how to make your own date format here%s.', 'tribe-events-calendar' ), '<a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">', '</a>' ) . '</p>',
			),
			'dateWithYearFormat'                 => array(
				'type'            => 'text',
				'label'           => esc_html__( 'Date with year', 'tribe-events-calendar' ),
				'tooltip'         => esc_html__( 'Enter the format to use for displaying dates with the year. Used when showing an event from a past or future year, also used for dates in view headers.', 'tribe-events-calendar' ),
				'default'         => get_option( 'date_format' ),
				'size'            => 'medium',
				'validation_type' => 'html',
			),
			'dateWithoutYearFormat'              => array(
				'type'            => 'text',
				'label'           => esc_html__( 'Date without year', 'tribe-events-calendar' ),
				'tooltip'         => esc_html__( 'Enter the format to use for displaying dates without a year. Used when showing an event from the current year.', 'tribe-events-calendar' ),
				'default'         => 'F j',
				'size'            => 'medium',
				'validation_type' => 'html',
			),
			'monthAndYearFormat'                 => array(
				'type'            => 'text',
				'label'           => esc_html__( 'Month and year format', 'tribe-events-calendar' ),
				'tooltip'         => esc_html__( 'Enter the format to use for dates that show a month and year only. Used on month view.', 'tribe-events-calendar' ),
				'default'         => 'F Y',
				'size'            => 'medium',
				'validation_type' => 'html',
			),
			'dateTimeSeparator'                  => array(
				'type'            => 'text',
				'label'           => esc_html__( 'Date time separator', 'tribe-events-calendar' ),
				'tooltip'         => esc_html__( 'Enter the separator that will be placed between the date and time, when both are shown.', 'tribe-events-calendar' ),
				'default'         => ' @ ',
				'size'            => 'small',
				'validation_type' => 'html',
			),
			'timeRangeSeparator'                 => array(
				'type'            => 'text',
				'label'           => esc_html__( 'Time range separator', 'tribe-events-calendar' ),
				'tooltip'         => esc_html__( 'Enter the separator that will be used between the start and end time of an event.', 'tribe-events-calendar' ),
				'default'         => ' - ',
				'size'            => 'small',
				'validation_type' => 'html',
			),
			'datepickerFormat'                   => array(
				'type'            => 'dropdown_select2',
				'label'           => esc_html__( 'Datepicker Date Format', 'tribe-events-calendar' ),
				'tooltip'         => esc_html__( 'Select the date format to use in datepickers', 'tribe-events-calendar' ),
				'default'         => 'Y-m-d',
				'options'         => array(
					'0' => date( 'Y-m-d', $sample_date ),
					'1' => date( 'n/j/Y', $sample_date ),
					'2' => date( 'm/d/Y', $sample_date ),
					'3' => date( 'j/n/Y', $sample_date ),
					'4' => date( 'd/m/Y', $sample_date ),
					'5' => date( 'n-j-Y', $sample_date ),
					'6' => date( 'm-d-Y', $sample_date ),
					'7' => date( 'j-n-Y', $sample_date ),
					'8' => date( 'd-m-Y', $sample_date ),
				),
				'validation_type' => 'options',
			),
			'tribeEventsAdvancedSettingsTitle'   => array(
				'type' => 'html',
				'html' => '<h3>' . esc_html__( 'Advanced Template Settings', 'tribe-events-calendar' ) . '</h3>',
			),
			'tribeEventsBeforeHTML'              => array(
				'type'            => 'wysiwyg',
				'label'           => esc_html__( 'Add HTML before event content', 'tribe-events-calendar' ),
				'tooltip'         => esc_html__( 'If you are familiar with HTML, you can add additional code before the event template. Some themes may require this to help with styling or layout.', 'tribe-events-calendar' ),
				'validation_type' => 'html',
			),
			'tribeEventsAfterHTML'               => array(
				'type'            => 'wysiwyg',
				'label'           => esc_html__( 'Add HTML after event content', 'tribe-events-calendar' ),
				'tooltip'         => esc_html__( 'If you are familiar with HTML, you can add additional code after the event template. Some themes may require this to help with styling or layout.', 'tribe-events-calendar' ),
				'validation_type' => 'html',
			),
			'tribe-form-content-end'             => array(
				'type' => 'html',
				'html' => '</div>',
			),
		)
	),
);
