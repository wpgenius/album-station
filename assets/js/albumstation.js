jQuery(document).ready(function($){
	if (jQuery(".album-grid").length > 0) {
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
	}
});

jQuery( window ).load(function() {
	if (jQuery(".album-grid").length > 0) {
		jQuery(".button-group span:first-child").trigger('click');
	}
});
