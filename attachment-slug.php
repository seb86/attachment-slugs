<?php
/*
 * Plugin Name: Attachment Slugs for WordPress
 * Plugin URI:  https://wordpress.org/plugins/attachment-slug/
 * Description: Enables WordPress to allow attachments to have their own permalink structure.
 * Version:     1.0.0
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Text Domain: attachment-slug
 * Domain Path: /languages/
 *
 * Copyright:   © 2018 Sébastien Dumont
 * License:     GNU General Public License v2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! class_exists( 'Attachment_Slug' ) ) {
	class Attachment_Slug {

		/**
		 * @var Attachment_Slug - the single instance of the class.
		 *
		 * @access protected
		 * @static
		 */
		protected static $_instance = null;

		/**
		 * Plugin Version
		 *
		 * @access public
		 * @static
		 */
		public static $version = '1.0.0';

		/**
		 * Main Attachment_Slug Instance.
		 *
		 * Ensures only one instance of Attachment_Slug is loaded or can be loaded.
		 *
		 * @access public
		 * @static
		 * @see    Attachment_Slug()
		 * @return Attachment_Slug - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @access public
		 * @return void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning this object is forbidden.', 'attachment-slug' ), self::$version );
		} // END __clone()

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @access public
		 * @return void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'attachment-slug' ), self::$version );
		} // END __wakeup()

		/**
		 * Load the plugin.
		 *
		 * @access public
		 */
		public function __construct() {
			// Check WordPress enviroment.
			add_action( 'admin_init', array( $this, 'check_wp' ), 12 );

			// Permalink settings for attachments.
			add_action( 'admin_init', array( $this, 'initialize_permalink_settings' ) );

			// Filters the permalink for attachments.
			add_filter( 'attachment_link', array( $this, 'attachment_link'), 10, 2 );

			// Adds the rewrite rule for attachments.
			add_action( 'init', array( $this, 'add_rewrite_rule' ), 10, 0 );

			// Load plugin textdomain.
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Edit and Save Attachment.
			if ( is_admin() ) {
				add_action( 'attachment_submitbox_misc_actions', array( $this, 'individual_slug' ), 20 );
				add_action( 'attachment_updated', array( $this, 'save_individual_slug' ), 10, 1 );
			}
		} // END __construct()

		/**
		 * Checks that the WordPress version meets the plugin requirement.
		 *
		 * @access public
		 * @since  1.0.0
		 * @global string $wp_version
		 * @return bool
		 */
		public function check_wp() {
			global $wp_version;

			if ( ! version_compare( $wp_version, '4.4', '>=' ) ) {
				add_action( 'admin_notices', array( $this, 'requirement_wp_notice' ) );
				return false;
			}

			return true;
		} // END check_wp()

		/**
		 * Show the WordPress requirement notice.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function requirement_wp_notice() {
			include( dirname( __FILE__ ) . '/admin/views/html-notice-requirement-wp.php' );
		} // END requirement_wp_notice()

		/** 
		 * Includes the permalink settings.
		 *
		 * @access public
		 */
		public function initialize_permalink_settings() {
			include_once( dirname( __FILE__ ) . '/admin/class-as-permalink-settings.php' );
		} // END initialize_permalink_settings()

		/**
		 * Filters the attachment links
		 *
		 * @access public
		 * @return string $link
		 */
		public function attachment_link( $link, $post_id ){
			$permalink = get_option( 'attachment_permalink' );

			$attachment_slug = $permalink['base']; // Attachment base or custom base.

			if ( ! empty( $attachment_slug ) ) {
				$post = get_post( $post_id );
				$link = home_url( '/' . $attachment_slug . '/' . $post->post_name . '/' );
			}

			return $link;
		} // END attachment_link()

		/**
		 * Add the Attachment Slug rewrite rule
		 *
		 * @access public
		 * @return void
		 */
		public function add_rewrite_rule() {
			$permalink = get_option( 'attachment_permalink' );

			$attachment_slug = trim( $permalink['base'], '/' ); // Attachment base or custom base.

			if ( ! empty( $attachment_slug ) ) {
				// Add the rewrite rule.
				add_rewrite_rule('^' . $attachment_slug . '/([^/]*)/?', 'index.php?attachment=$matches[1]', 'top');
			}
		} // END add_rewrite_rule()

		/**
		 * Make the plugin translation ready.
		 *
		 * Translations should be added in the WordPress language directory:
		 *      - WP_LANG_DIR/plugins/attachment-slug-LOCALE.mo
		 *
		 * @access public
		 * @return void
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'attachment-slug', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		} // END load_plugin_textdomain()

		/**
		 * Adds a text field to allow an individual attachment 
		 * to have a unique slug.
		 *
		 * @access public
		 * @since  1.0.0
		 * @global object $post WP_Post
		 * @return void
 		 */
		public function individual_slug() {
			global $post;

			$slug = $post->post_name;

			echo '<div class="misc-pub-section"><h4 style="font-size: 14px; margin-top: 5px; margin-bottom: 0;">' . __( 'Attachment Slug', 'attachment-slug' ) . '</h4></div>';

			echo '<div class="misc-pub-section">' . esc_attr__( 'Here you can change the permalink slug for this attachment.', 'attachment-slug' ) . '</div>';

			echo '<div class="misc-pub-section misc-pub-attachment-slug"><label for="attachment_slug">' . __( 'Attachment Slug', 'attachment-slug' ) . ':</label>
				<input type="text" class="widefat urlfield" name="attachment_slug" id="attachment_slug" value="' . $slug . '">
			</div>';
		} // END individual_slug()

		/**
		 * Saves the individual slug for the attachment
		 *
		 * @access public
		 * @since  1.0.0
		 * @param  int $post_ID Post ID.
		 * @global     $wpdb WordPress database abstraction object.
		 */
		public function save_individual_slug( $post_ID ) {
			global $wpdb;

			$new_slug = isset( $_POST['attachment_slug'] ) ? trim( strip_tags( sanitize_title( $_POST['attachment_slug'] ) ) ) : '';

			if ( ! empty( $new_slug ) ) {
				$wpdb->query( $wpdb->prepare("
					UPDATE $wpdb->posts
					SET post_name = %s
					WHERE ID = %d
					",
					$new_slug,
					$post_ID
				) );
			}
		} // END save_individual_slug()

	} // END class

} // END if class exists

return Attachment_Slug::instance();
