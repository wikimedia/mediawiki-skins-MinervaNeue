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
namespace MediaWiki\Minerva\Menu\Main;

/**
 * Director responsible for building Main Menu
 */
final class Director {

	/**
	 * @var IBuilder
	 */
	private $builder;

	/**
	 * @var array
	 */
	private $menuData;

	/**
	 * Director responsible for Main Menu building
	 *
	 * @param IBuilder $builder
	 */
	public function __construct( IBuilder $builder ) {
		$this->builder = $builder;
	}

	/**
	 * Returns a data representation of the main menus
	 * @return array
	 */
	public function getMenuData(): array {
		if ( $this->menuData === null ) {
			$this->menuData = $this->buildMenu();
		}
		return $this->menuData;
	}

	/**
	 * Build the menu data array that can be passed to views/javascript
	 * @return array
	 */
	private function buildMenu(): array {
		$menuData = [
			'groups' => [],
			'sitelinks' => $this->builder->getSiteLinks()->getEntries()
		];
		foreach ( $this->builder->getGroups() as $group ) {
			if ( $group->hasEntries() ) {
				$menuData['groups'][] = $group->getEntries();
			}
		}
		return $menuData;
	}

}
