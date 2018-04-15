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

namespace MediaWiki\Minerva;

/**
 * Model for a menu entry.
 */
class MenuEntry {
	private $name;
	private $isJSOnly;
	private $components;

	/**
	 * @param string $name
	 * @param bool $isJSOnly Whether the entry works without JS
	 */
	public function __construct( $name, $isJSOnly ) {
		$this->name = $name;
		$this->isJSOnly = $isJSOnly;
		$this->components = [];
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Gets whether the entry should only be shown if JavaScript is disabled
	 * in the client.
	 *
	 * @return bool
	 */
	public function isJSOnly() {
		return $this->isJSOnly;
	}

	/**
	 * @return array
	 */
	public function getComponents() {
		return $this->components;
	}

	/**
	 * Add a link to the entry.
	 *
	 * An entry can have zero or more links.
	 *
	 * @param string $label
	 * @param string $url
	 * @param string $className Any additional CSS classes that should added to the output,
	 *  separated by spaces
	 * @param array $attrs Additional data that can be associated with the component
	 *
	 * @return MenuEntry
	 */
	public function addComponent( $label, $url, $className = '', $attrs = [] ) {
		$this->components[] = [
				'text' => $label,
				'href' => $url,
				'class' => $className,
			] + $attrs;

		return $this;
	}
}
