<?php

/*
 * ====================================================
 * $Author: julianm $
 * $Revision: 91 $
 * $Date: 2013-02-16 23:07:38 +0000 (Sat, 16 Feb 2013) $
 * $HeadURL: https://bravo.netlink-dns.com/svn/wordpress/plugins/wp-photo-galleries/settings.php $
 * ====================================================
 */

?><div class="gallery_settings_control">

	<fieldset><legend>Slideshow Player</legend>

	<!--<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras orci lorem, bibendum in pharetra ac, luctus ut mauris. Phasellus dapibus elit et justo malesuada eget <code>functions.php</code>.</p>-->

<?php

foreach ( $fields as $field ) :

	$id = $this->metaname . '_' . $field['key'];
	$name = $this->metaname . '[' . $field['key'] . ']';

	// get current post meta data
	$meta = get_post_meta( $post->ID, $id, true );
	echo '<p>', "\n", '<label for="', $id, '">', $field['label'], '</label>', "\n";
	
	switch ( $field['type'] ) :
	
		case 'text' :
		
			echo '<input ', $field['class'] ? 'class="' . $field['class'] . '" ' : '', 'type="text" name="', $name, '" id="', $id, '" value="', $meta ? $meta : $field['default'], '" size="30" style="width:97%" />', $field['desc'] ? ' <span>(' . $field['desc'] . ')</span>' : '', "\n\n";
			
		break;
		
		case 'textarea' :
		
			echo '<textarea name="', $name, '" id="', $id, '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['default'], '</textarea>', $field['desc'] ? ' <span>(' . $field['desc'] . ')</span>' : '', "\n\n";
			
		break;
		
		case 'select' :
		
			echo '<select ', $field['class'] ? 'class="' . $field['class'] . '" ' : '', 'name="', $name, '" id="', $id, '">', "\n";
			foreach ( $field['options'] as $option ) :
				echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>', "\n";
			endforeach;
			echo '</select>', $field['desc'] ? ' <span>(' . $field['desc'] . ')</span>' : '', "\n\n";
			
		break;
		
		case 'radio' :
		
			foreach ( $field['options'] as $option ) :
				echo '<input type="radio" name="', $name, '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'], "\n\n";
			endforeach;
			
		break;
		
		case 'checkbox' :
		
			echo '<input type="checkbox" name="', $name, '" id="', $id, '"', $meta ? ' checked="checked"' : '', ' />', "\n\n";
			
		break;
		
	endswitch;
endforeach;

echo '<input type="hidden" name="_gallery_nonce" value="', wp_create_nonce( '_gallery_settings_nonce' ), '" />', "\n</p>\n";
				
?>

	</fieldset>

</div>