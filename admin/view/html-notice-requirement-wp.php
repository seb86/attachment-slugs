<?php
/**
 * Admin View: WordPress Requirement Notice.
 *
 * @since    1.0.0
 * @author   Sébastien Dumont
 * @category Admin
 * @package  Attachment Slugs/Admin/Views
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-error">
	<p><?php echo sprintf( __( 'Sorry, <strong>%s</strong> now requires WordPress %s or higher. Please upgrade your WordPress setup.', 'attachment-slug' ), esc_html__( 'Attachment Slug', 'attachment-slug' ), '4.4' ); ?></p>
</div>
