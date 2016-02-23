<?php
/*
 * Plugin Name:       Attachment Slug
 * Plugin URI:        https://github.com/seb86/Attachment-Slug
 * Description:       Enables you to add an attachment slug allowing the attachment posts to have it's own URL structure.
 * Version:           0.0.1
 * Author:            Sébastien Dumont
 * Author URI:        http://sebastiendumont.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       attachment-slug
 * Domain Path:       /languages/
 *
 * Attachment Slug is distributed under the terms of the
 * GNU General Public License as published by the Free Software Foundation,
 * either version 2 of the License, or any later version.
 *
 * Attachment Slug is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Attachment Slug.
 * If not, see <http://www.gnu.org/licenses/>.
 */
if ( ! defined('ABSPATH')) {
	exit;
}

function as_wp_initialize_permalink_settings() {
	include_once( 'permalink-settings.php' );
}
add_action( 'admin_init', 'as_wp_initialize_permalink_settings' );

function as_wp_attachment_link( $link, $post_id ){
	$permalink = get_option( 'attachment_permalink' );

	$attachment_slug = $permalink['base']; // Attachment base or custom base.

	if ( ! empty( $attachment_slug ) ) {
		$post = get_post( $post_id );
		$link = home_url( '/' . $attachment_slug . '/' . $post->post_name . '/' );
	}

	return $link;
}
add_filter( 'attachment_link', 'as_wp_attachment_link', 10, 2 );

/**
 * Add the Attachment Slug rewrite rule
 *
 * @return void
 */
function as_add_rewrite_rule() {
	$permalink = get_option( 'attachment_permalink' );

	$attachment_slug = trim( $permalink['base'], '/' ); // Attachment base or custom base.

	if ( ! empty( $attachment_slug ) ) {
		// Add the rewrite rule.
		add_rewrite_rule('^' . $attachment_slug . '/([^/]*)/?', 'index.php?attachment=$matches[1]', 'top');
	}
}
add_action( 'init', 'as_add_rewrite_rule', 10, 0 );

/**
 * Loads the plugin language files
 *
 * @filter attachment_slug_languages_directory
 * @filter plugin_locale
 * @return void
 */
function as_init_textdomain() {
	// Set filter for plugin's languages directory
	$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$lang_dir = apply_filters( 'attachment_slug_languages_directory', $lang_dir );

	// Traditional WordPress plugin locale filter
	$locale = apply_filters( 'plugin_locale',  get_locale(), 'woocommerce-skip-one' );
	$mofile = sprintf( '%1$s-%2$s.mo', 'attachment-slug', $locale );

	// Setup paths to current locale file
	$mofile_local  = $lang_dir . $mofile;
	$mofile_global = WP_LANG_DIR . '/attachment-slug/' . $mofile;

	if ( file_exists( $mofile_global ) ) {
		// Look in global /wp-content/languages/attachment-slug folder
		load_textdomain( 'attachment-slug', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) {
		// Look in local /wp-content/plugins/attachment-slug/languages/ folder
		load_textdomain( 'attachment-slug', $mofile_local );
	} else {
		// Load the default language files
		load_plugin_textdomain( 'attachment-slug', false, $lang_dir );
	}
} // END as_init_textdomain()
add_action( 'init', 'as_init_textdomain' );