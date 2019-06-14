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

use MessageLocalizer;
use MinervaUI;
use MWException;

/**
 * Director responsible for building Page Actions menu.
 * This class is stateless.
 */
final class PageActionsDirector {

	/**
	 * @var ToolbarBuilder
	 */
	private $toolbarBuilder;

	/**
	 * @var IOverflowBuilder
	 */
	private $overflowBuilder;

	/**
	 * @var MessageLocalizers
	 */
	private $messageLocalizer;

	/**
	 * Director responsible for Page Actions menu building
	 *
	 * @param ToolbarBuilder $toolbarBuilder The toolbar builder
	 * @param IOverflowBuilder $overflowBuilder The overflow menu builder
	 * @param MessageLocalizer $messageLocalizer Message localizer used to translate texts
	 */
	public function __construct( ToolbarBuilder $toolbarBuilder, IOverflowBuilder $overflowBuilder,
								 MessageLocalizer $messageLocalizer ) {
		$this->toolbarBuilder = $toolbarBuilder;
		$this->overflowBuilder = $overflowBuilder;
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * Build the menu data array that can be passed to views/javascript
	 * @param array $navUrls A set of navigation urls passed to the builder
	 * @param bool $doesHaveLangUrls Whether the page is also available in other languages or variants
	 * @return array
	 * @throws MWException
	 */
	public function buildMenu( array $navUrls, $doesHaveLangUrls ): array {
		$toolbar = $this->toolbarBuilder->getGroup( $doesHaveLangUrls );
		$overflowMenu = $this->overflowBuilder->getGroup( $navUrls );

		$menu = [
			'toolbar' => $toolbar->getEntries()
		];
		if ( $overflowMenu->hasEntries() ) {
			$menu[ 'overflowMenu' ] = [
				'item-id' => 'page-actions-overflow',
				'class' => MinervaUI::iconClass( 'page-actions-overflow' ),
				'text' => $this->messageLocalizer->msg( 'minerva-page-actions-overflow' ),
				'pageActions' => $overflowMenu->getEntries()
			];
		}
		return $menu;
	}

}
