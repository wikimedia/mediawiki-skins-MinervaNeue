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

namespace MediaWiki\Minerva\Skins;

use MediaWiki\Cache\GenderCache;
use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\Minerva\LanguagesHelper;
use MediaWiki\Minerva\Menu\Definitions;
use MediaWiki\Minerva\Menu\Main\AdvancedMainMenuBuilder;
use MediaWiki\Minerva\Menu\Main\DefaultMainMenuBuilder;
use MediaWiki\Minerva\Menu\Main\MainMenuDirector;
use MediaWiki\Minerva\Menu\PageActions\PageActions;
use MediaWiki\Minerva\Menu\User\AdvancedUserMenuBuilder;
use MediaWiki\Minerva\Menu\User\DefaultUserMenuBuilder;
use MediaWiki\Minerva\Menu\User\UserMenuDirector;
use MediaWiki\Minerva\Permissions\IMinervaPagePermissions;
use MediaWiki\Minerva\Permissions\MinervaPagePermissions;
use MediaWiki\Minerva\SkinOptions;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Skin\SkinMustache;
use MediaWiki\Skin\SkinTemplate;
use MediaWiki\Skins\Vector\ConfigHelper;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\Title;
use MediaWiki\User\Options\UserOptionsManager;
use MediaWiki\User\UserIdentityUtils;
use MediaWiki\Utils\MWTimestamp;
use SpecialMobileHistory;

/**
 * Minerva: Born from the godhead of Jupiter with weapons!
 * A skin that works on both desktop and mobile
 * @ingroup Skins
 */
class SkinMinerva extends SkinMustache {
	/** @const LEAD_SECTION_NUMBER integer which corresponds to the lead section
	 * in editing mode
	 */
	public const LEAD_SECTION_NUMBER = 0;

	/** @var string Name of this skin */
	public $skinname = 'minerva';
	/** @var string Name of this used template */
	public $template = 'MinervaTemplate';

	private GenderCache $genderCache;
	private LinkRenderer $linkRenderer;
	private LanguagesHelper $languagesHelper;
	private Definitions $definitions;
	private PageActions $pageActions;
	private IMinervaPagePermissions $permissions;
	private SkinOptions $skinOptions;
	private SkinUserPageHelper $skinUserPageHelper;
	private NamespaceInfo $namespaceInfo;
	private RevisionLookup $revisionLookup;
	private UserIdentityUtils $userIdentityUtils;
	private UserOptionsManager $userOptionsManager;
	private FeaturesHelper $featuresHelper;

	/**
	 * @param GenderCache $genderCache
	 * @param LinkRenderer $linkRenderer
	 * @param LanguagesHelper $languagesHelper
	 * @param Definitions $definitions
	 * @param PageActions $pageActions
	 * @param MinervaPagePermissions $permissions
	 * @param SkinOptions $skinOptions
	 * @param SkinUserPageHelper $skinUserPageHelper
	 * @param NamespaceInfo $namespaceInfo
	 * @param RevisionLookup $revisionLookup
	 * @param UserIdentityUtils $userIdentityUtils
	 * @param UserOptionsManager $userOptionsManager
	 * @param ConfigHelper|null $configHelper
	 * @param array $options
	 */
	public function __construct(
		GenderCache $genderCache,
		LinkRenderer $linkRenderer,
		LanguagesHelper $languagesHelper,
		Definitions $definitions,
		PageActions $pageActions,
		MinervaPagePermissions $permissions,
		SkinOptions $skinOptions,
		SkinUserPageHelper $skinUserPageHelper,
		NamespaceInfo $namespaceInfo,
		RevisionLookup $revisionLookup,
		UserIdentityUtils $userIdentityUtils,
		UserOptionsManager $userOptionsManager,
		?ConfigHelper $configHelper,
		$options = []
	) {
		parent::__construct( $options );
		$this->genderCache = $genderCache;
		$this->linkRenderer = $linkRenderer;
		$this->languagesHelper = $languagesHelper;
		$this->definitions = $definitions
			->setContext( $this->getContext() );
		$this->pageActions = $pageActions;
		$this->permissions = $permissions
			->setContext( $this->getContext() );
		$this->skinOptions = $skinOptions;
		$this->skinUserPageHelper = $skinUserPageHelper
			->setContext( $this->getContext() )
			->setTitle( $this->getTitle() );
		$this->namespaceInfo = $namespaceInfo;
		$this->revisionLookup = $revisionLookup;
		$this->userIdentityUtils = $userIdentityUtils;
		$this->userOptionsManager = $userOptionsManager;
		$this->featuresHelper = new FeaturesHelper( $configHelper );
	}

	/**
	 * @return bool
	 */
	private function hasPageActions(): bool {
		return !$this->getTitle()->isSpecialPage() &&
			$this->getActionName() === 'view';
	}

	/**
	 * @return bool
	 */
	private function hasSecondaryActions(): bool {
		return !$this->skinUserPageHelper->isUserPage();
	}

	/**
	 * @return bool
	 */
	private function isFallbackEditor(): bool {
		return $this->getActionName() === 'edit';
	}

	/**
	 * Returns available page actions if the page has any.
	 *
	 * @param array $actions
	 * @param array $views
	 * @return array|null
	 */
	private function getPageActions( array $actions, array $views ): ?array {
		if ( $this->isFallbackEditor() || !$this->hasPageActions() ) {
			return null;
		}

		$pageActionsDirector = $this->pageActions->getPageActionsDirector( $this->getContext() );
		$sidebar = $this->buildSidebar();
		return $pageActionsDirector->buildMenu( $sidebar['TOOLBOX'], $actions, $views );
	}

	/**
	 * A notification icon that links to Special:Mytalk when Echo is not installed.
	 * Consider upstreaming this to core or removing at a future date.
	 *
	 * @return array
	 */
	private function getNotificationFallbackButton(): array {
		return [
			'icon' => 'bellOutline',
			'href' => SpecialPage::getTitleFor( 'Mytalk' )->getLocalURL(
				[ 'returnto' => $this->getTitle()->getPrefixedText() ]
			),
		];
	}

	/**
	 * Caches content navigation urls locally for use inside getTemplateData
	 *
	 * @inheritDoc
	 */
	protected function runOnSkinTemplateNavigationHooks( SkinTemplate $skin, &$contentNavigationUrls ) {
		parent::runOnSkinTemplateNavigationHooks( $skin, $contentNavigationUrls );
		// There are some SkinTemplate modifications that occur after the execution of this hook
		// to add rel attributes and ID attributes.
		// The only one Minerva needs is this one so we manually add it.
		foreach ( array_keys( $contentNavigationUrls['associated-pages'] ) as $id ) {
			if ( in_array( $id, [ 'user_talk', 'talk' ] ) ) {
				$contentNavigationUrls['associated-pages'][ $id ]['rel'] = 'discussion';
			}
		}

		if ( $this->getUser()->isRegistered() ) {
			if ( count( $contentNavigationUrls['notifications'] ) === 0 ) {
				// Shown to logged in users when Echo is not installed:
				$contentNavigationUrls['notifications']['mytalks'] = $this->getNotificationFallbackButton();
			}
		}
	}

	/**
	 * Maps data returned by getTemplateData into a historic data format that can be used
	 * in Minerva's menu code.
	 * @param array $dataPortlet
	 * @return array
	 */
	private function mapPortletData( array $dataPortlet ) {
		$nav = [];
		foreach ( $dataPortlet[ 'array-items' ] as $item ) {
			$itemData = $item[ 'array-links' ][ 0 ];
			$data = [
				'class' => $item[ 'class' ] ?? '',
				'text' => $itemData[ 'text' ] ?? '',
				'icon' => $itemData[ 'icon' ] ?? null,
				'context' => $item[ 'name' ] ?? null,
			];
			$attributes = $itemData['array-attributes' ] ?? [];
			$attributesNew = [];
			foreach ( $attributes as $key => $attrDefinition ) {
				$key = $attrDefinition[ 'key' ];
				if ( in_array( $key, [ 'href', 'rel' ] ) ) {
					$data[ $key ] = $attrDefinition[ 'value' ];
				} elseif ( $key !== 'class' ) {
					// ignore class name but copy across other attributes
					$attributesNew[] = $attrDefinition;
				}
			}
			$nav[ $item[ 'name' ] ] = $data + [
				'array-attributes' => $attributesNew,
			];
		}
		return $nav;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$data = parent::getTemplateData();
		$portletData = $data[ 'data-portlets' ];
		$navUserMenu = $this->mapPortletData( $portletData['data-user-menu'] ?? [] );

		$actions = $this->mapPortletData( $portletData['data-actions'] ?? [] );
		$views = $this->mapPortletData( $portletData['data-views'] ?? [] );

		if ( !$this->hasCategoryLinks() ) {
			unset( $data['html-categories'] );
		}

		// Special handling for certain pages.
		// This is technical debt that should be upstreamed to core.
		$isUserPage = $this->skinUserPageHelper->isUserPage();
		$isUserPageAccessible = $this->skinUserPageHelper->isUserPageAccessibleToCurrentUser();
		if ( $isUserPage && $isUserPageAccessible ) {
			$data['html-title-heading'] = $this->getUserPageHeadingHtml();
		}

		$usermessage = $data['html-user-message'] ?? '';
		if ( $usermessage ) {
			$data['html-user-message'] = Html::warningBox(
				'<span class="minerva-icon minerva-icon--userTalk-warning"></span>&nbsp;'
					. $usermessage,
				'minerva-anon-talk-message'
			);
		}
		$allLanguages = $data['data-portlets']['data-languages']['array-items'] ?? [];
		$allVariants = $data['data-portlets']['data-variants']['array-items'] ?? [];
		$notifications = $data['data-portlets']['data-notifications']['array-items'] ?? [];
		$associatedPages = $data['data-portlets']['data-associated-pages'] ?? [];
		$associatedPagesPortletData = $this->mapPortletData( $associatedPages );

		return $data + [
			'has-minerva-languages' => $allLanguages || $allVariants,
			'array-minerva-banners' => $this->prepareBanners( $data['html-site-notice'] ?? '' ),
			'data-minerva-search-box' => $data['data-search-box'] + [
				'data-btn' => [
					'data-icon' => [
						'icon' => 'search',
					],
					'label' => $this->msg( 'searchbutton' )->escaped(),
					'classes' => 'skin-minerva-search-trigger',
					'array-attributes' => [
						[
							'key' => 'id',
							'value' => 'searchIcon',
						]
					]
				],
			],
			'data-minerva-main-menu-btn' => [
				'data-icon' => [
					'icon' => 'menu',
				],
				'tag-name' => 'label',
				'classes' => 'toggle-list__toggle',
				'array-attributes' => [
					[
						'key' => 'for',
						'value' => 'main-menu-input',
					],
					[
						'key' => 'id',
						'value' => 'mw-mf-main-menu-button',
					],
					[
						'key' => 'aria-hidden',
						'value' => 'true',
					],
				],
				'text' => $this->msg( 'mobile-frontend-main-menu-button-tooltip' )->escaped(),
			],
			'data-minerva-main-menu' => $this->getMainMenu()->getMenuData(
				$navUserMenu,
				$this->buildSidebar(),
			)['items'],
			// Refer to https://phabricator.wikimedia.org/T388036#10608151 for the following line:
			'data-donation-banner' => $this->skinOptions->get( SkinOptions::SHOW_DONATE_BANNER ) &&
				$this->getUser()->isAnon(),
			'html-minerva-tagline' => $this->getTaglineHtml(),
			'html-minerva-user-menu' => $this->getPersonalToolsMenu( $navUserMenu ),
			'is-minerva-beta' => $this->skinOptions->get( SkinOptions::BETA_MODE ),
			'data-minerva-notifications' => $notifications ? [
				'array-buttons' => $this->getNotificationButtons( $notifications ),
			] : null,
			'data-minerva-tabs' => $this->getTabsData( $associatedPagesPortletData, $associatedPages['id'] ?? null ),
			'data-minerva-page-actions' => $this->getPageActions( $actions, $views ),
			'data-minerva-secondary-actions' => $this->getSecondaryActions( $associatedPagesPortletData ),
			'html-minerva-subject-link' => $this->getSubjectPage(),
			'data-minerva-history-link' => $this->getHistoryLink( $this->getTitle() ),
		];
	}

	/**
	 * Prepares the notification badges for the Button template.
	 *
	 * @internal
	 * @param array $notifications
	 * @return array
	 */
	public static function getNotificationButtons( array $notifications ): array {
		$btns = [];

		foreach ( $notifications as $notification ) {
			$linkData = $notification['array-links'][ 0 ] ?? [];
			$icon = $linkData['icon'] ?? null;
			if ( !$icon ) {
				continue;
			}
			$id = $notification['id'] ?? null;
			$classes = '';
			$attributes = [];

			// We don't want to output multiple attributes.
			// Iterate through the attributes and pull out ID and class which
			// will be defined separately.
			foreach ( $linkData[ 'array-attributes' ] as $keyValuePair ) {
				if ( $keyValuePair['key'] === 'class' ) {
					$classes = $keyValuePair['value'];
				} elseif ( $keyValuePair['key'] === 'id' ) {
					// ignore. We want to use the LI `id` instead.
				} else {
					$attributes[] = $keyValuePair;
				}
			}
			// add LI ID to end for use on the button.
			if ( $id ) {
				$attributes[] = [
					'key' => 'id',
					'value' => $id,
				];
			}
			$btns[] = [
				'tag-name' => 'a',
				'isButton' => true,
				'classes' => $classes,
				'array-attributes' => $attributes,
				'data-icon' => [
					'icon' => $icon,
				],
				'label' => $linkData['text'] ?? '',
			];
		}
		return $btns;
	}

	/**
	 * @return bool
	 */
	private function isHistoryPage(): bool {
		return $this->getRequest()->getRawVal( 'action' ) === 'history';
	}

	/**
	 * Tabs are available if a page has page actions but is not the talk page of
	 * the main page.
	 *
	 * Special pages have tabs if SkinOptions::TABS_ON_SPECIALS is enabled.
	 * This is used by Extension:GrowthExperiments
	 *
	 * @return bool
	 */
	private function hasPageTabs(): bool {
		$title = $this->getTitle();

		$isSpecialPageOrHistory = $title->isSpecialPage() ||
			$this->isHistoryPage();
		$subjectPage = $this->namespaceInfo->getSubjectPage( $title );
		$isMainPageTalk = Title::newFromLinkTarget( $subjectPage )->isMainPage();
		return (
				$this->hasPageActions() && !$isMainPageTalk &&
				$this->skinOptions->get( SkinOptions::TALK_AT_TOP )
			) || (
				$isSpecialPageOrHistory &&
				$this->skinOptions->get( SkinOptions::TABS_ON_SPECIALS )
			);
	}

	/**
	 * @param array $associatedPagesPortletData
	 * @param string|null $id of portlet
	 * @return array
	 */
	private function getTabsData( array $associatedPagesPortletData, $id = null ): array {
		$hasPageTabs = $this->hasPageTabs();
		if ( !$hasPageTabs ) {
			return [];
		}
		return $associatedPagesPortletData ? [
			'items' => array_values( $associatedPagesPortletData ),
			'id' => $id,
		] : [];
	}

	/**
	 * Build the Main Menu Director by passing the skin options
	 *
	 * @return MainMenuDirector
	 */
	protected function getMainMenu(): MainMenuDirector {
		$showMobileOptions = $this->skinOptions->get( SkinOptions::MOBILE_OPTIONS );
		// Add a donate link (see https://phabricator.wikimedia.org/T219793)
		$showDonateLink = $this->skinOptions->get( SkinOptions::SHOW_DONATE );
		$builder = $this->skinOptions->get( SkinOptions::MAIN_MENU_EXPANDED ) ?
			new AdvancedMainMenuBuilder(
				$showMobileOptions,
				$showDonateLink,
				$this->definitions
			) :
			new DefaultMainMenuBuilder(
				$showMobileOptions,
				$showDonateLink,
				$this->getUser(),
				$this->definitions,
				$this->userIdentityUtils
			);
		return new MainMenuDirector( $builder );
	}

	/**
	 * Prepare all Minerva menus
	 *
	 * @param array $personalUrls result of SkinTemplate::buildPersonalUrls
	 * @return string|null
	 */
	private function getPersonalToolsMenu( array $personalUrls ): ?string {
		$builder = $this->skinOptions->get( SkinOptions::PERSONAL_MENU ) ?
			new AdvancedUserMenuBuilder(
				$this->getContext(),
				$this->getUser(),
				$this->definitions
			) :
			new DefaultUserMenuBuilder();

		$userMenuDirector = new UserMenuDirector(
			$builder,
			$this->getSkin()
		);
		return $userMenuDirector->renderMenuData( $personalUrls );
	}

	/**
	 * @return string
	 */
	protected function getSubjectPage(): string {
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
				default:
					// generic (all other NS)
					$msg = 'mobile-frontend-talk-back-to-page';
			}
			$subjectPage = $this->namespaceInfo->getSubjectPage( $title );

			return $this->linkRenderer->makeLink(
				$subjectPage,
				$this->msg( $msg, $title->getText() )->text(),
				[
					'data-event-name' => 'talk.returnto',
					'class' => 'return-link'
				]
			);
		} else {
			return '';
		}
	}

	/**
	 * Modifies the template data before parsing in SkinMustache.
	 *
	 * @inheritDoc
	 */
	final protected function doEditSectionLinksHTML( array $links, Language $lang ): string {
		$transformedLinks = [];
		foreach ( $links as $key => $link ) {
			$transformedLinks[] = $link + [
				'data-icon' => [
					'icon' => $link['icon'],
				],
			];
		}
		return parent::doEditSectionLinksHTML( $transformedLinks, $lang );
	}

	/**
	 * Takes a title and returns classes to apply to the body tag
	 * @param Title $title
	 * @return string
	 */
	public function getPageClasses( $title ): string {
		$className = parent::getPageClasses( $title );
		$className .= ' ' . ( $this->skinOptions->get( SkinOptions::BETA_MODE )
				? 'beta' : 'stable' );

		if ( $this->getUser()->isRegistered() ) {
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
	 * Converts "1", "2", and "0" to equivalent values.
	 *
	 * @return string
	 */
	private static function resolveNightModeQueryValue( string $value ): string {
		switch ( $value ) {
			case 'day':
			case 'night':
			case 'os':
				return $value;
			case '1':
				return 'night';
			case '2':
				return 'os';
			default:
				return 'day';
		}
	}

	/**
	 * Provides skin-specific modifications to the HTML element attributes
	 *
	 * Currently only used for adding the night mode class
	 *
	 * @return array
	 */
	public function getHtmlElementAttributes(): array {
		$attributes = parent::getHtmlElementAttributes();

		// check to see if night mode is enabled via query params or by config
		$webRequest = $this->getRequest();
		$forceNightMode = $webRequest->getRawVal( 'minervanightmode' );

		// get skin config of night mode to check what is execluded
		$nightModeConfig = $this->getConfig()->get( 'MinervaNightModeOptions' );
		$shouldDisableNightMode = $this->featuresHelper->shouldDisableNightMode( $nightModeConfig,
			$webRequest,
			$this->getTitle()
		);

		if (
			$this->skinOptions->get( SkinOptions::NIGHT_MODE ) || $forceNightMode !== null
		) {
			$user = $this->getUser();
			$value = $this->userOptionsManager->getOption( $user, 'minerva-theme' );

			// if forcing a (valid) setting via query params, take priority over the user option
			if ( $forceNightMode !== null && in_array( $forceNightMode, [ '1', '0', '2', 'day', 'night', 'os' ] ) ) {
				$value = self::resolveNightModeQueryValue( $forceNightMode );
			}

			// For T356653 add a class to the page to allow the client to detect we've
			// intentionally disabled night mode.
			if ( $shouldDisableNightMode ) {
				$attributes[ 'class' ] .= ' skin-night-mode-page-disabled';
				return $attributes;
			}

			$attributes[ 'class' ] .= " skin-theme-clientpref-$value";
		}

		return $attributes;
	}

	/**
	 * Whether the output page contains category links and the category feature is enabled.
	 * @return bool
	 */
	private function hasCategoryLinks(): bool {
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
	 * Get a history link which describes author and relative time of last edit
	 * @param Title $title The Title object of the page being viewed
	 * @param string $timestamp
	 * @return array
	 */
	protected function getRelativeHistoryLink( Title $title, string $timestamp ): array {
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
	protected function getGenericHistoryLink( Title $title ): array {
		$text = $this->msg( 'mobile-frontend-history' )->plain();
		return [
			'href' => $this->getHistoryUrl( $title ),
			'text' => $text,
		];
	}

	/**
	 * Checks if the Special:History page is being used.
	 * @param Title $title The Title object of the page being viewed
	 * @return bool
	 */
	private function shouldUseSpecialHistory( Title $title ): bool {
		return ExtensionRegistry::getInstance()->isLoaded( 'MobileFrontend' ) &&
			SpecialMobileHistory::shouldUseSpecialHistory( $title, $this->getUser() );
	}

	/**
	 * Get the URL for the history page for the given title using Special:History
	 * when available.
	 * @param Title $title The Title object of the page being viewed
	 * @return string
	 */
	protected function getHistoryUrl( Title $title ): string {
		return $this->shouldUseSpecialHistory( $title ) ?
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
	 * @return array|null
	 */
	protected function getHistoryLink( Title $title ): ?array {
		if ( !$title->exists() ||
			$this->getActionName() !== 'view'
		) {
			return null;
		}
		// Do not show the last modified bar on diff pages [T350515]
		$request = $this->getRequest();
		if ( $request->getCheck( 'diff' ) ) {
			return null;
		}

		$out = $this->getOutput();

		if ( !$out->getRevisionId() || !$out->isRevisionCurrent() || $title->isMainPage() ) {
			$historyLink = $this->getGenericHistoryLink( $title );
		} else {
			// Get rev_timestamp of current revision (preloaded by MediaWiki core)
			$timestamp = $out->getRevisionTimestamp();
			if ( !$timestamp ) {
				# No cached timestamp, load it from the database
				$timestamp = $this->revisionLookup->getTimestampFromId( $out->getRevisionId() );
			}
			$historyLink = $this->getRelativeHistoryLink( $title, $timestamp );
		}

		return $historyLink + [
			'historyIcon' => [
				'icon' => 'modified-history',
				'size' => 'medium'
			],
			'arrowIcon' => [
				'icon' => 'expand',
				'size' => 'small'
			]
		];
	}

	/**
	 * Returns data attributes representing the editor for the current revision.
	 * @param LinkTarget $title The Title object of the page being viewed
	 * @return array representing user with name and gender fields. Empty if the editor no longer
	 *   exists in the database or is hidden from public view.
	 */
	private function getRevisionEditorData( LinkTarget $title ): array {
		$rev = $this->revisionLookup->getRevisionByTitle( $title );
		$result = [];
		if ( $rev ) {
			$revUser = $rev->getUser();
			// Note the user will only be returned if that information is public
			if ( $revUser ) {
				$editorName = $revUser->getName();
				$editorGender = $this->genderCache->getGenderOf( $revUser, __METHOD__ );
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
	protected function getTaglineHtml(): string {
		$tagline = '';

		$pageUser = $this->skinUserPageHelper->getPageUser();
		if ( $pageUser ) {
			$fromDate = $pageUser->getRegistration();

			if ( $this->skinUserPageHelper->isUserPageAccessibleToCurrentUser() && is_string( $fromDate ) ) {
				$fromDateTs = wfTimestamp( TS_UNIX, $fromDate );

				// This is shown when js is disabled. js enhancement made due to caching
				$tagline = $this->msg( 'mobile-frontend-user-page-member-since',
						$this->getLanguage()->userDate( new MWTimestamp( $fromDateTs ), $this->getUser() ),
						$pageUser )->text();

				// Define html attributes for usage with js enhancement (unix timestamp, gender)
				$attrs = [ 'id' => 'tagline-userpage',
					'data-userpage-registration-date' => $fromDateTs,
					'data-userpage-gender' => $this->genderCache->getGenderOf( $pageUser, __METHOD__ ) ];
			}
		} else {
			if ( $this->getTitle() ) {
				$out = $this->getOutput();
				$tagline = $out->getProperty( 'wgMFDescription' );
			}
		}

		$attrs[ 'class' ] = 'tagline';
		return Html::element( 'div', $attrs, $tagline );
	}

	/**
	 * Returns the HTML representing the heading.
	 *
	 * @return string HTML for header
	 */
	private function getUserPageHeadingHtml(): string {
		// The heading is just the username without namespace
		return Html::element( 'h1',
			// These IDs and classes should match Skin::getTemplateData
			[
				'id' => 'firstHeading',
				'class' => 'firstHeading mw-first-heading mw-minerva-user-heading',
			],
			$this->skinUserPageHelper->getPageUser()->getName()
		);
	}

	/**
	 * Load internal banner content to show in pre content in template
	 * Beware of HTML caching when using this function.
	 * Content set as "internalbanner"
	 * @param string $siteNotice HTML fragment
	 * @return array
	 */
	protected function prepareBanners( string $siteNotice ): array {
		$banners = [];
		if ( $siteNotice && $this->getConfig()->get( 'MinervaEnableSiteNotice' ) ) {
			$banners[] = $siteNotice;
		} else {
			$banners[] = '<div id="siteNotice"></div>';
		}
		return $banners;
	}

	/**
	 * Returns an array with details for a language button.
	 * @return array
	 */
	protected function getLanguageButton(): array {
		return [
			'array-attributes' => [
				[
					'key' => 'href',
					'value' => '#p-lang'
				]
			],
			'tag-name' => 'a',
			'isButton' => true,
			'classes' => 'language-selector button',
			'label' => $this->msg( 'mobile-frontend-language-article-heading' )->text()
		];
	}

	/**
	 * Returns an array with details for a talk button.
	 * @param Title $talkTitle Title object of the talk page
	 * @param string $label Button label
	 * @return array
	 */
	protected function getTalkButton( Title $talkTitle, string $label ): array {
		return [
			'array-attributes' => [
				[
					'key' => 'href',
					'value' => $talkTitle->getLinkURL(),
				],
				[
					'key' => 'data-title',
					'value' => $talkTitle->getFullText(),
				]
			],
			'tag-name' => 'a',
			'isButton' => true,
			'classes' => 'talk button',
			'label' => $label,
		];
	}

	/**
	 * Returns an array of links for page secondary actions
	 * @param array $namespaces or associated pages
	 * @return array|null
	 */
	protected function getSecondaryActions( array $namespaces ): ?array {
		if ( $this->isFallbackEditor() || !$this->hasSecondaryActions() ) {
			return null;
		}

		$buttons = [];
		// always add a button to link to the talk page
		// it will link to the wikitext talk page
		$title = $this->getTitle();
		$subjectPage = Title::newFromLinkTarget( $this->namespaceInfo->getSubjectPage( $title ) );
		$talkAtBottom = !$this->skinOptions->get( SkinOptions::TALK_AT_TOP ) ||
			$subjectPage->isMainPage();
		if ( !$this->skinUserPageHelper->isUserPage() &&
			$this->permissions->isTalkAllowed() && $talkAtBottom &&
			// When showing talk at the bottom we restrict this so it is not shown to anons
			// https://phabricator.wikimedia.org/T54165
			// This whole code block can be removed when SkinOptions::TALK_AT_TOP is always true
			$this->getUser()->isRegistered()
		) {
			// FIXME [core]: This seems unnecessary..
			$subjectId = $title->getNamespaceKey( '' );
			$talkId = $subjectId === 'main' ? 'talk' : "{$subjectId}_talk";

			if ( isset( $namespaces[$talkId] ) ) {
				$talkButton = $namespaces[$talkId];
				$talkTitle = Title::newFromLinkTarget( $this->namespaceInfo->getTalkPage( $title ) );

				$buttons['talk'] = $this->getTalkButton( $talkTitle, $talkButton['text'] );
			}
		}

		if (
			$this->languagesHelper->doesTitleHasLanguagesOrVariants( $this->getOutput(), $title ) &&
			$title->isMainPage()
		) {
			$buttons['language'] = $this->getLanguageButton();
		}

		return $buttons;
	}

	/**
	 * @inheritDoc
	 * @return array
	 */
	protected function getJsConfigVars(): array {
		return array_merge( parent::getJsConfigVars(), [
			'wgMinervaPermissions' => [
				'watchable' => $this->permissions->isAllowed( IMinervaPagePermissions::WATCHABLE ),
				'watch' => $this->permissions->isAllowed( IMinervaPagePermissions::WATCH ),
			],
			'wgMinervaFeatures' => $this->skinOptions->getAll(),
			'wgMinervaDownloadNamespaces' => $this->getConfig()->get( 'MinervaDownloadNamespaces' ),
		] );
	}

	/**
	 * Returns the javascript entry modules to load. Only modules that need to
	 * be overriden or added conditionally should be placed here.
	 * @return array
	 */
	public function getDefaultModules(): array {
		$modules = parent::getDefaultModules();

		// FIXME: T223204: Dequeue default content modules except for the history
		// action. Allow default content modules on history action in order to
		// enable toggling of the filters.
		// Long term this won't be necessary when T111565 is resolved and a
		// more general solution can be used.
		if ( $this->getActionName() !== 'history' ) {
			// dequeue default content modules (toc, collapsible, etc.)
			$modules['content'] = array_diff( $modules['content'], [
				// T111565
				'jquery.makeCollapsible',
				// Minerva provides its own implementation. Loading this will break display.
				'mediawiki.toc'
			] );
			// dequeue styles associated with `content` key.
			$modules['styles']['content'] = array_diff( $modules['styles']['content'], [
				// T111565
				'jquery.makeCollapsible.styles',
			] );
		}

		$modules['styles']['skin.page'] = $this->getPageSpecificStyles();
		$modules['styles']['skin.features'] = $this->getFeatureSpecificStyles();

		return $modules;
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
	protected function getPageSpecificStyles(): array {
		$styles = [];
		$title = $this->getTitle();
		$request = $this->getRequest();
		$requestAction = $this->getActionName();
		$viewAction = $requestAction === 'view';

		if ( $title->isMainPage() ) {
			$styles[] = 'skins.minerva.mainPage.styles';
		} elseif ( $this->skinUserPageHelper->isUserPage() ) {
			$styles[] = 'skins.minerva.userpage.styles';
		}

		if ( $this->getUser()->isRegistered() ) {
			$styles[] = 'skins.minerva.loggedin.styles';
		}

		// When any of these features are enabled in production
		// remove the if condition
		// and move the associated LESS file inside `skins.minerva.amc.styles`
		// into a more appropriate module.
		if (
			// T356117 - enable on all special pages - some special pages e.g. Special:Contribute have tabs.
			$title->isSpecialPage() ||
			( $this->isHistoryPage() && !$this->shouldUseSpecialHistory( $title ) ) ||
			$this->skinOptions->get( SkinOptions::PERSONAL_MENU ) ||
			$this->skinOptions->get( SkinOptions::TALK_AT_TOP ) ||
			$this->skinOptions->get( SkinOptions::HISTORY_IN_PAGE_ACTIONS ) ||
			$this->skinOptions->get( SkinOptions::TOOLBAR_SUBMENU )
		) {
			// SkinOptions::PERSONAL_MENU + SkinOptions::TOOLBAR_SUBMENU uses ToggleList
			// SkinOptions::TALK_AT_TOP uses tabs.less
			// SkinOptions::HISTORY_IN_PAGE_ACTIONS + SkinOptions::TOOLBAR_SUBMENU uses pageactions.less
			$styles[] = 'skins.minerva.amc.styles';
		}

		return $styles;
	}

	/**
	 * Provide styles required to present the server rendered page with related features in this skin.
	 * Additional styles may be loaded dynamically by the client.
	 *
	 *  Any styles returned by this method are loaded on the critical rendering path as linked
	 *  stylesheets. I.e., they are required to load on the client before first paint.
	 *
	 * @return array
	 */
	protected function getFeatureSpecificStyles(): array {
		$styles = [];

		if ( $this->hasCategoryLinks() ) {
			$styles[] = 'skins.minerva.categories.styles';
		}

		if ( $this->skinOptions->get( SkinOptions::PERSONAL_MENU ) ) {
			// If ever enabled as the default, please remove the duplicate icons
			// inside skins.minerva.mainMenu.icons. See comment for MAIN_MENU_EXPANDED
			$styles[] = 'skins.minerva.personalMenu.icons';
		}

		if (
			$this->skinOptions->get( SkinOptions::MAIN_MENU_EXPANDED )
		) {
			// If ever enabled as the default, please review skins.minerva.mainMenu.icons
			// and remove any unneeded icons
			$styles[] = 'skins.minerva.mainMenu.advanced.icons';
		}

		if (
			$this->skinOptions->get( SkinOptions::PERSONAL_MENU ) ||
			$this->skinOptions->get( SkinOptions::TOOLBAR_SUBMENU )
		) {
			// SkinOptions::PERSONAL_MENU requires the `userTalk` icon.
			// SkinOptions::TOOLBAR_SUBMENU requires the rest of the icons including `overflow`.
			// Note `skins.minerva.overflow.icons` is pulled down by skins.minerva.scripts but the menu can
			// work without JS.
			$styles[] = 'skins.minerva.overflow.icons';
		}

		return $styles;
	}
}
