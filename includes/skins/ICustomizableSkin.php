<?php

interface ICustomizableSkin {
	/**
	 * override an existing option or options with new values
	 * @param array $options
	 */
	public function setSkinOptions( $options );

	/**
	 * Return whether a skin option is truthy
	 * @param string $key
	 * @return bool
	 */
	public function getSkinOption( $key );
}
