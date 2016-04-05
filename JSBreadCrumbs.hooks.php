<?php
class JSBreadCrumbsHooks {
	/**
	 * BeforePageDisplay hook
	 */
	public static function addResources( $out ) {
		global $wgExtensionAssetsPath;
		global $wgUser;

		if ( $wgUser->isAllowed('read') ) {
			if ( self::enableBreadCrumbs() ) {
				$out->addModules( 'ext.JSBreadCrumbs' );
			}
		}

		return true;
	}

	/**
	 * MakeGlobalVariablesScript hook
	 *
	 * @param array $vars
	 * @param OutputPage $outPage
	 *
	 * @return bool
	 */
	public static function addJSVars( $vars, $outPage ) {
		global $wgJSBreadCrumbsSeparator, $wgJSBreadCrumbsCookiePath, $wgJSBreadCrumbsCSSSelector, $wgJSBreadCrumbsSkinCSSArray,
		$wgJSBreadCrumbsSkinCSSSelector, $wgUser;

		if ( !self::enableBreadCrumbs() ) {
			return true;
		}

		// Allow localized separator to be overriden
		if ( $wgJSBreadCrumbsSeparator !== '' ) {
			$separator = $wgJSBreadCrumbsSeparator;
		} else {
			$separator = wfMessage( "jsbreadcrumbs-separator" )->escaped();
		}

		$variables = array();

		$skinName = $outPage->getSkin()->getSkinName();
		if(array_key_exists($skinName, $wgJSBreadCrumbsSkinCSSArray)) {
			$outPage->addJsConfigVars('wgJSBreadCrumbsCSSSelector', $wgJSBreadCrumbsSkinCSSArray[$skinName]);
		} else {
			$outPage->addJsConfigVars('wgJSBreadCrumbsCSSSelector', $wgJSBreadCrumbsSkinCSSSelector);
		}

			$outPage->addJsConfigVars('wgJSBreadCrumbsMaxCrumbs', $wgUser->getOption( "jsbreadcrumbs-numberofcrumbs" ));
			$outPage->addJsConfigVars('wgJSBreadCrumbsShowSidebar', $wgUser->getOption( "jsbreadcrumbs-showcrumbssidebar" ));
			$outPage->addJsConfigVars('wgJSBreadCrumbsPervasiveWikiFarm', $wgUser->getOption ("jsbreadcrumbs-pervasivewikifarm" ));
			$outPage->addJsConfigVars('wgJSBreadCrumbsSeparator', $separator);
			$outPage->addJsConfigVars('wgJSBreadCrumbsCookiePath', $wgJSBreadCrumbsCookiePath);
			$outPage->addJsConfigVars('wgJSBreadCrumbsLeadingDescription', wfMessage( "jsbreadcrumbs-leading-description" )->escaped());
			$outPage->addJsConfigVars('wgJSBreadCrumbsShowSiteName', $wgUser->getOption( "jsbreadcrumbs-showsite" ));


		global $wgTitle;
		if ( self::getDisplayTitle($wgTitle, $displayTitle) ) {

			if(trim( str_replace( '&nbsp;', '', strip_tags( $displayTitle ) ) ) != '' ) {
				$outPage->addJsConfigVars('wgJSBreadCrumbsPageName', $displayTitle );
			} else {
				$outPage->addJsConfigVars('wgJSBreadCrumbsPageName', $wgTitle->getPrefixedText());
			}
		} else {
			$outPage->addJsConfigVars('wgJSBreadCrumbsPageName', $wgTitle->getPrefixedText());
		}

		$vars = array_merge( $vars, $variables );

		return true;
	}

	/**
	 * GetPreferences hook
	 *
	 * Add module-releated items to the preferences
	 */
	public static function addPreferences( $user, $defaultPreferences ) {
		$defaultPreferences['jsbreadcrumbs-showcrumbs'] = array(
			'type' => 'toggle',
			'label-message' => 'prefs-jsbreadcrumbs-showcrumbs',
			'section' => 'rendering/jsbreadcrumbs',
		);

		$defaultPreferences['jsbreadcrumbs-showcrumbssidebar'] = array(
			'type' => 'toggle',
			'label-message' => 'prefs-jsbreadcrumbs-showcrumbssidebar',
			'section' => 'rendering/jsbreadcrumbs',
		);

		$defaultPreferences['jsbreadcrumbs-showsite'] = array(
			'type' => 'toggle',
			'label-message' => 'prefs-jsbreadcrumbs-showsite',
			'section' => 'rendering/jsbreadcrumbs',
		);

		$defaultPreferences['jsbreadcrumbs-numberofcrumbs'] = array(
			'type' => 'int',
			'min' => 1,
			'max' => 20,
			'section' => 'rendering/jsbreadcrumbs',
			'help' => wfMessage( 'prefs-jsbreadcrumbs-numberofcrumbs-max' )->text(),
			'label-message' => 'prefs-jsbreadcrumbs-numberofcrumbs',
		);

		return true;
	}

  	public static function getDisplayTitle( Title $title, &$displayTitle ) {
    	$id = $title->getArticleID();
 
    	$dbr = wfGetDB( DB_SLAVE );
    	$result = $dbr->select(
      		'page_props',
      		array( 'pp_value' ),
      		array(
        		'pp_page' => $id,
        		'pp_propname' => 'displaytitle'
      		),
      		__METHOD__
    	);
 
	    if ( $result->numRows() > 0 ) {
    		$row = $result->fetchRow();
      		$displayTitle = $row['pp_value'];

			if($displayTitle == '') {
				return false;
			}
      		return true;
    	}
 
    	return false;
  	}

	static function enableBreadCrumbs() {
		global $wgUser;

		// Ensure we only enable bread crumbs if we are using vector and
		// the user has them enabled
		if ( $wgUser->getOption( "jsbreadcrumbs-showcrumbs" ) ) {
			return true;
		}
	}
}
