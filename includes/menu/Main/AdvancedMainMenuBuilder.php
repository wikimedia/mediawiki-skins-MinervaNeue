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
use User;
use MediaWiki\Minerva\Menu\Definitions;
use MediaWiki\Minerva\Menu\Group;

/**
 * A menu builder that provides additional menu entries that match
 * Advanced Mobile Contributions project requirements. This menu
 * is used when AMC SkinOption flag is set to true.
 *
 * @package MediaWiki\Minerva\Menu\Main
 */
final class AdvancedMainMenuBuilder implements IMainMenuBuilder {
	/**
	 * @var bool
	 */
	private $showMobileOptions;

	/**
	 * Currently logged in user
	 * @var User
	 */
	private $user;

	/**
	 * @var Definitions
	 */
	private $definitions;

	/**
	 * Initialize the Default Main Menu builder
	 *
	 * @param bool $showMobileOptions Show MobileOptions instead of Preferences
	 * @param User $user The current user
	 * @param Definitions $definitions A menu items definitions set
	 */
	public function __construct( $showMobileOptions, User $user, Definitions $definitions ) {
		$this->showMobileOptions = $showMobileOptions;
		$this->user = $user;
		$this->definitions = $definitions;
	}

	/**
	 * @inheritDoc
	 * @return Group[]
	 * @throws FatalError
	 * @throws MWException
	 */
	public function getGroups(): array {
		return [
			BuilderUtil::getDiscoveryTools( $this->definitions ),
			$this->getSiteTools(),
			BuilderUtil::getConfigurationTools( $this->definitions, $this->showMobileOptions ),
		];
	}

	/**
	 * @inheritDoc
	 * @throws FatalError
	 * @throws MWException
	 */
	public function getSiteLinks(): Group {
		return BuilderUtil::getSiteLinks( $this->definitions );
	}

	/**
	 * Prepares a list of links that have the purpose of discovery in the main navigation menu
	 * @return Group
	 * @throws FatalError
	 * @throws MWException
	 */
	private function getSiteTools(): Group {
		$group = new Group();

		$this->definitions->insertRecentChanges( $group );
		$this->definitions->insertSpecialPages( $group );
		$this->definitions->insertCommunityPortal( $group );

		// Allow other extensions to add or override tools
		Hooks::run( 'MobileMenu', [ 'sitetools', &$group ] );
		return $group;
	}
}
