<?php
/**
 * Settings page.
 *
 * @package StackFolio
 */

defined( 'ABSPATH' ) || exit;

/**
 * StackFolio > Settings page (YouTube API key, video cache).
 */
class StackFolio_Settings {

	const OPTION = 'stackfolio_settings';
	const PAGE   = 'stackfolio-settings';

	/**
	 * Hook everything up.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_post_stackfolio_clear_cache', array( __CLASS__, 'clear_cache' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( STACKFOLIO_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Read one setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $fallback Fallback.
	 * @return mixed
	 */
	public static function get( $key, $fallback = '' ) {
		$options = get_option( self::OPTION, array() );
		return ( is_array( $options ) && isset( $options[ $key ] ) ) ? $options[ $key ] : $fallback;
	}

	/**
	 * Add the submenu page.
	 */
	public static function menu() {
		add_submenu_page(
			'edit.php?post_type=' . StackFolio_Post_Type::POST_TYPE,
			__( 'StackFolio Settings', 'stackfolio' ),
			__( 'Settings', 'stackfolio' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register the option and its fields.
	 */
	public static function register() {
		register_setting(
			'stackfolio',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);

		add_settings_section(
			'stackfolio_youtube',
			__( 'YouTube playlists', 'stackfolio' ),
			array( __CLASS__, 'youtube_section' ),
			self::PAGE
		);

		add_settings_field(
			'youtube_api_key',
			__( 'YouTube Data API key', 'stackfolio' ),
			array( __CLASS__, 'api_key_field' ),
			self::PAGE,
			'stackfolio_youtube',
			array( 'label_for' => 'stackfolio-youtube-api-key' )
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		return array(
			'youtube_api_key' => isset( $input['youtube_api_key'] ) ? sanitize_text_field( $input['youtube_api_key'] ) : '',
		);
	}

	/**
	 * Section intro.
	 */
	public static function youtube_section() {
		echo '<p>' . esc_html__( 'Single YouTube and Vimeo videos work without a key. A key is only needed to load the videos of a YouTube playlist.', 'stackfolio' ) . '</p>';
		printf(
			'<p><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></p>',
			esc_url( 'https://developers.google.com/youtube/v3/getting-started' ),
			esc_html__( 'How to get a YouTube Data API key', 'stackfolio' )
		);
	}

	/**
	 * API key input.
	 */
	public static function api_key_field() {
		printf(
			'<input type="text" class="regular-text code" id="stackfolio-youtube-api-key" name="%s[youtube_api_key]" value="%s" autocomplete="off" />',
			esc_attr( self::OPTION ),
			esc_attr( self::get( 'youtube_api_key' ) )
		);
	}

	/**
	 * Render the settings page.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag set after a nonce-checked redirect.
			if ( isset( $_GET['stackfolio-cache-cleared'] ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Video cache cleared.', 'stackfolio' ) . '</p></div>';
			}
			?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'stackfolio' );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>

			<h2><?php esc_html_e( 'Video cache', 'stackfolio' ); ?></h2>
			<p><?php esc_html_e( 'Playlist contents and Vimeo thumbnails are cached for 12 hours. Clear the cache to load changes right away.', 'stackfolio' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="stackfolio_clear_cache" />
				<?php wp_nonce_field( 'stackfolio_clear_cache' ); ?>
				<?php submit_button( __( 'Clear video cache', 'stackfolio' ), 'secondary', 'submit', false ); ?>
			</form>

			<h2><?php esc_html_e( 'Shortcode', 'stackfolio' ); ?></h2>
			<p><?php esc_html_e( 'Use the StackFolio block or Elementor widget, or add this shortcode anywhere:', 'stackfolio' ); ?></p>
			<p><code>[stackfolio columns="3" filters="yes" category="weddings,events"]</code></p>
		</div>
		<?php
	}

	/**
	 * Clear cached remote video data.
	 */
	public static function clear_cache() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'stackfolio' ) );
		}
		check_admin_referer( 'stackfolio_clear_cache' );
		StackFolio_Videos::flush_cache();
		wp_safe_redirect(
			add_query_arg(
				array(
					'post_type'                => StackFolio_Post_Type::POST_TYPE,
					'page'                     => self::PAGE,
					'stackfolio-cache-cleared' => 1,
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}

	/**
	 * "Settings" link on the Plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$url = add_query_arg(
			array(
				'post_type' => StackFolio_Post_Type::POST_TYPE,
				'page'      => self::PAGE,
			),
			admin_url( 'edit.php' )
		);
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'stackfolio' ) . '</a>' );
		return $links;
	}
}
