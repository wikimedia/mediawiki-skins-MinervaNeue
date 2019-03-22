<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

use MediaWiki\MediaWikiServices;

/**
 * Hook handlers for Minerva skin.
 *
 * Hook handler method names should be in the form of:
 *	on<HookName>()
 */
class MinervaHooks {
	/**
	 * Register mobile web beta features
	 * @see https://www.mediawiki.org/wiki/
	 *  Extension:MobileFrontend/MobileFrontendFeaturesRegistration
	 *
	 * @param MobileFrontend\Features\FeaturesManager $featureManager
	 */
	public static function onMobileFrontendFeaturesRegistration( $featureManager ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'minerva' );

		try {
			$featureManager->registerFeature(
				new MobileFrontend\Features\Feature(
					'MinervaShowCategoriesButton',
					'skin-minerva',
					$config->get( 'MinervaShowCategoriesButton' )
				)
			);
			$featureManager->registerFeature(
				new MobileFrontend\Features\Feature(
					'MinervaEnableBackToTop',
					'skin-minerva',
					$config->get( 'MinervaEnableBackToTop' )
				)
			);
			$featureManager->registerFeature(
				new MobileFrontend\Features\Feature(
					'MinervaShareButton',
					'skin-minerva',
					$config->get( 'MinervaShowShareButton' )
				)
			);
			$featureManager->registerFeature(
				new MobileFrontend\Features\Feature(
					'MinervaPageIssuesNewTreatment',
					'skin-minerva',
					$config->get( 'MinervaPageIssuesNewTreatment' )
				)
			);
			$featureManager->registerFeature(
				new MobileFrontend\Features\Feature(
					'MinervaTalkAtTop',
					'skin-minerva',
					$config->get( 'MinervaTalkAtTop' )
				)
			);
			$featureManager->registerFeature(
				new MobileFrontend\Features\Feature(
					'MinervaHistoryInPageActions',
					'skin-minerva',
					$config->get( 'MinervaHistoryInPageActions' )
				)
			);
		} catch ( RuntimeException $e ) {
			// features already registered...
			// due to a bug it's possible for this to run twice
			// https://phabricator.wikimedia.org/T165068
		}
	}

	/**
	 * ResourceLoaderTestModules hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderTestModules
	 *
	 * @param array &$testModules
	 * @param ResourceLoader $resourceLoader
	 */
	public static function onResourceLoaderTestModules( array &$testModules,
		ResourceLoader $resourceLoader
	) {
		$testModule = [
			'dependencies' => [
				'mobile.startup',
				'skins.minerva.notifications.badge',
				'mediawiki.user',
				'mediawiki.experiments'
			],
			'localBasePath' => dirname( __DIR__ ),
			'remoteSkinPath' => 'MinervaNeue',
			'targets' => [ 'mobile', 'desktop' ],
			'scripts' => [
				// additional scaffolding (minus initialisation scripts)
				'tests/qunit/skins.minerva.scripts/stubs.js',

				'resources/skins.minerva.scripts/pageIssuesParser.js',
				'resources/skins.minerva.scripts/downloadPageAction.js',
				'resources/skins.minerva.scripts/AB.js',
				'resources/skins.minerva.scripts/page-issues/overlay/IssueNotice.js',
				'resources/skins.minerva.scripts/page-issues/overlay/IssueList.js',
				'resources/skins.minerva.scripts/page-issues/overlay/pageIssuesOverlay.js',
				'resources/skins.minerva.scripts/page-issues/page/PageIssueLearnMoreLink.js',
				'resources/skins.minerva.scripts/page-issues/page/PageIssueLink.js',
				'resources/skins.minerva.scripts/page-issues/page/pageIssueFormatter.js',
				'resources/skins.minerva.scripts/pageIssues.js',
				'resources/skins.minerva.scripts/UriUtil.js',
				'resources/skins.minerva.scripts/TitleUtil.js',
				// test files
				'tests/qunit/skins.minerva.scripts/downloadPageAction.test.js',
				'tests/qunit/skins.minerva.scripts/pageIssuesParser.test.js',
				'tests/qunit/skins.minerva.scripts/AB.test.js',
				'tests/qunit/skins.minerva.scripts/pageIssues.test.js',
				'tests/qunit/skins.minerva.scripts/UriUtil.test.js',
				'tests/qunit/skins.minerva.scripts/TitleUtil.test.js',
				'tests/qunit/skins.minerva.notifications.badge/NotificationBadge.test.js'
			],
		];

		// Expose templates module
		$testModules['qunit']['tests.skins.minerva'] = $testModule;
	}

	/**
	 * Invocation of hook SpecialPageBeforeExecute
	 *
	 * We use this hook to ensure that login/account creation pages
	 * are redirected to HTTPS if they are not accessed via HTTPS and
	 * $wgSecureLogin == true - but only when using the
	 * mobile site.
	 *
	 * @param SpecialPage $special
	 * @param string $subpage
	 */
	public static function onSpecialPageBeforeExecute( SpecialPage $special, $subpage ) {
		$name = $special->getName();
		$out = $special->getOutput();
		$skin = $out->getSkin();
		$request = $special->getRequest();

		if ( $skin instanceof SkinMinerva ) {
			switch ( $name ) {
				case 'MobileMenu':
					$out->addModuleStyles( [
						'skins.minerva.mainMenu.icons',
						'skins.minerva.mainMenu.styles',
					] );
					break;
				case 'Userlogin':
				case 'CreateAccount':
					// Add default warning message to Special:UserLogin and Special:UserCreate
					// if no warning message set.
					if (
						!$request->getVal( 'warning' ) &&
						!$special->getUser()->isLoggedIn() &&
						!$request->wasPosted()
					) {
						$request->setVal( 'warning', 'mobile-frontend-generic-login-new' );
					}
					break;
			}
		}
	}

	/**
	 * BeforePageDisplayMobile hook handler.
	 *
	 * @param MobileContext $mobileContext
	 * @param Skin $skin
	 */
	public static function onRequestContextCreateSkinMobile(
		MobileContext $mobileContext, Skin $skin
	) {
		// setSkinOptions is not available
		if ( $skin instanceof SkinMinerva ) {
			$services = \MediaWiki\MediaWikiServices::getInstance();
			$featureManager = $services
				->getService( 'MobileFrontend.FeaturesManager' );
			$userMode = $services->getService( 'MobileFrontend.AMC.UserMode' );

			$isBeta = $mobileContext->isBetaGroupMember();
			$skin->setSkinOptions( [
				SkinMinerva::OPTION_AMC => $userMode->isEnabled(),
				SkinMinerva::OPTIONS_TALK_AT_TOP => $featureManager->isFeatureAvailableForCurrentUser(
					'MinervaTalkAtTop'
				),
				SkinMinerva::OPTIONS_MOBILE_BETA
					=> $isBeta,
				SkinMinerva::OPTION_CATEGORIES
					=> $featureManager->isFeatureAvailableInContext( 'MinervaShowCategoriesButton',
							$mobileContext ),
				SkinMinerva::OPTION_BACK_TO_TOP
					=> $featureManager->isFeatureAvailableInContext( 'MinervaEnableBackToTop', $mobileContext ),
				SkinMinerva::OPTION_PAGE_ISSUES
					=> $featureManager->isFeatureAvailableInContext(
							'MinervaPageIssuesNewTreatment', $mobileContext
						),
				SkinMinerva::OPTION_SHARE_BUTTON
					=> $featureManager->isFeatureAvailableInContext( 'MinervaShareButton', $mobileContext ),
				SkinMinerva::OPTION_TOGGLING => true,
				SkinMinerva::OPTION_MOBILE_OPTIONS => true,
				SkinMinerva::OPTIONS_HISTORY_PAGE_ACTIONS => $featureManager->isFeatureAvailableForCurrentUser(
					'MinervaHistoryInPageActions'
				),
			] );
		}
	}
	/**
	 * ResourceLoaderGetConfigVars hook handler.
	 * Used for setting JS variables which are pulled in dynamically with RL
	 * instead of embedded directly on the page with a script tag.
	 * These vars have a shorter cache-life than those in `getSkinConfigVariables`.
	 *
	 * @param array &$vars Array of variables to be added into the output of the RL startup module.
	 * @param string $skin
	 */
	public static function onResourceLoaderGetConfigVars( &$vars, $skin ) {
		if ( $skin === 'minerva' ) {
			$config = MediaWikiServices::getInstance()->getConfigFactory()
				->makeConfig( 'minerva' );
			$vars += [
				'wgMinervaSchemaMainMenuClickTrackingSampleRate' =>
					$config->get( 'MinervaSchemaMainMenuClickTrackingSampleRate' ),
				'wgMinervaABSamplingRate' => $config->get( 'MinervaABSamplingRate' ),
				'wgMinervaCountErrors' => $config->get( 'MinervaCountErrors' ),
				'wgMinervaErrorLogSamplingRate' => $config->get( 'MinervaErrorLogSamplingRate' ),
				'wgMinervaReadOnly' => wfReadOnly()
			];
		}
	}
}
