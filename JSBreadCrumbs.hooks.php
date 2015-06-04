<?php
class JSBreadCrumbsHooks {
	/**
	 * BeforePageDisplay hook
	 */
	public static function addResources( $out ) {
		global $wgExtensionAssetsPath;

		if ( self::enableBreadCrumbs() ) {
			$out->addScriptFile( "$wgExtensionAssetsPath/JSBreadCrumbs/js/jquery.cookie.js", 7 );
			$out->addScriptFile( "$wgExtensionAssetsPath/JSBreadCrumbs/js/BreadCrumbs.js", 7 );
			$out->addExtensionStyle( "$wgExtensionAssetsPath/JSBreadCrumbs/css/BreadCrumbs.css?1" );
		}

		return true;
	}

	/**
	 * MakeGlobalVariablesScript hook
	 */
	public static function addJSVars( $vars ) {
		global $wgJSBreadCrumbsSeparator, $wgJSBreadCrumbsCookiePath, $wgJSBreadCrumbsCSSSelector, $wgJSBreadCrumbsSkinCSSArray;
		global $wgUser;

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

		$skinName = $wgUser->getSkin()->getSkinName();
		if(array_key_exists($skinName, $wgJSBreadCrumbsSkinCSSArray)) {
			$variables['wgJSBreadCrumbsCSSSelector'] = $wgJSBreadCrumbsSkinCSSArray[$skinName];
		} else {
			$variables['wgJSBreadCrumbsCSSSelector'] = $wgJSBreadCrumbsCSSSelector;
		}

		$variables['wgJSBreadCrumbsMaxCrumbs'] = $wgUser->getOption( "jsbreadcrumbs-numberofcrumbs" );
		$variables['wgJSBreadCrumbsShowSidebar'] = $wgUser->getOption( "jsbreadcrumbs-showcrumbssidebar" );
		$variables['wgJSBreadCrumbsSeparator'] = $separator;
		$variables['wgJSBreadCrumbsCookiePath'] = $wgJSBreadCrumbsCookiePath;
		$variables['wgJSBreadCrumbsLeadingDescription'] =
			wfMessage( "jsbreadcrumbs-leading-description" )->escaped();
		$variables['wgJSBreadCrumbsShowSiteName'] = $wgUser->getOption( "jsbreadcrumbs-showsite" );

		global $wgTitle;
		if ( class_exists( "SemanticTitle" ) ) {
			$variables['wgJSBreadCrumbsPageName'] = SemanticTitle::getText( $wgTitle );
		} else {
			$variables['wgJSBreadCrumbsPageName'] = $wgTitle->getPrefixedText();
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

	static function enableBreadCrumbs() {
		global $wgUser;

		// Ensure we only enable bread crumbs if we are using vector and
		// the user has them enabled
		if ( $wgUser->getOption( "jsbreadcrumbs-showcrumbs" ) ) {
			return true;
		}
	}
}
