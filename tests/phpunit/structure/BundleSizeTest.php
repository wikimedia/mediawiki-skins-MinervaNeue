<?php

namespace MediaWiki\Skins\MinervaNeue\Tests\Structure;

class BundleSizeTest extends \MediaWiki\Tests\Structure\BundleSizeTestBase {

	/** @inheritDoc */
	public static function getBundleSizeConfigData(): string {
		return dirname( __DIR__, 3 ) . '/bundlesize.config.json';
	}

	/** @inheritDoc */
	public function getSkinName(): string {
		return 'minerva';
	}
}
