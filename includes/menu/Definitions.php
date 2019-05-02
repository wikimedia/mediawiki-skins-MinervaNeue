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
use Message;
use MinervaUI;
use MWException;
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
		$group->insert( 'contribs' )
			->addComponent(
				$this->context->msg( 'mobile-frontend-main-menu-contributions' )->escaped(),
				SpecialPage::getTitleFor( 'Contributions', $this->user->getName() )->getLocalURL(),
				MinervaUI::iconClass( 'contributions', 'before' ),
				[ 'data-event-name' => 'contributions' ]
			);
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

		$group->insert( 'watchlist' )
			->addComponent(
				$this->context->msg( 'mobile-frontend-main-menu-watchlist' )->escaped(),
				$watchTitle->getLocalURL( $watchlistQuery ),
				MinervaUI::iconClass( 'watchlist', 'before' ),
				[ 'data-event-name' => 'watchlist' ]
			);
	}

	/**
	 * Creates a login or logout button
	 *
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertLogInOutMenuItem( Group $group ) {
		$query = [];
		$returntoquery = [];
		$request = $this->context->getRequest();

		if ( !$request->wasPosted() ) {
			$returntoquery = $request->getValues();
			unset( $returntoquery['title'] );
			unset( $returntoquery['returnto'] );
			unset( $returntoquery['returntoquery'] );
		}
		$title = $this->context->getTitle();
		// Don't ever redirect back to the login page (bug 55379)
		if ( !$title->isSpecial( 'Userlogin' ) ) {
			$query[ 'returnto' ] = $title->getPrefixedText();
		}

		if ( $this->user->isLoggedIn() ) {
			if ( !empty( $returntoquery ) ) {
				$query[ 'returntoquery' ] = wfArrayToCgi( $returntoquery );
			}
			$url = SpecialPage::getTitleFor( 'Userlogout' )->getLocalURL( $query );
			$username = $this->user->getName();

			$group->insert( 'auth', false )
				->addComponent(
					$username,
					Title::newFromText( $username, NS_USER )->getLocalURL(),
					MinervaUI::iconClass( 'profile', 'before', 'truncated-text primary-action' ),
					[ 'data-event-name' => 'profile' ]
				)
				->addComponent(
					$this->context->msg( 'mobile-frontend-main-menu-logout' )->escaped(),
					$url,
					MinervaUI::iconClass(
						'logout', 'element', 'secondary-action truncated-text' ),
					[ 'data-event-name' => 'logout' ]
				);
		} else {
			// unset campaign on login link so as not to interfere with A/B tests
			unset( $returntoquery['campaign'] );
			$query[ 'returntoquery' ] = wfArrayToCgi( $returntoquery );
			$url = $this->getLoginUrl( $query );
			$group->insert( 'auth', false )
				->addComponent(
					$this->context->msg( 'mobile-frontend-main-menu-login' )->escaped(),
					$url,
					MinervaUI::iconClass( 'login', 'before' ),
					[ 'data-event-name' => 'login' ]
				);
		}
	}

	/**
	 * Build and insert Home link
	 * @param Group $group
	 */
	public function insertHomeItem( Group $group ) {
		// Home link
		$group->insert( 'home' )
			->addComponent(
				$this->context->msg( 'mobile-frontend-home-button' )->escaped(),
				Title::newMainPage()->getLocalURL(),
				MinervaUI::iconClass( 'home', 'before' ),
				[ 'data-event-name' => 'home' ]
			);
	}

	/**
	 * Build and insert Random link
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertRandomItem( Group $group ) {
		// Random link
		$group->insert( 'random' )
			->addComponent( $this->context->msg( 'mobile-frontend-random-button' )->escaped(),
				SpecialPage::getTitleFor( 'Randompage' )->getLocalURL() . '#/random',
				MinervaUI::iconClass( 'random', 'before' ), [
					'id' => 'randomButton',
					'data-event-name' => 'random',
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
					[ 'data-event-name' => 'nearby' ]
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
		$group->insert( 'settings' )
			->addComponent(
				$this->context->msg( 'mobile-frontend-main-menu-settings' )->escaped(),
				SpecialPage::getTitleFor( 'MobileOptions' )->
					getLocalURL( [ 'returnto' => $returnToTitle ] ),
				MinervaUI::iconClass( 'settings', 'before' ),
				[ 'data-event-name' => 'settings' ]
			);
	}

	/**
	 * Build and insert the Preferences link
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertPreferencesItem( Group $group ) {
		$group->insert( 'preferences' )
			->addComponent(
				$this->context->msg( 'preferences' )->escaped(),
				SpecialPage::getTitleFor( 'Preferences' )->getLocalURL(),
				MinervaUI::iconClass( 'settings', 'before' ),
				[ 'data-event-name' => 'preferences' ]
			);
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
	 * Build and insert the SpecialPages link
	 * @param Group $group
	 * @throws MWException
	 */
	public function insertSpecialPages( Group $group ) {
		$group->insert( 'specialpages' )
			->addComponent(
				$this->context->msg( 'specialpages' )->escaped(),
				SpecialPage::getTitleFor( 'Specialpages' )->getLocalURL(),
				MinervaUI::iconClass( 'specialpages', 'before' ),
				[ 'data-event-name' => 'specialpages' ]
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
		if ( $inContentLang->isDisabled() || \Http::isValidURI( $titleName ) ) {
			return;
		}
		$title = Title::newFromText( $titleName );
		if ( !$title->exists() ) {
			return;
		}

		$group->insert( 'communityportal' )
			->addComponent(
				$title->getText(),
				$title->getLocalURL(),
				MinervaUI::iconClass( 'communityportal', 'before' ),
				[ 'data-event-name' => 'community-portal' ]
			);
	}

	/**
	 * Prepares a url to the Special:UserLogin with query parameters
	 *
	 * @param array $query
	 * @return string
	 * @throws MWException
	 */
	private function getLoginUrl( $query ) {
		return SpecialPage::getTitleFor( 'Userlogin' )->getLocalURL( $query );
	}

}
