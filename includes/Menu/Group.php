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

namespace MediaWiki\Minerva\Menu;

use DomainException;
use MediaWiki\Minerva\Menu\Entries\IMenuEntry;

/**
 * Model for a menu that can be presented in a skin.
 */
final class Group {
	/** @var IMenuEntry[] */
	private array $entries = [];
	private string $id;

	/**
	 * @param string $id of the menu defaults to null (optional)
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * Get the identifier for the group
	 *
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * Return entries count
	 *
	 * @return bool
	 */
	public function hasEntries(): bool {
		return count( $this->entries ) > 0;
	}

	/**
	 * Get all entries represented as plain old PHP arrays.
	 *
	 * @return array
	 */
	public function getEntries(): array {
		$entryPresenter = static function ( IMenuEntry $entry ) {
			$result = [
				'name' => $entry->getName(),
				'components' => $entry->getComponents(),
			];
			$classes = $entry->getCSSClasses();
			if ( $classes ) {
				$result[ 'class' ] = implode( ' ', $classes );
			}

			return $result;
		};

		return array_map( $entryPresenter, $this->entries );
	}

	/**
	 * Helper method to verify that the $name of entry is unique (do not exists
	 * in current Group )
	 * @param string $name
	 * @throws DomainException When the entry already exists
	 */
	private function throwIfNotUnique( string $name ): void {
		try {
			$this->search( $name );
		} catch ( DomainException ) {
			return;
		}
		throw new DomainException( "The \"{$name}\" entry already exists." );
	}

	/**
	 * Prepend new menu entry
	 * @param IMenuEntry $entry
	 * @throws DomainException When the entry already exists
	 */
	public function prependEntry( IMenuEntry $entry ): void {
		$this->throwIfNotUnique( $entry->getName() );
		array_unshift( $this->entries, $entry );
	}

	/**
	 * Insert new menu entry
	 * @param IMenuEntry $entry
	 * @throws DomainException When the entry already exists
	 */
	public function insertEntry( IMenuEntry $entry ): void {
		$this->throwIfNotUnique( $entry->getName() );
		$this->entries[] = $entry;
	}

	/**
	 * Searches for a menu entry by name.
	 *
	 * @param string $name
	 * @return int If the menu entry exists, then the 0-based index of the entry; otherwise, -1
	 * @throws DomainException
	 */
	private function search( string $name ): int {
		$count = count( $this->entries );

		for ( $i = 0; $i < $count; ++$i ) {
			if ( $this->entries[$i]->getName() === $name ) {
				return $i;
			}
		}
		throw new DomainException( "The \"{$name}\" entry doesn't exist." );
	}

	/**
	 * @param string $targetName
	 * @return IMenuEntry
	 * @throws DomainException
	 */
	public function getEntryByName( string $targetName ): IMenuEntry {
		$index = $this->search( $targetName );
		return $this->entries[$index];
	}

	/**
	 * Serialize the group for use in a template
	 * @return array{entries:array,id:string}
	 */
	public function serialize(): array {
		return [
			'entries' => $this->getEntries(),
			'id' => $this->getId(),
		];
	}
}
