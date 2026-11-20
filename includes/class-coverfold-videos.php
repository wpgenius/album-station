<?php
/**
 * Video link parsing and remote video data.
 *
 * @package Coverfold
 */

defined( 'ABSPATH' ) || exit;

/**
 * Turns YouTube / YouTube playlist / Vimeo links into Foldbox lightbox items.
 */
class Coverfold_Videos {

	const CACHE_TTL       = 12 * HOUR_IN_SECONDS;
	const ERROR_CACHE_TTL = HOUR_IN_SECONDS;
	// Old data is kept this long and shown while a fresh copy is fetched in the background.
	const STALE_TTL    = 30 * DAY_IN_SECONDS;
	const REFRESH_HOOK = 'coverfold_refresh_video_data';

	/**
	 * Hook everything up.
	 */
	public static function init() {
		add_action( self::REFRESH_HOOK, array( __CLASS__, 'refresh' ), 10, 2 );
	}

	/**
	 * Background refresh of one cached entry (WP-Cron).
	 *
	 * @param string $type playlist|vimeo.
	 * @param string $id   Playlist ID or Vimeo URL.
	 */
	public static function refresh( $type, $id ) {
		if ( 'playlist' === $type ) {
			self::playlist_videos( (string) $id, true );
		} elseif ( 'vimeo' === $type ) {
			self::vimeo_data( (string) $id, true );
		}
	}

	/**
	 * Fetch any missing remote data for these links now, e.g. when an album is
	 * saved, so visitors don't wait for it.
	 *
	 * @param string[] $links Video links.
	 */
	public static function warm( array $links ) {
		self::to_items( $links );
	}

	/**
	 * Read a cached entry. Expired entries are still returned, and a background
	 * refresh is scheduled.
	 *
	 * @param string $cache_key Transient key.
	 * @param string $type      playlist|vimeo.
	 * @param string $id        Playlist ID or Vimeo URL.
	 * @return array|null Cached data, or null when there is none.
	 */
	private static function cached( $cache_key, $type, $id ) {
		$entry = get_transient( $cache_key );
		if ( ! is_array( $entry ) || ! isset( $entry['data'], $entry['fresh_until'] ) || ! is_array( $entry['data'] ) ) {
			return null;
		}
		if ( (int) $entry['fresh_until'] < time() && ! wp_next_scheduled( self::REFRESH_HOOK, array( $type, $id ) ) ) {
			wp_schedule_single_event( time(), self::REFRESH_HOOK, array( $type, $id ) );
		}
		return $entry['data'];
	}

	/**
	 * Store remote data. A failed fetch keeps the previous data, if any.
	 *
	 * @param string     $cache_key Transient key.
	 * @param array|null $data      Fetched data, or null when the request failed.
	 * @param array      $fallback  Data to use when nothing was ever fetched.
	 * @return array Data to use now.
	 */
	private static function store( $cache_key, $data, array $fallback ) {
		if ( null === $data ) {
			$entry = get_transient( $cache_key );
			$data  = ( is_array( $entry ) && isset( $entry['data'] ) && is_array( $entry['data'] ) ) ? $entry['data'] : $fallback;
			$ttl   = self::ERROR_CACHE_TTL;
		} else {
			$ttl = self::CACHE_TTL;
		}
		set_transient(
			$cache_key,
			array(
				'data'        => $data,
				'fresh_until' => time() + $ttl,
			),
			self::STALE_TTL
		);
		return $data;
	}

	/**
	 * Timeout for remote video requests.
	 *
	 * @return int Seconds.
	 */
	private static function timeout() {
		/**
		 * Filters the timeout of Vimeo and YouTube requests, in seconds.
		 *
		 * @param int $timeout Seconds.
		 */
		return max( 1, (int) apply_filters( 'coverfold_remote_timeout', 3 ) );
	}

	/**
	 * Work out what a single line of the "Video links" field points to.
	 *
	 * @param string $link URL or bare ID.
	 * @return array|null { type: youtube|youtube_playlist|vimeo, id: string, url: string } or null.
	 */
	public static function parse( $link ) {
		$link = trim( (string) $link );
		if ( '' === $link ) {
			return null;
		}

		// Bare YouTube video ID (11 chars), as stored by older versions.
		if ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $link ) ) {
			return self::youtube( $link );
		}

		// Bare YouTube playlist ID.
		if ( preg_match( '/^(PL|UU|OL|FL|LL)[A-Za-z0-9_-]{10,}$/', $link ) ) {
			return self::playlist( $link );
		}

		if ( ! preg_match( '#^https?://#i', $link ) ) {
			$link = 'https://' . ltrim( $link, '/' );
		}

		$parts = wp_parse_url( $link );
		if ( empty( $parts['host'] ) ) {
			return null;
		}
		$host  = strtolower( preg_replace( '/^(www\.|m\.)/i', '', $parts['host'] ) );
		$path  = isset( $parts['path'] ) ? $parts['path'] : '';
		$query = array();
		if ( ! empty( $parts['query'] ) ) {
			parse_str( $parts['query'], $query );
			// `?v[]=x` gives an array; only plain string values are usable.
			$query = array_filter( $query, 'is_string' );
		}

		if ( 'youtu.be' === $host ) {
			$id = trim( $path, '/' );
			return preg_match( '/^[A-Za-z0-9_-]{11}$/', $id ) ? self::youtube( $id ) : null;
		}

		if ( in_array( $host, array( 'youtube.com', 'youtube-nocookie.com', 'music.youtube.com' ), true ) ) {
			if ( ! empty( $query['v'] ) && preg_match( '/^[A-Za-z0-9_-]{11}$/', $query['v'] ) ) {
				return self::youtube( $query['v'] );
			}
			if ( preg_match( '#^/(?:embed|shorts|live|v)/([A-Za-z0-9_-]{11})#', $path, $m ) ) {
				return self::youtube( $m[1] );
			}
			if ( ! empty( $query['list'] ) && preg_match( '/^[A-Za-z0-9_-]+$/', $query['list'] ) ) {
				return self::playlist( $query['list'] );
			}
			return null;
		}

		if ( in_array( $host, array( 'vimeo.com', 'player.vimeo.com' ), true ) ) {
			if ( preg_match( '#/(?:video/)?(\d{5,})(?:/([A-Za-z0-9]+))?#', $path, $m ) ) {
				$hash = ! empty( $m[2] ) ? $m[2] : ( isset( $query['h'] ) ? sanitize_key( $query['h'] ) : '' );
				$url  = 'https://vimeo.com/' . $m[1] . ( $hash ? '/' . $hash : '' );
				return array(
					'type' => 'vimeo',
					'id'   => $m[1],
					'url'  => $url,
				);
			}
		}

		return null;
	}

	/**
	 * Build a YouTube video descriptor.
	 *
	 * @param string $id Video ID.
	 * @return array
	 */
	private static function youtube( $id ) {
		return array(
			'type' => 'youtube',
			'id'   => $id,
			'url'  => 'https://www.youtube.com/watch?v=' . $id,
		);
	}

	/**
	 * Build a YouTube playlist descriptor.
	 *
	 * @param string $id Playlist ID.
	 * @return array
	 */
	private static function playlist( $id ) {
		return array(
			'type' => 'youtube_playlist',
			'id'   => $id,
			'url'  => 'https://www.youtube.com/playlist?list=' . $id,
		);
	}

	/**
	 * Convert video links into Foldbox lightbox items.
	 *
	 * @param string[] $links Links, one per entry.
	 * @return array[]
	 */
	public static function to_items( array $links ) {
		$items = array();
		foreach ( $links as $link ) {
			$video = self::parse( $link );
			if ( ! $video ) {
				continue;
			}
			switch ( $video['type'] ) {
				case 'youtube':
					$items[] = array(
						'src'   => $video['url'],
						'thumb' => self::youtube_thumb( $video['id'] ),
					);
					break;

				case 'youtube_playlist':
					foreach ( self::playlist_videos( $video['id'] ) as $entry ) {
						$items[] = array(
							'src'     => 'https://www.youtube.com/watch?v=' . $entry['id'],
							'thumb'   => $entry['thumb'] ? $entry['thumb'] : self::youtube_thumb( $entry['id'] ),
							'caption' => $entry['title'] ? '<h4>' . esc_html( $entry['title'] ) . '</h4>' : '',
						);
					}
					break;

				case 'vimeo':
					$data    = self::vimeo_data( $video['url'] );
					$items[] = array(
						'src'     => $video['url'],
						'thumb'   => $data['thumb'] ? $data['thumb'] : COVERFOLD_URL . 'assets/images/video-thumb.svg',
						'caption' => $data['title'] ? '<h4>' . esc_html( $data['title'] ) . '</h4>' : '',
					);
					break;
			}
		}
		return $items;
	}

	/**
	 * YouTube thumbnail URL (always available in this size).
	 *
	 * @param string $id Video ID.
	 * @return string
	 */
	public static function youtube_thumb( $id ) {
		return 'https://i.ytimg.com/vi/' . rawurlencode( $id ) . '/hqdefault.jpg';
	}

	/**
	 * Videos of a YouTube playlist (cached). Needs the API key from settings.
	 *
	 * @param string $playlist_id Playlist ID.
	 * @param bool   $force       Skip the cache and fetch now.
	 * @return array[] Each { id, title, thumb }.
	 */
	public static function playlist_videos( $playlist_id, $force = false ) {
		$api_key = Coverfold_Settings::get( 'youtube_api_key' );
		if ( '' === $api_key ) {
			return array();
		}

		$cache_key = self::cache_key( 'yt_pl_' . $playlist_id );
		$cached    = $force ? null : self::cached( $cache_key, 'playlist', $playlist_id );
		if ( null !== $cached ) {
			return $cached;
		}

		/**
		 * Filters how many videos are loaded from a YouTube playlist (max 50).
		 *
		 * @param int    $max         Number of videos.
		 * @param string $playlist_id Playlist ID.
		 */
		$max = min( 50, max( 1, (int) apply_filters( 'coverfold_playlist_max_videos', 50, $playlist_id ) ) );

		$url = add_query_arg(
			array(
				'part'       => 'snippet,status',
				'maxResults' => $max,
				'playlistId' => rawurlencode( $playlist_id ),
				'key'        => rawurlencode( $api_key ),
			),
			'https://www.googleapis.com/youtube/v3/playlistItems'
		);

		$response = wp_remote_get( $url, array( 'timeout' => self::timeout() ) );
		$videos   = null;

		if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$body   = json_decode( wp_remote_retrieve_body( $response ), true );
			$videos = array();
			if ( ! empty( $body['items'] ) && is_array( $body['items'] ) ) {
				foreach ( $body['items'] as $item ) {
					$snippet = isset( $item['snippet'] ) ? $item['snippet'] : array();
					$status  = isset( $item['status']['privacyStatus'] ) ? $item['status']['privacyStatus'] : 'public';
					if ( empty( $snippet['resourceId']['videoId'] ) || 'private' === $status || empty( $snippet['thumbnails'] ) ) {
						continue; // Private or deleted videos.
					}
					$videos[] = array(
						'id'    => sanitize_text_field( $snippet['resourceId']['videoId'] ),
						'title' => isset( $snippet['title'] ) ? sanitize_text_field( $snippet['title'] ) : '',
						'thumb' => isset( $snippet['thumbnails']['medium']['url'] ) ? esc_url_raw( $snippet['thumbnails']['medium']['url'] ) : '',
					);
				}
			}
		}

		return self::store( $cache_key, $videos, array() );
	}

	/**
	 * Vimeo title and thumbnail via oEmbed (cached).
	 *
	 * @param string $url   Vimeo URL.
	 * @param bool   $force Skip the cache and fetch now.
	 * @return array { title, thumb }
	 */
	public static function vimeo_data( $url, $force = false ) {
		$cache_key = self::cache_key( 'vimeo_' . md5( $url ) );
		$cached    = $force ? null : self::cached( $cache_key, 'vimeo', $url );
		if ( null !== $cached ) {
			return $cached;
		}

		$empty    = array(
			'title' => '',
			'thumb' => '',
		);
		$data     = null;
		$response = wp_remote_get(
			add_query_arg(
				array(
					'url'   => rawurlencode( $url ),
					'width' => 640,
				),
				'https://vimeo.com/api/oembed.json'
			),
			array( 'timeout' => self::timeout() )
		);

		if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			$data = $empty;
			if ( is_array( $body ) ) {
				$data['title'] = isset( $body['title'] ) ? sanitize_text_field( $body['title'] ) : '';
				$data['thumb'] = isset( $body['thumbnail_url'] ) ? esc_url_raw( $body['thumbnail_url'] ) : '';
			}
		}

		return self::store( $cache_key, $data, $empty );
	}

	/**
	 * Versioned cache key, so the whole cache can be invalidated at once.
	 *
	 * @param string $key Base key.
	 * @return string
	 */
	private static function cache_key( $key ) {
		$version = (int) get_option( 'coverfold_cache_version', 1 );
		// "v2": entries now hold { data, fresh_until }.
		return 'coverfold_' . $version . '_' . md5( 'v2_' . $key );
	}

	/**
	 * Invalidate all cached remote video data.
	 */
	public static function flush_cache() {
		update_option( 'coverfold_cache_version', (int) get_option( 'coverfold_cache_version', 1 ) + 1, false );
	}
}
