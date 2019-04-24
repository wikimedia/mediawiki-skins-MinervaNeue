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
 * Used to build default (available for everyone by default) main menu
 */
class DefaultBuilder implements IBuilder {

	/**
	 * @var bool
	 */
	private $showMobileOptions;

	/**
	 * Currently logged in user
	 * @var User
	 */
	protected $user;

	/**
	 * @var Definitions
	 */
	protected $definitions;

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
	 * @return Group[]
	 * @throws FatalError
	 * @throws MWException
	 */
	public function getGroups() {
		return [
			$this->getDiscoveryTools(),
			$this->getPersonalTools(),
			$this->getConfigurationTools(),
		];
	}

	/**
	 * Prepares a list of links that have the purpose of discovery in the main navigation menu
	 * @return Group
	 * @throws FatalError
	 * @throws MWException
	 */
	protected function getDiscoveryTools() {
		$group = new Group();

		$this->definitions->insertHomeItem( $group );
		$this->definitions->insertRandomItem( $group );
		$this->definitions->insertNearbyIfSupported( $group );

		// Allow other extensions to add or override tools
		Hooks::run( 'MobileMenu', [ 'discovery', &$group ] );
		return $group;
	}

	/**
	 * Builds the personal tools menu item group.
	 *
	 * ... by adding the Watchlist, Settings, and Log{in,out} menu items in the given order.
	 *
	 * @return Group
	 * @throws FatalError
	 * @throws MWException
	 */
	protected function getPersonalTools() {
		$group = new Group();

		$this->definitions->insertLogInOutMenuItem( $group );

		if ( $this->user->isLoggedIn() ) {
			$this->definitions->insertWatchlistMenuItem( $group );
			$this->definitions->insertContributionsMenuItem( $group );
		}

		// Allow other extensions to add or override tools
		Hooks::run( 'MobileMenu', [ 'personal', &$group ] );
		return $group;
	}

	/**
	 * Like <code>SkinMinerva#getDiscoveryTools</code> and <code>#getPersonalTools</code>, create
	 * a group of configuration-related menu items. Currently, only the Settings menu item is in the
	 * group.
	 *
	 * @return Group
	 * @throws MWException
	 */
	protected function getConfigurationTools() {
		$group = new Group();

		$this->showMobileOptions ?
			$this->definitions->insertMobileOptionsItem( $group ) :
			$this->definitions->insertPreferencesItem( $group );

		return $group;
	}

	/**
	 * Returns an array of sitelinks to add into the main menu footer.
	 * @return Group Collection of site links
	 * @throws MWException
	 */
	public function getSiteLinks() {
		$group = new Group();

		$this->definitions->insertAboutItem( $group );
		$this->definitions->insertDisclaimersItem( $group );
		// Allow other extensions to add or override tools
		Hooks::run( 'MobileMenu', [ 'sitelinks', &$group ] );
		return $group;
	}

}
