var
	mobile = mw.mobileFrontend.require( 'mobile.startup' ),
	OverlayManager = mobile.OverlayManager,
	overlayManager = OverlayManager.getSingleton();

module.exports = overlayManager;
