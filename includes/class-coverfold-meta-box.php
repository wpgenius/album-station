<?php
/**
 * Album edit screen meta box.
 *
 * @package Coverfold
 */

defined( 'ABSPATH' ) || exit;

/**
 * "Album contents" meta box: media picker, video links and order.
 */
class Coverfold_Meta_Box {

	const NONCE = 'coverfold_album_nonce';

	/**
	 * Hook everything up.
	 */
	public static function init() {
		add_action( 'add_meta_boxes_' . Coverfold_Post_Type::POST_TYPE, array( __CLASS__, 'add' ) );
		add_action( 'save_post_' . Coverfold_Post_Type::POST_TYPE, array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Register the meta box.
	 */
	public static function add() {
		add_meta_box(
			'coverfold-album-contents',
			__( 'Album contents', 'coverfold' ),
			array( __CLASS__, 'render' ),
			Coverfold_Post_Type::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Admin assets, only on the album edit screen.
	 *
	 * @param string $hook Current admin page.
	 */
	public static function enqueue( $hook ) {
		$screen = get_current_screen();
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! $screen || Coverfold_Post_Type::POST_TYPE !== $screen->post_type ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'coverfold-admin', COVERFOLD_URL . 'assets/css/coverfold-admin.css', array(), COVERFOLD_VERSION );
		wp_enqueue_script( 'coverfold-admin', COVERFOLD_URL . 'assets/js/coverfold-admin.js', array( 'jquery', 'jquery-ui-sortable' ), COVERFOLD_VERSION, true );
		wp_localize_script(
			'coverfold-admin',
			'coverfoldAdmin',
			array(
				'frameTitle'  => __( 'Add photos and videos to this album', 'coverfold' ),
				'frameButton' => __( 'Add to album', 'coverfold' ),
				'remove'      => __( 'Remove', 'coverfold' ),
				'video'       => __( 'Video', 'coverfold' ),
			)
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post Album.
	 */
	public static function render( $post ) {
		wp_nonce_field( 'coverfold_save_album', self::NONCE );

		$media_ids = Coverfold_Album::get_media_ids( $post->ID );
		$links     = Coverfold_Album::get_video_links( $post->ID );
		$position  = Coverfold_Album::get_videos_position( $post->ID );
		$has_key   = '' !== Coverfold_Settings::get( 'youtube_api_key' );
		?>
		<div class="coverfold-metabox">
			<h3 class="coverfold-metabox__heading"><?php esc_html_e( 'Photos and videos', 'coverfold' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Pick images and videos from the Media Library. Drag to reorder. The featured image is the album cover.', 'coverfold' ); ?></p>

			<input type="hidden" name="coverfold_media" id="coverfold-media" value="<?php echo esc_attr( implode( ',', $media_ids ) ); ?>" />
			<ul class="coverfold-media-list" id="coverfold-media-list">
				<?php
				foreach ( $media_ids as $id ) {
					self::render_media_item( $id );
				}
				?>
			</ul>
			<p>
				<button type="button" class="button button-primary" id="coverfold-add-media"><?php esc_html_e( 'Add photos and videos', 'coverfold' ); ?></button>
				<button type="button" class="button-link button-link-delete" id="coverfold-clear-media" <?php echo $media_ids ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove all', 'coverfold' ); ?></button>
			</p>

			<h3 class="coverfold-metabox__heading"><label for="coverfold-video-links"><?php esc_html_e( 'Video links', 'coverfold' ); ?></label></h3>
			<p class="description">
				<?php esc_html_e( 'One link per line. YouTube videos, YouTube playlists and Vimeo videos are supported.', 'coverfold' ); ?>
				<?php if ( ! $has_key ) : ?>
					<br />
					<?php
					printf(
						/* translators: %s: link to the settings page. */
						esc_html__( 'YouTube playlists need an API key in %s.', 'coverfold' ),
						'<a href="' . esc_url( admin_url( 'edit.php?post_type=' . Coverfold_Post_Type::POST_TYPE . '&page=' . Coverfold_Settings::PAGE ) ) . '">' . esc_html__( 'Settings', 'coverfold' ) . '</a>'
					);
					?>
				<?php endif; ?>
			</p>
			<textarea name="coverfold_video_links" id="coverfold-video-links" class="large-text code" rows="4" placeholder="https://www.youtube.com/watch?v=…&#10;https://www.youtube.com/playlist?list=…&#10;https://vimeo.com/…"><?php echo esc_textarea( implode( "\n", $links ) ); ?></textarea>
			<?php
			foreach ( $links as $link ) {
				if ( ! Coverfold_Videos::parse( $link ) ) {
					echo '<p class="coverfold-warning">' . esc_html(
						sprintf(
							/* translators: %s: the link that was not recognised. */
							__( 'Not recognised, will be skipped: %s', 'coverfold' ),
							$link
						)
					) . '</p>';
				}
			}
			?>

			<fieldset class="coverfold-position">
				<legend><?php esc_html_e( 'Show video links', 'coverfold' ); ?></legend>
				<label><input type="radio" name="coverfold_videos_position" value="after" <?php checked( 'after', $position ); ?> /> <?php esc_html_e( 'After the photos', 'coverfold' ); ?></label>
				<label><input type="radio" name="coverfold_videos_position" value="before" <?php checked( 'before', $position ); ?> /> <?php esc_html_e( 'Before the photos', 'coverfold' ); ?></label>
			</fieldset>
		</div>
		<?php
	}

	/**
	 * One thumbnail in the media list.
	 *
	 * @param int $id Attachment ID.
	 */
	private static function render_media_item( $id ) {
		$mime = (string) get_post_mime_type( $id );
		if ( ! $mime ) {
			return;
		}
		$is_video = 0 === strpos( $mime, 'video/' );
		?>
		<li class="coverfold-media-item<?php echo $is_video ? ' is-video' : ''; ?>" data-id="<?php echo esc_attr( (string) $id ); ?>">
			<?php
			if ( $is_video ) {
				echo '<span class="dashicons dashicons-format-video" aria-hidden="true"></span><span class="coverfold-media-item__name">' . esc_html( get_the_title( $id ) ) . '</span>';
			} else {
				echo wp_get_attachment_image( $id, 'thumbnail' );
			}
			?>
			<button type="button" class="coverfold-media-item__remove" aria-label="<?php esc_attr_e( 'Remove', 'coverfold' ); ?>">&times;</button>
		</li>
		<?php
	}

	/**
	 * Save album meta.
	 *
	 * @param int $post_id Album ID.
	 */
	public static function save( $post_id ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), 'coverfold_save_album' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$media = isset( $_POST['coverfold_media'] ) ? Coverfold_Album::parse_ids( sanitize_text_field( wp_unslash( $_POST['coverfold_media'] ) ) ) : array();
		update_post_meta( $post_id, Coverfold_Album::META_MEDIA, implode( ',', $media ) );

		$links = array();
		if ( isset( $_POST['coverfold_video_links'] ) ) {
			$raw = sanitize_textarea_field( wp_unslash( $_POST['coverfold_video_links'] ) );
			foreach ( preg_split( '/[\r\n]+/', $raw ) as $line ) {
				$line = trim( $line );
				if ( '' !== $line ) {
					$links[] = $line;
				}
			}
		}
		update_post_meta( $post_id, Coverfold_Album::META_VIDEOS, implode( "\n", $links ) );
		Coverfold_Videos::warm( $links );

		$position = isset( $_POST['coverfold_videos_position'] ) && 'before' === $_POST['coverfold_videos_position'] ? 'before' : 'after';
		update_post_meta( $post_id, Coverfold_Album::META_VIDEOS_POSITION, $position );
	}
}
