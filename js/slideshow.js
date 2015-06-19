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
	
	if ( container == '' )
	{
		container = "#slideshow";
	}
	
	var photos = imgArr;
	
	var slideshowSpeed = ( +jsvars['interval'] );
	var transition = jsvars['transition'] / 2;
	
	$( container ).css( 'position', 'relative' );
	
	$( container ).append( '<div class="slideshow-images-1"><div class="caption"></div></div>' );
	$( container ).append( '<div class="slideshow-images-2"><div class="caption"></div></div>' );
	
	var images = new Array();
	
	for ( i = 0; i < photos.length; i++ ) {
		images[i] = new Image();
		images[i].src = photos[i]['src'];
	}
	
	console.log( images );
	

    var init_slick = function() {
		$('.responsive').slick({
			dots: true,
			infinite: false,
			speed: 500,
			slidesToShow: 4,
			slidesToScroll: 4,
			responsive: [{
				breakpoint: 1024,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3,
					infinite: true,
					dots: true
				}
			}, {
				breakpoint: 600,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				}
			}, {
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}]
		});
	};
	
	if ( String( jsvars['thumbnails'] ) == "Enabled" ) {
		
		var thumbs = '<div class="slider responsive">';
		
		$.each( photos, function( i, item ) {
			thumbs += '<div><a href="javascript:void(0);" data-image="' + i + '"><img src="' + photos[i].thumb + '" /></a></div>';
		});
		
		thumbs += '</div>';
		
		$( container ).after( thumbs );
		
		init_slick();
	}
	
	$( ".slick-slide a" ).click( function() {
		console.log( $(this).data( 'image' ) );
		stopAnimation();
		navigate( $(this).data( 'image' ) );
	});
	
	if ( String( jsvars['controls'] ) != 'Disabled' ) {
		$( container ).append( '<div class="controls"><a href="javascript:void(0);" class="btn back" title="Back"></a><a href="javascript:void(0);" class="btn play-pause" title="Pause"></a><a href="javascript:void(0);" class="btn next" title="Next"></a></div>' );
	}
	
	$( container + ' .slideshow-images-1' ).css({ position: 'absolute', top: 0, right: 0, bottom: 0, left: 0, 'background-position': jsvars['y-position'] + ' ' + jsvars['x-position'], 'background-repeat': 'no-repeat' });
	
	$( container + ' .slideshow-images-2' ).css({ position: 'absolute', top: 0, right: 0, bottom: 0, left: 0, 'background-position': jsvars['y-position'] + ' ' + jsvars['x-position'], 'background-repeat': 'no-repeat' });
	
	$( container + ' .controls' ).css({ height: '32px', position: 'absolute', right: '5px', bottom: '5px' });
	$( container + ' .controls .btn' ).css({ display: 'inline-block', width: '32px', height: '32px', margin: '0 5px' });
	$( container + ' .controls .btn.back' ).css({ "background-image" : "url('" + jsvars['image_url'] + "btn-back.png')" });
	$( container + ' .controls .btn.next' ).css({ "background-image" : "url('" + jsvars['image_url'] + "btn-next.png')" });
	$( container + ' .controls .btn.play-pause' ).css({ "background" : "url('" + jsvars['image_url'] + "btn-play-pause.png') 0% 100%" });
	
	$( container + ' .caption' ).css({ display: 'none' });
	
	// Backwards navigation
	$( container + ' .controls .btn.back' ).click( function() {
		stopAnimation();
		navigate( "back" );
	});
	
	// Forward navigation
	$( container + ' .controls .btn.next' ).click( function() {
		stopAnimation();
		navigate( "next" );
	});
	
	// Play / Pause
	$( container + ' .controls .btn.play-pause' ).click( function() {
		if ( $( this ).attr( 'title' ) == 'Pause' )
		{
			stopAnimation();
		}
		else
		{
			// Change title and background image to "pause"
			$( this ).css({ "background-position" : "0% 100%" });
			$( this ).attr( 'title', 'Pause' );
			// Show the next image
			navigate( "next" );
			// Start playing the animation
			interval = setInterval( function() { navigate( "next" ); }, slideshowSpeed );
		}
	});
	
	/*
	$( container + ' .controls .btn.play-pause' ).toggle( function() {
		stopAnimation();
	}, function() {
		// Change the background image to "pause"
		$(this).css({ "background-position" : "0% 100%" });
		
		// Show the next image
		navigate( "next" );
		
		// Start playing the animation
		interval = setInterval( function() { navigate( "next" ); }, slideshowSpeed );
	});
	*/	
	
	var activeContainer = 1;	
	var currentImg = 0;
	var animating = false;
	
	var navigate = function( image )
	{
		// Check if no animation is running. If it is, prevent the action
		if ( animating )
		{
			//return;
		}
		
		// Check which current image we need to show
		if ( image == "next" )
		{
			if ( currentImg == photos.length - 1 ) {
				currentImg = 0;
			}
			else {
				currentImg++;
			}
		}
		else if ( image == "back" )
		{
			
			if ( currentImg <= 0 ) {
				currentImg = photos.length - 1;
			}
			else {
				currentImg--;
			}
		}
		
		else {
			currentImg = image;
		}
		
		console.log( currentImg );
		
		// Check which container we need to use
		var currentContainer = activeContainer;
		
		if( activeContainer == 1 ) { activeContainer = 2; } else { activeContainer = 1; }
		
		//if ( !window.console ) console.log( "Cur Img: " + currentImg );
		//if ( !window.console ) console.log( photos.length );
		
		if ( currentImg > 2 )
		{
			$( container ).css({ "background-image" : "none" });
		}
		
		showImage( photos[currentImg], currentContainer, activeContainer );
		
	};
	
	var currentZindex = -1;
	
	var showImage = function( photoObject, currentContainer, activeContainer )
	{
		//console.log( container + " .slideshow-images-" + currentContainer );
		//console.log( container + " .slideshow-images-" + activeContainer );
		animating = true;
		
		// Make sure the new container is always on the background
		currentZindex--;
		
		// Set the background image of the new active container
		/*
		$( container + " .slideshow-images-" + activeContainer ).animate({opacity: 1}, transition, function(){
			$(this).css({
				"background-image" : "url(" + photoObject.src + ")",
				"display" : "block"
				//"z-index" : currentZindex
			});
		});
		//*/
		
		$( container + " .slideshow-images-" + activeContainer ).css({
			"background-image" : "url(" + photoObject.src + ")",
			"display" : "block"
			//"z-index" : currentZindex
		});
		
		console.log( photoObject );
		
		$( container + " .slideshow-images-" + activeContainer ).animate( { opacity: 1 }, transition, function() {
			
			//console.log( photoObject );
		});
				
		// Hide the header text
		//$("#headertxt").css({"display" : "none"});
		
		//$( container + " .caption span:empty" ).hide();
		
		// Set the new header text
		/*
		$("#firstline").html(photoObject.firstline);
		$("#secondline")
			.attr("href", photoObject.url)
			.html(photoObject.secondline);
		$("#pictureduri")
			.attr("href", photoObject.url)
			.html(photoObject.title);
		*/
		
		//$( container + " .caption span" ).html(photoObject.caption);
		//alert(photoObject.caption);
		//console.log( photoObject.caption );
		
		// Fade out the current container
		// and display the header text when animation is complete
		$( container + " .slideshow-images-" + currentContainer ).animate( { opacity: 0 }, transition, function() {
			//$(this).css({
				//"background-image" : "url(" + photoObject.src + ")",
				//"display" : "block"
				//"z-index" : currentZindex
			//});
			
			if ( photoObject.caption != "" )
			{
				$( container + " .slideshow-images-" + currentContainer + " .caption" ).html( photoObject.caption );
				$( container + " .slideshow-images-" + currentContainer + " .caption" ).show();
			}
			else
			{
				$( container + " .slideshow-images-" + currentContainer + " .caption" ).hide();
			}
			//$( container + " .caption:empty" ).hide();
			//$( container + " .caption" ).css({ "display" : "block" });
			
			//$(this).html('<p>' + photoObject.caption + '</p>');
			setTimeout( function() {
				/*$("#headertxt").css( {"display" : "block"} );*/
				//$( container + " .caption" ).css( {"display" : "block", 'z-index': currentZindex + 1} );
				animating = false;
			}, transition );
			
		});
	};
	
	var stopAnimation = function()
	{
		// Change title and background image to "play"
		$( container + ' .controls .btn.play-pause' ).css({ "background-position" : "0% 0%" });
		$( container + ' .controls .btn.play-pause' ).attr( 'title', 'Play' );
		// Clear the interval
		clearInterval( interval );
	};
	
	// We should statically set the first image
	navigate( 0 );
	
	// Start playing the animation
	interval = setInterval( function() { navigate( "next" ); }, slideshowSpeed );

});