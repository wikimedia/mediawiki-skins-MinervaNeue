<?php

namespace MediaWiki\Minerva\Menu;

use DomainException;
use MediaWiki\Minerva\Menu\Entries\IMenuEntry;
use MediaWiki\Minerva\Menu\Entries\SingleMenuEntry;
use MediaWikiIntegrationTestCase;

/**
 * @group MinervaNeue
 * @coversDefaultClass \MediaWiki\Minerva\Menu\Group
 */
class GroupTest extends MediaWikiIntegrationTestCase {
	/** @var string[] */
	private $homeComponent = [
		'text' => 'Home',
		'href' => '/Main_page',
		'class' => 'mw-ui-icon mw-ui-icon-before mw-ui-icon-home',
		'data-event-name' => 'menu.home',
		'icon' => null
	];

	/** @var string[] */
	private $nearbyComponent = [
		'text' => 'Nearby',
		'href' => '/wiki/Special:Nearby',
		'class' => 'mw-ui-icon mw-ui-icon-before mw-ui-icon-nearby',
		'icon' => null
	];

	/**
	 * @covers ::getEntries
	 * @covers ::hasEntries
	 */
	public function testItShouldntHaveEntriesByDefault() {
		$menu = new Group( 'p-test' );

		$this->assertEmpty( $menu->getEntries() );
		$this->assertFalse( $menu->hasEntries() );
	}

	/**
	 * @covers ::insertEntry
	 * @covers ::search
	 * @covers ::getEntries
	 * @covers ::hasEntries
	 */
	public function testInsertingAnEntry() {
		$menu = new Group( 'p-test' );
		$entry = SingleMenuEntry::create(
			'home',
			$this->homeComponent['text'],
			$this->homeComponent['href'],
			$this->homeComponent['class'],
			$this->homeComponent['icon'],
			true
		);
		$menu->insertEntry( $entry );

		$expectedEntries = [
			[
				'name' => 'home',
				'components' => [
					[
						'text' => $this->homeComponent['text'],
						'href' => $this->homeComponent['href'],
						'class' => 'mw-ui-icon mw-ui-icon-before mw-ui-icon-home menu__item--home',
						'icon' => 'minerva-home',
						'data-event-name' => 'menu.home'
					]
				 ],
			],
		];

		$this->assertEquals( $expectedEntries, $menu->getEntries() );
		$this->assertTrue( $menu->hasEntries() );
	}

	/**
	 * @covers ::insertEntry
	 * @covers ::search
	 * @covers ::getEntries
	 * @covers ::insertAfter
	 */
	public function testInsertingAnEntryAfterAnother() {
		$menu = new Group( 'p-test' );
		$entryHome = SingleMenuEntry::create(
			'home',
			$this->homeComponent['text'],
			$this->homeComponent['href'],
			$this->homeComponent['class'],
			$this->homeComponent['icon'],
			true
		);
		$menu->insertEntry( $entryHome );

		$entryOtherHome = SingleMenuEntry::create(
			'another_home',
			$this->homeComponent['text'],
			$this->homeComponent['href'],
			$this->homeComponent['class'],
			$this->homeComponent['icon'],
			true
		);
		$menu->insertEntry( $entryOtherHome );
		$menu->insertAfter(
			'home',
			'nearby',
			$this->nearbyComponent['text'],
			$this->nearbyComponent['href'],
			$this->nearbyComponent['class'],
			$this->nearbyComponent['icon']
		);

		$expectedEntries = [
			[
				'name' => 'home',
				'components' => [
					[
						'text' => $this->homeComponent['text'],
						'href' => $this->homeComponent['href'],
						'class' => 'mw-ui-icon mw-ui-icon-before mw-ui-icon-home menu__item--home',
						'icon' => 'minerva-home',
						'data-event-name' => 'menu.home'
					]
				],
			],
			[
				'name' => 'nearby',
				'components' => [
					[
						'text' => $this->nearbyComponent['text'],
						'href' => $this->nearbyComponent['href'],
						'class' => 'mw-ui-icon mw-ui-icon-before mw-ui-icon-nearby menu__item--nearby',
						'icon' => 'minerva-nearby'
					]
				],
			],
			[
				'name' => 'another_home',
				'components' => [
					[
						'text' => $this->homeComponent['text'],
						'href' => $this->homeComponent['href'],
						'class' => 'mw-ui-icon mw-ui-icon-before mw-ui-icon-home menu__item--another_home',
						'icon' => 'minerva-another_home',
						'data-event-name' => 'menu.another_home'
					]
				],
			],
		];

		$this->assertEquals( $expectedEntries, $menu->getEntries() );
	}

	/**
	 * @covers ::insertAfter
	 * @covers ::search
	 */
	public function testInsertAfterWhenTargetEntryDoesntExist() {
		$menu = new Group( 'p-test' );
		$this->expectException( DomainException::class );
		$this->expectExceptionMessage( 'The "home" entry doesn\'t exist.' );
		$menu->insertAfter(
				'home',
				'nearby',
				$this->nearbyComponent['text'],
				$this->nearbyComponent['href'],
				$this->nearbyComponent['class'],
				$this->nearbyComponent['icon']
			);
	}

	/**
	 * @covers ::insertAfter
	 */
	public function testInsertAfterWithAnEntryWithAnExistingName() {
		$menu = new Group( 'p-test' );
		$entryHome = SingleMenuEntry::create(
			'home',
			$this->homeComponent['text'],
			$this->homeComponent['href'],
			$this->homeComponent['class']
		);
		$menu->insertEntry( $entryHome );
		$entryCar = SingleMenuEntry::create(
			'car',
			$this->homeComponent['text'],
			$this->homeComponent['href'],
			$this->homeComponent['class']
		);
		$menu->insertEntry( $entryCar );
		$this->expectException( DomainException::class );
		$this->expectExceptionMessage( 'The "car" entry already exists.' );
		$menu->insertAfter(
			'home',
			'car',
			$this->homeComponent['text'],
			$this->homeComponent['href'],
			$this->homeComponent['class']
		);
	}

	/**
	 * @covers ::insertEntry
	 */
	public function testInsertingAnEntryWithAnExistingName() {
		$menu = new Group( 'p-test' );
		$entryHome = SingleMenuEntry::create(
			'home',
			$this->homeComponent['text'],
			$this->homeComponent['href'],
			$this->homeComponent['class']
		);
		$menu->insertEntry( $entryHome );
		$this->expectException( DomainException::class );
		$this->expectExceptionMessage( 'The "home" entry already exists.' );
		$menu->insertEntry( $entryHome );
	}

	/**
	 * @covers ::insertEntry
	 * @covers ::insertAfter
	 */
	public function testInsertingAnEntryAfterAnotherOne() {
		$menu = new Group( 'p-test' );
		$entryFirst = SingleMenuEntry::create(
			'first',
			$this->homeComponent['text'],
			$this->homeComponent['href'],
			$this->homeComponent['class']
		);
		$menu->insertEntry( $entryFirst );

		$entryLast = SingleMenuEntry::create(
			'last',
			$this->homeComponent['text'],
			$this->homeComponent['href'],
			$this->homeComponent['class']
		);
		$menu->insertEntry( $entryLast );
		$menu->insertAfter(
			'first',
			'middle',
			$this->nearbyComponent['text'],
			$this->nearbyComponent['href'],
			$this->nearbyComponent['class'],
			$this->nearbyComponent['icon']
		);
		$items = $menu->getEntries();
		$this->assertCount( 3, $items );
		$this->assertSame( 'first', $items[0]['name'] );
		$this->assertSame( 'middle', $items[1]['name'] );
		$this->assertSame( 'last', $items[2]['name'] );
	}

	/**
	 * @covers ::insertEntry
	 * @covers ::getEntries
	 */
	public function testInsertingAJavascriptOnlyEntry() {
		$menu = new Group( 'p-test' );
		$entryHome = SingleMenuEntry::create(
			'nearby',
			$this->nearbyComponent['text'],
			$this->nearbyComponent['href'],
			$this->nearbyComponent['class']
		);
		$entryHome->setJSOnly();
		$menu->insertEntry( $entryHome );

		$expectedEntries = [
			[
				'name' => 'nearby',
				'components' => [
					[
						'text' => $this->nearbyComponent['text'],
						'href' => $this->nearbyComponent['href'],
						'class' => 'mw-ui-icon mw-ui-icon-before mw-ui-icon-nearby menu__item--nearby',
						'icon' => 'minerva-nearby'
					]
				],
				'class' => 'jsonly'
			],
		];

		$this->assertEquals( $expectedEntries, $menu->getEntries() );
	}

	/**
	 * @covers ::getEntryByName
	 * @covers ::search
	 */
	public function testGetEntryByName() {
		$menu = new Group( 'p-test' );
		$entryHome = SingleMenuEntry::create(
			'home',
			$this->homeComponent['text'],
			$this->homeComponent['href'],
			$this->homeComponent['class']
		);
		$menu->insertEntry( $entryHome );
		$this->assertInstanceOf( IMenuEntry::class, $menu->getEntryByName( 'home' ) );
	}

	/**
	 * @covers ::getEntryByName
	 * @covers ::search
	 */
	public function testGetEntryByNameException() {
		$menu = new Group( 'p-test' );
		$this->expectException( DomainException::class );
		$menu->getEntryByName( 'home' );
	}

}
