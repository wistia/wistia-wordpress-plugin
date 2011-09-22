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
wp_oembed_add_provider( 'http://app.wistia.com/embed/medias/*', 'http://app.wistia.com/embed/oembed' );

function wistia_shortcode( $atts ) {

  // We need to use the WP_Embed class instance
  global $wp_embed;

  // The "url" parameter is required
  if ( empty($atts['url']) ) {
    return '';
  }

  // Construct the YouTube URL
  $url = '' . $atts['url'];

  // Run the URL through the  handler.
  // This handler handles calling the oEmbed class
  // and more importantly will also do the caching!
  return $wp_embed->shortcode( $atts, $url );
}

add_shortcode( 'wistia', 'wistia_shortcode' );
?>
