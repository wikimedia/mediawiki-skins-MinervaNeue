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
		$defaultValue = $options->get( SkinOptions::OPTION_AMC );
		$options->setMultiple( [ SkinOptions::OPTION_AMC => !$defaultValue ] );

		$allOptions = $options->getAll();

		$this->assertEquals( !$defaultValue, $options->get( SkinOptions::OPTION_AMC ) );
		$this->assertArrayHasKey( SkinOptions::OPTION_AMC, $allOptions );
		$this->assertEquals( !$defaultValue, $allOptions[ SkinOptions::OPTION_AMC ] );
	}

	/**
	 * @covers ::hasSkinOptions
	 */
	public function testHasSkinOptions() {
		$options = new SkinOptions();
		// set OPTION_AMC to true just in case someone decides to set everything to false
		// sometime in the future.
		$options->setMultiple( [ SkinOptions::OPTION_AMC => true ] );
		$this->assertTrue( $options->hasSkinOptions() );
		$options->setMultiple( [ SkinOptions::OPTION_BACK_TO_TOP => true ] );
		$this->assertTrue( $options->hasSkinOptions() );
		$options->setMultiple( [
			SkinOptions::OPTION_AMC => false,
			SkinOptions::OPTION_BACK_TO_TOP => false
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
