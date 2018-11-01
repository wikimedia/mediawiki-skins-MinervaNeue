<?php

namespace Tests\MediaWiki\Minerva;

use MediaWiki\Minerva\SkinUserPageHelper;
use MediaWikiTestCase;
use Title;

/**
 * @group MinervaNeue
 * @coversDefaultClass \MediaWiki\Minerva\SkinUserPageHelper
 */
class SkinUserPageHelperTest extends MediaWikiTestCase {

	/**
	 * @covers ::isUserPage
	 * @covers ::fetchData
	 * @covers ::__construct
	 */
	public function testTitleNotInUserNamespace() {
		$title = Title::newFromText( 'Test Page' );

		$helper = new SkinUserPageHelper( $title );
		$this->assertEquals( false, $helper->isUserPage() );
	}

	/**
	 * @covers ::isUserPage
	 * @covers ::fetchData
	 */
	public function testTitleisASubpage() {
		$title = Title::newFromText( 'User:TestUser/subpage' );

		$helper = new SkinUserPageHelper( $title );
		$this->assertEquals( false, $helper->isUserPage() );
	}

	/**
	 * @covers ::fetchData
	 */
	public function testTitleProcessingIsCached() {
		$titleMock = $this->getMockBuilder( Title::class )
			->getMock();
		$titleMock->expects( $this->once() )
			->method( 'inNamespace' )
			->with( NS_USER )
			->willReturn( true );

		$titleMock->expects( $this->once() )
			->method( 'isSubpage' )
			->willReturn( false );

		$titleMock->expects( $this->once() )
			->method( 'getText' )
			->willReturn( 'Test' );

		$helper = new SkinUserPageHelper( $titleMock );
		$helper->isUserPage();
		$helper->isUserPage();
		$helper->getPageUser();
		$helper->getPageUser();
	}

	/**
	 * @covers ::fetchData
	 * @covers ::getPageUser
	 * @covers ::isUserPage
	 */
	public function testGetPageUserWhenOnUserPage() {
		$testUser = $this->getTestUser()->getUser();
		$title = $testUser->getUserPage();

		$helper = new SkinUserPageHelper( $title );
		$this->assertEquals( true, $helper->isUserPage() );
		$this->assertEquals( $testUser->getId(), $helper->getPageUser()->getId() );
	}

	/**
	 * @covers ::fetchData
	 * @covers ::getPageUser
	 * @covers ::isUserPage
	 */
	public function testGetPageUserWhenOnUserPageReturnsCorrectUser() {
		$testUser = $this->getTestUser()->getUser();
		$testUserTitle = $testUser->getUserPage();

		$secondTestUser = $this->getTestSysop()->getUser();
		$secondTestUserTitle = $secondTestUser->getUserPage();

		$helper = new SkinUserPageHelper( $secondTestUserTitle );
		$this->assertEquals( true, $helper->isUserPage() );
		$this->assertNotEquals( $testUser->getId(), $helper->getPageUser()->getId() );
		$this->assertNotEquals( $helper->getPageUser()->getUserPage(), $testUserTitle );
	}

}
