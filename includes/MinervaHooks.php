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
	 * @param ResourceLoader &$resourceLoader
	 */
	public static function onResourceLoaderTestModules( array &$testModules,
		ResourceLoader &$resourceLoader
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

				'resources/skins.minerva.scripts/pageIssuesLogger.js',
				'resources/skins.minerva.scripts/pageIssuesParser.js',
				'resources/skins.minerva.scripts/DownloadIcon.js',
				'resources/skins.minerva.scripts/AB.js',
				'resources/skins.minerva.scripts/PageIssuesOverlay.js',
				'resources/skins.minerva.scripts/pageIssues.js',
				// test files
				'tests/qunit/skins.minerva.scripts/DownloadIcon.test.js',
				'tests/qunit/skins.minerva.scripts/pageIssuesParser.test.js',
				'tests/qunit/skins.minerva.scripts/AB.test.js',
				'tests/qunit/skins.minerva.scripts/PageIssuesOverlay.test.js',
				'tests/qunit/skins.minerva.scripts/pageIssues.test.js',
				'tests/qunit/skins.minerva.notifications.badge/NotificationBadge.test.js'
			],
		];

		// Expose templates module
		$testModules['qunit']["tests.skins.minerva"] = $testModule;
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
						'skins.minerva.icons.images.variants',
						'skins.minerva.mainMenu.icons',
						'skins.minerva.mainMenu.styles',
					] );
					$out->addModules( [
						'skins.minerva.mainMenu'
					] );
					break;
				case 'Userlogin':
				case 'CreateAccount':
					// FIXME: Note mobile.ajax.styles should not be necessary here.
					// It's used by the Captcha extension (see T162196)
					$out->addModuleStyles( [ 'mobile.ajax.styles' ] );
					// Add default warning message to Special:UserLogin and Special:UserCreate
					// if no warning message set.
					if (
						!$request->getVal( 'warning', null ) &&
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
			$featureManager = \MediaWiki\MediaWikiServices::getInstance()
				->getService( 'MobileFrontend.FeaturesManager' );

			$isBeta = $mobileContext->isBetaGroupMember();
			$skin->setSkinOptions( [
				SkinMinerva::OPTIONS_MOBILE_BETA
					=> $isBeta,
				SkinMinerva::OPTION_CATEGORIES
					=> $featureManager->isFeatureAvailableInContext( 'MinervaShowCategoriesButton',
							$mobileContext ),
				SkinMinerva::OPTION_BACK_TO_TOP
					=> $featureManager->isFeatureAvailableInContext( 'MinervaEnableBackToTop', $mobileContext ),
				SkinMinerva::OPTION_SHARE_BUTTON
					=> $featureManager->isFeatureAvailableInContext( 'MinervaShareButton', $mobileContext ),
				SkinMinerva::OPTION_TOGGLING => true,
				SkinMinerva::OPTION_MOBILE_OPTIONS => true,
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
	 * @return bool
	 */
	public static function onResourceLoaderGetConfigVars( &$vars ) {
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

		return true;
	}
}
