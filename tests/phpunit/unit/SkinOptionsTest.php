<?php

namespace Tests\MediaWiki\Minerva;

use MediaWiki\Minerva\SkinOptions;

/**
 * Class SkinMinervaTest
 * @package Tests\MediaWiki\Minerva
 * @group MinervaNeue
 * @coversDefaultClass \MediaWiki\Minerva\SkinOptions
 */
class SkinOptionsTest extends \MediaWikiUnitTestCase {

	/**
	 * @covers ::get
	 * @covers ::getAll
	 * @covers ::setMultiple
	 */
	public function testSettersAndGetters() {
		$options = new SkinOptions();
		$defaultValue = $options->get( SkinOptions::AMC_MODE );
		$options->setMultiple( [ SkinOptions::AMC_MODE => !$defaultValue ] );

		$allOptions = $options->getAll();

		$this->assertEquals( !$defaultValue, $options->get( SkinOptions::AMC_MODE ) );
		$this->assertArrayHasKey( SkinOptions::AMC_MODE, $allOptions );
		$this->assertEquals( !$defaultValue, $allOptions[ SkinOptions::AMC_MODE ] );
	}

	/**
	 * @covers ::hasSkinOptions
	 */
	public function testHasSkinOptions() {
		$options = new SkinOptions();
		// set AMC_MODE to true just in case someone decides to set everything to false
		// sometime in the future.
		$options->setMultiple( [ SkinOptions::AMC_MODE => true ] );
		$this->assertTrue( $options->hasSkinOptions() );
		$options->setMultiple( [ SkinOptions::BACK_TO_TOP => true ] );
		$this->assertTrue( $options->hasSkinOptions() );
		$options->setMultiple( [
			SkinOptions::TALK_AT_TOP => false,
			SkinOptions::HISTORY_IN_PAGE_ACTIONS => false,
			SkinOptions::TOOLBAR_SUBMENU => false,
			SkinOptions::AMC_MODE => false,
			SkinOptions::BACK_TO_TOP => false
		] );
		$this->assertFalse( $options->hasSkinOptions() );
	}

	/**
	 * @covers ::get
	 * @expectedException \OutOfBoundsException
	 */
	public function testGettingUnknownKeyShouldThrowException() {
		$options = new SkinOptions();
		$options->get( 'non_existing_key' );
	}

	/**
	 * @covers ::get
	 * @expectedException \OutOfBoundsException
	*/
	public function testSettingUnknownKeyShouldThrowException() {
		$options = new SkinOptions();
		$options->setMultiple( [
			'non_existing_key' => 1
		] );
	}
}
