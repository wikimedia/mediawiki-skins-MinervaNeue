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
	const OPTION_OVERFLOW_SUBMENU = 'overflowSubmenu';
	const OPTION_TABS_ON_SPECIALS = 'tabsOnSpecials';

	/** @var array skin specific options */
	private $skinOptions = [
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
		self::OPTION_OVERFLOW_SUBMENU => false,
	];

	/**
	 * override an existing option or options with new values
	 * @param array $options
	 */
	public function setMultiple( array $options ) {
		$this->skinOptions = array_merge( $this->skinOptions, $options );
	}

	/**
	 * Return whether a skin option is truthy. Should be one of self:OPTION_* flags
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
