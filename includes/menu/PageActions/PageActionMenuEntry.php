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

use Message;
use MediaWiki\Minerva\Menu\IMenuEntry;

class PageActionMenuEntry implements IMenuEntry {

	/**
	 * Menu entry unique identifier
	 * @var string
	 */
	private $name;
	/**
	 * A definition of a menu component
	 * An array containing HTML attributes and label for the menu entry
	 * @var array
	 */
	private $component;

	/**
	 * PageActionMenuEntry constructor.
	 * @param string $name Unique identifier
	 * @param string $href And URL menu entry points to
	 * @param string $componentClass A CSS class injected to component
	 * @param Message $message Message
	 */
	public function __construct( $name, $href, $componentClass, Message $message ) {
		$this->name = $name;
		$this->component = [
			'href' => $href,
			'class' => $componentClass,
			'text' => $message->escaped()
		];
	}

	/**
	 * Named constructor for easier class creation when we want to additionally set things
	 * like title or nodeId. Mostly a syntax sugar.
	 * @param string $name Unique identifier
	 * @param string $href And URL menu entry points to
	 * @param string $componentClass A CSS class injected to component
	 * @param Message $message Message
	 * @return self
	 */
	public static function create( $name, $href, $componentClass, Message $message ) {
		return new PageActionMenuEntry( $name, $href, $componentClass, $message );
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @inheritDoc
	 *
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

	/**
	 * Set the menu entry title
	 * @param Message $message Title message
	 * @return $this
	 */
	public function setTitle( Message $message ) {
		$this->component['title'] = $message->escaped();
		return $this;
	}

	/**
	 * Set the Menu entry ID html attribute
	 * @param string $nodeID
	 * @return $this
	 */
	public function setNodeID( $nodeID ) {
		$this->component['id'] = $nodeID;
		return $this;
	}

}
