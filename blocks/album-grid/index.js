/**
 * StackFolio Albums block (editor). Plain JavaScript, no build step needed.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;
	var useSelect = wp.data.useSelect;
	var ServerSideRender = wp.serverSideRender;

	function CategoryPicker( props ) {
		var terms = useSelect( function ( select ) {
			return select( 'core' ).getEntityRecords( 'taxonomy', 'stackfolio_category', { per_page: 100, hide_empty: false } );
		}, [] );

		if ( terms === null ) {
			return el( c.Spinner );
		}
		if ( ! terms || ! terms.length ) {
			return el( 'p', { className: 'components-base-control__help' }, __( 'No album categories yet. All albums are shown.', 'stackfolio' ) );
		}

		return el(
			Fragment,
			null,
			el( 'p', { className: 'components-base-control__help' }, __( 'Leave all unticked to show every album.', 'stackfolio' ) ),
			terms.map( function ( term ) {
				var checked = props.value.indexOf( term.id ) !== -1;
				return el( c.CheckboxControl, {
					key: term.id,
					label: term.name,
					checked: checked,
					onChange: function ( isChecked ) {
						var next = props.value.filter( function ( id ) {
							return id !== term.id;
						} );
						if ( isChecked ) {
							next.push( term.id );
						}
						props.onChange( next );
					},
				} );
			} )
		);
	}

	function toggle( attributes, setAttributes, key, label, help ) {
		return el( c.ToggleControl, {
			label: label,
			help: help,
			checked: !! attributes[ key ],
			onChange: function ( value ) {
				var update = {};
				update[ key ] = value;
				setAttributes( update );
			},
			__nextHasNoMarginBottom: true,
		} );
	}

	function align( value, onChange, label ) {
		return el( c.SelectControl, {
			label: label,
			value: value,
			options: [
				{ label: __( 'Left', 'stackfolio' ), value: 'left' },
				{ label: __( 'Center', 'stackfolio' ), value: 'center' },
				{ label: __( 'Right', 'stackfolio' ), value: 'right' },
			],
			onChange: onChange,
			__nextHasNoMarginBottom: true,
		} );
	}

	function Edit( props ) {
		var a = props.attributes;
		var set = props.setAttributes;
		var blockProps = be.useBlockProps();

		var inspector = el(
			be.InspectorControls,
			null,
			el(
				c.PanelBody,
				{ title: __( 'Albums', 'stackfolio' ), initialOpen: true },
				el( CategoryPicker, { value: a.categories || [], onChange: function ( v ) { set( { categories: v } ); } } ),
				el( c.RangeControl, {
					label: __( 'Number of albums (0 = all)', 'stackfolio' ),
					value: a.number,
					min: 0,
					max: 100,
					onChange: function ( v ) { set( { number: v || 0 } ); },
					__nextHasNoMarginBottom: true,
				} ),
				el( c.SelectControl, {
					label: __( 'Order by', 'stackfolio' ),
					value: a.orderby,
					options: [
						{ label: __( 'Date', 'stackfolio' ), value: 'date' },
						{ label: __( 'Title', 'stackfolio' ), value: 'title' },
						{ label: __( 'Custom order (Order field)', 'stackfolio' ), value: 'menu_order' },
						{ label: __( 'Last modified', 'stackfolio' ), value: 'modified' },
						{ label: __( 'Random', 'stackfolio' ), value: 'rand' },
					],
					onChange: function ( v ) { set( { orderby: v } ); },
					__nextHasNoMarginBottom: true,
				} ),
				el( c.SelectControl, {
					label: __( 'Order', 'stackfolio' ),
					value: a.order,
					options: [
						{ label: __( 'Descending', 'stackfolio' ), value: 'DESC' },
						{ label: __( 'Ascending', 'stackfolio' ), value: 'ASC' },
					],
					onChange: function ( v ) { set( { order: v } ); },
					__nextHasNoMarginBottom: true,
				} )
			),
			el(
				c.PanelBody,
				{ title: __( 'Layout', 'stackfolio' ), initialOpen: false },
				el( c.RangeControl, { label: __( 'Columns (desktop)', 'stackfolio' ), value: a.columns, min: 1, max: 6, onChange: function ( v ) { set( { columns: v } ); } } ),
				el( c.RangeControl, { label: __( 'Columns (tablet)', 'stackfolio' ), value: a.columnsTablet, min: 1, max: 6, onChange: function ( v ) { set( { columnsTablet: v } ); } } ),
				el( c.RangeControl, { label: __( 'Columns (mobile)', 'stackfolio' ), value: a.columnsMobile, min: 1, max: 6, onChange: function ( v ) { set( { columnsMobile: v } ); } } ),
				el( c.RangeControl, { label: __( 'Spacing (px)', 'stackfolio' ), value: a.gap, min: 0, max: 100, onChange: function ( v ) { set( { gap: v } ); } } ),
				el( c.SelectControl, {
					label: __( 'Hover effect', 'stackfolio' ),
					value: a.hover,
					options: [
						{ label: __( 'Zoom', 'stackfolio' ), value: 'zoom' },
						{ label: __( 'None', 'stackfolio' ), value: 'none' },
					],
					onChange: function ( v ) { set( { hover: v } ); },
					__nextHasNoMarginBottom: true,
				} ),
				toggle( a, set, 'showTitle', __( 'Show album titles', 'stackfolio' ) ),
				a.showTitle && el( c.SelectControl, {
					label: __( 'Title tag', 'stackfolio' ),
					value: a.titleTag,
					options: [ 'h2', 'h3', 'h4', 'h5', 'h6', 'p' ].map( function ( t ) { return { label: t.toUpperCase(), value: t }; } ),
					onChange: function ( v ) { set( { titleTag: v } ); },
					__nextHasNoMarginBottom: true,
				} ),
				a.showTitle && align( a.titleAlign, function ( v ) { set( { titleAlign: v } ); }, __( 'Title alignment', 'stackfolio' ) ),
				toggle( a, set, 'showCount', __( 'Show photo and video count', 'stackfolio' ) )
			),
			el(
				c.PanelBody,
				{ title: __( 'Category filters', 'stackfolio' ), initialOpen: false },
				toggle( a, set, 'filters', __( 'Show category filters', 'stackfolio' ) ),
				a.filters && toggle( a, set, 'filterAll', __( 'Show an "All" button', 'stackfolio' ) ),
				a.filters && a.filterAll && el( c.TextControl, {
					label: __( '"All" button label', 'stackfolio' ),
					value: a.allLabel,
					placeholder: __( 'All', 'stackfolio' ),
					onChange: function ( v ) { set( { allLabel: v } ); },
					__nextHasNoMarginBottom: true,
				} ),
				a.filters && align( a.filterAlign, function ( v ) { set( { filterAlign: v } ); }, __( 'Filter alignment', 'stackfolio' ) )
			),
			el(
				c.PanelBody,
				{ title: __( 'Lightbox', 'stackfolio' ), initialOpen: false },
				toggle( a, set, 'videos', __( 'Include videos', 'stackfolio' ) ),
				toggle( a, set, 'captions', __( 'Show captions', 'stackfolio' ), __( 'Uses the title and caption from the Media Library.', 'stackfolio' ) ),
				toggle( a, set, 'thumbnails', __( 'Thumbnail strip', 'stackfolio' ) ),
				a.thumbnails && toggle( a, set, 'hideThumbsFullscreen', __( 'Hide thumbnails in fullscreen', 'stackfolio' ) ),
				toggle( a, set, 'zoom', __( 'Zoom', 'stackfolio' ) ),
				toggle( a, set, 'fullscreen', __( 'Fullscreen button', 'stackfolio' ) ),
				toggle( a, set, 'slideshow', __( 'Slideshow button', 'stackfolio' ) ),
				a.slideshow && toggle( a, set, 'slideshowAutostart', __( 'Start slideshow automatically', 'stackfolio' ) ),
				toggle( a, set, 'download', __( 'Download button', 'stackfolio' ) )
			)
		);

		return el(
			'div',
			blockProps,
			inspector,
			el(
				c.Disabled,
				null,
				el( ServerSideRender, {
					block: 'stackfolio/album-grid',
					attributes: a,
					skipBlockSupportAttributes: true,
				} )
			)
		);
	}

	wp.blocks.registerBlockType( 'stackfolio/album-grid', {
		edit: Edit,
		save: function () {
			return null;
		},
	} );
}( window.wp ) );
