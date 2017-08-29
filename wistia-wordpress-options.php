<?php
/**
 * "Settings" are serialized associative arrays saved to the wp_options table.
 * This is a convenience method to pull out values by key from that array.
 *
 * @param $key
 *
 * @return string
 */
function get_wistia_wordpress_option( $key ) {
	$options = get_option( 'wistia_wordpress_settings' );
	if ( $options ) {
		return $options[ $key ];
	}

	return '';
}

/**
 * "Settings" are serialized associative arrays saved to the wp_options table.
 * This is a convenience method to set values by key in that array.
 *
 * @param $key
 * @param $val
 */
function set_wistia_wordpress_option( $key, $val ) {
	$options = get_option( 'wistia_wordpress_settings' );
	$options[ $key ] = $val;
	update_option( 'wistia_wordpress_settings', $options );
}
