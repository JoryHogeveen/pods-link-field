<?php
/*
Plugin Name: Pods Link Field
Plugin URI: http://pods.io/
Description: Adds a link field type for Pods (pre Pods 2.7)
Version: 1.0
Author: Jory Hogeveen
Author URI: https://www.keraweb.nl
Text Domain: pods-link-field
Domain Path: /languages/

Copyright 2013-2016  Jory Hogeveen  (email : info@keraweb.nl)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'PODS_LINK_FIELD_VERSION', '1.0' );
define( 'PODS_LINK_FIELD_URL', plugin_dir_url( __FILE__ ) );
define( 'PODS_LINK_FIELD_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Initialize plugin
 */
function pods_link_field_init() {
	register_activation_hook( __FILE__, 'pods_link_field_reset' );
	register_deactivation_hook( __FILE__, 'pods_link_field_reset' );

	if ( ! function_exists( 'pods' ) || ! function_exists( 'pods_register_field_type' ) ) {
		return;
	}

	add_filter( 'pods_api_field_types', 'pods_link_field_add_field_type' );

	// Load plugin textdomain
	load_plugin_textdomain( 'pods-link-field', false, PODS_LINK_FIELD_DIR . 'languages/' );
	// Only load as needed
	pods_register_field_type( 'link', PODS_LINK_FIELD_DIR . 'classes/fields/link.php' );

}
add_action( 'plugins_loaded', 'pods_link_field_init', 20 );

/**
 * Reset transients
 */
function pods_link_field_reset() {
	delete_transient( 'pods_field_types' );
}

function pods_link_field_add_field_type( $types ) {
	$types[] = 'link';
	return $types;
}