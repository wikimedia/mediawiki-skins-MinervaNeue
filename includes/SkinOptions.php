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
namespace MediaWiki\Minerva;

/**
 * A wrapper for all available Skin options.
 */
final class SkinOptions {

	const MOBILE_OPTIONS = 'mobileOptionsLink';
	const AMC_MODE = 'amc';
	const CATEGORIES = 'categories';
	const BACK_TO_TOP = 'backToTop';
	const PAGE_ISSUES = 'pageIssues';
	const SHARE_BUTTON = 'shareButton';
	const TOGGLING = 'toggling';
	const BETA_MODE = 'beta';
	const TALK_AT_TOP = 'talkAtTop';
	const HISTORY_IN_PAGE_ACTIONS = 'historyInPageActions';
	const TOOLBAR_SUBMENU = 'overflowSubmenu';
	const TABS_ON_SPECIALS = 'tabsOnSpecials';

	/** @var array skin specific options, initialized with default values */
	private $skinOptions = [
		// Defaults to true for desktop mode.
		self::AMC_MODE => true,
		self::BETA_MODE => false,
		/**
		 * Whether the main menu should include a link to
		 * Special:Preferences of Special:MobileOptions
		 */
		self::MOBILE_OPTIONS => false,
		/** Whether a categories button should appear at the bottom of the skin. */
		self::CATEGORIES => false,
		/** Whether a back to top button appears at the bottom of the view page */
		self::BACK_TO_TOP => false,
		/** Whether a share button should appear in icons section */
		self::SHARE_BUTTON => false,
		/** Whether sections can be collapsed (requires MobileFrontend and MobileFormatter) */
		self::TOGGLING => false,
		self::PAGE_ISSUES => false,
		self::TALK_AT_TOP => false,
		self::HISTORY_IN_PAGE_ACTIONS => false,
		self::TOOLBAR_SUBMENU => false,
		/** Whether to show tabs on special pages */
		self::TABS_ON_SPECIALS => false,
	];

	/**
	 * override an existing option or options with new values
	 * @param array $options
	 */
	public function setMultiple( array $options ) {
		foreach ( $options as $option => $value ) {
			if ( !array_key_exists( $option, $this->skinOptions ) ) {
				throw new \OutOfBoundsException( "SkinOption $option is not defined" );
			}
		}
		$this->skinOptions = array_merge( $this->skinOptions, $options );
	}

	/**
	 * Return whether a skin option is truthy. Should be one of self:* constants
	 * @param string $key
	 * @return bool
	 */
	public function get( $key ) {
		if ( !array_key_exists( $key, $this->skinOptions ) ) {
			throw new \OutOfBoundsException( "SkinOption $key doesn't exist" );
		}
		return $this->skinOptions[$key];
	}

	/**
	 * Get all skin options
	 * @return array
	 */
	public function getAll() {
		return $this->skinOptions;
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

}
