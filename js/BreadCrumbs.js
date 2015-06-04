$(document).ready( function() {
	// Set defaults if included as a gadget, otherwise they should
	// be defined by the extension.

	if ( typeof wgJSBreadCrumbsMaxCrumbs == "undefined" ) {
		wgJSBreadCrumbsMaxCrumbs = 5;
	}
	if ( typeof wgJSBreadCrumbsSeparator == "undefined" ) {
		wgJSBreadCrumbsSeparator = "»";
	}
	if ( typeof wgJSBreadCrumbsCookiePath == "undefined" ) {
		wgJSBreadCrumbsCookiePath = "/";
	}
	if ( typeof wgJSBreadCrumbsLeadingDescription == "undefined" ) {
		wgJSBreadCrumbsLeadingDescription = "Navigation trail";
	}
	if ( typeof wgJSBreadCrumbsCSSSelector == "undefined" ) {
		wgJSBreadCrumbsCSSSelector = "#top";
	}
	if ( typeof wgJSBreadCrumbsShowSiteName == "undefined" ) {
		wgJSBreadCrumbsShowSiteName = false;
	}
	if ( typeof wgJSBreadCrumbsShowSidebar == "undefined" ) {
		wgJSBreadCrumbsShowSidebar = false;
	}

	if ( wgCanonicalSpecialPageName == "Userlogout" ) {
		$.cookie( 'mwext-bc-title', '', { path: wgJSBreadCrumbsCookiePath } );
		$.cookie( 'mwext-bc-url', '', { path: wgJSBreadCrumbsCookiePath } );
		$.cookie( 'mwext-bc-site', '', { path: wgJSBreadCrumbsCookiePath } );
	}
	// Get the breadcrumbs from the cookies
	var titleState = ( $.cookie( 'mwext-bc-title' ) || "" ).split( '|' );
	var urlState = ( $.cookie( 'mwext-bc-url' ) || "" ).split( '|' );
	var siteState = ( $.cookie( 'mwext-bc-site' ) || "" ).split( '|' );

	// Strip the first title/url if it is empty
	if ( titleState[0].length == 0 ) {
		titleState.splice( 0, 1 );
		urlState.splice( 0, 1 );
		siteState.splice( 0, 1 );
	}

	// Get the full title
	var title = wgJSBreadCrumbsPageName;

	// Remove duplicates
	var matchTitleIndex = $.inArray( title, titleState );
	var matchUrlIndex = $.inArray( location.pathname + location.search, urlState );
	if ( matchTitleIndex != -1 && ( matchUrlIndex == matchTitleIndex ) ) {
		titleState.splice( matchTitleIndex, 1 );
		urlState.splice( matchTitleIndex, 1 );
		siteState.splice( matchTitleIndex, 1 );
	}

	// Add the current page
	titleState.push( title );
	urlState.push( location.pathname + location.search );
	siteState.push( wgSiteName );

	// Ensure we only display the maximum breadcrumbs set
	if ( titleState.length > wgJSBreadCrumbsMaxCrumbs ) {
		titleState = titleState.slice( titleState.length - wgJSBreadCrumbsMaxCrumbs );
		urlState = urlState.slice( urlState.length - wgJSBreadCrumbsMaxCrumbs );
		siteState = siteState.slice( siteState.length - wgJSBreadCrumbsMaxCrumbs );
	}

	//Insert SideBar List
	if( wgJSBreadCrumbsShowSidebar == true ) {
		if(skin == "vector") {
			postVector();
		} else if(skin == "modern") {
			postOther('#mw_portlets');
		} else if(skin == "monobook") {
			postOther('#column-one');
		} else if(skin == "cologneblue") {
			postOther('#quickbar');
		}

		for ( var i = titleState.length-1; i >= 0; i-- ) {
			if ( wgJSBreadCrumbsShowSiteName == true ) {
				$("#p-rv-list").append('<li><a href="'  + urlState[i]+'">'+ '(' + siteState[i] + ')' + titleState[i] + '</a></li>');
			} else {
				$("#p-rv-list").append('<li><a href="'  + urlState[i]+'">'+ titleState[i]+'</a></li>');
			}
		}
	} else {
		// Insert the span we are going to populate
		$( wgJSBreadCrumbsCSSSelector ).before( '<span id="mwext-bc" class="noprint plainlinks jsbc-breadcrumbs"></span>' );

		var mwextbc = $( "#mwext-bc" );

		// Add the bread crumb description
		mwextbc.append( wgJSBreadCrumbsLeadingDescription + ': ' );

		// Add the bread crumbs
		for ( var i = 0; i < titleState.length; i++ ) {
			if ( wgJSBreadCrumbsShowSiteName == true ) {
				urltoappend = '<a href="' + urlState[i] + '">' + '(' + siteState[i] + ') ' + titleState[i] + '</a> ';
			} else {
				urltoappend = '<a href="' + urlState[i] + '">' + titleState[i] + '</a> ';
			}

			// Only add the separator if this isn't the last title
			if ( i < titleState.length - 1 ) {
				urltoappend = urltoappend + wgJSBreadCrumbsSeparator + ' ';
			}
			mwextbc.append( urltoappend );
		}
	}
	// Save the bread crumb states to the cookies
	$.cookie( 'mwext-bc-title', titleState.join( '|' ), { path: wgJSBreadCrumbsCookiePath } );
	$.cookie( 'mwext-bc-url', urlState.join( '|' ), { path: wgJSBreadCrumbsCookiePath } );
	$.cookie( 'mwext-bc-site', siteState.join( '|' ), { path: wgJSBreadCrumbsCookiePath } );
});


function postVector() {
	$("#mw-panel").append("<div class='portal persistent' role='navigation' id='p-rv' aria-labelledby='p-rv-label'></div>");
	$("#p-rv").append("<h3 id='p-rv-label' tabindex='3'><a href='#' aria-haspopup='true' aria-controls='p-rv-list' role='button' aria-pressed='false' aria-expanded='true'>Last "+ wgJSBreadCrumbsMaxCrumbs + " Pages Viewed</a></h3>");
	$("#p-rv").append("<div class='body' style='display: block;'><ul id='p-rv-list'></ul>");
}

function postOther(id) {
	$(id).append("<div class='portlet' id='p-rv' role='navigation'></div>");
	$("#p-rv").append("<h3>Last " + wgJSBreadCrumbsMaxCrumbs+ "  Pages Viewed</h3><div class='pBody'><ul id='p-rv-list'></ul></div>");
}
