<?php
class JSBreadCrumbsHooks {
	/**
	 * BeforePageDisplay hook
	 */
	public static function addResources( $out ) {
		global $wgExtensionAssetsPath;

		if ( self::enableBreadCrumbs() ) {
			$out->addModules( 'ext.JSBreadCrumbs' );		
		}

		return true;
	}

	/**
	 * MakeGlobalVariablesScript hook
	 */
	public static function addJSVars( $vars, $outPage ) {
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
			//$variables['wgJSBreadCrumbsCSSSelector'] = $wgJSBreadCrumbsSkinCSSArray[$skinName];
			$outPage->addJsConfigVars('wgJSBreadCrumbsCSSSelector', $wgJSBreadCrumbsSkinCSSArray[$skinName]);
		} else {
			//$variables['wgJSBreadCrumbsCSSSelector'] = $wgJSBreadCrumbsCSSSelector;
			$outPage->addJsConfigVars('wgJSBreadCrumbsCSSSelector', $wgJSBreadCrumbsSkinCSSSelector);
		}

		//$variables['wgJSBreadCrumbsMaxCrumbs'] = $wgUser->getOption( "jsbreadcrumbs-numberofcrumbs" );
		//$variables['wgJSBreadCrumbsShowSidebar'] = $wgUser->getOption( "jsbreadcrumbs-showcrumbssidebar" );
		//$variables['wgJSBreadCrumbsPervasiveWikiFarm'] = $wgUser->getOption( "jsbreadcrumbs-pervasivewikifarm" );
		//$variables['wgJSBreadCrumbsSeparator'] = $separator;
		//$variables['wgJSBreadCrumbsCookiePath'] = $wgJSBreadCrumbsCookiePath;
		//$variables['wgJSBreadCrumbsLeadingDescription'] =
		//	wfMessage( "jsbreadcrumbs-leading-description" )->escaped();
		//$variables['wgJSBreadCrumbsShowSiteName'] = $wgUser->getOption( "jsbreadcrumbs-showsite" );

			$outPage->addJsConfigVars('wgJSBreadCrumbsMaxCrumbs', $wgUser->getOption( "jsbreadcrumbs-numberofcrumbs" ));
			$outPage->addJsConfigVars('wgJSBreadCrumbsShowSidebar', $wgUser->getOption( "jsbreadcrumbs-showcrumbssidebar" ));
			$outPage->addJsConfigVars('wgJSBreadCrumbsPervasiveWikiFarm', $wgUser->getOption ("jsbreadcrumbs-pervasivewikifarm" ));
			$outPage->addJsConfigVars('wgJSBreadCrumbsSeparator', $separator);
			$outPage->addJsConfigVars('wgJSBreadCrumbsCookiePath', $wgJSBreadCrumbsCookiePath);
			$outPage->addJsConfigVars('wgJSBreadCrumbsLeadingDescription', wfMessage( "jsbreadcrumbs-leading-description" )->escaped());
			$outPage->addJsConfigVars('wgJSBreadCrumbsShowSiteName', $wgUser->getOption( "jsbreadcrumbs-showsite" ));

		global $wgTitle;
		if ( class_exists( "SemanticTitle" ) ) {
			//$variables['wgJSBreadCrumbsPageName'] = SemanticTitle::getText( $wgTitle );
			$outPage->addJsConfigVars('wgJSBreadCrumbsPageName', SemanticTitle::getText( $wgTitle ));
		} else {
			//$variables['wgJSBreadCrumbsPageName'] = $wgTitle->getPrefixedText();
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

	static function enableBreadCrumbs() {
		global $wgUser;

		// Ensure we only enable bread crumbs if we are using vector and
		// the user has them enabled
		if ( $wgUser->getOption( "jsbreadcrumbs-showcrumbs" ) ) {
			return true;
		}
	}
}
