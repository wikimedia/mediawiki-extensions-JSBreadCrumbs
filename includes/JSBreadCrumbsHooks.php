<?php

use MediaWiki\MediaWikiServices;

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
		$userOptionsManager = MediaWikiServices::getInstance()->getUserOptionsManager();
		$user = $output->getUser();
		if ( !$user->isAllowed( 'read' ) ||
			!$userOptionsManager->getOption( $user, 'jsbreadcrumbs-showcrumbs' ) ) {
			return;
		}

		$vars = [];

		$vars['SiteMaxCrumbs'] = $userOptionsManager->getOption( $user, 'jsbreadcrumbs-numberofcrumbs' );
		$vars['GlobalMaxCrumbs'] = $GLOBALS['wgJSBreadCrumbsGlobalMaxCrumbs'];

		$vars['ShowAction'] = (bool)$userOptionsManager->getOption( $user, 'jsbreadcrumbs-showaction' );
		$vars['ShowSite'] = (bool)$userOptionsManager->getOption( $user, 'jsbreadcrumbs-showsite' );
		$vars['Domain'] = (bool)$userOptionsManager->getOption( $user, 'jsbreadcrumbs-domain' );

		// Allow localized horizontal separator to be overriden
		if ( $GLOBALS['wgJSBreadCrumbsHorizontalSeparator'] !== '' ) {
			$vars['HorizontalSeparator'] =
				$GLOBALS['wgJSBreadCrumbsHorizontalSeparator'];
		} else {
			$vars['HorizontalSeparator'] =
				wfMessage( 'jsbreadcrumbs-horizontal-separator' )->escaped();
		}

		$horizontal =
			(bool)$userOptionsManager->getOption( $user, 'jsbreadcrumbs-horizontal' );
		$vars['Horizontal'] = $horizontal;
		if ( $horizontal ) {
			$vars['CSSSelector'] = $GLOBALS['wgJSBreadCrumbsCSSSelectorHorizontal'];
			$vars['LeadingDescription'] = wfMessage( 'jsbreadcrumbs-intro-horizontal',
				$vars['SiteMaxCrumbs'] )->parse();
			$vars['MaxLength'] =
				$userOptionsManager->getOption( $user, 'jsbreadcrumbs-maxlength-horizontal' );
		} else {
			$vars['CSSSelector'] = $GLOBALS['wgJSBreadCrumbsCSSSelectorVertical'];
			$vars['LeadingDescription'] = wfMessage( 'jsbreadcrumbs-intro-vertical',
				$vars['SiteMaxCrumbs'] )->parse();
			$vars['MaxLength'] =
				$userOptionsManager->getOption( $user, 'jsbreadcrumbs-maxlength-vertical' );
		}

		$title = $output->getTitle();
		if ( self::getDisplayTitle( $title, $displayTitle ) ) {
			$pagename = $displayTitle;
		} else {
			$pagename = $title->getPrefixedText();
		}
		$vars['PageName'] = $pagename;

		$action = Action::getActionName( $output->getContext() );
		if ( $action === 'view' ) {
			$vars['Action'] = '';
		} else {
			$message = wfMessage( $action );
			if ( $message->isBlank() ) {
				$vars['Action'] = $action;
			} else {
				$vars['Action'] = $message->parse();
			}
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

		$max = $GLOBALS['wgJSBreadCrumbsGlobalMaxCrumbs'];
		$preferences['jsbreadcrumbs-numberofcrumbs'] = [
			'type' => 'int',
			'min' => 1,
			'max' => $max,
			'label-message' => 'prefs-jsbreadcrumbs-numberofcrumbs',
			'section' => 'rendering/jsbreadcrumbs'
		];

		$preferences['jsbreadcrumbs-horizontal'] = [
			'type' => 'toggle',
			'label-message' => 'prefs-jsbreadcrumbs-horizontal',
			'section' => 'rendering/jsbreadcrumbs'
		];

		$preferences['jsbreadcrumbs-maxlength-horizontal'] = [
			'type' => 'int',
			'min' => 0,
			'max' => 100,
			'label-message' => 'prefs-jsbreadcrumbs-maxlength-horizontal',
			'section' => 'rendering/jsbreadcrumbs'
		];

		$preferences['jsbreadcrumbs-maxlength-vertical'] = [
			'type' => 'int',
			'min' => 0,
			'max' => 100,
			'label-message' => 'prefs-jsbreadcrumbs-maxlength-vertical',
			'section' => 'rendering/jsbreadcrumbs'
		];

		$preferences['jsbreadcrumbs-showaction'] = [
			'type' => 'toggle',
			'label-message' => 'prefs-jsbreadcrumbs-showaction',
			'section' => 'rendering/jsbreadcrumbs'
		];

		$preferences['jsbreadcrumbs-showsite'] = [
			'type' => 'toggle',
			'label-message' => 'prefs-jsbreadcrumbs-showsite',
			'section' => 'rendering/jsbreadcrumbs'
		];

		$preferences['jsbreadcrumbs-domain'] = [
			'type' => 'toggle',
			'label-message' => 'prefs-jsbreadcrumbs-domain',
			'section' => 'rendering/jsbreadcrumbs'
		];
	}

	/**
	 * Get displaytitle page property text.
	 *
	 * @since 1.0
	 * @param Title $title the Title object for the page
	 * @param string &$displaytitle to return the display title, if set
	 * @return bool true if the page has a displaytitle page property that is
	 * different from the prefixed page name, false otherwise
	 */
	private static function getDisplayTitle( Title $title, &$displaytitle ) {
		$pagetitle = $title->getPrefixedText();
		$title = $title->createFragmentTarget( '' );
		if ( $title instanceof Title && $title->canExist() ) {
			$services = MediaWikiServices::getInstance();
			// TODO MW 1.36+ Simplify: MediaWikiServices::getPageProps() has been introduced in MW 1.36.
			$pageProps = $services->hasService( 'PageProps' ) ? $services->getPageProps() : PageProps::getInstance();
			$values = $pageProps->getProperties( $title, 'displaytitle' );
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
