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

use Hooks;
use MediaWiki\Minerva\Menu\Group;
use MediaWiki\Minerva\SkinUserPageHelper;
use MessageLocalizer;
use MinervaUI;
use MWException;
use SpecialPage;
use User;

class UserNamespaceOverflowBuilder implements IOverflowBuilder {

	/**
	 * @var MessageLocalizer
	 */
	private $messageLocalizer;

	/**
	 * @var User|null
	 */
	private $pageUser;

	/**
	 * Initialize the overflow menu visible on the User namespace
	 * @param MessageLocalizer $msgLocalizer
	 * @param SkinUserPageHelper $userPageHelper
	 */
	public function __construct( MessageLocalizer $msgLocalizer, SkinUserPageHelper $userPageHelper ) {
		$this->messageLocalizer = $msgLocalizer;
		$this->pageUser = $userPageHelper->getPageUser();
	}

	/**
	 * @inheritDoc
	 * @throws MWException
	 */
	public function getGroup( array $navUrls ) {
		$group = new Group();
		$group->insertEntry( $this->buildEntry(
			'uploads', 'upload', SpecialPage::getTitleFor( 'Uploads', $this->pageUser )->getLocalURL()
		) );

		$possibleEntries = array_filter( [
			$this->buildEntryFromNav( 'user-rights', 'userAvatar', 'userrights', $navUrls ),
			$this->buildEntryFromNav( 'logs', 'listBullet', 'log', $navUrls ),
			$this->buildEntryFromNav( 'info', 'info', 'info', $navUrls ),
			$this->buildEntryFromNav( 'permalink', 'link', 'permalink', $navUrls ),
			$this->buildEntryFromNav( 'backlinks', 'articleRedirect', 'whatlinkshere', $navUrls )
		] );

		foreach ( $possibleEntries as $menuEntry ) {
			$group->insertEntry( $menuEntry );
		}
		Hooks::run( 'MobileMenu', [ 'pageactions.overflow', &$group ] );
		return $group;
	}

	/**
	 * @param string $name
	 * @param string $icon Wikimedia UI icon name.
	 * @param string $navUrlKey
	 * @param array $navUrls A set of navigation urls build by SkinTemplate::buildNavUrls()
	 * @return PageActionMenuEntry|null
	 */
	private function buildEntryFromNav( $name, $icon, $navUrlKey, array $navUrls ) {
		return $this->buildEntry( $name, $icon, $navUrls[$navUrlKey]['href'] ?? null );
	}

	/**
	 * @param string $name
	 * @param string $icon Wikimedia UI icon name.
	 * @param string|null $href
	 * @return PageActionMenuEntry|null
	 */
	private function buildEntry( $name, $icon, $href ) {
		return $href ?
			new PageActionMenuEntry(
				'page-actions-overflow-' . $name,
				$href,
				MinervaUI::iconClass( '', 'before', 'wikimedia-ui-' . $icon . '-base20' ),
				$this->messageLocalizer->msg( 'minerva-page-actions-' . $name )
			) : null;
	}
}
