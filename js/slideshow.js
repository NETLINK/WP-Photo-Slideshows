/*
 * ====================================================
 * $Author: julianm $
 * $Revision: 132 $
 * $Date: 2014-11-30 09:36:40 +0000 (Sun, 30 Nov 2014) $
 * $HeadURL: https://bravo.netlink-dns.com/svn/wordpress/plugins/wp-photo-galleries/js/slideshow.js $
 * ====================================================
 */



jQuery( document ).ready( function($) {
	
	var container = String( jsvars['container'] );
	
	if ( container == '' ) {
		container = "#slideshow";
	}
	console.log( jsvars['interval'] );
	
	$( container ).vegas({
		slides: imgArr,
		delay: jsvars['interval'],
		transition: 'blur',
		animation: jsvars['animation'],
		transitionDuration: jsvars['transitionDuration'],
		init: function( globalSettings ) {
			console.log( "Init" );
		},
		walk: function( index, slideSettings ) {
			console.log( "Slide index " + index + " image " + slideSettings.src );
		}
	});
	
});