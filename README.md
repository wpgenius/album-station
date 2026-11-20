# Coverfold Album Gallery

**Stunning photo & video albums for WordPress.**

[![Try it in your browser](https://img.shields.io/badge/WordPress_Playground-Try_it_in_your_browser-3858e9?logo=wordpress)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/wpgenius/coverfold/main/.wordpress-org/blueprints/blueprint.json)

Coverfold shows a filterable grid of album covers. Click a cover and the whole album (photos, Media Library videos, YouTube, YouTube playlists and Vimeo) opens in a lightGallery popup. Use it as a Gutenberg block, an Elementor widget or the `[coverfold]` shortcode.

Coverfold is the successor of Album Station and migrates its albums automatically.

## Structure

| Path | What it does |
| --- | --- |
| `coverfold.php` | Plugin bootstrap |
| `includes/class-coverfold-post-type.php` | `coverfold_album` post type, `coverfold_category` taxonomy |
| `includes/class-coverfold-meta-box.php` | Album edit screen: media picker, video links |
| `includes/class-coverfold-album.php` | Album data and lightbox items |
| `includes/class-coverfold-videos.php` | YouTube / playlist / Vimeo parsing and cached remote data |
| `includes/class-coverfold-renderer.php` | Shared grid markup for block, widget and shortcode |
| `includes/class-coverfold-block.php`, `blocks/album-grid/` | Gutenberg block (plain JS, no build step) |
| `includes/elementor/` | Elementor widget (+ hidden Album Station compatibility widget) |
| `includes/class-coverfold-shortcode.php` | `[coverfold]` and legacy `[feature_album]` |
| `includes/class-coverfold-migration.php` | One-time Album Station migration |
| `assets/vendor/` | lightGallery 2.9.0 (GPLv3), Vimeo Player API 2.30.4 (MIT) |

## Try it in your browser

[**Open the demo in WordPress Playground**](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/wpgenius/coverfold/main/.wordpress-org/blueprints/blueprint.json): it installs the latest release with six sample albums and a demo page. (Playground downloads `releases/latest/download/coverfold.zip`, so it needs at least one release.)

## How changes ship

```
issue  →  branch  →  pull request (Closes #N)  →  squash merge to main  →  automatic release  →  deploy (if enabled)
```

- **`main` is protected.** Changes go through pull requests, merged with squash. Repository admins can still bypass the rules.
- **Every PR closes an issue.** The *Linked issue* check fails a PR without `Closes #N`. One request with several changes = one issue per change, delivered in one PR.
- **CI** runs on every PR and must pass: PHP 7.4–8.5 syntax and unit tests, PHPCS, PHPStan, JS/CSS lint and WordPress Plugin Check. Plugin Check fails when `Tested up to` in `readme.txt` is behind the latest WordPress, so bump it after testing each new WordPress version.
- **Releases are automatic.** On each merge to `main`, the [Release workflow](.github/workflows/release.yml) releases only if the plugin zip would change (everything [`.distignore`](.distignore) doesn't exclude). It bumps the version (patch by default, or minor/major with the `release:minor` / `release:major` label), writes the changelog (one line per closed issue) and the version into `readme.txt` and the plugin header, commits that to `main`, tags it and publishes a GitHub release with `coverfold.zip`.
- **Deploys are off.** The [Deploy workflow](.github/workflows/deploy.yml) targets WordPress.org SVN (`DEPLOY_METHOD=wporg`) and runs only while the repo variable `DEPLOY_ENABLED` is `true`. Turn it on once the plugin is approved on WordPress.org and the `SVN_USERNAME` / `SVN_PASSWORD` secrets are set.

## Development

```bash
composer install && npm ci
```

| Command | What it checks |
|---|---|
| `composer lint` | PHP syntax |
| `composer phpcs` | WordPress Coding Standards (`composer phpcbf` fixes most issues) |
| `composer phpstan` | Static analysis |
| `composer test` | Unit tests |
| `npm run lint` | JS and CSS (Node 22.12 or newer) |

A local site: `cp .env.example .env && docker compose up -d`, then open http://localhost:8000.

## Developer hooks

- `coverfold_post_type_args`, `coverfold_taxonomy_args` – change registration arguments
- `coverfold_query_args` – change the album query
- `coverfold_album_items` – change an album's lightbox items
- `coverfold_grid_html` – change the grid markup
- `coverfold_playlist_max_videos` – videos loaded per YouTube playlist (max 50)

## License

GPL-3.0-or-later. See `LICENSE`.
