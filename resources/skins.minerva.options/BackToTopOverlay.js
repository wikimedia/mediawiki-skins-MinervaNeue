( function ( M ) {
	var
		mobile = M.require( 'mobile.startup' ),
		mfExtend = mobile.mfExtend,
		View = mobile.View,
		util = mobile.util;

	/**
	 * Displays a little arrow at the bottom right of the viewport.
	 * @class BackToTopOverlay
	 * @extends View
	 * @param {Object} props
	 */
	function BackToTopOverlay( props ) {
		View.call( this,
			util.extend( {}, props, {
				className: 'backtotop',
				events: { click: 'onBackToTopClick' }
			} )
		);
	}

	mfExtend( BackToTopOverlay, View, {
		template: mw.template.get( 'skins.minerva.options', 'BackToTopOverlay.hogan' ),

		/**
		 * Show the back to top element, if it's not visible already.
		 * @memberof BackToTopOverlay
		 * @instance
		 */
		show: function () {
			this.$el.css( 'visibility', 'visible' ).addClass( 'visible' );
		},

		/**
		 * Hide the back to top element, if it's visible.
		 * @memberof BackToTopOverlay
		 * @instance
		 */
		hide: function () {
			this.$el.removeClass( 'visible' );
		},

		/**
		 * Handles the click on the "Back to top" element and scrolls back
		 * to the top smoothly.
		 * @memberof BackToTopOverlay
		 * @instance
		 */
		onBackToTopClick: function () {
			// eslint-disable-next-line no-jquery/no-global-selector
			$( 'html, body' ).animate( { scrollTop: 0 }, 400 );
		}
	} );

	M.define( 'skins.minerva.options/BackToTopOverlay', BackToTopOverlay );

}( mw.mobileFrontend ) );
