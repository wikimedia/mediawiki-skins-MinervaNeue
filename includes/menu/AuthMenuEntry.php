<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace MediaWiki\Minerva\Menu;

use MessageLocalizer;
use MinervaUI;
use SpecialPage;
use Title;
use User;
use WebRequest;

/**
 * Model for a menu entry that represents log-in / profile+logout pair of links
 */
class AuthMenuEntry implements IMenuEntry {

	/**
	 * Default tracking code for clicks on profile menu link
	 */
	const DEFAULT_PROFILE_TRACKING_CODE = 'profile';
	/**
	 * @var User
	 */
	private $user;
	/**
	 * @var WebRequest
	 */
	private $request;
	/**
	 * @var Title
	 */
	private $title;

	/**
	 * @var MessageLocalizer
	 */
	private $messageLocalizer;

	/**
	 * Code used to track clicks on the link to profile page
	 * @var string
	 */
	private $profileTrackingCode = 'profile';

	/**
	 * Custom profile URL, can be used to override where the profile link href
	 * @var string|null
	 */
	private $customProfileURL = null;

	/**
	 * Custom profile label, can be used to override the profile label
	 * @var string|null
	 */
	private $customProfileLabel = null;

	/**
	 * Initialize the Auth menu entry
	 *
	 * @param User $user Currently logged in user/anon
	 * @param WebRequest $request Request to load the returnToQuery values
	 * @param MessageLocalizer $messageLocalizer used for text translation
	 * @param Title|null $currentTitle The current title, will be used as returnTo
	 */
	public function __construct( User $user, WebRequest $request,
		\MessageLocalizer $messageLocalizer, Title $currentTitle = null ) {
		$this->user = $user;
		$this->request = $request;
		$this->title = $currentTitle;
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return 'auth';
	}

	/**
	 * Override the href for the profile component for logged in users
	 * @param string $customURL A new href for profile entry
	 * @param string|null $customLabel A new label for profile entry. Null if you don't want to
	 * override it
	 * @param string $trackingCode new tracking code
	 */
	public function overrideProfileURL( $customURL, $customLabel = null,
		$trackingCode = self::DEFAULT_PROFILE_TRACKING_CODE ) {
		$this->customProfileURL = $customURL;
		$this->customProfileLabel = $customLabel;
		$this->profileTrackingCode = $trackingCode;
	}

	/**
	 * @inheritDoc
	 */
	public function getCSSClasses(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getComponents(): array {
		$authLinksQuery = [];
		$returnToQuery = $this->getReturnToQuery();

		// Don't ever redirect back to the login page (bug 55379)
		if ( $this->title && !$this->title->isSpecial( 'Userlogin' ) ) {
			$authLinksQuery[ 'returnto' ] = $this->title->getPrefixedText();
		}

		return $this->user->isLoggedIn()
			? $this->buildComponentsForLoggedIn( $returnToQuery, $authLinksQuery )
			: $this->buildComponentsForAnon( $returnToQuery, $authLinksQuery );
	}

	/**
	 * Retrieve current query parameters from Request object so system can pass those
	 * to the Login/logout links
	 * Some parameters are disabled (like title), as the returnto will be replaced with
	 * the current page.
	 * @return array
	 */
	private function getReturnToQuery(): array {
		$returnToQuery = [];

		if ( !$this->request->wasPosted() ) {
			$returnToQuery = $this->request->getValues();
			unset( $returnToQuery['title'] );
			unset( $returnToQuery['returnto'] );
			unset( $returnToQuery['returntoquery'] );
		}
		return $returnToQuery;
	}

	/**
	 * @param array $returnToQuery
	 * @param array $authLinksQuery
	 * @return array
	 * @throws \MWException
	 */
	private function buildComponentsForLoggedIn( array $returnToQuery, array $authLinksQuery ): array {
		if ( !empty( $returnToQuery ) ) {
			$authLinksQuery['returntoquery'] = wfArrayToCgi( $returnToQuery );
		}
		$authLinksQuery['logoutToken'] = $this->user->getEditToken( 'logoutToken', $this->request );

		$logoutURL = SpecialPage::getTitleFor( 'Userlogout' )->getLocalURL( $authLinksQuery );
		$username = $this->user->getName();
		$profileUrl = $this->customProfileURL ??
					  Title::newFromText( $username, NS_USER )->getLocalURL();
		$profileLabel = $this->customProfileLabel ?? $username;

		return [
			[
				'text' => $profileLabel,
				'href' => $profileUrl,
				'class' => MinervaUI::iconClass( 'profile', 'before',
					'truncated-text primary-action' ),
				'data-event-name' => $this->profileTrackingCode
			],
			[
				'text' => $this->messageLocalizer->msg( 'mobile-frontend-main-menu-logout' )->escaped(),
				'href' => $logoutURL,
				'class' => MinervaUI::iconClass( 'logout', 'element',
					'truncated-text secondary-action' ),
				'data-event-name' => 'logout'
			]
		];
	}

	/**
	 * @param array $returnToQuery
	 * @param $authLinksQuery
	 * @return array
	 */
	private function buildComponentsForAnon( array $returnToQuery, $authLinksQuery ): array {
		// unset campaign on login link so as not to interfere with A/B tests
		unset( $returnToQuery['campaign'] );
		if ( !empty( $returnToQuery ) ) {
			$authLinksQuery['returntoquery'] = wfArrayToCgi( $returnToQuery );
		}

		return [
			'text' => $this->messageLocalizer->msg( 'mobile-frontend-main-menu-login' )->escaped(),
			'href' => SpecialPage::getTitleFor( 'Userlogin' )->getLocalURL( $authLinksQuery ),
			'class' => MinervaUI::iconClass( 'login', 'before' ),
			'data-event-name' => 'login'
		];
	}

}
