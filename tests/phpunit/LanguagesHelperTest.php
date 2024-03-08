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
	 * Build test LanguageConverterFactory object
	 * @param bool $hasVariants
	 * @return LanguageConverterFactory
	 */
	private function getLanguageConverterFactory( $hasVariants ) {
		$langConv = $this->createMock( ILanguageConverter::class );
		$langConv->method( 'hasVariants' )->willReturn( $hasVariants );
		$langConvFactory = $this->createMock( LanguageConverterFactory::class );
		$langConvFactory->method( 'getLanguageConverter' )->willReturn( $langConv );

		return $langConvFactory;
	}

	/**
	 * Build test Title object
	 * @return Title
	 */
	private function getTitle() {
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
		$helper = new LanguagesHelper(
			$this->getLanguageConverterFactory( false ),
			$this->getOutput( [ 'pl:StronaTestowa', 'en:TestPage' ] )
		);

		$this->assertTrue( $helper->doesTitleHasLanguagesOrVariants( $this->getTitle() ) );

		$helper = new LanguagesHelper(
			$this->getLanguageConverterFactory( true ),
			$this->getOutput( [ 'pl:StronaTestowa', 'en:TestPage' ] )
		);

		$this->assertTrue( $helper->doesTitleHasLanguagesOrVariants( $this->getTitle() ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::doesTitleHasLanguagesOrVariants
	 */
	public function testReturnsWhenOutputDoesNotHaveLangLinks() {
		$helper = new LanguagesHelper(
			$this->getLanguageConverterFactory( false ),
			$this->getOutput( [] )
		);

		$this->assertFalse( $helper->doesTitleHasLanguagesOrVariants(
			$this->getTitle() ) );

		$helper = new LanguagesHelper(
			$this->getLanguageConverterFactory( true ),
			$this->getOutput( [] )
		);

		$this->assertTrue( $helper->doesTitleHasLanguagesOrVariants(
			$this->getTitle() ) );
	}
}
