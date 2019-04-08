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

use MediaWiki\MediaWikiServices;
use MediaWiki\Minerva\Menu\Definitions;
use MediaWiki\Minerva\Menu\Main\AdvancedBuilder;
use MediaWiki\Minerva\Menu\Main\DefaultBuilder;
use MediaWiki\Minerva\Menu\Main\Director;
use MediaWiki\Minerva\SkinOptions;
use MediaWiki\Minerva\SkinUserPageHelper;

return [
	'Minerva.ContentHandler' => function ( MediaWikiServices $services ) {
		return ContentHandler::getForTitle( RequestContext::getMain()->getTitle() );
	},
	'Minerva.Menu.MainDirector' => function ( MediaWikiServices $services ) {
		$context = RequestContext::getMain();
		/** @var SkinOptions $options */
		$options = $services->getService( 'Minerva.SkinOptions' );
		$showMobileOptions = $options->get( SkinOptions::OPTION_MOBILE_OPTIONS );
		$user = $context->getUser();
		$definitions = new Definitions( $context,  $services->getSpecialPageFactory() );
		$builder = $options->get( SkinOptions::OPTION_AMC ) ?
			new AdvancedBuilder( $showMobileOptions, $user, $definitions ) :
			new DefaultBuilder( $showMobileOptions, $user, $definitions );

		return new Director( $builder );
	},
	'Minerva.SkinUserPageHelper' => function ( MediaWikiServices $services ) {
		return new SkinUserPageHelper( RequestContext::getMain()->getTitle() );
	},
	'Minerva.SkinOptions' => function ( MediaWikiServices $services ) {
		return new SkinOptions();
	}
];
