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
	 * @covers ::isJSOnly()
	 * @covers ::getComponents()
	 */
	public function testMenuEntryConstruction() {
		$name = 'test';
		$isJSOnly = true;
		$entry = new MenuEntry( $name, $isJSOnly );
		$this->assertSame( $name, $entry->getName() );
		$this->assertSame( $isJSOnly, $entry->isJSOnly() );
		$this->assertSame( [], $entry->getComponents() );
	}
}
