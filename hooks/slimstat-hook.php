<?php
/*
Plugin Name: Slimstat Hook
Plugin URI: http://www.weinschenker.name/
Description: This plugin calls the slimstat-hook everytime a page or feed of your blog is visited.
Version:  0.5
Author: Jan Weinschenker
Author URI: http://www.weinschenker.name


   $Id$

   
    Plugin: Copyright 2006  Jan Weinschenker  (email: pandorafeeds@weinschenker.name)

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

*/
function slimstat_hook(){
    @include_once($_SERVER["DOCUMENT_ROOT"]."slimstat.www/inc.stats.php");
}

function google_analytics_hook(){
	$domain = $_SERVER["SERVER_NAME"];
	// rufe Funktion domain_theme()
	// für gewünschte Domain auf
	if ($domain != "m.weinschenker.name" && ! is_feed()) {?>
		<script type="text/javascript">/* <![CDATA[ */
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-338677-1']);
		  _gaq.push(['_setDomainName', '.weinschenker.name']);
  		  _gaq.push(['_trackPageview']);
                  _gaq.push(['_gat._anonymizeIp']);
		
		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    (document.getElementsByTagName('body')[0]).appendChild(ga);
		  })();
		/* ]]> */
		</script>
	<?php } 
}

function html_header(){
global $post;
$domain = $_SERVER["SERVER_NAME"];
?>
<?php if (is_single() or is_page()) { 
    while (have_posts()) : the_post(); 
	$custom_fields = get_post_custom();
        if ($custom_fields['noindex']['0'] == 'true') { ?>
	<meta name="robots" content="noindex,nofollow" />
    <?php } else { ?>
    	<meta name="domain" content="<?php echo $domain; ?>" />
    	<meta name="postmetanoindex" content="<?php echo $custom_fields['noindex']; ?>" />
    
<?php } endwhile; ?>            
<?php rewind_posts(); ?>
<?php } else if(!is_home() and !is_single() and !is_page()) {
?><meta name="robots" content="noindex,nofollow" />
<?php 
    }
?>
<meta name="y_key" content="c84bed6e73bbb09e" />
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="openid.server" href="http://www.weinschenker.name/myId/MyID.config.php" />
<link rel="openid.delegate" href="http://www.weinschenker.name/myId/MyID.config.php" />    
<link rel="pavatar" href="http://www.weinschenker.name/wp-images/avatar.png" />
<link rel="alternate" media="handheld" title="Mobile View | Mobile Version" href="http://<?php echo 'm.weinschenker.name' . $_SERVER["REQUEST_URI"] ?>" type="application/xhtml+xml"/>
<!-- script type="text/javascript" src="/js/jquery.ui.all.js"></script -->
<!-- AKVS head start v1.5 -->
   <!--[if gte IE 5.5]>
    <![if lt IE 7]>
    <style type="text/css">
    div#akct a#akpeel:hover {
            right: -1px;
    }
    </style>
    <![endif]>
    <![endif]-->
    <!-- AKVS head end -->
<?php
}
function slimstat_hook_add_scripts(){
	if (!is_admin()){
		wp_register_script('jquerythickbox', '/js/thickbox.js',array('jquery'),'3.1',true);
		wp_enqueue_script('jquerythickbox');
		//wp_enqueue_script('snap_shots', 'http://shots.snap.com/ss/85880223acc5e80adac6a2f427f26178/snap_shots.js', array(), '1.0', true);	
//		wp_register_script('facebookapi','http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/en_US', array(), '1.0', true);
//		wp_enqueue_script('facebookapi');
 		wp_register_script('beautyOfCode-scripts', '/js/jquery.beautyOfCode.js',array('jquery'),'0.1.1', true);
		wp_enqueue_script('beautyOfCode-scripts');
 		wp_register_script('slimstat-hook-scripts', WP_PLUGIN_URL.'/slimstat-hook.js',array('beautyOfCode-scripts','jquery'),'1.0', true);
		wp_enqueue_script('slimstat-hook-scripts');
	}
}
function slimstat_hook_add_styles(){
	if (!is_admin()){
 		wp_register_style('slimstat-hook-styles', WP_PLUGIN_URL.'/slimstat-hook.css');
		wp_enqueue_style('slimstat-hook-styles');
		
        wp_register_style('jquerythickboxcss', '/css/thickbox.css');
        wp_enqueue_style('jquerythickboxcss');
    }
}

function slimstat_nosidebar_hook_add_styles(){
	if (!is_admin()){
 		wp_register_style('slimstat-nosidebar-hook-styles', WP_PLUGIN_URL.'/slimstat-nosidebar-hook.css');
		wp_enqueue_style('slimstat-nosidebar-hook-styles');
	}
}

add_action('wp_head', 'html_header');
add_action('wp_head', 'slimstat_hook');
add_action('atom_head', 'slimstat_hook');
add_action('rdf_header', 'slimstat_hook');
add_action('rss_head', 'slimstat_hook');
add_action('rss2_head', 'slimstat_hook');
add_action('do_feed_rss2(true)', 'slimstat_hook');
add_action('do_feed_atom(true)', 'slimstat_hook');
//add_filter( 'sidebars_widgets', 'slimstat_disable_footer_widgets' );
add_action('wp_print_scripts',	'slimstat_hook_add_scripts');
add_action('wp_print_styles' ,	'slimstat_hook_add_styles');
add_action('wp_footer','google_analytics_hook');
add_filter( 'enable_post_by_email_configuration', '__return_true' );
function body_hook(){
?>

    <!-- AKVS body start v1.5 -->
    <!-- div id="akct">
        <a id="akpeel" href="http://www.vorratsdatenspeicherung.de" title="Stoppt die Vorratsdatenspeicherung! Jetzt klicken &amp; handeln!">
            <img src="http://wiki.vorratsdatenspeicherung.de/images/Akvst.gif" alt="Stoppt die Vorratsdatenspeicherung! Jetzt klicken &amp; handeln!" />
        </a>
        <a id="akpreload" href="http://wiki.vorratsdatenspeicherung.de/?title=Online_Demo" title="Willst du auch bei der Aktion teilnehmen? Hier findest du alle relevanten Infos und Materialien:">
            <img src="http://wiki.vorratsdatenspeicherung.de/images/Akvsi.gif" alt="Willst du auch bei der Aktion teilnehmen? Hier findest du alle relevanten Infos und Materialien:" />
        </a>
    </div -->
<!-- AKVS body end -->

<?php
}

function google_analytics_mobile(){
    //$GA_ACCOUNT = "MO-338677-7";
    $GA_ACCOUNT = "MO-338677-1";
    $GA_PIXEL = "/ga/ga.php";
    $url = "";
    $url .= $GA_PIXEL . "?";
    $url .= "utmac=" . $GA_ACCOUNT;
    $url .= "&amp;utmhn=" . '.weinschenker.name';
    $url .= "&amp;utmn=" . rand(0, 0x7fffffff);

    $referer = $_SERVER["HTTP_REFERER"];
    $query = $_SERVER["QUERY_STRING"];
    $path = $_SERVER["REQUEST_URI"];

    if (empty($referer)) {
      $referer = "-";
    }
    $url .= "&amp;utmr=" . urlencode($referer);

    if (!empty($path)) {
      $url .= "&amp;utmp=" . urlencode($path);
    }

    $url .= "&amp;guid=ON";
    return $url;
}

function slimstat_disable_footer_widgets( $sidebars_widgets ) {
	$showSidebar = (bool) $custom_fields["nosidebar"][0] == "true";

	if ( is_single() && $showSidebar == false){	
		$sidebars_widgets["primary-widget-area"] = false;
		$sidebars_widgets["secondary-widget-area"] = false;
		add_action('wp_print_styles' , 'slimstat_nosidebar_hook_add_styles');
	}
	return $sidebars_widgets;
}

?>
