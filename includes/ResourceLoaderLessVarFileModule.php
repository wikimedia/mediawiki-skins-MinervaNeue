<?php

namespace MediaWiki\Minerva;

/**
 * Subclass with extra LESS variables for Minerva configuration.
 */
class ResourceLoaderLessVarFileModule extends \ResourceLoaderFileModule {
	/**
	 * @param \ResourceLoaderContext $context
	 * @return array LESS variables
	 */
	protected function getLessVars( \ResourceLoaderContext $context ) {
		return [
			'wgMinervaApplyKnownTemplateHacks' =>
				$this->getConfig()->get( 'MinervaApplyKnownTemplateHacks' ),
		];
	}
}
