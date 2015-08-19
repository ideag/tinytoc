=== tinyTOC ===
Contributors: ideag
Tags: table of contents, toc, headings, widget, shortcode
Donate link: http://arunas.co#coffee
Requires at least: 3.0.0
Tested up to: 4.3.0
Stable tag: 0.8.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automaticly builds a Table of Contents using headings (h1-h6) in post/page/CPT content.

== Description ==

A tiny and simple to help with navigation in long texts by forming an automatic Wikipedia-like table of contents.

It works by scanning the text for headings (`<h1>-<h6>` HTML tags). If more than a certain amount of headings (3 by default) are found - a table of contents with bookmarks is formed and inserted to the post content. Location (above or below) the text can be chosen in settings page.

TOC is formed as HTML5 `<nav>` element with nested ordered list inside. No specific styling is provided, so that it fits organicaly into the text. If you need specific styling, include it to your theme's CSS.

You can also use a shortcode - `[toc]`, template tags - `get_toc()`/`the_toc()` and a widget - `tinyTOC Widget`.

The plugin is translation ready and has Lithuanian translation.

Also try out my other plugins:

* [Gust](http://arunas.co/gust) - a Ghost-like admin panel for WordPress, featuring Markdown based split-view editor.
* [tinyCoffee](http://arunas.co/tinycoffee) - a PayPal donations button with a twist. Ask people to treat you to a coffee/beer/etc. 
* [tinySocial](http://arunas.co/tinysocial) - a plugin to display social sharing links to Facebook/Twitter/etc. via shortcodes
* [tinyRelated](http://arunas.co/tinyrelated) - a plugin to manually assign and display related posts.
* [tinyIP](http://arunas.co/tinyip) - *Premium* - stop WordPress users from sharing login information, force users to be logged in only from one device at a time.

An enormous amount of coffee was consumed while developing these plugins, so if you like what you get, please consider treating me to a [cup](http://arunas.co#coffee). Or two. Or ten.

Cover image credit: [Jeremy Keith](https://www.flickr.com/photos/adactio/1523797880/)

== Installation ==

1. Install via `WP Admin > Plugins > Add New` or download a .zip file and upload via FTP to `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. (optional) Modify options via `WP Admin > Settings > tinyTOC`, if needed. Plugin automatically embeds TOC top of post content, but you can use a widget, a shortcode (`[toc]`), a couple of template tags (`the_toc()/get_toc()`) for other integration options.

== Frequently Asked Questions ==

= I don't see tinyTOC Widget in the available widget list. =

You need to activate the widget via plugin Settings page (`WP Admin > Settings > tinyTOC`)

== Screenshots ==

1. A TOC widget (left) and a TOC above the page content (center) in action in TwentyFourteen theme.
2. Settings screen.

== Changelog ==

= 0.8.0 = 

* a `tinytoc_widget_content` filter (by Maciej Gryniuk / odie2 )
* better non-latin character support (by Maciej Gryniuk / odie2 )
* prevent duplicate slugs (by Maciej Gryniuk / odie2 )
* better `PHP < 5.3.6` support (suggested by Maciej Gryniuk / odie2 )
* a link to plugin settings from Plugins list (suggested by Maciej Gryniuk / odie2 )
* updated widget code to remove PHP4 style constructors

= 0.7 =

* an option to generate slug-based links

= 0.6 =

* a big code rewrite (options page, main class, etc.)
* renamed main plugin file to fit WordPress tradition better. Plugin will be deactivated after upgrade and you'll need to reactivate it.

= 0.5 =

* fixed some typos

= 0.4 = 

* fixed UTF-8 issue

= 0.3 =

* shortcode and template tags added
* parser rewriten (regex -> DOMDocument)

= 0.2 =

* Widget added (thanks to Darcy W. Christ)
* Small bug fixes

= 0.1 =

* Initial release

== Upgrade Notice ==

= 0.6 = 

Renamed main plugin file to fit WordPress tradition better. Plugin will be deactivated after upgrade and you'll need to reactivate it.