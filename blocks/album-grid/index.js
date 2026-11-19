/**
 * Coverfold Albums block (editor). Plain JavaScript, no build step needed.
 *
 * @param {Object} wp WordPress script globals.
 */
( function ( wp ) {
	'use strict';

	const el = wp.element.createElement;
	const Fragment = wp.element.Fragment;
	const __ = wp.i18n.__;
	const be = wp.blockEditor;
	const c = wp.components;
	const useSelect = wp.data.useSelect;
	const ServerSideRender = wp.serverSideRender;

	function CategoryPicker( props ) {
		const terms = useSelect( function ( select ) {
			return select( 'core' ).getEntityRecords(
				'taxonomy',
				'coverfold_category',
				{ per_page: 100, hide_empty: false }
			);
		}, [] );

		if ( terms === null ) {
			return el( c.Spinner );
		}
		if ( ! terms || ! terms.length ) {
			return el(
				'p',
				{ className: 'components-base-control__help' },
				__(
					'No album categories yet. All albums are shown.',
					'coverfold'
				)
			);
		}

		return el(
			Fragment,
			null,
			el(
				'p',
				{ className: 'components-base-control__help' },
				__( 'Leave all unticked to show every album.', 'coverfold' )
			),
			terms.map( function ( term ) {
				const checked = props.value.indexOf( term.id ) !== -1;
				return el( c.CheckboxControl, {
					key: term.id,
					label: term.name,
					checked,
					onChange( isChecked ) {
						const next = props.value.filter( function ( id ) {
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
			label,
			help,
			checked: !! attributes[ key ],
			onChange( value ) {
				const update = {};
				update[ key ] = value;
				setAttributes( update );
			},
			__nextHasNoMarginBottom: true,
		} );
	}

	function align( value, onChange, label ) {
		return el( c.SelectControl, {
			label,
			value,
			options: [
				{ label: __( 'Left', 'coverfold' ), value: 'left' },
				{ label: __( 'Center', 'coverfold' ), value: 'center' },
				{ label: __( 'Right', 'coverfold' ), value: 'right' },
			],
			onChange,
			__nextHasNoMarginBottom: true,
		} );
	}

	function Edit( props ) {
		const a = props.attributes;
		const set = props.setAttributes;
		const blockProps = be.useBlockProps();

		const inspector = el(
			be.InspectorControls,
			null,
			el(
				c.PanelBody,
				{ title: __( 'Albums', 'coverfold' ), initialOpen: true },
				el( CategoryPicker, {
					value: a.categories || [],
					onChange( v ) {
						set( { categories: v } );
					},
				} ),
				el( c.RangeControl, {
					label: __( 'Number of albums (0 = all)', 'coverfold' ),
					value: a.number,
					min: 0,
					max: 100,
					onChange( v ) {
						set( { number: v || 0 } );
					},
					__nextHasNoMarginBottom: true,
				} ),
				el( c.SelectControl, {
					label: __( 'Order by', 'coverfold' ),
					value: a.orderby,
					options: [
						{ label: __( 'Date', 'coverfold' ), value: 'date' },
						{ label: __( 'Title', 'coverfold' ), value: 'title' },
						{
							label: __(
								'Custom order (Order field)',
								'coverfold'
							),
							value: 'menu_order',
						},
						{
							label: __( 'Last modified', 'coverfold' ),
							value: 'modified',
						},
						{ label: __( 'Random', 'coverfold' ), value: 'rand' },
					],
					onChange( v ) {
						set( { orderby: v } );
					},
					__nextHasNoMarginBottom: true,
				} ),
				el( c.SelectControl, {
					label: __( 'Order', 'coverfold' ),
					value: a.order,
					options: [
						{
							label: __( 'Descending', 'coverfold' ),
							value: 'DESC',
						},
						{
							label: __( 'Ascending', 'coverfold' ),
							value: 'ASC',
						},
					],
					onChange( v ) {
						set( { order: v } );
					},
					__nextHasNoMarginBottom: true,
				} )
			),
			el(
				c.PanelBody,
				{ title: __( 'Layout', 'coverfold' ), initialOpen: false },
				el( c.RangeControl, {
					label: __( 'Columns (desktop)', 'coverfold' ),
					value: a.columns,
					min: 1,
					max: 6,
					onChange( v ) {
						set( { columns: v } );
					},
				} ),
				el( c.RangeControl, {
					label: __( 'Columns (tablet)', 'coverfold' ),
					value: a.columnsTablet,
					min: 1,
					max: 6,
					onChange( v ) {
						set( { columnsTablet: v } );
					},
				} ),
				el( c.RangeControl, {
					label: __( 'Columns (mobile)', 'coverfold' ),
					value: a.columnsMobile,
					min: 1,
					max: 6,
					onChange( v ) {
						set( { columnsMobile: v } );
					},
				} ),
				el( c.RangeControl, {
					label: __( 'Spacing (px)', 'coverfold' ),
					value: a.gap,
					min: 0,
					max: 100,
					onChange( v ) {
						set( { gap: v } );
					},
				} ),
				el( c.SelectControl, {
					label: __( 'Hover effect', 'coverfold' ),
					value: a.hover,
					options: [
						{ label: __( 'Zoom', 'coverfold' ), value: 'zoom' },
						{ label: __( 'None', 'coverfold' ), value: 'none' },
					],
					onChange( v ) {
						set( { hover: v } );
					},
					__nextHasNoMarginBottom: true,
				} ),
				toggle(
					a,
					set,
					'showTitle',
					__( 'Show album titles', 'coverfold' )
				),
				a.showTitle &&
					el( c.SelectControl, {
						label: __( 'Title tag', 'coverfold' ),
						value: a.titleTag,
						options: [ 'h2', 'h3', 'h4', 'h5', 'h6', 'p' ].map(
							function ( t ) {
								return { label: t.toUpperCase(), value: t };
							}
						),
						onChange( v ) {
							set( { titleTag: v } );
						},
						__nextHasNoMarginBottom: true,
					} ),
				a.showTitle &&
					align(
						a.titleAlign,
						function ( v ) {
							set( { titleAlign: v } );
						},
						__( 'Title alignment', 'coverfold' )
					),
				toggle(
					a,
					set,
					'showCount',
					__( 'Show photo and video count', 'coverfold' )
				)
			),
			el(
				c.PanelBody,
				{
					title: __( 'Category filters', 'coverfold' ),
					initialOpen: false,
				},
				toggle(
					a,
					set,
					'filters',
					__( 'Show category filters', 'coverfold' )
				),
				a.filters &&
					toggle(
						a,
						set,
						'filterAll',
						__( 'Show an "All" button', 'coverfold' )
					),
				a.filters &&
					a.filterAll &&
					el( c.TextControl, {
						label: __( '"All" button label', 'coverfold' ),
						value: a.allLabel,
						placeholder: __( 'All', 'coverfold' ),
						onChange( v ) {
							set( { allLabel: v } );
						},
						__nextHasNoMarginBottom: true,
					} ),
				a.filters &&
					align(
						a.filterAlign,
						function ( v ) {
							set( { filterAlign: v } );
						},
						__( 'Filter alignment', 'coverfold' )
					)
			),
			el(
				c.PanelBody,
				{ title: __( 'Lightbox', 'coverfold' ), initialOpen: false },
				toggle( a, set, 'videos', __( 'Include videos', 'coverfold' ) ),
				toggle(
					a,
					set,
					'captions',
					__( 'Show captions', 'coverfold' ),
					__(
						'Uses the title and caption from the Media Library.',
						'coverfold'
					)
				),
				toggle(
					a,
					set,
					'thumbnails',
					__( 'Thumbnail strip', 'coverfold' )
				),
				a.thumbnails &&
					toggle(
						a,
						set,
						'hideThumbsFullscreen',
						__( 'Hide thumbnails in fullscreen', 'coverfold' )
					),
				toggle( a, set, 'zoom', __( 'Zoom', 'coverfold' ) ),
				toggle(
					a,
					set,
					'fullscreen',
					__( 'Fullscreen button', 'coverfold' )
				),
				toggle(
					a,
					set,
					'slideshow',
					__( 'Slideshow button', 'coverfold' )
				),
				a.slideshow &&
					toggle(
						a,
						set,
						'slideshowAutostart',
						__( 'Start slideshow automatically', 'coverfold' )
					),
				toggle(
					a,
					set,
					'download',
					__( 'Download button', 'coverfold' )
				)
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
					block: 'coverfold/album-grid',
					attributes: a,
					skipBlockSupportAttributes: true,
				} )
			)
		);
	}

	wp.blocks.registerBlockType( 'coverfold/album-grid', {
		edit: Edit,
		save() {
			return null;
		},
	} );
} )( window.wp );
