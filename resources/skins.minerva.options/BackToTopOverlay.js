( function ( M ) {

	var View = M.require( 'mobile.startup/View' ),
		util = M.require( 'mobile.startup/util' );

	/**
	 * Displays a little arrow at the bottom right of the viewport.
	 * @class BackToTopOverlay
	 * @extends View
	 * @param {Object} props
	 */
	function BackToTopOverlay( props ) {
		View.call( this,
			util.extend( {}, props, {
				className: 'backtotop'
			} )
		);
	}

	OO.mfExtend( BackToTopOverlay, View, {
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
			// eslint-disable-next-line jquery/no-global-selector, jquery/no-animate
			$( 'html, body' ).animate( { scrollTop: 0 }, 400 );
		}
	} );

	M.define( 'skins.minerva.options/BackToTopOverlay', BackToTopOverlay );

}( mw.mobileFrontend ) );
