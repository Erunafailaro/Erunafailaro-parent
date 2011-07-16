<?php
/**
 Plugin Name: WP Social Blogroll
 Plugin URI: http://www.weinschenker.name/plugin-feed-reading-blogroll/
 Description: This plugin allows you to add a social blogroll to your blog. The blogroll will display your bookmarks, their freshness and their latest post-title.
 Version: 1.5.7
 Author: Jan Weinschenker
 Author URI: http://www.weinschenker.name


 Plugin: Copyright 2008  Jan Weinschenker  (email: kontakt@weinschenker.name)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 ==============================================================================
 WP Social Blogroll uses the great JSMin

 JSMin 1.1.1
 jsmin.php - PHP implementation of Douglas Crockford's JSMin.
 Ryan Grove <ryan@wonko.com>
 copyright 2002 Douglas Crockford <douglas@crockford.com> (jsmin.c)
 copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
 license: http://opensource.org/licenses/mit-license.php MIT License
 http://code.google.com/p/jsmin-php/
 ==============================================================================

 */
global $blog_id;

if (version_compare(PHP_VERSION, '5.0.0') === 1) {
	require_once('inc/jsmin-1.1.1-frbr.php');
}
if (!defined('WP_CONTENT_URL'))
define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if (!defined('WP_CONTENT_DIR'))
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined( 'WP_PLUGIN_URL'))
define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if (!defined( 'WP_PLUGIN_DIR'))
define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
if (defined('MULTISITE') && MULTISITE == true && function_exists('get_blog_details')){
	define('FEEDREADING_JS_FILE','/feedreading_blogroll_blogid'.$blog_id.'.js');
} else {
	define('FEEDREADING_JS_FILE','/feedreading_blogroll.js');
}
define('FEEDREADING_VERSION', '1.5.7');

global $wp_version;

/**
 * add options actions
 */
add_option('feedreading_blogroll_settings', $data, 'feedreading_blogroll Options');
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'feedreading_blogroll_addConfigureLink' );

add_action('admin_menu',		'feedreading_blogroll_register_options');
add_action('admin_init', 		'feedreading_blogroll_register_setting');
add_action('widgets_init', 		'widget_init_feedreading_blogroll');

if (version_compare($wp_version, '2.8', '>=') or stristr( $wp_version, 'RC' ) == true) {
	add_action('admin_enqueue_scripts', 'feedreading_blogroll_admin_add_scripts');
	add_action('wp_enqueue_scripts', 'feedreading_blogroll_add_scripts');
	add_action('widgets_init', create_function('', 'return register_widget("FeedReadingBlogrollWidget");'));
	add_filter('ozh_adminmenu_icon_feedreading_blogroll.php', 'feedreading_blogroll_myplugin_icon');
}

if (version_compare($wp_version, '2.8', '<') and stristr( $wp_version, 'RC' ) == false) {
	add_action('wp_print_scripts',	'feedreading_blogroll_add_scripts_27');
	add_action('wp_print_styles',	'feedreading_blogroll_add_styles_27');
	add_action('after_plugin_row_'.plugin_basename(__FILE__), 'feedreading_blogroll_admin_27_notice');
}

add_action('wp_ajax_feedreading_blogroll_generate_javascript_lookup', 'feedreading_blogroll_generate_javascript_ajax');
add_action('add_link','feedreading_blogroll_generate_javascript');
add_action('edit_link','feedreading_blogroll_generate_javascript');
add_action('delete_link','feedreading_blogroll_generate_javascript');
add_action('update_option_feedreading_blogroll_settings', 'feedreading_blogroll_generate_javascript');
add_action('update_option_widget_feedreadingblogroll', 'feedreading_blogroll_generate_javascript');

/**
 * Register uninstall-hook
 * @since WordPress 2.7
 */
if (function_exists('register_uninstall_hook')){
	register_uninstall_hook(__FILE__, 'feedreading_blogroll_uninstall_hook');
}

/**
 * Register activation-hook
 * @since WordPress 2.7
 */
if (function_exists('register_activation_hook')){
	register_activation_hook(__FILE__, 'feedreading_blogroll_generate_javascript' );
}

/**
 * Load text-domain
 * @since WordPress 2.6
 */
if (function_exists('load_plugin_textdomain')) {
	load_plugin_textdomain('feedreading_blogroll', false, dirname(plugin_basename(__FILE__)).'/lang');
}

/**
 * Initialize the widget
 */
function widget_init_feedreading_blogroll() {
	// Check for required functions
	$desc = __('This widget adds the WP Social Blogroll to your sidebar.', 'feedreading_blogroll').' '.__('It can display multiple link categories. Only a single instance of this widget can be added to your sidebars.', 'feedreading_blogroll');
	$widget_ops = array('classname' => 'widget_feedreading_blogroll', 'description' => $desc,'feedreading_blogroll');
	wp_register_sidebar_widget('widget_feedreading_blogroll', 'WP Social Blogroll Multi', 'widget_feedreading_blogroll', $widget_ops);
	register_widget_control('widget_feedreading_blogroll', 'widget_feedreading_blogroll_control');
}

/**
 * Register Settings and options
 * @since WordPress 2.7
 */
function feedreading_blogroll_register_setting(){
	if (function_exists('register_setting')) {
		register_setting('feedreading_blogroll_settings_group', 'feedreading_blogroll_settings');
	}
}

/**
 * The uninstall-hook for the plugin
 * @since WordPress 2.7
 */
function feedreading_blogroll_uninstall_hook() {
	delete_option('feedreading_blogroll_settings');
}
/**
 * Register with options-page
 */
function feedreading_blogroll_register_options() {
	if (function_exists('add_options_page')) {
		$menutitle = '<img src="' . feedreading_blogroll_get_resource_url('ssprc.gif') . '" alt="" />' . ' ';
		add_options_page($menutitle. 'WP Social Blogroll', $menutitle. 'WP Social Blogroll', 8, basename(__FILE__), 'feedreading_blogroll_options_subpanel');
	}
}
/**
 * Add configure-link to plugin-list
 *
 * @since WordPress 2.7
 * @param unknown_type $links
 * @return unknown
 */
function feedreading_blogroll_addConfigureLink($links) {
	$settings_link = '<a href=\'options-general.php?page=feedreading_blogroll.php\'>'.__('Settings','feedreading_blogroll').'</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

/**
 * Get link-categories from database
 * @return resultset containing term_id and term_id
 */
function feedreading_blogroll_get_linkcats(){
	global $wpdb;
	$cats = $wpdb->get_results("SELECT $wpdb->terms.term_ID, $wpdb->terms.name FROM $wpdb->terms join $wpdb->term_taxonomy on $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id WHERE $wpdb->term_taxonomy.taxonomy = 'link_category' order by name");
	return $cats;
}

/**
 * Return an array containing all IDs from all existing single-category-widgets
 * @since 2.8
 *
 * @return array of int values, may be empty
 */
function feedreading_blogroll_get_widget_ids(){
	$ids = array();
	$sidebars_widgets=wp_get_sidebars_widgets();
	$option=get_option('widget_feedreadingblogroll');
	foreach((array) $sidebars_widgets as $sidebar_widgets) {
		foreach ((array) $sidebar_widgets as $widget){
			$pos = strpos($widget, "feedreadingb");
			if ($pos===0){
				$key=intval(substr($widget,20, strlen($widget)-18));
				array_push($ids, $key);
			}
		}
	}
	return $ids;
}

/**
 * Returns true if the multi blogroll widget is active
 * @since 2.8
 *
 * @return array of int values, may be empty
 */
function feedreading_blogroll_get_multiwidget_id(){
	$feedreading_blogroll_settings = get_option('feedreading_blogroll_settings');
	$enableLinkPage = strcmp($feedreading_blogroll_settings['enableLinkPage'], 'enableLinkPage');
	if ($enableLinkPage == 0){
		return (bool) TRUE;
	}
	$foundWidget = (bool) FALSE;
	$sidebars_widgets=wp_get_sidebars_widgets();
	foreach((array) $sidebars_widgets as $sidebar_name => $sidebar_widgets) {
		if ($sidebar_name != "wp_inactive_widgets"){
			foreach ((array) $sidebar_widgets as $widget){
				if ($widget == 'widget_feedreading_blogroll') {
					$foundWidget = (bool) TRUE;
				}
			}
		}
	}
	return (bool)$foundWidget;
}




/**
 * return an array of category-IDs ordered by the user
 *
 * @param array of strings $linkCats
 * @param array of strings  $orderString
 * @return ordered array of strings. The strings are category-ids
 */
function feedreading_blogroll_order_cats($linkCats, $orderString){
	$orderArray=explode(",", $orderString);
	$orderArray2 = array();
	$orderedCats = array();
	$linkCats2= array();
	$i=1;
	foreach($orderArray as $orderedCat){
		$orderArray2[substr($orderedCat,8)]=$i;
		$i=$i+1;
	}
	$i=1;
	foreach($linkCats as $linkCat){
		$catId=(int) $linkCat->term_ID;
		if(!empty($orderArray2->$catId)){
			$linkCats2[$orderArray2[$catId]]=$linkCat;
		} else {
			$linkCats2[$i+1000000]=$linkCat;
		}
		$i=$i+1;
	}
	ksort($linkCats2);
	return array_values($linkCats2);
}

/**
 * Returns true, if new values are contained in POST-request, else false
 * @return boolean
 */
function feedreading_blogroll_vars_are_set(){
	if (isset($_POST['rebuild_javascript'])) {
		feedreading_blogroll_generate_javascript();
	}

	if (isset($_POST['linkCssClass']) or
	isset($_POST['showLastUpdated']) or
	isset($_POST['showLastPostTitle']) or
	isset($_POST['feedreadingTitle']) or
	isset($_POST['showAuthor']) or
	isset($_POST['showTitle']) or
	isset($_POST['showIcon']) or
	isset($_POST['defaultIcon']) or
	isset($_POST['googleAPIKey']) or
	isset($_POST['previewButton']) or
	isset($_POST['linkCats']) or
	isset($_POST['groupByLinkCats']) or
	isset($_POST['maxBookmarks']) or
	isset($_POST['orderBy']) or
	isset($_POST['orderDirection']) or
	isset($_POST['customCSSDir']) or
	isset($_POST['displayStyle']) or
	isset($_POST['feedDiscovery']) or
	isset($_POST['jsSorting']) or
	isset($_POST['enableLinkPage']) or
	isset($_POST['jsSortingTimeout']) or
	isset($_POST['bookmarkTooltip']) or
	isset($_POST['categoryOrderArray']) or
	isset($_POST['scriptLoadingPosition'])
	){
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Enqueue scripts after WordPress has finished loading but before any headers are sent.
 *
 * files are headed to header or footer, according to the user's setting
 *
 * @since WordPress 2.8
 */
function feedreading_blogroll_add_scripts(){
	$feedreading_blogroll_settings = get_option('feedreading_blogroll_settings');
	$scriptinheader = (bool) strcmp($feedreading_blogroll_settings['scriptLoadingPosition'],'footer');
	
	$feedreadingVersion = $feedreading_blogroll_settings['version'];
	if ($feedreadingVersion == null or version_compare($feedreadingVersion, FEEDREADING_VERSION, '<')){
		feedreading_blogroll_generate_javascript();
		$feedreading_blogroll_settings['version'] = FEEDREADING_VERSION;
		update_option('feedreading_blogroll_settings', $feedreading_blogroll_settings);
	}

	if ($feedreading_blogroll_settings['customCSSDir'] != ''){
		$customStyleFile = $feedreading_blogroll_settings['customCSSDir'].'/feedreading_blogroll.css';
		wp_register_style('feedreading_style', $customStyleFile, array(), FEEDREADING_VERSION);
		wp_enqueue_style('feedreading_style');
	} else {
		$defaultStyleFile = WP_PLUGIN_URL.'/feed-reading-blogroll/css/feedreading_blogroll.css';
		wp_register_style('feedreading_style', $defaultStyleFile, array(), FEEDREADING_VERSION);
		wp_enqueue_style('feedreading_style');
	}

	$googleAPIKey = $feedreading_blogroll_settings['googleAPIKey'];
	$blogDeps = array('jquery');
	wp_enqueue_script('feedreading_main', WP_CONTENT_URL.FEEDREADING_JS_FILE, $blogDeps, FEEDREADING_VERSION, !$scriptinheader);
}

/**
 * Add JavaScripts and CSS-styles header of admin-page
 * @since WordPress 2.8
 */
function feedreading_blogroll_admin_add_scripts($hook_suffix){
	if(strcmp($hook_suffix, 'settings_page_feedreading_blogroll')==0){
		$feedreading_blogroll_settings = get_option('feedreading_blogroll_settings');
		$googleAPIKey = $feedreading_blogroll_settings['googleAPIKey'];
		$adminDeps = array( "jquery-ui-tabs", "jquery-ui-sortable", "feedreading_form_validation",  "feedreading_google_api");
		$adminStyleFile = WP_PLUGIN_URL.'/feed-reading-blogroll/css/feedreading_blogroll_admin.css';

		wp_register_script('feedreading_google_api', 'http://www.google.com/jsapi?key='.$googleAPIKey, array(), FEEDREADING_VERSION,true);
		wp_register_script('feedreading_form_validation',WP_PLUGIN_URL.'/feed-reading-blogroll/js/jquery.validate.pack.js', array('jquery'),'1.5.2',true);
		wp_enqueue_script('feedreading_admin_ajax', WP_PLUGIN_URL.'/feed-reading-blogroll/js/feedreading_blogroll_admin.packed.js', $adminDeps, FEEDREADING_VERSION,true);
		wp_enqueue_style('feedreading_admin_style', $adminStyleFile, array(), FEEDREADING_VERSION);
	}
}

/**
 * add icon to Ozh' Admin Drop Down Menu
 * for WP 2.8 and later
 */
function feedreading_blogroll_myplugin_icon() {
    return feedreading_blogroll_get_resource_url('ssprc.gif');
}

/********************************************************************************************
 * LEGACY-CODE WORDPRESS 2.7.1 AND OLDER
 ********************************************************************************************
 */

/**
 * Enqueue scripts after WordPress has finished loading but before any headers are sent.
 * @since WordPress 2.7
 */
function feedreading_blogroll_add_scripts_27(){
	$feedreading_blogroll_settings = get_option('feedreading_blogroll_settings');
	$googleAPIKey = $feedreading_blogroll_settings['googleAPIKey'];
	if (!is_admin()){
		$blogDeps = array('jquery');
		wp_register_script('feedreading_main', WP_CONTENT_URL.FEEDREADING_JS_FILE, $blogDeps, FEEDREADING_VERSION);
		wp_enqueue_script('feedreading_main');
	} else if(is_admin()){
		$adminDeps = array( "jquery-ui-tabs", "jquery-ui-sortable", "feedreading_form_validation",  "feedreading_google_api");
		wp_register_script('feedreading_google_api', 'http://www.google.com/jsapi?key='.$googleAPIKey, array(), FEEDREADING_VERSION);
		wp_register_script('feedreading_form_validation',WP_PLUGIN_URL.'/feed-reading-blogroll/js/jquery.validate.pack.js', array('jquery'),'1.5.2');
		wp_register_script('feedreading_admin_ajax', WP_PLUGIN_URL.'/feed-reading-blogroll/js/feedreading_blogroll_admin.packed.js', $adminDeps, FEEDREADING_VERSION);
		wp_enqueue_script('feedreading_admin_ajax');
	}
}
/**
 * Enqueue styles after WordPress has finished loading but before any headers are sent.
 * @since WordPress 2.7
 */
function feedreading_blogroll_add_styles_27(){
	$feedreading_blogroll_settings = get_option('feedreading_blogroll_settings');
	if(!is_admin()){
		if ($feedreading_blogroll_settings['customCSSDir'] != ''){
			$customStyleFile = $feedreading_blogroll_settings['customCSSDir'].'/feedreading_blogroll.css';
			wp_register_style('feedreading_style', $customStyleFile, array(), FEEDREADING_VERSION);
			wp_enqueue_style('feedreading_style');
		} else {
			$defaultStyleFile = WP_PLUGIN_URL.'/feed-reading-blogroll/css/feedreading_blogroll.css';
			wp_register_style('feedreading_style', $defaultStyleFile, array(), FEEDREADING_VERSION);
			wp_enqueue_style('feedreading_style');
		}
	} else if(is_admin()){
		$adminStyleFile = WP_PLUGIN_URL.'/feed-reading-blogroll/css/feedreading_blogroll_admin.css';
		wp_register_style('feedreading_admin_style', $adminStyleFile, array(), FEEDREADING_VERSION);
		wp_enqueue_style('feedreading_admin_style');
	}
}

/********************************************************************************************
 * END OF LEGACY-CODE WORDPRESS 2.7.1 AND OLDER
 ********************************************************************************************
 */

/**
 * Images/ Icons in base64-encoding
 * @see http://bueltge.de/test/image2base64/
 * @use function feedreading_blogroll_get_resource_url() for display
 */
if( isset($_GET['resource']) && !empty($_GET['resource'])) {
	# base64 encoding
	$feedreading_resources = array(
		'ssprc.gif' =>
			'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABG'.
			'dBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1h'.
			'Z2VSZWFkeXHJZTwAAAK4SURBVBgZBcFfaFV1AADg7/zO2Zy7A3'.
			'fVebdEQ2yCoeVfmmhQGlRgmCg0sEAoeivvWy/BiigYvoi9hPYg'.
			'EfQYKGVgiIhaaJPSVlC6hU7v9Pon166755577+n7ovRtvZ3LjU'.
			'iUAQQEAAQAQBO3HWrc8HHSudyIwRfLShuJOwgJUUSIiAIhIkSI'.
			'iRAhb3H7Urnz++MSibLSRtKY/s3EuPsH9y4TAnFAIEYUyHN6Fr'.
			'NkA0uOlxMQdxC6WL8bsJusxt+nuPYdrVlE5DkiHk7TtYg2QRtR'.
			'wuMKV79l4hy1Kh0Fnn6dVw/zxBAgIsuoZzQbNEgEhIjHFX7/mh'.
			'CRBBavY92bLFjOcwe4fIRrp0ibNFKyFnWCgID+zez9iu2f8NQu'.
			'ZiY4/QE3zgIb3qW0jjSl3iRr0iBoQ0yEjgJ9q3l2H68cpncFY1'.
			'/wzxlgywHyedQzshZ1goAID37j5PuMHaFWpaObbR/Ss5Szh5id'.
			'prOHNTupN8naNEgEhEAemLurfv20cxfGXElXKi7qc/9en709ty'.
			'z88XNfTixSLBY99JJnJqu2pbFEGyLiXLap7JsT5/2Xd9r31htq'.
			'tZqpqSnHf0p136l6bd9+cRyrVCrGxsbcbKySgJDTt8aFyYbrlX'.
			'/tGR527NP3nJ+c0z8wYO3atSYezbl48KDp6WlbV5Zs27vfyckd'.
			'EiBi7q5fxy8pFAqgvHrGmT8bhoeHFYtFs7OzqtWqo0ePKj8/6B'.
			'cUehdKNJA30TJw/5yfHw2A0cvztNoNo6OjCoUCqNVq8mZm9MRF'.
			'O97ZqnrrpvijQb365w8pDpo/v9vZ8WnpXGrV+i02DW21bNkyrV'.
			'ZLqVQyNDTkhe3bDawY9Nf4VdfGrzyI0pf1dvYb0a/cajE52+Oz'.
			'25t1L3lSx7wu7Zw9d36wMJtzbOkurSjI0rp7lamZBTMPdv4PY0'.
			'MOogadRGMAAAAASUVORK5CYII='.
	'',
	'text-list.gif' =>'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABG'.
'dBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1h'.
'Z2VSZWFkeXHJZTwAAADqSURBVDjLY/j//z8DJZiBKgbkzH9cMH'.
'XX6wcgmiwDQJq3nv/4H0SD+OXl5dlA/L+kpOR/QUHB/+zs7P+p'.
'qan/ExIS/kdGRv4PDg7+T10XDHwgpsx8VNC56eWDkJ675Hmhbf'.
'3zB0uPvP1fuvQpOBDj4uKyIyIi/gcGBv738vL67+zs/N/Gxua/'.
'iYnJf11d3f9qamqogRjQcaugZPHjB66V14ZqINrmXyqIn3bvgX'.
'XeJfK8ANLcv+3lfxAN4hsZGWVra2v/V1FR+S8nJ/dfXFz8v5CQ'.
'0H8eHp7/7Ozs/5mZmVEDEWQzRS6gBAMAYBDQP57x26IAAAAASU'.
'VORK5CYII='.
'',
'help.gif' => 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABG'.
'dBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1h'.
'Z2VSZWFkeXHJZTwAAAKkSURBVDjLpZPdT5JhGMb9W+BPaK3mat'.
'VqndXWOOigA6fmJ9DUcrUMlrN0mNMsKTUznQpq6pyKAm8CIogm'.
'ypcg8GIiX8rHRHjhVbPt6o01nMvZWge/k3vP9duuZ/edAyDnf/'.
'hjoCMP2Vr3gUDj3CdV6zT1xZ6iFDaKnLEkBFOmPfaZArWT5sw6'.
'0iFP+BAbOzTcQSqDZzsNRyCNkcVoaGghzDlVQKylOHJrMrUZ2Y'.
'f52y6kc36IxpyoH1lHF7EBgyMKV4jCJ5U/1UVscU4IZOYEa3I1'.
'HtwI01hwxlDLhDoJD/wxGr5YGmOLAdRIrVCuhmD3JdA6SQabx1'.
'2srGB0KSpc86ew4olDOGjH4x4z0gdHDD9+c4TaQQtq+k2Yt0eg'.
'XYugTmoVZgV9cyHSxXTtJjZR3WNCVfcK/NE0ppYDUNu2QTMCtS'.
'0IbrsOrVMOWL27eNJtJLOCDoWXdgeTEEosqPxoBK/TwDzWY9ro'.
'wy51gJ1dGr2zLpS2aVH5QQ+Hbw88sZ7OClrGXbQrkMTTAQu4HX'.
'qUv9eh7J0OSfo7tiIU+GItilpUuM/AF2tg98eR36Q+FryQ2kjb'.
'VhximQu8dgPKxPMoeTuH4tfqDIWvCBQ2KlDQKEe9dBlGTwR36+'.
'THFZg+QoUxAL0jgsoOQzYYS+wjskcjTzSToVAkA7Hqg4Spc6tm'.
'4vgT+eIFVvmb+eCSMwLlih/cNg0KmpRoGzdl+BXOb5jAsMYNjS'.
'WAm9VjwesPR1knFilPNMu510CkdPZtqK1BvJQsoaRZjqLGaTzv'.
'1UNp9EJl9uNqxefU5QdDnFNX+Y5Qxrn9bDLUR6zjqzsMizeWYd'.
'G5gy6ZDbk8aehiuYRz5jHdeDTKvlY1IrhSMUxe4g9SuVwpdaFs'.
'gDxf2i84V9zH/us1/is/AdevBaK9Tb3EAAAAAElFTkSuQmCC'.
'');
	if(array_key_exists($_GET['resource'], $feedreading_resources)) {
		$content = base64_decode($feedreading_resources[ $_GET['resource'] ]);
		$lastMod = filemtime(__FILE__);
		$client = ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false );
		// Checking if the client is validating his cache and if it is current.
		if (isset($client) && (strtotime($client) == $lastMod)) {
			// Client's cache IS current, so we just respond '304 Not Modified'.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 304);
			exit;
		} else {
			// Image not cached or cache outdated, we respond '200 OK' and output the image.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 200);
			header('Content-Length: '.strlen($content));
			header('Content-Type: image/' . substr(strrchr($_GET['resource'], '.'), 1) );
			echo $content;
			exit;
		}
	}
}

/**
 * Display Images/ Icons in base64-encoding
 * @return $resourceID
 */
function feedreading_blogroll_get_resource_url($resourceID) {
	return trailingslashit( get_bloginfo('url') ) . '?resource=' . $resourceID;
}

/**
 * Read http-data and set options.
 *
 * @return array with updated plugin-options
 */
function feedreading_blogroll_setOptions() {
	$feedreading_blogroll_settings = get_option('feedreading_blogroll_settings');
	if (feedreading_blogroll_vars_are_set()) {
		$feedreading_blogroll_settings['linkCssClass']      = trim(strip_tags(stripslashes($_POST['linkCssClass'])));
		$feedreading_blogroll_settings['linkCats']          = $_POST['linkCats'];
		$feedreading_blogroll_settings['groupByLinkCats']   = strip_tags(stripslashes($_POST['groupByLinkCats']));
		$feedreading_blogroll_settings['orderBy']           = strip_tags(stripslashes($_POST['orderBy']));
		$feedreading_blogroll_settings['orderDirection']    = strip_tags(stripslashes($_POST['orderDirection']));
		$feedreading_blogroll_settings['showLastUpdated']   = strip_tags(stripslashes($_POST['showLastUpdated']));
		$feedreading_blogroll_settings['showLastPostTitle'] = strip_tags(stripslashes($_POST['showLastPostTitle']));
		$feedreading_blogroll_settings['feedreadingTitle']  = strip_tags(stripslashes($_POST['feedreadingTitle']));
		$feedreading_blogroll_settings['displayStyle']      = strip_tags(stripslashes($_POST['displayStyle']));
		$feedreading_blogroll_settings['showAuthor']        = strip_tags(stripslashes($_POST['showAuthor']));
		$feedreading_blogroll_settings['showTitle']         = strip_tags(stripslashes($_POST['showTitle']));
		$feedreading_blogroll_settings['defaultIcon']       = trim(strip_tags(stripslashes($_POST['defaultIcon'])));
		$feedreading_blogroll_settings['showIcon']          = strip_tags(stripslashes($_POST['showIcon']));
		$feedreading_blogroll_settings['googleAPIKey']      = strip_tags(stripslashes($_POST['googleAPIKey']));
		$feedreading_blogroll_settings['previewButton']     = strip_tags(stripslashes($_POST['previewButton']));
		$feedreading_blogroll_settings['customCSSDir']      = trim(strip_tags(stripslashes($_POST['customCSSDir'])));
		$feedreading_blogroll_settings['feedDiscovery']     = trim(strip_tags(stripslashes($_POST['feedDiscovery'])));
		$feedreading_blogroll_settings['enableLinkPage']     = trim(strip_tags(stripslashes($_POST['enableLinkPage'])));
		$feedreading_blogroll_settings['faviconDiscovery']  = trim(strip_tags(stripslashes($_POST['faviconDiscovery'])));
		$feedreading_blogroll_settings['jsSorting']         = trim(strip_tags(stripslashes($_POST['jsSorting'])));
		$feedreading_blogroll_settings['jsSortingTimeout']  = trim(strip_tags(stripslashes($_POST['jsSortingTimeout'])));
		$feedreading_blogroll_settings['bookmarkTooltip']  = trim(strip_tags(stripslashes($_POST['bookmarkTooltip'])));
		if ($feedreading_blogroll_settings['displayStyle']=="rolling"){
			$feedreading_blogroll_settings['maxBookmarks']="";
		} else {
			$feedreading_blogroll_settings['maxBookmarks']      = trim(strip_tags(stripslashes($_POST['maxBookmarks'])));
		}
		$feedreading_blogroll_settings['categoryOrderArray'] = trim(strip_tags(stripslashes($_POST['categoryOrderArray'])));
		$feedreading_blogroll_settings['scriptLoadingPosition'] = trim(strip_tags(stripslashes($_POST['scriptLoadingPosition'])));

		update_option('feedreading_blogroll_settings', $feedreading_blogroll_settings);
		$feedreading_blogroll_settings['flash'] = __('Settings saved.','feedreading_blogroll');
	}
	return $feedreading_blogroll_settings;
}


/**
 * Render the options-panel with form
 */
function feedreading_blogroll_options_subpanel(){
	global $wp_version;

	if (function_exists('wp_nonce_field') and !function_exists('settings_fields')) {
		if (feedreading_blogroll_vars_are_set())
		check_admin_referer('plugin-feed-reading-blogroll-action_option_panel');
	} else if (function_exists('settings_fields')) {
		if (feedreading_blogroll_vars_are_set())
		check_admin_referer('feedreading_blogroll_settings_group-options');
	}
	$feedreading_blogroll_settings = feedreading_blogroll_setOptions();
	$cats = feedreading_blogroll_get_linkcats();
	$hasSingleWidgets=false;
	$menutitle = '<img src="' . feedreading_blogroll_get_resource_url('ssprc.gif') . '" alt="" />' . ' ';
	if (is_null($feedreading_blogroll_settings['linkCats'])) $feedreading_blogroll_settings['linkCats']='';
	if ($feedreading_blogroll_flash != ''){
		?>
<div id="message" class="updated fade">
<p><?php echo $feedreading_blogroll_flash; ?></p>
</div>

<?php } ?>

<?php if (version_compare($wp_version, '2.8', '<') and stristr( $wp_version, 'RC' ) == false) { ?>
<div id="deprecated_warning" class="updated">
<p>
	<?php
 	printf(__("You are currently using WordPress version %s. Please be informed, that future versions of the WP Social Blogroll plugin will no longer support this version of WordPress. I recommend to upgrade your WordPress-installation to <a href=\"http://wordpress.org\">the latest version</a>.","feedreading_blogroll"),$wp_version);
	?>
</p>
</div>
<?php } ?>

<?php if (version_compare($wp_version, '5.8', '>=')) { ?>
<div id="deprecated_warning" class="updated">
<p>
	<strong>
		<?php printf(__("If you have just upgraded to WordPress 2.8, you will have to add the Widget of the WP Social Blogroll plugin to your sidebar again. <a href=\"%s\">Please go to the Widget-Settings-Page</a> to add the widget to your sidebar.","feedreading_blogroll"),get_option('siteurl'). '/wp-admin/widgets.php'); ?>
	</strong>
</p>
</div>
<?php } ?>

<div class="wrap">
<h2><?php echo $menutitle; ?> WP Social Blogroll</h2>

	<?php
	if (!$isHook and is_writable(WP_CONTENT_DIR) and file_exists(WP_CONTENT_DIR.FEEDREADING_JS_FILE) and is_writable(WP_CONTENT_DIR.FEEDREADING_JS_FILE)) { ?>
<div id="message" class="error fade">
<p><?php _e('JavaScript-file <strong>and</strong> content-directory are writable. This is no longer necessary. You should make the content-directory read-only (chmod 755) now for security-reasons.</p><p>This plugin requires <strong>only</strong> JavaScript-file itself to be writable.', 'feedreading_blogroll'); ?></p>
<p><?php echo WP_CONTENT_DIR.FEEDREADING_JS_FILE; ?></p>
</div>
	<?php }

	if (feedreading_is_not_jsfile_writable()) {?>
<div id="message" class="error fade">
<p><?php _e('Content directory and/or JavaScript-file not writable!', 'feedreading_blogroll'); ?></p>
<p><?php echo WP_CONTENT_DIR.FEEDREADING_JS_FILE; ?></p>
</div>
	<?php
	}?>
	<div id="devbloglinks">
		<ul>
			<li>
				<small><a href="http://www.weinschenker.name/kategorien/feedreadingblogroll/" title="<?php _e('Developer&rsquo;s Blog', 'feedreading_blogroll'); ?>" rel="external"><?php _e('Developer&rsquo;s Blog', 'feedreading_blogroll'); ?></a>
		(<a href="http://www.weinschenker.name/kategorien/feedreadingblogroll/feed" title="<?php _e('Subscribe to the Developer&rsquo;s RSS-Feed', 'feedreading_blogroll'); ?>" rel="external"><?php _e('RSS-Feed', 'feedreading_blogroll'); ?></a>):
				<strong><a id="devblogentryanchor" href="#" target="_blank"></a></strong></small>
			</li>
			<li><small><a href="http://www.weinschenker.name/plugin-feed-reading-blogroll/"
				target="_blank"><?php _e('Plugin Home','feedreading_blogroll');?></a></small>
			</li>
			<li><small><a href="http://wordpress.org/extend/plugins/feed-reading-blogroll/"
				target="_blank"><?php _e('Download latest Version','feedreading_blogroll');?></a></small>
			</li>
			<li><small><a
				href="http://wordpress.org/tags/feed-reading-blogroll?forum_id=10#postform"
				target="_blank"><?php _e('Supportforum','feedreading_blogroll');?></a></small>
			</li>
			<li><small><a
				href="http://wiki.weinschenker.name/feedreadingblogroll:start"
				target="_blank"><?php _e('Help','feedreading_blogroll');?> / <?php _e('Wiki','feedreading_blogroll');?></a></small>
			</li>
			<li><small><a
				href="http://www.facebook.com/pages/WP-Social-Blogroll-A-WordPress-Plugin/191088298807"
				target="_blank"><?php _e('Facebook','feedreading_blogroll');?></a></small>
			</li>

		</ul>
	</div>
<div id="feedreading_blogroll_option_tabs">

	<ul id="feedreading_blogroll_option_tab_heads">
		<li><span><a href="#feedreading_blogroll_main_tab"><?php _e('Options','feedreading_blogroll'); ?></a></span></li>
		<li><span><a href="#feedreading_blogroll_preview_tab"><?php _e('URL-Check','feedreading_blogroll');?></a></span></li>
		<li><span><a href="#feedreading_blogroll_js_tab"><?php _e('JavaScript','feedreading_blogroll');?></a></span></li>
		<li><span><a href="#feedreading_blogroll_news_tab"><?php _e('News','feedreading_blogroll');?></a></span></li>
	</ul>

	<div id="feedreading_blogroll_main_tab">
		<h3><?php _e('Options','feedreading_blogroll'); ?></h3>
		<form id="feedreading_options_form" action="" method="post"><?php
			if (function_exists('wp_nonce_field') and !function_exists('settings_fields')) {
				wp_nonce_field('plugin-feed-reading-blogroll-action_option_panel');
			} else if (function_exists('settings_fields')) {
				settings_fields('feedreading_blogroll_settings_group');
			}
			?>
			<p class="submit"><input name="submit" class="button-primary" value="<?php _e('Save Changes','feedreading_blogroll'); ?>" type="submit" /></p>

			<table class="form-table" summary="<?php _e('Options','feedreading_blogroll'); ?>">
				<tr>
					<th scope="row" valign="top"><label for="googleAPIKey"> <a
						href="http://code.google.com/apis/ajaxfeeds/signup.html"><?php _e('Google API Key','feedreading_blogroll')?></a>
					</label></th>
					<td><input size="90" class="required" id="googleAPIKey" name="googleAPIKey" type="text"
						value="<?php echo $feedreading_blogroll_settings['googleAPIKey']; ?>" title="<?php _e('This field is required!','feedreading_blogroll')?>" />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="feedreadingTitle"> <?php _e('Blogroll Title for sidebar widget','feedreading_blogroll')?>
					</label></th>
					<td>
					<fieldset><input size="90" id="feedreadingTitle"
						name="feedreadingTitle" type="text"
						value="<?php echo $feedreading_blogroll_settings['feedreadingTitle']; ?>" /><br />
					<input id="showTitle" name="showTitle" type="checkbox"
						value="showTitle"
						<?php checked('showTitle', $feedreading_blogroll_settings['showTitle']);?> />
						<?php _e('Display title above blogroll','feedreading_blogroll')?></fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="feedDiscovery"> <?php _e('Use Google Feed Discovery','feedreading_blogroll')?></label>
					</th>
					<td><input id="feedDiscovery" name="feedDiscovery" type="checkbox"
						value="feedDiscovery"
						<?php checked('feedDiscovery', $feedreading_blogroll_settings['feedDiscovery']);?> />
							<div class="popup_info" title="<?php _e('Click me for help','feedreading_blogroll'); ?>">
							<span class="infotext_trigger"><img	src="<?php echo feedreading_blogroll_get_resource_url('help.gif');?>" alt="" /></span>
							<p class="infotext">
								<?php _e('Automatic Feed Discovery is a service by Google which will try to find the correct feeds that belong to your bookmarks. When using Feed Discovery, you do not need to save extra feed-URLs along with your bookmarks. If you have saved a feed-URL for a bookmark, the plugin will always use the saved feed for that particular bookmark instead of Google Feed Discovery.' ,'feedreading_blogroll')?>
							</p>
							</div>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="faviconDiscovery"> <?php _e('Use Google Favicon Discovery','feedreading_blogroll')?></label>
					</th>
					<td><input id="faviconDiscovery" name="faviconDiscovery" type="checkbox"
						value="faviconDiscovery"
						<?php checked('faviconDiscovery', $feedreading_blogroll_settings['faviconDiscovery']);?> />
							<div class="popup_info" title="<?php _e('Click me for help','feedreading_blogroll'); ?>">
							<span class="infotext_trigger"><img	src="<?php echo feedreading_blogroll_get_resource_url('help.gif');?>" alt="" /></span>
							<p class="infotext">
								<?php _e('Automatic Favicon Discovery is a service by Google which will try to find the correct favicons that belong to your bookmarks. When using Favicon Discovery, you do not need to save extra icon-URLs along with your bookmarks. If you have saved a icon-URL for a bookmark, the plugin will always use the saved icon for that particular bookmark instead of Google Favicon Discovery.' ,'feedreading_blogroll')?>
							</p>
							</div>
					</td>
				</tr>

<?php if (version_compare($wp_version, '2.8', '>=') or stristr( $wp_version, 'RC' ) == true) { ?>
				<tr>
					<th scope="row" valign="top"><label for="scriptLoadingPosition"> <?php _e('JavaScript loading in','feedreading_blogroll')?></label>
					</th>
					<td>
						<select id="scriptLoadingPosition" name="scriptLoadingPosition"
							style="width: 300px;">
							<option value=""
							<?php selected($feedreading_blogroll_settings['scriptLoadingPosition'], ''); ?>>
								<?php _e('Pageheader','feedreading_blogroll'); ?> (<?php _e('Default','feedreading_blogroll'); ?>)</option>
							<option value="footer"
							<?php selected($feedreading_blogroll_settings['scriptLoadingPosition'], 'footer'); ?>>
								<?php _e('Pagefooter','feedreading_blogroll'); ?></option>
						</select>
							<div class="popup_info" title="<?php _e('Click me for help','feedreading_blogroll'); ?>">
							<span class="infotext_trigger"><img	src="<?php echo feedreading_blogroll_get_resource_url('help.gif');?>" alt="" /></span>
							<p class="infotext">
								<?php _e('Loading of the JavaScript from your blog&rsquo;s header is better for the compatibility, while loading it from the footer is better for the performance.' ,'feedreading_blogroll')?>
							</p>
							</div>
					</td>
				</tr>
<?php } ?>
			</table>
			<h3><?php _e('Link categories','feedreading_blogroll'); ?></h3>
			<table class="form-table" summary="<?php _e('Options','feedreading_blogroll'); ?>">
				<tr>
					<th scope="row" valign="top"><?php _e('Categories','feedreading_blogroll')?></th>
					<td>
						<ul id="categoryOrderList">
						<?php
						$orderedArray = feedreading_blogroll_order_cats((array) $cats, $feedreading_blogroll_settings['categoryOrderArray']);
						foreach ($orderedArray as $orderedCat) {
							$checked = "";
							$inBlogroll = "";
							if(!empty($feedreading_blogroll_settings) and !is_null($feedreading_blogroll_settings['linkCats']))
							if (in_array($orderedCat->name , (array)$feedreading_blogroll_settings['linkCats'])==1) {
								$checked =" checked=\"checked\" ";
								$inBlogroll = " inblogroll";
							}
							 ?>
							<li id="linkcat_<?php echo $orderedCat->term_ID;?>" class="<?php echo $inBlogroll;?>"><img
								src="<?php echo feedreading_blogroll_get_resource_url('text-list.gif');?>" alt="" />
							<input type="checkbox" class="categoryCheckbox" name="linkCats[]"
							id="checkbox_<?php echo $orderedCat->term_ID ?>" value="<?php echo($orderedCat->name); ?>"
							<?php echo $checked; ?> /> <label
							for="checkbox_<?php echo $orderedCat->term_ID; ?>"><?php echo($orderedCat->name); ?></label>
							</li>
							<?php } ?>
						</ul>
							<div class="popup_info" title="<?php _e('Click me for help','feedreading_blogroll'); ?>">
							<span class="infotext_trigger"><img	src="<?php echo feedreading_blogroll_get_resource_url('help.gif');?>" alt="" /></span>
							<p class="infotext">
								<?php _e('Add a category to your blogroll by activating its checkbox.','feedreading_blogroll')?>
								<?php _e('Use drag and drop to reorder the categories, then save the changes with the button above.','feedreading_blogroll')?>
							</p>
							</div>

						<input id="categoryOrderArray" name="categoryOrderArray" size="256"
						type="hidden" />
					</td>
				</tr>
				<?php
				$ids = feedreading_blogroll_get_widget_ids();
				$widget_option=get_option('widget_feedreadingblogroll');
				if(sizeof($ids) > 0 ){ ?>
				<tr>
					<th scope="row" valign="top"><label for="groupByLinkCats"><?php _e('Single Category Widgets','feedreading_blogroll')?></label>
					</th>
					<td>
						<ul id="singlewidgetlist" style="float:left"><?php
							foreach((array)$ids as $key){
								$eachCat=	$widget_option[$key]["cat"];
								$eachTitle=	$widget_option[$key]["title"];
								$eachTerm = get_term_by('id',$eachCat,'link_category');
								$eachCatName = $eachTerm->name;
								?><li><?php echo $eachCatName. ' &bull; '. $eachTitle .'<br /><small>'.__('Widget','feedreading_blogroll').' '.$key.'</small>'; ?></li><?php

							}
						?>

							<div class="popup_info" title="<?php _e('Click me for help','feedreading_blogroll'); ?>">
							<span class="infotext_trigger"><img	src="<?php echo feedreading_blogroll_get_resource_url('help.gif');?>" alt="" /></span>
							<p class="infotext">
								<?php _e('You have added these widgets to your sidebar from the Widget-Panel. There, you can edit the widget-titles and the order of the widgets.','feedreading_blogroll')?>
							</p>
							</div>
						</ul>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th scope="row" valign="top"><label for="groupByLinkCats"> <?php _e('Group bookmarks by category','feedreading_blogroll')?></label>
					</th>
					<td><input id="groupByLinkCats" name="groupByLinkCats" type="checkbox"
						value="groupByLinkCats"
						<?php checked('groupByLinkCats', $feedreading_blogroll_settings['groupByLinkCats']);?> />
					</td>
				</tr>
				<tr>

					<th scope="row" valign="top"><label for="enableLinkPage"> <?php _e('Enable Linkpage','feedreading_blogroll')?></label>
					</th>
					<td><input id="enableLinkPage" name="enableLinkPage" type="checkbox"
						value="enableLinkPage"
						<?php checked('enableLinkPage', $feedreading_blogroll_settings['enableLinkPage']);?> />
							<div class="popup_info" title="<?php _e('Click me for help','feedreading_blogroll'); ?>">
							<span class="infotext_trigger"><img	src="<?php echo feedreading_blogroll_get_resource_url('help.gif');?>" alt="" /></span>
							<p class="infotext">
								<?php _e('Enable this option, if you are using the blogroll on a linkpage.','feedreading_blogroll')?>
								<a href="http://wiki.weinschenker.name/feedreadingblogroll:linkpage" target="_blank"><?php _e('Help','feedreading_blogroll')?></a>
							</p>
							</div>
					</td>
				</tr>
			</table>
			<h3><?php _e('Sorting','feedreading_blogroll'); ?></h3>
			<table class="form-table"  summary="<?php _e('Options','feedreading_blogroll'); ?>">
				<tr>
					<th scope="row" valign="top"><label for="orderBy"><?php _e('Order bookmarks by','feedreading_blogroll')?></label>
					</th>
					<td>
					<fieldset><select name="orderBy" id="orderBy" style="width: 300px;">
						<option value="id"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'id'); ?>>id</option>
						<option value="url"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'url'); ?>>url</option>
						<option value="name"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'name'); ?>>name</option>
						<option value="target"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'target'); ?>>target</option>
						<option value="description"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'description'); ?>>description</option>
						<option value="owner"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'owner'); ?>>owner</option>
						<option value="rating"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'rating'); ?>>rating</option>
						<option value="rel"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'rel'); ?>>rel</option>
						<option value="notes"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'notes'); ?>>notes</option>
						<option value="rss"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'rss'); ?>>rss</option>
						<option value="length"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'length'); ?>>length</option>
						<option value="rand"
						<?php selected($feedreading_blogroll_settings['orderBy'], 'rand'); ?>>rand</option>
					</select><br />
					<input id="orderDirectionASC" name="orderDirection" type="radio"
						value="ASC"
						<?php checked('ASC', $feedreading_blogroll_settings['orderDirection']);?> /><label
						for="orderDirectionASC"><?php _e('ascending','feedreading_blogroll'); ?></label><br />
					<input id="orderDirectionDESC" name="orderDirection" type="radio"
						value="DESC"
						<?php checked('DESC', $feedreading_blogroll_settings['orderDirection']);?> /><label
						for="orderDirectionDESC"><?php _e('descending','feedreading_blogroll'); ?></label>
					</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="jsSorting"><?php _e('Enable JavaScript-sorting','feedreading_blogroll')?></label>
					</th>
					<td><input id="jsSorting" name="jsSorting" type="checkbox"
						value="jsSorting"
						<?php checked('jsSorting', $feedreading_blogroll_settings['jsSorting']);?> />
						<div class="popup_info" title="<?php _e('Click me for help','feedreading_blogroll'); ?>">
							<span class="infotext_trigger"><img	src="<?php echo feedreading_blogroll_get_resource_url('help.gif');?>" alt="" /></span>
							<p class="infotext">
								<?php _e('JavaScript-sorting allows sorting by &quot;latest update&quot;.','feedreading_blogroll')?>
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="jsSortingTimeout"><?php _e('JavaScript-sorting timeout','feedreading_blogroll')?></label>
					</th>
					<td>
					<select id="jsSortingTimeout" name="jsSortingTimeout"
						style="width: 300px;">
						<option value=""
						<?php selected($feedreading_blogroll_settings['jsSortingTimeout'], ''); ?>>20
							<?php _e('Seconds','feedreading_blogroll'); ?> (<?php _e('Default','feedreading_blogroll'); ?>)</option>
						<option value="30"
						<?php selected($feedreading_blogroll_settings['jsSortingTimeout'], '30'); ?>>30
							<?php _e('Seconds','feedreading_blogroll'); ?></option>
						<option value="40"
						<?php selected($feedreading_blogroll_settings['jsSortingTimeout'], '40'); ?>>40
							<?php _e('Seconds','feedreading_blogroll'); ?></option>
						<option value="50"
						<?php selected($feedreading_blogroll_settings['jsSortingTimeout'], '50'); ?>>50
							<?php _e('Seconds','feedreading_blogroll'); ?></option>
						<option value="60"
						<?php selected($feedreading_blogroll_settings['jsSortingTimeout'], '60'); ?>>60
							<?php _e('Seconds','feedreading_blogroll'); ?></option>
					</select>
						<div class="popup_info" title="<?php _e('Click me for help','feedreading_blogroll'); ?>">
							<span class="infotext_trigger"><img	src="<?php echo feedreading_blogroll_get_resource_url('help.gif');?>" alt="" /></span>
							<p class="infotext">
							<?php _e('Try to sort the blogroll with JavaScript for this amount of seconds.','feedreading_blogroll')?>
							</p>
							</div>
					</td>
				</tr>
			</table>
			<h3><?php _e('Display Options','feedreading_blogroll'); ?></h3>
			<table class="form-table"  summary="<?php _e('Options','feedreading_blogroll'); ?>">
				<tr>
					<th scope="row" valign="top"><?php _e('Style','feedreading_blogroll'); ?>
					</th>
					<td>
					<fieldset>
					<table summary="<?php _e('Options','feedreading_blogroll'); ?>">
						<tr>
							<td><input type="radio" id="minimal" name="displayStyle"
								value="minimal"
								<?php checked('minimal',$feedreading_blogroll_settings['displayStyle']); ?> />
							<label for="minimal"><?php _e('Minimal','feedreading_blogroll')?></label>
							</td>
							<td><p class="infoVisible"><?php _e('Displays the blogname and age of latest article.', 'feedreading_blogroll'); ?></p></td>
						</tr>
						<tr>
							<td><input type="radio" id="classic" name="displayStyle"
								value="classic"
								<?php checked('classic',$feedreading_blogroll_settings['displayStyle']); ?> />
							<label for="classic"><?php _e('Classic','feedreading_blogroll')?></label><br />
							</td>
							<td><p class="infoVisible"><?php _e('Displays the blogname and below that in one single line the age of the latest article and its title.', 'feedreading_blogroll'); ?></p></td>
						</tr>
						<tr>
							<td><input type="radio" id="blogger" name="displayStyle"
								value="blogger"
								<?php checked('blogger',$feedreading_blogroll_settings['displayStyle']); ?> />
							<label for="blogger"><?php _e('Blogger(TM)','feedreading_blogroll')?></label><br />
							</td>
							<td><p class="infoVisible"><?php _e('Displays the blogname, the age of the latest article and its title. Each element in its own line.', 'feedreading_blogroll'); ?></p></td>
						</tr>
						<tr>
							<td><input type="radio" id="banners" name="displayStyle"
								value="banners"
								<?php checked('banners',$feedreading_blogroll_settings['displayStyle']); ?> />
							<label for="banners"><?php _e('Banners','feedreading_blogroll')?></label>
							</td>
							<td><p class="infoVisible"><?php _e('The bookmark is displayed as a banner-link. Below the image the age of the latest article and its title are displayed.', 'feedreading_blogroll'); ?><p></td>
						</tr>
						<tr>
							<td><input type="radio" id="rolling" name="displayStyle"
								value="rolling"
								<?php checked('rolling',$feedreading_blogroll_settings['displayStyle']); ?> />
							<label for="rolling"><?php _e('Rolling','feedreading_blogroll')?></label>
							</td>
							<td><p class="infoVisible"><?php _e('A very compact, animated style. All bookmarks will show up, but only five of them at a time. Every few seconds, a new bookmark will fade in at the top, while another one will fade out at the bottom of the blogroll.', 'feedreading_blogroll'); ?><p></td>
						</tr>
					</table>
					</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="maxBookmarks"><?php _e('Max number of bookmarks per category','feedreading_blogroll')?></label>
					</th>
					<td>
						<input id="maxBookmarks" name="maxBookmarks" type="text" size="3" value="<?php echo $feedreading_blogroll_settings['maxBookmarks']; ?>" style="width: 300px;" title="<?php _e('Enter a positive integer without point or other special characters. Examples: 3, 5, 10, 15, ...','feedreading_blogroll')?>"/>
						<div class="popup_info" title="<?php _e('Click me for help','feedreading_blogroll'); ?>">
						<span class="infotext_trigger"><img	src="<?php echo feedreading_blogroll_get_resource_url('help.gif');?>" alt="" /></span>
						<p class="infotext">
						<?php _e('Enter the maximum number of bookmarks that should be visible in each category. Leave the field empty, if you want all bookmarks to be displayed.','feedreading_blogroll')?>
						<?php _e('If JavaScript-sorting is active, the limiting will be applied after the JavaScript-sorting timeout.', 'feedreading_blogroll'); ?>
						</p>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="customCSSDir"><?php _e('Custom location for stylesheets','feedreading_blogroll'); ?></label>
					</th>
					<td><input size="60" id="customCSSDir" name="customCSSDir" type="text"
						value="<?php echo $feedreading_blogroll_settings['customCSSDir']; ?>" /><?php echo ' /feedreading_blogroll.css'; ?>
					<?php
					$customfile = $feedreading_blogroll_settings['customCSSDir'].'/feedreading_blogroll.css';
					if (trim($feedreading_blogroll_settings['customCSSDir'])!= '') {
						// everything is okay
					} else {
						_e('No custom location was specified.', 'feedreading_blogroll');
						echo ' ';
						_e('Using default instead: ','feedreading_blogroll');
						?><br />
					<strong><?php
					echo WP_PLUGIN_URL.'/feed-reading-blogroll/css/feedreading_blogroll.css';
					?></strong><?php
					}
					?>
						<div class="popup_info" title="<?php _e('Click me for help','feedreading_blogroll'); ?>">
							<span class="infotext_trigger"><img	src="<?php echo feedreading_blogroll_get_resource_url('help.gif');?>" alt="" /></span>
							<p class="infotext">
								<?php _e('Examples: &quot;http://yourblog.com/wp-content&quot; or &quot;http://yourblog.com&quot; or &quot;http://yourblog.com/wp-content/css&quot;.','feedreading_blogroll'); ?>
								<br />
								<br />
								<?php _e('If you modify the file feedreading_blogroll.css, it is recommended to move it to another directory (e.g. /wp-content) to prevent it from getting lost during a plugin-update.','feedreading_blogroll') ?>
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="defaultIcon"><?php _e('Default icon','feedreading_blogroll');?></label>
					</th>
					<td><input size="90" id="defaultIcon" name="defaultIcon" type="text"
						value="<?php echo $feedreading_blogroll_settings['defaultIcon']; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="linkCssClass"><?php _e('CSS class for links','feedreading_blogroll')?></label>
					</th>
					<td><input size="90" id="linkCssClass" name="linkCssClass" type="text"
						value="<?php echo $feedreading_blogroll_settings['linkCssClass']; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="showLastUpdated"><?php _e('Show update-information','feedreading_blogroll')?></label>
					</th>
					<td><input id="showLastUpdated" name="showLastUpdated" type="checkbox"
						value="showLastUpdated"
						<?php checked('showLastUpdated', $feedreading_blogroll_settings['showLastUpdated']);?> />
						<?php _e('last change','feedreading_blogroll');?>: <?php printf(__("%d hrs ago","feedreading_blogroll"),2); ?>, <?php printf(__("%d days ago","feedreading_blogroll"),3); ?>, <?php _e("yesterday","feedreading_blogroll"); ?>, ...
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="showAuthor"><?php _e('Show name of author','feedreading_blogroll')?></label>
					</th>
					<td><input id="showAuthor" name="showAuthor" type="checkbox"
						value="showAuthor"
						<?php checked('showAuthor', $feedreading_blogroll_settings['showAuthor']);?> />
						<?php _e('This option requires the enabled option <em>Show update-information</em>.','feedreading_blogroll'); ?>
						<div class="popup_info" title="<?php _e('Click me for help','feedreading_blogroll'); ?>">
							<span class="infotext_trigger"><img	src="<?php echo feedreading_blogroll_get_resource_url('help.gif');?>" alt="" /></span>
							<p class="infotext">
							<?php _e('Some blogs do not provide the author&rsquo;s name in their feed. In those cases, the author&rsquo;s name cannot be shown in the blogroll.','feedreading_blogroll'); ?>
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="showLastPostTitle"><?php _e('Show title of latest post','feedreading_blogroll')?></label>
					</th>
					<td valign="top"><input id="showLastPostTitle"
						name="showLastPostTitle" type="checkbox" value="showLastPostTitle"
						<?php checked('showLastPostTitle', $feedreading_blogroll_settings['showLastPostTitle']);?> />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="bookmarkTooltip"><?php _e('Bookmark tooltip','feedreading_blogroll')?></label>
					</th>
					<td>
					<select id="bookmarkTooltip" name="bookmarkTooltip"
						style="width: 600px;">
						<option <?php selected($feedreading_blogroll_settings['bookmarkTooltip'], 'none'); ?> value="none"><?php _e('No tooltip','feedreading_blogroll');?></option>
						<option <?php selected($feedreading_blogroll_settings['bookmarkTooltip'], 'blogdescription'); ?> value="blogdescription"><?php _e('Show blog description from feed','feedreading_blogroll');?></option>
						<option <?php selected($feedreading_blogroll_settings['bookmarkTooltip'], 'latestposttitle'); selected($feedreading_blogroll_settings['bookmarkTooltip'], '');?> value="latestposttitle"><?php _e('Show title of latest post','feedreading_blogroll');?></option>
					</select>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="showIcon"><?php _e('Show icon','feedreading_blogroll')?></label>
					</th>
					<td><input id="showIcon" name="showIcon" type="checkbox"
						value="showIcon"
						<?php checked('showIcon', $feedreading_blogroll_settings['showIcon']);?> />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><label for="previewButton"><?php _e('Enable bookmark preview','feedreading_blogroll')?></label>
					</th>
					<td><input id="previewButton" name="previewButton" type="checkbox"
						value="previewButton"
						<?php checked('previewButton', $feedreading_blogroll_settings['previewButton']);?> />
					</td>
				</tr>
			</table>
			<p class="submit"><input name="submit" class="button-primary"
				value="<?php _e('Save Changes','feedreading_blogroll'); ?>" type="submit" /></p>
		</form>
	</div>
	<div id="feedreading_blogroll_js_tab">
		<h3><?php _e('JavaScript','feedreading_blogroll'); ?></h3>
		<form id="feedreading_rebuild_javascript" action="">
			<table class="form-table" summary="<?php _e('Options','feedreading_blogroll'); ?>">
				<tr>
					<th scope="row" valign="top"><label for="jsRebuildSubmit"><?php _e('JavaScript file','feedreading_blogroll'); ?></label></th>
					<td>
					<p class="feedreading_info"><?php _e('This file renders article-titles and date-information into your blogroll. It is rebuild automatically by the plugin each time your bookmarks or the options of this plugin change. You can rebuild it manually by clicking the button below.','feedreading_blogroll'); ?></p>
					<p class="feedreading_info"><code><?php echo WP_CONTENT_URL.FEEDREADING_JS_FILE; ?></code></p>
					<p class="feedreading_info"><?php _e('Last rebuild:','feedreading_blogroll'); ?> <span
						id="feedreading_ajax_response"><?php echo date(__('dS F, Y h:i:s a','feedreading_blogroll'), filemtime(WP_CONTENT_DIR.FEEDREADING_JS_FILE)); ?></span></p>
					<input type="hidden" name="rebuild_javascript" />
					<p class="submit"><input type="button" id="jsRebuildSubmit"
						name="jsRebuildSubmit" class="submit"
						onclick="feedreadingBlogrollRebuildJS('<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php', '<?php echo wp_create_nonce("plugin-feed-reading-ajax-action_option_panel") ?>')"
						value="<?php _e('Rebuild file','feedreading_blogroll'); ?>" title="<?php _e('Rebuild file','feedreading_blogroll'); ?>"
						<?php if (feedreading_is_not_jsfile_writable()) { ?>
						disabled="disabled" <?php } ?> /></p>
					</td>
				</tr>
			</table>
		</form>
	</div>

	<div id="feedreading_blogroll_preview_tab">
		<?php widget_feedreading_admin_preview(); ?>
	</div>

	<div id="feedreading_blogroll_news_tab">
		<h3 id="feedreading_blogroll_changelog"><?php _e('News','feedreading_blogroll');?></h3>
		<div>
			<p><?php _e('You are using version','feedreading_blogroll'); echo ' ' . FEEDREADING_VERSION. ' '.WPLANG; ?></p>
			<p id="feedreading_blogroll_changelog_body"></p>
		</div>
	</div>
</div>
			<?php
}

/**
 * render the preview of the link-data, that is necessary to make the plugin work properly
 */
function widget_feedreading_admin_preview(){
	$feedreading_blogroll_settings = get_option('feedreading_blogroll_settings');
	$linkCssClass = $feedreading_blogroll_settings['linkCssClass'];
	$showLastUpdated = strcmp($feedreading_blogroll_settings['showLastUpdated'], 'showLastUpdated');
	$showIcon = strcmp($feedreading_blogroll_settings['showIcon'], 'showIcon');
	$defaultIcon = $feedreading_blogroll_settings['defaultIcon'];
	$feedDiscovery = strcmp($feedreading_blogroll_settings['feedDiscovery'], 'feedDiscovery');
	?>
<h3 id="feedreading_blogroll_preview"><?php _e('URL-Check','feedreading_blogroll');?></h3>

<p class="feedreading_info"><?php _e('This section will tell you, if RSS-URLs or image-URLs are missing. Update-information in the blogroll can only be displayed if you have saved the necessary URLs.','feedreading_blogroll');?>
</p>

<table class="form-table"  summary="<?php _e('Options','feedreading_blogroll'); ?>">
<?php
// fetch bookmarks from wordpress
$bookmarks = get_bookmarks(feedreading_blogroll_bookmarkArgs(FALSE, ''));

foreach ((array)$bookmarks as $bookmark) {
	$name=$bookmark->link_name;
	$url=$bookmark->link_url;
	$urlFeed=$bookmark->link_rss;
	$image=$bookmark->link_image;
	?>
	<tr>
		<th scope="row" valign="top"><strong><?php echo $name;?></strong></th>
		<td>
			<p class="feedreading_info"><?php _e('Web Address');?>: <a href="<?php echo $url;?>"
				target="_blank"><?php echo $url;?></a></p>
			<p class="feedreading_info"><?php _e('RSS Address');?>: <?php
			if (trim($urlFeed) != ''){
				?><a href="<?php echo $urlFeed;?>" target="_blank"><?php echo $urlFeed;?></a><?php
			} else if($feedDiscovery == 0){
				?><span class="feedreading_okay"><?php echo _e('via Google Feed Discovery','feedreading_blogroll');?></span><?php
			} else {
				?><span class="feedreading_error"><?php _e('Warning','feedreading_blogroll'); echo '! '.__('No RSS address! Please provide a feed-url for this bookmark!','feedreading_blogroll').' ';
				?></span> <a
				href="<?php
				echo '/wp-admin/link.php?link_id='.$bookmark->link_id.'&amp;action=edit#linkadvanceddiv';?>"><?php _e('Edit Link','feedreading_blogroll');?></a><?php
			}
			?></p>
			<p class="feedreading_info"><?php _e('Image Address','feedreading_blogroll');?>: <?php
			if (trim($image) != ''){
				?><a href="<?php echo $image;?>" target="_blank"><?php echo $image;?></a><?php
			} else {
				?><span class="feedreading_warning"><?php _e('Warning','feedreading_blogroll'); echo '! '.__('No image address! If you have configured a default icon, it will be used.','feedreading_blogroll').' ';
				?></span> <a
				href="<?php
				echo '/wp-admin/link.php?link_id='.$bookmark->link_id.'&amp;action=edit#linkadvanceddiv';?>"><?php _e('Edit Link','feedreading_blogroll');?></a><?php
			}
			?></p>
		</td>
	</tr>
	<?php
}
?>
</table>
<?php
}

/**
 * Render the widget-body
 */
function widget_feedreading_blogroll($args) {
	extract($args);
	echo $before_widget;
	widget_feedreading_blogroll_content($before_title, $after_title);
	echo $after_widget;
}


/**
 * Render the widget-control
 */
function widget_feedreading_blogroll_control(){
	$desc = __('This widget adds the WP Social Blogroll to your sidebar.', 'feedreading_blogroll').' '.__('It can display multiple link categories. Only a single instance of this widget can be added to your sidebars.', 'feedreading_blogroll');
	?>
	<p><?php echo $desc; ?></p>
	<p><a href="options-general.php?page=feedreading_blogroll.php">Feed
Reading Blogroll <?php _e('Options','feedreading_blogroll'); ?></a></p>
	<?php
}


/**
 * Shortcut for non-widget-users. This is the actual template-tag.
 */
function feedreading_blogroll() {
	widget_feedreading_blogroll_content('<h2>','</h2>');
}


/**
 * transforms the plugin-options into an argument-string that works with the WordPress-function "get_bookmarks()"
 * @return String with arguments for the WordPress-function "get_bookmarks()"
 */
function feedreading_blogroll_bookmarkArgs($usedForGroupedBookmarks, $cat){
	$args='';
	$feedreading_blogroll_settings = get_option('feedreading_blogroll_settings');
	$feedreadingTitle = $feedreading_blogroll_settings['feedreadingTitle'];
	$linkCats = $feedreading_blogroll_settings['linkCats'];
	$orderBy = $feedreading_blogroll_settings['orderBy'];
	$orderDirection = $feedreading_blogroll_settings['orderDirection'];
	$jsSorting = strcmp($feedreading_blogroll_settings['jsSorting'], 'jsSorting');

	$catOption = '';
	$catDelim = '';
	$catIds='';

	if (!is_null($linkCats)) {
		foreach ((array)$linkCats as $l){
			$catOption = $catOption.$catDelim.$l;
			$eachTerm=get_term_by('name',$l,'link_category');
			$eachId = $eachTerm -> term_id;
			$catIds = $catIds.$catDelim.$eachId;
			$catDelim =',';

		}
	}
	$args='order='.$orderDirection.'&orderby='.$orderBy;
	if (!$usedForGroupedBookmarks){
		// use all linkcats
		$args='category='.$catIds.'&'.$args;
	} else {
		// use single linkcat
		$args='category_name='.$cat.'&'.$args;
	}
	return $args;
	}

/**
 * render content of widget for the sidebar
 */
function widget_feedreading_blogroll_content($before_title, $after_title){
	$feedreading_blogroll_settings = get_option('feedreading_blogroll_settings');
	$groupByLinkCats = strcmp($feedreading_blogroll_settings['groupByLinkCats'],'groupByLinkCats');
	$orderString = $feedreading_blogroll_settings['categoryOrderArray'];
	$orderArray = explode(",", $orderString);
	$linkCats = $feedreading_blogroll_settings['linkCats'];

// BUGFIX: Put the widget title at the top of the widget, if specified and set to show
if ($feedreading_blogroll_settings['feedreadingTitle'] !='' && $feedreading_blogroll_settings['showTitle'] == true) {
 //echo "<h2 class='widgettitle'>" . $feedreading_blogroll_settings['feedreadingTitle'] . "</h2>";
}

	// fetch bookmarks from wordpress
	if ( $groupByLinkCats==0){
		if (!is_null($linkCats)) {
			foreach($orderArray as $orderedCat){
				$eachTerm = get_term_by('id',substr($orderedCat,8),'link_category');
				$eachName = $eachTerm->name;
				if (in_array($eachName, $linkCats)){
					$bookmarks = get_bookmarks(feedreading_blogroll_bookmarkArgs(TRUE, $eachName));
                                        // BUGFIX: Check to see if there are actually any bookmarks in this category, before building the list
                                        if (count($bookmarks) != 0) {
					      widget_feedreading_blogroll_list($before_title, $after_title, $bookmarks, $eachName, $feedreading_blogroll_settings);
                                        }
				}
			}
		}
	} else {
		$bookmarks = get_bookmarks(feedreading_blogroll_bookmarkArgs(FALSE,''));
                // BUGFIX: Check to see if there are actually any bookmarks in this category, before building the list
                if (count($bookmarks) != 0) {
		      widget_feedreading_blogroll_list($before_title, $after_title, $bookmarks, '', $feedreading_blogroll_settings);
                }
	}
}

/**
 * render content of widget for the sidebar
 */
function widget_feedreading_blogroll_single($before_title, $after_title, $title, $cat, $widgetId){
	$feedreading_blogroll_settings = get_option('feedreading_blogroll_settings');
	$orderBy = $feedreading_blogroll_settings['orderBy'];
	$orderDirection = $feedreading_blogroll_settings['orderDirection'];
	$args='order='.$orderDirection.'&orderby='.$orderBy.'&category='.$cat;
	$bookmarks = get_bookmarks($args);
	?><?php
	widget_feedreading_blogroll_list($before_title, $after_title, $bookmarks, $title, $feedreading_blogroll_settings, $widgetId);
	?><?php

}

/**
 * render the blogroll. This is an HTML-unordered-list (<ul>...</ul>) with list-items (<li>...</li>)
 *
 * this function is called one time for every category by widget_feedreading_blogroll_content()
 *
 * @see widget_feedreading_blogroll_content()
 */
function widget_feedreading_blogroll_list($before_title, $after_title, $bookmarks, $title, $feedreading_blogroll_settings,$widgetId=''){
	$linkCssClass = $feedreading_blogroll_settings['linkCssClass'];
	$showLastUpdated = strcmp($feedreading_blogroll_settings['showLastUpdated'], 'showLastUpdated');
	$showIcon = strcmp($feedreading_blogroll_settings['showIcon'], 'showIcon');
	$showLastPostTitle = strcmp($feedreading_blogroll_settings['showLastPostTitle'], 'showLastPostTitle');
	$showPreviewButton = strcmp($feedreading_blogroll_settings['previewButton'], 'previewButton');
	$defaultIcon = $feedreading_blogroll_settings['defaultIcon'];
	$showTitle = strcmp($feedreading_blogroll_settings['showTitle'],'showTitle');
	$displayStyle = $feedreading_blogroll_settings['displayStyle'];
	$linkCats = $feedreading_blogroll_settings['linkCats'];
	$jsSorting = strcmp($feedreading_blogroll_settings['jsSorting'],'jsSorting');
	$groupByLinkCats = strcmp($feedreading_blogroll_settings['groupByLinkCats'],'groupByLinkCats');
	$feedDiscovery = strcmp($feedreading_blogroll_settings['feedDiscovery'], 'feedDiscovery');
	$faviconDiscovery = strcmp($feedreading_blogroll_settings['faviconDiscovery'], 'faviconDiscovery');

	if ($title =='') $title = $feedreading_blogroll_settings['feedreadingTitle'];
	if ($showTitle == 0 or $groupByLinkCats==0){
		echo $before_title;
		echo $title;
		echo $after_title;
	}
	$safeTitle='';

	if ( $groupByLinkCats==0 and $widgetId==''){
		$safeTerm= get_term_by('name',$title,'link_category');
		$safeTitle = $safeTerm->term_id;
	}
	?>
<ul id="<?php echo $widgetId;?>feedreading_blogroll_<?php echo $safeTitle; ?>" class="feedreading_blogroll_bookmarklist">
<?php
foreach ((array)$bookmarks as $bookmark){
	// print bookmark

	if ($displayStyle=='minimal'){
		widget_feedreading_blogroll_style_minimal($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId);
	} else if ($displayStyle=='classic'){
		widget_feedreading_blogroll_style_classic($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId);
	} else if ($displayStyle=='blogger') {
		widget_feedreading_blogroll_style_blogger($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId);
	} else if ($displayStyle=='banners') {
		widget_feedreading_blogroll_style_banners($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId);
	} else if ($displayStyle=='accordion') {
		widget_feedreading_blogroll_style_accordion($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId);
	} else if ($displayStyle=='rolling') {
		widget_feedreading_blogroll_style_rolling($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId);
	}
}
?>
</ul>
<?php if ($displayStyle=='rolling') {
	foreach ((array)$bookmarks as $bookmark){?>

<div style="display: none;" class="preview_wrap"
	id="<?php echo $widgetId; ?>feedreading_preview_wrap_<?php echo $bookmark->link_id;?>">
<p><small><?php _e('Close preview','feedreading_blogroll');?></small></p>
<div id="<?php echo $widgetId; ?>feedreading_preview_<?php echo $bookmark->link_id;?>">Loading...</div>
</div>

<?php }
} ?>

<?php
}

/**
 * The simple display-style with name of target-blog and update-information:
 *
 *  * Blog-name
 *    * updated 3 days ago
 */
function widget_feedreading_blogroll_style_minimal($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId){
	$name=$bookmark->link_name;
	$url=$bookmark->link_url;
	$urlFeed=$bookmark->link_rss;
	$image=$bookmark->link_image;
	$xfn=$bookmark->link_rel;
	?>
<li  class="feedreading_bookmark"
	id="<?php echo $widgetId; ?>feedreading_bookmark_<?php echo $bookmark->link_id;?>"><?php if (trim($image) != '' and $showIcon == 0){
		?><img src="<?php echo $image; ?>" class="icon16px" width="16"
	height="16" alt="Icon" />&nbsp;<?php
	} else if (trim($image) == '' and $faviconDiscovery == 0){
		?><img src="http://www.google.com/s2/favicons?domain=<?php echo feedreading_blogroll_getDomain($url); ?>" class="icon16px"
	width="16" height="16" alt="Icon" />&nbsp;<?php
	} else if (trim($image) == '' and $showIcon == 0 and trim($defaultIcon) != ''){
		?><img src="<?php echo trim($defaultIcon); ?>" class="icon16px"
	width="16" height="16" alt="Icon" />&nbsp;<?php
	}
	?><a  id="<?php echo $widgetId; ?>feedreading_anchor_<?php echo $bookmark->link_id;?>"
	href="<?php echo $url;?>" title="<?php echo $name; ?>"
	<?php if ($bookmark->link_target != '') {?>
	target="<?php echo $bookmark->link_target; ?>" <?php } ?>
	rel="external <?php echo $xfn;?>" class="feedreading_anchor <?php echo $linkCssClass; ?>"><?php echo $name; ?></a><?php
	// if bookmark has feed-info, get update-info and print it
	if ((trim($urlFeed)!='' or $feedDiscovery==0) and ($showLastUpdated == 0 or $showLastPostTitle == 0)) { ?>
<div id="<?php echo $widgetId; ?>feedreading_info_<?php echo $bookmark->link_id;?>"
	class="lastPublicationDate" style="display: block"><abbr
	title="<?php _e('last change','feedreading_blogroll');?>"
	id="<?php echo $widgetId; ?>feedreading_previewtoggle_<?php echo $bookmark->link_id;?>"></abbr><abbr
	style="display: none"
	id="<?php echo $widgetId; ?>frbl_last_posttitle_<?php echo $bookmark->link_id;?>"></abbr></div>
<div style="display: none;" class="preview_wrap"
	id="<?php echo $widgetId; ?>feedreading_preview_wrap_<?php echo $bookmark->link_id;?>">
<p><small><?php _e('Close preview','feedreading_blogroll');?></small></p>
<div id="<?php echo $widgetId; ?>feedreading_preview_<?php echo $bookmark->link_id;?>">Loading...</div>
</div>
	<?php
	}
	?></li>
	<?php
}

/**
 * The simple display-style with name of target-blog and update-information:
 *
 *  * Blog-name
 *    * updated 3 days ago
 *
 * Intended to be used with the rolling-style
 */
function widget_feedreading_blogroll_style_rolling($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId){
	$name=$bookmark->link_name;
	$url=$bookmark->link_url;
	$urlFeed=$bookmark->link_rss;
	$image=$bookmark->link_image;
	$xfn=$bookmark->link_rel;
	?>
<li  class="feedreading_bookmark"
	id="<?php echo $widgetId; ?>feedreading_bookmark_<?php echo $bookmark->link_id;?>">
	<?php if (trim($image) != '' and $showIcon == 0){
		?><img src="<?php echo $image; ?>" class="icon16px" width="16"
	height="16" alt="Icon" />&nbsp;<?php
	} else if (trim($image) == '' and $faviconDiscovery == 0){
		?><img src="http://www.google.com/s2/favicons?domain=<?php echo feedreading_blogroll_getDomain($url); ?>" class="icon16px"
	width="16" height="16" alt="Icon" />&nbsp;<?php
	} else if (trim($image) == '' and $showIcon == 0 and trim($defaultIcon) != ''){
		?><img src="<?php echo trim($defaultIcon); ?>" class="icon16px"
	width="16" height="16" alt="Icon" />&nbsp;<?php
	}
	?><a  id="<?php echo $widgetId; ?>feedreading_anchor_<?php echo $bookmark->link_id;?>"
	href="<?php echo $url;?>" title="<?php echo $name; ?>"
	<?php if ($bookmark->link_target != '') {?>
	target="<?php echo $bookmark->link_target; ?>" <?php } ?>
	rel="external <?php echo $xfn;?>" class="feedreading_anchor <?php echo $linkCssClass; ?>"><?php echo $name; ?></a><?php
	// if bookmark has feed-info, get update-info and print it
	if ((trim($urlFeed)!='' or $feedDiscovery==0) and ($showLastUpdated == 0 or $showLastPostTitle == 0)) { ?>
<div id="<?php echo $widgetId; ?>feedreading_info_<?php echo $bookmark->link_id;?>"
	class="lastPublicationDate" style="display: block"><span style="display:none;" class="previewtarget"><?php echo $widgetId; ?>feedreading_preview_wrap_<?php echo $bookmark->link_id;?></span><abbr
	title="<?php _e('last change','feedreading_blogroll');?>"
	id="<?php echo $widgetId; ?>feedreading_previewtoggle_<?php echo $bookmark->link_id;?>"></abbr><abbr
	style="display: none"
	id="<?php echo $widgetId; ?>frbl_last_posttitle_<?php echo $bookmark->link_id;?>"></abbr></div>
	<?php
	}
	?></li>
	<?php
}

/**
 * The classic display-style with name of target-blog, update-information and unlinked title of most recent post
 *
 *  * Blog-name
 *    * updated 3 days ago: Title of most recent article
 */
function widget_feedreading_blogroll_style_classic($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId){
	$name=$bookmark->link_name;
	$url=$bookmark->link_url;
	$urlFeed=$bookmark->link_rss;
	$image=$bookmark->link_image;
	$xfn=$bookmark->link_rel;
	?>
<li  class="feedreading_bookmark"
	id="<?php echo $widgetId; ?>feedreading_bookmark_<?php echo $bookmark->link_id;?>"><?php if (trim($image) != '' and $showIcon == 0){
		?><img src="<?php echo $image; ?>" class="icon16px" width="16"
	height="16" alt="Icon" />&nbsp;<?php
	} else if (trim($image) == '' and $faviconDiscovery == 0){
		?><img src="http://www.google.com/s2/favicons?domain=<?php echo feedreading_blogroll_getDomain($url); ?>" class="icon16px"
	width="16" height="16" alt="Icon" />&nbsp;<?php
	} else if (trim($image) == '' and $showIcon == 0 and trim($defaultIcon) != ''){
		?><img src="<?php echo trim($defaultIcon); ?>" class="icon16px"
	width="16" height="16" alt="Icon" />&nbsp;<?php
	}
	?><a  id="<?php echo $widgetId; ?>feedreading_anchor_<?php echo $bookmark->link_id;?>"
	href="<?php echo $url;?>" title="<?php echo $name; ?>"
	<?php if ($bookmark->link_target != '') {?>
	target="<?php echo $bookmark->link_target; ?>" <?php } ?>
	rel="external <?php echo $xfn;?>" class="feedreading_anchor <?php echo $linkCssClass; ?>"><?php echo $name; ?></a><?php
	// if bookmark has feed-info, get update-info and print it
	if ((trim($urlFeed)!='' or $feedDiscovery==0) and ($showLastUpdated == 0 or $showLastPostTitle == 0)) { ?>
<div id="<?php echo $widgetId; ?>feedreading_info_<?php echo $bookmark->link_id;?>"
	class="lastPublicationDate" style="display: block"><abbr
	title="<?php _e('last change','feedreading_blogroll');?>"
	id="<?php echo $widgetId; ?>feedreading_previewtoggle_<?php echo $bookmark->link_id;?>"></abbr>
<abbr style="" id="<?php echo $widgetId; ?>frbl_last_posttitle_<?php echo $bookmark->link_id;?>"></abbr></div>
<div style="display: none;" class="preview_wrap"
	id="<?php echo $widgetId; ?>feedreading_preview_wrap_<?php echo $bookmark->link_id;?>">
<p><small><?php _e('Close preview','feedreading_blogroll');?></small></p>
<div id="<?php echo $widgetId; ?>feedreading_preview_<?php echo $bookmark->link_id;?>">Loading...</div>
</div>
	<?php
	}
	?></li>
	<?php
}

/**
 * The Blogger(TM)-like display-style with name of target-blog, update-information and link to most recent article:
 *
 *  * Blog-name
 *    * Link to most recent article
 *    * updated 3 days ago
 */
function widget_feedreading_blogroll_style_blogger($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId){
	$name=$bookmark->link_name;
	$url=$bookmark->link_url;
	$urlFeed=$bookmark->link_rss;
	$image=$bookmark->link_image;
	$xfn=$bookmark->link_rel;
	?>
<li class="feedreading_bookmark"
	id="<?php echo $widgetId; ?>feedreading_bookmark_<?php echo $bookmark->link_id;?>"><?php if (trim($image) != '' and $showIcon == 0){
		?><img src="<?php echo $image; ?>" class="icon16px" width="16"
	height="16" alt="Icon" />&nbsp;<?php
	} else if (trim($image) == '' and $faviconDiscovery == 0){
		?><img src="http://www.google.com/s2/favicons?domain=<?php echo feedreading_blogroll_getDomain($url); ?>" class="icon16px"
	width="16" height="16" alt="Icon" />&nbsp;<?php
	} else if (trim($image) == '' and $showIcon == 0 and trim($defaultIcon) != ''){
		?><img src="<?php echo trim($defaultIcon); ?>" class="icon16px"
	width="16" height="16" alt="Icon" />&nbsp;<?php
	}
	?><a  id="<?php echo $widgetId; ?>feedreading_anchor_<?php echo $bookmark->link_id;?>"
	href="<?php echo $url;?>" title="<?php echo $name; ?>"
	<?php if ($bookmark->link_target != '') {?>
	target="<?php echo $bookmark->link_target; ?>" <?php } ?>
	rel="external <?php echo $xfn;?>" class="feedreading_anchor <?php echo $linkCssClass; ?>"><?php echo $name; ?></a><?php
	// if bookmark has feed-info, get update-info and print it
	if ((trim($urlFeed)!='' or $feedDiscovery==0) and ($showLastUpdated == 0 or $showLastPostTitle == 0)) { ?>
<div id="<?php echo $widgetId; ?>feedreading_info_<?php echo $bookmark->link_id;?>"
	class="lastPublicationDate" style="display: block">
<p class="frbl_last_posttitle"><a href="#"
	id="<?php echo $widgetId; ?>frbl_last_posttitle_<?php echo $bookmark->link_id;?>"
	<?php if ($bookmark->link_target != '') {?>
	target="<?php echo $bookmark->link_target; ?>" <?php } ?>></a></p>
<abbr title="<?php _e('last change','feedreading_blogroll');?>"
	id="<?php echo $widgetId; ?>feedreading_previewtoggle_<?php echo $bookmark->link_id;?>"></abbr></div>
<div style="display: none;" class="preview_wrap"
	id="<?php echo $widgetId; ?>feedreading_preview_wrap_<?php echo $bookmark->link_id;?>">
<p><small><?php _e('Close preview','feedreading_blogroll');?></small></p>
<div id="<?php echo $widgetId; ?>feedreading_preview_<?php echo $bookmark->link_id;?>">Loading...</div>
</div>
	<?php
	}
	?></li>
	<?php
}

/**
 * The banner display-style with banner of target-blog, update-information and link to most recent article:
 *
 *  * Blog-banner (Text-Link if no banner-url is supplied)
 *    * Link to most recent article
 *    * updated 3 days ago
 */
function widget_feedreading_blogroll_style_banners($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId){
	$name=$bookmark->link_name;
	$url=$bookmark->link_url;
	$urlFeed=$bookmark->link_rss;
	$image=$bookmark->link_image;
	$xfn=$bookmark->link_rel;
	?>
<li class="feedreading_bookmark"
	id="<?php echo $widgetId; ?>feedreading_bookmark_<?php echo $bookmark->link_id;?>"><a
	 id="<?php echo $widgetId; ?>feedreading_anchor_<?php echo $bookmark->link_id;?>"
	href="<?php echo $url;?>" title="<?php echo $name; ?>"
	<?php if ($bookmark->link_target != '') {?>
	target="<?php echo $bookmark->link_target; ?>" <?php } ?>
	rel="external <?php echo $xfn;?>" class="feedreading_anchor <?php echo $linkCssClass; ?>"><?php if (trim($image) != '' and $showIcon == 0){
		?><img src="<?php echo $image; ?>" alt="<?php echo $name; ?>" /><?php
	} else if (trim($image) == '' and $showIcon == 0 and trim($defaultIcon) != ''){
		?><?php echo $name; ?><?php
	}
	?></a><?php
	// if bookmark has feed-info, get update-info and print it
	if ((trim($urlFeed)!='' or $feedDiscovery==0) and ($showLastUpdated == 0 or $showLastPostTitle == 0)) { ?>
<div id="<?php echo $widgetId; ?>feedreading_info_<?php echo $bookmark->link_id;?>"
	class="lastPublicationDate" style="display: block">
<p class="frbl_last_posttitle"><a href="#"
	id="<?php echo $widgetId; ?>frbl_last_posttitle_<?php echo $bookmark->link_id;?>"
	<?php if ($bookmark->link_target != '') {?>
	target="<?php echo $bookmark->link_target; ?>" <?php } ?>></a></p>
<abbr title="<?php _e('last change','feedreading_blogroll');?>"
	id="<?php echo $widgetId; ?>feedreading_previewtoggle_<?php echo $bookmark->link_id;?>"></abbr></div>
<div style="display: none;" class="preview_wrap"
	id="<?php echo $widgetId; ?>feedreading_preview_wrap_<?php echo $bookmark->link_id;?>">
<p><small><?php _e('Close preview','feedreading_blogroll');?></small></p>
<div id="<?php echo $widgetId; ?>feedreading_preview_<?php echo $bookmark->link_id;?>">Loading...</div>
</div>
	<?php
	}
	?></li>
	<?php
}

/**
 * The accordion-like display-style with name of target-blog, update-information and link to most recent article:
 *
 *  * Blog-name
 *    * Link to most recent article
 *    * updated 3 days ago
 */
function widget_feedreading_blogroll_style_accordion($bookmark,$linkCssClass,$showLastUpdated,$showIcon,$showLastPostTitle,$showPreviewButton,$defaultIcon,$feedDiscovery,$faviconDiscovery,$widgetId){
	$name=$bookmark->link_name;
	$url=$bookmark->link_url;
	$urlFeed=$bookmark->link_rss;
	$image=$bookmark->link_image;
	$xfn=$bookmark->link_rel;
	?>
<li class="<?php echo $widgetId; ?>feedreading_bookmark"
	id="<?php echo $widgetId; ?>feedreading_bookmark_<?php echo $bookmark->link_id;?>"><a class="feedreading_bookmark_header" href="#"><?php echo $name; ?></a><div><a id="<?php echo $widgetId; ?>feedreading_anchor_<?php echo $bookmark->link_id;?>"
	href="<?php echo $url;?>" title="<?php echo $name; ?>"
	<?php if ($bookmark->link_target != '') {?> target="<?php echo $bookmark->link_target; ?>" <?php } ?>
	rel="external <?php echo $xfn;?>" class="feedreading_anchor <?php echo $linkCssClass; ?>"><?php if (trim($image) != '' and $showIcon == 0){
		?><img src="<?php echo $image; ?>" class="icon16px" width="16"
	height="16" alt="Icon" />&nbsp;<?php
	} else if (trim($image) == '' and $faviconDiscovery == 0){
		?><img src="http://www.google.com/s2/favicons?domain=<?php echo feedreading_blogroll_getDomain($url); ?>" class="icon16px"
	width="16" height="16" alt="Icon" />&nbsp;<?php
	} else if (trim($image) == '' and $showIcon == 0 and trim($defaultIcon) != ''){
		?><img src="<?php echo trim($defaultIcon); ?>" class="icon16px"
	width="16" height="16" alt="Icon" />&nbsp;<?php
	}
	?><?php echo $name; ?></a><?php
	// if bookmark has feed-info, get update-info and print it
	if ((trim($urlFeed)!='' or $feedDiscovery==0) and ($showLastUpdated == 0 or $showLastPostTitle == 0)) { ?>
<div id="<?php echo $widgetId; ?>feedreading_info_<?php echo $bookmark->link_id;?>"
	class="lastPublicationDate" style="display: block">
<p class="frbl_last_posttitle"><a href="#"
	id="<?php echo $widgetId; ?>frbl_last_posttitle_<?php echo $bookmark->link_id;?>"
	<?php if ($bookmark->link_target != '') {?>
	target="<?php echo $bookmark->link_target; ?>" <?php } ?>></a></p>
<abbr title="<?php _e('last change','feedreading_blogroll');?>"
	id="<?php echo $widgetId; ?>feedreading_previewtoggle_<?php echo $bookmark->link_id;?>"></abbr></div>
<div style="display: none;" class="preview_wrap"
	id="<?php echo $widgetId; ?>feedreading_preview_wrap_<?php echo $bookmark->link_id;?>">
<p><small><?php _e('Close preview','feedreading_blogroll');?></small></p>
<div id="<?php echo $widgetId; ?>feedreading_preview_<?php echo $bookmark->link_id;?>">Loading...</div>
</div>
	<?php
	}
	?></div></li>
	<?php
}




/**
 * Endpoint for ajax-request that rebuilds the javascript-file
 *
 */
function feedreading_blogroll_generate_javascript_ajax(){
	if (function_exists('check_ajax_referer')) {
		check_ajax_referer('plugin-feed-reading-ajax-action_option_panel');
	}
	feedreading_blogroll_generate_javascript();
	$fileTime = date(__('dS F, Y h:i:s a','feedreading_blogroll'), filemtime(WP_CONTENT_DIR.FEEDREADING_JS_FILE));
	die($fileTime);
}

/**
 * Generate the .js-file and store it in WP_CONTENT_DIR.
 *
 */
function feedreading_blogroll_generate_javascript($isHook = TRUE){
	$feedreading_blogroll_settings = get_option('feedreading_blogroll_settings');
	$googleAPIKey = $feedreading_blogroll_settings['googleAPIKey'];
	$bookmarks = get_bookmarks(feedreading_blogroll_bookmarkArgs(FALSE,''));
	$showLastUpdated = strcmp($feedreading_blogroll_settings['showLastUpdated'], 'showLastUpdated');
	$showLastPostTitle = strcmp($feedreading_blogroll_settings['showLastPostTitle'], 'showLastPostTitle');
	$showAuthor = strcmp($feedreading_blogroll_settings['showAuthor'], 'showAuthor');
	$showPreviewButton = strcmp($feedreading_blogroll_settings['previewButton'], 'previewButton');
	$feedDiscovery = strcmp($feedreading_blogroll_settings['feedDiscovery'], 'feedDiscovery');
	$jsSorting = strcmp($feedreading_blogroll_settings['jsSorting'], 'jsSorting');
	$timeout = $feedreading_blogroll_settings['jsSortingTimeout'];
	$title = $feedreading_blogroll_settings['feedreadingTitle'];
	$maxBookmarks = intval($feedreading_blogroll_settings['maxBookmarks']);
	$sidebars_widgets=wp_get_sidebars_widgets();
	$displayStyle = $feedreading_blogroll_settings['displayStyle'];
	$option=get_option('widget_feedreadingblogroll');
	$isMultiWidgetActive =  feedreading_blogroll_get_multiwidget_id();
	$bookmarkTooltip = $feedreading_blogroll_settings['bookmarkTooltip'];
	$groupByLinkCats = strcmp($feedreading_blogroll_settings['groupByLinkCats'],'groupByLinkCats');
	$groupByLinkCats =  (bool) $groupByLinkCats==0;
	if ($isMultiWidgetActive) {
		$linkCats = $feedreading_blogroll_settings['linkCats'];
	} else {
		$linkCats = null;
	}

	if ($timeout=="") {
		$timeout="20000";
	} else {
		$timeout=$timeout."000";
	}
	ob_start();
	?>
<script type="text/javascript">/* <![CDATA[ */

jQuery(function($) {
	if ($('.feedreading_blogroll_bookmarklist').length > 0){
		$.getScript("http://www.google.com/jsapi?key=<?php echo $googleAPIKey; ?>", function(){
			google.load("feeds", "1",{"callback" : initializeBR});
		});
	}

	//initializeBR();

			/**
			 * printf() for Javascript
			 */
			function sprintf() {
                if( sprintf.arguments.length < 2 ) {
                    return;
                }
                var data = sprintf.arguments[ 0 ];
                for( var k=1; k<sprintf.arguments.length; ++k ) {
                    switch( typeof( sprintf.arguments[ k ] ) ){
                        case 'string':
                            data = data.replace( /%s/, sprintf.arguments[ k ] );
                            break;
                        case 'number':
                            data = data.replace( /%d/, sprintf.arguments[ k ] );
                            break;
                        case 'boolean':
                            data = data.replace( /%b/, sprintf.arguments[ k ] ? 'true' : 'false' );
                            break;
                            default:
                           /// function | object | undefined
                        break;
                    }
                }
                return(data);
            }
            if( !String.sprintf ) {
            	String.sprintf = sprintf;
            }


            /**
             * generate text showing the age of the bookmark's latest post
             */
            function getAge(days, hours){
                if (days > 1) {
                    return String.sprintf("<?php _e('%d days ago','feedreading_blogroll') ?>", days);
                } else if (days==1){
                	return "<?php _e('yesterday','feedreading_blogroll') ?> ";
                } else if (days< 1 && hours > 1) {
                	return String.sprintf("<?php _e('%d hrs ago','feedreading_blogroll') ?>", hours);
                } else if (days< 1 && hours == 1) {
                	return "<?php _e('in the last hour','feedreading_blogroll') ?>";
                } else if (days < 1 && hours < 1) {
                	return "<?php _e('just recently','feedreading_blogroll') ?>";
                }
            }
            /* add age and other info to bookmark*/
            function addAge(feed, bookmark, anchor, divID, previewtoggle, last_posttitle){
                var $li =$(bookmark),
                 $a =$(anchor),
                 $toggle =$(previewtoggle),
                 $title =$(last_posttitle),
                 now = (new Date()).getTime(),
                 then = (new Date()).getTime(),
                 ageInDays,
                 ageInHours,
                 randomAge,
                 ageMsg=[],
                 $snippet,
                 entry<?php if ($showAuthor ==0 ){  ?>, author=""<?php } ?>;
                entry = feed.entries[0]
                /*,entry1= feed.entries[1] */;
                try {
                    then = new Date(entry.publishedDate).getTime();
                } catch (dateException) {
                    // do noting
                }
                <?php if ($showAuthor ==0 ){  ?>
                if(!(entry.author===null || entry.author == "")){
                    author = entry.author;
                } else {
                    if(!(feed.author===null || feed.author == "")){
                    	author = feed.author;
                    }
                }
                <?php } ?>

                ageInDays =  Math.floor((now-then)/(1000*60*60*24)),
                ageInHours = Math.floor((now-then)%(1000*60*60*24)/3600000);

                try {
                    <?php if ('latestposttitle' == $bookmarkTooltip or '' == $bookmarkTooltip) {?>
                     $entryTitle =$("<p></p>").html(entry.title);
                    <?php } else if('blogdescription' == $bookmarkTooltip) { ?>
                    $entryTitle=$("<p></p>").html(feed.description);
                    <?php } else { ?>
                    $entryTitle=$("<p></p>");
                    <?php } ?>
                    $a.attr({title: $entryTitle.html()});
                } catch (titleException) {
                    $a.attr({title: ""});
                }
				if (!isNaN(then)){
					// insert age into list-item to allow sorting by age of post
                    $li.attr({age:then});
				} else {
		            /* add "very old" age to bookmarks with no feed-url */
					randomAge = Math.floor(Math.random()*1000001);
					$li.attr({age:randomAge});
				}

                <?php if ($showLastUpdated==0) { ?>
                if (!(isNaN(ageInDays) || isNaN(ageInHours))){
                    ageMsg.push(getAge(ageInDays, ageInHours));
					<?php if ($displayStyle=='classic'){ ?>
					ageMsg.push(":");
					<?php } ?>
                    <?php if ($showAuthor ==0 ){  ?>
                    if (author!= "" ) {
	                    ageMsg.push(" <?php _e('by','feedreading_blogroll') ?> ");
	                    ageMsg.push(author);
                    }
                    <?php } ?>
                    $toggle.html(ageMsg.join('')).attr({feedurl:feed.link});
                }
                <?php } ?>
                <?php if ($showLastPostTitle==0) { ?>
			$title.html(entry.title);
			$title.attr( {
				href  : entry.link,
				title : $("<p></p>").html(entry.contentSnippet).html(),
				rel   : "external",
				rev   : "bookmark"
			});

			/*$title.parent().append($("<a></a>").attr({
					href : entry1.link,
					title: $("<p></p>").html(entry1.contentSnippet).html(),
					rel   : "external",
					rev   : "bookmark"
			}).html(entry1.title));*/
                <?php } ?>
                return false;
            }
            /* add "very old" age to bookmarks with no feed-url */
            function addZeroAge(zeroAgeBookmark){
                var $zali =$(zeroAgeBookmark),
                randomAge = Math.floor(Math.random()*10000001);
                if ($zali !== null ) {
                    $zali.attr({age:randomAge});
                }
                return false;
            }

            /* add jQuery-oberserver to enable mouse-clicks*/
            function addFeedControl(preview, feed, name){
                    var feedControl = new google.feeds.FeedControl();
                    feedControl.addFeed(feed, name);
                    feedControl.draw($(preview).get(0));
                    return false;
            }

			/*
			 *
			 */
            function feedreading_limit_display(){
            	var
                <?php
				$widget_keys= feedreading_blogroll_get_widget_ids();
	        	if ( $groupByLinkCats){
	        		if (!is_null($linkCats)) {
	        			$varSeperator="";
	        			foreach ((array)$linkCats as $linkCat){
							$safeTerm = get_term_by('name',$linkCat,'link_category');
	        				$safeTitle = $safeTerm->term_id;
	        				echo $varSeperator;
	        				?> liArray<?php echo $safeTitle; ?> = $("#feedreading_blogroll_<?php echo $safeTitle; ?> > li") <?php
	        				$varSeperator =",";
	        			}
	    	        }
    	        } else {
    	        	?> liArray = $("#feedreading_blogroll_ > li")<?php
	        		$varSeperator =",";
				}
				foreach((array) $widget_keys as $key){
	        		echo $varSeperator;
					?> liArraySingle_<?php echo $key; ?> = $("#single-<?php echo $key; ?>_feedreading_blogroll_ > li") <?php
					$varSeperator =",";
				}
	    	    echo ";";

	        	if ( $groupByLinkCats){
	        		if (!is_null($linkCats)) {
	        			foreach ((array)$linkCats as $linkCat){
							$safeTerm = get_term_by('name',$linkCat,'link_category');
	        				$safeTitle = $safeTerm->term_id; ?>
	        				for (var i=0; i < liArray<?php echo $safeTitle; ?>.length; i++) {
	        					if(i > <?php echo $maxBookmarks -1; ?>){
	                				$(liArray<?php echo $safeTitle; ?>[i]).css("display","none");
	        					}
	        				}
	        				<?php
	         			}
	        		}
	        	} else { ?>
					for (var i=0; i < liArray.length; i++) {
						if(i > <?php echo $maxBookmarks -1; ?>){
	        				$(liArray[i]).css("display","none");
						}
					} <?php
		        }
				foreach((array) $widget_keys as $key){?>
					for (var i=0; i < liArraySingle_<?php echo $key; ?>.length; i++) {
						if(i > <?php echo $maxBookmarks -1; ?>){
	        				$(liArraySingle_<?php echo $key; ?>[i]).css("display","none");
						}
					} <?php
				} ?>
				return false;
        	}

            /*
             * Sort the blogroll with tsort()
             */
            function feedreading_automatic_sort() { <?php
            	$varSeperator="";
            	if ($groupByLinkCats){
            		if (!is_null($linkCats) or !is_null((array) $widget_keys)) { ?>
                		var $allCompleted ,
					<?php
           			foreach ((array)$linkCats as $linkCat){
					$safeTerm = get_term_by('name',$linkCat,'link_category');
            				$safeTitle = $safeTerm->term_id;
					echo $varSeperator;
					?>
            				$hasCompleteAge<?php echo $safeTitle; ?>=true, isComplete<?php echo $safeTitle; ?>=false, $liSortArray<?php echo $safeTitle; ?> = $("#feedreading_blogroll_<?php echo $safeTitle; ?>>li")
          				<?php
					$varSeperator=" , ";
             			}
				foreach((array) $widget_keys as $key){
					echo $varSeperator; ?>
					$hasCompleteAgeSingle<?php echo $key; ?>=true, isCompleteSingle<?php echo $key; ?>=false, $liSortSingleArray<?php echo $key; ?>= $("#single-<?php echo $key;?>_feedreading_blogroll_ > li")
					<?php
					$varSeperator=" , ";
				}

				echo ";";
           			foreach ((array)$linkCats as $linkCat){
					$safeTerm = get_term_by('name',$linkCat,'link_category');
            				$safeTitle = $safeTerm->term_id; ?>
            		for (var i=0; i < $liSortArray<?php echo $safeTitle; ?>.length; i++) {
						var $age<?php echo $safeTitle; ?> = $($liSortArray<?php echo $safeTitle; ?>[i]).attr("age");
						if ($age<?php echo $safeTitle; ?> === null || $age<?php echo $safeTitle; ?> == "" || isNaN($age<?php echo $safeTitle; ?>)) {
							$hasCompleteAge<?php echo $safeTitle; ?> = false;
						}
            		}
					if ($hasCompleteAge<?php echo $safeTitle; ?> && !isComplete<?php echo $safeTitle; ?>) {
						try {
							//$("#feedreading_blogroll_<?php echo $safeTitle; ?>>li").tsort({order:"desc",attr:"age"});
							$("#feedreading_blogroll_<?php echo $safeTitle; ?>>li").frbrsort(sortAlpha).appendTo("#feedreading_blogroll_<?php echo $safeTitle; ?>");
							isComplete<?php echo $safeTitle; ?>=true;
						}catch (e){
							//do nothing
						}
					}
          				<?php
             			}
				foreach((array) $widget_keys as $key){?>
	        		for (var i=0; i < $liSortSingleArray<?php echo $key; ?>.length; i++) {
						var $age<?php echo $key; ?> = $($liSortSingleArray<?php echo $key; ?>[i]).attr("age");
						if ($age<?php echo $key; ?> === null || $age<?php echo $key; ?> == "" || isNaN($age<?php echo $key; ?>)) {
							$hasCompleteAgeSingle<?php echo $key; ?> = false;
						}
	        		}


					if ($hasCompleteAgeSingle<?php echo $key; ?> && !isCompleteSingle<?php echo $key; ?>) {
						try{
							//$("#single-<?php echo $key;?>_feedreading_blogroll_ > li").tsort({order:"desc",attr:"age"});
							$("#single-<?php echo $key;?>_feedreading_blogroll_ > li").frbrsort(sortAlpha).appendTo("#single-<?php echo $key;?>_feedreading_blogroll_");
							isCompleteSingle<?php echo $key; ?>=true;
						} catch (e){
							// do nothing
						}
					}
					<?php
				}


				?>
				$allCompleted =
				<?php
				$varSeperator ="";
            			foreach ((array)$linkCats as $linkCat){
					$safeTerm = get_term_by('name',$linkCat,'link_category');
            				$safeTitle = $safeTerm->term_id; ?>
					<?php echo $varSeperator; ?>
					isComplete<?php echo $safeTitle; ?>
          				<?php
					$varSeperator=" && ";
             			}
				foreach((array) $widget_keys as $key){
					echo $varSeperator; ?>
					isCompleteSingle<?php echo $key; ?>
					<?php
					$varSeperator=" && ";
				}
				echo ';';
				?>

				if ($allCompleted){
				clearInterval(myInterval);
				<?php if($maxBookmarks > 0) { ?>
				feedreading_limit_display();
				<?php } ?>
				<?php if ($displayStyle =='rolling') { ?> feedreading_rolling(); <?php } ?>
				}
				<?php
            		}

            	} else {?>
					var $allCompleted=false, $hasCompleteAge=true, isComplete=false, $sortArray=$("#feedreading_blogroll_ >li")
					<?php
					$varSeperator = ",";
					foreach((array) $widget_keys as $key){
						echo $varSeperator; ?>
						$hasCompleteAgeSingle<?php echo $key; ?>=true, isCompleteSingle<?php echo $key; ?>=false, $liSortSingleArray<?php echo $key; ?>= $("#single-<?php echo $key;?>_feedreading_blogroll_ > li")
						<?php
					}?>
					;
	        		for (var i=0; i < $sortArray.length; i++) {
						var $age_ = $( $sortArray[i]).attr("age");
						if ($age_ === null || $age_ == "" || isNaN($age_)) {
							$hasCompleteAge = false;
						}
	        		}
	        		if($sortArray.length==0){
	        			$hasCompleteAge = true;
	        		}
					<?php

					foreach((array) $widget_keys as $key){
						?>
		        		for (var i=0; i < $liSortSingleArray<?php echo $key; ?>.length; i++) {
							var $age<?php echo $key; ?> = $($liSortSingleArray<?php echo $key; ?>[i]).attr("age");
							if ($age<?php echo $key; ?> === null || $age<?php echo $key; ?> == "" || isNaN($age<?php echo $key; ?>)) {
								$hasCompleteAgeSingle<?php echo $key; ?> = false;
							}
		        		}
						if ($hasCompleteAgeSingle<?php echo $key; ?> && !isCompleteSingle<?php echo $key; ?>) {
							try{
							//$("#single-<?php echo $key;?>_feedreading_blogroll_ > li").tsort({order:"desc",attr:"age"});
							$("#single-<?php echo $key;?>_feedreading_blogroll_ > li").frbrsort(sortAlpha).appendTo("#single-<?php echo $key;?>_feedreading_blogroll_");
							isCompleteSingle<?php echo $key; ?>=true;
							} catch (e){
							// do nothing
							}
						}
						<?php
					}
						?>
						if ($hasCompleteAge && !isComplete){
							try{
							//$("#feedreading_blogroll_ > li").tsort({order:"desc",attr:"age"});
							$("#feedreading_blogroll_ > li").frbrsort(sortAlpha).appendTo("#feedreading_blogroll_ ");
							isComplete=true;
							} catch (e){
							// do nothing
							}
						}


					$allCompleted = $hasCompleteAge
					<?php
						$varSeperator = " && ";
						foreach((array) $widget_keys as $key){
							echo $varSeperator; ?>
							isCompleteSingle<?php echo $key; ?>
							<?php
						}
						echo ";";?>

					if ($allCompleted) {
						clearInterval(myInterval);
						<?php if($maxBookmarks > 0) { ?>
							feedreading_limit_display();
						<?php } ?>
						<?php if ($displayStyle=='rolling'){ ?>feedreading_rolling();<?php }  ?>
					}
					<?php
				} ?>
				return false;
            }

	function feedreading_rolling(){
			<?php $separator="";?>
			var
			<?php if(! $groupByLinkCats){ ?>
				$blogroll_all= $('#feedreading_blogroll_'), $blogroll_all_size= $blogroll_all.find('li').size(), $blogroll_all_limit = ($blogroll_all_size>5)? (5): ($blogroll_all_size-1)
			<?php
					if(sizeof($widget_keys)>0){
						$seperator= " ,";
					}
				} else {
					foreach ((array)$linkCats as $linkCat){
						$safeTerm = get_term_by('name',$linkCat,'link_category');
						$safeTitle = $safeTerm->term_id;
						echo $seperator;
						?>
						$blogroll_<?php echo $safeTitle; ?> = $('#feedreading_blogroll_<?php echo $safeTitle; ?>'), $blogroll_<?php echo $safeTitle; ?>_size=$blogroll_<?php echo $safeTitle; ?>.find('li').size() , $blogroll_<?php echo $safeTitle; ?>_limit = ($blogroll_<?php echo $safeTitle; ?>_size>5)? (5): ($blogroll_<?php echo $safeTitle; ?>_size-1)
						<?php
						$seperator=",";
						}
				}

			foreach((array) $widget_keys as $key){
			echo $seperator;
			?>
			$single_blogroll_<?php echo $key; ?> = $('#single-<?php echo $key;?>_feedreading_blogroll_'), $single_blogroll_<?php echo $key; ?>_size=$single_blogroll_<?php echo $key; ?>.find('li').size() , $single_blogroll_<?php echo $key; ?>_limit = ($single_blogroll_<?php echo $key; ?>_size>5)? (5): ($single_blogroll_<?php echo $key; ?>_size-1)
			<?php $seperator=",";
			} ?>
			;

			<?php if(! $groupByLinkCats){ ?>
			$('#feedreading_blogroll_ > li').css("display","none");
			$blogroll_all.feedReadingBlogrollSpy($blogroll_all_limit,4000);
			<?php } ?>

			<?php
			if($groupByLinkCats){
				foreach ((array)$linkCats as $linkCat){
					$safeTerm = get_term_by('name',$linkCat,'link_category');
					$safeTitle = $safeTerm->term_id; ?>
				if ($blogroll_<?php echo $safeTitle; ?>_size > 1) {
					$("#feedreading_blogroll_<?php echo $safeTitle; ?> > li").css("display","none");
					$blogroll_<?php echo $safeTitle; ?>.feedReadingBlogrollSpy($blogroll_<?php echo $safeTitle; ?>_limit,4000);
				}
				<?php
				}
			}
			foreach((array) $widget_keys as $key){
			?>
			if ($single_blogroll_<?php echo $key; ?>_size > 1) {
				$("#single-<?php echo $key;?>_feedreading_blogroll_ > li").css("display","none");
				$single_blogroll_<?php echo $key; ?>.feedReadingBlogrollSpy($single_blogroll_<?php echo $key; ?>_limit,4000);
			}

			<?php } ?>
		return false;
	}

            /*
             * add observer to blogroll()
             */
	function feedreading_category_observer() {
	    <?php	if ($showPreviewButton==0){?>
		$("#widget_feedreading_blogroll, .widget_feedreading_blogroll").bind("change click keypress", function(event){
			var $eventTarget = $(event.target), $previewtarget = "#"+$eventTarget.parent().children(".previewtarget").text();
			<?php if ($displayStyle != "rolling") { ?>
			if ($eventTarget.is('abbr')){
				$eventTarget.parent().parent().children(".preview_wrap").toggle("slow");
			}
			if ($eventTarget.is('small')){
				$eventTarget.parent().parent().parent().children(".preview_wrap").toggle("slow");

			}
			<?php } ?>

			<?php if ($displayStyle == "rolling") { ?>
			if ($eventTarget.is('abbr')){
				$($previewtarget).toggle("slow");
			}
			if ($eventTarget.is('small')){
				$eventTarget.parent().parent().toggle("slow");
			}
			<?php } ?>
		});
		<?php }  ?>
		$("ul.feedreading_blogroll_bookmarklist").bind("mouseenter",function(event){
			var $eventTarget =$(event.target);
			$eventTarget.parents(".feedreading_blogroll_bookmarklist").addClass("mouseover");
		});
		$("ul.feedreading_blogroll_bookmarklist").bind("mouseleave",function(event){
			var $eventTarget =$(event.target);
			$eventTarget.parents(".feedreading_blogroll_bookmarklist").removeClass("mouseover");
		});
		return false;
	}
			<?php if ($jsSorting == 0 ) { ?>
		            /* call sort-function every half second */
		            var myInterval = window.setInterval(function (){feedreading_automatic_sort(); },1000);
			/* stop calling sort-function after n seconds */
			window.setTimeout(function (a,b){
				clearInterval(myInterval);
			}, <?php echo $timeout; ?>);
			<?php } ?>

      function initializeBR() {
			<?php
                	$printVars = true;
                	$printSemicolon=false;
                	$komma ='';
			foreach ((array)$bookmarks as $bookmark){
				$urlFeed=$bookmark->link_rss;
				if (trim($urlFeed) != "" ){
					if($printVars){?>
						var <?php
						$printVars=false;
						$printSemicolon=true;
					}
					echo $komma; ?>
					feed<?php echo $bookmark->link_id;?> = new google.feeds.Feed("<?php echo $urlFeed; ?>")
					<?php $komma=',';
				}
			}
			foreach((array) $widget_keys as $key){
				$eachCat = $option[$key]["cat"];
				$widgetbookmarks = get_bookmarks('category='.$eachCat);
				foreach ((array) $widgetbookmarks as $bookmark) {
					$urlFeed=$bookmark->link_rss;
					if(trim($bookmark->link_rss)!='') {
						if($printVars){ ?>
							var <?php
							$printVars=false;
							$printSemicolon=true;
						}
						echo $komma;
						?>feed_single<?php echo $key.$bookmark->link_id;?>  = new google.feeds.Feed("<?php echo $urlFeed; ?>")
						<?php $komma=',';
					}  // end if
				}
			}

			if($printSemicolon){
				?>;<?php
			}


                    foreach ((array)$bookmarks as $bookmark){
                        $name=$bookmark->link_name;
                   		$urlFeed=$bookmark->link_rss;

                        if(trim($bookmark->link_rss)!='') {?>
                        if($("#feedreading_bookmark_<?php echo $bookmark->link_id;?>").length > 0){
                                      feed<?php echo $bookmark->link_id;?>.load(function(result_<?php echo $bookmark->link_id;?>){
                                      if (!result_<?php echo $bookmark->link_id;?>.error) {
                                      addAge(result_<?php echo $bookmark->link_id;?>.feed, "#feedreading_bookmark_<?php echo $bookmark->link_id;?>", "#feedreading_anchor_<?php echo $bookmark->link_id;?>", "#feedreading_info_<?php echo $bookmark->link_id;?>", "#feedreading_previewtoggle_<?php echo $bookmark->link_id;?>", "#frbl_last_posttitle_<?php echo $bookmark->link_id;?>");
<?php
                                if ($showPreviewButton==0){?>
                                      addFeedControl("#feedreading_preview_<?php echo $bookmark->link_id;?>", "<?php echo $urlFeed; ?>", "<?php echo $name; ?>");
    						<?php } // end if ?>
						} else {
                  			addZeroAge("#feedreading_bookmark_<?php echo $bookmark->link_id;?>");
              			}
                                      });}
                      <?php } else if ($feedDiscovery==0) { ?>
                      if($("#feedreading_bookmark_<?php echo $bookmark->link_id;?>").length > 0){

						google.feeds.lookupFeed("<?php echo $bookmark->link_url;?>", function() {
							var url<?php echo $bookmark->link_id;?>= this.url, feed<?php echo $bookmark->link_id;?> = new google.feeds.Feed(this.url);
                            feed<?php echo $bookmark->link_id;?>.load(function(result_<?php echo $bookmark->link_id;?>){
                                if (!result_<?php echo $bookmark->link_id;?>.error) {
                                addAge(result_<?php echo $bookmark->link_id;?>.feed, "#feedreading_bookmark_<?php echo $bookmark->link_id;?>", "#feedreading_anchor_<?php echo $bookmark->link_id;?>", "#feedreading_info_<?php echo $bookmark->link_id;?>", "#feedreading_previewtoggle_<?php echo $bookmark->link_id;?>", "#frbl_last_posttitle_<?php echo $bookmark->link_id;?>");
<?php
                          if ($showPreviewButton==0){?>
                                addFeedControl("#feedreading_preview_<?php echo $bookmark->link_id;?>", url<?php echo $bookmark->link_id;?>, "<?php echo $name; ?>");
						<?php } // end if ?>
                  			} else {
                      			addZeroAge("#feedreading_bookmark_<?php echo $bookmark->link_id;?>");
                  			}
                                });

						});}

                      <?php } else { ?>
                      	addZeroAge("#feedreading_bookmark_<?php echo $bookmark->link_id;?>");
                      <?php }// end if ?>
                    <?php } // end foreach ?>

					<?php //****************************************************************************************************************** ?>
					<?php //                Create entries for bookmarks from single-cat-widgets                                               ?>
					<?php //****************************************************************************************************************** ?>
					<?php
						foreach((array) $widget_keys as $key){
									$eachCat = $option[$key]["cat"];
									$widgetbookmarks = get_bookmarks('category='.$eachCat);
									foreach ((array) $widgetbookmarks as $bookmark){
										$name=$bookmark->link_name;
										$urlFeed=$bookmark->link_rss;
										if(trim($bookmark->link_rss)!='') {?>
										// hallo
										if($("#single-<?php echo $key ?>_feedreading_bookmark_<?php echo $bookmark->link_id;?>").length > 0){

										feed_single<?php echo $key.$bookmark->link_id;?>.load(function(result_single_<?php echo $key.$bookmark->link_id;?>){
										if (!result_single_<?php echo $key.$bookmark->link_id;?>.error) {
											addAge(result_single_<?php echo $key.$bookmark->link_id;?>.feed, "#single-<?php echo $key ?>_feedreading_bookmark_<?php echo $bookmark->link_id;?>", "#single-<?php echo $key ?>_feedreading_anchor_<?php echo $bookmark->link_id;?>", "#single-<?php echo $key ?>_feedreading_info_<?php echo $bookmark->link_id;?>", "#single-<?php echo $key ?>_feedreading_previewtoggle_<?php echo $bookmark->link_id;?>", "#single-<?php echo $key ?>_frbl_last_posttitle_<?php echo $bookmark->link_id;?>");
<?php
										if ($showPreviewButton==0){?>
											addFeedControl("#single-<?php echo $key ?>_feedreading_preview_<?php echo $bookmark->link_id;?>", "<?php echo $urlFeed; ?>", "<?php echo $name; ?>");
										<?php } // end if ?>
											} else {
												addZeroAge("#single-<?php echo $key ?>_feedreading_bookmark_<?php echo $bookmark->link_id;?>");
											}
										});
										}
										<?php } else if ($feedDiscovery==0) { ?>
											if($("#single-<?php echo $key ?>_feedreading_bookmark_<?php echo $bookmark->link_id;?>").length > 0){
											google.feeds.lookupFeed("<?php echo $bookmark->link_url;?>", function() {
											var url_single_<?php echo $key.$bookmark->link_id;?>= this.url, feed_single_<?php echo $key.$bookmark->link_id;?> = new google.feeds.Feed(this.url);
											feed_single_<?php echo $key.$bookmark->link_id;?>.load(function(result_single_<?php echo $key.$bookmark->link_id;?>){
											if (!result_single_<?php echo $key.$bookmark->link_id;?>.error) {
											addAge(result_single_<?php echo $key.$bookmark->link_id;?>.feed, "#single-<?php echo $key ?>_feedreading_bookmark_<?php echo $bookmark->link_id;?>", "#single-<?php echo $key ?>_feedreading_anchor_<?php echo $bookmark->link_id;?>", "#single-<?php echo $key ?>_feedreading_info_<?php echo $bookmark->link_id;?>", "#single-<?php echo $key ?>_feedreading_previewtoggle_<?php echo $bookmark->link_id;?>", "#single-<?php echo $key ?>_frbl_last_posttitle_<?php echo $bookmark->link_id;?>");
										<?php
											if ($showPreviewButton==0){?>
											addFeedControl("#single-<?php echo $key ?>_feedreading_preview_<?php echo $bookmark->link_id;?>", url_single_<?php echo $key.$bookmark->link_id;?>, "<?php echo $name; ?>");
										<?php } // end if ?>
											} else {
											addZeroAge("#single-<?php echo $key ?>_feedreading_bookmark_<?php echo $bookmark->link_id;?>");
											}
										});
									});}
                      <?php } else { ?>
                      	addZeroAge("#feedreading_bookmark_<?php echo $bookmark->link_id;?>");
                      <?php }// end if ?>
                    <?php }	?>
					<?php //****************************************************************************************************************** ?>
					<?php //                CreateD entries for bookmarks from single-cat-widgets                                        EOC   ?>
					<?php //******************************************************************************************************************
					}?>
				<?php if ($jsSorting != 0 and $maxBookmarks > 0) { ?>
				feedreading_limit_display();
				<?php } ?>
				<?php if ($jsSorting != 0 and $displayStyle=='rolling'){ ?> feedreading_rolling(); <?php } ?>
				feedreading_category_observer();
				return false;
                }


      $.fn.feedReadingBlogrollSpy = function (limit, interval) {
    	    limit = limit || 4;
    	    interval = interval || 4000;

    	    return this.each(function () {
    	        // 1. setup
    	            // capture a cache of all the list items
    	            // chomp the list down to limit li elements
    	        var $list = $(this),
    	            items = [], // uninitialised
    	            currentItem = limit,
    	            total = 0, // initialise later on
    	            height = $list.find('li:first').height();

    	        // capture the cache
    	        $list.find('li').each(function () {
    	            //items.push('<li>' + $(this).html() + '</li>');
    	            items.push($(this));
    	        });
    	        /*for (var i=0; i < li_items.length; i++){
    	            //items.push('<li>' + $(this).html() + '</li>');
    	            items.push($(this));
    	        } */

    	        total = items.length;

    	        $list.wrap('<div class="spyWrapper" />').parent().css({ height : height * (limit + 2) });

    	        $list.find('li').filter(':gt(' + (limit - 1) + ')').remove();

    	        // 2. effect
    	        function spy() {
			if(!$list.hasClass("mouseover")){
			    // insert a new item with opacity and height of zero
			    var $insert = $(items[currentItem]).css({
				height : 0,
				opacity : 0,
				display : 'none'
			    }).prependTo($list);

			    // fade the LAST item out
			    $list.find('li:last').animate({ opacity : 0}, 1000, function () {
				// increase the height of the NEW first item
				$insert.animate({ height : height }, 1000).animate({ opacity : 1 }, 1000);
				$insert.show();
				// AND at the same time - decrease the height of the LAST item
				// $(this).animate({ height : 0 }, 1000, function () {
				    // finally fade the first item in (and we can remove the last)
				    $(this).remove();
				// });
			    });

			    currentItem++;
			    if (currentItem >= total) {
				currentItem = 0;
			    }
			}
			setTimeout(spy, interval);
    	        }

    	        spy();
    	    });
    	};
    	$.fn.frbrsort = function() {
    		return this.pushStack( [].sort.apply(this, arguments), []);
    	};

    	function sortAlpha(a, b) {
    		return parseInt($(a).attr("age")) < parseInt($(b).attr("age")) ? 1 : -1;
    	};
});
            /* ]]> */</script>
                    <?php
                    $buffer = ob_get_contents();
                    // remove script-tags, as they are not necessary in a flat .js-file
                    // they are only needed during development to allow automatic code-formatting, d'oh
                    $buffer = str_replace('<script type="text/javascript">/* <![CDATA[ */','', $buffer);
                    $buffer = str_replace('/* ]]> */</script>','', $buffer);

                    if (version_compare(PHP_VERSION, '5.0.0') === 1 and class_exists('JSMinFRBR')) {
                    	$buffer = JSMinFRBR::minify($buffer);
                    }

                    ob_end_clean();

                    if ((is_writable(WP_CONTENT_DIR) and !file_exists(WP_CONTENT_DIR.FEEDREADING_JS_FILE)) or is_writable(WP_CONTENT_DIR.FEEDREADING_JS_FILE)) {
                    	$fp = fopen(WP_CONTENT_DIR.FEEDREADING_JS_FILE, "w");
                    	fwrite($fp, $buffer);
                    	fclose($fp);
                    }
                    return;
}
/**
 * is the JavaScript-file writable?
 *
 * @return boolean
 */
function feedreading_is_not_jsfile_writable(){

	return (!is_writable(WP_CONTENT_DIR) and !file_exists(WP_CONTENT_DIR.FEEDREADING_JS_FILE)) or !is_writable(WP_CONTENT_DIR.FEEDREADING_JS_FILE);
}

if (version_compare($wp_version, '2.8', '>=') or stristr( $wp_version, 'RC' ) == true) {
	/**
	 * FooWidget Class
	 */
	class FeedReadingBlogrollWidget extends WP_Widget {
	    /** constructor */
	    function FeedReadingBlogrollWidget() {
	    	$desc = __('This widget adds the WP Social Blogroll to your sidebar.', 'feedreading_blogroll').' '.__('It can display one single link category. An arbitrary number of instances of this widget can be added to your sidebars.', 'feedreading_blogroll');

	    	$widget_ops = array('classname' => 'widget_feedreading_blogroll', 'description' => $desc);
			$this->WP_Widget('feedReadingBlogroll', 'WP Social Blogroll', $widget_ops);
	        //parent::WP_Widget(false, $name = 'WP Social Blogroll');
	    }

	    /** @see WP_Widget::widget */
	    function widget($args, $instance) {
	        extract( $args );
	        $title = esc_attr($instance['title']);
			$cat   = esc_attr($instance['cat']);
	        $widgetId = "single-".$this->number."_";
	        //add_action('feedreading_blogroll_single_widget','feedreading_blogroll_widget_javascript',10,2);
	        ?>
	              <?php echo $before_widget; ?>
	                  <?php
	                  widget_feedreading_blogroll_single($before_title, $after_title, $title, $cat, $widgetId)
	                  ?>
	              <?php echo $after_widget; ?>
	        <?php
	    }

	    /** @see WP_Widget::update */
	    function update($new_instance, $old_instance) {
	        return $new_instance;
	    }

	    /** @see WP_Widget::form */
	    function form($instance) {
			$title = esc_attr($instance['title']);
			$cat   = esc_attr($instance['cat']);
			$desc = __('This widget adds the WP Social Blogroll to your sidebar.', 'feedreading_blogroll').' '.__('It can display one single link category. An arbitrary number of instances of this widget can be added to your sidebars.', 'feedreading_blogroll');

			?>
			<p><?php echo $desc; ?></p>
			<p><a href="options-general.php?page=feedreading_blogroll.php">Feed
Reading Blogroll <?php _e('Options','feedreading_blogroll'); ?></a></p>

            <p><label for="<?php echo $this->get_field_id('title'); ?>">
            	<?php _e('Title:','feedreading_blogroll'); ?>
            	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            	</label>
            </p>
            <div>
	            <label for="<?php echo $this->get_field_id('cat'); ?>">
	            	<?php _e('Link categories','feedreading_blogroll'); ?>:<br />
	            	<select name="<?php echo $this->get_field_name('cat'); ?>"
	            		id="<?php echo $this->get_field_id('cat'); ?>" class="widefat">
		            	<?php
		            	$cats = feedreading_blogroll_get_linkcats();
		            	foreach ($cats as $eachCat){
		            		$eachTermID = $eachCat->term_ID;
		            		?><option value="<?php echo $eachTermID ?>" <?php selected($cat, $eachTermID); ?>><?php echo $eachCat->name;  ?></option>
		            	<?php }	?>
	            	</select>
	            </label>
            </div>
        <?php
	    }

	} // class FooWidget
}

function feedreading_blogroll_admin_28_notice($plugin='feed-reading-blogroll'){
	?><tr><td class="plugin-update" colspan="5"><div class="update-message"><?php
	printf(__("If you have just upgraded to WordPress 2.8, you will have to add the Widget of the WP Social Blogroll plugin to your sidebar again. <a href=\"%s\">Please go to the Widget-Settings-Page</a> to add the widget to your sidebar.","feedreading_blogroll"),get_option('siteurl'). '/wp-admin/widgets.php');
	?></div></td></tr><?php

}
function feedreading_blogroll_admin_27_notice($plugin='feed-reading-blogroll'){
	global $wp_version;
	?><tr><td class="plugin-update" colspan="5"><div class="update-message"><?php
 	printf(__("You are currently using WordPress version %s. Please be informed, that future versions of the WP Social Blogroll plugin will no longer support this version of WordPress. I recommend to upgrade your WordPress-installation to <a href=\"http://wordpress.org\">the latest version</a>.","feedreading_blogroll"),$wp_version);
	?></div></td></tr><?php

}

function feedreading_blogroll_add_plugin_row($links, $file) {
	static $this_plugin;
	global $wp_version;
	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);

	if ($file == $this_plugin ){
		$current = get_option('update_plugins');
		if (!isset($current->response[$file])) return false;

		$columns = substr($wp_version, 0, 3) == "2.8" ? 3 : 5;
		$url = "http://www.weinschenker.name/info/info.txt";
		$update = wp_remote_fopen($url);
		echo '<td colspan="'.$columns.'">';
		echo $update;
		echo '</td>';
	}
}
function feedreading_blogroll_getDomain($url) {
// get host name from URL
	preg_match('@^(?:http://)?([^/]+)@i', $url, $matches);
	return $matches[1];
}
?>
