<?php

namespace MediaWiki\Minerva;

use MediaWiki\Config\Config;
use MediaWiki\Skin\Skin;
use MobileContext;
use MobileFrontend\Features\Feature;
use MobileFrontend\Features\FeaturesManager;
use MobileFrontend\Hooks\MobileFrontendFeaturesRegistrationHook;
use MobileFrontend\Hooks\RequestContextCreateSkinMobileHook;
use RuntimeException;

/**
 * Hooks from MobileFrontend extension,
 * which is optional to use with this skin.
 */
class MobileFrontendHooks implements
	MobileFrontendFeaturesRegistrationHook,
	RequestContextCreateSkinMobileHook
{
	public function __construct(
		private readonly Config $config,
		private readonly SkinOptions $skinOptions,
	) {
	}

	/**
	 * Register mobile web beta features
	 * @see https://www.mediawiki.org/wiki/
	 *  Extension:MobileFrontend/MobileFrontendFeaturesRegistration
	 *
	 * @param FeaturesManager $featuresManager
	 */
	public function onMobileFrontendFeaturesRegistration( FeaturesManager $featuresManager ) {
		try {
			$featuresManager->registerFeature(
				new Feature(
					'MinervaShowCategories',
					'skin-minerva',
					$this->config->get( 'MinervaShowCategories' )
				)
			);
			$featuresManager->registerFeature(
				new Feature(
					'MinervaPageIssuesNewTreatment',
					'skin-minerva',
					$this->config->get( 'MinervaPageIssuesNewTreatment' )
				)
			);
			$featuresManager->registerFeature(
				new Feature(
					'MinervaTalkAtTop',
					'skin-minerva',
					$this->config->get( 'MinervaTalkAtTop' )
				)
			);
			$featuresManager->registerFeature(
				new Feature(
					'MinervaDonateLink',
					'skin-minerva',
					$this->config->get( 'MinervaDonateLink' )
				)
			);
			$featuresManager->registerFeature(
				new Feature(
					'MinervaDonateBanner',
					'skin-minerva',
					$this->config->get( 'MinervaDonateBanner' )
				)
			);
			$featuresManager->registerFeature(
				new Feature(
					'MinervaHistoryInPageActions',
					'skin-minerva',
					$this->config->get( 'MinervaHistoryInPageActions' )
				)
			);
			$featuresManager->registerFeature(
				new Feature(
					Hooks::FEATURE_OVERFLOW_PAGE_ACTIONS,
					'skin-minerva',
					$this->config->get( Hooks::FEATURE_OVERFLOW_PAGE_ACTIONS )
				)
			);
			$featuresManager->registerFeature(
				new Feature(
					'MinervaAdvancedMainMenu',
					'skin-minerva',
					$this->config->get( 'MinervaAdvancedMainMenu' )
				)
			);

		} catch ( RuntimeException ) {
			// features already registered...
			// due to a bug it's possible for this to run twice
			// https://phabricator.wikimedia.org/T165068
		}
	}

	/**
	 * BeforePageDisplayMobile hook handler.
	 *
	 * @param MobileContext $mobileContext
	 * @param Skin $skin
	 */
	public function onRequestContextCreateSkinMobile(
		MobileContext $mobileContext, Skin $skin
	) {
		$this->skinOptions->setMinervaSkinOptions( $mobileContext, $skin );
	}
}
