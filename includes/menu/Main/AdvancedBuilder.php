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

use FatalError;
use Hooks;
use MWException;
use MediaWiki\Minerva\Menu\Group;

/**
 * A menu builder that provides additional menu entries that match
 * Advanced Mobile Contributions project requirements. This menu
 * is used when AMC SkinOption flag is set to true.
 *
 * @package MediaWiki\Minerva\Menu\Main
 */
class AdvancedBuilder extends DefaultBuilder {

	/**
	 * @return array|Group[]
	 * @throws FatalError
	 * @throws MWException
	 */
	public function getGroups() {
		return [
			$this->getDiscoveryTools(),
			$this->getPersonalTools(),
			$this->getSiteTools(),
			$this->getConfigurationTools(),
		];
	}

	/**
	 * Prepares a list of links that have the purpose of discovery in the main navigation menu
	 * @return Group
	 * @throws FatalError
	 * @throws MWException
	 */
	public function getSiteTools() {
		$group = new Group();

		$this->definitions->insertSpecialPages( $group );
		$this->definitions->insertCommunityPortal( $group );

		// Allow other extensions to add or override tools
		Hooks::run( 'MobileMenu', [ 'sitetools', &$group ] );
		return $group;
	}
}
