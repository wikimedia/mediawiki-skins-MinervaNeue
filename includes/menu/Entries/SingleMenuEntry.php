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

use MinervaUI;

/**
 * Model for a simple menu entries with label and icon
 */
class SingleMenuEntry implements IMenuEntry {
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var array
	 */
	private $component;

	/**
	 * Create a simple menu element with one component
	 *
	 * @param string $name An unique menu element identifier
	 * @param string $text Text to show on menu element
	 * @param string $url URL menu element points to
	 * @param bool|string $trackClicks Should clicks be tracked. To override the tracking code
	 * pass the tracking code as string
	 * @param string|null $iconName The Icon name, if not defined, the $name will be used
	 * @param string $iconType 'before' or 'element'
	 * @param string $classes Additional CSS class names.
	 */
	public function __construct(
		$name, $text, $url, $trackClicks = true, $iconName = null, $iconType = 'before', $classes = ''
	) {
		$this->name = $name;
		$this->component = [
			'text' => $text,
			'href' => $url,
			'class' => MinervaUI::iconClass( $iconName ?? $name, $iconType, $classes ),
		];
		if ( $trackClicks !== false ) {
			$eventName = $trackClicks === true ? $name : $trackClicks;
			if ( $eventName ) {
				$this->component['data-event-name'] = 'menu.' . $eventName;
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return $this->name;
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
		return [ $this->component ];
	}
}
