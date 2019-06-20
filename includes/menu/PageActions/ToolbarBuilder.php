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

use ExtensionRegistry;
use Hooks;
use MediaWiki\Minerva\Menu\Group;
use MediaWiki\Minerva\Menu\LanguageSelectorEntry;
use MediaWiki\Permissions\PermissionManager;
use MessageLocalizer;
use MinervaUI;
use MWException;
use SkinMinerva;
use SpecialMobileHistory;
use SpecialPage;
use Title;
use User;

class ToolbarBuilder {

	/**
	 * @var User Currently logged in user
	 */
	private $user;
	/**
	 * @var Title Article title user is currently browsing
	 */
	private $title;
	/**
	 * Temporary variable to access isAllowedPageAction() method.
	 * FIXME: Create MinervaAllowedPageActions service
	 * @var SkinMinerva user skin
	 */
	private $skin;
	/**
	 * @var MessageLocalizer Message localizer to generate localized texts
	 */
	private $messageLocalizer;

	/**
	 * @var PermissionManager
	 */
	private $permissionsManager;
	/**
	 * Build Group containing icons for toolbar
	 * @param SkinMinerva $skin User Skin
	 * @param Title $title Article title user is currently browsing
	 * @param User $user Currently logged in user
	 * @param MessageLocalizer $msgLocalizer Message localizer to generate localized texts
	 * @param PermissionManager $permissionManager Mediawiki Permissions Manager
	 */
	public function __construct( SkinMinerva $skin, Title $title, User $user,
								 MessageLocalizer $msgLocalizer, PermissionManager $permissionManager ) {
		$this->skin = $skin;
		$this->title = $title;
		$this->user = $user;
		$this->messageLocalizer = $msgLocalizer;
		$this->permissionsManager = $permissionManager;
	}

	/**
	 * @param bool $doesPageHaveLanguages Whether the page is also available in other languages
	 * or variants
	 * @return Group
	 * @throws MWException
	 */
	public function getGroup( $doesPageHaveLanguages ) {
		$group = new Group();

		if ( $this->skin->isAllowedPageAction( 'switch-language' ) ) {
			$group->insertEntry(
				new LanguageSelectorEntry( $this->title, $doesPageHaveLanguages, $this->messageLocalizer )
			);
		}

		if ( $this->skin->isAllowedPageAction( 'watch' ) ) {
			$group->insertEntry( $this->createWatchPageAction() );
		}

		if ( $this->skin->isAllowedPageAction( 'history' ) ) {
			$group->insertEntry( $this->getHistoryPageAction() );
		}

		Hooks::run( 'MobileMenu', [ 'pageactions.toolbar', &$group ] );

		// We want the edit icon/action always to be the last element on the toolbar list
		if ( $this->skin->isAllowedPageAction( 'edit' ) ) {
			$group->insertEntry( $this->createEditPageAction() );
		}
		return $group;
	}

	/**
	 * Creates the "edit" page action: the well-known pencil icon that, when tapped, will open an
	 * editor with the lead section loaded.
	 *
	 * @return PageActionMenuEntry An edit page actions menu entry
	 * @throws MWException
	 * @throws \Exception
	 */
	protected function createEditPageAction() {
		$title = $this->title;
		$user = $this->user;
		$pm = $this->permissionsManager;

		$editArgs = [ 'action' => 'edit' ];
		if ( $title->isWikitextPage() ) {
			// If the content model is wikitext we'll default to editing the lead section.
			// Full wikitext editing is hard on mobile devices.
			$editArgs['section'] = SkinMinerva::LEAD_SECTION_NUMBER;
		}

		$userQuickEditCheck =
			$pm->userCan( 'edit', $user, $title, PermissionManager::RIGOR_QUICK ) &&
			(
				$title->exists() ||
				$pm->userCan( 'create', $user, $title, PermissionManager::RIGOR_QUICK )
			);

		$userBlockInfo = $user->isAnon() ? false : $pm->isBlockedFrom( $user, $title, true );
		$userCanEdit = $userQuickEditCheck && !$userBlockInfo;

		$entry = new PageActionMenuEntry(
			'page-actions-edit',
			$title->getLocalURL( $editArgs ),
			'edit-page '
			. MinervaUI::iconClass( $userCanEdit ? 'edit-enabled' : 'edit', 'element' ),
			$this->messageLocalizer->msg( 'mobile-frontend-editor-edit' )
		);
		return $entry
			->setTitle( $this->messageLocalizer->msg( 'mobile-frontend-pageaction-edit-tooltip' ) )
			->setNodeID( 'ca-edit' );
	}

	/**
	 * Creates the "watch" or "unwatch" action: the well-known star icon that, when tapped, will
	 * add the page to or remove the page from the user's watchlist; or, if the user is logged out,
	 * will direct the user's UA to Special:Login.
	 *
	 * @return PageActionMenuEntry An watch/unwatch page actions menu entry
	 * @throws MWException
	 */
	protected function createWatchPageAction() {
		$title = $this->title;
		$user = $this->user;
		$isWatched = $title && $user->isLoggedIn() && $user->isWatched( $title );
		$mode = $isWatched ? 'watch' : 'unwatch';
		$href = $user->isAnon()
			? $this->getLoginUrl( [ 'returnto' => $title ] )
			: $title->getLocalURL( [ 'action' => $mode ] );

		if ( $isWatched ) {
			$msg = $this->messageLocalizer->msg( 'unwatchthispage' );
			$icon = 'watched';
		} else {
			$msg = $this->messageLocalizer->msg( 'watchthispage' );
			$icon = 'watch';
		}
		$iconClass = MinervaUI::iconClass( $icon, 'element', 'watch-this-article' ) . ' jsonly';
		if ( $isWatched ) {
			$iconClass .= ' watched';
		}

		$entry = new PageActionMenuEntry(
			'page-actions-watch',
			$href,
			$iconClass,
			$msg
		);
		return $entry
			->setTitle( $msg )
			->setNodeID( 'ca-watch' );
	}

	/**
	 * Creates a history action: An icon that links to the mobile history page.
	 *
	 * @return PageActionMenuEntry A menu entry object that represents a map of HTML attributes
	 * and a 'text' property to be used with the pageActionMenu.mustache template.
	 * @throws MWException
	 */
	protected function getHistoryPageAction() {
		return new PageActionMenuEntry(
			'page-actions-history',
			$this->getHistoryUrl( $this->title ),
			MinervaUI::iconClass( 'clock' ),
			$this->messageLocalizer->msg( 'mobile-frontend-history' )
		);
	}

	/**
	 * Get the URL for the history page for the given title using Special:History
	 * when available.
	 * FIXME: temporary duplicated code, same as SkinMinerva::getHistoryUrl()
	 * @param Title $title The Title object of the page being viewed
	 * @return string
	 * @throws MWException
	 */
	protected function getHistoryUrl( Title $title ) {
		return ExtensionRegistry::getInstance()->isLoaded( 'MobileFrontend' ) &&
			   SpecialMobileHistory::shouldUseSpecialHistory( $title, $this->user ) ?
			SpecialPage::getTitleFor( 'History', $title )->getLocalURL() :
			$title->getLocalURL( [ 'action' => 'history' ] );
	}

	/**
	 * Prepares a url to the Special:UserLogin with query parameters
	 * @param array $query
	 * @return string
	 * @throws MWException
	 */
	private function getLoginUrl( $query ) {
		return SpecialPage::getTitleFor( 'Userlogin' )->getLocalURL( $query );
	}
}
