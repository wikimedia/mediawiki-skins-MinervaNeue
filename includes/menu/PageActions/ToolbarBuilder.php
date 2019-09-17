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
use MediaWiki\Minerva\LanguagesHelper;
use MediaWiki\Minerva\Menu\Entries\IMenuEntry;
use MediaWiki\Minerva\Menu\Entries\LanguageSelectorEntry;
use MediaWiki\Minerva\Menu\Group;
use MediaWiki\Minerva\Permissions\IMinervaPagePermissions;
use MediaWiki\Minerva\SkinOptions;
use MediaWiki\Minerva\SkinUserPageHelper;
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
	 * @var MessageLocalizer Message localizer to generate localized texts
	 */
	private $messageLocalizer;
	/**
	 * @var IMinervaPagePermissions
	 */
	private $permissions;

	/**
	 * @var SkinOptions
	 */
	private $skinOptions;

	/**
	 * @var SkinUserPageHelper
	 */
	private $userPageHelper;

	/**
	 * @var LanguagesHelper
	 */
	private $languagesHelper;
	/**
	 * Build Group containing icons for toolbar
	 * @param Title $title Article title user is currently browsing
	 * @param User $user Currently logged in user
	 * @param MessageLocalizer $msgLocalizer Message localizer to generate localized texts
	 * @param IMinervaPagePermissions $permissions Minerva permissions system
	 * @param SkinOptions $skinOptions Skin options
	 * @param SkinUserPageHelper $userPageHelper User Page helper
	 * @param LanguagesHelper $languagesHelper Helper to check title languages/variants
	 */
	public function __construct(
		Title $title,
		User $user,
		MessageLocalizer $msgLocalizer,
		IMinervaPagePermissions $permissions,
		SkinOptions $skinOptions,
		SkinUserPageHelper $userPageHelper,
		LanguagesHelper $languagesHelper
	) {
		$this->title = $title;
		$this->user = $user;
		$this->messageLocalizer = $msgLocalizer;
		$this->permissions = $permissions;
		$this->skinOptions = $skinOptions;
		$this->userPageHelper = $userPageHelper;
		$this->languagesHelper = $languagesHelper;
	}

	/**
	 * @return Group
	 * @throws MWException
	 */
	public function getGroup(): Group {
		$group = new Group();
		$permissions = $this->permissions;
		$userPageWithOveflowMode = $this->skinOptions->get( SkinOptions::TOOLBAR_SUBMENU ) &&
			$this->userPageHelper->isUserPage();

		if ( !$userPageWithOveflowMode && $permissions->isAllowed(
			IMinervaPagePermissions::SWITCH_LANGUAGE ) ) {
			$group->insertEntry( new LanguageSelectorEntry(
				$this->title,
				$this->languagesHelper->doesTitleHasLanguagesOrVariants( $this->title ),
				$this->messageLocalizer,
				MinervaUI::iconClass( 'language-switcher', 'element' ) )
			);
		}

		if ( $permissions->isAllowed( IMinervaPagePermissions::WATCH ) ) {
			$group->insertEntry( $this->createWatchPageAction() );
		}

		if ( $permissions->isAllowed( IMinervaPagePermissions::HISTORY ) ) {
			$group->insertEntry( $this->getHistoryPageAction() );
		}

		if ( $userPageWithOveflowMode ) {
			// User links are hidden when Overflow menu is visible. We want to show Contributions
			// link on toolbar only when overflow is visible
			$group->insertEntry( $this->createContributionsPageAction() );
		}

		Hooks::run( 'MobileMenu', [ 'pageactions.toolbar', &$group ] );

		// We want the edit icon/action always to be the last element on the toolbar list
		if ( $permissions->isAllowed( IMinervaPagePermissions::CONTENT_EDIT ) ) {
			$group->insertEntry( $this->createEditPageAction() );
		}
		return $group;
	}

	/**
	 * Create Contributions page action visible on user pages
	 *
	 * @return IMenuEntry
	 */
	protected function createContributionsPageAction(): IMenuEntry {
		$pageUser = $this->userPageHelper->getPageUser();
		$label = $this->messageLocalizer->msg( 'mobile-frontend-user-page-contributions' );

		return PageActionMenuEntry::create(
			'page-actions-contributions',
			SpecialPage::getTitleFor( 'Contributions', $pageUser )->getLocalURL(),
			MinervaUI::iconClass( 'contributions' ),
			$label,
			'contributions'

		)->setTitle( $label );
	}

	/**
	 * Creates the "edit" page action: the well-known pencil icon that, when tapped, will open an
	 * editor with the lead section loaded.
	 *
	 * @return PageActionMenuEntry An edit page actions menu entry
	 * @throws MWException
	 * @throws \Exception
	 */
	protected function createEditPageAction(): IMenuEntry {
		$title = $this->title;

		$editArgs = [ 'action' => 'edit' ];
		if ( $title->isWikitextPage() ) {
			// If the content model is wikitext we'll default to editing the lead section.
			// Full wikitext editing is hard on mobile devices.
			$editArgs['section'] = SkinMinerva::LEAD_SECTION_NUMBER;
		}

		$editOrCreate = $this->permissions->isAllowed( IMinervaPagePermissions::EDIT_OR_CREATE );

		$entry = new PageActionMenuEntry(
			'page-actions-edit',
			$title->getLocalURL( $editArgs ),
			'edit-page '
			. MinervaUI::iconClass( $editOrCreate ? 'edit-enabled' : 'edit', 'element' ),
			$this->messageLocalizer->msg( 'mobile-frontend-editor-edit' ),
			'edit'
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
	protected function createWatchPageAction(): IMenuEntry {
		$title = $this->title;
		$user = $this->user;
		$isWatched = $title && $user->isLoggedIn() && $user->isWatched( $title );
		$newModeToSet = $isWatched ? 'unwatch' : 'watch';
		$href = $user->isAnon()
			? $this->getLoginUrl( [ 'returnto' => $title ] )
			: $title->getLocalURL( [ 'action' => $newModeToSet ] );

		if ( $isWatched ) {
			$msg = $this->messageLocalizer->msg( 'unwatchthispage' );
			$icon = 'unStar-progressive';
		} else {
			$msg = $this->messageLocalizer->msg( 'watchthispage' );
			$icon = 'star-base20';
		}
		$iconClass = MinervaUI::iconClass( $icon, 'element', 'watch-this-article', 'wikimedia' );
		if ( $isWatched ) {
			$iconClass .= ' watched';
		}

		$entry = new PageActionMenuEntry(
			'page-actions-watch',
			$href,
			$iconClass,
			$msg,
			$newModeToSet
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
	protected function getHistoryPageAction(): IMenuEntry {
		return new PageActionMenuEntry(
			'page-actions-history',
			$this->getHistoryUrl( $this->title ),
			MinervaUI::iconClass( 'clock' ),
			$this->messageLocalizer->msg( 'mobile-frontend-history' ),
			'history'
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
