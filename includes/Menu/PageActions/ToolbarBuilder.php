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

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Context\IContextSource;
use MediaWiki\Minerva\LanguagesHelper;
use MediaWiki\Minerva\Menu\Entries\IMenuEntry;
use MediaWiki\Minerva\Menu\Entries\LanguageSelectorEntry;
use MediaWiki\Minerva\Menu\Entries\SingleMenuEntry;
use MediaWiki\Minerva\Menu\Group;
use MediaWiki\Minerva\Permissions\IMinervaPagePermissions;
use MediaWiki\Minerva\SkinOptions;
use MediaWiki\Minerva\Skins\SkinUserPageHelper;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MediaWiki\Watchlist\WatchlistManager;

class ToolbarBuilder {

	private readonly bool $watchlistExpiryEnabled;
	private readonly Title $loginTitle;

	/**
	 * ServiceOptions needed.
	 */
	public const CONSTRUCTOR_OPTIONS = [
		'WatchlistExpiry',
	];

	/**
	 * Build Group containing icons for toolbar
	 * @param Title $title Article title user is currently browsing
	 * @param User $user Currently logged in user
	 * @param IContextSource $context
	 * @param IMinervaPagePermissions $permissions Minerva permissions system
	 * @param SkinOptions $skinOptions
	 * @param SkinUserPageHelper $relevantUserPageHelper User Page helper. The
	 * UserPageHelper passed should always be specific to the user page Title. If on a
	 * user talk page, UserPageHelper should be instantiated with the user page
	 * Title and NOT with the user talk page Title.
	 * @param LanguagesHelper $languagesHelper Helper to check title languages/variants
	 * @param ServiceOptions $options
	 * @param WatchlistManager $watchlistManager
	 */
	public function __construct(
		private readonly Title $title,
		private readonly User $user,
		private readonly IContextSource $context,
		private readonly IMinervaPagePermissions $permissions,
		private readonly SkinOptions $skinOptions,
		private readonly SkinUserPageHelper $relevantUserPageHelper,
		private readonly LanguagesHelper $languagesHelper,
		ServiceOptions $options,
		private readonly WatchlistManager $watchlistManager,
		?Title $loginTitle = null
	) {
		$this->watchlistExpiryEnabled = $options->get( 'WatchlistExpiry' );
		$this->loginTitle = $loginTitle ?: SpecialPage::getTitleFor( 'Userlogin' );
	}

	/**
	 * Return the primary subscribe action for this page. Loops through all
	 * potential actions in order of priority and returns the first one found.
	 *
	 * @param array $views as provided by Skin::getTemplateData
	 * @param array $actions as provided by Skin::getTemplateData
	 * @return string of what should be considered the primary action key. Will return
	 * 	watch if none of the actions are found in $views or $actions.
	 */
	public static function findPrimarySubscribeAction( array $views, array $actions ): string {
		$primarySubscribeAction = [
			// 'bookmark' defined in ReadingLists
			'bookmark',
			// "subscribe" icon on talk pages defined in Extension:DiscussionTools
			'dt-page-subscribe',
			// watchstar provided by core fallback if neither of the above is present
			'unwatch',
			'watch'
		];
		foreach ( $primarySubscribeAction as $actionKey ) {
			if ( isset( $views[ $actionKey ] ) || isset( $actions[ $actionKey ] ) ) {
				return $actionKey;
			}
		}
		// Return the last item in $primarySubscribeAction
		return $actionKey;
	}

	/**
	 * @param array $actions
	 * @param array $views
	 * @return Group
	 */
	public function getGroup( array $actions, array $views ): Group {
		$group = new Group( 'p-views' );
		$permissions = $this->permissions;
		$userPageOrUserTalkPageWithOverflowMode = $this->skinOptions->get( SkinOptions::TOOLBAR_SUBMENU )
			&& $this->relevantUserPageHelper->isUserPage();

		if ( !$userPageOrUserTalkPageWithOverflowMode && $permissions->isAllowed(
			IMinervaPagePermissions::SWITCH_LANGUAGE ) ) {
			$group->insertEntry( new LanguageSelectorEntry(
				$this->title,
				$this->languagesHelper->doesTitleHasLanguagesOrVariants(
					$this->context->getOutput(),
					$this->title
				),
				$this->context,
				true
			) );
		}

		$primarySubscribeActionKey = self::findPrimarySubscribeAction( $views, $actions );
		// This code adds the watchstar for anonymous users if it is not present in the $views array.
		// On mobile we show it to anonymous user but we don't do this on desktop.
		// In future we can consider removing this if we show bookmark to all logged out users.
		// This code is intended to guarantee that every page has a primary subscribe action for
		// logged out users.
		if ( !$this->user->isNamed() && !isset( $views[ $primarySubscribeActionKey ] ) ) {
			// no action was found so force insert a watchstar
			$watchData = [
				'icon' => 'star',
				'class' => '',
				'href' => $this->getLoginUrl( [ 'returnto' => $this->title ] ),
				'text' => $this->context->msg( 'watch' ),
			];
			if ( $permissions->isAllowed( IMinervaPagePermissions::WATCHABLE ) ) {
				$group->insertEntry( $this->createWatchPageAction( 'watch', $watchData ) );
			}
		}

		$user = $this->relevantUserPageHelper->getPageUser();
		$isUserPageAccessible = $this->relevantUserPageHelper->isUserPageAccessibleToCurrentUser();
		// needs to be inserted after history
		if ( $user && $isUserPageAccessible ) {
			// T235681: Contributions icon should be added to toolbar on user pages
			// and user talk pages for all users
			$group->insertEntry( $this->createContributionsPageAction( $user ) );
		}

		// T388909: Iterate through all view actions. Known edit actions (edit, ve-edit, viewsource) are
		// always included if the user has edit permissions, even if they do not define an icon.
		// All other actions must define an icon to be included in the mobile toolbar.
		// This ensures compatibility with hook-defined entries (e.g., 'bookmark') while preserving
		// fallback icons for core actions.
		foreach ( $views as $key => $viewData ) {
			$isEditAction = in_array( $key, [ 've-edit', 'viewsource', 'edit' ] );

			if ( $isEditAction ) {
				// Only insert edit actions if user has permission
				if ( $permissions->isAllowed( IMinervaPagePermissions::CONTENT_EDIT ) ) {
					$group->insertEntry( $this->createEditPageAction( $key, $viewData ) );
				}
			} elseif ( isset( $viewData[ 'icon' ] ) ) {
				self::copyItemToGroup( $viewData, $key, $group, $this->context );
			}
		}
		return $group;
	}

	/**
	 * @param array $viewData
	 * @param string $key
	 * @param Group $group
	 * @param IContextSource $context
	 */
	private static function copyItemToGroup( $viewData, $key, $group, $context ) {
		$entry = new SingleMenuEntry(
			'page-actions-' . $key,
			$viewData['text'] ?? $key,
			$viewData['href'] ?? '#',
			$viewData['class'] ?? '',
			true,
			$viewData['array-attributes'] ?? []
		);

		$entry
			->setIcon( $viewData['icon'] )
			->trackClicks( $key )
			->setTitle( $context->msg( 'tooltip-' . $key ) )
			->setNodeID( 'ca-' . $key );

		$group->insertEntry( $entry );
	}

	/**
	 * Create Contributions page action visible on user pages or user talk pages
	 * for given $user
	 *
	 * @param UserIdentity $user Determines what the contribution page action will link to
	 * @return IMenuEntry
	 */
	protected function createContributionsPageAction( UserIdentity $user ): IMenuEntry {
		$label = $this->context->msg( 'mobile-frontend-user-page-contributions' );

		$entry = new SingleMenuEntry(
			'page-actions-contributions',
			$label->escaped(),
			SpecialPage::getTitleFor( 'Contributions', $user->getName() )->getLocalURL() );
		$entry->setTitle( $label )
			->trackClicks( 'contributions' )
			->setIcon( 'userContributions' );

		return $entry;
	}

	/**
	 * Creates the "edit" page action: the well-known pencil icon that, when tapped, will open an
	 * editor.
	 *
	 * @param string $key
	 * @param array $editAction
	 * @return IMenuEntry An edit page actions menu entry
	 */
	protected function createEditPageAction( string $key, array $editAction ): IMenuEntry {
		$title = $this->title;

		$id = $editAction['single-id'] ?? 'ca-edit';
		$entry = new SingleMenuEntry(
			'page-actions-' . $key,
			$editAction['text'],
			$editAction['href'],
			'edit-page'
		);
		$iconFallback = $key === 'viewsource' ? 'editLock' : 'edit';
		$icon = $editAction['icon'] ?? $iconFallback;
		$entry->setIcon( $icon )
			->trackClicks( $key )
			->setTitle( $this->context->msg( 'tooltip-' . $id ) )
			->setNodeID( $id );
		return $entry;
	}

	/**
	 * Creates the "watch" or "unwatch" action: the well-known star icon that, when tapped, will
	 * add the page to or remove the page from the user's watchlist; or, if the user is logged out,
	 * will direct the user's UA to Special:Login.
	 *
	 * @param string $watchKey either watch or unwatch
	 * @param array $watchData
	 * @return IMenuEntry An watch/unwatch page actions menu entry
	 */
	protected function createWatchPageAction( string $watchKey, array $watchData ): IMenuEntry {
		$entry = new SingleMenuEntry(
			'page-actions-watch',
			$watchData['text'],
			$watchData['href'],
			$watchData[ 'class' ],
			$this->permissions->isAllowed( IMinervaPagePermissions::WATCH )
		);
		$icon = $watchData['icon'] ?? '';
		return $entry->trackClicks( $watchKey )
			->setIcon( $icon )
			->setTitle( $this->context->msg( $watchKey ) )
			->setNodeID( 'ca-watch' );
	}

	/**
	 * Creates a history action: An icon that links to the mobile history page.
	 *
	 * @param array $historyAction
	 * @return IMenuEntry A menu entry object that represents a map of HTML attributes
	 * and a 'text' property to be used with the pageActionMenu.mustache template.
	 */
	protected function getHistoryPageAction( array $historyAction ): IMenuEntry {
		$entry = new SingleMenuEntry(
			'page-actions-history',
			$historyAction['text'],
			$historyAction['href'],
		);
		$icon = $historyAction['icon'] ?? 'history';
		$entry->setIcon( $icon )
			->trackClicks( 'history' );
		return $entry;
	}

	/**
	 * Prepares a url to the Special:UserLogin with query parameters
	 * @param array $query
	 * @return string
	 */
	private function getLoginUrl( $query ): string {
		return $this->loginTitle->getLocalURL( $query );
	}
}
