<?php

namespace MediaWiki\Minerva;

use ILanguageConverter;
use Language;
use MediaWiki\Languages\LanguageConverterFactory;
use MediaWiki\Output\OutputPage;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;

/**
 * @package Tests\MediaWiki\Minerva
 * @group MinervaNeue
 * @coversDefaultClass \MediaWiki\Minerva\LanguagesHelper
 */
class LanguagesHelperTest extends MediaWikiIntegrationTestCase {

	/**
	 * Build test Output object
	 * @param array $langLinks
	 * @return OutputPage
	 */
	private function getOutput( array $langLinks ) {
		$out = $this->createMock( OutputPage::class );
		$out->expects( $this->once() )
			->method( 'getLanguageLinks' )
			->willReturn( $langLinks );

		return $out;
	}

	/**
	 * Build test Title object
	 * @param bool $hasVariants
	 * @return Title
	 */
	private function getTitle( $hasVariants ) {
		$langConv = $this->createMock( ILanguageConverter::class );
		$langConv->method( 'hasVariants' )->willReturn( $hasVariants );
		$langConvFactory = $this->createMock( LanguageConverterFactory::class );
		$langConvFactory->method( 'getLanguageConverter' )->willReturn( $langConv );
		$this->setService( 'LanguageConverterFactory', $langConvFactory );

		$languageMock = $this->createMock( Language::class );
		$title = $this->createMock( Title::class );
		$title->method( 'getPageLanguage' )
			->willReturn( $languageMock );

		return $title;
	}

	/**
	 * @covers ::__construct
	 * @covers ::doesTitleHasLanguagesOrVariants
	 */
	public function testReturnsWhenOutputPageHasLangLinks() {
		$helper = new LanguagesHelper( $this->getOutput( [ 'pl:StronaTestowa', 'en:TestPage' ] ) );

		$this->assertTrue( $helper->doesTitleHasLanguagesOrVariants( $this->getTitle( false ) ) );
		$this->assertTrue( $helper->doesTitleHasLanguagesOrVariants( $this->getTitle( true ) ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::doesTitleHasLanguagesOrVariants
	 */
	public function testReturnsWhenOutputDoesNotHaveLangLinks() {
		$helper = new LanguagesHelper( $this->getOutput( [] ) );

		$this->assertFalse( $helper->doesTitleHasLanguagesOrVariants(
			$this->getTitle( false ) ) );
		$this->assertTrue( $helper->doesTitleHasLanguagesOrVariants(
			$this->getTitle( true ) ) );
	}
}
