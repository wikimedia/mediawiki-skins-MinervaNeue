<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWiki\Minerva\Menu\PageActions;

use Hooks;
use MediaWiki\Minerva\Menu\Group;
use MessageLocalizer;
use MinervaUI;

class DefaultOverflowBuilder implements IOverflowBuilder {

	/**
	 * @var MessageLocalizer
	 */
	private $messageLocalizer;

	/**
	 * Initialize Default overflow menu Group
	 *
	 * @param MessageLocalizer $messageLocalizer
	 */
	public function __construct( MessageLocalizer $messageLocalizer ) {
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * @inheritDoc
	 */
	public function getGroup( array $navUrls ) {
		$group = new Group();
		$possibleEntries = array_filter( [
			$this->buildEntry( 'info', 'info', 'info', $navUrls ),
			$this->buildEntry( 'permalink', 'link', 'permalink', $navUrls ),
			$this->buildEntry( 'backlinks', 'articleRedirect', 'whatlinkshere', $navUrls ),
			$this->buildEntry( 'cite', 'quotes', 'citethispage', $navUrls )
		] );

		foreach ( $possibleEntries as $menuEntry ) {
			$group->insertEntry( $menuEntry );
		}
		Hooks::run( 'MobileMenu', [ 'pageactions.overflow', &$group ] );
		return $group;
	}

	/**
	 * @param string $name
	 * @param string $icon Wikimedia UI icon name.
	 * @param string $navUrlKey
	 * @param array $navUrls A set of navigation urls build by SkinTemplate::buildNavUrls()
	 * @return PageActionMenuEntry|null
	 */
	private function buildEntry( $name, $icon, $navUrlKey, array $navUrls ) {
		$href = $navUrls[$navUrlKey]['href'] ?? null;

		return $href ?
			new PageActionMenuEntry(
				'page-actions-overflow-' . $name,
				$href,
				MinervaUI::iconClass( '', 'before', 'wikimedia-ui-' . $icon . '-base20' ),
				$this->messageLocalizer->msg( 'minerva-page-actions-' . $name )
			) : null;
	}
}
