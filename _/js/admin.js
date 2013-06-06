var frame;
(function($) {
	// DOM ready
	$(function() {	
		/*
			IMAGE GALLERY
		*/
		//		$('#hue_gallery .button').click(function() {
		//			if ( typeof wp !== 'undefined' && wp.media && wp.media.editor )
		//			        wp.media.editor.open( 'hue_gallery' );
		//		});
		//var frame; // store new frame variable
		var gallery = $('#hue_gallery');
		gallery.find('.button').click(function(e) {
			e.preventDefault(); // prevent default click action
			
			// use existing frame if possible
			if (frame) {
				frame.open();
				return;
			}
			
			// create a new uploader frame
			frame = wp.media.frames.hue_frame = wp.media({
				className: 'media-frame hue_gallery_frame', // frame class for styling
			    title : "Add Images", // uploader title 
			    multiple : true, // multiple select
			    library : { type: 'image' }, //
			    button : { text : 'Add Images' }
			});
			
			// callbacks
		    frame.on( 'select', function() {
		      // We set multiple to false so only get one image from the uploader
		      var selection = frame.state().get('selection');
		      // Do something with attachment.id and/or attachment.url here
		      var att_ids = [];
		      selection.each(function(att) {
		      	att = att.toJSON();
		      	att_ids.push(att.id);
		      });
		      // request new thumbnail html
		      $.post(ajaxurl, {
		      		action: 'get_thumbnails',
		      		gallery_att_ids: JSON.stringify(att_ids),
		      		nonce: gallery.find('#hue_gallerynonce').val()
		      	}, 
		      	function(data, textStatus, jqXHR) {
		      		//console.log(textStatus);
		      		//console.log(data);
		      		if (textStatus !== 'success' || data === 0) {
		      			console.warn('requesting thumbnail html failed')
		      			return; // fail
		      		}
		      		thumbs.append(data);
		      		initGallery();
		      		//console.log('add: ' + getGalleryIds());
		      		updateGalleryIds();
		      	}, 'html');
		    });
			
			// on first openining this frame: switch to uploader tab
			frame.on('open', function() {
				//console.log("switching to uploader");
				$('.hue_gallery_frame .media-router a').eq(0).trigger('click');
			});
			
			// open frame
			frame.open();
			

			//return false;
		});
		
		var thumbs = gallery.find('.thumbnails');
		
		// init or refresh gallery
		// only inits if there are thumbnails present
		// -> this prevents a bug where added items are not really responding well
		var initGallery = function() {
			if ( !thumbs.hasClass('ui-sortable') ) {
				if ( thumbs.children().length > 0 ) {
					thumbs.sortable({
						containment: 'parent',
						tolerance: 'pointer',
						cursorAt: { left: 75, top: 75 },
						revert: 150,
						start: function(event, ui) {
							ui.item.find('.remove').fadeOut(0);
						},
						stop: function(event, ui) { // when sorting is finished
							ui.item.find('.remove').fadeIn(0);
							//console.log('sort: ' + getGalleryIds());
							updateGalleryIds();
						}
					});
				}
			} else {
				thumbs.sortable('refresh');
			}
		};
		
		// get an array of attachment ids currently represented by the gallery
		var getGalleryIds = function() {
			var att_ids = [];
			thumbs.find('.thumbnail').each(function(idx, thumb) {
				var id = $(thumb).data('id'); // get id form data attribute. returns undefined if not set
				// check data-id (for "" or undefined)
				if (!id) {
					return;
				}
				att_ids.push(id);
			});
			return att_ids;
		};
		
		// update hidden input field
		var updateGalleryIds = function() {
			gallery.find('.att_ids').val(JSON.stringify(getGalleryIds())); 
		};
		
		// enable sorting
		initGallery();
		
		// enable remove
		thumbs.on('click', '.remove', function(e) {
			$(this).parent().fadeOut(300, function(){
				$(this).remove();
				updateGalleryIds();
			});
			//console.log('delete: ' + getGalleryIds());
			
		});
		
	});
}(jQuery));
