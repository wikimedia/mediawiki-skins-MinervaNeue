<?php

namespace MediaWiki\Minerva;

use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;
use MediaWiki\Minerva\Menu\Definitions;
use MediaWiki\Minerva\Menu\Main\DefaultMainMenuBuilder;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentityUtils;

/**
 * These are tests for the hamburger menu.
 *
 * @group MinervaNeue
 * @coversDefaultClass \MediaWiki\Minerva\Menu\Main\DefaultMainMenuBuilder
 */
class DefaultMainMenuBuilderTest extends \MediaWikiUnitTestCase {
	private const PREFERENCES_ITEM = [
		'href' => '/wiki/Special:Preferences'
	];

	private const AUTH_PERSONAL_TOOLS = [
		'createaccount' => [ 'href' => '/wiki/Special:CreateAccount',
			'text' => 'Create Account',
		],
		'login' => [ 'href' => '/wiki/Special:Login',
			'text' => 'Login',
		],
	];

	private function makeBuilder(
		bool $isRegistered = true,
		bool $showMobileOptions = true,
		bool $showDonateLink = true
	) {
		$title = $this->createMock( Title::class );
		$userMock = $this->createMock( User::class );
		$identityToolsMock = $this->createMock( UserIdentityUtils::class );
		$specialPageMock = $this->createMock( Title::class );
		$factoryMock = $this->createMock( SpecialPageFactory::class );
		$contextMock = $this->createMock( IContextSource::class );
		$messageMock = $this->createMock( Message::class );
		$registryMock = $this->createMock( ExtensionRegistry::class );
		$specialPageMock->expects( $this->any() )
			->method( 'getLocalURL' )
			->willReturn( '/wiki/Special:DummyTest' );
		$factoryMock->expects( $this->any() )
			->method( 'getTitleForAlias' )
			->willReturn( $specialPageMock );
		$messageMock->expects( $this->any() )
			->method( 'text' )
			->willReturn( 'msg-text' );
		$contextMock->expects( $this->any() )
			->method( 'msg' )
			->willReturn( $messageMock );
		$contextMock->expects( $this->once() )
			->method( 'getUser' )
			->willReturn( $userMock );
		$contextMock->expects( $this->any() )
			->method( 'getTitle' )
			->willReturn( $title );
		$userMock->expects( $this->any() )
			->method( 'isRegistered' )
			->willReturn( $isRegistered );
		$registryMock->method( 'isLoaded' )->willReturn( true );

		$definitions = new Definitions( $factoryMock, $registryMock );
		$definitions->setContext( $contextMock );
		return new DefaultMainMenuBuilder(
			$showMobileOptions,
			$showDonateLink,
			$userMock,
			$definitions,
			$identityToolsMock,
		);
	}

	/**
	 * @covers ::getPersonalToolsGroup
	 */
	public function testGetPersonalToolsGroupAnonymousUserOnMobile() {
		$builder = $this->makeBuilder(
			// anon
			false,
			// $showMobileOptions = true
			true
		);
		$personalToolsGroup = $builder->getPersonalToolsGroup( [
			'preferences' => self::PREFERENCES_ITEM,
		] );
		$settingsGroup = $builder->getSettingsGroup();
		$this->assertSame( 'settings', $settingsGroup->getEntries()[0]['name'],
			'the settings has its own dedicated menu' );
		$this->assertCount( 0, $personalToolsGroup->getEntries(),
			'personal tools group should be empty' );
	}

	/**
	 * @covers ::getPersonalToolsGroup
	 */
	public function testGetPersonalToolsGroupOnMobileRegistered() {
		$builder = $this->makeBuilder(
			// registered user
			true,
			// $showMobileOptions = true
			true
		);
		$personalToolsGroup = $builder->getPersonalToolsGroup( [
			'preferences' => self::PREFERENCES_ITEM
		] );
		$settingsGroup = $builder->getSettingsGroup();

		$this->assertCount( 0, $personalToolsGroup->getEntries(),
			'for logged in users the personal tools are handled elsewhere' );
		$this->assertCount( 1, $settingsGroup->getEntries(),
			'the settings menu has one entry in this case.' );
	}

	/**
	 * @covers ::getPersonalToolsGroup
	 */
	public function testGetPersonalToolsGroupLoggedOutWhenShouldShowAccountMenuItems() {
		$builder = $this->makeBuilder( false, false, true, true );
		$personalToolsGroup = $builder->getPersonalToolsGroup( self::AUTH_PERSONAL_TOOLS );

		$this->assertCount( 2, $personalToolsGroup->getEntries(),
		'personal tools group should include two entries' );
		$this->assertSame( 'createaccount',
			$personalToolsGroup->getEntries()[0]['name'] );
		$this->assertSame( 'login',
			$personalToolsGroup->getEntries()[1]['name'] );
	}
}
