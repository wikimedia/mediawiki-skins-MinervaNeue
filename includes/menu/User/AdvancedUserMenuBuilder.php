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
namespace MediaWiki\Minerva\Menu\User;

use Hooks;
use IContextSource;
use MediaWiki\Minerva\Menu\Definitions;
use MediaWiki\Minerva\Menu\Entries\ProfileMenuEntry;
use MediaWiki\Minerva\Menu\Entries\SingleMenuEntry;
use MediaWiki\Minerva\Menu\Group;
use User;

/**
 * Logged-in, advanced Mobile Contributions user menu config generator.
 */
final class AdvancedUserMenuBuilder implements IUserMenuBuilder {
	/**
	 * @var IContextSource
	 */
	private $context;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var Definitions
	 */
	private $definitions;

	/**
	 * @var array|null
	 */
	private $sandbox;

	/**
	 * @param IContextSource $context
	 * @param User $user
	 * @param Definitions $definitions A menu items definitions set
	 * @param array|null $sandbox
	 */
	public function __construct(
		IContextSource $context, User $user, Definitions $definitions, $sandbox
	) {
		$this->context = $context;
		$this->user = $user;
		$this->definitions = $definitions;
		$this->sandbox = $sandbox;
	}

	/**
	 * @inheritDoc
	 * @return Group
	 */
	public function getGroup(): Group {
		$group = new Group();
		$group->insertEntry( new ProfileMenuEntry( $this->user ) );
		$group->insertEntry( new SingleMenuEntry(
			'userTalk',
			$this->context->msg( 'mobile-frontend-user-page-talk' )->escaped(),
			$this->user->getUserPage()->getTalkPage()->getLocalURL(),
			true,
			null,
			'before',
			'wikimedia-ui-userTalk-base20'
		) );
		if ( $this->sandbox ) {
			$group->insertEntry( new SingleMenuEntry(
				'userSandbox',
				$this->sandbox['text'],
				$this->sandbox['href']
			) );
		}
		$this->definitions->insertWatchlistMenuItem( $group );
		$this->definitions->insertContributionsMenuItem( $group );
		if ( $this->user->isAnon() ) {
			$this->definitions->insertLogInMenuItem( $group );
		} else {
			$this->definitions->insertLogOutMenuItem( $group );
		}
		Hooks::run( 'MobileMenu', [ 'user', &$group ] );
		return $group;
	}
}
