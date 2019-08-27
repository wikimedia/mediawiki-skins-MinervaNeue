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

namespace MediaWiki\Minerva\Menu;

use IContextSource;
use MediaWiki\Special\SpecialPageFactory;
use MediaWiki\Minerva\Menu\Entries\AuthMenuEntry;
use MediaWiki\Minerva\Menu\Entries\HomeMenuEntry;
use MediaWiki\Minerva\Menu\Entries\LogInMenuEntry;
use MediaWiki\Minerva\Menu\Entries\LogOutMenuEntry;
use MediaWiki\Minerva\Menu\Entries\SingleMenuEntry;
use Message;
use MinervaUI;
use MWException;
use MWHttpRequest;
use SpecialMobileWatchlist;
use SpecialPage;
use Title;
use User;

/**
 * Set of all know menu items for easier building
 */
final class Definitions {

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var IContextSource
	 */
	private $context;

	/**
	 * @var SpecialPageFactory
	 */
	private $specialPageFactory;

	/**
	 * Initialize definitions helper class
	 *
	 * @param IContextSource $context
	 * @param SpecialPageFactory $factory
	 */
	public function __construct( IContextSource $context, SpecialPageFactory $factory ) {
		$this->user = $context->getUser();
		$this->context = $context;
		$this->specialPageFactory = $factory;
	}

	/**
	 * Inserts the Contributions menu item into the menu.
	 *
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertContributionsMenuItem( Group $group ) {
		$group->insertEntry( new SingleMenuEntry(
			'contributions',
			$this->context->msg( 'mobile-frontend-main-menu-contributions' )->escaped(),
			SpecialPage::getTitleFor( 'Contributions', $this->user->getName() )->getLocalURL()
		) );
	}

	/**
	 * Inserts the Watchlist menu item into the menu for a logged in user
	 *
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertWatchlistMenuItem( Group $group ) {
		$watchTitle = SpecialPage::getTitleFor( 'Watchlist' );

		// Watchlist link
		$watchlistQuery = [];
		// Avoid fatal when MobileFrontend not available (T171241)
		if ( class_exists( 'SpecialMobileWatchlist' ) ) {
			$view = $this->user->getOption( SpecialMobileWatchlist::VIEW_OPTION_NAME, false );
			$filter = $this->user->getOption( SpecialMobileWatchlist::FILTER_OPTION_NAME, false );
			if ( $view ) {
				$watchlistQuery['watchlistview'] = $view;
			}
			if ( $filter && $view === 'feed' ) {
				$watchlistQuery['filter'] = $filter;
			}
		}
		$group->insertEntry( new SingleMenuEntry(
			'watchlist',
			$this->context->msg( 'mobile-frontend-main-menu-watchlist' )->escaped(),
			$watchTitle->getLocalURL( $watchlistQuery )
		) );
	}

	/**
	 * Creates a log in or log out button.
	 *
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertLogInMenuItem( Group $group ) {
		$group->insertEntry( new LogInMenuEntry(
			$this->context,
			$this->newLogInOutQuery( $this->newReturnToQuery() )
		) );
	}

	/**
	 * Creates a log in or log out button.
	 *
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertLogOutMenuItem( Group $group ) {
		$group->insertEntry( new LogOutMenuEntry(
			$this->context,
			$this->newLogInOutQuery( $this->newReturnToQuery() )
		) );
	}

	/**
	 * Creates a login or logout button with a profile button.
	 *
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertAuthMenuItem( Group $group ) {
		$group->insertEntry( new AuthMenuEntry(
			$this->user,
			$this->context,
			$this->newLogInOutQuery( $this->newReturnToQuery() )
		) );
	}

	/**
	 * Build and insert Home link
	 * @param Group $group
	 */
	public function insertHomeItem( Group $group ) {
		$group->insertEntry( new HomeMenuEntry(
			'home',
			$this->context->msg( 'mobile-frontend-home-button' )->escaped(),
			Title::newMainPage()->getLocalURL()
		) );
	}

	/**
	 * Build and insert Random link
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertRandomItem( Group $group ) {
		$pageMsg = new Message( 'randompage-url' );
		if ( !$pageMsg->exists() ) {
			return;
		}
		$group->insert( 'random' )
			->addComponent( $this->context->msg( 'mobile-frontend-random-button' )->escaped(),
				Title::newFromText( $pageMsg->escaped() )->getLocalURL() . '#/random',
				MinervaUI::iconClass( 'random', 'before' ), [
					'id' => 'randomButton',
					'data-event-name' => 'menu.random',
				] );
	}

	/**
	 * If Nearby is supported, build and inject the Nearby link
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertNearbyIfSupported( Group $group ) {
		// Nearby link (if supported)
		if ( $this->specialPageFactory->exists( 'Nearby' ) ) {
			$group->insert( 'nearby', $isJSOnly = true )
				->addComponent(
					$this->context->msg( 'mobile-frontend-main-menu-nearby' )->escaped(),
					SpecialPage::getTitleFor( 'Nearby' )->getLocalURL(),
					MinervaUI::iconClass( 'nearby', 'before', 'nearby' ),
					[ 'data-event-name' => 'menu.nearby' ]
				);
		}
	}

	/**
	 * Build and insert the Settings link
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertMobileOptionsItem( Group $group ) {
		$title = $this->context->getTitle();
		$returnToTitle = $title->getPrefixedText();

		$group->insertEntry( new SingleMenuEntry(
			'settings',
			$this->context->msg( 'mobile-frontend-main-menu-settings' )->escaped(),
			SpecialPage::getTitleFor( 'MobileOptions' )
				->getLocalURL( [ 'returnto' => $returnToTitle ] )
		) );
	}

	/**
	 * Build and insert the Preferences link
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertPreferencesItem( Group $group ) {
		$group->insertEntry( new SingleMenuEntry(
			'preferences',
			$this->context->msg( 'preferences' )->escaped(),
			SpecialPage::getTitleFor( 'Preferences' )->getLocalURL(),
			true,
			'settings'
		) );
	}

	/**
	 * Build and insert About page link
	 * @param Group $group
	 */
	public function insertAboutItem( Group $group ) {
		$title = Title::newFromText( $this->context->msg( 'aboutpage' )->inContentLanguage()->text() );
		$msg = $this->context->msg( 'aboutsite' );
		if ( $title && !$msg->isDisabled() ) {
			$group->insert( 'about' )
				->addComponent( $msg->text(), $title->getLocalURL() );
		}
	}

	/**
	 * Build and insert Disclaimers link
	 * @param Group $group
	 */
	public function insertDisclaimersItem( Group $group ) {
		$title = Title::newFromText( $this->context->msg( 'disclaimerpage' )
			->inContentLanguage()->text() );
		$msg = $this->context->msg( 'disclaimers' );
		if ( $title && !$msg->isDisabled() ) {
			$group->insert( 'disclaimers' )
				->addComponent( $msg->text(), $title->getLocalURL() );
		}
	}

	/**
	 * Build and insert the RecentChanges link
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertRecentChanges( Group $group ) {
		$title = SpecialPage::getTitleFor( 'Recentchanges' );

		$group->insert( 'recentchanges' )
			->addComponent(
				$this->context->msg( 'recentchanges' )->escaped(),
				$title->getLocalURL(),
				MinervaUI::iconClass( 'recentchanges', 'before' ),
				[ 'data-event-name' => 'menu.recentchanges' ]
			);
	}

	/**
	 * Build and insert the SpecialPages link
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertSpecialPages( Group $group ) {
		$group->insertEntry(
			new SingleMenuEntry(
				'specialpages',
				$this->context->msg( 'specialpages' )->escaped(),
				SpecialPage::getTitleFor( 'Specialpages' )->getLocalURL()
			)
		);
	}

	/**
	 * Build and insert the CommunityPortal link
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertCommunityPortal( Group $group ) {
		$message = new Message( 'Portal-url' );
		if ( !$message->exists() ) {
			return;
		}
		$inContentLang = $message->inContentLanguage();
		$titleName = $inContentLang->plain();
		if ( $inContentLang->isDisabled() || MWHttpRequest::isValidURI( $titleName ) ) {
			return;
		}
		$title = Title::newFromText( $titleName );
		if ( $title === null || !$title->exists() ) {
			return;
		}

		$group->insertEntry( new SingleMenuEntry(
			'communityportal',
			$title->getText(),
			$title->getLocalURL()
		) );
	}

	/**
	 * @param array $returnToQuery
	 * @return array
	 */
	private function newLogInOutQuery( array $returnToQuery ): array {
		$ret = [];
		$title = $this->context->getTitle();
		$user = $this->user;
		if ( $title && !$title->isSpecial( 'Userlogin' ) ) {
			$ret[ 'returnto' ] = $title->getPrefixedText();
		}
		if ( $user && $user->isLoggedIn() ) {
			$ret['logoutToken'] = $user->getEditToken( 'logoutToken', $this->context->getRequest() );
		} else {
			// unset campaign on login link so as not to interfere with A/B tests
			unset( $returnToQuery['campaign'] );
		}
		if ( !empty( $returnToQuery ) ) {
			$ret['returntoquery'] = wfArrayToCgi( $returnToQuery );
		}
		return $ret;
	}

	/**
	 * Retrieve current query parameters from Request object so system can pass those
	 * to the Login/logout links
	 * Some parameters are disabled (like title), as the returnto will be replaced with
	 * the current page.
	 * @return array
	 */
	private function newReturnToQuery(): array {
		$returnToQuery = [];
		if ( !$this->context->getRequest()->wasPosted() ) {
			$returnToQuery = $this->context->getRequest()->getValues();
			unset( $returnToQuery['title'] );
			unset( $returnToQuery['returnto'] );
			unset( $returnToQuery['returntoquery'] );
		}
		return $returnToQuery;
	}
}
