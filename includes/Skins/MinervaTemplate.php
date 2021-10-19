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
use MediaWiki\Minerva\SkinOptions;

/**
 * Extended Template class of BaseTemplate for mobile devices
 */
class MinervaTemplate extends BaseTemplate {
	/**
	 * Start render the page in template
	 * @deprecated please migrate code here to SkinMinerva::getTemplateData
	 * @return array
	 */
	public function execute() {
		Hooks::run( 'MinervaPreRender', [ $this ], '1.35' );
		return $this->getTemplateData();
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
				'historyIconClass' => MinervaUI::iconClass(
					'history-base20', 'mw-ui-icon-small', '', 'wikimedia'
				),
				'arrowIconClass' => MinervaUI::iconClass(
					'expand-gray', 'small',
					'mf-mw-ui-icon-rotate-anti-clockwise indicator',
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
	 * @return array
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
				// @phan-suppress-next-line PhanTypeMismatchArgument
				$html .= Html::element( 'a', $el['attributes'], $el['label'] );
			}
		}

		return $html;
	}

	/**
	 * Gets the main menu HTML.
	 * @param array $data Data used to build the page
	 * @return string
	 */
	protected function getMainMenuData( $data ) {
		return $data['mainMenu']['items'];
	}

	/**
	 * Render the entire page
	 * @deprecated please migrate code here to SkinMinerva::getTemplateData
	 * @return array
	 */
	protected function getTemplateData() {
		$data = $this->data;
		$skinOptions = MediaWikiServices::getInstance()->getService( 'Minerva.SkinOptions' );
		$templateParser = new TemplateParser( __DIR__ );

		// prepare template data
		return [
			'banners' => $data['banners'],
			'isAnon' => $data['username'] === null,
			'userNotificationsHTML' => $data['userNotificationsHTML'] ?? '',
			'data-main-menu' => $this->getMainMenuData( $data ),
			'taglinehtml' => $data['taglinehtml'],
			'headinghtml' => $data['headinghtml'] ?? '',
			'postheadinghtml' => $data['postheadinghtml'] ?? '',
			'userMenuHTML' => $data['userMenuHTML'],
			'secondaryactionshtml' => $this->getSecondaryActionsHtml(),

			'html-minerva-lastmodified' => $this->getHistoryLinkHtml( $data ),
			// Note mobile-license is only available on the mobile skin. It is outputted as part of
			// footer-info on desktop hence the conditional check.
			'html-minerva-license' => ExtensionRegistry::getInstance()->isLoaded( 'MobileFrontend' ) ?
				MobileFrontendSkinHooks::getLicenseText( $this->getSkin() ) : '',

			'isBeta' => $skinOptions->get( SkinOptions::BETA_MODE ),
		];
	}
}
