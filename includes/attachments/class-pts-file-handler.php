<?php
/**
 * File Upload Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_File_Handler {
    
    /**
     * Handle file upload
     */
    public static function handle_upload($file) {
        $settings = get_option('pts_settings', array());
        $tickets_settings = isset($settings['tickets']) ? $settings['tickets'] : array();
        
        $max_size = isset($tickets_settings['attachment_max_size']) ? $tickets_settings['attachment_max_size'] : 2097152;
        $allowed_types = isset($tickets_settings['allowed_mime_types']) ? $tickets_settings['allowed_mime_types'] : array('image/jpeg', 'image/png', 'application/pdf');
        
        // Check file size
        if ($file['size'] > $max_size) {
            return new WP_Error('file_too_large', __('File size exceeds maximum allowed size.', 'prospectra-ticketing-system'));
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            return new WP_Error('invalid_file_type', __('Invalid file type.', 'prospectra-ticketing-system'));
        }
        
        // Handle upload
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $upload_overrides = array('test_form' => false);
        $uploaded = wp_handle_upload($file, $upload_overrides);
        
        if (isset($uploaded['error'])) {
            return new WP_Error('upload_error', $uploaded['error']);
        }
        
        // Create attachment
        $attachment = array(
            'post_mime_type' => $uploaded['type'],
            'post_title' => sanitize_file_name($file['name']),
            'post_content' => '',
            'post_status' => 'inherit',
        );
        
        $attachment_id = wp_insert_attachment($attachment, $uploaded['file']);
        
        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }
        
        // Generate metadata
        $attach_data = wp_generate_attachment_metadata($attachment_id, $uploaded['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        
        return array(
            'attachment_id' => $attachment_id,
            'url' => $uploaded['url'],
            'filename' => basename($uploaded['file']),
        );
    }
    
    /**
     * Get attachment HTML
     */
    public static function get_attachment_html($attachment_id) {
        $file_url = wp_get_attachment_url($attachment_id);
        $file_name = basename(get_attached_file($attachment_id));
        $file_type = get_post_mime_type($attachment_id);
        
        $icon_class = 'dashicons-media-default';
        if (strpos($file_type, 'image') !== false) {
            $icon_class = 'dashicons-format-image';
        } elseif (strpos($file_type, 'pdf') !== false) {
            $icon_class = 'dashicons-pdf';
        }
        
        ob_start();
        ?>
        <div class="pts-attachment" data-attachment-id="<?php echo esc_attr($attachment_id); ?>">
            <span class="dashicons <?php echo esc_attr($icon_class); ?>"></span>
            <a href="<?php echo esc_url($file_url); ?>" target="_blank"><?php echo esc_html($file_name); ?></a>
            <button type="button" class="pts-remove-attachment" data-attachment-id="<?php echo esc_attr($attachment_id); ?>">
                <span class="dashicons dashicons-no"></span>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }
}
