const jsbreadcrumbs_controller = ( function () {
	'use strict';

	return {

		initialize: function () {

			let config = mw.config.get( 'JSBreadCrumbs' ),
				pageName = config.PageName,
				action = config.Action,
				maxLength = config.MaxLength,
				siteMaxCrumbs = config.SiteMaxCrumbs,
				globalMaxCrumbs = config.GlobalMaxCrumbs,
				domain = config.Domain,
				showAction = config.ShowAction,
				showSite = config.ShowSite,
				horizontal = config.Horizontal,
				horizontalSeparator = config.HorizontalSeparator,
				cssSelector = config.CSSSelector,
				leadingDescription = config.LeadingDescription,

				siteName = mw.config.get( 'wgSiteName' ),

				// get the breadcrumbs from the cookie
				breadcrumbs = mw.cookie.get( 'mwext-jsbreadcrumbs' );
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
			let url = location.pathname + location.search,
				index = 0;
			while ( index < breadcrumbs.length ) {
				if ( breadcrumbs[ index ].url === url ) {
					breadcrumbs.splice( index, 1 );
				} else {
					index++;
				}
			}

			// add the current URL to the breadcrumb list if it points
			// to a valid page
			if ( pageName.slice( Math.max( 0, pageName.length - 8 ) ) !== 'Badtitle' ) {
				breadcrumbs.push( {
					url: url,
					title: pageName,
					action: action,
					siteName: siteName
				} );
			}

			// get the list of breadcrumbs to display
			const visibleCrumbs = [];
			for ( index = breadcrumbs.length - 1; index >= 0; index-- ) {
				if ( domain || breadcrumbs[ index ].siteName === siteName ) {
					if ( visibleCrumbs.length < siteMaxCrumbs ) {
						const breadcrumb = breadcrumbs[ index ];
						if ( !( 'action' in breadcrumb ) || showAction ||
							breadcrumb.action.length === 0 ) {
							let link = '<a href="' + breadcrumb.url + '">';
							if ( showSite ) {
								link += breadcrumb.siteName + ': ';
							}
							let title = breadcrumb.title;
							if ( title.length > maxLength ) {
								title = title.slice( 0, Math.max( 0, maxLength ) ) + '...';
							}
							if ( 'action' in breadcrumb && breadcrumb.action.length > 0 ) {
								title += ' [' + breadcrumb.action + ']';
							}
							link += title + '</a>';
							visibleCrumbs.push( link );
						} else {
							breadcrumbs.splice( index, 1 );
						}
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
			mw.cookie.set( 'mwext-jsbreadcrumbs', JSON.stringify( breadcrumbs ),
				{ path: '/', expires: 30, secure: window.location.protocol === 'https:', sameSite: 'Strict' } );

			let skin = mw.config.get( 'skin' ),

				selector;
			if ( horizontal ) {
				if ( skin in cssSelector ) {
					selector = cssSelector[ skin ];
				} else if ( skin === 'vector-2022' ) {
					selector = '#mw-content-subtitle';
				} else if ( skin === 'foreground' ) {
					selector = '#mw-js-message';
				} else {
					selector = '#top';
				}

				$( selector ).before(
					'<span id="mwext-bc" class="noprint plainlinks jsbc-breadcrumbs"></span>' );
				const mwextbc = $( '#mwext-bc' );
				mwextbc.append( leadingDescription + ' ' );
				for ( index = visibleCrumbs.length - 1; index >= 0; index-- ) {
					mwextbc.append( visibleCrumbs[ index ] );
					if ( index > 0 ) {
						mwextbc.append( ' ' + horizontalSeparator + ' ' );
					}
				}
			} else {
				if ( skin in cssSelector ) {
					selector = cssSelector[ skin ];
				} else if ( skin === 'vector' || skin === 'vector-2022' ) {
					selector = '#p-tb';
				} else if ( skin === 'modern' ) {
					selector = '#mw_portlets';
				} else if ( skin === 'monobook' ) {
					selector = '#column-one';
				} else if ( skin === 'cologneblue' ) {
					selector = '#quickbar';
				} else {
					selector = '#column-one';
				}

				if ( skin === 'vector' || skin === 'vector-2022' ) {
					$( selector ).after( "<div class='portal' id='p-rv' role='navigation' aria-labelledby='p-rv-label'></div>" );
					$( '#p-rv' ).append( "<h3 id='p-rv-label'>" + leadingDescription + '</h3>' );
					$( '#p-rv' ).append( "<div class='body' style='display: block;'><ul id='p-rv-list2'></ul>" );
				} else {
					$( selector ).append( "<div class='portlet' id='p-rv' role='navigation'></div>" );
					$( '#p-rv' ).append( '<h3>' + leadingDescription + "</h3><div class='pBody'><ul id='p-rv-list2'></ul></div>" );
				}

				for ( index = 0; index < visibleCrumbs.length; index++ ) {
					$( '#p-rv-list2' ).append( '<li>' + visibleCrumbs[ index ] + '</li>' );
				}
			}
		}
	};
}() );

window.JSBreadCrumbsController = jsbreadcrumbs_controller;

( function () {
	$( () => {
		if ( mw.config.exists( 'JSBreadCrumbs' ) ) {
			window.JSBreadCrumbsController.initialize();
		}
	} );
}() );
