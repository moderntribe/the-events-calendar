<?php
/**
 * Admin View: Widget Dropdown Component
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/widgets/components/dropdown.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @version TBD
 *
 * @var string $label Label for the dropdown.
 * @var string $value Value for the dropdown.
 * @var string $id    ID of the dropdown.
 * @var string $name  Name attribute for the dropdown.
 */

?>
<p
		class="tribe-widget-dropdown tribe-common-form-control-dropdown"
>
	<label
			class="tribe-common-form-control-dropdown__label"
			for="<?php echo esc_attr( $id ); ?>"
	>
		<?php echo esc_html( $label ); ?>
	</label>
	<select
			id="<?php echo esc_attr( $id ); ?>"
			name="<?php echo esc_attr( $name ); ?>"
			class="tribe-common-form-control-dropdown__input widefat"
	>
		<?php foreach ( $options as $option ) { ?>
			<option
					value="<?php echo esc_html( $option['value'] ); ?>"
					<?php selected( $option['value'], $value ); ?>
			>
				<?php echo esc_html( $option['text'] ); ?>
			</option>
		<?php } ?>
	</select>
</p>
