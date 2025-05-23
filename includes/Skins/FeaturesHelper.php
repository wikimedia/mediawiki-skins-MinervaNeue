<?php

namespace MediaWiki\Minerva\Skins;

use MediaWiki\Request\WebRequest;
use MediaWiki\Skins\Vector\ConfigHelper;
use MediaWiki\Title\Title;

class FeaturesHelper {

	private ?ConfigHelper $configHelper;

	public function __construct(
		?ConfigHelper $configHelper
	) {
		$this->configHelper = $configHelper;
	}

	/**
	 * Per the $options configuration (for use with $wgMinervaNightModeOptions)
	 * determine whether Night Mode should be disabled on the page.
	 * For the main page: Check the value of $options['exclude']['mainpage']
	 * Night Mode is disabled if:
	 *  1) The current namespace is listed in array $options['exclude']['namespaces']
	 *  OR
	 *  2) A query string parameter matches one of the regex patterns in $exclusions['querystring'].
	 *  OR
	 *  3) The canonical title matches one of the titles in $options['exclude']['pagetitles']
	 * For this functionality to work the Vector skin MUST be installed.
	 *
	 * @param array $options
	 * @param WebRequest $request
	 * @param Title|null $title
	 *
	 * @return bool
	 * @internal only for use inside tests.
	 */
	public function shouldDisableNightMode( array $options, WebRequest $request, ?Title $title = null ): bool {
		if ( $this->configHelper ) {
			return $this->configHelper->shouldDisable( $options, $request, $title );
		}
		return false;
	}
}
