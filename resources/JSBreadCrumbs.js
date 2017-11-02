var jsbreadcrumbs_controller = ( function( mw, $ ) {
	'use strict';

	return {

		initialize: function() {

			var config = mw.config.get( 'JSBreadCrumbs' );
			var siteMaxCrumbs = config.SiteMaxCrumbs;
			var direction = config.Direction;
			var showSite = config.ShowSite;
			var showGlobal = config.ShowGlobal;
			var globalMaxCrumbs = config.GlobalMaxCrumbs;
			var separator = config.Separator;
			var leadingDescription = config.LeadingDescription;
			var pageName = config.PageName;

			var siteName = mw.config.get( 'wgSiteName' );

			// get the breadcrumbs from the cookie
			var breadcrumbs = $.cookie( 'mwext-jsbreadcrumbs' );
			if ( breadcrumbs ) {
				try {
					breadcrumbs = JSON.parse( breadcrumbs );
				} catch ( e ) {
					breadcrumbs = [];
				}
			} else {
				breadcrumbs = [];
			}

			// remove this URL from the breadcrumb list if it is already in it
			var url = location.pathname + location.search;
			var index = 0;
			while ( index < breadcrumbs.length ) {
				if ( breadcrumbs[index].url === url ) {
					breadcrumbs.splice( index, 1 );
				} else {
					index++;
				}
			}

			// add the current URL to the breadcrumb list if it points
			// to a valid page
			if ( !pageName.endsWith( "Badtitle" ) ) {
				breadcrumbs.push( {
					url: url,
					title: pageName,
					siteName: siteName
				} );
			}

			// get the list of breadcrumbs to display
			var visibleCrumbs = [];
			for (index = breadcrumbs.length - 1; index >= 0; index--) {
				if ( showGlobal || breadcrumbs[index].siteName === siteName ) {
					if ( visibleCrumbs.length < siteMaxCrumbs ) {
						var breadcrumb = breadcrumbs[index];
						var link = '<a href="' + breadcrumb.url + '">';
						if ( showSite ) {
							link += breadcrumb.siteName + ': ';
						}
						link += breadcrumb.title + '</a>';
						visibleCrumbs.push( link );
					} else {
						breadcrumbs.splice( index, 1 );
					}
				}
			}

			// truncate the breadcrumb list if necessary
			if ( breadcrumbs.length > globalMaxCrumbs ) {
				breadcrumbs = breadcrumbs.slice( breadcrumbs.length - globalMaxCrumbs );
			}

			// save the breadcrumbs to the cookie
			$.cookie( 'mwext-jsbreadcrumbs', JSON.stringify( breadcrumbs ),
				{ path: '/', expires: 30 } );

			var skin = mw.config.get( 'skin' );

			// insert breadcrumbs in the sidebar
			if( direction === 'vertical' ) {
				if ( skin === "vector" ) {
					this.postVector( leadingDescription );
				} else if ( skin === "modern" ) {
					this.postOther( '#mw_portlets', leadingDescription );
				} else if ( skin === "monobook" ) {
					this.postOther( '#column-one', leadingDescription );
				} else if ( skin === "cologneblue" ) {
					this.postOther( '#quickbar', leadingDescription );
				}

				for ( index = 0; index < visibleCrumbs.length; index++ ) {
						$("#p-rv-list2").append('<li>' + visibleCrumbs[index] + '</li>');
				}

			// insert breadcrumbs elsewhere
			} else {

				var cssSelector;
				if ( skin === "foreground" ) {
					cssSelector = "#mw-js-message";
				} else {
					cssSelector = "#top";
				}

				$( cssSelector ).before(
					'<span id="mwext-bc" class="noprint plainlinks jsbc-breadcrumbs"></span>' );
				var mwextbc = $( "#mwext-bc" );
				mwextbc.append( leadingDescription + ': ' );
				for ( index = visibleCrumbs.length - 1; index >= 0; index-- ) {
					mwextbc.append( visibleCrumbs[index] );
					if ( index > 0 ) {
						mwextbc.append( ' ' + separator + ' ' );
					}
				}
			}
		},

		postVector: function( leadingDescription ) {
			$("#mw-panel").append("<div class='portal persistent' role='navigation' id='p-rv' aria-labelledby='p-rv-label'></div>");
			$("#p-rv").append("<h3 id='p-rv-label' tabindex='3'><a href='#' aria-haspopup='true' aria-controls='p-rv-list2' role='button' aria-pressed='false' aria-expanded='true'>" + leadingDescription + "</a></h3>");
			$("#p-rv").append("<div class='body' style='display: block;'><ul id='p-rv-list2'></ul>");
		},

		postOther: function(id, leadingDescription ) {
			$(id).append("<div class='portlet' id='p-rv' role='navigation'></div>");
			$("#p-rv").append("<h3>" + leadingDescription + "</h3><div class='pBody'><ul id='p-rv-list'></ul></div>");
		}
	};
}( mediaWiki, jQuery ) );

window.JSBreadCrumbsController = jsbreadcrumbs_controller;

( function( mw, $ ) {
	$( document )
		.ready( function() {
			if ( mw.config.exists( 'JSBreadCrumbs' ) ) {
				window.JSBreadCrumbsController.initialize();
			}
		} );
}( mediaWiki, jQuery ) );
