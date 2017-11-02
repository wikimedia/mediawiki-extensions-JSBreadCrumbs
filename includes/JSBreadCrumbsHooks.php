<?php

class JSBreadCrumbsHooks {

	/**
	 * Implements BeforePageDisplay hook.
	 * See https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 * Initializes variables to be passed to JavaScript.
	 *
	 * @param OutputPage $output OutputPage object
	 * @param Skin $skin Skin object that will be used to generate the page
	 */
	public static function onBeforePageDisplay( $output, $skin ) {
		$user = $output->getUser();
		if ( !$user->isAllowed( 'read' ) ||
			!$user->getOption( "jsbreadcrumbs-showcrumbs" ) ) {
			return;
		}

		$vars = [];

		$vars['ShowSidebar'] =
			(bool)$user->getOption( "jsbreadcrumbs-showcrumbssidebar" );
		$vars['SiteMaxCrumbs'] = $user->getOption( "jsbreadcrumbs-numberofcrumbs" );

		// Allow localized separator to be overriden
		if ( $GLOBALS['wgJSBreadCrumbsSeparator'] !== '' ) {
			$separator = $GLOBALS['wgJSBreadCrumbsSeparator'];
		} else {
			$separator = wfMessage( "jsbreadcrumbs-separator" )->escaped();
		}
		$vars['Separator'] = $separator;

		$vars['GlobalMaxCrumbs'] = $GLOBALS['wgJSBreadCrumbsGlobalMaxCrumbs'];

		$vars['LeadingDescription'] =
			wfMessage( "jsbreadcrumbs-intro", $vars['SiteMaxCrumbs'] )->plain();

		$title = $output->getTitle();
		if ( self::getDisplayTitle( $title, $displayTitle ) ) {
			$vars['PageName'] = $displayTitle;
		} else {
			$vars['PageName'] = $title->getPrefixedText();
		}

		$output->addJSConfigVars( 'JSBreadCrumbs', $vars );
		$output->addModules( 'ext.JSBreadCrumbs' );
	}

	/**
	 * GetPreferences hook
	 *
	 * @param User $user User whose preferences are being modified
	 * @param array &$preferences Preferences description array
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		$preferences['jsbreadcrumbs-showcrumbs'] = [
			'type' => 'toggle',
			'label-message' => 'prefs-jsbreadcrumbs-showcrumbs',
			'section' => 'rendering/jsbreadcrumbs'
		];

		$preferences['jsbreadcrumbs-showcrumbssidebar'] = [
			'type' => 'toggle',
			'label-message' => 'prefs-jsbreadcrumbs-showcrumbssidebar',
			'section' => 'rendering/jsbreadcrumbs'
		];

		$max = $GLOBALS['wgJSBreadCrumbsGlobalMaxCrumbs'];
		$preferences['jsbreadcrumbs-numberofcrumbs'] = [
			'type' => 'int',
			'min' => 1,
			'max' => $max,
			'label-message' => 'prefs-jsbreadcrumbs-numberofcrumbs',
			'section' => 'rendering/jsbreadcrumbs'
		];
	}

	/**
	 * Get displaytitle page property text.
	 *
	 * @since 1.0
	 * @param Title $title the Title object for the page
	 * @param string &$displaytitle to return the display title, if set
	 * @return boolean true if the page has a displaytitle page property that is
	 * different from the prefixed page name, false otherwise
	 */
	private static function getDisplayTitle( Title $title, &$displaytitle ) {
		$pagetitle = $title->getPrefixedText();
		// remove fragment
		$title = Title::newFromText( $pagetitle );
		if ( $title instanceof Title ) {
			$values = PageProps::getInstance()->getProperties( $title, 'displaytitle' );
			$id = $title->getArticleID();
			if ( array_key_exists( $id, $values ) ) {
				$value = $values[$id];
				if ( trim( str_replace( '&#160;', '', strip_tags( $value ) ) ) !== '' &&
					$value !== $pagetitle ) {
					$displaytitle = $value;
					return true;
				}
			}
		}
		return false;
	}
}
