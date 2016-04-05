$(document).ready( function() {
	// Set defaults if included as a gadget, otherwise they should
	// be defined by the extension.

	var maxCrumbs = mw.config.get('wgJSBreadCrumbsMaxCrumbs');
	var separator = mw.config.get('wgJSBreadCrumbsSeparator');
	var cookiePath = mw.config.get('wgJSBreadCrumbsCookiePath');
	var leadingDescription = mw.config.get('wgJSBreadCrumbsLeadingDescription');
	var cssSelector = mw.config.get('wgJSBreadCrumbsCSSSelector');
	var showSiteName = mw.config.get('wgJSBreadCrumbsShowSiteName');
	var showSidebar = mw.config.get('wgJSBreadCrumbsShowSidebar');
	var specialPageName = mw.config.get('wgCanonicalSpecialPageName');
	var crumbsPageName = mw.config.get('wgJSBreadCrumbsPageName');
	var siteName = mw.config.get('wgSiteName');
	var pervasiveWikiFarm = mw.config.get('wgJSBreadCrumbsPervasiveWikiFarm');
	var cookieNameSuffix = "-" + siteName;

	if(endsWith(crumbsPageName, "Badtitle")) {
	}
	if ( typeof maxCrumbs === "undefined" ) {
		maxCrumbs = 5;
	}
	if ( typeof separator === "undefined" ) {
		separator = "»";
	}
	if ( typeof cookiePath === "undefined" ) {
		cookiePath = "/";
	}
	if ( typeof leadingDescription === "undefined" ) {
		leadingDescription = "Navigation trail";
	}
	if ( typeof cssSelector === "undefined" ) {
		cssSelector = "#top";
	}
	if ( typeof showSiteName === "undefined" ) {
		showSiteName = false;
	}
	if ( typeof showSidebar === "undefined" ) {
		showSidebar = false;
	}
	if ( typeof pervasiveWikiFarm === "undefined" ) {
		pervasiveWikiFarm = false;
	}

	if(pervasiveWikiFarm) {
		cookieNameSuffix = "";
	}

	if ( specialPageName === "Userlogout" ) {
		$.cookie( 'mwext-bc-title' + cookieNameSuffix, '', { path: cookiePath } );
		$.cookie( 'mwext-bc-url' + cookieNameSuffix, '', { path: cookiePath } );
		$.cookie( 'mwext-bc-site' + cookieNameSuffix, '', { path: cookiePath } );
	}
	// Get the breadcrumbs from the cookies
	var titleState = ( $.cookie( 'mwext-bc-title' + cookieNameSuffix) || "" ).split( '|' );
	var urlState = ( $.cookie( 'mwext-bc-url' + cookieNameSuffix) || "" ).split( '|' );
	var siteState = ( $.cookie( 'mwext-bc-site' + cookieNameSuffix) || "" ).split( '|' );

	// Strip the first title/url if it is empty
	if ( titleState[0].length === 0 ) {
		titleState.splice( 0, 1 );
		urlState.splice( 0, 1 );
		siteState.splice( 0, 1 );
	}

	// Get the full title
	var title = crumbsPageName;

	// Remove duplicates
	/* var matchTitleIndex = $.inArray( title, titleState ); */
	var matchUrlIndex = $.inArray( location.pathname + location.search, urlState );
	/*if ( matchTitleIndex != -1 && ( matchUrlIndex == matchTitleIndex ) ) {
		titleState.splice( matchTitleIndex, 1 );
		urlState.splice( matchTitleIndex, 1 );
		siteState.splice( matchTitleIndex, 1 );
	}*/
	if ( matchUrlIndex !== -1  ) {
		titleState.splice( matchUrlIndex, 1 );
		urlState.splice( matchUrlIndex, 1 );
		siteState.splice( matchUrlIndex, 1 );
	}

	// Add the current page
	if(!endsWith(crumbsPageName, "Badtitle")) {
		titleState.push( title );
		urlState.push( location.pathname + location.search );
		siteState.push( siteName );
	}
	// Ensure we only display the maximum breadcrumbs set
	if ( titleState.length > maxCrumbs ) {
		titleState = titleState.slice( titleState.length - maxCrumbs );
		urlState = urlState.slice( urlState.length - maxCrumbs );
		siteState = siteState.slice( siteState.length - maxCrumbs );
	}

	var skin;

	//Insert SideBar List
	if( showSidebar === true ) {
		if(skin === "vector") {
			postVector(maxCrumbs);
		} else if(skin === "modern") {
			postOther('#mw_portlets', maxCrumbs);
		} else if(skin === "monobook") {
			postOther('#column-one', maxCrumbs);
		} else if(skin === "cologneblue") {
			postOther('#quickbar', maxCrumbs);
		}

		for ( var i = titleState.length-1; i >= 0; i-- ) {
			if ( showSiteName === true ) {
				$("#p-rv-list2").append('<li><a href="'  + urlState[i]+'">'+ '(' + siteState[i] + ')' + titleState[i] + '</a></li>');
			} else {
				$("#p-rv-list2").append('<li><a href="'  + urlState[i]+'">'+ titleState[i]+'</a></li>');
			}
		}
	} else {
		// Insert the span we are going to populate
		$( cssSelector ).before( '<span id="mwext-bc" class="noprint plainlinks jsbc-breadcrumbs"></span>' );

		var mwextbc = $( "#mwext-bc" );

		var urltoappend;

		// Add the bread crumb description
		mwextbc.append( leadingDescription + ': ' );

		// Add the bread crumbs
		for ( var k = 0; k < titleState.length; k++ ) {
			if ( showSiteName === true ) {
				urltoappend = '<a href="' + urlState[k] + '">' + '(' + siteState[k] + ') ' + titleState[k] + '</a> ';
			} else {
				urltoappend = '<a href="' + urlState[k] + '">' + titleState[k] + '</a> ';
			}

			// Only add the separator if this isn't the last title
			if ( k < titleState.length - 1 ) {
				urltoappend = urltoappend + separator + ' ';
			}
			mwextbc.append( urltoappend );
		}
	}
	// Save the bread crumb states to the cookies
	$.cookie( 'mwext-bc-title' + cookieNameSuffix, titleState.join( '|' ), { path: cookiePath, expires: 30 } );
	$.cookie( 'mwext-bc-url' + cookieNameSuffix, urlState.join( '|' ), { path: cookiePath, expires: 30 } );
	$.cookie( 'mwext-bc-site' + cookieNameSuffix, siteState.join( '|' ), { path: cookiePath, expires: 30 } );
});

function postVector(maxCrumbs) {
	$("#mw-panel").append("<div class='portal persistent' role='navigation' id='p-rv' aria-labelledby='p-rv-label'></div>");
	$("#p-rv").append("<h3 id='p-rv-label' tabindex='3'><a href='#' aria-haspopup='true' aria-controls='p-rv-list2' role='button' aria-pressed='false' aria-expanded='true'>Last "+ maxCrumbs + " Pages Viewed</a></h3>");
	$("#p-rv").append("<div class='body' style='display: block;'><ul id='p-rv-list2'></ul>");
}

function postOther(id, maxCrumbs) {
	$(id).append("<div class='portlet' id='p-rv' role='navigation'></div>");
	$("#p-rv").append("<h3>Last " + maxCrumbs+ "  Pages Viewed</h3><div class='pBody'><ul id='p-rv-list'></ul></div>");
}

function endsWith(str, suffix) {
	return str.indexOf(suffix, str.length - suffix.length) !== -1;
}


