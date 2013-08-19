=== Wistia WordPress Plugin ===
Contributors: wistia
Tags: wistia, oembed, video, embed
Requires at least: 2.9.1
Tested up to: 3.5.1
Stable tag: 0.6

Enables all Wistia embed types to be used in your WordPress blog.

== Description ==

Wistia's embed codes are designed to be very durable, but WordPress has a
history of being particularly troublesome. This plugin transparently makes
sure that your Wistia embeds will work, no matter what you do.

As of version 0.6 of this plugin, it is recommended that you check
"Use oEmbed?" under Advanced Options when generating your embed code.

See the Wistia documentation for more:
http://wistia.com/doc/wordpress#using_the_oembed_embed_code

== Installation ==

1. Make a 'wistia-wordpress-oembed-plugin' directory in '/wp-content/plugins/'.
2. Upload 'wistia-wordpress-oembed-plugin.php' to the
'/wp-content/plugins/wistia-wordpress-oembed-plugin/' directory.
3. Upload 'wistia-anti-mangler.php' to the
'/wp-content/plugins/wistia-wordpress-oembed-plugin/' directory.
4. Activate the plugin through the 'Plugins' menu in WordPress.

== Changelog ==

= 0.6 =
* Changed oembed regexp to properly detect new Wistia oembed URLs.

= 0.5.1 =
* Fixed a debug error complaining about `extended_valid_elements`
undefined in `add_valid_tiny_mce_elements`

= 0.5 =
* Updated the oembed endpoint for Wistia
* Updated the regexes for matching Wistia video URLs to the latest recommended in the doc

= 0.4 =
* Added support for the new Playlist embed structure

= 0.3 =
* Added support for all SuperEmbed style Wistia embeds

= 0.2 =
* Added support for SuperEmbed style oEmbeds

= 0.1 =
* Initial release
