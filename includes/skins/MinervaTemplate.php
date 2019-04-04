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

/**
 * Extended Template class of BaseTemplate for mobile devices
 */
class MinervaTemplate extends BaseTemplate {
	/** @var bool Specify whether the page is a special page */
	protected $isSpecialPage;

	/** @var bool Whether or not the user is on the Special:MobileMenu page */
	protected $isSpecialMobileMenuPage;

	/** @var bool Specify whether the page is main page */
	protected $isMainPage;

	/**
	 * Start render the page in template
	 */
	public function execute() {
		$title = $this->getSkin()->getTitle();
		$this->isSpecialPage = $title->isSpecialPage();
		$this->isSpecialMobileMenuPage = $this->isSpecialPage &&
			$title->isSpecial( 'MobileMenu' );
		$this->isMainPage = $title->isMainPage();
		$this->isMainPageTalk = $title->getSubjectPage()->isMainPage();
		Hooks::run( 'MinervaPreRender', [ $this ] );
		$this->render( $this->data );
	}

	/**
	 * Returns available page actions
	 * @return array
	 */
	protected function getPageActions() {
		return $this->isFallbackEditor() ? [] : $this->data['page_actions'];
	}

	/**
	 * Returns template data for footer
	 *
	 * @param array $data Data used to build the page
	 * @return array
	 */
	protected function getFooterTemplateData( $data ) {
		$groups = [];

		foreach ( $data['footerlinks'] as $category => $links ) {
			$items = [];
			foreach ( $links as $link ) {
				if ( isset( $this->data[$link] ) && $data[$link] !== '' && $data[$link] !== false ) {
					$items[] = [
						'category' => $category,
						'name' => $link,
						'linkhtml' => $data[$link],
					];
				}
			}
			$groups[] = [
				'name' => $category,
				'items' => $items,
			];
		}

		// This turns off the footer id and allows us to distinguish the old footer with the new design
		return [
			'lastmodified' => $this->getHistoryLinkHtml( $data ),
			'headinghtml' => $data['footer-site-heading-html'],
			// Note mobile-license is only available on the mobile skin. It is outputted as part of
			// footer-info on desktop hence the conditional check.
			'licensehtml' => isset( $data['mobile-license'] ) ? $data['mobile-license'] : '',
			'lists' => $groups,
		];
	}

	/**
	 * Get the HTML for rendering the available page actions
	 * @return string
	 */
	protected function getPageActionsHtml() {
		$templateParser = new TemplateParser( __DIR__ );
		$actions = $this->getPageActions();
		$html = '';

		if ( $actions ) {
			$html = $templateParser->processTemplate( 'pageActionMenu',  [ 'pageActions' => $actions ] );
		}
		return $html;
	}

	/**
	 * Returns the 'Last edited' message, e.g. 'Last edited on...'
	 * @param array $data Data used to build the page
	 * @return string
	 */
	protected function getHistoryLinkHtml( $data ) {
		$action = Action::getActionName( RequestContext::getMain() );
		if ( isset( $data['historyLink'] ) && $action === 'view' ) {
			$args = [
				'clockIconClass' => MinervaUI::iconClass( 'clock', 'before' ),
				'arrowIconClass' => MinervaUI::iconClass(
					'arrow-gray', 'element', 'mw-ui-icon-small mf-mw-ui-icon-rotate-anti-clockwise indicator',
					// Uses icon in MobileFrontend so must be prefixed mf.
					// Without MobileFrontend it will not render.
					// Rather than maintain 2 versions (and variants) of the arrow icon which can conflict
					// with each othe and bloat CSS, we'll
					// use the MobileFrontend one. Long term when T177432 and T160690 are resolved
					// we should be able to use one icon definition and break this dependency.
					'mf'
				 ),
			] + $data['historyLink'];
			$templateParser = new TemplateParser( __DIR__ );
			return $templateParser->processTemplate( 'history', $args );
		}

		return '';
	}

	/**
	 * @return bool
	 */
	protected function isFallbackEditor() {
		$action = $this->getSkin()->getRequest()->getVal( 'action' );
		return $action === 'edit';
	}

	/**
	 * Get page secondary actions
	 * @return string[]
	 */
	protected function getSecondaryActions() {
		if ( $this->isFallbackEditor() ) {
			return [];
		}

		return $this->data['secondary_actions'];
	}

	/**
	 * Get HTML representing secondary page actions like language selector
	 * @return string
	 */
	protected function getSecondaryActionsHtml() {
		$baseClass = MinervaUI::buttonClass( '', 'button' );
		/** @var SkinMinerva $skin */
		$skin = $this->getSkin();
		$html = '';
		// no secondary actions on the user page
		if ( $skin instanceof SkinMinerva && !$skin->getUserPageHelper()->isUserPage() ) {
			foreach ( $this->getSecondaryActions() as $el ) {
				if ( isset( $el['attributes']['class'] ) ) {
					$el['attributes']['class'] .= ' ' . $baseClass;
				} else {
					$el['attributes']['class'] = $baseClass;
				}
				$html .= Html::element( 'a', $el['attributes'], $el['label'] );
			}
		}

		return $html;
	}

	/**
	 * Get the HTML for the content of a page
	 * @param array $data Data used to build the page
	 * @return string representing HTML of content
	 */
	protected function getContentHtml( $data ) {
		if ( !$data[ 'unstyledContent' ] ) {
			$content = Html::openElement( 'div', [
				'id' => 'bodyContent',
				'class' => 'content',
			] );
			$content .= $data[ 'bodytext' ];
			if ( isset( $data['subject-page'] ) ) {
				$content .= $data['subject-page'];
			}
			return $content . Html::closeElement( 'div' );
		}

		return $data[ 'bodytext' ];
	}

	/**
	 * Gets the main menu only on Special:MobileMenu.
	 * On other pages the menu is rendered via JS.
	 * @param array $data Data used to build the page
	 * @return string
	 */
	protected function getMainMenuHtml( $data ) {
		if ( $this->isSpecialMobileMenuPage ) {
			$templateParser = new TemplateParser(
				__DIR__ . '/../../resources/skins.minerva.scripts/menu/' );

			return $templateParser->processTemplate( 'menu', $data['menu_data'] );
		}

		return '';
	}

	/**
	 * Render the entire page
	 * @param array $data Data used to build the page
	 * @todo replace with template engines
	 */
	protected function render( $data ) {
		$templateParser = new TemplateParser( __DIR__ );
		$skin = $this->getSkin();
		$internalBanner = $data[ 'internalBanner' ];
		$preBodyHtml = isset( $data['prebodyhtml'] ) ? $data['prebodyhtml'] : '';
		$hasHeadingHolder = $internalBanner || $preBodyHtml || isset( $data['page_actions'] );
		$hasPageActions = !$this->isSpecialPage && !$this->isMainPage &&
			Action::getActionName( RequestContext::getMain() ) === 'view';
		$hasTalkTabs = $hasPageActions && !$this->isMainPageTalk;

		// prepare template data
		$templateData = [
			'banners' => $data['banners'],
			'wgScript' => $data['wgScript'],
			'isAnon' => $data['username'] === null,
			'search' => $data['search'],
			'placeholder' => wfMessage( 'mobile-frontend-placeholder' ),
			'headelement' => $data[ 'headelement' ],
			'menuButton' => $data['menuButton'],
			'siteheading' => $data['footer-site-heading-html'],
			'mainPageURL' => Title::newMainPage()->getLocalURL(),
			// A button when clicked will submit the form
			// This is used so that on tablet devices with JS disabled the search button
			// passes the value of input to the search
			// We avoid using input[type=submit] as these cannot be easily styled as mediawiki ui icons
			// which is problematic in Opera Mini (see T140490)
			'searchButton' => Html::rawElement( 'button', [
				'id' => 'searchIcon',
				'class' => MinervaUI::iconClass( 'magnifying-glass', 'element', 'skin-minerva-search-trigger' ),
			], wfMessage( 'searchbutton' ) ),
			'secondaryButtonData' => $data['secondaryButtonData'],
			'mainmenuhtml' => $this->getMainMenuHtml( $data ),
			'hasheadingholder' => $hasHeadingHolder,
			'taglinehtml' => $data['taglinehtml'],
			'internalBanner' => $internalBanner,
			'prebodyhtml' => $preBodyHtml,
			'headinghtml' => isset( $data['headinghtml'] ) ? $data['headinghtml'] : '',
			'postheadinghtml' => isset( $data['postheadinghtml'] ) ? $data['postheadinghtml'] : '',
			'haspageactions' => $hasPageActions,
			'pageactionshtml' => $hasPageActions ? $this->getPageActionsHtml( $data ) : '',
			'subtitle' => $data['subtitle'],
			'contenthtml' => $this->getContentHtml( $data ),
			'secondaryactionshtml' => $this->getSecondaryActionsHtml(),
			'footer' => $this->getFooterTemplateData( $data ),
			'isBeta' => $skin->getSkinOption( SkinMinerva::OPTIONS_MOBILE_BETA ),
			'tabs' => $hasTalkTabs && $skin->getSkinOption( SkinMinerva::OPTIONS_TALK_AT_TOP ) ? [
				'items' => array_values( $data['content_navigation']['namespaces'] ),
			] : false,
		];
		// begin rendering
		echo $templateParser->processTemplate( 'minerva', $templateData );
		$this->printTrail();
		?>
		</body>
		</html>
		<?php
	}
}
