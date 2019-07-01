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

namespace MediaWiki\Minerva\Menu\Entries;

use MessageLocalizer;
use MinervaUI;
use SpecialPage;
use Title;

/**
 * Model for a menu entry that represents a language selector for current title
 */
class LanguageSelectorEntry implements IMenuEntry {

	/**
	 * @var MessageLocalizer
	 */
	private $messageLocalizer;
	/**
	 * @var Title
	 */
	private $title;
	/**
	 * @var bool
	 */
	private $doesPageHaveLanguages;
	/**
	 * LanguageSelectorEntry constructor.
	 * @param Title $title Current Title
	 * @param bool $doesPageHaveLanguages Whether the page is also available in other
	 * languages or variants
	 * @param MessageLocalizer $messageLocalizer Used for translation texts
	 *
	 */
	public function __construct(
		Title $title,
		$doesPageHaveLanguages,
		MessageLocalizer $messageLocalizer
	) {
		$this->title = $title;
		$this->doesPageHaveLanguages = $doesPageHaveLanguages;
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return 'language-selector';
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
		$switcherLink = false;
		$switcherClasses = ' language-selector';

		if ( $this->doesPageHaveLanguages ) {
			$switcherLink = SpecialPage::getTitleFor(
				'MobileLanguages',
				$this->title
			)->getLocalURL();
		} else {
			$switcherClasses .= ' disabled';
		}
		$iconClass = MinervaUI::iconClass( 'language-switcher', 'element', $switcherClasses );

		return [
			[
				'href' => $switcherLink,
				'class' => $iconClass,
				'text' => $this->messageLocalizer->msg( 'mobile-frontend-language-article-heading' ),
				'title' => $this->messageLocalizer->msg( 'mobile-frontend-language-article-heading' )
			]

		];
	}
}
