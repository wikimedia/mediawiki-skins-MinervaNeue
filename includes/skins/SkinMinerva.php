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

use MediaWiki\MediaWikiServices;
use MediaWiki\Minerva\Menu\Main\MainMenuDirector;
use MediaWiki\Minerva\Permissions\IMinervaPagePermissions;
use MediaWiki\Minerva\SkinOptions;
use MediaWiki\Minerva\SkinUserPageHelper;

/**
 * Minerva: Born from the godhead of Jupiter with weapons!
 * A skin that works on both desktop and mobile
 * @ingroup Skins
 */
class SkinMinerva extends SkinTemplate {
	/** @const LEAD_SECTION_NUMBER integer which corresponds to the lead section
	  in editing mode */
	const LEAD_SECTION_NUMBER = 0;

	/** @var string $skinname Name of this skin */
	public $skinname = 'minerva';
	/** @var string $template Name of this used template */
	public $template = 'MinervaTemplate';

	/** @var SkinOptions  */
	private $skinOptions;

	/**
	 * This variable is lazy loaded, please use getPermissions() getter
	 * @see SkinMinerva::getPermissions()
	 * @var IMinervaPagePermissions
	 */
	private $permissions;

	/**
	 * Initialize Minerva Skin
	 */
	public function __construct() {
		parent::__construct( 'minerva' );
		$this->skinOptions = MediaWikiServices::getInstance()->getService( 'Minerva.SkinOptions' );
	}

	/**
	 * Lazy load the permissions object. We don't want to initialize it as it requires many
	 * dependencies, sometimes some of those dependencies cannot be fulfilled (like missing Title
	 * object)
	 * @return IMinervaPagePermissions
	 */
	private function getPermissions(): IMinervaPagePermissions {
		if ( $this->permissions === null ) {
			$this->permissions = MediaWikiServices::getInstance()
				->getService( 'Minerva.Permissions' );
		}
		return $this->permissions;
	}

	/**
	 * Initalized main menu. Please use getter.
	 * @var MainMenuDirector
	 */
	private $mainMenu;

	/**
	 * Build the Main Menu Director by passing the skin options
	 *
	 * @return MainMenuDirector
	 */
	protected function getMainMenu(): MainMenuDirector {
		if ( !$this->mainMenu ) {
			$this->mainMenu = MediaWikiServices::getInstance()->getService( 'Minerva.Menu.MainDirector' );
		}
		return $this->mainMenu;
	}

	/**
	 * Returns the site name for the footer, either as a text or <img> tag
	 * @return string
	 * @throws ConfigException
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

	/**
	 * initialize various variables and generate the template
	 * @return QuickTemplate
	 * @suppress PhanTypeMismatchArgument
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

		// Set whether or not the page content should be wrapped in div.content (for
		// example, on a special page)
		$tpl->set( 'unstyledContent', $out->getProperty( 'unstyledContent' ) );

		// Set the links for page secondary actions
		$tpl->set( 'secondary_actions', $this->getSecondaryActions( $tpl ) );

		// Construct various Minerva-specific interface elements
		$this->prepareMenus( $tpl );
		$this->preparePageContent( $tpl );
		$this->prepareHeaderAndFooter( $tpl );
		$this->prepareBanners( $tpl );
		$this->prepareUserNotificationsButton( $tpl, $tpl->get( 'newtalk' ) );
		$this->prepareLanguages( $tpl );

		return $tpl;
	}

	/**
	 * Prepare all Minerva menus
	 * @param BaseTemplate $tpl
	 * @throws MWException
	 */
	private function prepareMenus( BaseTemplate $tpl ) {
		$services = MediaWikiServices::getInstance();
		/** @var \MediaWiki\Minerva\Menu\PageActions\PageActionsDirector $pageActionsDirector */
		$pageActionsDirector = $services->getService( 'Minerva.Menu.PageActionsDirector' );
		/** @var \MediaWiki\Minerva\Menu\User\UserMenuDirector $userMenuDirector */
		$userMenuDirector = $services->getService( 'Minerva.Menu.UserMenuDirector' );

		$tpl->set( 'mainMenu', $this->getMainMenu()->getMenuData() );
		$tpl->set( 'pageActionsMenu', $pageActionsDirector->buildMenu( $tpl->getToolbox() ) );
		$tpl->set( 'userMenuHTML', $userMenuDirector->renderMenuData( $tpl->getPersonalTools() ) );
	}

	/**
	 * Prepares the header and the content of a page
	 * Stores in QuickTemplate prebodytext, postbodytext keys
	 * @param QuickTemplate $tpl
	 */
	protected function preparePageContent( QuickTemplate $tpl ) {
		$services = MediaWikiServices::getInstance();
		$title = $this->getTitle();

		// If it's a talk page, add a link to the main namespace page
		// In AMC we do not need to do this as there is an easy way back to the article page
		// via the talk/article tabs.
		if ( $title->isTalkPage() && !$this->skinOptions->get( SkinOptions::TALK_AT_TOP ) ) {
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
			$subjectPage = $services->getNamespaceInfo()->getSubjectPage( $title );

			$tpl->set( 'subject-page', MediaWikiServices::getInstance()->getLinkRenderer()->makeLink(
				$subjectPage,
				$this->msg( $msg, $title->getText() )->text(),
				[ 'class' => 'return-link' ]
			) );
		}
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
		if ( $this->getPermissions()->isAllowed( IMinervaPagePermissions::EDIT_OR_CREATE ) &&
			 !$nt->isMainPage() ) {
			$message = $this->msg( 'mobile-frontend-editor-edit' )->inLanguage( $lang )->text();
			$html = Html::openElement( 'span', [ 'class' => 'mw-editsection' ] );
			$html .= Html::element( 'a', [
				'href' => $this->getTitle()->getLocalURL( [ 'action' => 'edit', 'section' => $section ] ),
				'title' => $this->msg( 'editsectionhint', $tooltip )->inLanguage( $lang )->text(),
				'data-section' => $section,
				// Note visibility of the edit section link button is controlled by .edit-page in ui.less so
				// we default to enabled even though this may not be true.
				'class' => MinervaUI::iconClass(
					'edit-enabled', 'element', 'edit-page mw-ui-icon-flush-right'
				),
			], $message );
			$html .= Html::closeElement( 'span' );
			return $html;
		}
		return '';
	}

	/**
	 * Takes a title and returns classes to apply to the body tag
	 * @param Title $title
	 * @return string
	 */
	public function getPageClasses( $title ) {
		$className = parent::getPageClasses( $title );
		$className .= ' ' . ( $this->skinOptions->get( SkinOptions::BETA_MODE )
				? 'beta' : 'stable' );

		if ( $title->isMainPage() ) {
			$className .= ' page-Main_Page ';
		}

		if ( $this->getUser()->isLoggedIn() ) {
			$className .= ' is-authenticated';
		}
		// The new treatment should only apply to the main namespace
		if (
			$title->getNamespace() === NS_MAIN &&
			$this->skinOptions->get( SkinOptions::PAGE_ISSUES )
		) {
			$className .= ' issues-group-B';
		}
		return $className;
	}

	/**
	 * Whether the output page contains category links and the category feature is enabled.
	 * @return bool
	 */
	private function hasCategoryLinks() {
		if ( !$this->skinOptions->get( SkinOptions::CATEGORIES ) ) {
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
		return MediaWikiServices::getInstance()->getService( 'Minerva.SkinUserPageHelper' );
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
	 * @param string $newTalks New talk page messages for the current user
	 */
	protected function prepareUserNotificationsButton( QuickTemplate $tpl, $newTalks ) {
		// Set user button to empty string by default
		$tpl->set( 'userNotificationsData', '' );
		$notificationsTitle = '';
		$count = 0;
		$countLabel = '';
		$isZero = true;
		$hasUnseen = false;

		$user = $this->getUser();
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
		} elseif ( !empty( $newTalks ) ) {
			$notificationsTitle = SpecialPage::getTitleFor( 'Mytalk' );
			$notificationsMsg = $this->msg( 'mobile-frontend-user-newmessages' )->text();
		}

		if ( $notificationsTitle ) {
			$url = $notificationsTitle->getLocalURL(
				[ 'returnto' => $currentTitle->getPrefixedText() ] );

			$tpl->set( 'userNotificationsData', [
				'notificationIconClass' => MinervaUI::iconClass( 'bellOutline-base20',
					'element', '', 'wikimedia' ),
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
	 * Get a history link which describes author and relative time of last edit
	 * @param Title $title The Title object of the page being viewed
	 * @param int $timestamp
	 * @return array
	 */
	protected function getRelativeHistoryLink( Title $title, $timestamp ) {
		$user = $this->getUser();
		$userDate = $this->getLanguage()->userDate( $timestamp, $user );
		$text = $this->msg(
			'minerva-last-modified-date', $userDate,
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
	 *
	 * The relative time is only rendered on the latest revision.
	 * For older revisions the last modified information will not render with a relative time
	 * nor will it show the name of the editor.
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
		if ( $this->getUserPageHelper()->isUserPage() &&
			 // when overflow menu is visible, we don't need to build secondary options
			 // as most of options are visible on Toolbar/Overflow menu
			 !$this->skinOptions->get( SkinOptions::TOOLBAR_SUBMENU ) ) {
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

		if ( $this->canUseWikiPage() && $this->getWikiPage()->exists() ) {
			$tpl->set( 'historyLink', $this->getHistoryLink( $title ) );
		}
		$tpl->set( 'headinghtml', $this->getHeadingHtml() );

		$tpl->set( 'footer-site-heading-html', $this->getSitename() );
		// set defaults
		if ( !isset( $tpl->data['postbodytext'] ) ) {
			$tpl->set( 'postbodytext', '' ); // not currently set in desktop skin
		}
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
	 * @return array
	 */
	protected function getSecondaryActions( BaseTemplate $tpl ) {
		$services = MediaWikiServices::getInstance();
		$namespaceInfo = $services->getNamespaceInfo();
		/** @var \MediaWiki\Minerva\LanguagesHelper $languagesHelper */
		$languagesHelper = $services->getService( 'Minerva.LanguagesHelper' );
		$buttons = [];
		// always add a button to link to the talk page
		// in beta it will be the entry point for the talk overlay feature,
		// in stable it will link to the wikitext talk page
		$title = $this->getTitle();
		$subjectPage = Title::newFromLinkTarget( $namespaceInfo->getSubjectPage( $title ) );
		$talkAtBottom = !$this->skinOptions->get( SkinOptions::TALK_AT_TOP ) ||
			$subjectPage->isMainPage();
		$namespaces = $tpl->data['content_navigation']['namespaces'];
		if ( !$this->getUserPageHelper()->isUserPage()
			 && $this->getPermissions()->isTalkAllowed() && $talkAtBottom
		) {
			// FIXME [core]: This seems unnecessary..
			$subjectId = $title->getNamespaceKey( '' );
			$talkId = $subjectId === 'main' ? 'talk' : "{$subjectId}_talk";

			if ( isset( $namespaces[$talkId] ) ) {
				$talkButton = $namespaces[$talkId];
				$talkTitle = Title::newFromLinkTarget( $namespaceInfo->getTalkPage( $title ) );

				if ( $title->isTalkPage() ) {
					$talkButton['text'] = wfMessage( 'minerva-talk-add-topic' );
					$buttons['talk'] = $this->getTalkButton( $title, $talkButton, true );
				} else {
					$buttons['talk'] = $this->getTalkButton( $talkTitle, $talkButton );
				}
			}
		}

		if ( $languagesHelper->doesTitleHasLanguagesOrVariants( $title ) && $title->isMainPage() ) {
			$buttons['language'] = $this->getLanguageButton();
		}

		if ( $this->hasCategoryLinks() ) {
			$buttons['categories'] = $this->getCategoryButton();
		}

		return $buttons;
	}

	/**
	 * Minerva skin do not have sidebar, there is no need to calculate that.
	 * @return array
	 */
	public function buildSidebar() {
		return [];
	}

	/**
	 * Returns array of config variables that should be added only to this skin
	 * for use in JavaScript.
	 * @return array
	 */
	public function getSkinConfigVariables() {
		$title = $this->getTitle();

		$menuData = $this->getMainMenu()->getMenuData();
		$vars = [
			'wgMinervaPermissions' => [
				'watch' => $this->getPermissions()->isAllowed( IMinervaPagePermissions::WATCH ),
				'talk' => $this->getUserPageHelper()->isUserPage() ||
					( $this->getPermissions()->isTalkAllowed() || $title->isTalkPage() ) &&
					$this->isWikiTextTalkPage()
			],
			'wgMinervaFeatures' => $this->skinOptions->getAll(),
			'wgMinervaDownloadNamespaces' => $this->getConfig()->get( 'MinervaDownloadNamespaces' ),
			// hamburger icon is already rendered, pass only menu items
			'wgMinervaMenuData' => $menuData['items']
		];

		return $vars;
	}

	/**
	 * Returns true, if the talk page of this page is wikitext-based.
	 * @return bool
	 */
	protected function isWikiTextTalkPage() {
		$title = $this->getTitle();
		if ( !$title->isTalkPage() ) {
			$namespaceInfo = MediaWikiServices::getInstance()->getNamespaceInfo();
			$title = Title::newFromLinkTarget( $namespaceInfo->getTalkPage( $title ) );
		}
		return $title->isWikitextPage();
	}

	/**
	 * Returns an array of modules related to the current context of the page.
	 * @return array
	 */
	public function getContextSpecificModules() {
		$modules = [];
		if ( $this->skinOptions->hasSkinOptions() ) {
			$modules[] = 'skins.minerva.options';
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

		// FIXME: T223204: Dequeue default content modules except for the history
		// action. Allow default history action content modules
		// (mediawiki.page.ready, jquery.makeCollapsible,
		// jquery.makeCollapsible.styles, etc) in order to enable toggling of the
		// filters. Long term this won't be necessary when T111565 is resolved and a
		// more general solution can be used.
		if ( Action::getActionName( $this->getContext() ) !== 'history' ) {
			// dequeue default content modules (toc, sortable, collapsible, etc.)
			$modules['content'] = [];
			// dequeue styles associated with `content` key.
			$modules['styles']['content'] = [];
		}
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
		if (
			// Class is used when page actions is modified to contain more elements
			$this->skinOptions->get( SkinOptions::HISTORY_IN_PAGE_ACTIONS )
		) {
			$classes .= ' minerva--history-page-action-enabled';
		}

		$bodyAttrs[ 'class' ] .= ' ' . $classes;
	}

	/**
	 * Provide styles required to present the server rendered page in this skin. Additional styles
	 * may be loaded dynamically by the client.
	 *
	 * Any styles returned by this method are loaded on the critical rendering path as linked
	 * stylesheets. I.e., they are required to load on the client before first paint.
	 *
	 * @return array
	 */
	protected function getSkinStyles(): array {
		$title = $this->getTitle();
		$styles = [
			'skins.minerva.base.styles',
			'skins.minerva.content.styles',
			'skins.minerva.content.styles.images',
			'mediawiki.hlist',
			'mediawiki.ui.icon',
			'mediawiki.ui.button',
			'skins.minerva.icons.wikimedia',
			'skins.minerva.icons.images',
		];
		if ( $title->isMainPage() ) {
			$styles[] = 'skins.minerva.mainPage.styles';
		} elseif ( $this->getUserPageHelper()->isUserPage() ) {
			$styles[] = 'skins.minerva.userpage.styles';
			$styles[] = 'skins.minerva.userpage.icons';
		}
		if ( $this->getUser()->isLoggedIn() ) {
			$styles[] = 'skins.minerva.loggedin.styles';
			$styles[] = 'skins.minerva.icons.loggedin';
		}

		// When any of these features are enabled in production
		// remove the if condition
		// and move the associated LESS file inside `skins.minerva.amc.styles`
		// into a more appropriate module.
		if (
			$this->skinOptions->get( SkinOptions::PERSONAL_MENU ) ||
			$this->skinOptions->get( SkinOptions::TALK_AT_TOP ) ||
			$this->skinOptions->get( SkinOptions::HISTORY_IN_PAGE_ACTIONS ) ||
			$this->skinOptions->get( SkinOptions::TOOLBAR_SUBMENU )
		) {
			// SkinOptions::PERSONAL_MENU + SkinOptions::TOOLBAR_SUBMENU uses components/ToggleList
			// SkinOptions::TALK_AT_TOP uses tabs.less
			// SkinOptions::HISTORY_IN_PAGE_ACTIONS + SkinOptions::TOOLBAR_SUBMENU uses pageactions.less
			$styles[] = 'skins.minerva.amc.styles';
		}

		if ( $this->skinOptions->get( SkinOptions::PERSONAL_MENU ) ) {
			// If ever enabled as the default, please remove the duplicate icons
			// inside skins.minerva.mainMenu.icons. See comment for MAIN_MENU_EXPANDED
			$styles[] = 'skins.minerva.personalMenu.icons';
		}

		if ( $this->skinOptions->get( SkinOptions::MAIN_MENU_EXPANDED ) ) {
			// If ever enabled as the default, please review skins.minerva.mainMenu.icons
			// and remove any unneeded icons
			$styles[] = 'skins.minerva.mainMenu.advanced.icons';
		}
		if (
			$this->skinOptions->get( SkinOptions::PERSONAL_MENU ) ||
			$this->skinOptions->get( SkinOptions::TOOLBAR_SUBMENU )
		) {
			// SkinOptions::PERSONAL_MENU requires the `userTalk` icon.
			// SkinOptions::TOOLBAR_SUBMENU requires the rest of the icons.
			// Note wikimedia.ui is pulled down by skins.minerva.scripts but the menu can
			// work without JS.
			$styles[] = 'wikimedia.ui';
		}

		return $styles;
	}
}

// Setup alias for compatibility with SkinMinervaNeue.
class_alias( 'SkinMinerva', 'SkinMinervaNeue' );
