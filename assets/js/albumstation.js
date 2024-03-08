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
	var list= []
	jQuery('.filter_buttons').each(function(n) {
		list[n] = jQuery(this).attr('id')
	})

	var classList = []
	jQuery('.album-station').each(function(n) {
		classList[n] = jQuery(this).data('sort')
	})

	function removeDuplicates(arr) {
		return arr.filter((item,
			index) => arr.indexOf(item) === index);
	}
	classList =  removeDuplicates(classList);
	classList.push('all')

	var array3 = list.filter(function(obj) { return classList.indexOf(obj) == -1; });
	for (let i = 0; i < array3.length; ++i) {
		jQuery('#'+ array3[i]).hide();
	}

});
