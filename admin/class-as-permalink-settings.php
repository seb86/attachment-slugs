<?php
/**
 * Adds settings to the permalinks admin settings page.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  Attachment Slugs/Admin
 * @since    0.0.1
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Attachment_Slug_Admin_Permalink_Settings' ) ) {

	class Attachment_Slug_Admin_Permalink_Settings {

		/**
		 * Permalink settings.
		 *
		 * @var array
		 */
		private $permalinks = array();

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->settings_init();
			$this->settings_save();
		} // END __construct()

		/**
		 * Add a section to the permalinks page.
		 *
		 * @access public
		 */
		public function settings_init() {
			add_settings_section( 'attachment-permalink', __( 'Attachment permalinks', 'attachment-slug' ), array( $this, 'settings' ), 'permalink' );

			$this->permalinks = Attachment_Slug::aswp_get_permalink_structure();
		} // END settings_init()

		/**
		 * Clean variables using sanitize_text_field.
		 *
		 * @access public
		 *
		 * @param string|array $var
		 *
		 * @return string|array
		 */
		public function aswp_as_clean( $var ) {
			return is_array( $var ) ? array_map( 'aswp_as_clean', $var ) : sanitize_text_field( $var );
		} // END aswp_as_clean()

		/**
		 * Show the settings.
		 *
		 * @access public
		 */
		public function settings() {
			echo wpautop( __( 'These settings control the permalink used specifically for attachments.', 'attachment-slug' ) );

			$base_slug = apply_filters( 'aswp_as_base_slug', 'image' );

			$structures = array(
				0 => '',
				1 => trailingslashit( $base_slug ),
			);
			?>
			<table class="form-table ac-as-permalink-structure">
				<tbody>
					<tr>
						<th><label><input name="attachment_permalink" type="radio" value="<?php echo esc_attr( $structures[0] ); ?>" class="astog" <?php checked( $structures[0], $this->permalinks['attachment_base'] ); ?> /> <?php _e( 'Default', 'attachment-slug' ); ?></label></th>
						<td><code class="default-example"><?php echo esc_html( home_url() ); ?>/?attachment=1</code> <code class="non-default-example"><?php echo esc_html( home_url() ); ?>/sample-attachment/</code></td>
					</tr>
					<tr>
						<th><label><input name="attachment_permalink" type="radio" value="<?php echo esc_attr( $structures[1] ); ?>" class="astog" <?php checked( $structures[1], trailingslashit( $this->permalinks['attachment_base'] ) ); ?> /> <?php _e( 'Attachment base', 'attachment-slug' ); ?></label></th>
						<td><code><?php echo esc_html( home_url() ); ?>/<?php echo esc_html( $base_slug ); ?>/sample-attachment/</code></td>
					</tr>
					<tr>
						<th><label><input name="attachment_permalink" id="attachment_custom_selection" type="radio" value="custom" class="tog" <?php checked( $this->permalinks['custom'], 1 ); ?> />
						<?php _e( 'Custom base', 'attachment-slug' ); ?></label></th>
						<td>
							<code><?php echo esc_html( home_url() ); ?>/</code>
							<input name="attachment_permalink_structure" id="attachment_permalink_structure" type="text" value="<?php echo esc_attr( $this->permalinks['attachment_base'] ? trailingslashit( $this->permalinks['attachment_base'] ) : '' ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Enter a custom base to use. A base <strong>must</strong> be set or WordPress will use default instead.', 'attachment-slug' ); ?></span>
						</td>
					</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( 'ac-as-permalinks', 'ac-as-permalinks-nonce' ); ?>
			<script type="text/javascript">
			jQuery( function() {
				jQuery('input.astog').change(function() {
					jQuery('#attachment_permalink_structure').val( jQuery( this ).val() );
				});
				jQuery('.permalink-structure input').change(function() {
					jQuery('.ac-as-permalink-structure').find('code.non-default-example, code.default-example').hide();
					if ( jQuery(this).val() ) {
						jQuery('.ac-as-permalink-structure code.non-default-example').show();
						jQuery('.ac-as-permalink-structure input').removeAttr('disabled');
					} else {
						jQuery('.ac-as-permalink-structure code.default-example').show();
						jQuery('.ac-as-permalink-structure input:eq(0)').click();
						jQuery('.ac-as-permalink-structure input').attr('disabled', 'disabled');
					}
				});
				jQuery('.permalink-structure input:checked').change();
				jQuery('#attachment_permalink_structure').focus( function(){
					jQuery('#attachment_custom_selection').click();
				});
			});
			</script>
			<?php
		} // END settings()

		/**
		 * Save the settings.
		 *
		 * @access public
		 */
		public function settings_save() {
			if ( ! is_admin() ) {
				return;
			}

			// We need to save the options ourselves.
			if ( isset( $_POST['permalink_structure'], $_POST['ac-as-permalinks-nonce'], $_POST['attachment_permalink'] ) && wp_verify_nonce( wp_unslash( $_POST['ac-as-permalinks-nonce'] ), 'ac-as-permalinks' ) ) {
				Attachment_Slug::aswp_switch_to_site_locale();

				$permalink = (array) get_option( 'attachment_permalink', array() );

				$permalink['custom'] = '';

				// Attachment base
				$attachment_permalink = self::aswp_as_clean( wp_unslash( $_POST['attachment_permalink'] ) );

				if ( 'custom' === $attachment_permalink ) {
					$attachment_permalink = trim( self::aswp_as_clean( wp_unslash( $_POST['attachment_permalink_structure'] ) ) );
					$permalink['custom'] = true;
				}

				$permalink['attachment_base'] = untrailingslashit( $attachment_permalink );

				update_option( 'attachment_permalink', $permalink );
				Attachment_Slug::aswp_restore_locale();
			}
		} // END settings_save()

	} // END class

} // END if class exists

return new Attachment_Slug_Admin_Permalink_Settings();
