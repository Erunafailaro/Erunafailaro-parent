/**
 * Plugin Name: Feed reading Blogroll Plugin URI:
 * http://www.weinschenker.name/plugin-feed-reading-blogroll/ Description: This
 * plugin lets you embed an enhanced blogroll that reads the feeds of your
 * bookmarked sites. 
 * 
 * Version: 1.5.8
 * Author: Jan Weinschenker 
 * Author URI: http://www.weinschenker.name
 * 
 * 
 * Plugin: Copyright 2008 Jan Weinschenker (email: kontakt@weinschenker.name)
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 * 
 * ==============================================================================
 * Feed reading Blogroll uses the great JSMin
 * 
 * JSMin 1.1.1 jsmin.php - PHP implementation of Douglas Crockford's JSMin. Ryan
 * Grove <ryan@wonko.com> copyright 2002 Douglas Crockford
 * <douglas@crockford.com> (jsmin.c) copyright 2008 Ryan Grove <ryan@wonko.com>
 * (PHP port) license: http://opensource.org/licenses/mit-license.php MIT
 * License http://code.google.com/p/jsmin-php/
 * ==============================================================================
 * ==============================================================================
 * Feed reading Blogroll uses the great jQuery TinySort jQuery TinySort - A
 * plugin to sort child nodes by (sub) contents or attributes.
 * 
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * ==============================================================================
 */

/*
 * packed with http://jscompress.com/
 */

/**
 * Initialize this JavaScript
 */
jQuery(document).ready( function() {
	google.load("feeds", "1", {
		"callback" : feedreading_initialize
	});
	// feedreading_initialize();
	});

/**
 * Submits an AJAX-request to WordPress that rebuilds the JavaScript-file.
 * Requires a valid nonce, otherwise the WordPress-core will not accept this
 * request.
 * 
 * @param url
 *            of the server-side ajax-endpoint
 * @param nonce
 * 
 * @return true
 */
function feedreadingBlogrollRebuildJS(requestUrl, nonce) {
	jQuery.ajax( {
		type : "POST",
		url : requestUrl,
		timeout : 3000,
		data : {
			action : 'feedreading_blogroll_generate_javascript_lookup',
			_ajax_nonce : nonce
		},
		success : function(msg) {
			jQuery('#feedreading_ajax_response').fadeOut(300);
			jQuery('#feedreading_ajax_response').html(msg);
			jQuery('#feedreading_ajax_response').fadeIn(300);
		},
		error : function(msg) {
			jQuery('#feedreading_ajax_response').html(
					'Error: ' + msg.responseText);
		}
	});
	return true;
}

/**
 * initializes and starts the Google FeddControl to update the feeds in the
 * admin-area of the plugin.
 *
 * @return void
 */
function feedreading_initialize() {
	var feedControl = new google.feeds.FeedControl(), $changelogBody = jQuery("#feedreading_blogroll_changelog_body");
	fetchBlogEntry();
	jQuery.validator.addMethod("naturalnumber", function(value, element) {
		return this.optional(element) || /^\d+$/.test(value);
	}, "Not a natural number!");

	jQuery("#feedreading_options_form").validate( {
		rules : {
			maxBookmarks : {
				required : false,
				naturalnumber : true,
				maxlength : 3
			}
		}
	});

	jQuery("#feedreading_blogroll_option_tabs").tabs( {
		fx : {
			opacity : 'toggle'
		}
	});
	jQuery("ul#categoryOrderList").sortable( {
		cursor : 'pointer',
		update : linkcategoriesSorted
	});
	jQuery("#categoryOrderArray").val(
			jQuery("ul#categoryOrderList").sortable('toArray'));
	jQuery("#categoryOrderList .categoryCheckbox").bind("click", function() {
		jQuery(this).parent().toggleClass("inblogroll");
	});
	feedControl.addFeed(
			"http://www.weinschenker.name/forum/rss/topic/changelog",
			"Release Notes");
	feedControl
			.addFeed(
					"http://www.weinschenker.name/forum/rss/forum/feed-reading-blogroll-developer-notes/topics",
					"Developer Notes");
	feedControl
			.addFeed(
					"http://www.weinschenker.name/kategorien/feedreadingblogroll/feed/",
					"Developer Blog");
	feedControl.setNumEntries(2);
	feedControl.setLinkTarget(google.feeds.LINK_TARGET_BLANK);
	feedControl.draw($changelogBody.get(0), {
		drawMode : google.feeds.FeedControl.DRAW_MODE_TABBED
	});

	jQuery(".popup_info .infotext").css("display", "none");
	;
	jQuery(".popup_info .infotext_trigger").bind("change click keypress",
			togglePopupInfo);
	jQuery("#showLastUpdated").bind("change click keypress", toggleShowAuthor);
	toggleShowAuthor();
}

/**
 * 
 * @return
 */
function toggleShowAuthor() {
	if (jQuery("#showLastUpdated").is(":checked")) {
		jQuery("#showAuthor").removeAttr("disabled");
	} else {
		jQuery("#showAuthor").attr("disabled", true);
		jQuery("#showAuthor").removeAttr("checked");
	}
}
function togglePopupInfo() {
	jQuery(this).parent().children(".infotext").toggle("fast");
}

/**
 * Save category-order to input-filed #categoryOrderString
 * @param event
 * @param ui
 * @return
 */
function linkcategoriesSorted(event, ui) {
	var result = jQuery("ul#categoryOrderList").sortable('toArray');
	jQuery("#categoryOrderArray").val(result);
}

function fetchBlogEntry() {
	var $feed = new google.feeds.Feed(
			"http://www.weinschenker.name/kategorien/feedreadingblogroll/feed"), $a = jQuery("#devblogentryanchor"), $entry = null, $text = "", $entryTitle = "", $date = new Date();
	$feed.load( function($result) {
		if (!$result.error) {
			try {
				$entry = $result.feed.entries[0];
				$entryTitle = $entry.title;
				if ($entry != null) {
					$date = new Date($entry.publishedDate);
					$a.html($entryTitle);
					$a.attr( {
						href : $entry.link,
						title : $date,
						rel : "external",
						rev : "bookmark",
						target : "_blank"
					});
				}
			} catch (error) {
				alert("error");
			}
		}
	});
}