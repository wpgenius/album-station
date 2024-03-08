<?php
/**
 *
 * @class       Album_station_settings
 * @author      Team WPGenius (Makarand Mane)
 * @category    Admin
 * @package     album-station/includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Album_station_settings {

	public static $instance;
	private $prefix		= 'youtube_';
	private $opt_grp	= 'album_station_api_';
	private $page		= 'album_station_settings';
	
	public static function init(){

	    if ( is_null( self::$instance ) )
	        self::$instance = new Album_station_settings();
	    return self::$instance;
	}

	private function __construct(){

		// add_action( 'admin_menu', array($this,'album_station_settings_menu'), 11);
		// add_action( 'admin_init', array($this,'album_station_register_settings'),10);

	} // END public function __construct

	function album_station_settings_menu(){

		add_submenu_page(
			'edit.php?post_type=album',
			__('Album Settings' ), // page title
			__('Settings' ), // menu title
			'manage_options', // capability
			$this->page, // menu slug
			array( $this, 'album_settings_callback')
		);
	}

	function album_station_register_settings() {
		
		//Register settings
	    register_setting( $this->opt_grp, $this->prefix.'api_key', array( 'type' => 'string', 'default' => '' ) );

		//Register sections
		add_settings_section( $this->prefix.'api_section',		__('Youtube API','album-station'),			array( $this, 'as_api_section_title' ),	$this->page );
		
		//Add settings to section- braincert_api_section 
		add_settings_field( $this->prefix.'api_key',	__('Youtube API Key :','album-station'), array( $this, 'as_api_key_field' ), 	$this->page, $this->prefix.'api_section' );
		
	}
	
	function album_settings_callback(){
		?>
        <div class="wrap">
    	
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <form method="POST" action="options.php">
				<?php
					// output security fields for the registered setting "wporg"
					settings_fields( $this->opt_grp );
					// output setting sections and their fields
					// (sections are registered for "wporg", each field is registered to a specific section)
					do_settings_sections( $this->page );
					// output save settings button
					submit_button( __( 'Save Settings','album-station') );
                 ?>
            </form>
        </div>
        <?php
		
	}
	
	function as_api_section_title(){
		?>
		<p><?php _e( 'Get API details from https://developers.google.com/ & put below.','album-station'); ?></p>
        <?php 
	}
	
	
	
	function as_api_key_field(){
		?>
       	<input type='text' name='<?php echo $this->prefix ?>api_key' id='<?php echo $this->prefix ?>api_key' value='<?php echo get_option( $this->prefix.'api_key' );?>' style="width: 300px;">
        <?php
	}
	
	

}
Album_station_settings::init();