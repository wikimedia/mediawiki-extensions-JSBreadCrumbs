<?php
# Copyright (C) 2010 Ryan Lane <http://www.mediawiki.org/wiki/User:Ryan_lane>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
# http://www.gnu.org/copyleft/gpl.html

//BreadCrumbsSeparator default set via localization
$wgJSBreadCrumbsSeparator = '';
$wgJSBreadCrumbsCookiePath = '/';
$wgJSBreadCrumbsCSSSelector = "#top";
$wgJSBreadCrumbsSkinCSSArray = array (
    "vector" => "#top",
    "foreground" => "#mw-js-message"
);

$wgDefaultUserOptions['jsbreadcrumbs-showcrumbs'] = true;
$wgDefaultUserOptions['jsbreadcrumbs-showcrumbssidebar'] = false;
$wgDefaultUserOptions['jsbreadcrumbs-showsite'] = false;
$wgDefaultUserOptions['jsbreadcrumbs-pervasivewikifarm'] = false;
$wgDefaultUserOptions['jsbreadcrumbs-numberofcrumbs'] = 5;

// Sets Credits
$wgExtensionCredits['other'][] = array(
		'path' => __FILE__,
		'name' => 'JSBreadCrumbs',
		'author' => 'Ryan Lane',
		'version' => '0.6.4',
		'url' => 'https://www.mediawiki.org/wiki/Extension:JSBreadCrumbs',
		'descriptionmsg' => 'jsbreadcrumbs-desc',
);

// Adds Autoload Classes
//$wgAutoloadClasses['JSBreadCrumbsHooks'] =
//		dirname( __FILE__ ) . "/JSBreadCrumbs.hooks.php";

$wgResourceModules['ext.JSBreadCrumbs'] = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'JSBreadCrumbs',
	'scripts' => array( 'js/BreadCrumbs.js' ),
	'styles' => array( 'css/BreadCrumbs.css' ),
	'dependencies' => array(
		'jquery.cookie',
	)
);

$wgAutoloadClasses['JSBreadCrumbsHooks'] =
		dirname( __FILE__ ) . "/JSBreadCrumbs.hooks.php";


// Adds Internationalized Messages
$wgMessagesDirs['JSBreadCrumbs'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['JSBreadCrumbs'] =
		dirname( __FILE__ ) . "/JSBreadCrumbs.i18n.php";

// Registers Hooks
$wgHooks['BeforePageDisplay'][] = 'JSBreadCrumbsHooks::addResources';
$wgHooks['MakeGlobalVariablesScript'][] = 'JSBreadCrumbsHooks::addJSVars';
$wgHooks['GetPreferences'][] = 'JSBreadCrumbsHooks::addPreferences';
