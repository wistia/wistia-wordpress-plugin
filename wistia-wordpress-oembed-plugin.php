<?php
/*
Plugin Name: Wistia WordPress Plugin
Plugin URI: https://github.com/wistia/wistia-wordpress-plugin
Description: A plugin that allows you to embed videos from your Wistia account into WordPress.
Version: 0.5
Author: Wistia, Inc.
Author URI: http://wistia.com
License: MIT
*/

wp_oembed_add_provider( 'https?:\/\/(.+)?(wistia\.com|wi\.st)\/.*', 'http://fast.wistia.com/oembed' );
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

function add_valid_tiny_mce_elements($in) {
  $in['extended_valid_elements'] = $in['extended_valid_elements'] . 'div[*],iframe[*],script[*],object[*],embed[*],a[*],noscript[*]';
  return $in;
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
