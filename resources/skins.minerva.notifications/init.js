/*
 * This code loads the necessary modules for the notifications overlay, not to be confused
 * with the Toast notifications defined by common/toast.js.
 */
( function ( M ) {
	var badge,
		mainMenu = M.require( 'skins.minerva.scripts/mainMenu' ),
		router = require( 'mediawiki.router' ),
		mobile = M.require( 'mobile.startup' ),
		util = mobile.util,
		NotificationBadge = M.require( 'skins.minerva.notifications/NotificationBadge' ),
		overlayManager = M.require( 'skins.minerva.scripts/overlayManager' ),
		initialized = false;

	function showNotificationOverlay() {
		// eslint-disable-next-line no-jquery/no-global-selector
		var $pageCenter = $( '#mw-mf-page-center' ),
			overlay = mobile.notifications.overlay( badge.setCount.bind( badge ),
				badge.markAsSeen.bind( badge ) );
		mainMenu.openNavigationDrawer( 'secondary' );
		overlay.on( 'hide', function () {
			mainMenu.closeNavigationDrawers();
			$pageCenter.off( '.secondary' );
		} );

		$pageCenter.one( 'click.secondary', function () {
			router.back();
		} );
		return overlay;
	}

	// Once the DOM is loaded hijack the notifications button to display an overlay rather
	// than linking to Special:Notifications.
	util.docReady( function () {
		badge = new NotificationBadge( {
			onClick: function () {
				router.navigate( '#/notifications' );
				// Important that we also prevent propagation to avoid interference
				// with events that may
				// be binded on #mw-mf-page-center that close overlay
				return false;
			},
			// eslint-disable-next-line no-jquery/no-global-selector
			el: $( '#secondary-button.user-button' ).parent()
		} );
		overlayManager.add( /^\/notifications$/, showNotificationOverlay );

		/**
		 * Adds a filter button to the UI inside notificationsInboxWidget
		 * @method
		 * @ignore
		 */
		function addFilterButton() {
			// Create filter button once the notifications overlay has been loaded
			var filterStatusButton = new OO.ui.ButtonWidget(
				{
					href: '#/notifications-filter',
					classes: [ 'mw-echo-ui-notificationsInboxWidget-main-toolbar-nav-filter-placeholder' ],
					icon: 'funnel',
					label: mw.msg( 'mobile-frontend-notifications-filter' )
				} );

			// eslint-disable-next-line no-jquery/no-global-selector
			$( '.mw-echo-ui-notificationsInboxWidget-cell-placeholder' ).append(
				$( '<div>' )
					.addClass( 'mw-echo-ui-notificationsInboxWidget-main-toolbar-nav-filter' )
					.addClass( 'mw-echo-ui-notificationsInboxWidget-cell' )
					.append( filterStatusButton.$element )
			);
		}

		// This code will currently only be invoked on Special:Notifications
		// The code is bundled here since it makes use of loadModuleScript. This also allows
		// the possibility of invoking the filter from outside the Special page in future.
		// Once the 'ext.echo.special.onInitialize' hook has fired, load notification filter.
		mw.hook( 'ext.echo.special.onInitialize' ).add( function () {
			// The 'ext.echo.special.onInitialize' hook is fired whenever special page notification
			// changes display on click of a filter.
			// Hence the hook is restricted from firing more than once.
			if ( initialized ) {
				return;
			}

			// Load the notification filter overlay
			mw.loader.using( 'mobile.notifications.overlay' ).then( function () {
				// eslint-disable-next-line no-jquery/no-global-selector
				var $crossWikiUnreadFilter = $( '.mw-echo-ui-crossWikiUnreadFilterWidget' ),
					// eslint-disable-next-line no-jquery/no-global-selector
					$notifReadState = $( '.mw-echo-ui-notificationsInboxWidget-main-toolbar-readState' ),
					NotificationsFilterOverlay = M.require( 'mobile.notifications.overlay/NotificationsFilterOverlay' );

				// setup the filter button (now we have OOjs UI)
				addFilterButton();

				// setup route
				overlayManager.add( /^\/notifications-filter$/, function () {
					mainMenu.openNavigationDrawer( 'secondary' );
					return new NotificationsFilterOverlay( {
						$notifReadState: $notifReadState,
						mainMenu: mainMenu,
						$crossWikiUnreadFilter: $crossWikiUnreadFilter
					} );
				} );
			} );
			initialized = true;
		} );
	} );

}( mw.mobileFrontend ) );
