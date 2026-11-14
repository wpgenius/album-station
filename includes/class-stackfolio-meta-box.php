<?php
/**
 * Album edit screen meta box.
 *
 * @package StackFolio
 */

defined( 'ABSPATH' ) || exit;

/**
 * "Album contents" meta box: media picker, video links and order.
 */
class StackFolio_Meta_Box {

	const NONCE = 'stackfolio_album_nonce';

	/**
	 * Hook everything up.
	 */
	public static function init() {
		add_action( 'add_meta_boxes_' . StackFolio_Post_Type::POST_TYPE, array( __CLASS__, 'add' ) );
		add_action( 'save_post_' . StackFolio_Post_Type::POST_TYPE, array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Register the meta box.
	 */
	public static function add() {
		add_meta_box(
			'stackfolio-album-contents',
			__( 'Album contents', 'stackfolio' ),
			array( __CLASS__, 'render' ),
			StackFolio_Post_Type::POST_TYPE,
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
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! $screen || StackFolio_Post_Type::POST_TYPE !== $screen->post_type ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'stackfolio-admin', STACKFOLIO_URL . 'assets/css/stackfolio-admin.css', array(), STACKFOLIO_VERSION );
		wp_enqueue_script( 'stackfolio-admin', STACKFOLIO_URL . 'assets/js/stackfolio-admin.js', array( 'jquery', 'jquery-ui-sortable' ), STACKFOLIO_VERSION, true );
		wp_localize_script(
			'stackfolio-admin',
			'stackfolioAdmin',
			array(
				'frameTitle'  => __( 'Add photos and videos to this album', 'stackfolio' ),
				'frameButton' => __( 'Add to album', 'stackfolio' ),
				'remove'      => __( 'Remove', 'stackfolio' ),
				'video'       => __( 'Video', 'stackfolio' ),
			)
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post Album.
	 */
	public static function render( $post ) {
		wp_nonce_field( 'stackfolio_save_album', self::NONCE );

		$media_ids = StackFolio_Album::get_media_ids( $post->ID );
		$links     = StackFolio_Album::get_video_links( $post->ID );
		$position  = StackFolio_Album::get_videos_position( $post->ID );
		$has_key   = '' !== StackFolio_Settings::get( 'youtube_api_key' );
		?>
		<div class="stackfolio-metabox">
			<h3 class="stackfolio-metabox__heading"><?php esc_html_e( 'Photos and videos', 'stackfolio' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Pick images and videos from the Media Library. Drag to reorder. The featured image is the album cover.', 'stackfolio' ); ?></p>

			<input type="hidden" name="stackfolio_media" id="stackfolio-media" value="<?php echo esc_attr( implode( ',', $media_ids ) ); ?>" />
			<ul class="stackfolio-media-list" id="stackfolio-media-list">
				<?php
				foreach ( $media_ids as $id ) {
					self::render_media_item( $id );
				}
				?>
			</ul>
			<p>
				<button type="button" class="button button-primary" id="stackfolio-add-media"><?php esc_html_e( 'Add photos and videos', 'stackfolio' ); ?></button>
				<button type="button" class="button-link button-link-delete" id="stackfolio-clear-media" <?php echo $media_ids ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove all', 'stackfolio' ); ?></button>
			</p>

			<h3 class="stackfolio-metabox__heading"><label for="stackfolio-video-links"><?php esc_html_e( 'Video links', 'stackfolio' ); ?></label></h3>
			<p class="description">
				<?php esc_html_e( 'One link per line. YouTube videos, YouTube playlists and Vimeo videos are supported.', 'stackfolio' ); ?>
				<?php if ( ! $has_key ) : ?>
					<br />
					<?php
					printf(
						/* translators: %s: link to the settings page. */
						esc_html__( 'YouTube playlists need an API key in %s.', 'stackfolio' ),
						'<a href="' . esc_url( admin_url( 'edit.php?post_type=' . StackFolio_Post_Type::POST_TYPE . '&page=' . StackFolio_Settings::PAGE ) ) . '">' . esc_html__( 'Settings', 'stackfolio' ) . '</a>'
					);
					?>
				<?php endif; ?>
			</p>
			<textarea name="stackfolio_video_links" id="stackfolio-video-links" class="large-text code" rows="4" placeholder="https://www.youtube.com/watch?v=…&#10;https://www.youtube.com/playlist?list=…&#10;https://vimeo.com/…"><?php echo esc_textarea( implode( "\n", $links ) ); ?></textarea>
			<?php
			foreach ( $links as $link ) {
				if ( ! StackFolio_Videos::parse( $link ) ) {
					echo '<p class="stackfolio-warning">' . esc_html(
						sprintf(
							/* translators: %s: the link that was not recognised. */
							__( 'Not recognised, will be skipped: %s', 'stackfolio' ),
							$link
						)
					) . '</p>';
				}
			}
			?>

			<fieldset class="stackfolio-position">
				<legend><?php esc_html_e( 'Show video links', 'stackfolio' ); ?></legend>
				<label><input type="radio" name="stackfolio_videos_position" value="after" <?php checked( 'after', $position ); ?> /> <?php esc_html_e( 'After the photos', 'stackfolio' ); ?></label>
				<label><input type="radio" name="stackfolio_videos_position" value="before" <?php checked( 'before', $position ); ?> /> <?php esc_html_e( 'Before the photos', 'stackfolio' ); ?></label>
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
		<li class="stackfolio-media-item<?php echo $is_video ? ' is-video' : ''; ?>" data-id="<?php echo esc_attr( $id ); ?>">
			<?php
			if ( $is_video ) {
				echo '<span class="dashicons dashicons-format-video" aria-hidden="true"></span><span class="stackfolio-media-item__name">' . esc_html( get_the_title( $id ) ) . '</span>';
			} else {
				echo wp_get_attachment_image( $id, 'thumbnail' );
			}
			?>
			<button type="button" class="stackfolio-media-item__remove" aria-label="<?php esc_attr_e( 'Remove', 'stackfolio' ); ?>">&times;</button>
		</li>
		<?php
	}

	/**
	 * Save album meta.
	 *
	 * @param int $post_id Album ID.
	 */
	public static function save( $post_id ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), 'stackfolio_save_album' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$media = isset( $_POST['stackfolio_media'] ) ? StackFolio_Album::parse_ids( sanitize_text_field( wp_unslash( $_POST['stackfolio_media'] ) ) ) : array();
		update_post_meta( $post_id, StackFolio_Album::META_MEDIA, implode( ',', $media ) );

		$links = array();
		if ( isset( $_POST['stackfolio_video_links'] ) ) {
			$raw = sanitize_textarea_field( wp_unslash( $_POST['stackfolio_video_links'] ) );
			foreach ( preg_split( '/[\r\n]+/', $raw ) as $line ) {
				$line = trim( $line );
				if ( '' !== $line ) {
					$links[] = $line;
				}
			}
		}
		update_post_meta( $post_id, StackFolio_Album::META_VIDEOS, implode( "\n", $links ) );

		$position = isset( $_POST['stackfolio_videos_position'] ) && 'before' === $_POST['stackfolio_videos_position'] ? 'before' : 'after';
		update_post_meta( $post_id, StackFolio_Album::META_VIDEOS_POSITION, $position );
	}
}
