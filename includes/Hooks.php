<?php

namespace MediaWiki\Extension\JSBreadCrumbs;

use Action;
use MediaWiki\Config\Config;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\PageProps;
use MediaWiki\Title\Title;
use MediaWiki\User\Options\UserOptionsManager;
use MediaWiki\User\User;
use Skin;

class Hooks implements
	\MediaWiki\Output\Hook\BeforePageDisplayHook,
	\MediaWiki\Preferences\Hook\GetPreferencesHook
{

	private Config $config;
	private PageProps $pageProps;
	private UserOptionsManager $userOptionsManager;

	public function __construct(
		Config $config,
		PageProps $pageProps,
		UserOptionsManager $userOptionsManager
	) {
		$this->config = $config;
		$this->pageProps = $pageProps;
		$this->userOptionsManager = $userOptionsManager;
	}

	/**
	 * Implements BeforePageDisplay hook.
	 * See https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 * Initializes variables to be passed to JavaScript.
	 *
	 * @param OutputPage $output OutputPage object
	 * @param Skin $skin Skin object that will be used to generate the page
	 */
	public function onBeforePageDisplay( $output, $skin ): void {
		$user = $output->getUser();
		if ( !$user->isAllowed( 'read' ) ||
			!$this->userOptionsManager->getOption( $user, 'jsbreadcrumbs-showcrumbs' ) ) {
			return;
		}

		$vars = [];

		$vars['SiteMaxCrumbs'] = $this->userOptionsManager->getOption( $user, 'jsbreadcrumbs-numberofcrumbs' );
		$vars['GlobalMaxCrumbs'] = $this->config->get( 'JSBreadCrumbsGlobalMaxCrumbs' );

		$vars['ShowAction'] = (bool)$this->userOptionsManager->getOption( $user, 'jsbreadcrumbs-showaction' );
		$vars['ShowSite'] = (bool)$this->userOptionsManager->getOption( $user, 'jsbreadcrumbs-showsite' );
		$vars['Domain'] = (bool)$this->userOptionsManager->getOption( $user, 'jsbreadcrumbs-domain' );

		// Allow localized horizontal separator to be overriden
		if ( $this->config->get( 'JSBreadCrumbsHorizontalSeparator' ) !== '' ) {
			$vars['HorizontalSeparator'] =
				$this->config->get( 'JSBreadCrumbsHorizontalSeparator' );
		} else {
			$vars['HorizontalSeparator'] =
				wfMessage( 'jsbreadcrumbs-horizontal-separator' )->escaped();
		}

		$horizontal =
			(bool)$this->userOptionsManager->getOption( $user, 'jsbreadcrumbs-horizontal' );
		$vars['Horizontal'] = $horizontal;
		if ( $horizontal ) {
			$vars['CSSSelector'] = $this->config->get( 'JSBreadCrumbsCSSSelectorHorizontal' );
			$vars['LeadingDescription'] = wfMessage( 'jsbreadcrumbs-intro-horizontal',
				$vars['SiteMaxCrumbs'] )->parse();
			$vars['MaxLength'] =
				$this->userOptionsManager->getOption( $user, 'jsbreadcrumbs-maxlength-horizontal' );
		} else {
			$vars['CSSSelector'] = $this->config->get( 'JSBreadCrumbsCSSSelectorVertical' );
			$vars['LeadingDescription'] = wfMessage( 'jsbreadcrumbs-intro-vertical',
				$vars['SiteMaxCrumbs'] )->parse();
			$vars['MaxLength'] =
				$this->userOptionsManager->getOption( $user, 'jsbreadcrumbs-maxlength-vertical' );
		}

		$title = $output->getTitle();
		if ( $this->getDisplayTitle( $title, $displayTitle ) ) {
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
	public function onGetPreferences( $user, &$preferences ) {
		$preferences['jsbreadcrumbs-showcrumbs'] = [
			'type' => 'toggle',
			'label-message' => 'prefs-jsbreadcrumbs-showcrumbs',
			'section' => 'rendering/jsbreadcrumbs'
		];

		$max = $this->config->get( 'JSBreadCrumbsGlobalMaxCrumbs' );
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
	private function getDisplayTitle( Title $title, &$displaytitle ): bool {
		$pagetitle = $title->getPrefixedText();
		$title = $title->createFragmentTarget( '' );
		if ( $title->canExist() ) {
			$values = $this->pageProps->getProperties( $title, 'displaytitle' );
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
