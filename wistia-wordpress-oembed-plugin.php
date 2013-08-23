<?php
/*
Plugin Name: Wistia WordPress Plugin
Plugin URI: https://github.com/wistia/wistia-wordpress-plugin
Description: A plugin that allows you to embed videos from your Wistia account into WordPress.
Version: 0.6
Author: Wistia, Inc.
Author URI: http://wistia.com
License: MIT
*/

wp_oembed_add_provider( '/https?\:\/\/(.+)?(wistia\.com|wi\.st)\/.*/', 'https://fast.wistia.com/oembed', true );
require('wistia-anti-mangler.php');

global $wistia_anti_mangler;
$wistia_anti_mangler = new WistiaAntiMangler();

function wistia_extract_embeds($text) {
  global $wistia_anti_mangler;
  return $wistia_anti_mangler->extract_embeds($text);
}

function wistia_insert_embeds($text) {
  global $wistia_anti_mangler;
  return $wistia_anti_mangler->insert_embeds($text);
}

function wistia_insert_embeds_for_editor($text) {
  global $wistia_anti_mangler;
  return $wistia_anti_mangler->insert_embeds_for_editor($text);
}

function wistia_add_scripts_if_necessary($text) {
  global $wistia_anti_mangler;
  return $wistia_anti_mangler->add_scripts_if_necessary($text);
}

/**
 * Extends the allowed HTML elements in the TinyMCE editor.
 *
 * @link http://bit.ly/11hhyG3
 *
 * @param array $init Initial associative array of allowed elements.
 *
 * @return array
 */
function add_valid_tiny_mce_elements( $init ) {
	$elements = 'div[*],iframe[*],script[*],object[*],embed[*],a[*],noscript[*]';

	/*
	Add to extended_valid_elements if it already exists,
	else ours becomes the only allowed elements.
	*/
	if ( isset( $init['extended_valid_elements'] ) && is_string( $init['extended_valid_elements'] ) )
		$init['extended_valid_elements'] .= ',' . $elements;
	else
		$init['extended_valid_elements'] = $elements;

	return $init;
}

add_filter('content_save_pre', 'wistia_extract_embeds', 2);
add_filter('content_save_pre', 'wistia_insert_embeds', 1001);
add_filter('the_content', 'wistia_extract_embeds', 2);
add_filter('the_content', 'wistia_insert_embeds', 1001);
add_filter('the_content', 'wistia_add_scripts_if_necessary', 1002);
add_filter('the_editor_content', 'wistia_extract_embeds', 2);
add_filter('the_editor_content', 'wistia_insert_embeds_for_editor', 1001);
add_filter('tiny_mce_before_init', 'add_valid_tiny_mce_elements' );

?>
