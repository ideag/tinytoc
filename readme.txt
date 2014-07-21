=== tinyTOC ===
Contributors: ideag
Donate link: http://kava.tribuna.lt/en/
Tags: table of contents, toc, headings
Requires at least: 3.0.0
Tested up to: 3.9.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automaticly builds a Table of Contents once specific number (eg. 3) of headings (h1-h6) is reached and inserts it before or after post/page content.

== Description ==

This plugin is meant to help with navigation in long texts by forming an automatic Wikipedia-like table of contents.

It works by scanning the text for headings (`<h1>-<h6>` HTML tags). If more than a certain amount of headings (3 by default) are found - a table of contents with bookmarks is formed and inserted to the post content. Location (above or below) the text can be chosen in settings page.

TOC is formed as HTML5 `<nav>` element with nested ordered list inside. No specific styling is provided, so that it fits organicaly into the text. If you need specific styling, include it to your theme's CSS.

You can also use a shortcode - [toc] - and a template tags - get_toc()/the_toc().

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Download and unzip `tiny_toc.zip`
1. Upload `tiny_toc` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Check the configuration page at `Settings > tinyTOC`

== Frequently Asked Questions ==

= Can I insert TOC at random place with a shortcode or template tag? =

Yes, since version 0.3 you can use [toc] shortcode and get_toc()/the_toc() template tags.

== Screenshots ==

No screenshots.

== Changelog ==

= 0.3 =
* shortcode and template tags added
* parser rewriten (regex -> DOMDocument)

= 0.2 =
* Widget added (thanks to Darcy W. Christ)
* Small bug fixes

= 0.1 =
* Initial release

== Upgrade Notice =

No upgrade notices.
