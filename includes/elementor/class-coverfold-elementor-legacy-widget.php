<?php
/**
 * Hidden widget that renders pages built with Album Station's widget.
 *
 * @package Coverfold
 */

defined( 'ABSPATH' ) || exit;

/**
 * Same controls as the Coverfold widget (they reuse Album Station's control
 * IDs), registered under Album Station's widget name and hidden from the panel.
 */
class Coverfold_Elementor_Legacy_Widget extends Coverfold_Elementor_Widget {

	/**
	 * Widget name used by Album Station.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'as-gallery';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Coverfold Albums (Album Station)', 'coverfold' );
	}

	/**
	 * Hide from the widget panel; existing instances still render and edit.
	 *
	 * @return bool
	 */
	public function show_in_panel() {
		return false;
	}
}
