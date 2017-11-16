<?php
/*
 * Plugin Name: Attachment Slugs for WordPress
 * Plugin URI:  https://wordpress.org/plugins/attachment-slug/
 * Description: Enables you to add an attachment slug allowing the attachment posts to have it's own URL structure.
 * Version:     0.0.2
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Text Domain: attachment-slug
 * Domain Path: /languages/
 *
 * Copyright:   © 2017 Sébastien Dumont
 * License:     GNU General Public License v2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
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
	$locale = apply_filters( 'plugin_locale',  get_locale(), 'attachment-slug' );
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
