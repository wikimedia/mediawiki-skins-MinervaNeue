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

use MediaWiki\Minerva\MenuBuilder;
use MediaWiki\MediaWikiServices;
use MediaWiki\Minerva\SkinUserPageHelper;

/**
 * Minerva: Born from the godhead of Jupiter with weapons!
 * A skin that works on both desktop and mobile
 * @ingroup Skins
 */
class SkinMinerva extends SkinTemplate {
	/** Set of keys for available skin options. See $skinOptions. */
	const OPTION_MOBILE_OPTIONS = 'mobileOptionsLink';
	const OPTION_AMC = 'amc';
	const OPTION_CATEGORIES = 'categories';
	const OPTION_BACK_TO_TOP = 'backToTop';
	const OPTION_PAGE_ISSUES = 'pageIssues';
	const OPTION_SHARE_BUTTON = 'shareButton';
	const OPTION_TOGGLING = 'toggling';
	const OPTIONS_MOBILE_BETA = 'beta';
	const OPTIONS_TALK_AT_TOP = 'talkAtTop';
	const OPTIONS_HISTORY_PAGE_ACTIONS = 'historyInPageActions';

	/** @const LEAD_SECTION_NUMBER integer which corresponds to the lead section
	  in editing mode */
	const LEAD_SECTION_NUMBER = 0;

	/** @var string $skinname Name of this skin */
	public $skinname = 'minerva';
	/** @var string $template Name of this used template */
	public $template = 'MinervaTemplate';
	/** @var ContentHandler Content handler of page; only access through getContentHandler */
	protected $contentHandler = null;
	/** @var bool Whether the page is also available in other languages or variants */
	protected $doesPageHaveLanguages = false;
	/** @var SkinUserPageHelper Helper class for UserPage handling */
	protected $userPageHelper;

	/**
	 * Returns the site name for the footer, either as a text or <img> tag
	 * @return string
	 */
	public function getSitename() {
		$config = $this->getConfig();
		$customLogos = $config->get( 'MinervaCustomLogos' );

		$footerSitename = $this->msg( 'mobile-frontend-footer-sitename' )->text();

		// If there's a custom site logo, use that instead of text
		if ( isset( $customLogos['copyright'] ) ) {
			$attributes = [
				'src' => $customLogos['copyright'],
				'alt' => $footerSitename,
			];
			if ( pathinfo( $customLogos['copyright'], PATHINFO_EXTENSION ) === 'svg' ) {
				$attributes['srcset'] = $customLogos['copyright'] . ' 1x';
				if ( isset( $customLogos['copyright-fallback'] ) ) {
					$attributes['src'] = $customLogos['copyright-fallback'];
				} else {
					$attributes['src'] = preg_replace( '/\.svg$/i', '.png', $customLogos['copyright'] );
				}
			}
			if ( isset( $customLogos['copyright-height'] ) ) {
				$attributes['height'] = $customLogos['copyright-height'];
			}
			if ( isset( $customLogos['copyright-width'] ) ) {
				$attributes['width'] = $customLogos['copyright-width'];
			}
			$sitename = Html::element( 'img', $attributes );
		} else {
			$sitename = $footerSitename;
		}

		return $sitename;
	}

	/** @var array skin specific options */
	protected $skinOptions = [
		// Defaults to true for desktop mode.
		self::OPTION_AMC => true,
		self::OPTIONS_MOBILE_BETA => false,
		/**
		 * Whether the main menu should include a link to
		 * Special:Preferences of Special:MobileOptions
		 */
		self::OPTION_MOBILE_OPTIONS => false,
		/** Whether a categories button should appear at the bottom of the skin. */
		self::OPTION_CATEGORIES => false,
		/** Whether a back to top button appears at the bottom of the view page */
		self::OPTION_BACK_TO_TOP => false,
		/** Whether a share button should appear in icons section */
		self::OPTION_SHARE_BUTTON => false,
		/** Whether sections can be collapsed (requires MobileFrontend and MobileFormatter) */
		self::OPTION_TOGGLING => false,
		self::OPTION_PAGE_ISSUES => false,
		self::OPTIONS_TALK_AT_TOP => false,
		self::OPTIONS_HISTORY_PAGE_ACTIONS => false,
	];

	/**
	 * override an existing option or options with new values
	 * @param array $options
	 */
	public function setSkinOptions( $options ) {
		$this->skinOptions = array_merge( $this->skinOptions, $options );
	}

	/**
	 * Return whether a skin option is truthy
	 * @param string $key
	 * @return bool
	 */
	public function getSkinOption( $key ) {
		return $this->skinOptions[$key];
	}

	/**
	 * Return whether any of the skin options have been set
	 * @return bool
	 */
	public function hasSkinOptions() {
		foreach ( $this->skinOptions as $key => $val ) {
			if ( $val ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * initialize various variables and generate the template
	 * @return QuickTemplate
	 */
	protected function prepareQuickTemplate() {
		$out = $this->getOutput();

		// add head items
		$out->addMeta( 'viewport', 'initial-scale=1.0, user-scalable=yes, minimum-scale=0.25, ' .
				'maximum-scale=5.0, width=device-width'
		);
		// T204691
		$theme = $out->getConfig()->get( 'MFManifestThemeColor' );
		if ( $theme ) {
			$out->addMeta( 'theme-color', $theme );
		}

		// Generate skin template
		$tpl = parent::prepareQuickTemplate();

		$this->doesPageHaveLanguages = $tpl->data['content_navigation']['variants'] ||
			$tpl->data['language_urls'];

		// Set whether or not the page content should be wrapped in div.content (for
		// example, on a special page)
		$tpl->set( 'unstyledContent', $out->getProperty( 'unstyledContent' ) );

		// Set the links for the main menu
		$tpl->set( 'menu_data', $this->getMenuData() );

		// Set the links for page secondary actions
		$tpl->set( 'secondary_actions', $this->getSecondaryActions( $tpl ) );

		// Construct various Minerva-specific interface elements
		$this->preparePageContent( $tpl );
		$this->prepareHeaderAndFooter( $tpl );
		$this->prepareMenuButton( $tpl );
		$this->prepareBanners( $tpl );
		$this->preparePageActions( $tpl );
		$this->prepareUserButton( $tpl );
		$this->prepareLanguages( $tpl );

		return $tpl;
	}

	/**
	 * Prepares the header and the content of a page
	 * Stores in QuickTemplate prebodytext, postbodytext keys
	 * @param QuickTemplate $tpl
	 */
	protected function preparePageContent( QuickTemplate $tpl ) {
		$title = $this->getTitle();

		// If it's a talk page, add a link to the main namespace page
		// In AMC we do not need to do this as there is an easy way back to the article page
		// via the talk/article tabs.
		if ( $title->isTalkPage() && !$this->getSkinOption( self::OPTION_AMC ) ) {
			// if it's a talk page for which we have a special message, use it
			switch ( $title->getNamespace() ) {
				case NS_USER_TALK:
					$msg = 'mobile-frontend-talk-back-to-userpage';
					break;
				case NS_PROJECT_TALK:
					$msg = 'mobile-frontend-talk-back-to-projectpage';
					break;
				case NS_FILE_TALK:
					$msg = 'mobile-frontend-talk-back-to-filepage';
					break;
				default: // generic (all other NS)
					$msg = 'mobile-frontend-talk-back-to-page';
			}
			$tpl->set( 'subject-page', MediaWikiServices::getInstance()->getLinkRenderer()->makeLink(
				$title->getSubjectPage(),
				$this->msg( $msg, $title->getText() )->text(),
				[ 'class' => 'return-link' ]
			) );
		}
	}

	/**
	 * Gets whether or not the page action is allowed.
	 *
	 * Page actions isn't allowed when:
	 * <ul>
	 *   <li>
	 *     the action is disabled (by removing it from the <code>MinervaPageActions</code>
	 *     configuration variable; or
	 *   </li>
	 *   <li>the user is on the main page</li>
	 * </ul>
	 *
	 * The "edit" page action is not allowed if editing is not possible on the page
	 * see @method: isCurrentPageContentModelEditable
	 *
	 * The "switch-language" is allowed if there are interlanguage links on the page,
	 * or <code>$wgMinervaAlwaysShowLanguageButton</code>
	 * is truthy.
	 *
	 * @param string $action
	 * @return bool
	 */
	protected function isAllowedPageAction( $action ) {
		$title = $this->getTitle();
		$config = $this->getConfig();
		// Title may be undefined in certain contexts (T179833)
		if ( !$title ) {
			return false;
		}

		// T206406: Enable "Talk" or "Discussion" button on Main page, also, not forgetting
		// the "switch-language" button. But disable "edit" and "watch" actions.
		if ( $title->isMainPage() ) {
			return ( in_array( $action, $config->get( 'MinervaPageActions' ) )
				&& ( $action === 'talk' || $action === 'switch-language' ) );
		}

		if ( $action === 'history' && $title->exists() ) {
			return $this->getSkinOption( self::OPTIONS_HISTORY_PAGE_ACTIONS );
		}

		if (
			!in_array( $action, $config->get( 'MinervaPageActions' ) )
			|| ( $this->getUserPageHelper()->isUserPage() && !$title->exists() )
		) {
			return false;
		}

		if ( $action === 'edit' ) {
			return $this->isCurrentPageContentModelEditable();
		}

		if ( $action === 'switch-language' ) {
			return $this->doesPageHaveLanguages || $config->get( 'MinervaAlwaysShowLanguageButton' );
		}

		return true;
	}

	/**
	 * Overrides Skin::doEditSectionLink
	 * @param Title $nt
	 * @param string $section
	 * @param string|null $tooltip
	 * @param Language $lang
	 * @return string
	 */
	public function doEditSectionLink( Title $nt, $section, $tooltip, Language $lang ) {
		if ( $this->isAllowedPageAction( 'edit' ) && !$nt->isMainPage() ) {
			$message = $this->msg( 'mobile-frontend-editor-edit' )->inLanguage( $lang )->text();
			$html = Html::openElement( 'span', [ 'class' => 'mw-editsection' ] );
			$html .= Html::element( 'a', [
				'href' => $this->getTitle()->getLocalURL( [ 'action' => 'edit', 'section' => $section ] ),
				'title' => $this->msg( 'editsectionhint', $tooltip )->inLanguage( $lang )->text(),
				'data-section' => $section,
				// Note visibility of the edit section link button is controlled by .edit-page in ui.less so
				// we default to enabled even though this may not be true.
				'class' => MinervaUI::iconClass( 'edit-enabled', 'element', 'edit-page' ),
			], $message );
			$html .= Html::closeElement( 'span' );
			return $html;
		}
		return '';
	}

	/**
	 * Gets content handler of current title
	 *
	 * @return ContentHandler
	 */
	protected function getContentHandler() {
		if ( $this->contentHandler === null ) {
			$this->contentHandler = ContentHandler::getForTitle( $this->getTitle() );
		}

		return $this->contentHandler;
	}

	/**
	 * Takes a title and returns classes to apply to the body tag
	 * @param Title $title
	 * @return string
	 */
	public function getPageClasses( $title ) {
		$className = parent::getPageClasses( $title );
		$className .= ' ' . ( $this->getSkinOption( self::OPTIONS_MOBILE_BETA ) ? 'beta' : 'stable' );

		if ( $title->isMainPage() ) {
			$className .= ' page-Main_Page ';
		}

		if ( $this->isAuthenticatedUser() ) {
			$className .= ' is-authenticated';
		}
		// The new treatment should only apply to the main namespace
		if (
			$title->getNamespace() === NS_MAIN &&
			$this->getSkinOption( self::OPTION_PAGE_ISSUES )
		) {
			$className .= ' issues-group-B';
		}
		return $className;
	}

	/**
	 * Check whether the current user is authenticated or not.
	 * @todo This helper function is only truly needed whilst SkinMobileApp does not support login
	 * @return bool
	 */
	protected function isAuthenticatedUser() {
		return $this->getUser()->isLoggedIn();
	}

	/**
	 * Whether the output page contains category links and the category feature is enabled.
	 * @return bool
	 */
	private function hasCategoryLinks() {
		if ( !$this->getSkinOption( self::OPTION_CATEGORIES ) ) {
			return false;
		}
		$categoryLinks = $this->getOutput()->getCategoryLinks();

		if ( !count( $categoryLinks ) ) {
			return false;
		}
		return !empty( $categoryLinks['normal'] ) || !empty( $categoryLinks['hidden'] );
	}

	/**
	 * @return SkinUserPageHelper
	 */
	public function getUserPageHelper() {
		if ( $this->userPageHelper === null ) {
			$this->userPageHelper = new SkinUserPageHelper( $this->getContext()->getTitle() );
		}
		return $this->userPageHelper;
	}

	/**
	 * Initializes output page and sets up skin-specific parameters
	 * @param OutputPage $out object to initialize
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );
		$out->addJsConfigVars( $this->getSkinConfigVariables() );
	}

	/**
	 * Returns, if Extension:Echo should be used.
	 * @return bool
	 */
	protected function useEcho() {
		return ExtensionRegistry::getInstance()->isLoaded( 'Echo' );
	}

	/**
	 * Get Echo notification target user
	 * @param User $user
	 * @return MWEchoNotifUser
	 */
	protected function getEchoNotifUser( User $user ) {
		return MWEchoNotifUser::newFromUser( $user );
	}

	/**
	 * Get the last time user has seen particular type of notifications.
	 * @param User $user
	 * @param string $type Type of seen time to get
	 * @return string|bool Timestamp in TS_ISO_8601 format, or false if no stored time
	 */
	protected function getEchoSeenTime( User $user, $type = 'all' ) {
		return EchoSeenTime::newFromUser( $user )->getTime( $type, TS_ISO_8601 );
	}

	/**
	 * Get formatted Echo notification count
	 * @param int $count
	 * @return string
	 */
	protected function getFormattedEchoNotificationCount( $count ) {
		return EchoNotificationController::formatNotificationCount( $count );
	}

	/**
	 * Prepares the user button.
	 * @param QuickTemplate $tpl
	 */
	protected function prepareUserButton( QuickTemplate $tpl ) {
		// Set user button to empty string by default
		$tpl->set( 'secondaryButtonData', '' );
		$notificationsTitle = '';
		$count = 0;
		$countLabel = '';
		$isZero = true;
		$hasUnseen = false;

		$user = $this->getUser();
		$newtalks = $this->getNewtalks();
		$currentTitle = $this->getTitle();

		// If Echo is available, the user is logged in, and they are not already on the
		// notifications archive, show the notifications icon in the header.
		if ( $this->useEcho() && $user->isLoggedIn() ) {
			$notificationsTitle = SpecialPage::getTitleFor( 'Notifications' );
			if ( $currentTitle->equals( $notificationsTitle ) ) {
				// Don't show the secondary button at all
				$notificationsTitle = null;
			} else {
				$notificationsMsg = $this->msg( 'mobile-frontend-user-button-tooltip' )->text();

				$notifUser = $this->getEchoNotifUser( $user );
				$count = $notifUser->getNotificationCount();

				$seenAlertTime = $this->getEchoSeenTime( $user, 'alert' );
				$seenMsgTime = $this->getEchoSeenTime( $user, 'message' );

				$alertNotificationTimestamp = $notifUser->getLastUnreadAlertTime();
				$msgNotificationTimestamp = $notifUser->getLastUnreadMessageTime();

				$isZero = $count === 0;
				$hasUnseen = $count > 0 &&
					(
						$seenMsgTime !== false && $msgNotificationTimestamp !== false &&
						$seenMsgTime < $msgNotificationTimestamp->getTimestamp( TS_ISO_8601 )
					) ||
					(
						$seenAlertTime !== false && $alertNotificationTimestamp !== false &&
						$seenAlertTime < $alertNotificationTimestamp->getTimestamp( TS_ISO_8601 )
					);

				$countLabel = $this->getFormattedEchoNotificationCount( $count );
			}
		} elseif ( !empty( $newtalks ) ) {
			$notificationsTitle = SpecialPage::getTitleFor( 'Mytalk' );
			$notificationsMsg = $this->msg( 'mobile-frontend-user-newmessages' )->text();
		}

		if ( $notificationsTitle ) {
			$url = $notificationsTitle->getLocalURL(
				[ 'returnto' => $currentTitle->getPrefixedText() ] );

			$tpl->set( 'secondaryButtonData', [
				'notificationIconClass' => MinervaUI::iconClass( 'notifications' ),
				'title' => $notificationsMsg,
				'url' => $url,
				'notificationCountRaw' => $count,
				'notificationCountString' => $countLabel,
				'isNotificationCountZero' => $isZero,
				'hasNotifications' => $hasUnseen,
				'hasUnseenNotifications' => $hasUnseen
			] );
		}
	}

	/**
	 * Inserts the Contributions menu item into the menu.
	 *
	 * @param MenuBuilder $menu
	 * @param User $user The user to whom the contributions belong
	 */
	private function insertContributionsMenuItem( MenuBuilder $menu, User $user ) {
		$menu->insert( 'contribs' )
			->addComponent(
				$this->msg( 'mobile-frontend-main-menu-contributions' )->escaped(),
				SpecialPage::getTitleFor( 'Contributions', $user->getName() )->getLocalUrl(),
				MinervaUI::iconClass( 'contributions', 'before' ),
				[ 'data-event-name' => 'contributions' ]
			);
	}

	/**
	 * Inserts the Watchlist menu item into the menu for a logged in user
	 *
	 * @param MenuBuilder $menu
	 * @param User $user that must be logged in
	 */
	protected function insertWatchlistMenuItem( MenuBuilder $menu, User $user ) {
		$watchTitle = SpecialPage::getTitleFor( 'Watchlist' );

		// Watchlist link
		$watchlistQuery = [];
		// Avoid fatal when MobileFrontend not available (T171241)
		if ( class_exists( 'SpecialMobileWatchlist' ) ) {
			$view = $user->getOption( SpecialMobileWatchlist::VIEW_OPTION_NAME, false );
			$filter = $user->getOption( SpecialMobileWatchlist::FILTER_OPTION_NAME, false );
			if ( $view ) {
				$watchlistQuery['watchlistview'] = $view;
			}
			if ( $filter && $view === 'feed' ) {
				$watchlistQuery['filter'] = $filter;
			}
		}

		$menu->insert( 'watchlist' )
			->addComponent(
				$this->msg( 'mobile-frontend-main-menu-watchlist' )->escaped(),
				$watchTitle->getLocalURL( $watchlistQuery ),
				MinervaUI::iconClass( 'watchlist', 'before' ),
				[ 'data-event-name' => 'watchlist' ]
			);
	}

	/**
	 * If the user is using a mobile device (or the UA presents itself as a mobile device), then the
	 * Settings menu item is inserted into the menu; otherwise the Preferences menu item is inserted.
	 *
	 * @param MenuBuilder $menu
	 */
	protected function insertSettingsMenuItem( MenuBuilder $menu ) {
		$returnToTitle = $this->getTitle()->getPrefixedText();

		// Links specifically for mobile mode
		if ( $this->getSkinOption( self::OPTION_MOBILE_OPTIONS ) ) {
			// Settings link
			$menu->insert( 'settings' )
				->addComponent(
					$this->msg( 'mobile-frontend-main-menu-settings' )->escaped(),
					SpecialPage::getTitleFor( 'MobileOptions' )->
						getLocalURL( [ 'returnto' => $returnToTitle ] ),
					MinervaUI::iconClass( 'settings', 'before' ),
					[ 'data-event-name' => 'settings' ]
				);

		// Links specifically for desktop mode
		} else {

			// Preferences link
			$menu->insert( 'preferences' )
				->addComponent(
					$this->msg( 'preferences' )->escaped(),
					SpecialPage::getTitleFor( 'Preferences' )->getLocalURL(),
					MinervaUI::iconClass( 'settings', 'before' ),
					[ 'data-event-name' => 'preferences' ]
				);
		}
	}

	/**
	 * Builds the personal tools menu item group.
	 *
	 * ... by adding the Watchlist, Settings, and Log{in,out} menu items in the given order.
	 *
	 * @param MenuBuilder $menu
	 */
	protected function buildPersonalTools( MenuBuilder $menu ) {
		$this->insertLogInOutMenuItem( $menu );

		$user = $this->getUser();

		if ( $user->isLoggedIn() ) {
			$this->insertWatchlistMenuItem( $menu, $user );
			$this->insertContributionsMenuItem( $menu, $user );
		}
	}

	/**
	 * Prepares and returns urls and links personal to the given user
	 * @return array
	 */
	protected function getPersonalTools() {
		$menu = new MenuBuilder();

		$this->buildPersonalTools( $menu );

		// Allow other extensions to add or override tools
		Hooks::run( 'MobileMenu', [ 'personal', &$menu ] );
		return $menu->getEntries();
	}

	/**
	 * Rewrites the language list so that it cannot be contaminated by other extensions with things
	 * other than languages
	 * See bug 57094.
	 *
	 * @todo Remove when Special:Languages link goes stable
	 * @param QuickTemplate $tpl
	 */
	protected function prepareLanguages( $tpl ) {
		$lang = $this->getTitle()->getPageViewLanguage();
		$tpl->set( 'pageLang', $lang->getHtmlCode() );
		$tpl->set( 'pageDir', $lang->getDir() );
		// If the array is empty, then instead give the skin boolean false
		$language_urls = $this->getLanguages() ?: false;
		$tpl->set( 'language_urls', $language_urls );
	}

	/**
	 * Like <code>SkinMinerva#getDiscoveryTools</code> and <code>#getPersonalTools</code>, create
	 * a group of configuration-related menu items. Currently, only the Settings menu item is in the
	 * group.
	 *
	 * @return array
	 */
	private function getConfigurationTools() {
		$menu = new MenuBuilder();

		$this->insertSettingsMenuItem( $menu );

		return $menu->getEntries();
	}

	/**
	 * Prepares a list of links that have the purpose of discovery in the main navigation menu
	 * @return array
	 */
	protected function getDiscoveryTools() {
		$config = $this->getConfig();
		$menu = new MenuBuilder();
		$factory = MediaWikiServices::getInstance()->getSpecialPageFactory();

		// Home link
		$menu->insert( 'home' )
			->addComponent(
				$this->msg( 'mobile-frontend-home-button' )->escaped(),
				Title::newMainPage()->getLocalURL(),
				MinervaUI::iconClass( 'home', 'before' ),
				[ 'data-event-name' => 'home' ]
			);

		// Random link
		$menu->insert( 'random' )
			->addComponent(
				$this->msg( 'mobile-frontend-random-button' )->escaped(),
				SpecialPage::getTitleFor( 'Randompage' )->getLocalURL() . '#/random',
				MinervaUI::iconClass( 'random', 'before' ),
				[
					'id' => 'randomButton',
					'data-event-name' => 'random',
				]
			);

		// Nearby link (if supported)
		if ( $factory->exists( 'Nearby' ) ) {
			$menu->insert( 'nearby', $isJSOnly = true )
				->addComponent(
					$this->msg( 'mobile-frontend-main-menu-nearby' )->escaped(),
					SpecialPage::getTitleFor( 'Nearby' )->getLocalURL(),
					MinervaUI::iconClass( 'nearby', 'before', 'nearby' ),
					[ 'data-event-name' => 'nearby' ]
				);
		}

		// Allow other extensions to add or override tools
		Hooks::run( 'MobileMenu', [ 'discovery', &$menu ] );
		return $menu->getEntries();
	}

	/**
	 * Prepares a url to the Special:UserLogin with query parameters
	 * @param array $query
	 * @return string
	 */
	public function getLoginUrl( $query ) {
		return SpecialPage::getTitleFor( 'Userlogin' )->getLocalURL( $query );
	}

	/**
	 * Creates a login or logout button
	 *
	 * @param MenuBuilder $menu
	 */
	protected function insertLogInOutMenuItem( MenuBuilder $menu ) {
		$query = [];
		$returntoquery = [];

		if ( !$this->getRequest()->wasPosted() ) {
			$returntoquery = $this->getRequest()->getValues();
			unset( $returntoquery['title'] );
			unset( $returntoquery['returnto'] );
			unset( $returntoquery['returntoquery'] );
		}
		$title = $this->getTitle();
		// Don't ever redirect back to the login page (bug 55379)
		if ( !$title->isSpecial( 'Userlogin' ) ) {
			$query[ 'returnto' ] = $title->getPrefixedText();
		}

		$user = $this->getUser();
		if ( $user->isLoggedIn() ) {
			if ( !empty( $returntoquery ) ) {
				$query[ 'returntoquery' ] = wfArrayToCgi( $returntoquery );
			}
			$url = SpecialPage::getTitleFor( 'Userlogout' )->getLocalURL( $query );
			$username = $user->getName();

			$menu->insert( 'auth', false )
				->addComponent(
					$username,
					Title::newFromText( $username, NS_USER )->getLocalURL(),
					MinervaUI::iconClass( 'profile', 'before', 'truncated-text primary-action' ),
					[ 'data-event-name' => 'profile' ]
				)
				->addComponent(
					$this->msg( 'mobile-frontend-main-menu-logout' )->escaped(),
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
			$menu->insert( 'auth', false )
				->addComponent(
					$this->msg( 'mobile-frontend-main-menu-login' )->escaped(),
					$url,
					MinervaUI::iconClass( 'login', 'before' ),
					[ 'data-event-name' => 'login' ]
				);
		}
	}

	/**
	 * Get a history link which describes author and relative time of last edit
	 * @param Title $title The Title object of the page being viewed
	 * @param int $timestamp
	 * @return array
	 */
	protected function getRelativeHistoryLink( Title $title, $timestamp ) {
		$user = $this->getUser();
		$text = $this->msg(
			'minerva-last-modified-date',
			$this->getLanguage()->userDate( $timestamp, $user ),
			$this->getLanguage()->userTime( $timestamp, $user )
		)->parse();
		return [
			// Use $edit['timestamp'] (Unix format) instead of $timestamp (MW format)
			'data-timestamp' => wfTimestamp( TS_UNIX, $timestamp ),
			'href' => $this->getHistoryUrl( $title ),
			'text' => $text,
		] + $this->getRevisionEditorData( $title );
	}

	/**
	 * Get a history link which makes no reference to user or last edited time
	 * @param Title $title The Title object of the page being viewed
	 * @return array
	 */
	protected function getGenericHistoryLink( Title $title ) {
		$text = $this->msg( 'mobile-frontend-history' )->plain();
		return [
			'href' => $this->getHistoryUrl( $title ),
			'text' => $text,
		];
	}

	/**
	 * Get the URL for the history page for the given title using Special:History
	 * when available.
	 * @param Title $title The Title object of the page being viewed
	 * @return string
	 */
	protected function getHistoryUrl( Title $title ) {
		return ExtensionRegistry::getInstance()->isLoaded( 'MobileFrontend' ) &&
			SpecialMobileHistory::shouldUseSpecialHistory( $title, $this->getUser() ) ?
			SpecialPage::getTitleFor( 'History', $title )->getLocalURL() :
			$title->getLocalURL( [ 'action' => 'history' ] );
	}

	/**
	 * Prepare the content for the 'last edited' message, e.g. 'Last edited on 30 August
	 * 2013, at 23:31'. This message is different for the main page since main page
	 * content is typically transcluded rather than edited directly.
	 * @param Title $title The Title object of the page being viewed
	 * @return array
	 */
	protected function getHistoryLink( Title $title ) {
		$isLatestRevision = $this->getRevisionId() === $title->getLatestRevID();
		// Get rev_timestamp of current revision (preloaded by MediaWiki core)
		$timestamp = $this->getOutput()->getRevisionTimestamp();
		# No cached timestamp, load it from the database
		if ( $timestamp === null ) {
			$timestamp = Revision::getTimestampFromId( $this->getTitle(), $this->getRevisionId() );
		}

		return !$isLatestRevision || $title->isMainPage() ?
			$this->getGenericHistoryLink( $title ) :
			$this->getRelativeHistoryLink( $title, $timestamp );
	}

	/**
	 * Returns data attributes representing the editor for the current revision.
	 * @param Title $title The Title object of the page being viewed
	 * @return array representing user with name and gender fields. Empty if the editor no longer
	 *   exists in the database or is hidden from public view.
	 */
	private function getRevisionEditorData( Title $title ) {
		$rev = Revision::newFromTitle( $title );
		$result = [];
		if ( $rev ) {
			$revUserId = $rev->getUser();
			// Note the user will only be returned if that information is public
			if ( $revUserId ) {
				$revUser = User::newFromId( $revUserId );
				$editorName = $revUser->getName();
				$editorGender = $revUser->getOption( 'gender' );
				$result += [
					'data-user-name' => $editorName,
					'data-user-gender' => $editorGender,
				];
			}
		}
		return $result;
	}

	/**
	 * Returns the HTML representing the tagline
	 * @return string HTML for tagline
	 */
	protected function getTaglineHtml() {
		$tagline = '';

		if ( $this->getUserPageHelper()->isUserPage() ) {
			$pageUser = $this->getUserPageHelper()->getPageUser();
			$fromDate = $pageUser->getRegistration();
			if ( is_string( $fromDate ) ) {
				$fromDateTs = wfTimestamp( TS_UNIX, $fromDate );

				// This is shown when js is disabled. js enhancement made due to caching
				$tagline = $this->msg( 'mobile-frontend-user-page-member-since',
						$this->getLanguage()->userDate( new MWTimestamp( $fromDateTs ), $this->getUser() ),
						$pageUser )->text();

				// Define html attributes for usage with js enhancement (unix timestamp, gender)
				$attrs = [ 'id' => 'tagline-userpage',
					'data-userpage-registration-date' => $fromDateTs,
					'data-userpage-gender' => $pageUser->getOption( 'gender' ) ];
			}
		} else {
			$title = $this->getTitle();
			if ( $title ) {
				$out = $this->getOutput();
				$tagline = $out->getProperty( 'wgMFDescription' );
			}
		}

		$attrs[ 'class' ] = 'tagline';
		return Html::element( 'div', $attrs, $tagline );
	}
	/**
	 * Returns the HTML representing the heading.
	 * @return string HTML for header
	 */
	protected function getHeadingHtml() {
		if ( $this->getUserPageHelper()->isUserPage() ) {
			// The heading is just the username without namespace
			$heading = $this->getUserPageHelper()->getPageUser()->getName();
		} else {
			$heading = $this->getOutput()->getPageTitle();
		}
		return Html::rawElement( 'h1', [ 'id' => 'section_0' ], $heading );
	}
	/**
	 * Create and prepare header and footer content
	 * @param BaseTemplate $tpl
	 */
	protected function prepareHeaderAndFooter( BaseTemplate $tpl ) {
		$title = $this->getTitle();
		$user = $this->getUser();
		$out = $this->getOutput();
		$tpl->set( 'taglinehtml', $this->getTaglineHtml() );
		if ( $this->getUserPageHelper()->isUserPage() ) {
			$pageUser = $this->getUserPageHelper()->getPageUser();
			$talkPage = $pageUser->getTalkPage();
			$data = [
				// Talk page icon is provided by mobile.userpage.icons for time being
				'userPageIconClass' => MinervaUI::iconClass( 'talk', 'before', 'talk', 'mf' ),
				'talkPageTitle' => $talkPage->getPrefixedURL(),
				'talkPageLink' => $talkPage->getLocalURL(),
				'talkPageLinkTitle' => $this->msg(
					'mobile-frontend-user-page-talk' )->escaped(),
				'contributionsPageLink' => SpecialPage::getTitleFor(
					'Contributions', $pageUser )->getLocalURL(),
				'contributionsPageTitle' => $this->msg(
					'mobile-frontend-user-page-contributions' )->escaped(),
				'uploadsPageLink' => SpecialPage::getTitleFor(
					'Uploads', $pageUser )->getLocalURL(),
				'uploadsPageTitle' => $this->msg(
					'mobile-frontend-user-page-uploads' )->escaped(),
			];
			$templateParser = new TemplateParser( __DIR__ );
			$tpl->set( 'postheadinghtml',
				$templateParser->processTemplate( 'user_page_links', $data )
			);
		} elseif ( $title->isMainPage() ) {
			if ( $user->isLoggedIn() ) {
				$pageTitle = $this->msg(
					'mobile-frontend-logged-in-homepage-notification', $user->getName() )->text();
			} else {
				$pageTitle = '';
			}
			$out->setPageTitle( $pageTitle );
		}

		if ( $this->canUseWikiPage() ) {
			// If it's a page that exists, add last edited timestamp
			// The relative time is only rendered on the latest revision.
			// For older revisions the last modified
			// information will not render with a relative time
			// nor will it show the name of the editor.
			if ( $this->getWikiPage()->exists() ) {
				$tpl->set( 'historyLink', $this->getHistoryLink( $title ) );
			}
		}
		$tpl->set( 'headinghtml', $this->getHeadingHtml() );

		$tpl->set( 'footer-site-heading-html', $this->getSitename() );
		// set defaults
		if ( !isset( $tpl->data['postbodytext'] ) ) {
			$tpl->set( 'postbodytext', '' ); // not currently set in desktop skin
		}
	}

	/**
	 * Prepare the button opens the main side menu
	 * @param BaseTemplate $tpl
	 */
	protected function prepareMenuButton( BaseTemplate $tpl ) {
		// menu button
		$url = SpecialPageFactory::exists( 'MobileMenu' ) ?
			SpecialPage::getTitleFor( 'MobileMenu' )->getLocalURL() : '#';

		$tpl->set( 'menuButton',
			Html::element( 'a', [
				'title' => $this->msg( 'mobile-frontend-main-menu-button-tooltip' )->text(),
				'href' => $url,
				'class' => MinervaUI::iconClass( 'mainmenu', 'element', 'main-menu-button' ),
				'id' => 'mw-mf-main-menu-button',
			], $this->msg( 'mobile-frontend-main-menu-button-tooltip' )->text() )
		);
	}

	/**
	 * Load internal banner content to show in pre content in template
	 * Beware of HTML caching when using this function.
	 * Content set as "internalbanner"
	 * @param BaseTemplate $tpl
	 */
	protected function prepareBanners( BaseTemplate $tpl ) {
		// Make sure Zero banner are always on top
		$banners = [ '<div id="siteNotice"></div>' ];
		if ( $this->getConfig()->get( 'MinervaEnableSiteNotice' ) ) {
			$siteNotice = $this->getSiteNotice();
			if ( $siteNotice ) {
				$banners[] = $siteNotice;
			}
		}
		$tpl->set( 'banners', $banners );
		// These banners unlike 'banners' show inside the main content chrome underneath the
		// page actions.
		$tpl->set( 'internalBanner', '' );
	}

	/**
	 * Returns an array of sitelinks to add into the main menu footer.
	 * @return array Array of site links
	 */
	protected function getSiteLinks() {
		$menu = new MenuBuilder();

		// About link
		$title = Title::newFromText( $this->msg( 'aboutpage' )->inContentLanguage()->text() );
		$msg = $this->msg( 'aboutsite' );
		if ( $title && !$msg->isDisabled() ) {
			$menu->insert( 'about' )
				->addComponent( $msg->text(), $title->getLocalURL() );
		}

		// Disclaimers link
		$title = Title::newFromText( $this->msg( 'disclaimerpage' )->inContentLanguage()->text() );
		$msg = $this->msg( 'disclaimers' );
		if ( $title && !$msg->isDisabled() ) {
			$menu->insert( 'disclaimers' )
				->addComponent( $msg->text(), $title->getLocalURL() );
		}

		// Allow other extensions to add or override tools
		Hooks::run( 'MobileMenu', [ 'sitelinks', &$menu ] );
		return $menu->getEntries();
	}

	/**
	 * Returns an array with details for a language button.
	 * @return array
	 */
	protected function getLanguageButton() {
		$languageUrl = SpecialPage::getTitleFor(
			'MobileLanguages',
			$this->getSkin()->getTitle()
		)->getLocalURL();

		return [
			'attributes' => [
				'class' => 'language-selector',
				'href' => $languageUrl,
			],
			'label' => $this->msg( 'mobile-frontend-language-article-heading' )->text()
		];
	}

	/**
	 * Returns an array with details for a talk button.
	 * @param Title $talkTitle Title object of the talk page
	 * @param array $talkButton Array with data of desktop talk button
	 * @param bool $addSection (optional) when added the talk button will render
	 *  as an add topic button. Defaults to false.
	 * @return array
	 */
	protected function getTalkButton( $talkTitle, $talkButton, $addSection = false ) {
		if ( $addSection ) {
			$params = [ 'action' => 'edit', 'section' => 'new' ];
			$className = 'talk continue add';
		} else {
			$params = [];
			$className = 'talk';
		}

		return [
			'attributes' => [
				'href' => $talkTitle->getLinkURL( $params ),
				'data-title' => $talkTitle->getFullText(),
				'class' => $className,
			],
			'label' => $talkButton['text'],
		];
	}

	/**
	 * Returns an array with details for a categories button.
	 * @return array
	 */
	protected function getCategoryButton() {
		return [
			'attributes' => [
				'href' => '#/categories',
				// add hidden class (the overlay works only, when JS is enabled (class will
				// be removed in categories/init.js)
				'class' => 'category-button hidden',
			],
			'label' => $this->msg( 'categories' )->text()
		];
	}

	/**
	 * Returns an array of links for page secondary actions
	 * @param BaseTemplate $tpl
	 * @return string[]
	 */
	protected function getSecondaryActions( BaseTemplate $tpl ) {
		$buttons = [];

		// always add a button to link to the talk page
		// in beta it will be the entry point for the talk overlay feature,
		// in stable it will link to the wikitext talk page
		$title = $this->getTitle();
		$subjectPage = $title->getSubjectPage();
		$talkAtBottom = !$this->getSkinOption( self::OPTIONS_TALK_AT_TOP ) ||
			$subjectPage->isMainPage();
		$namespaces = $tpl->data['content_navigation']['namespaces'];
		if ( !$this->getUserPageHelper()->isUserPage()
			&& $this->isTalkAllowed() && $talkAtBottom
		) {
			// FIXME [core]: This seems unnecessary..
			$subjectId = $title->getNamespaceKey( '' );
			$talkId = $subjectId === 'main' ? 'talk' : "{$subjectId}_talk";

			if ( isset( $namespaces[$talkId] ) ) {
				$talkButton = $namespaces[$talkId];
				$talkTitle = $title->getTalkPage();
				if ( $title->isTalkPage() ) {
					$talkButton['text'] = wfMessage( 'minerva-talk-add-topic' );
					$buttons['talk'] = $this->getTalkButton( $title, $talkButton, true );
				} else {
					$buttons['talk'] = $this->getTalkButton( $talkTitle, $talkButton );
				}
			}
		}

		if ( $this->doesPageHaveLanguages && $title->isMainPage() ) {
			$buttons['language'] = $this->getLanguageButton();
		}

		if ( $this->hasCategoryLinks() ) {
			$buttons['categories'] = $this->getCategoryButton();
		}

		return $buttons;
	}

	/**
	 * Prepare configured and available page actions
	 *
	 * If a page action should display a placeholder element
	 * (i.e. it will be hydrated on the client with JS) add the 'jsonly' CSS class to
	 * the 'class' key of its array.
	 *
	 * @param BaseTemplate $tpl
	 */
	protected function preparePageActions( BaseTemplate $tpl ) {
		$menu = [];

		if ( $this->isAllowedPageAction( 'switch-language' ) ) {
			$menu[] = $this->createSwitchLanguageAction();
		}

		if ( $this->isAllowedPageAction( 'watch' ) ) {
			// SkinTemplate#buildContentNavigationUrls creates distinct "watch" and "unwatch" actions.
			// Pass these actions in as context for #createWatchPageAction.
			$actions = $tpl->data['content_navigation']['actions'];

			$menu[] = $this->createWatchPageAction( $actions );
		}

		if ( $this->isAllowedPageAction( 'history' ) ) {
			$menu[] = $this->getHistoryPageAction();
		}

		if ( $this->isAllowedPageAction( 'edit' ) ) {
			$menu[] = $this->createEditPageAction();
		}

		$tpl->set( 'page_actions', $menu );
	}

	/**
	 * Creates the "edit" page action: the well-known pencil icon that, when tapped, will open an
	 * editor with the lead section loaded.
	 *
	 * @return array A map of HTML attributes and a "text" property to be used with the
	 * pageActionMenu.mustache template.
	 */
	protected function createEditPageAction() {
		$title = $this->getTitle();
		$user = $this->getUser();
		$editArgs = [ 'action' => 'edit' ];
		if ( $title->isWikitextPage() ) {
			// If the content model is wikitext we'll default to editing the lead section.
			// Full wikitext editing is hard on mobile devices.
			$editArgs['section'] = self::LEAD_SECTION_NUMBER;
		}
		$userQuickEditCheck = $title->quickUserCan( 'edit', $user )
			&& ( $title->exists() || $title->quickUserCan( 'create', $user ) );
		$userBlockInfo = $user->getId() == 0 ? false : $user->isBlockedFrom( $title, true );
		$userCanEdit = $userQuickEditCheck && !$userBlockInfo;

		return [
				'item-id' => 'page-actions-edit',
				'id' => 'ca-edit',
				'href' => $title->getLocalURL( $editArgs ),
				'class' => 'edit-page '
					. MinervaUI::iconClass( $userCanEdit ? 'edit-enabled' : 'edit', 'element' ),
				'title' => $this->msg( 'mobile-frontend-pageaction-edit-tooltip' ),
				'text' => $this->msg( 'mobile-frontend-editor-edit' )
		];
	}

	/**
	 * Creates the "watch" or "unwatch" action: the well-known star icon that, when tapped, will
	 * add the page to or remove the page from the user's watchlist; or, if the user is logged out,
	 * will direct the user's UA to Special:Login.
	 *
	 * @param array $actions
	 * @return array A map of HTML attributes and a "text" property to be used with the
	 * pageActionMenu.mustache template.
	 */
	protected function createWatchPageAction( $actions ) {
		$title = $this->getTitle();
		$user = $this->getUser();
		$ctaUrl = $this->getLoginUrl( [ 'returnto' => $title ] );
		if ( $title && $user->isWatched( $title ) ) {
			$icon = 'watched';
		} else {
			$icon = 'watch';
		}
		$baseResult = [
			'item-id' => 'page-actions-watch',
			'id' => 'ca-watch',
			// Use blank icon to reserve space for watchstar icon once JS loads
			'class' => MinervaUI::iconClass( $icon, 'element', 'watch-this-article' ) . ' jsonly',
			'title' => $this->msg( 'watchthispage' ),
			'text' => $this->msg( 'watchthispage' )
		];

		if ( isset( $actions['watch'] ) ) {
			$result = array_merge( $actions['watch'], $baseResult );
		} elseif ( isset( $actions['unwatch'] ) ) {
			$result = array_merge( $actions['unwatch'], $baseResult );
			$result['class'] .= ' watched';
			$result[ 'text' ] = $this->msg( 'unwatchthispage' );
		} else {
			// placeholder for not logged in
			$result = array_merge( $baseResult, [
				'href' => $ctaUrl,
			] );
		}

		return $result;
	}

	/**
	 * Creates the "switch-language" action: the icon that, when tapped, opens the language
	 * switcher.
	 *
	 * @return array A map of HTML attributes and a 'text' property to be used with the
	 * pageActionMenu.mustache template.
	 */
	protected function createSwitchLanguageAction() {
		$languageSwitcherLink = false;
		$languageSwitcherClasses = ' language-selector';

		if ( $this->doesPageHaveLanguages ) {
			$languageSwitcherLink = SpecialPage::getTitleFor(
				'MobileLanguages',
				$this->getTitle()
			)->getLocalURL();
		} else {
			$languageSwitcherClasses .= ' disabled';
		}
		return [
				'item-id' => 'page-actions-languages',
				'class' => MinervaUI::iconClass( 'language-switcher', 'element', $languageSwitcherClasses ),
				'href' => $languageSwitcherLink,
				'title' => $this->msg( 'mobile-frontend-language-article-heading' ),
				'text' => $this->msg( 'mobile-frontend-language-article-heading' )
		];
	}

	/**
	 * Creates a history action: An icon that links to the mobile history page.
	 *
	 * @return array A map of HTML attributes and a 'text' property to be used with the
	 * pageActionMenu.mustache template.
	 */
	protected function getHistoryPageAction() {
		return [
			'item-id' => 'page-actions-history',
			'class' => MinervaUI::iconClass( 'clock' ),
			'text' => $this->msg( 'mobile-frontend-history' ),
			'href' => $this->getHistoryUrl( $this->getTitle() )
		];
	}

	/**
	 * Checks whether the editor can handle the existing content handler type.
	 *
	 * @return bool
	 */
	protected function isCurrentPageContentModelEditable() {
		$contentHandler = $this->getContentHandler();

		return $contentHandler->supportsDirectEditing()
			&& $contentHandler->supportsDirectApiEditing();
	}

	/**
	 * Returns a data representation of the main menus
	 * @return array
	 */
	protected function getMenuData() {
		$data = [
			'groups' => [
				$this->getDiscoveryTools(),
				$this->getPersonalTools(),
				$this->getConfigurationTools(),
			],
			'sitelinks' => $this->getSiteLinks(),
		];

		return $data;
	}

	/**
	 * Returns array of config variables that should be added only to this skin
	 * for use in JavaScript.
	 * @return array
	 */
	public function getSkinConfigVariables() {
		$title = $this->getTitle();
		$user = $this->getUser();
		$out = $this->getOutput();

		$vars = [
			'wgMinervaFeatures' => $this->skinOptions,
			'wgMinervaDownloadNamespaces' => $this->getConfig()->get( 'MinervaDownloadNamespaces' ),
			'wgMinervaMenuData' => $this->getMenuData(),
		];

		return $vars;
	}

	/**
	 * Returns true, if the page can have a talk page and user is logged in.
	 * @return bool
	 */
	protected function isTalkAllowed() {
		$title = $this->getTitle();
		return $this->isAllowedPageAction( 'talk' ) &&
			( $title->isTalkPage() || $title->canHaveTalkPage() ) &&
			$this->getUser()->isLoggedIn();
	}

	/**
	 * Returns true, if the talk page of this page is wikitext-based.
	 * @return bool
	 */
	protected function isWikiTextTalkPage() {
		$title = $this->getTitle();
		if ( !$title->isTalkPage() ) {
			$title = $title->getTalkPage();
		}
		return $title->isWikitextPage();
	}

	/**
	 * Returns an array of modules related to the current context of the page.
	 * @return array
	 */
	public function getContextSpecificModules() {
		$modules = [];
		$user = $this->getUser();
		$req = $this->getRequest();
		$title = $this->getTitle();

		if ( !$title->isSpecialPage() ) {
			if ( $this->isAllowedPageAction( 'watch' ) ) {
				// Explicitly add the mobile watchstar code.
				$modules[] = 'skins.minerva.watchstar';
			}
		}

		if ( $user->isLoggedIn() ) {
			if ( $this->useEcho() ) {
				$modules[] = 'skins.minerva.notifications';
			}
		}

		// TalkOverlay feature
		if (
			$this->getUserPageHelper()->isUserPage() ||
			( $this->isTalkAllowed() || $title->isTalkPage() ) &&
			$this->isWikiTextTalkPage()
		) {
			$modules[] = 'skins.minerva.talk';
		}

		if ( $this->hasSkinOptions() ) {
			$modules[] = 'skins.minerva.options';
		}
		if ( $this->getSkinOption( self::OPTION_SHARE_BUTTON ) ) {
			$modules[] = 'skins.minerva.share';
		}
		return $modules;
	}

	/**
	 * Returns the javascript entry modules to load. Only modules that need to
	 * be overriden or added conditionally should be placed here.
	 * @return array
	 */
	public function getDefaultModules() {
		$modules = parent::getDefaultModules();
		// dequeue default content modules (toc, sortable, collapsible, etc.)
		$modules['content'] = [];
		// dequeue styles associated with `content` key.
		$modules['styles']['content'] = [];
		$modules['styles']['core'] = $this->getSkinStyles();
		// dequeue default watch module (not needed, no watchstar in this skin)
		$modules['watch'] = [];
		// disable default skin search modules
		$modules['search'] = [];

		$modules['minerva'] = array_merge(
			$this->getContextSpecificModules(),
			[
				'skins.minerva.scripts'
			]
		);

		if ( $this->getSkinOption( self::OPTION_TOGGLING ) ) {
			// Extension can unload "toggling" modules via the hook
			$modules['toggling'] = [ 'skins.minerva.toggling' ];
		}

		Hooks::run( 'SkinMinervaDefaultModules', [ $this, &$modules ] );

		return $modules;
	}

	/**
	 * Modifies the `<body>` element's attributes.
	 *
	 * By default, the `class` attribute is set to the output's "bodyClassName"
	 * property.
	 *
	 * @param OutputPage $out
	 * @param array &$bodyAttrs
	 */
	public function addToBodyAttributes( $out, &$bodyAttrs ) {
		$classes = $out->getProperty( 'bodyClassName' );
		if ( $this->getSkinOption( self::OPTION_AMC ) ) {
			$classes .= ' minerva--amc-enabled';
		} else {
			$classes .= ' minerva--amc-disabled';
		}

		$bodyAttrs[ 'class' ] .= ' ' . $classes;
	}

	/**
	 * Get the needed styles for this skin
	 * @return array
	 */
	protected function getSkinStyles() {
		$title = $this->getTitle();
		$styles = [
			'skins.minerva.base.styles',
			'skins.minerva.content.styles',
			'skins.minerva.content.styles.images',
			'mediawiki.hlist',
			'mediawiki.ui.icon',
			'mediawiki.ui.button',
			'skins.minerva.icons.images'
		];
		if ( $title->isMainPage() ) {
			$styles[] = 'skins.minerva.mainPage.styles';
		} elseif ( $this->getUserPageHelper()->isUserPage() ) {
			$styles[] = 'skins.minerva.userpage.styles';
			$styles[] = 'skins.minerva.userpage.icons';
		}
		if ( $this->isAuthenticatedUser() ) {
			$styles[] = 'skins.minerva.loggedin.styles';
			$styles[] = 'skins.minerva.icons.loggedin';
		}

		if ( $this->getSkinOption( self::OPTION_AMC ) ) {
			$styles[] = 'skins.minerva.amc.styles';
		}

		return $styles;
	}
}

// Setup alias for compatibility with SkinMinervaNeue.
class_alias( 'SkinMinerva', 'SkinMinervaNeue' );
