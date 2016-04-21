<?php
/*
Plugin Name: Photo Slideshows
Plugin URI: http://www.netlink.ie/
Description: WP Photo Slideshows - Upload and activate.
Author: NETLINK IT SERVICES
Version: 1.7
Author URI: http://www.netlink.ie/
*/

/*
 * ====================================================
 * $Author: julianm $
 * $Revision: 117 $
 * $Date: 2013-04-27 03:44:30 +0100 (Sat, 27 Apr 2013) $
 * $HeadURL: https://bravo.netlink-dns.com/svn/wordpress/plugins/wp-photo-galleries/wp-photo-galleries.php $
 * ====================================================
 */


$update_class = WP_PLUGIN_DIR . '/private-plugin-updater/update.class.php';

if ( is_file( $update_class ) )
{
	require_once $update_class;
	if ( class_exists( 'PrivatePluginUpdater' ) )
	{
		$updater = new PrivatePluginUpdater( __FILE__, NULL, 1 );
	}
}

include dirname( __FILE__ ) . '/plugin.php';

//register_activation_hook( __FILE__, array( $wp_photo_galleries, 'activate' ) );

?>