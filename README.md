# StackFolio

**Stunning photo & video albums for WordPress.**

StackFolio shows a filterable grid of album covers. Click a cover and the whole album (photos, Media Library videos, YouTube, YouTube playlists and Vimeo) opens in a lightGallery popup. Use it as a Gutenberg block, an Elementor widget or the `[stackfolio]` shortcode.

StackFolio is the successor of Album Station and migrates its albums automatically.

## Structure

| Path | What it does |
| --- | --- |
| `stackfolio.php` | Plugin bootstrap |
| `includes/class-stackfolio-post-type.php` | `stackfolio_album` post type, `stackfolio_category` taxonomy |
| `includes/class-stackfolio-meta-box.php` | Album edit screen: media picker, video links |
| `includes/class-stackfolio-album.php` | Album data and lightbox items |
| `includes/class-stackfolio-videos.php` | YouTube / playlist / Vimeo parsing and cached remote data |
| `includes/class-stackfolio-renderer.php` | Shared grid markup for block, widget and shortcode |
| `includes/class-stackfolio-block.php`, `blocks/album-grid/` | Gutenberg block (plain JS, no build step) |
| `includes/elementor/` | Elementor widget (+ hidden Album Station compatibility widget) |
| `includes/class-stackfolio-shortcode.php` | `[stackfolio]` and legacy `[feature_album]` |
| `includes/class-stackfolio-migration.php` | One-time Album Station migration |
| `assets/vendor/` | lightGallery 2.9.0 and Isotope 3.0.6 (GPLv3), Vimeo Player API 2.30.4 (MIT) |

## Developer hooks

- `stackfolio_post_type_args`, `stackfolio_taxonomy_args` – change registration arguments
- `stackfolio_query_args` – change the album query
- `stackfolio_album_items` – change an album's lightbox items
- `stackfolio_grid_html` – change the grid markup
- `stackfolio_playlist_max_videos` – videos loaded per YouTube playlist (max 50)

## License

GPL-3.0-or-later. See `LICENSE`.
