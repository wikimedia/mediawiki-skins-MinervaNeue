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
use MediaWiki\Minerva\LanguagesHelper;
use MediaWiki\Minerva\Menu\Definitions;
use MediaWiki\Minerva\Menu\Main\MainMenuDirector;
use MediaWiki\Minerva\Menu\Main\DefaultMainMenuBuilder;
use MediaWiki\Minerva\Menu\Main\AdvancedMainMenuBuilder;
use MediaWiki\Minerva\Menu\PageActions as PageActionsMenu;
use MediaWiki\Minerva\Menu\User\AdvancedUserMenuBuilder;
use MediaWiki\Minerva\Menu\User\DefaultUserMenuBuilder;
use MediaWiki\Minerva\Menu\User\UserMenuDirector;
use MediaWiki\Minerva\Permissions\IMinervaPagePermissions;
use MediaWiki\Minerva\Permissions\MinervaPagePermissions;
use MediaWiki\Minerva\Permissions\MinervaNoPagePermissions;
use MediaWiki\Minerva\SkinOptions;
use MediaWiki\Minerva\SkinUserPageHelper;

return [
	'Minerva.Menu.Definitions' => function ( MediaWikiServices $services ): Definitions {
		return new Definitions( RequestContext::getMain(), $services->getSpecialPageFactory() );
	},
	'Minerva.Menu.UserMenuDirector' => function ( MediaWikiServices $services ): UserMenuDirector {
		$options = $services->getService( 'Minerva.SkinOptions' );
		$definitions = $services->getService( 'Minerva.Menu.Definitions' );

		$context = RequestContext::getMain();
		$builder = $options->get( SkinOptions::PERSONAL_MENU ) ?
			new AdvancedUserMenuBuilder(
				$context,
				$context->getUser(),
				$definitions
			) :
			new DefaultUserMenuBuilder();

		return new UserMenuDirector(
			$builder,
			$context->getSkin()
		);
	},
	'Minerva.Menu.MainDirector' => function ( MediaWikiServices $services ): MainMenuDirector {
		$context = RequestContext::getMain();
		/** @var SkinOptions $options */
		$options = $services->getService( 'Minerva.SkinOptions' );
		$definitions = $services->getService( 'Minerva.Menu.Definitions' );
		$showMobileOptions = $options->get( SkinOptions::MOBILE_OPTIONS );
		$user = $context->getUser();
		$builder = $options->get( SkinOptions::MAIN_MENU_EXPANDED ) ?
			new AdvancedMainMenuBuilder( $showMobileOptions, $user, $definitions ) :
			new DefaultMainMenuBuilder( $showMobileOptions, $user, $definitions );

		return new MainMenuDirector( $builder, $context, $services->getSpecialPageFactory() );
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
		$title = $context->getTitle();
		$user = $context->getUser();
		$userPageHelper = $services->getService( 'Minerva.SkinUserPageHelper' );
		$languagesHelper = $services->getService( 'Minerva.LanguagesHelper' );
		$toolbarBuilder = new PageActionsMenu\ToolbarBuilder(
			$title,
			$user,
			$context,
			$services->getService( 'Minerva.Permissions' ),
			$skinOptions,
			$userPageHelper,
			$languagesHelper
		);
		if ( $skinOptions->get( SkinOptions::TOOLBAR_SUBMENU ) ) {
			 $overflowBuilder = $userPageHelper->isUserPage() ?
				 new PageActionsMenu\UserNamespaceOverflowBuilder(
					 $title,
					 $context,
					 $userPageHelper,
					 $services->getService( 'Minerva.Permissions' ),
					 $languagesHelper
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
		return new SkinUserPageHelper(
			RequestContext::getMain()->getSkin()->getRelevantTitle()
		);
	},
	'Minerva.LanguagesHelper' => function (): LanguagesHelper {
		return new LanguagesHelper( RequestContext::getMain()->getOutput() );
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
				$services->getService( 'Minerva.SkinOptions' ),
				$contentHandler,
				$services->getService( 'Minerva.LanguagesHelper' ),
				$services->getPermissionManager()
			);
		} else {
			return new MinervaNoPagePermissions();
		}
	}
];
