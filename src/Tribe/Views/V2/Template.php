<?php
/**
 * The base template all Views will use to locate, manage and render their HTML code.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

use Tribe__Template as Base_Template;

/**
 * Class Template
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */
class Template extends Base_Template {
	/**
	 * The slug the template should use to build its path.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Renders and returns the View template contents.
	 *
	 * @since 4.9.2
	 *
	 * @param array $context_overrides Any context data you need to expose to this file
	 *
	 * @return string The rendered template contents.
	 */
	public function render( array $context_overrides = [] ) {
		$context = wp_parse_args( $context_overrides, $this->context );
		$context['_context'] = $context;

		return parent::template( $this->slug, $context, false );
	}

	/**
	 * Template constructor.
	 *
	 * @param string $slug The slug the template should use to build its path.
	 *
	 * @since 4.9.2
	 *
	 */
	public function __construct( $slug ) {
		$this->slug = $slug;
		$this->set( 'slug', $slug, false );
		$this->set_template_origin( tribe( 'tec.main' ) )
		     ->set_template_folder( 'src/views/v2' )
		     ->set_template_folder_lookup( true )
		     ->set_template_context_extract( true );
	}

	/**
	 * Returns the template file the View will use to render.
	 *
	 * If a template cannot be found for the view then the base template for the view will be returned.
	 *
	 * @param string|null $name Either a specific name to check or `null` to let the view pick the
	 *                          template according to the template override rules.
	 *
	 * @return string The path to the template file the View will use to render its contents.
	 * @since 4.9.2
	 *
	 */
	public function get_template_file( $name = null ) {
		$name = null !== $name ? $name : $this->slug;

		$template = parent::get_template_file( $name );

		return false !== $template
			? $template
			: $this->get_base_template_file();
	}

	/**
	 * Returns the absolute path to the view base template file.
	 *
	 * @since 4.9.2
	 *
	 * @return string The absolute path to the Views base template.
	 */
	public function get_base_template_file() {
		// Print the lookup folders as relative paths.
		$this->set( 'lookup_folders', array_map( function ( array $folder ) {
			$folder['path'] = str_replace( WP_CONTENT_DIR, '', $folder['path'] );
			return $folder;
		}, $this->get_template_path_list() ) );

		return parent::get_template_file( 'base' );
	}

	/**
	 * Returns the absolute path to the view "not found" template file.
	 *
	 * @since 4.9.2
	 *
	 * @return string The absolute path to the Views "not found" template.
	 */
	public function get_not_found_template() {
		return parent::get_template_file( 'not-found' );
	}

	/**
	 * Sets an array of values in the template.
	 *
	 * This is a sugar method to calling `Tribe\Events\Views\V2\Template::set` in a loop.
	 *
	 * @since TBD
	 *
	 * @param  array    $values    An associative array of keys and values that will be set on the template.
	 * @param  boolean  $is_local  Set the values in the local or Global context.
	 */
	public function set_values( array $values, $is_local = true ) {
		foreach ( $values as $key => $value ) {
			$this->set( $key, $value, $is_local );
		}
	}

	/**
	 * Returns the template local values merged over the global ones.
	 *
	 * @since TBD
	 *
	 * @return array An associative array of the template global values.
	 */
	public function get_values() {
		return array_merge( $this->get_global_values(), $this->get_local_values() );
	}

	/**
	 * Returns the template global values only.
	 *
	 * @since TBD
	 *
	 * @return array An associative array containing only the template global values.
	 */
	public function get_global_values() {
		return $this->global;
	}

	/**
	 * Returns the template local values only.
	 *
	 * @since TBD
	 *
	 * @return array An associative array containing only the template local values.
	 */
	public function get_local_values() {
		return $this->context;
	}
}
