<?php
/*
Plugin Name: Wistia WordPress Plugin
Plugin URI: https://github.com/wistia/wistia-wordpress-plugin
Description: A plugin that allows you to embed videos from your Wistia account into WordPress.
Version: 0.7
Author: Wistia, Inc.
Author URI: http://wistia.com
License: MIT
*/

wp_oembed_add_provider( '/https?\:\/\/(.+)?(wistia\.com|wi\.st)\/.*/', 'https://fast.wistia.com/oembed', true );

?>
