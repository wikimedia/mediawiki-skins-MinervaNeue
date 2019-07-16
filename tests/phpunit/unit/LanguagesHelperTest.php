<?php

namespace Tests\MediaWiki\Minerva;

use MediaWiki\Minerva\LanguagesHelper;
use PHPUnit\Framework\MockObject\Invocation;

/**
 * Class SkinMinervaTest
 * @package Tests\MediaWiki\Minerva
 * @group MinervaNeue
 * @coversDefaultClass \MediaWiki\Minerva\LanguagesHelper
 */
class LanguagesHelperTest extends \MediaWikiUnitTestCase {

	/**
	 * Build test Output object
	 * @param array $langLinks
	 * @return \OutputPage
	 */
	private function getOutput( array $langLinks ) {
		$out = $this->getMock(
			\OutputPage::class,
			[ 'getLanguageLinks' ],
			[],
			'',
			false
			);
		$out->expects( $this->once() )
			->method( 'getLanguageLinks' )
			->willReturn( $langLinks );

		return $out;
	}

	/**
	 * Build test Title object
	 * @param $hasVariants
	 * @param Invocation|null $matcher
	 * @return \Title
	 */
	private function getTitle( $hasVariants, Invocation $matcher = null ) {
		$languageMock = $this->getMock( \Language::class, [ 'hasVariants' ], [], '', false );
		$languageMock->expects( $matcher ?? $this->any() )
			->method( 'hasVariants' )
			->willReturn( $hasVariants );

		$title = $this->getMock( \Title::class, [ 'getPageLanguage' ] );
		$title->expects( $matcher ?? $this->any() )
			->method( 'getPageLanguage' )
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
			$this->getTitle( false ), $this->once() ) );
		$this->assertTrue( $helper->doesTitleHasLanguagesOrVariants(
			$this->getTitle( true ), $this->once() ) );
	}
}
