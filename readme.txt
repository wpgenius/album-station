=== Coverfold Album Gallery ===
Contributors: mkrndmane, wpgenius
Tags: gallery, album, portfolio, lightbox, video gallery
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Stunning photo and video albums. One cover image opens the full album in a popup, with a filterable grid for Elementor and Gutenberg.

== Description ==

**Coverfold** shows your work as a grid of album covers. Click a cover and the whole album opens in a lightbox, with photos and videos side by side.

It suits photographers, studios, agencies, event organisers, schools and anyone who has more pictures than fit on one page.

= How it works =

1. Create an album under **Coverfold → Add New Album**.
2. Set a featured image as the album cover.
3. Add photos and videos from the Media Library, and paste YouTube or Vimeo links.
4. Show your albums with the **Coverfold Albums** block, the Elementor widget or the `[coverfold]` shortcode.

= Features =

* **Albums, not single images.** Each album is a stack of photos and videos behind one cover.
* **Photos and videos together.** Media Library images and MP4/WebM videos, YouTube videos, YouTube playlists and Vimeo videos.
* **Lightbox** powered by Foldbox, with thumbnails, zoom, fullscreen, slideshow, captions, swipe and keyboard support.
* **Category filters** with smooth animations. Only categories that have albums in the grid are shown.
* **Gutenberg block** with live preview.
* **Elementor widget** with full style controls for cards, titles and filter buttons.
* **Shortcode** for any other page builder or theme.
* **Responsive columns** for desktop, tablet and mobile.
* **Drag-and-drop ordering** of photos and videos inside an album.
* **Accessible:** keyboard-friendly cards and filter buttons.
* **Lightweight:** scripts load only on pages that show albums.
* **Translation ready.**

= Shortcode =

`[coverfold]`

Options:

* `category` – category slugs or IDs, comma separated
* `ids` – album IDs, comma separated
* `number` – how many albums (default: all)
* `orderby` – `date`, `title`, `menu_order`, `modified` or `rand`
* `order` – `DESC` or `ASC`
* `columns`, `columns_tablet`, `columns_mobile` – 1 to 6 (default 3 / 2 / 1)
* `gap` – spacing in pixels (default 20)
* `filters` – `yes` to show category filters
* `all`, `all_label` – show and label the "All" filter button
* `title`, `title_tag`, `title_align` – album titles
* `count` – `yes` to show "12 photos · 2 videos" on covers
* `videos`, `captions`, `thumbnails`, `zoom`, `fullscreen`, `slideshow`, `download` – lightbox features (`yes` / `no`)
* `autoplay` – `yes` to start the slideshow automatically
* `hover` – `zoom` or `none`

Example: `[coverfold category="weddings,events" columns="4" filters="yes" count="yes"]`

= Coming from Album Station? =

Coverfold is the new name of Album Station. When you activate Coverfold it moves your albums, categories, videos and YouTube API key over automatically. Pages built with the old Elementor widget or the `[feature_album]` shortcode keep working. Once you see the notice, deactivate and delete Album Station.

== External services ==

Coverfold connects to these services only when an album contains the matching video links:

* **YouTube** – Thumbnails load from `i.ytimg.com` when a visitor opens an album, and videos play in an embedded YouTube player (`youtube-nocookie.com`) once the visitor presses play. For YouTube playlists, your site calls the YouTube Data API (`googleapis.com`) with your API key and the playlist ID to list the videos; results are cached for 12 hours. [YouTube Terms of Service](https://www.youtube.com/t/terms), [Google Privacy Policy](https://policies.google.com/privacy).
* **Vimeo** – Your site calls Vimeo's oEmbed endpoint (`vimeo.com/api/oembed.json`) with the video URL to get its title and thumbnail; results are cached for 12 hours. Videos play in an embedded Vimeo player once the visitor presses play. [Vimeo Terms of Service](https://vimeo.com/terms), [Vimeo Privacy Policy](https://vimeo.com/privacy).

No data about your visitors is sent by the plugin itself. Embedded players are loaded by the visitor's browser only after they press play.

== Installation ==

1. Install Coverfold from **Plugins → Add New**, or upload the `coverfold` folder to `/wp-content/plugins/`.
2. Activate the plugin.
3. Go to **Coverfold → Add New Album** and create your first album.
4. Add the **Coverfold Albums** block to a page, use the Elementor widget, or paste `[coverfold]`.

== Frequently Asked Questions ==

= Which videos can I add? =

Videos uploaded to the Media Library (MP4 or WebM), YouTube videos, YouTube playlists and Vimeo videos. Paste one link per line in the **Video links** box of an album.

= Do I need a YouTube API key? =

Only for YouTube playlists. Single YouTube and Vimeo videos work without a key. Add the key under **Coverfold → Settings**.

= Can I put videos before the photos? =

Yes. Each album has a **Show video links** option: before or after the photos. Media Library videos follow the order you drag them into.

= How do I change the order of albums? =

Set the **Order** field in the album's **Page Attributes** box and choose "Custom order" in the block or widget, or use `orderby="menu_order" order="ASC"` in the shortcode.

= Where does the album cover come from? =

From the album's featured image. If there is none, the first photo in the album is used.

= Does it work with my theme? =

Yes. Coverfold uses its own small stylesheet and works with any theme. Colours can be changed in the Elementor widget, or with CSS custom properties such as `--coverfold-accent`.

= Are the albums public pages? =

No. Albums are shown only through the block, widget or shortcode. Developers can change this with the `coverfold_post_type_args` filter.

== Screenshots ==

1. Album grid with category filters.
2. An album open in the lightbox, with thumbnails.
3. Editing an album: drag-and-drop photos and videos, and video links.
4. The Coverfold Albums block settings.
5. The Elementor widget.

== Third-party libraries ==

* [Foldbox](https://github.com/wpgenius/foldbox) 1.1.1 – MIT

Unminified sources are available at the links above.

== Changelog ==

= 2.0.0 =

* Rename the plugin to Coverfold Album Gallery (slug coverfold) (#37)
* Replace Isotope with an MIT-licensed filter (#39)

= 1.0.5 =

* Album pages go empty if the plugin is activated while Album Station is still active (#43)
* Pages can load slowly when cached Vimeo and YouTube playlist data expires (#44)
* The lightbox loads full-size original images (#45)
* A fast double-click opens two lightboxes (#46)
* Vimeo video thumbnail is blank when Vimeo does not answer (#47)
* Album categories should be top-level only (#48)

= 1.0.4 =

* Category migration moves terms used by content that is not migrated (#40)
* Videos::parse() fatal error on array-valued URL parameters (#41)

= 1.0.3 =

* Plugin URI and Author URI must differ (#35)

= 1.0.2 =

* Dragging photos sideways in the album editor doesn't reorder them (#32)
* Uploaded videos without a poster look like a copy of the album cover (#33)

= 1.0.1 =

* Match the readme.txt plugin name to the plugin header (#28)
* Remove the Domain Path header (#29)
* Use lightGallery's default licence key (#30)

= 1.0.0 =

* Initial release.

= 1.0.0 =
* Album Station is now Coverfold, rebuilt from the ground up.
* New: Coverfold Albums block for the block editor.
* New: Media Library videos, cached YouTube playlists, and Vimeo titles and thumbnails.
* New: drag-and-drop photo and video ordering, responsive columns, "All" filter, photo and video count.
* Improved: Elementor widget with scoped style controls; several grids can share a page.
* Improved: security hardening and translation support throughout.
* Automatic migration from Album Station.

== Upgrade Notice ==

= 1.0.0 =
Album Station is now Coverfold. Your albums move over automatically when you activate Coverfold.
