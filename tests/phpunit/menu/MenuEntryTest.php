<?php

namespace Tests\MediaWiki\Minerva\Menu;

use MediaWiki\Minerva\Menu\MenuEntry;

/**
 * @group MinervaNeue
 * @coversDefaultClass \MediaWiki\Minerva\Menu\MenuEntry
 */
class MenuEntryTest extends \MediaWikiTestCase {

	/**
	 * @covers ::__construct
	 * @covers ::getName()
	 * @covers ::getCSSClasses()
	 * @covers ::getComponents()
	 */
	public function testMenuEntryConstruction() {
		$name = 'test';
		$isJSOnly = true;
		$entry = new MenuEntry( $name, $isJSOnly );
		$this->assertSame( $name, $entry->getName() );
		$this->assertArrayEquals( [ 'jsonly' ], $entry->getCSSClasses() );
		$this->assertSame( [], $entry->getComponents() );
	}
}
