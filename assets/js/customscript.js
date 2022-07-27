jQuery(function($){

	// on upload button click
	$('body').on( 'click', '#upload_img', function(e){

		e.preventDefault();

		custom_uploader = wp.media({
			title: 'Insert image',
			library : {
				// uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
				type : 'image'
			},
			button: {
				text: 'Use this image' // button label text
			},
			multiple: true
		});

        

        custom_uploader.on('select', function() { // it also has "open" and "close" events

			var attachments= custom_uploader.state().get('selection').toJSON();
            
            var newarr = attachments.map( (img) => {
                return img.id;
            } );
            var img_ids = newarr.toString();      
            $('#immage_id').val(img_ids);

            var html = '';
            for ( i=0; i<attachments.length; i++){
                html += '<img src="' + attachments[i].url + '" height="100" width="100">';
            }
            $('.selected_images').html( html );
		});

        custom_uploader.on('open', function() {

            var selection = custom_uploader.state().get('selection');
            
            var ids = jQuery('input#immage_id').val().split(',');

            ids.forEach(function(id) {
              var attachment = wp.media.attachment(id);
              attachment.fetch();
              selection.add(attachment ? [attachment] : []);
            }); // would be probably a good idea to check if it is indeed a non-empty array

        }).open();
	});
});

jQuery(document).ready(function(){

    jQuery("input[name='video_choice']").change( function(){
        var videoChoice = jQuery("input[name='video_choice']:checked").val();
        if(videoChoice=='single'){
           jQuery('.video_playlist').hide(); 
           jQuery('.video_ids').show(); 
        }else{
            jQuery('.video_ids').hide(); 
            jQuery('.video_playlist').show(); 

        }
    });

    jQuery( "input[name='video_choice']:checked" ).trigger( "change" );
    
    var jQuerygrid = jQuery('.album-grid').isotope({
        itemSelector: '.album-station',
        layoutMode: 'fitRows',
        percentPosition: true,
        
    });
    jQuery('.filter-button-group').on( 'click', 'span', function() {
        var filterValue = jQuery(this).attr('data-filter');
        jQuerygrid.isotope({ filter: filterValue });
    });

    jQuery('.button-group').each( function( i, buttonGroup ) {
        var jQuerybuttonGroup = jQuery( buttonGroup );
        jQuerybuttonGroup.on( 'click', 'span', function() {
            jQuerybuttonGroup.find('.is-checked').removeClass('is-checked');
            jQuery( this ).addClass('is-checked');
        });
    });
});

jQuery( window ).load(function() {
	jQuery(".button-group a:first-child").trigger('click');
});	