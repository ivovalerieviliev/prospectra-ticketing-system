<? php
/**
 * Notification Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Notification_Manager {
    
    /**
     * Send notification based on event
     */
    public static function send_notification($ticket_id, $event_type, $value = null) {
        $settings = get_option('pts_settings', array());
        $notifications = isset($settings['notifications']) ? $settings['notifications'] : array();
        
        // Check if notification is enabled
        $notification_key = self::get_notification_key($event_type);
        if (empty($notifications[$notification_key])) {
            return;
        }
        
        $ticket = get_post($ticket_id);
        if (!$ticket) {
            return;
        }
        
        $data = array(
            'ticket_id' => $ticket_id,
            'ticket_title' => $ticket->post_title,
            'ticket_url' => add_query_arg('ticket_id', $ticket_id, home_url('/tickets/')),
        );
        
        switch ($event_type) {
            case 'new_ticket':
                self::notify_new_ticket($ticket_id, $data);
                break;
            
            case '_pts_ticket_status':
                $data['old_status'] = get_post_meta($ticket_id, '_pts_ticket_status', true);
                $data['new_status'] = $value;
                self::notify_status_changed($ticket_id, $data);
                break;
            
            case '_pts_ticket_assignee':
                $data['assignee_id'] = $value;
                self::notify_ticket_assigned($ticket_id, $data);
                break;
        }
    }
    
    /**
     * Get notification key from event type
     */
    private static function get_notification_key($event_type) {
        $map = array(
            'new_ticket' => 'new_ticket',
            '_pts_ticket_status' => 'status_changed',
            '_pts_ticket_assignee' => 'ticket_assigned',
            'new_comment' => 'new_comment',
            'new_report' => 'new_report',
        );
        
        return isset($map[$event_type]) ?  $map[$event_type] : '';
    }
    
    /**
     * Notify about new ticket
     */
    private static function notify_new_ticket($ticket_id, $data) {
        // Notify shift leaders and team leaders
        $users = get_users(array(
            'role__in' => array('pts_shift_leader', 'pts_team_leader', 'administrator'),
        ));
        
        foreach ($users as $user) {
            PTS_Email_Sender::send_notification($user->ID, 'new_ticket', $data);
        }
    }
    
    /**
     * Notify about status change
     */
    private static function notify_status_changed($ticket_id, $data) {
        // Notify ticket author and assignee
        $ticket = get_post($ticket_id);
        $assignee_id = get_post_meta($ticket_id, '_pts_ticket_assignee', true);
        
        if ($ticket->post_author) {
            PTS_Email_Sender::send_notification($ticket->post_author, 'status_changed', $data);
        }
        
        if ($assignee_id && $assignee_id != $ticket->post_author) {
            PTS_Email_Sender::send_notification($assignee_id, 'status_changed', $data);
        }
        
        // Notify email requester if exists
        $email_id = get_post_meta($ticket_id, '_pts_ticket_email_id', true);
        if ($email_id) {
            $subject = sprintf(__('Ticket #%d Status Changed', 'prospectra-ticketing-system'), $ticket_id);
            $body = sprintf(
                __('The status of your ticket has been changed from "%s" to "%s".', 'prospectra-ticketing-system'),
                $data['old_status'],
                $data['new_status']
            );
            wp_mail($email_id, $subject, $body);
        }
    }
    
    /**
     * Notify about ticket assignment
     */
    private static function notify_ticket_assigned($ticket_id, $data) {
        if (empty($data['assignee_id'])) {
            return;
        }
        
        PTS_Email_Sender::send_notification($data['assignee_id'], 'ticket_assigned', $data);
    }
    
    /**
     * Notify about new comment
     */
    public static function notify_new_comment($ticket_id, $comment_id) {
        $settings = get_option('pts_settings', array());
        $notifications = isset($settings['notifications']) ? $settings['notifications'] : array();
        
        if (empty($notifications['new_comment'])) {
            return;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'pts_comments';
        $comment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $comment_id), ARRAY_A);
        
        if (!$comment) {
            return;
        }
        
        $ticket = get_post($ticket_id);
        $assignee_id = get_post_meta($ticket_id, '_pts_ticket_assignee', true);
        
        $data = array(
            'ticket_id' => $ticket_id,
            'ticket_title' => $ticket->post_title,
            'comment_author' => get_user_by('id', $comment['user_id'])->display_name,
            'comment_content' => wp_trim_words($comment['content'], 50),
        );
        
        // Notify ticket author if not the commenter
        if ($ticket->post_author != $comment['user_id']) {
            PTS_Email_Sender::send_notification($ticket->post_author, 'new_comment', $data);
        }
        
        // Notify assignee if not the commenter
        if ($assignee_id && $assignee_id != $comment['user_id'] && $assignee_id != $ticket->post_author) {
            PTS_Email_Sender::send_notification($assignee_id, 'new_comment', $data);
        }
    }
    
    /**
     * Notify about new shift report
     */
    public static function notify_new_report($report_id) {
        $settings = get_option('pts_settings', array());
        $notifications = isset($settings['notifications']) ? $settings['notifications'] : array();
        
        if (empty($notifications['new_report'])) {
            return;
        }
        
        $report = get_post($report_id);
        $shift_type = get_post_meta($report_id, '_pts_shift_type', true);
        
        // Get users in relevant organizations
        $organizations = wp_get_post_terms($report_id, 'pts_organization', array('fields' => 'ids'));
        
        if (empty($organizations)) {
            return;
        }
        
        $users = get_users(array(
            'role__in' => array('pts_shift_leader', 'pts_team_leader'),
            'meta_query' => array(
                array(
                    'key' => 'pts_organization',
                    'value' => $organizations,
                    'compare' => 'IN',
                ),
            ),
        ));
        
        $data = array(
            'report_id' => $report_id,
            'report_title' => $report->post_title,
            'shift_type' => $shift_type,
            'report_url' => get_permalink($report_id),
        );
        
        foreach ($users as $user) {
            PTS_Email_Sender::send_notification($user->ID, 'new_report', $data);
        }
    }
}
