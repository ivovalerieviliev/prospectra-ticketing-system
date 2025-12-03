<?php
/**
 * Email Sender - Send notifications and replies
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Email_Sender {
    
    /**
     * Send ticket reply via email
     */
    public static function send_reply($ticket_id, $comment_id, $to_email) {
        $ticket = get_post($ticket_id);
        $comment = self::get_comment($comment_id);
        
        if (!$ticket || !$comment) {
            return false;
        }
        
        $subject = sprintf(__('Re: [Ticket #%d] %s', 'prospectra-ticketing-system'), $ticket_id, $ticket->post_title);
        
        $body = self::get_email_template('ticket_reply', array(
            'ticket_id' => $ticket_id,
            'ticket_title' => $ticket->post_title,
            'comment_content' => $comment['content'],
            'comment_author' => get_user_by('id', $comment['user_id'])->display_name,
        ));
        
        // Add reply headers for threading
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );
        
        $message_id = get_post_meta($ticket_id, '_pts_email_message_id', true);
        if ($message_id) {
            $headers[] = 'In-Reply-To: ' . $message_id;
            $headers[] = 'References: ' . $message_id;
        }
        
        // Attach comment attachments
        $attachments = array();
        $attachment_ids = get_comment_meta($comment_id, '_pts_attachments', true);
        if (! empty($attachment_ids)) {
            foreach ($attachment_ids as $attachment_id) {
                $file = get_attached_file($attachment_id);
                if ($file) {
                    $attachments[] = $file;
                }
            }
        }
        
        return wp_mail($to_email, $subject, $body, $headers, $attachments);
    }
    
    /**
     * Send ticket created confirmation
     */
    public static function send_ticket_created_confirmation($ticket_id, $to_email) {
        $ticket = get_post($ticket_id);
        
        if (!$ticket) {
            return false;
        }
        
        $subject = sprintf(__('[Ticket #%d] %s', 'prospectra-ticketing-system'), $ticket_id, $ticket->post_title);
        
        $body = self::get_email_template('ticket_created', array(
            'ticket_id' => $ticket_id,
            'ticket_title' => $ticket->post_title,
            'ticket_url' => add_query_arg('ticket_id', $ticket_id, home_url('/tickets/')),
        ));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($to_email, $subject, $body, $headers);
    }
    
    /**
     * Send notification email
     */
    public static function send_notification($user_id, $type, $data = array()) {
        $user = get_user_by('id', $user_id);
        
        if (!$user) {
            return false;
        }
        
        $settings = get_option('pts_settings', array());
        $notifications = isset($settings['notifications']) ?  $settings['notifications'] : array();
        
        // Check if notification type is enabled
        if (empty($notifications[$type])) {
            return false;
        }
        
        $subject = self::get_notification_subject($type, $data);
        $body = self::get_email_template($type, $data);
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($user->user_email, $subject, $body, $headers);
    }
    
    /**
     * Get notification subject
     */
    private static function get_notification_subject($type, $data) {
        switch ($type) {
            case 'new_ticket':
                return sprintf(__('New Ticket: %s', 'prospectra-ticketing-system'), $data['ticket_title']);
            
            case 'status_changed':
                return sprintf(__('Ticket #%d Status Changed', 'prospectra-ticketing-system'), $data['ticket_id']);
            
            case 'ticket_assigned':
                return sprintf(__('Ticket #%d Assigned to You', 'prospectra-ticketing-system'), $data['ticket_id']);
            
            case 'new_comment':
                return sprintf(__('New Comment on Ticket #%d', 'prospectra-ticketing-system'), $data['ticket_id']);
            
            case 'new_report':
                return __('New Shift Report Available', 'prospectra-ticketing-system');
            
            default:
                return __('Notification', 'prospectra-ticketing-system');
        }
    }
    
    /**
     * Get email template
     */
    private static function get_email_template($type, $data = array()) {
        $settings = get_option('pts_settings', array());
        $templates = isset($settings['email_templates']) ? $settings['email_templates'] : array();
        
        // Get custom template or use default
        if (isset($templates[$type])) {
            $template = $templates[$type];
        } else {
            $template = self::get_default_template($type);
        }
        
        // Replace placeholders
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        // Wrap in email layout
        return self::wrap_email_layout($template);
    }
    
    /**
     * Get default email template
     */
    private static function get_default_template($type) {
        switch ($type) {
            case 'ticket_created':
                return '<p>' . __('Your ticket has been created successfully.', 'prospectra-ticketing-system') . '</p>
                        <p><strong>' . __('Ticket ID:', 'prospectra-ticketing-system') . '</strong> #{ticket_id}</p>
                        <p><strong>' . __('Title:', 'prospectra-ticketing-system') . '</strong> {ticket_title}</p>
                        <p><a href="{ticket_url}">' . __('View Ticket', 'prospectra-ticketing-system') . '</a></p>';
            
            case 'ticket_reply':
                return '<p>' .  __('A new reply has been added to your ticket.', 'prospectra-ticketing-system') . '</p>
                        <p><strong>' . __('From:', 'prospectra-ticketing-system') . '</strong> {comment_author}</p>
                        <div>{comment_content}</div>';
            
            case 'ticket_assigned':
                return '<p>' . __('A ticket has been assigned to you.', 'prospectra-ticketing-system') . '</p>
                        <p><strong>' . __('Ticket ID:', 'prospectra-ticketing-system') . '</strong> #{ticket_id}</p>
                        <p><strong>' . __('Title:', 'prospectra-ticketing-system') . '</strong> {ticket_title}</p>';
            
            default:
                return '<p>' .  __('You have a new notification.', 'prospectra-ticketing-system') . '</p>';
        }
    }
    
    /**
     * Wrap email in layout
     */
    private static function wrap_email_layout($content) {
        $settings = get_option('pts_settings', array());
        $company_name = get_bloginfo('name');
        $logo_url = isset($settings['exports']['company_logo']) ? wp_get_attachment_url($settings['exports']['company_logo']) : '';
        
        ob_start();
        ?>
        <! DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                . container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { background: white; padding: 30px; }
                .footer { background: #f3f4f6; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
                a { color: #2563eb; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <? php if ($logo_url): ?>
                        <img src="<? php echo esc_url($logo_url); ?>" alt="<? php echo esc_attr($company_name); ?>" style="max-height: 50px;">
                    <?php else: ?>
                        <h1><?php echo esc_html($company_name); ?></h1>
                    <?php endif; ?>
                </div>
                <div class="content">
                    <?php echo $content; ?>
                </div>
                <div class="footer">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html($company_name); ?>. <? php _e('All rights reserved.', 'prospectra-ticketing-system'); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get comment from custom table
     */
    private static function get_comment($comment_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pts_comments';
        
        $comment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $comment_id
        ), ARRAY_A);
        
        return $comment;
    }
}
