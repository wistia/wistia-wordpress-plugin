<?php
/*
Plugin Name: Wistia WordPress oEmbed Plugin
Plugin URI: https://github.com/wistia/wistia-wordpress-oembed-plugin
Description: A plugin that allows you to embed videos from your Wistia account into WordPress.
Version: 0.2
Author: Wistia, Inc.
Author URI: http://www.wistia.com/
License: MIT
*/
wp_oembed_add_provider( 'http://*.wistia.com/embed/*', 'http://app.wistia.com/embed/oembed' );
if ($_SERVER['https'] == 'on') {
  wp_enqueue_script( 'wistia-iframe-api', 'https://fast.wistia.com/static/iframe-api-v1.js', null, null, true );
} else {
  wp_enqueue_script( 'wistia-iframe-api', 'http://fast.wistia.com/static/iframe-api-v1.js', null, null, true );
}
?>
