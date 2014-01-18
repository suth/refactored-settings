<?php
/*  Copyright 2014 Sutherland Boswell  (email : hello@sutherlandboswell.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !class_exists( 'Refactored_Settings_0_3_1' ) ) :

class Refactored_Settings_0_3_1 {

	public $plugin_file;
	public $version;
	public $name;
	public $slug;
	public $options_fields;
	public $options;

	function __construct( $args ) {
		$this->plugin_file    = $args['file'];
		$this->version        = $args['version'];
		$this->name           = $args['name'];
		$this->slug           = $args['slug'];
		$this->options_fields = $args['options'];
		// Activation and deactivation hooks
		register_activation_hook( $this->plugin_file, array( &$this, 'plugin_activation' ) );
		register_deactivation_hook( $this->plugin_file, array( &$this, 'plugin_deactivation' ) );
		// Set current options
		add_action( 'plugins_loaded', array( &$this, 'set_options' ) );
		// Add options page to menu
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		// Initialize options
		add_action( 'admin_init', array( &$this, 'initialize_options' ) );
	}

	// Activation hook
	function plugin_activation() {
		add_option( $this->slug, $this->get_default_options() );
	}

	// Deactivation hook
	function plugin_deactivation() {
		delete_option( $this->slug );
	}

	/**
	 * Gets the default options
	 * @return array An array of default options
	 */
	function get_default_options() {
		$default_options = array(
			'version' => $this->version
		);
		foreach ( $this->options_fields as $group => $value ) {
			$option_group = array();
			foreach ( $value['fields'] as $option => $value ) {
				$option_group[$option] = $value['default'];
			}
			$default_options[$group] = $option_group;
		}
		return $default_options;
	}

	/**
	 * Sets $this->options to current options or defaults if none exists. Also checks if options need upgrading.
	 */
	function set_options() {
		// Get the current options from the database
		$options = get_option( $this->slug );
		// If there aren't any options, load the defaults
		if ( ! $options ) $options = $this->get_default_options();
		// Check if our options need upgrading
		$options = $this->upgrade_options( $options );
		// Set the options class variable
		$this->options = $options;
	}

	/**
	 * Takes an options array and upgrades it if needed. Also saves the results if needed.
	 * @param  array $options An array of options
	 * @return array          The upgraded options
	 */
	function upgrade_options( $options ) {

		// Boolean for if options need updating
		$options_need_updating = false;

		// Save options to database if they've been updated
		if ( $options_need_updating ) {
			update_option( $this->slug, $options );
		}

		return $options;

	}

	/**
	 * Adds the options page to the admin menu
	 */
	function admin_menu() {
		add_options_page(
			$this->name . ' Settings',
			$this->name,
			'manage_options',
			$this->slug,
			array( &$this, 'options_page' )
		);
	}

	/**
	 * Initialize the options
	 */
	function initialize_options() {
		foreach ( $this->options_fields as $group => $value ) {
			add_settings_section(  
				$group,
				$value['name'],
				array( &$this, 'settings_section_callback' ),
				$this->slug
			);
			foreach( $value['fields'] as $field => $value ) {
				// Set up args for settings field callback
				$args = $value;
				$args['slug'] = $field;
				$args['group'] = $group;
				// Add the settings field
				add_settings_field(
					$field,
					$value['name'],
					array( &$this, 'settings_field_callback' ),
					$this->slug,
					$group,
					$args
				);
			}
		}
		register_setting( $this->slug, $this->slug, array( &$this, 'sanitize_callback' ) );
	}

	function sanitize_callback( $input ) {
		$output = array(
			'version' => $this->version
		);
		foreach ( $this->options_fields as $group => $value ) {
			$option_group = array();
			foreach ( $value['fields'] as $option => $value ) {
				$option_group[$option] = $input[$group][$option];
			}
			$output[$group] = $option_group;
		}
		return $output;
		return $input;
	}

	function settings_section_callback( $section ) {
		echo $this->options_fields[$section['id']]['description'];
	}

	function settings_field_callback( $args ) {
		$html = '';
		switch ( $args['type'] ) {
			case 'text':
				$html .= '<input type="text" id="' . $this->slug . '-' . $args['slug'] . '" name="' . $this->slug . '[' . $args['group'] . '][' . $args['slug'] . ']" value="' . $this->options[$args['group']][$args['slug']] . '"/>';
				$html .= ' <label for="' . $this->slug . '-' . $args['slug'] . '"> ' . $args['description'] . '</label>';
				break;

			case 'textarea':
				$class = false;
				if ( $args['class'] && is_array( $args['class'] ) ) {
					$class = implode( ' ', $args['class'] );
				} elseif ( $args['class'] ) {
					$class = $args['class'];
				}
				$html .= '<textarea id="' . $this->slug . '-' . $args['slug'] . '"' . ( $class ? 'class="' . $class . '"' : '' ) . ' name="' . $this->slug . '[' . $args['group'] . '][' . $args['slug'] . ']" style="width:420px;height:200px;">' . $this->options[$args['group']][$args['slug']] . '</textarea>';
				if ( $args['description'] ) $html .= '<br>' . $args['description'];
				break;

			case 'checkbox':
				$html .= '<label for="' . $this->slug . '-' . $args['slug'] . '">';
				$html .= '<input type="checkbox" id="' . $this->slug . '-' . $args['slug'] . '" name="' . $this->slug . '[' . $args['group'] . '][' . $args['slug'] . ']" ' . ( $this->options[$args['group']][$args['slug']] ? 'checked="checked"' : '' ) . '/>';
				$html .= ' ' . $args['description'] . '</label>';
				break;

			case 'multicheckbox':
				if ( $args['description'] ) $html .= $args['description'] . '<br>';
				$i = 0;
				foreach ( $args['options'] as $key => $checkbox_option ) {
					$i++;
					$selected = ( $this->options[$args['group']][$args['slug']] ? $this->options[$args['group']][$args['slug']] : array() );
					$html .= '<label for="' . $this->slug . '-' . $args['slug'] . '-' . $key . '">';
					$html .= '<input type="checkbox" id="' . $this->slug . '-' . $args['slug'] . '-' . $key . '" name="' . $this->slug . '[' . $args['group'] . '][' . $args['slug'] . '][]" value="' . $key . '" ' . ( in_array( $key, $selected ) ? 'checked="checked"' : '' ) . '/>';
					$html .= ' ' . $checkbox_option . '</label>';
					if ( $i != count( $args['options'] ) ) $html .= '<br>';
				}
				break;

			case 'radio':
				if ( $args['description'] ) $html .= $args['description'] . '<br>';
				$i = 0;
				foreach ( $args['options'] as $key => $radio ) {
					$i++;
					$html .= '<label for="' . $this->slug . '-' . $args['slug'] . '-' . $key . '">';
					$html .= '<input type="radio" id="' . $this->slug . '-' . $args['slug'] . '-' . $key . '" name="' . $this->slug . '[' . $args['group'] . '][' . $args['slug'] . ']" value="' . $key . '" ' . ( $key == $this->options[$args['group']][$args['slug']] ? 'checked="checked"' : '' ) . '/>';
					$html .= ' ' . $radio . '</label>';
					if ( $i != count( $args['options'] ) ) $html .= '<br>';
				}
				break;
			
			case 'dropdown':
				$html .= '<select id="' . $this->slug . '-' . $args['slug'] . '" name="' . $this->slug . '[' . $args['group'] . '][' . $args['slug'] . ']">';
				foreach ( $args['options'] as $key => $dropdown_option ) {
					$selected_attr = ( $this->options[$args['group']][$args['slug']] == $key ? 'selected="selected" ' : '' );
					$html .= '<option value="' . $key . '" ' . $selected_attr . '>' . $dropdown_option . '</option>';
				}
				$html .= '</select>';
				$html .= ' <label for="' . $this->slug . '-' . $args['slug'] . '"> ' . $args['description'] . '</label>';
				break;

			case 'multi_post_types':
				if ( $args['description'] ) $html .= $args['description'] . '<br>';
				$post_types = get_post_types( array( 'public' => true ), 'objects' );
				unset( $post_types['attachment'] );
				$i = 0;
				foreach ( $post_types as $post_type ) {
					$i++;
					$html .= '<label for="' . $this->slug . '-' . $args['slug'] . '-' . $post_type->name . '">';
					$html .= '<input type="checkbox" id="' . $this->slug . '-' . $args['slug'] . '-' . $post_type->name . '" name="' . $this->slug . '[' . $args['group'] . '][' . $args['slug'] . '][]" value="' . $post_type->name . '" ' . ( in_array( $post_type->name, $this->options[$args['group']][$args['slug']] ) ? 'checked="checked"' : '' ) . '/>';
					$html .= ' ' . $post_type->labels->singular_name . '</label>';
					if ( $i != count( $post_types ) ) $html .= '<br>';
				}
				break;
			
			case 'single_post_type':
				$post_types = get_post_types( array( 'public' => true ), 'objects' );
				unset( $post_types['attachment'] );
				$html .= '<select id="' . $this->slug . '-' . $args['slug'] . '" name="' . $this->slug . '[' . $args['group'] . '][' . $args['slug'] . ']">';
				foreach ( $post_types as $post_type ) {
					$selected_attr = ( $this->options[$args['group']][$args['slug']] == $post_type->name ? 'selected="selected" ' : '' );
					$html .= '<option value="' . $post_type->name . '" ' . $selected_attr . '>' . $post_type->labels->singular_name . '</option>';
				}
				$html .= '</select>';
				$html .= ' <label for="' . $this->slug . '-' . $args['slug'] . '"> ' . $args['description'] . '</label>';
				break;
			
			default:
				# code...
				break;
		}
		echo $html;
	}

	function options_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		?><div class="wrap">

			<div id="icon-options-general" class="icon32"></div><h2><?php echo $this->name ?> Settings</h2>

			<form method="post" action="options.php">  
				<?php settings_fields( $this->slug ); ?>  
				<?php do_settings_sections( $this->slug ); ?>            
				<?php submit_button(); ?>  
			</form>

			<?php

	}

}

endif;