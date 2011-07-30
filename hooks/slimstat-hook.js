jQuery(function($) {
    addPjirc();
    addGameOfLife();
    addFB();
    addBC();
    //addFBShareLike();
    trackSocial();
    //googlePlusOne();
    //nwBlogs();
    //addGA();
    //$('a[rel*=lightbox]').lightBox();
});

function trackSocial(){
FB.Event.subscribe('edge.create', function(targetUrl) {
  _gaq.push(['_trackSocial', 'facebook', 'like', targetUrl]);
});
FB.Event.subscribe('edge.remove', function(targetUrl) {
  _gaq.push(['_trackSocial', 'facebook', 'unlike', targetUrl]);
});
FB.Event.subscribe('message.send', function(targetUrl) {
  _gaq.push(['_trackSocial', 'facebook', 'send', targetUrl]);
});twttr.events.bind('tweet', function(event) {
  if (event) {
    var targetUrl;
    if (event.target && event.target.nodeName == 'IFRAME') {
      targetUrl = extractParamFromUri(event.target.src, 'url');
    }
    _gaq.push(['_trackSocial', 'twitter', 'tweet', targetUrl]);
  }
});
}

function nwBlogs(){
	if (jQuery('#networkedblogs_nwidget_container').length > 0){
		jQuery.getScript("http://nwidget.networkedblogs.com/getnetworkwidget?bid=52780", function(){
            if(typeof(networkedblogs)=="undefined"){
                networkedblogs = {};
                networkedblogs.blogId=52780;
                networkedblogs.shortName="sinn_city_blog";
            }
		});
	}
}

function googlePlusOne(){
    if(jQuery('.tw_button').length > 0){
        jQuery.getScript("http://apis.google.com/js/plusone.js", function(){
            jQuery('.tw_button').before(jQuery('<g:plusone count="true" size="medium"></g:plusone>'));
    	});
    }
}

function addGA(){
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
  alert('hallo');
}

function addBC(){
if (jQuery('.code').length > 0){
    jQuery.beautyOfCode.init('clipboard.swf');
    jQuery.beautyOfCode.beautifyAll();
    }
}

function addFB(){
	if (jQuery('#fbWrapper').length > 0){
	jQuery.getScript('http://connect.facebook.net/en_US/all.js#xfbml=1',function() {
		//FB.init('12916e567e56a553a46a89e6a895603e');
		var $fbTag = jQuery('<fb:like />');
		$fbTag.attr({
			href : 'https://www.facebook.com/pages/WP-Social-Blogroll-A-WordPress-Plugin/191088298807',
			send : 'true',
			width : '450',
			show_faces : 'true'
			});
		$fbTag.appendTo(jQuery('#fbWrapper'));
	});
	}
}

function addFBShareLike(){
	if(jQuery('fb\\:like').length > 0) {
		jQuery.getScript('http://connect.facebook.net/en_US/all.js', function(){

		});
		jQuery.getScript('http://static.ak.fbcdn.net/connect.php/js/FB.Share', function(){

		});
	}
}

function addPjirc(){

	var $pjicTag = jQuery('<object />');
	$pjicTag.attr({
		width: '550',
		height: '500',
		type:'application/x-java-applet',
		code: 'IRCApplet.class',
		codebase: '/applets/pjirc'
	});
	if(navigator.appName == "Microsoft Internet Explorer"){
		$pjicTag.attr('classid','clsid:8AD9C840-044E-11D1-B3E9-00805F499D93');
		$pjicTag.attr('codebase','http://java.sun.com/update/1.6.0/jinstall-6-windows-i586.cab#Version=6,0,0,0');
	}
	$pjicTag.append(jQuery('<param />').attr({
		name: 'java_archive',
		value : 'pixx.jar,irc.jar'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'codebase',
		value : '/applets/pjirc'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'java_codebase',
		value : '/applets/pjirc'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'java_code',
		value : 'IRCApplet.class'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'CABINETS',
		value : 'irc.cab securedirc.cab pixx.cab'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'nick',
		value : 'Anonymous'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'alternatenick',
		value : 'Anon???'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'name',
		value : 'Java User'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'host',
		value : 'irc.freenode.net'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'port',
		value : '6666'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'gui',
		value : 'pixx'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'quitmessage',
		value : 'PJIRC forever!'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'asl',
		value : 'true'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'useinfo',
		value : 'true'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'style:bitmapsmileys',
		value : 'true'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'style:smiley1',
		value : ':) img/sourire.gif'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'pixx:timestamp',
		value : 'true'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'pixx:highlight',
		value : 'true'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'pixx:highlightnick',
		value : 'true'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'pixx:nickfield',
		value : 'true'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'pixx:styleselector',
		value : 'true'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'pixx:setfontonstyle',
		value : 'true'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'type',
		value : 'application/x-java-applet'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'command1',
		value : '/join #wordpress-dev'
	}));
	$pjicTag.appendTo(jQuery('#pjircWrapper'));
}

function addGameOfLife(){

	var $pjicTag = jQuery('<object />');
	$pjicTag.attr({
		width: '250',
		height: '50',
		type:'application/x-java-applet',
		code: 'life/v41d/LifeButton.class',
		codebase: '/applets/gameoflife'
	});
	if(navigator.appName == "Microsoft Internet Explorer"){
		$pjicTag.attr('classid','clsid:8AD9C840-044E-11D1-B3E9-00805F499D93');
		$pjicTag.attr('codebase','http://java.sun.com/update/1.6.0/jinstall-6-windows-i586.cab#Version=6,0,0,0');
	}
	$pjicTag.append(jQuery('<param />').attr({
		name: 'java_archive',
		value : 'gameoflife.jar'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'codebase',
		value : '/applets/gameoflife'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'java_codebase',
		value : '/applets/gameoflife'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'java_code',
		value : 'life/v41d/LifeButton.class'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'CABINETS',
		value : 'irc.cab securedirc.cab pixx.cab'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'autostart',
		value : 'false'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'rules',
		value : '23/63'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'loaddir',
		value : 'lif'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'describe',
		value : 'false'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'zoom',
		value : '3'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'fgcolor',
		value : '0000c0'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'gridcolor',
		value : 'ffffff'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'grids',
		value : 'true'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'fps',
		value : '20'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'skip',
		value : '1'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'toolbar',
		value : 'Open Go HowFar Clear Rules Speed Zoom Count'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'howfarchoices',
		value : 'forever +1 -1 +46'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'scrollbarwidth',
		value : '20'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'editable',
		value : 'true'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'windowwidth',
		value : '600'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'windowheight',
		value : '400'
	}));
	$pjicTag.append(jQuery('<param />').attr({
		name: 'buttonname',
		value : 'Start Life'
	}));
	$pjicTag.appendTo(jQuery('#gameOfLifeWrapper'));

}

function getTinyURL(longURL, success) {
    var API = 'http://json-tinyurl.appspot.com/?url=',
        URL = API + encodeURIComponent(longURL) + '&callback=?';
    jQuery.getJSON(URL, function(data){
        success && success(data.tinyurl);
    });
}

function toggleTinyUrl() {
    var url= jQuery("link[rel='canonical']").attr("href"), $a = jQuery("a[title='TwitThis']");
     getTinyURL(url, function(tinyurl){
        var $twitturl="http://twitter.com/home?status="+encodeURIComponent(tinyurl);
        $a.attr({
            href : $twitturl
        });
    });
}
