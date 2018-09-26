( function ( M, $ ) {

	var View = M.require( 'mobile.startup/View' );

	/**
	 * Displays a little arrow at the bottom right of the viewport.
	 * @class BackToTopOverlay
	 * @extends View
	 */
	function BackToTopOverlay() {
		View.apply( this, arguments );
	}

	OO.mfExtend( BackToTopOverlay, View, {
		className: 'backtotop',
		template: mw.template.get( 'skins.minerva.options', 'BackToTopOverlay.hogan' ),
		events: $.extend( {}, View.prototype.events, {
			click: 'onBackToTopClick'
		} ),

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
			$( 'html, body' ).animate( { scrollTop: 0 }, 400 );
		}
	} );

	M.define( 'skins.minerva.options/BackToTopOverlay', BackToTopOverlay );

}( mw.mobileFrontend, jQuery ) );
