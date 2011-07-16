=== WP Social Blogroll ===
Contributors: erunafailaro
Donate link: http://www.icrc.org/Web/eng/siteeng0.nsf/htmlall/helpicrc
Tags: blogroll, sort, social, freshness, bookmarks, links, sidebar, widget, update, icon, feed, rss2, atom, ajax, Google API, template tag, bloglist, JavaScript, wpmu
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 1.5.8

WP Social Blogroll adds a social blogroll to your blog. It will follow and display all updates of the sites in your blogroll.

== Description ==

WP Social Blogroll adds a social blogroll to your blog. It will follow and display all updates of the sites in your blogroll. See the [plugin page](http://www.weinschenker.name/plugin-feed-reading-blogroll/ "visit the development blog") or the [author's page](http://www.weinschenker.name/ "visit the development blog") for more details.

See this [screenshot](http://wordpress.org/extend/plugins/feed-reading-blogroll/screenshots/ "example of the WP Social Blogroll") for example.

The plugin retrieves the date of the most recent feed-item of the bookmarked site. The feeds must be indexed by Google as the plugin uses [Google's free AJAX Feed API](http://code.google.com/apis/ajaxfeeds/ "Google AJAX Feed API").

You need a free [Google API Key](http://code.google.com/apis/ajaxfeeds/signup.html "Google API Key") to get it to work. Based on Google's information, the age of the most recent feed-item is calculated and then displayed under each bookmark in your sidebar.

**Your blogroll can be sorted by the freshness of the bookmarks.**

Furthermore, the title and author of the freshest post and an icon for each bookmark can be displayed.

Get more info at the [development blog](http://www.weinschenker.name/kategorien/feedreadingblogroll/ "visit the development blog") ([feed](http://www.weinschenker.name/kategorien/feedreadingblogroll/feed "subscribe to the feed")).

== Installation ==

Download the archive and extract all files.

Upload the archive and the files to the plugin-directory of your blog. This should be

* `/wp-content/plugins/feed-reading-blogroll/*`

After that, all files should be located at that directory.

Now, go to the plugins-section of your dashboard, look for the entry called **Feed Reading Blogroll** and press **activate**.

Furthermore, the plugin will create a JavaScript file in your WordPress content-directory (normally, this is `/wp-content/`). Therefore, this directory must be writable (`chmod 777`) at the moment, the Javascript-file is created for the first time. After that, the content-directory does not need to be writable as far as this plugin is concerned. I would recommend the following procedure:

* Set `/wp-content/` to `chmod 777`, e.g. by using your ftp-client.
* Go to the plugin's option-page. This will let the plugin create the JavaScript-file.
* Go back to your ftp-client and go to `/wp-content/`. You should find a file called `feedreading_blogroll.js` in it. Perfect! The plugin will now be able to overwrite this file, even if `/wp-content/` is no longer writable, so ...
* ... set the directory `/wp-content/` back to chmod 755, while letting the rights of `feedreading_blogroll.js` remain chmod 777.

Finally, save your own [Google API Key](http://code.google.com/apis/ajaxfeeds/signup.html "Google API Key") at the plugin's option-panel and add the blogroll-widget to your blog's sidebar.

[Next steps](http://wiki.weinschenker.name/feedreadingblogroll:start "Get the plugin running")

== Upgrading ==

You are encouraged to use the plugin-upgrade-function that WordPress is offering.

However. if you would you are preferring a manual upgrade, follow these steps:

1. Download the most recent zip-file of this plugin and extract the contents.
1. On your server, where your WordPress-installation is located, delete the contents of this directory: `/wp-content/plugins/feed-reading-blogroll/*`
1. Upload all the new plugin-files to this directory (`/wp-content/plugins/feed-reading-blogroll/*`).
1. Login to your admin-dashboard and go to the plugin's options-page.
1. Click on the JavaScript-tab and push the rebuild-button to generate a new JavaScript-file on the server.

That's it :-)

== Frequently Asked Questions ==

= Does this plugin work with WordPress MU? =

This plugin works with with version 3.0 of WordPress in single- and multisite-installations. So, yes - it works with WPMU version 3.0 and newer.

= Do I have to save an extra feed-URL along with each of my bookmarks? =

No, since version 1.1 of the plugin, this is no longer necessary. The plugin is able to discover the feed-URLs automatically. However, it's still possible and the plugin would use a separatly saved feed-URL.

= Can the bookmarks be ordered by "last updated" ? =

Yes, by using JavaScript. The visitor's browser will perform the sorting. Just enable the plugin's JavaScript-sorting option.

= Does the plugin access the remote blogs and their feeds directly? =

No, the plugin adds JavaScript to your blog that accesses Google and the copy of the feed Google has stored on its servers. Google will be accessed from your visitor's browser.

= Will the pageload of my blog be affected in a negative way? =

Yes, depending on how many bookmarks you have in your blogroll, you will notice a longer pageload. The plugin will generate a javaScript, which executes in your visitor's browser. It will load the update-infomation from Google. This does not affect the traffic between your blog and the visitor.

= Are the links visible to search engine crawlers? =

Yes, they are.

= Does the plugin support link-categories? =

Yes, if you created link-categories, you will be able to group your bookmarks by these categories. On the plugin's option page, you will even be able to order the link-categories customly with drag-and-drop.

= The plugin relies on JavaScript, has it been tested to work with the common web browsers? =

It has been tested. It is known to work with the following web browsers:

* Firefox 3.0 and newer
* Apple Safari 3.2 and newer
* Microsoft Internet Explorer 7 and newer
* Microsoft Internet Explorer 8 beta
* Opera 9.51
* Opera Mobile 9.51
* Opera Mini 4.2
* All versions of Google Chrome
* SRWare Iron 1.0 and newer

Microsoft Internet Explorer version 6 and older are unable to display the Feed Reading Blogroll properly. 

= What can I do to boost the performance? =

You can do a couple of things. 

For starters, I recommend to save the feed-URLs within your WordPress linkmanager. How to do this is described in [my manual](http://wiki.weinschenker.name/feedreadingblogroll:addingfeedurls "Adding feeds to your bookmarks").

Furthermore, I recommend to additionally install the following plugins:

* [Autoptimize](http://wordpress.org/extend/plugins/autoptimize/ "Autoptimize plugin") - It concatenates all scripts and styles, minifies and compresses them, adds expires headers, caches them, and moves styles to the page head, and scripts to the footer. It also minifies the HTML code itself, making your page really lightweight.
* [W3 Total Cache](http://wordpress.org/extend/plugins/w3-total-cache/ "W3 Total Cache") - This plugin combines static page-caching and a database cache. Can be integrated with opcode-caches like eAccelerator, XCache or APC.

Additionally, it may be a good idea to divide your bookmarks into a couple of categories. Afterwards you could add Social Blogrolls for only some of these categories and normal non-social blogrolls for the rest of them.

This idea can pursued even further with help from the plugin [Widget Logic](http://wordpress.org/extend/plugins/widget-logic/ "Widget Logic"). Widget Logic allows you to place sidebar widgets only on certain parts of your blog. For example, you can add a social blogroll only on your homepage and prevent it from being shown on articles and pages. 


== Screenshots ==

1. This is an example of what the Blogroll might look like, when you have installed this plugin. Notice that the entries are sorted by the latest update of the bookmark.

2. This is a screenshot of the settings-page. It shows the Options-tab. Notice that you can change the order of the categories by draggging and dropping them with your mouse.

3. This is a screenshot of the remaining settings you can perform.

4. Use snapshot-services like snap.com with WP Social Blogroll.



== Changelog ==
= 1.5.8 =
* Fixed a bug that made it impossible to sort the link-categories of the multi-category-widget.
* Updated Turkish translation.

= 1.5.7 =
* Fixed the JavaScript for the Rolling style following changes in the WordPress core that came into effect with WordPress 3.1.

= 1.5.6 =
* Added Dutch language translation.
* Automatic update of javascript-file during plugin-updates does no longer rely on a WordPress hook.

= 1.5.5 =
* Fixed a compatibility-issue with PHP 5.3.
* Added Ukrainian language translation.
* Updated Italian language translation.

= 1.5.4 =
* Fixed a bug with the linkpage. Linkpage-users please activate the new option called **Enable Linkpage**.

= 1.5.3 =
* Fixed a bug with the classic style.

= 1.5.2 =
* Compatibility with WP 3.0 multisite-installations.
* Plugin requires at least WordPress 2.8
* Fixed a couple of bugs with the sorting by latest update.
* Fixed a bug where the blogroll-title was displayed twice in certain cases.
* Changed JavaScript to avoid unnecessary HTTP-requests on parts of your blog where no blogroll is displayed.
* Replaced jQuery-TSort by a self-written sorting-function. 
* Dynamic loading of Google-API. This prevents an HTTP-Request on parts of your blog where no blogroll is displayed.
* Fixed typos on the admin-panel.

= 1.5.1 =
* Fixed a bug that occured with the rolling style in combination with single-category-widgets. Thanks to [jbmoxie](http://www.buildingmoxie.com) for reporting this bug and for helping me to find a solution.
* Fixed handling of empty link-categories. Thanks go to David Szego who send me the necessary code.

= 1.5 =
* Plugin is called WP Social Blogroll from now on.
* Added Google Favicon Discovery.
* Changed links to support-forum and wiki.
* Added icon for [Ozh Admin Drop Down Menu](http://wordpress.org/extend/plugins/ozh-admin-drop-down-menu/ "Ozh Admin Drop Down Menu").

= 1.4.1 =
* Language-support for Belorussian (thanks to [Ilyuha](http://antsar.info/ "Ilyuha"))
* Tooltip for bookmarks in sidebar is now configurable to show either the blog-description, the latest post-tile or nothing at all.
* Added sub-directories for javascript, css and languages-files to plugin-directory.
* Enhanced JavaScript-performance by replacing jQuery.each()-calls with native JavaScript for-loops.

= 1.4 =
* Added blogroll style "Rolling". An animated style that will only occupy a small part of your sidebar.
* Removed the "lone colon" that appeared on Classic-Style-Blogrolls with bookmarks that had no date-information.
* Language-support for French (thanks to [Steve](http://www.fruitmur.ca/ "Steve"))
* Language-support for Swedish (thanks to [Markus](http://markus.wallgren.be/ "Markus"))
* Moved changelog to readme-file.
* [more info](http://www.weinschenker.name/2009-07-24/version-1-4-is-out-lets-roll/ "more info")

= 1.3 =
* Compatibility with WordPress 2.7.1 and 2.8
* Added multi-widget-support (WordPress-2.8-blogs only)
* Plugin's JavaScript can be loaded either from the pageheader or pagefooter (WordPress-2.8-blogs only)
* Further improved JavaScript-performance
* Improved layout of the admin-interface
* Language-support for Portuguese (thanks to [Jorge Silva](http://jncs.contabite.com/ "Jorge Silva"))
* Language-support for Russian (thanks to [Fat Cow](http://www.fatcow.com/ "Fat Cow"))

= 1.2.1 =
* Optimized the JavaScript. The number of attempts needed to sort the blogroll has been reduced to one or to one per blogroll-category, if categories are used. This increases the performance of the plugin significantly.
* Fixed a bug that caused conflicts between WordPress' jQuery-library and other JavaScript-libraries or other versions of jQuery.

= 1.2 =
* The name of the author of the remote blog can be displayed, if the remote blog provides this information.
* The number of shown bookmarks can now be limited, i.e. you may display only the 10 most freshiest blogs).
* This plugin is now compatible with [Parallel Load plugin]
* Enhanced JavaScript-performance

= 1.1.3 =
* Fixed a bug that was creating invalid javascript on systems where no separate feed URL was saved.
* turkish translations

= 1.1.2 =
* fixed the line 892 error. It was an incompatibilty with PHP4

= 1.1 =
* Added Google Automatic Feed Discovery. Automatic Feed Discovery is a service by Google which will try to find the correct feeds that belong to your bookmarks. When using Feed Discovery, you do not need to save extra feed-URLs along with your bookmarks. If you have saved a feed-URL for a bookmark, the plugin will always use the saved feed for that particular bookmark instead of Google Feed Discovery.
* Added custom sorting of link-categories.
* polished the admin-interface
* When link-categorization is enabled, the category-titles are always displayed above their bookmarks.

= 1.0.5 =
* fixed an incompatibility-issue with other WordPress-plugins that are also using JSMin (like PHP Speedy WP)
* polished the admin-interface
* toggling of Blog-preview is now using the toggle()-function from jQuery

= 1.0.4 =
* fixed a JavaScript error, that prevented special-characters (Japanese, funny German characters etc) from being correctly displayed on some systems
* migrated all JavaScript to jQuery
* dynamic JavaScript is now minified on PHP5-Systems with JSMin
* dynamic JavaScript can now be manually created from the admin-section (see new tab called 'JavaScript')
* attached the building of dynamic JavaScript to plugin-activation-hook.

= 1.0.1 =
* Fixed JavaScript errors, enabling sorting with Microsoft Internet Explorer 8
* Fixed a bug that prevented sorting when categorization was disabled
* Added Spanish language files

= 1.0 =
* Sorting by "last update" has been enhanced and works automatically now. Sort-Buttons have been removed.
* The admin-page has been revamped

= 0.9.5.2 =
* Sorting by freshness is now possible by using JavaScript
* Javascript-files are now created and cached in the content-directory instead of putting a lot of Javascript into the html-header of your blog.
* Various Bugfixes

= 0.9.1.2 =
* new file feedreading_blogroll.css to allow customizing of the blogroll

= 0.9.0.2 =
* Fixed an annoying bug that made it impossible to save options

= 0.9.0.1 =
* Various Bugfixes
* Plugin will now use the jQuery JavaScript-library that is coming with WordPress
* Some internal changes to let the plugin use the new plugin-API from WordPress 2.7

= 0.9.0.0 =
* Various Bugfixes
* New blogroll-style to display it as a list of banners

= 0.8.8.0 =
* Various Bugfixes
* Usage of WordPress nonces for the amin-interface
* Introduction of Blogroll Styles
* Blogroll now takes account of the target-settings you made for your links (i.e. target="_blank")

= 0.8.6.1 =
* Fixed a bug with the plugin's template-tag.

= 0.8.0.6 =
* Additionally to the age of the latest post, the post title from your bookmark can be displayed.
* You will now be able to select link-categories by clicking some checkboxes. Furthermore, you can group the bookmarks by their associated link-categories.
* Bookmarks can be sorted by various cirterias.
* A textual blog-preview can be displayed, when the visitor clicks on the update-information below the bookmark.
* The CSS-structure of the bookmark-items has changed. 

= 0.8.0.5 =
* The plugin's option-page now contains a section called URL-check. There you will see, if vital information like feed-URLs or icon-URLs are missing. If they are missing, update-information for your bookmarks cannot be displayed by the plugin.
* The plugin now supports XFN-link-relations. If you saved this information with your bookmarks, the plugin will generate the according HTML-attributes

= 0.7.0.5 =
* Now, the plugin uses the Google AJAX Feed API to fetch the feeds of your bookmarks.
* This will increase the performance of your blog. All you need to use the Google API is a Google API key. The docu-page describes how to acquire a key and how to configure the plugin. 

= 0.7.0.0 =
* Initial release

== Upgrade Notice ==
= 1.5.8 =
* Fixed a bug that made it impossible to sort the link-categories of the multi-category-widget.
* Updated Turkish translation.

= 1.5.7 =
* Fixed a compatibility issue with WordPress 3.1 that stopped the Rolling style from working.

= 1.5.6 =
* Added Dutch language translation.
* Automatic update of javascript-file during plugin-updates does no longer rely on a WordPress hook.

= 1.5.5 =
Fixed a compatibility issue with PHP 5.3.

= 1.5.4 =
Fixed a bug with the linkpage. Linkpage-users please upgrade and activate the new option called **Enable Linkpage**.

= 1.5.3 =
The last version contained a bug with the callis-style. 1.5.3 fixes it.

= 1.5.2 =
This update aims to increase client-side performance by avoiding unnecessary HTTP-requests. Also, I fixed a couple of bugs with the sorting by latest update. Furthermore, this update adds support for WP 3.0 multisite-installations.

= 1.5.1 =
This small update fixes two bugs. One was related to the rolling style and prevented it from starting to roll in some special situations. The second fix solves an issue that occured when there were link-categories that did not contain links.


== Language Support / Translations ==

This plugin supports different languages. Check the [language section](http://www.weinschenker.name/plugin-feed-reading-blogroll/#language "language section") on my homepage for details. If you want to contribute with your own translation, feel free to contact me. I'd be delighted to add it to the plugin.

With the current version these languages are available:

* Belorussian
* Danish
* English
* French
* Italian
* Portuguese
* Russian
* Spanish
* Swedish
* Turkish
* German

== Plugin Homepage ==

The complete documentation is available at my [website](http://www.weinschenker.name/plugin-feed-reading-blogroll/ "website").

== Support Forum ==

You will get help at the official WordPress [Forum](http://wordpress.org/tags/feed-reading-blogroll?forum_id=10#postform "Support forum").

== Wiki ==

You can find lots of information at my [Wiki](http://wiki.weinschenker.name/feedreadingblogroll:start "Wiki")

