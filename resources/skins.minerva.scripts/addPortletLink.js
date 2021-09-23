/**
 * @param {jQuery.Object} $item The added list item, or null if no element was added.
 * @return {Object} of arrays with mandatory class names for list item elements.
 */
function getClassesForItem( $item ) {
	// eslint-disable-next-line no-jquery/no-class-state
	var isToggleList = $item.parent().hasClass( 'toggle-list__list' );
	if ( isToggleList ) {
		return {
			li: [ 'toggle-list-item' ],
			span: [ 'toggle-list-item__label' ],
			a: [ 'toggle-list-item__anchor' ]
		};
	} else {
		return {
			li: [],
			span: [],
			a: []
		};
	}
}

/**
 * Insert icon into the portlet link.
 *
 * @param {jQuery.Object} $link
 * @param {string} id for icon
 */
function insertIcon( $link, id ) {
	var icon = document.createElement( 'span' );
	icon.setAttribute( 'class', 'mw-ui-icon mw-ui-icon-portletlink-' + id );
	$link.prepend( '&nbsp;' );
	$link.prepend( icon );
}

/**
 * @param {HTMLElement|null} listItem The added list item, or null if no element was added.
 * @param {Object} data
 */
function hookHandler( listItem, data ) {
	var $item, $a, classes,
		id = data.id || 'unknowngadget';

	if ( listItem ) {
		$item = $( listItem );
		classes = getClassesForItem( $item );
		$item.addClass( classes.li );
		$a = $item.find( 'a' );
		$a.addClass( classes.a );
		$item.find( 'a > span' ).addClass( classes.span );
		insertIcon( $a, id );
	}
}

module.exports = {
	hookHandler: hookHandler
};
