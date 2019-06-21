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
use MediaWiki\Minerva\Menu\Main as MainMenu;
use MediaWiki\Minerva\Menu\PageActions as PageActionsMenu;
use MediaWiki\Minerva\Permissions\IMinervaPagePermissions;
use MediaWiki\Minerva\Permissions\MinervaPagePermissions;
use MediaWiki\Minerva\Permissions\MinervaNoPagePermissions;
use MediaWiki\Minerva\SkinOptions;
use MediaWiki\Minerva\SkinUserPageHelper;

return [
	'Minerva.Menu.MainDirector' => function ( MediaWikiServices $services ): MainMenu\Director {
		$context = RequestContext::getMain();
		/** @var SkinOptions $options */
		$options = $services->getService( 'Minerva.SkinOptions' );
		$showMobileOptions = $options->get( SkinOptions::OPTION_MOBILE_OPTIONS );
		$user = $context->getUser();
		$definitions = new Definitions( $context,  $services->getSpecialPageFactory() );
		$builder = $options->get( SkinOptions::OPTION_AMC ) ?
			new MainMenu\AdvancedBuilder( $showMobileOptions, $user, $definitions ) :
			new MainMenu\DefaultBuilder( $showMobileOptions, $user, $definitions );

		return new MainMenu\Director( $builder );
	},
	'Minerva.Menu.PageActionsDirector' =>
		function ( MediaWikiServices $services ): PageActionsMenu\PageActionsDirector {
		/**
		 * @var SkinOptions $skinOptions
		 * @var SkinMinerva $skin
		 * @var SkinUserPageHelper $userPageHelper
		 */
		$skinOptions = $services->getService( 'Minerva.SkinOptions' );
		$context = RequestContext::getMain();
		$userPageHelper = $services->getService( 'Minerva.SkinUserPageHelper' );
		$toolbarBuilder = new PageActionsMenu\ToolbarBuilder(
			$context->getTitle(),
			$context->getUser(),
			$context,
			$services->getPermissionManager(),
			$services->getService( 'Minerva.Permissions' )
		);
		if ( $skinOptions->get( SkinOptions::OPTION_OVERFLOW_SUBMENU ) ) {
			 $overflowBuilder = $userPageHelper->isUserPage() ?
				 new PageActionsMenu\UserNamespaceOverflowBuilder(
					 $context,
					 $userPageHelper
				 ) :
				 new PageActionsMenu\DefaultOverflowBuilder(
					 $context
				 );
		} else {
			$overflowBuilder = new PageActionsMenu\EmptyOverflowBuilder();
		}

		return new PageActionsMenu\PageActionsDirector(
			$toolbarBuilder,
			$overflowBuilder,
			$context
		);
	 },
	'Minerva.SkinUserPageHelper' => function (): SkinUserPageHelper {
		return new SkinUserPageHelper( RequestContext::getMain()->getTitle() );
	},
	'Minerva.SkinOptions' => function (): SkinOptions {
		return new SkinOptions();
	},
	'Minerva.Permissions' => function ( MediaWikiServices $services ): IMinervaPagePermissions {
		$context = RequestContext::getMain();
		$title = $context->getTitle();

		// Title may be undefined in certain contexts (T179833)
		if ( $title ) {
			$contentHandler = ContentHandler::getForTitle( $title );

			return new MinervaPagePermissions(
				$context->getTitle(),
				$context->getConfig(),
				$context->getUser(),
				$context->getOutput(),
				$services->getService( 'Minerva.SkinOptions' ),
				$contentHandler
			);
		} else {
			return new MinervaNoPagePermissions();
		}
	}
];
