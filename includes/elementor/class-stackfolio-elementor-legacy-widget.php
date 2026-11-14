<?php
/**
 * Hidden widget that renders pages built with Album Station's widget.
 *
 * @package StackFolio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Same controls as the StackFolio widget (they reuse Album Station's control
 * IDs), registered under Album Station's widget name and hidden from the panel.
 */
class StackFolio_Elementor_Legacy_Widget extends StackFolio_Elementor_Widget {

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
		return esc_html__( 'StackFolio Albums (Album Station)', 'stackfolio' );
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
