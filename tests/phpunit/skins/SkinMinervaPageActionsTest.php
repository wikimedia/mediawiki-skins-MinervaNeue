<?php

namespace Tests\MediaWiki\Minerva;

use SkinMinerva;
use MediaWikiTestCase;
use Title;
use RequestContext;
use MediaWiki\Minerva\SkinUserPageHelper;

// FIXME: That this class exists is an indicator that at least SkinMinerva#isAllowedPageAction
// should be extracted from SkinMinerva.
// phpcs:ignore MediaWiki.Files.ClassMatchesFilename.NotMatch
class TestSkinMinerva extends SkinMinerva {

	public function isAllowedPageAction( $action ) {
		return parent::isAllowedPageAction( $action );
	}

	public function setDoesPageHaveLanguages( $doesPageHaveLanguages ) {
		$this->doesPageHaveLanguages = $doesPageHaveLanguages;
	}

}

/**
 * @group MinervaNeue
 */
class SkinMinervaPageActionsTest extends MediaWikiTestCase {

	/**
	 * @var TestSkinMinerva
	 */
	private $skin;

	protected function setUp() {
		parent::setUp();

		$this->skin = $this->getSkin( Title::newFromText( 'SkinMinervaPageActionsTest' ) );
	}

	/**
	 * @param Title $title
	 * @return TestSkinMinerva
	 */
	private function getSkin( Title $title ) {
		$requestContext = RequestContext::getMain();
		$requestContext->setTitle( $title );

		$result = new TestSkinMinerva();
		$result->setContext( $requestContext );

		return $result;
	}

	/**
	 * @covers SkinMinerva::isAllowedPageAction
	 */
	public function testPageActionsArentAllowedWhenOnTheMainPage() {
		$skin = $this->getSkin( Title::newMainPage() );

		$this->assertFalse( $skin->isAllowedPageAction( 'watch' ) );
		$this->assertFalse( $skin->isAllowedPageAction( 'edit' ) );

		// Check to make sure 'talk' and 'switch-language' are enabled on the Main page.
		$this->assertTrue( $skin->isAllowedPageAction( 'talk' ) );
		$this->assertTrue( $skin->isAllowedPageAction( 'switch-language' ) );
	}

	/**
	 * @covers SkinMinerva::isAllowedPageAction
	 */
	public function testInvalidPageActionsArentAllowed() {
		$this->setMwGlobals( 'wgMinervaPageActions', [] );

		// By default, the "talk" and "watch" page actions are allowed but are now deemed invalid.
		$this->assertFalse( $this->skin->isAllowedPageAction( 'talk' ) );
		$this->assertFalse( $this->skin->isAllowedPageAction( 'watch' ) );
	}

	/**
	 * @covers SkinMinerva::isAllowedPageAction
	 */
	public function testValidPageActionsAreAllowed() {
		$this->assertTrue( $this->skin->isAllowedPageAction( 'talk' ) );
		$this->assertTrue( $this->skin->isAllowedPageAction( 'watch' ) );
	}

	public static function editPageActionProvider() {
		return [
			[ false, false, false ],
			[ true, false, false ],
			[ true, true, true ]
		];
	}

	/**
	 * The "edit" page action is allowed when the page doesn't support direct editing via the API.
	 *
	 * @dataProvider editPageActionProvider
	 * @covers SkinMinerva::isAllowedPageAction
	 */
	public function testEditPageAction(
		$supportsDirectEditing,
		$supportsDirectApiEditing,
		$expected
	) {
		$contentHandler = $this->getMockBuilder( 'ContentHandler' )
			->disableOriginalConstructor()
			->getMock();

		$contentHandler->method( 'supportsDirectEditing' )
			->will( $this->returnValue( $supportsDirectEditing ) );

		$contentHandler->method( 'supportsDirectApiEditing' )
			->will( $this->returnValue( $supportsDirectApiEditing ) );

		$this->setService( 'Minerva.ContentHandler', $contentHandler );

		$this->assertEquals( $expected, $this->skin->isAllowedPageAction( 'edit' ) );
	}

	/**
	 * @covers SkinMinerva::isAllowedPageAction
	 */
	public function testPageActionsWhenOnUserPage() {
		$userPageHelper = $this->getMockBuilder( SkinUserPageHelper::class )
			->disableOriginalConstructor()
			->getMock();

		$skin = $this->getSkin( Title::newFromText( 'User:Admin' ) );

		$this->setService( 'Minerva.SkinUserPageHelper', $userPageHelper );

		$this->assertTrue( $skin->isAllowedPageAction( 'talk' ) );
	}

	/**
	 * @covers SkinMinerva::isAllowedPageAction
	 */
	public function testPageActionsWhenNotOnUserPage() {
		$userPageHelper = $this->getMockBuilder( SkinUserPageHelper::class )
			->disableOriginalConstructor()
			->getMock();

		$skin = $this->getSkin( Title::newFromText( 'A_test_page' ) );
		$this->setService( 'Minerva.SkinUserPageHelper', $userPageHelper );

		$this->assertTrue( $skin->isAllowedPageAction( 'talk' ) );
	}

	/**
	 * @covers SkinMinerva::isAllowedPageAction
	 */
	public function testPageActionsWhenOnAnonUserPage() {
		$userPageHelper = $this->getMockBuilder( SkinUserPageHelper::class )
			->disableOriginalConstructor()
			->getMock();

		$skin = $this->getSkin( Title::newFromText( 'User:1.1.1.1' ) );
		$this->setService( 'Minerva.SkinUserPageHelper', $userPageHelper );

		$this->assertTrue( $skin->isAllowedPageAction( 'talk' ) );
	}

	public static function switchLanguagePageActionProvider() {
		return [
			[ true, false, true ],
			[ false, true, true ],
			[ false, false, false ],
			[ true, false, true ],
		];
	}

	/**
	 * The "switch-language" page action is allowed when: v2 of the page action bar is enabled and
	 * if the page has interlanguage links or if the <code>$wgMinervaAlwaysShowLanguageButton</code>
	 * configuration variable is set to truthy.
	 *
	 * @dataProvider switchLanguagePageActionProvider
	 * @covers SkinMinerva::isAllowedPageAction
	 */
	public function testSwitchLanguagePageAction(
		$doesPageHaveLanguages,
		$minervaAlwaysShowLanguageButton,
		$expected
	) {
		$this->skin->setDoesPageHaveLanguages( $doesPageHaveLanguages );
		$this->setMwGlobals( [
			'wgMinervaAlwaysShowLanguageButton' => $minervaAlwaysShowLanguageButton,
		] );

		$this->assertEquals( $expected, $this->skin->isAllowedPageAction( 'switch-language' ) );
	}

	/**
	 * Watch action requires 'viewmywatchlist' and 'editmywatchlist' permissions
	 * to be grated. Verify that isAllowedAction('watch') returns false when user
	 * do not have those permissions granted
	 * @covers SkinMinerva::isAllowedPageAction
	 */
	public function testWatchIsAllowedOnlyWhenWatchlistPermissionsAreGranted() {
		$title = Title::newFromText( 'test_watchstar_permissions' );
		$requestContext = RequestContext::getMain();
		$requestContext->setTitle( $title );
		$userMock = $this->getMockBuilder( 'User' )
			->disableOriginalConstructor()
			->setMethods( [ 'isAllowedAll' ] )
			->getMock();
		$userMock->expects( $this->once() )
			->method( 'isAllowedAll' )
			->with( 'viewmywatchlist', 'editmywatchlist' )
			->willReturn( false );
		$requestContext->setUser( $userMock );

		$result = new TestSkinMinerva();
		$result->setContext( $requestContext );

		$this->assertTrue( $this->skin->isAllowedPageAction( 'talk' ) );
		$this->assertFalse( $this->skin->isAllowedPageAction( 'watch' ) );
	}
}
