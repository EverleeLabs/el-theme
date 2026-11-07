<?php
/**
 * Theme updates from GitHub releases.
 *
 * WordPress (6.1+) passes any theme with an `Update URI` header on
 * github.com to the `update_themes_github.com` filter. We answer it with the
 * latest GitHub release and its el-theme.zip asset so the normal
 * "Update available" notices and one-click updates work.
 *
 * Release checklist: bump `Version:` in style.css, then publish a GitHub
 * release whose tag matches that version (e.g. 1.1.0). The release workflow
 * attaches el-theme.zip automatically.
 *
 * @package EL_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'EL_THEME_UPDATE_URI', 'https://github.com/EverleeLabs/el-theme' );
define( 'EL_THEME_GITHUB_REPO', 'EverleeLabs/el-theme' );
define( 'EL_THEME_RELEASE_ASSET', 'el-theme.zip' );

add_filter( 'update_themes_github.com', 'el_theme_check_for_update', 10, 3 );

/**
 * Supply update data for this theme.
 *
 * @param array|false $update           Update data, false if none yet.
 * @param array       $theme_data       Theme headers.
 * @param string      $theme_stylesheet Theme directory name.
 * @return array|false
 */
function el_theme_check_for_update( $update, $theme_data, $theme_stylesheet ) {
    // Other GitHub-hosted themes use the same hostname filter; only handle ours.
    if ( empty( $theme_data['UpdateURI'] ) || untrailingslashit( $theme_data['UpdateURI'] ) !== EL_THEME_UPDATE_URI ) {
        return $update;
    }

    $release = el_theme_get_latest_release();

    if ( ! $release ) {
        return $update;
    }

    return array(
        'theme'   => $theme_stylesheet,
        'version' => $release['version'],
        'url'     => $release['url'],
        'package' => $release['package'],
    );
}

/**
 * Fetch the latest GitHub release, cached to stay under the API rate limit
 * (60 unauthenticated requests per hour, shared by every site on an IP).
 *
 * @return array|false Array with version, url and package, or false.
 */
function el_theme_get_latest_release() {
    $cache_key = 'el_theme_github_release';

    // "Check again" on Dashboard > Updates bypasses the cache.
    $force = is_admin() && isset( $_GET['force-check'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ( ! $force ) {
        $cached = get_site_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached ? $cached : false;
        }
    }

    $response = wp_remote_get(
        'https://api.github.com/repos/' . EL_THEME_GITHUB_REPO . '/releases/latest',
        array(
            'timeout' => 10,
            'headers' => array( 'Accept' => 'application/vnd.github+json' ),
        )
    );

    $release = false;

    if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( is_array( $body ) && ! empty( $body['tag_name'] ) && ! empty( $body['assets'] ) ) {
            foreach ( $body['assets'] as $asset ) {
                if ( isset( $asset['name'], $asset['browser_download_url'] ) && EL_THEME_RELEASE_ASSET === $asset['name'] ) {
                    $release = array(
                        'version' => ltrim( $body['tag_name'], 'vV' ),
                        'url'     => $body['html_url'],
                        'package' => $asset['browser_download_url'],
                    );
                    break;
                }
            }
        }
    }

    // Cache misses briefly (store '' since false means "not cached") so a
    // GitHub outage or a release still building doesn't trigger constant requests.
    set_site_transient( $cache_key, $release ? $release : '', $release ? 6 * HOUR_IN_SECONDS : 30 * MINUTE_IN_SECONDS );

    return $release;
}

add_filter( 'upgrader_source_selection', 'el_theme_fix_update_folder_name', 10, 4 );

/**
 * Keep the existing folder name when updating.
 *
 * The release zip contains an `el-theme/` folder. If a site installed the
 * theme under a different folder name, rename the unpacked source to match so
 * the update replaces the installed theme instead of adding a second copy.
 *
 * @param string      $source        Unpacked source path.
 * @param string      $remote_source Remote source path.
 * @param WP_Upgrader $upgrader      Upgrader instance.
 * @param array       $hook_extra    Extra arguments.
 * @return string|WP_Error
 */
function el_theme_fix_update_folder_name( $source, $remote_source, $upgrader, $hook_extra = array() ) {
    global $wp_filesystem;

    if ( empty( $hook_extra['theme'] ) || ! $wp_filesystem ) {
        return $source;
    }

    $theme = wp_get_theme( $hook_extra['theme'] );

    if ( ! $theme->exists() || EL_THEME_UPDATE_URI !== untrailingslashit( (string) $theme->get( 'UpdateURI' ) ) ) {
        return $source;
    }

    $desired = trailingslashit( $remote_source ) . $hook_extra['theme'];

    if ( untrailingslashit( $source ) === $desired ) {
        return $source;
    }

    if ( $wp_filesystem->move( untrailingslashit( $source ), $desired, true ) ) {
        return trailingslashit( $desired );
    }

    return new WP_Error( 'el_theme_rename_failed', __( 'Unable to prepare the theme update folder.', 'el-theme' ) );
}
