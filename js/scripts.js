// JavaScript Document

jQuery(document).ready(function() {
	
	if ( jQuery( '#_pg_gallery_settings_player' ).val() == 'JavaScript' ) {
		jQuery( '._pg_flash_settings' ).attr( 'disabled', true );
		jQuery( '._pg_javascript_settings' ).removeAttr( 'disabled' );
	}
	else {
		jQuery( '._pg_javascript_settings' ).attr( 'disabled', true );
		jQuery( '._pg_flash_settings' ).removeAttr( 'disabled' );
	}

	jQuery( '#_pg_gallery_settings_player' ).change( function() {
		if ( jQuery( this ).val() == 'JavaScript' ) {
			jQuery( '._pg_flash_settings' ).attr( 'disabled', true );
			jQuery( '._pg_javascript_settings' ).removeAttr( 'disabled' );
		}
		else {
			jQuery( '._pg_javascript_settings' ).attr( 'disabled', true );
			jQuery( '._pg_flash_settings' ).removeAttr( 'disabled' );
		}
	});
	
/*
val = jQuery('#post_ID').val();
//alert( val );
//jQuery('.image-upload').click(function() {
 //formfield = jQuery('#upload_image').attr('name');
 //tb_show('', 'media-upload.php?post_id=' + val + 'type=image&amp;TB_iframe=true');
 //return false;
//});
 
window.send_to_editor = function(html) {
 imgurl = jQuery('img',html).attr('src');
 jQuery('#upload_image').val(imgurl);
 tb_remove();
}
*/
 
});

