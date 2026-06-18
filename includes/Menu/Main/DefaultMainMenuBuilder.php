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

use MediaWiki\Minerva\Menu\Definitions;
use MediaWiki\Minerva\Menu\Entries\SingleMenuEntry;
use MediaWiki\Minerva\Menu\Group;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentityUtils;

/**
 * Used to build default (available for everyone by default) main menu
 */
final class DefaultMainMenuBuilder implements IMainMenuBuilder {

	/**
	 * Initialize the Default Main Menu builder
	 *
	 * @param bool $showMobileOptions Show MobileOptions instead of Preferences
	 * @param bool $showDonateLink whether to show the donate link
	 * @param User $user The current user
	 * @param Definitions $definitions A menu items definitions set
	 * @param UserIdentityUtils $userIdentityUtils
	 */
	public function __construct(
		private readonly bool $showMobileOptions,
		private readonly bool $showDonateLink,
		private readonly User $user,
		private readonly Definitions $definitions,
		private readonly UserIdentityUtils $userIdentityUtils,
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getDiscoveryGroup( array $navigationTools ): Group {
		return BuilderUtil::getDiscoveryTools( $this->definitions, $navigationTools );
	}

	/**
	 * @inheritDoc
	 */
	public function getDonateGroup(): Group {
		return BuilderUtil::getDonateGroup( $this->definitions, $this->showDonateLink );
	}

	/**
	 * @inheritDoc
	 */
	public function getInteractionToolsGroup(): Group {
		return new Group( 'p-interaction' );
	}

	/**
	 * @inheritDoc
	 */
	public function getSiteLinks(): Group {
		return BuilderUtil::getSiteLinks( $this->definitions );
	}

	/**
	 * Builds the anonymous settings group.
	 *
	 * @inheritDoc
	 */
	public function getSettingsGroup(): Group {
		$group = new Group( 'pt-preferences' );
		// Show settings group always, since personal mode has been removed
		if ( $this->showMobileOptions ) {
			$this->definitions->insertMobileOptionsItem( $group );
		}
		return $group;
	}

	/**
	 * Builds the personal tools menu item group.
	 *
	 * ... by adding the Watchlist, Settings, and Log{in,out} menu items in the given order.
	 *
	 * @inheritDoc
	 */
	public function getPersonalToolsGroup( array $personalTools ): Group {
		$group = new Group( 'p-personal' );
		$excludeKeyList = [ 'betafeatures', 'mytalk', 'sandbox' ];

		$isTemp = $this->userIdentityUtils->isTemp( $this->user );
		if ( $isTemp ) {
			$excludeKeyList[] = 'mycontris';
		}

		// If personal tools are handled elsewhere, no need to output them in this menu.
		if ( $this->user->isRegistered() ) {
			return $group;
		}

		if ( !$this->user->isRegistered() ) {
			// For anonymous users exclude all links except login and create account.
			$keysToNotExclude = [ 'login', 'login-private', 'createaccount' ];

			$excludeKeyList = array_diff(
				array_keys( $personalTools ),
				$keysToNotExclude
			);
		}
		foreach ( $personalTools as $key => $item ) {
			// Default to EditWatchlist if $user has no edits
			// Many users use the watchlist like a favorites list without ever editing.
			// [T88270].
			if ( $key === 'watchlist' && $this->user->getEditCount() === 0 ) {
				$item['href'] = SpecialPage::getTitleFor( 'EditWatchlist' )->getLocalURL();
			}
			$href = $item['href'] ?? null;
			if ( $href && !in_array( $key, $excludeKeyList ) ) {
				// Substitute preference if $showMobileOptions is set.
				if ( $this->showMobileOptions && $key === 'preferences' ) {
					$this->definitions->insertMobileOptionsItem( $group );
				} else {
					$icon = $item['icon'] ?? null;
					$entry = SingleMenuEntry::create(
						$key,
						$item['text'],
						$href,
						$item['class'] ?? '',
						$icon
					);

					$entry->trackClicks( $key );
					$group->insertEntry( $entry );
				}
			}
		}
		return $group;
	}
}
