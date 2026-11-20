<?php
/**
 * Minimal Elementor stubs for PHPStan (Elementor is not installed in CI).
 * Only the classes, constants and methods Coverfold uses.
 *
 * @package Coverfold
 */

// phpcs:disable

namespace Elementor;

abstract class Widget_Base {
	/** @param array<string, mixed> $args */
	public function start_controls_section( string $id, array $args = array() ): void {}
	public function end_controls_section(): void {}
	/** @param array<string, mixed> $args */
	public function add_control( string $id, array $args = array() ): void {}
	/** @param array<string, mixed> $args */
	public function add_responsive_control( string $id, array $args = array() ): void {}
	/** @param array<string, mixed> $args */
	public function add_group_control( string $type, array $args = array() ): void {}
	public function start_controls_tabs( string $id ): void {}
	public function end_controls_tabs(): void {}
	/** @param array<string, mixed> $args */
	public function start_controls_tab( string $id, array $args = array() ): void {}
	public function end_controls_tab(): void {}
	/** @return array<string, mixed> */
	public function get_settings_for_display(): array { return array(); }
}

class Controls_Manager {
	const TAB_STYLE  = 'style';
	const TEXT       = 'text';
	const NUMBER     = 'number';
	const SELECT     = 'select';
	const SELECT2    = 'select2';
	const SWITCHER   = 'switcher';
	const CHOOSE     = 'choose';
	const COLOR      = 'color';
	const SLIDER     = 'slider';
	const DIMENSIONS = 'dimensions';
}

class Group_Control_Border {
	public static function get_type(): string { return 'border'; }
}

class Group_Control_Typography {
	public static function get_type(): string { return 'typography'; }
}

class Widgets_Manager {
	public function register( Widget_Base $widget ): void {}
}

class Files_Manager {
	public function clear_cache(): void {}
}

class Plugin {
	/** @var Plugin */
	public static $instance;
	/** @var Files_Manager|null */
	public $files_manager;
}
