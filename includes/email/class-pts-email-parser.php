<?php
/**
 * Email Parser - Convert emails to tickets
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Email_Parser {
    
    /**
     * Process incoming emails and create tickets
     */
    public static function process_incoming_emails() {
        $emails = PTS_Email_Connector::fetch_emails(10);
        
        if (is_wp_error($emails)) {
            error_log('PTS Email Fetch Error: ' . $emails->get_error_message());
            return;
        }
        
        foreach ($emails as $email) {
            // Check if this is a reply to existing ticket
            $ticket_id = self::find_ticket_by_message_id($email['in_reply_to']);
            
            if ($ticket_id) {
                self::add_email_reply_to_ticket($ticket_id, $email);
            } else {
                self::create_ticket_from_email($email);
            }
        }
    }
    
    /**
     * Create ticket from email
     */
    private static function create_ticket_from_email($email) {
        // Sanitize email body
        $body = wp_kses_post($email['body']);
        
        // Create ticket
        $ticket_id = wp_insert_post(array(
            'post_type' => 'pts_ticket',
            'post_title' => sanitize_text_field($email['subject']),
            'post_content' => $body,
            'post_status' => 'publish',
        ));
        
        if (is_wp_error($ticket_id)) {
            error_log('PTS Ticket Creation Error: ' . $ticket_id->get_error_message());
            return;
        }
        
        // Save metadata
        update_post_meta($ticket_id, '_pts_ticket_email_id', sanitize_email($email['from_email']));
        update_post_meta($ticket_id, '_pts_ticket_status', 'Open');
        update_post_meta($ticket_id, '_pts_ticket_priority', 'Medium');
        update_post_meta($ticket_id, '_pts_email_message_id', $email['message_id']);
        
        // Handle attachments
        if (!empty($email['attachments'])) {
            $attachment_ids = array();
            
            foreach ($email['attachments'] as $attachment) {
                $uploaded = self::save_email_attachment($attachment);
                if ($uploaded) {
                    $attachment_ids[] = $uploaded;
                }
            }
            
            update_post_meta($ticket_id, '_pts_ticket_attachments', $attachment_ids);
        }
        
        // Auto-assign based on category keywords (optional)
        self::auto_assign_ticket($ticket_id, $email);
        
        // Send confirmation email
        PTS_Email_Sender::send_ticket_created_confirmation($ticket_id, $email['from_email']);
        
        do_action('pts_ticket_created_from_email', $ticket_id, $email);
    }
    
    /**
     * Add email reply as comment to existing ticket
     */
    private static function add_email_reply_to_ticket($ticket_id, $email) {
        global $wpdb;
        $table = $wpdb->prefix .  'pts_comments';
        
        $body = wp_kses_post($email['body']);
        
        // Get or create user from email
        $user = get_user_by('email', $email['from_email']);
        $user_id = $user ? $user->ID : 0;
        
        $wpdb->insert($table, array(
            'ticket_id' => $ticket_id,
            'user_id' => $user_id,
            'content' => $body,
            'is_system_event' => 0,
        ));
        
        $comment_id = $wpdb->insert_id;
        
        // Handle attachments
        if (!empty($email['attachments'])) {
            $attachment_ids = array();
            
            foreach ($email['attachments'] as $attachment) {
                $uploaded = self::save_email_attachment($attachment);
                if ($uploaded) {
                    $attachment_ids[] = $uploaded;
                }
            }
            
            update_comment_meta($comment_id, '_pts_attachments', $attachment_ids);
        }
        
        do_action('pts_email_reply_added', $ticket_id, $comment_id, $email);
    }
    
    /**
     * Find ticket by email message ID
     */
    private static function find_ticket_by_message_id($message_id) {
        if (empty($message_id)) {
            return false;
        }
        
        global $wpdb;
        
        $ticket_id = $wpdb->get_var($wpdb->prepare("
            SELECT post_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_pts_email_message_id' 
            AND meta_value = %s
            LIMIT 1
        ", $message_id));
        
        return $ticket_id ?  absint($ticket_id) : false;
    }
    
    /**
     * Save email attachment to media library
     */
    private static function save_email_attachment($attachment) {
        $upload_dir = wp_upload_dir();
        $filename = sanitize_file_name($attachment['filename']);
        $filepath = $upload_dir['path'] . '/' . $filename;
        
        // Save file
        file_put_contents($filepath, $attachment['data']);
        
        // Create attachment post
        $filetype = wp_check_filetype($filename, null);
        
        $attachment_post = array(
            'post_mime_type' => $filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit',
        );
        
        $attachment_id = wp_insert_attachment($attachment_post, $filepath);
        
        if (is_wp_error($attachment_id)) {
            return false;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attachment_id, $filepath);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        
        return $attachment_id;
    }
    
    /**
     * Auto-assign ticket based on keywords
     */
    private static function auto_assign_ticket($ticket_id, $email) {
        $settings = get_option('pts_settings', array());
        $auto_assign_rules = isset($settings['auto_assign']) ? $settings['auto_assign'] : array();
        
        if (empty($auto_assign_rules)) {
            return;
        }
        
        $subject = strtolower($email['subject']);
        $body = strtolower($email['body']);
        
        foreach ($auto_assign_rules as $rule) {
            $keywords = array_map('strtolower', $rule['keywords']);
            
            foreach ($keywords as $keyword) {
                if (strpos($subject, $keyword) !== false || strpos($body, $keyword) !== false) {
                    update_post_meta($ticket_id, '_pts_ticket_assignee', $rule['user_id']);
                    update_post_meta($ticket_id, '_pts_ticket_category', $rule['category']);
                    return;
                }
            }
        }
    }
}
