<?php

namespace Tests\MediaWiki\Minerva;

use HashConfig;
use MediaWiki\Minerva\SkinOptions;
use MediaWikiTestCase;
use OutputPage;
use RequestContext;
use SkinMinerva;
use Title;
use Wikimedia\TestingAccessWrapper;

/**
 * @coversDefaultClass SkinMinerva
 * @group MinervaNeue
 */
class SkinMinervaTest extends MediaWikiTestCase {
	private const OPTIONS_MODULE = 'skins.minerva.options';

	private function overrideSkinOptions( $options ) {
		$mockOptions = new SkinOptions();
		$mockOptions->setMultiple( $options );
		$this->setService( 'Minerva.SkinOptions', $mockOptions );
	}

	/**
	 * @covers ::setContext
	 * @covers ::hasCategoryLinks
	 */
	public function testHasCategoryLinksWhenOptionIsOff() {
		$outputPage = $this->getMockBuilder( OutputPage::class )
			->disableOriginalConstructor()
			->getMock();
		$outputPage->expects( $this->never() )
			->method( 'getCategoryLinks' );

		$this->overrideSkinOptions( [ SkinOptions::CATEGORIES => false ] );
		$context = new RequestContext();
		$context->setTitle( Title::newFromText( 'Test' ) );
		$context->setOutput( $outputPage );

		$skin = new SkinMinerva();
		$skin->setContext( $context );
		$skin = TestingAccessWrapper::newFromObject( $skin );

		$this->assertEquals( $skin->hasCategoryLinks(), false );
	}

	/**
	 * @dataProvider provideHasCategoryLinks
	 * @param array $categoryLinks
	 * @param bool $expected
	 * @covers ::setContext
	 * @covers ::hasCategoryLinks
	 */
	public function testHasCategoryLinks( array $categoryLinks, $expected ) {
		$outputPage = $this->getMockBuilder( OutputPage::class )
			->disableOriginalConstructor()
			->getMock();
		$outputPage->expects( $this->once() )
			->method( 'getCategoryLinks' )
			->will( $this->returnValue( $categoryLinks ) );

		$this->overrideSkinOptions( [ SkinOptions::CATEGORIES => true ] );

		$context = new RequestContext();
		$context->setTitle( Title::newFromText( 'Test' ) );
		$context->setOutput( $outputPage );

		$skin = new SkinMinerva();
		$skin->setContext( $context );

		$skin = TestingAccessWrapper::newFromObject( $skin );

		$this->assertEquals( $skin->hasCategoryLinks(), $expected );
	}

	public function provideHasCategoryLinks() {
		return [
			[ [], false ],
			[
				[
					'normal' => '<ul><li><a href="/wiki/Category:1">1</a></li></ul>'
				],
				true
			],
			[
				[
					'hidden' => '<ul><li><a href="/wiki/Category:Hidden">Hidden</a></li></ul>'
				],
				true
			],
			[
				[
					'normal' => '<ul><li><a href="/wiki/Category:1">1</a></li></ul>',
					'hidden' => '<ul><li><a href="/wiki/Category:Hidden">Hidden</a></li></ul>'
				],
				true
			],
			[
				[
					'unexpected' => '<ul><li><a href="/wiki/Category:1">1</a></li></ul>'
				],
				false
			],
		];
	}

	/**
	 * Test whether the font changer module is correctly added to the list context modules.
	 *
	 * @covers       ::getContextSpecificModules
	 * @dataProvider provideGetContextSpecificModules
	 * @param mixed  $categoryLinks whether category link feature is enabled
	 * @param string $moduleName Module name that is being tested
	 * @param bool $expected Whether the module is expected to be returned by the function being tested
	 */
	public function testGetContextSpecificModules( $categoryLinks, $moduleName, $expected ) {
		$this->overrideSkinOptions( [
			SkinOptions::SHOW_DONATE => false,
			SkinOptions::TALK_AT_TOP => false,
			SkinOptions::TALK_AT_TOP => false,
			SkinOptions::HISTORY_IN_PAGE_ACTIONS => false,
			SkinOptions::TOOLBAR_SUBMENU => false,
			SkinOptions::MAIN_MENU_EXPANDED => false,
			SkinOptions::PERSONAL_MENU => false,
			SkinOptions::CATEGORIES => $categoryLinks,
		] );

		$skin = new SkinMinerva();
		$title = Title::newFromText( 'Test' );
		$testContext = RequestContext::getMain();
		$testContext->setTitle( $title );

		$skin->setContext( $testContext );

		if ( $expected ) {
			$this->assertContains( $moduleName, $skin->getContextSpecificModules() );
		} else {
			$this->assertNotContains( $moduleName, $skin->getContextSpecificModules() );
		}
	}

	public function provideGetContextSpecificModules() {
		return [
			[ true, self::OPTIONS_MODULE, true ],
			[ false, self::OPTIONS_MODULE, false ],
		];
	}

	public function provideGetSitename() {
		return [
			[
				[
					'Logos' => false,
					'Logo' => '/logo.svg',
				],
				'(mobile-frontend-footer-sitename)',
				'No wgLogos defined.'
			],
			[
				[
					'Logos' => [
						'1x' => '/logo.svg',
						'wordmark' => [
							'src' => '/wordmark.svg',
							'width' => '10',
							'height' => '10',
						],
					],
				],
				'<img src="/wordmark.svg" width="10" height="10" alt="(mobile-frontend-footer-sitename)"/>',
				'wgLogos used.'
			],
			[
				[
					'Logos' => [
						'1x' => '/logo.svg',
						'wordmark' => [
							'src' => '/wordmark.png',
							'1x' => '/wordmark.svg',
							'width' => '10',
							'height' => '10',
						],
					],
				],
				'<img src="/wordmark.png" width="10" height="10" '
					. 'alt="(mobile-frontend-footer-sitename)" srcset="/wordmark.svg 1x"/>',
				'wgLogos used with `png` and `svg`.'
			]
		];
	}

	/**
	 * Test whether the font changer module is correctly added to the list context modules
	 *
	 * @covers       ::getSitename
	 * @dataProvider provideGetSitename
	 */
	public function testGetSitename( $configData, $expected, $msg ) {
		$skin = new SkinMinerva();
		$config = new HashConfig( $configData );
		$context = new RequestContext();
		$context->setLanguage( 'qqx' );
		$context->setConfig( $config );
		$skin->setContext( $context );

		// See T236778
		$this->setMwGlobals( [
			'wgDevelopmentWarnings' => false
		] );

		$sitename = $skin->getSitename();
		$this->assertEquals( $sitename, $expected, $msg );
	}
}
