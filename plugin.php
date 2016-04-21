<?php

class WPPhotoSlideshows {
	
	private $load = true;
	private $uploads;	
	private $default;
	
	// Default image size
	var $width = 1024;
	var $height = 768;
	
	private $metaname = '_pg_gallery_settings';
	
	function __construct() {
		
		$this->uploads = wp_upload_dir();
		
		add_action( 'init', array( $this, 'init' ) );
		//add_action( 'wp_head', array( $this, 'head' ) );
		add_action( 'do_meta_boxes', array( $this, 'meta_boxes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_action( 'manage_gallery_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_gallery_meta' ), 1, 2 ); // Save the custom fields
		add_action( 'before_delete_post', array( $this, 'delete_associated_media' ) );
		
		add_filter( 'media_upload_tabs', array( $this, 'remove_media_tabs' ) );
		add_filter( 'post_updated_messages', array( $this, 'messages' ) );
		add_filter( 'manage_edit-gallery_columns', array( $this, 'columns' ) );
		
		add_shortcode( 'slideshow', array( $this, 'slideshow_shortcode' ) );
		
	}
		
	function init() {
		# Get default gallery
		$this->default = get_page_by_title( 'Default', 'OBJECT', 'gallery' );
		# Register Custom Post Type
		$this->register_galleries_post_type();
	}
	
	function head() {
		
		global $post;
		$id = $post->ID;
		$array = array(
			'loadParams' => 'false',
			'xmlFilePath' => $this->uploads['baseurl'] . "/galleries/gallery-params-$id.js",
		);
				
		$params = json_encode( $array );
		
		wp_localize_script( 'swfobject', 'flashvars', $array );
		wp_localize_script( 'swfobject', 'params', array(
			loadParams => 'false'
		) );
		wp_localize_script( 'swfobject', 'attributes', array() );
	}
		
	private function register_galleries_post_type() {
		
		$labels = array(
			'name' => __( 'Slideshows', 'post type general name' ),
			'singular_name' => __( 'Gallery', 'post type singular name' ),
			'add_new' => __( 'Add New', 'gallery' ),
			'add_new_item' => __( "Add New Gallery" ),
			'edit_item' => __( "Edit Gallery" ),
			'new_item' => __( "New Gallery" ),
			'view_item' => __( "View Gallery" ),
			'search_items' => __( "Search Gallery" ),
			'not_found' =>  __( 'No galleries found' ),
			'not_found_in_trash' => __( 'No galleries found in Trash' ), 
			'parent_item_colon' => '',
		);
		$args = array(
			'labels' => $labels,
			'public' => false,
			'slug' => 'galleries',
			'has_archive' => true,
			'hierarchical' => true,
			'exclude_from_search' => true,
			'show_in_menu' => true,
			'show_ui' => true,
			'publicly_queryable' => false,
			'show_ui' => true, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'page',
			//'hierarchical' => false,
			'menu_position' => NULL,
			'menu_icon' => 'dashicons-format-gallery',
			'supports' => array( 'title' ),
		);
		
		register_post_type( 'gallery', $args );
	}
	
	function enqueue() {
		
		global $post;
		$pid = $post->ID;
		//$meta = get_post_custom( $post->ID );
		$gid = get_post_meta( $pid, $this->metaname . '_gallery_id', true );
		
		// Use default gallery
		if ( empty( $gid ) )
			$gid = $this->default->ID;
		
		if ( empty( $gid ) )
		{
			$this->load = false;
			return;
		}
		
		$player = get_post_meta( $gid, $this->metaname . '_player', true );
		
		//wp_register_script( 'slick', plugins_url( '/libraries/slick/slick/slick.min.js', __FILE__ ), array( 'jquery' ) );
		
		$mtime = filemtime( __DIR__ . "/js/slideshow.js" );
		wp_register_script( 'javascript-slideshow', plugins_url( "/js/slideshow.js", __FILE__ ), array( 'jquery', 'gallery-slideshow', 'vegas' ), $mtime, true );
		
		wp_register_script( 'vegas', plugins_url( '/lib/vegas/vegas.min.js', __FILE__ ), array( 'jquery', 'gallery-slideshow' ), '2.2.0', true );
		
		$mtime = filemtime( $this->uploads['basedir'] . "/galleries/gallery-{$gid}.js" );
		wp_register_script( 'gallery-slideshow', $this->uploads['baseurl'] . "/galleries/gallery-{$gid}.js?ver={$mtime}", array( 'jquery' ), $mtime, true );
		
		//wp_enqueue_script( 'slick' );
		wp_enqueue_script( 'javascript-slideshow' );
		
		wp_enqueue_style( 'vegas', plugins_url( '/lib/vegas/vegas.min.css', __FILE__ ) );
		//wp_enqueue_style( 'slick-theme', plugins_url( '/libraries/slick/slick/slick-theme.css', __FILE__ ) );

	}

	function admin_enqueue() {
		
		$is_gallery = $this->verify_post_type();
		
		//if ( isset( $_GET['gallery-settings'] ) && $_GET['gallery-settings'] == 'false' )
		if ( $is_gallery )
		{
			wp_enqueue_style( 'wp-photo-galleries', plugins_url( '/css/media-upload.css', __FILE__) );
		//}
		
		//if ( 'gallery' == $post_type )
		//if ( $is_gallery )
		//{
			//wp_register_script( 'gallery-settings', plugins_url( '/js/scripts.js', __FILE__ ) );
			//wp_enqueue_script( 'gallery-settings' );
			wp_enqueue_script( 'media-upload' );
			add_thickbox();
		}
	}
	
	function get_cached_files( $id ) {
		
		$files = self::get_cached_file_info( $id );
		if ( is_array( $files ) ) {
			if ( $files['js']['time'] < ( time() + $this->cache_time ) ) {
				
			}
		}
		if ( $files !== false && ( $files['js'] ) ) {
			
		}
	}
	
	function meta_boxes() {
		// Image management
		add_meta_box(
			'wp_custom_attachment',
			'Gallery Images',
			array( $this, 'gallery_attachments' ),
			'gallery',
			'normal'
		);
		// Gallery settings
		add_meta_box(
			'gallery_settings',
			'Gallery Settings',
			array( $this, 'gallery_settings' ),
			'gallery',
			'normal'
		);
		// Gallery select
		add_meta_box(
			'pg_gallery_select',
			'Photo Gallery',
			array( $this, 'list_galleries' ),
			'page',
			'side',
			'core'
		);	// Define the custom attachment for pages
	}

	function gallery_attachments( $post ) {
		
		wp_nonce_field( plugin_basename( __FILE__ ), 'wp_custom_attachment_nonce' );
	
		$html = '<div class="description">';
		$html .= "\t<p>Add or edit gallery images here...</p>\n";
		$html .= "\t\t<p>\n";
		$html .= "\t\t\t" . '<a class="thickbox button" href="media-upload.php?post_id=' . $post->ID . '&amp;gallery-settings=false&amp;type=image&amp;&amp;TB_iframe=true" title="Upload images">Upload images</a>' . "\n";
		$html .= "\t\t\t" . '<a class="thickbox button" href="media-upload.php?post_id=' . $post->ID . '&amp;gallery-settings=false&amp;type=image&amp;tab=gallery&amp;TB_iframe=true" title="View images">View images</a>' . "\n";
		$html .= "\t</p>\n";
		$html .= "</div>\n";
	
		echo $html;
	
	}
	
	function gallery_settings( $post ) {
		
		$fields = array(
			/*
			array(
				'label' => 'Slideshow player',
				'desc' => 'select JavaScript or Flash player',
				'key' => 'player',
				'type' => 'select',
				'options' => array( 'JavaScript', 'Flash' ),
			),
			*/
			array(
				'label' => 'Container',
				'desc' => 'can be a class or id (e.g. .slideshow or #slideshow)',
				'key' => 'container',
				'type' => 'text',
				'class' => '_pg_javascript_settings',
				'default' => '#slideshow',
			),
			array(
				'label' => 'Interval',
				'desc' => 'in milliseconds',
				'key' => 'interval',
				'type' => 'text',
				'class' => '_pg_javascript_settings',
				'default' => 6000,
			),
			array(
				'label' => 'Transition Duration',
				'desc' => 'in milliseconds',
				'key' => 'transitionDuration',
				'type' => 'text',
				'class' => '_pg_javascript_settings',
				'default' => 1000,
			),
			/*
			array(
				'label' => 'x-Position',
				'desc' => '',
				'key' => 'x-position',
				'type' => 'select',
				'class' => '_pg_javascript_settings',
				'options' => array( 'center', 'left', 'right' ),
			),
			array(
				'label' => 'y-Position',
				'desc' => '',
				'key' => 'y-position',
				'type' => 'select',
				'class' => '_pg_javascript_settings',
				'options' => array( 'center', 'top', 'bottom' ),
			),
			*/
			array(
				'label' => 'Pan / Zoom',
				'desc' => 'choose to enable or disable pan/zoom effect',
				'key' => 'panZoom',
				'type' => 'select',
				'class' => '_pg_javascript_settings',
				'options' => array( 'On', 'Off' ),
			),
			array(
				'label' => 'Show controls',
				'desc' => 'Enable or disable controls',
				'key' => 'controls',
				'type' => 'select',
				'class' => '_pg_controls',
				'options' => array( 'Enabled', 'Disabled' ),
			),
			array(
				'label' => 'Show thumbnails',
				'desc' => 'Enable or disable thumbnails',
				'key' => 'thumbnails',
				'type' => 'select',
				'class' => '_pg_thumbnails',
				'options' => array( 'Enabled', 'Disabled' ),
			),
			array(
				'label' => 'Order by',
				'desc' => 'the order in which the images will be rotated',
				'key' => 'orderby',
				'type' => 'select',
				'class' => '_pg_javascript_settings',
				'options' => array( 'custom defined', 'title', 'random' ),
			),
			/*
			array(
				'name' => 'Textarea',
				'desc' => 'Enter big text here',
				'id' => 'textarea',
				'type' => 'textarea',
				'std' => 'Default value 2'
			),
			*/
		);
				
		include_once( plugin_dir_path( __FILE__ ) . 'settings.php' );
	}

	// Save the Metabox Data
	function save_gallery_meta( $post_id, $post )
	{
		//$this->save_meta( '_gallery_settings', $post );
		//$this->save_meta( '_gallery_id', $post );
		
		$nonce = isset( $_POST['_gallery_nonce'] ) ? $_POST['_gallery_nonce'] : NULL;
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !wp_verify_nonce( $nonce, '_gallery_settings_nonce' ) )
		{
			return $post->ID;
		}
		// Is the user allowed to edit the post or page?
		if ( !current_user_can( 'edit_post', $post->ID ) )
		{
			return $post->ID;
		}
		
		$this->save_meta( $post );
		
		if ( $post->post_type === 'gallery' )
		{
			$orderby = get_post_meta( $post_id, $this->metaname . '_orderby', true );
			
			switch( $orderby )
			{
				case 'custom defined' : $orderby = 'menu_order';
				break;
				
				case 'title' : $orderby = 'title';
				break;
				
				case 'random' : $orderby = 'rand';
				break;
				
				default : $orderby = 'menu_order';
			}
			
			$args = array(
				'post_type' => 'attachment',
				'numberposts' => -1,
				'post_status' => NULL,
				'post_mime_type' => 'image',
				'post_parent' => $post_id,
				'orderby' => $orderby,
				'order' => 'ASC',
			);
			
			$attachments = get_posts( $args );
			
			/*
			$content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
				. "<gallery>\n"
				. "\t<album>\n"
			;
			*/
			
			$imgArray = array();
			$i = 0;
			
			foreach( $attachments as $attachment ) :
				$image = wp_get_attachment_image_src( $attachment->ID, array( $this->width, $this->height ) );
				$thumb = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' );
				$imgArray[$i]['src'] = $image[0];
				//$imgArray[] = $image[0];
				//$imgArray[$i]['thumb'] = $thumb[0];
				//$imgArray[$i]['caption'] = $attachment->post_excerpt;
				$content .= "\t\t" . '<img src="' . $img[0] . '" />' . "\n";
				$i++;
			endforeach;
			
			/*
			$content .= "\t</album>\n"
				. "</gallery>\n"
			;
			*/
			
			//if ( is_writable( __DIR__ . '/xml/' ) ) :
				//$file = __DIR__ . "/xml/gallery-images-$post_id.xml";
			//else :
				$basedir = $this->uploads['basedir'];
				
				if ( !is_dir( "$basedir/galleries" ) ) {
					mkdir( "$basedir/galleries", 0755 );
				}
				
				//$file = $basedir . "/galleries/gallery-images-$post_id.xml";
				
			//endif;
			
			//$result = file_put_contents( $file, $content, LOCK_EX );
			
			//$xmlfile = $this->uploads['baseurl'] . "/galleries/gallery-images-$post_id.xml";
			
			//$basedir = $this->uploads['basedir'];
			//if ( !is_dir( "$basedir/js" ) )
				//$result  = mkdir( "$basedir/js", 0755 );
				//die( var_export( $result, true ) );
			
			$file = $basedir . "/galleries/gallery-$post_id.js";
			
			$panzoom = ( get_post_meta( $post_id, $this->metaname . '_panZoom', true ) === 'On' ) ? 'random' : '';
			//$panzoom = get_post_meta( $post_id, $this->metaname . '_panZoom', true );
			//die( var_export( $panzoom, true )) ;
			
			
			$jsvars = array(
				'container' => get_post_meta( $post_id, $this->metaname . '_container' ),
				'transition' => get_post_meta( $post_id, $this->metaname . '_transition' ),
				'transitionDuration' => get_post_meta( $post_id, $this->metaname . '_transitionDuration' ),
				'interval' => get_post_meta( $post_id, $this->metaname . '_interval' ),
				//'x-position' => get_post_meta( $post_id, $this->metaname . '_x-position' ),
				//'y-position' => get_post_meta( $post_id, $this->metaname . '_y-position' ),
				'controls' => get_post_meta( $post_id, $this->metaname . '_controls' ),
				'thumbnails' => get_post_meta( $post_id, $this->metaname . '_thumbnails' ),
				'animation' => $panzoom,
				'image_url' => plugins_url( 'images/', __FILE__ ),
			);
			
			/*
			$flashvars = array(
				'loadParams' => "false",
				'xmlFilePath' => $xmlfile,
				'navAppearance' => "Hidden",
				'feedbackTimerAppearance' => 'Hidden',
				'panZoom' => get_post_meta( $post_id, $this->metaname . '_panZoom' ),
				'contentScale' => 'Crop to Fit All',
			);
			
			$params = array( 'wmode' => "transparent" );
			*/
			
			$attributes = array();
			
			$content = "var imgArr = " . json_encode( $imgArray ) . ";\n";
			$content .= "var jsvars = " . json_encode( $jsvars ) . ";\n";
			//$content .= "var flashvars = " . json_encode( $flashvars ) . ";\n";
			//$content .= "var params = " . json_encode( $params ) . ";\n";
			$content .= "var attributes = " . json_encode( $attributes ) . ";\n";
			
			file_put_contents( $file, $content, LOCK_EX );
			
		}
	}

	function save_meta( $post ) {
		
		// DONT FORGET PREFIX !!!!!!!!!!!!!!!!!!!!!!!!!

		// OK, we're authenticated: we need to find and save the data
		// We'll put it into an array to make it easier to loop though.
		$meta = $_POST[ $this->metaname ];
		// Add values of $events_meta as custom fields
		foreach ( $meta as $key => $value )
		{
			$key = $this->metaname . '_' . $key;
			
			// Cycle through the meta array!
			if ( $post->post_type == 'revision' ) return; // Don't store custom data twice
			$value = implode( ',', (array)$value ); // If $value is an array, make it a CSV (unlikely)
			if ( get_post_meta( $post->ID, $key, FALSE ) )
			{
				// If the custom field already has a value
				update_post_meta( $post->ID, $key, $value );
			}
			else
			{
				// If the custom field doesn't have a value
				add_post_meta( $post->ID, $key, $value );
			}
			if ( !$value ) delete_post_meta( $post->ID, $key ); // Delete if blank
		}
	}

	function remove_media_tabs( $tabs ) {
		
		$post_id = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : NULL;
		
		if ( !empty( $post_id ) && 'gallery' == get_post_type( $post_id ) ) {
			unset( $tabs['library'] );
			unset( $tabs['type_url'] );
		}
		
		return $tabs;
	}
	
	function messages( $messages ) {
		
		$messages['gallery'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Gallery updated.' ),
			2 => __( 'Custom field updated.' ),
			3 => __( 'Custom field deleted.' ),
			4 => __( 'Gallery updated.' ),
			6 => __( 'Gallery published.' ),
			7 => __( 'Gallery saved.' ),
			8 => __( 'Gallery submitted.' ),
			9 => __( 'Gallery scheduled for: <strong>%1$s</strong>.' ),
			10 => __( 'Gallery draft updated.' ),
		);
	
	  return $messages;
	}
	
	function columns( $columns ) {
		
		$new_columns['cb'] = '<input type="checkbox" />';
		$new_columns['id'] = __( 'ID' );
		$new_columns['title'] = _x( 'Gallery Name', 'column name' );
		$new_columns['images'] = _x( 'Images', 'column name' );
		$new_columns['author'] = __( 'Author' );
		$new_columns['date'] = _x( 'Date', 'column name' );
		
		return $new_columns;
	}
	
	function column_content( $column, $id ) {
		
		global $wpdb;
		
		switch ( $column )
		{
			case 'id' :
				echo $id;
				break;
			
			case 'images' :
				// Get number of images in gallery
				$num_images = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_parent = {$id} AND post_type = 'attachment';" ) );
				echo $num_images;
				break;
			
			default :
			
				break;
		}
	}

	function list_galleries() {
		
		global $post;
		
		$gallery = get_post_meta( $post->ID, $this->metaname . "_gallery_id", true );
				
		$default = $this->default;
		
		$default_gallery_id = is_array( $default ) ? (string)$default->ID : NULL;
		
		$args = array(
			'post_type' => 'gallery',
			'exclude' => $default_gallery_id,
			'hierarchical' => 0,
			'name' => $this->metaname . '[gallery_id]',
			'id' => $this->metaname . '_gallery_id',
			'show_option_none' => '(use default) ',
			'selected' => (int)$gallery,
		);
		wp_dropdown_pages( $args );
		echo '<input type="hidden" id="_gallery_nonce" name="_gallery_nonce" value="',
		wp_create_nonce( '_gallery_settings_nonce' ), '" />', "\n";
	}
		
	private function get_cached_file_info( $id ) {
		
		$files = array();
		
		$filename = "galleries/gallery-params-$id.js";
		$file = plugin_dir_path( __FILE__ ) . $filename;
		if ( !file_exists( $file ) )
		{
			$file = $this->uploads['basedir'] . $filename;
			if ( !file_exists( $file ) )
				return false;
		}
		$files['js']['url'] = plugins_url( $filename, __FILE__ );
		$files['js']['time'] = filemtime( $file );

		$filename = "galleries/gallery-images-$id.xml";
		$file = plugin_dir_path( __FILE__ ) . $filename;
		if ( !file_exists( $file ) )
		{
			$file = $this->uploads['basedir'] . "/$filename";
			if ( !file_exists( $file ) )
				return false;
		}
		$files['xml']['url'] = $uploads['baseurl'] . "$filename";
		$files['xml']['time'] = filemtime( $file );

		return $files;
	}
		
	function slideshow_shortcode( $atts ) {
		
		extract( shortcode_atts( array(
			'width' => $this->width,
			'height' => $this->height,
			'align' => NULL,
		), $atts ) );
		
		$code = '<div id="slideshow"></div>' . "\n";
		
		return $code;
	}
	
	function verify_post_type( $post = false ) {
		
		// check for post_type query arg (post new)
		if ( $post == false && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'gallery' )
			return true;

		// if post isn't set, try get vars (edit post)
		if ( $post == false )
			$post = ( isset( $_GET['post'] ) ) ? $_GET['post'] : false;

		// look for post_id via post or get (media upload)
		if ( $post == false )
			$post = ( isset( $_REQUEST['post_id'] ) ) ? $_REQUEST['post_id'] : false;


		$post_type = get_post_type( $post );

		//if post is really an attachment or revision, look to the post's parent
		//if ( $post_type == 'gallery' )
			//$post_type = get_post_type( get_post( $post )->post_parent );
			
		return $post_type == 'gallery';

	}
	
	function delete_associated_media( $id ) {
		
		# Check if gallery
		if ( 'gallery' !== get_post_type( $id ) ) {
			return;
		}
		
		$media = get_children( array(
			'post_parent' => $id,
			'post_type' => 'attachment'
		) );
		
		if ( empty( $media ) ) {
			return;
		}
		
		foreach ( $media as $file ) {
			// pick what you want to do
			wp_delete_attachment( $file->ID );
		}
		
		$js_file = $this->uploads['basedir'] . "/galleries/gallery-{$id}.js";
		
		if ( file_exists( $js_file ) ) {
			unlink( $js_file );
		}
	}
}

new WPPhotoSlideshows;

?>