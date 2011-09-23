=== Wistia WordPress oEmbed Plugin ===
Contributors: wistia
Tags: wistia, oembed, video, embed
Requires at least: 2.9.0
Tested up to: 3.0.3
Stable tag: trunk

Enable oEmbed-based embedding for Wistia videos in your WordPress blog.

== Description ==

This plugin enables oEmbed-based embedding for Wistia videos in your WordPress blog.  oEmbed support is not turned on by default in Wistia accounts.  Please contact us at support@wistia.com to have it turned on for your account.  See http://wistia.com/doc/oembed for more information.

With this plugin, you can put oEmbed links in your posts which will be replaced with the actual Wistia video when your page renders. The easiest way to specify the link is like this:

    [embed]http://app.wistia.com/embed/medias/3a3b7090ca?width=960&height=360&autoplay=false&playbutton=true&controls_visible=false&end_video_behavior=default[/embed]

You can also put just the link (without any [embed] tags) on its own line (no extra spaces) and blank lines above and below.

== Installation ==

1. Upload 'wistia-wordpress-oembed-plugin.php' to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= I've installed the plugin and pasted a link from Wistia, but it's not getting embedded as a video.  What gives? =

Chances are that you need to have oEmbed support enabled in your Wistia account.  Please see http://wistia.com/doc/oembed for everything you need to know about using oEmbed links from Wistia.

== Changelog ==

= 0.1 =
* Initial release
