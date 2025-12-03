<?php
/**
 * AJAX Request Handlers
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Ajax_Handlers {
    
    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        // Ticket operations
        add_action('wp_ajax_pts_update_ticket_meta', array(__CLASS__, 'update_ticket_meta'));
        add_action('wp_ajax_pts_add_comment', array(__CLASS__, 'add_comment'));
        add_action('wp_ajax_pts_upload_attachment', array(__CLASS__, 'upload_attachment'));
        add_action('wp_ajax_pts_create_ticket', array(__CLASS__, 'create_ticket'));
        add_action('wp_ajax_pts_delete_comment', array(__CLASS__, 'delete_comment'));
        
        // Report operations
        add_action('wp_ajax_pts_filter_reports', array(__CLASS__, 'filter_reports'));
        add_action('wp_ajax_pts_create_report', array(__CLASS__, 'create_report'));
        add_action('wp_ajax_pts_export_report', array(__CLASS__, 'export_report'));
        
        // Task operations
        add_action('wp_ajax_pts_mark_task_complete', array(__CLASS__, 'mark_task_complete'));
        add_action('wp_ajax_pts_filter_tickets', array(__CLASS__, 'filter_tickets'));
        
        // Search
        add_action('wp_ajax_pts_search', array(__CLASS__, 'search'));
        
        // Metrics
        add_action('wp_ajax_pts_calculate_metrics', array(__CLASS__, 'calculate_metrics'));
    }
    
    /**
     * Update ticket meta (status, priority, assignee, etc.)
     */
    public static function update_ticket_meta() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        if (!current_user_can('manage_tickets')) {
            wp_send_json_error(array('message' => __('Permission denied. ', 'prospectra-ticketing-system')));
        }
        
        $ticket_id = isset($_POST['ticket_id']) ?  absint($_POST['ticket_id']) : 0;
        $meta_key = isset($_POST['meta_key']) ? sanitize_text_field($_POST['meta_key']) : '';
        $meta_value = isset($_POST['meta_value']) ? sanitize_text_field($_POST['meta_value']) : '';
        
        if (! $ticket_id || !$meta_key) {
            wp_send_json_error(array('message' => __('Invalid parameters.', 'prospectra-ticketing-system')));
        }
        
        $allowed_keys = array('_pts_ticket_status', '_pts_ticket_priority', '_pts_ticket_category', '_pts_ticket_assignee', '_pts_ticket_due_date');
        
        if (!in_array($meta_key, $allowed_keys)) {
            wp_send_json_error(array('message' => __('Invalid meta key.', 'prospectra-ticketing-system')));
        }
        
        $old_value = get_post_meta($ticket_id, $meta_key, true);
        update_post_meta($ticket_id, $meta_key, $meta_value);
        
        // Add system event to timeline
        global $wpdb;
        $table = $wpdb->prefix . 'pts_comments';
        
        $user = wp_get_current_user();
        $field_name = str_replace('_pts_ticket_', '', $meta_key);
        $field_name = ucwords(str_replace('_', ' ', $field_name));
        
        $content = sprintf(
            __('%s changed %s from "%s" to "%s"', 'prospectra-ticketing-system'),
            $user->display_name,
            $field_name,
            $old_value,
            $meta_value
        );
        
        $wpdb->insert($table, array(
            'ticket_id' => $ticket_id,
            'user_id' => get_current_user_id(),
            'content' => $content,
            'is_system_event' => 1,
        ));
        
        // Send notification if status changed or ticket assigned
        if ($meta_key === '_pts_ticket_status' || $meta_key === '_pts_ticket_assignee') {
            PTS_Notification_Manager::send_notification($ticket_id, $meta_key, $meta_value);
        }
        
        wp_send_json_success(array(
            'message' => __('Updated successfully.', 'prospectra-ticketing-system'),
            'new_value' => $meta_value,
        ));
    }
    
    /**
     * Add comment to ticket
     */
    public static function add_comment() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        if (!current_user_can('comment_on_ticket')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'prospectra-ticketing-system')));
        }
        
        $ticket_id = isset($_POST['ticket_id']) ?  absint($_POST['ticket_id']) : 0;
        $content = isset($_POST['content']) ?  wp_kses_post($_POST['content']) : '';
        $attachments = isset($_POST['attachments']) ? array_map('absint', $_POST['attachments']) : array();
        
        if (!$ticket_id || empty($content)) {
            wp_send_json_error(array('message' => __('Invalid parameters. ', 'prospectra-ticketing-system')));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'pts_comments';
        
        $inserted = $wpdb->insert($table, array(
            'ticket_id' => $ticket_id,
            'user_id' => get_current_user_id(),
            'content' => $content,
            'is_system_event' => 0,
        ));
        
        if (! $inserted) {
            wp_send_json_error(array('message' => __('Failed to add comment.', 'prospectra-ticketing-system')));
        }
        
        $comment_id = $wpdb->insert_id;
        
        // Save attachments
        if (!empty($attachments)) {
            update_comment_meta($comment_id, '_pts_attachments', $attachments);
        }
        
        // Get comment data for response
        $user = wp_get_current_user();
        $comment_html = self::render_comment(array(
            'id' => $comment_id,
            'user_id' => get_current_user_id(),
            'content' => $content,
            'created_at' => current_time('mysql'),
            'attachments' => $attachments,
        ));
        
        // Send email notification if ticket has email requester
        $email_id = get_post_meta($ticket_id, '_pts_ticket_email_id', true);
        if ($email_id) {
            PTS_Email_Sender::send_reply($ticket_id, $comment_id, $email_id);
        }
        
        wp_send_json_success(array(
            'message' => __('Comment added successfully.', 'prospectra-ticketing-system'),
            'comment_html' => $comment_html,
        ));
    }
    
    /**
     * Upload attachment
     */
    public static function upload_attachment() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        if (!current_user_can('create_ticket')) {
            wp_send_json_error(array('message' => __('Permission denied. ', 'prospectra-ticketing-system')));
        }
        
        if (empty($_FILES['file'])) {
            wp_send_json_error(array('message' => __('No file uploaded.', 'prospectra-ticketing-system')));
        }
        
        $file = $_FILES['file'];
        $upload = PTS_File_Handler::handle_upload($file);
        
        if (is_wp_error($upload)) {
            wp_send_json_error(array('message' => $upload->get_error_message()));
        }
        
        wp_send_json_success(array(
            'attachment_id' => $upload['attachment_id'],
            'url' => $upload['url'],
            'filename' => $upload['filename'],
        ));
    }
    
    /**
     * Create new ticket
     */
    public static function create_ticket() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        if (!current_user_can('create_ticket')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'prospectra-ticketing-system')));
        }
        
        $title = isset($_POST['title']) ?  sanitize_text_field($_POST['title']) : '';
        $description = isset($_POST['description']) ? wp_kses_post($_POST['description']) : '';
        $email_id = isset($_POST['email_id']) ? sanitize_email($_POST['email_id']) : '';
        $category = isset($_POST['category']) ?  sanitize_text_field($_POST['category']) : '';
        $priority = isset($_POST['priority']) ? sanitize_text_field($_POST['priority']) : '';
        $shift_leader = isset($_POST['shift_leader']) ? absint($_POST['shift_leader']) : get_current_user_id();
        $attachments = isset($_POST['attachments']) ? array_map('absint', $_POST['attachments']) : array();
        
        if (empty($title) || strlen($description) < 20) {
            wp_send_json_error(array('message' => __('Title is required and description must be at least 20 characters.', 'prospectra-ticketing-system')));
        }
        
        $ticket_id = wp_insert_post(array(
            'post_type' => 'pts_ticket',
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => 'publish',
            'post_author' => $shift_leader,
        ));
        
        if (is_wp_error($ticket_id)) {
            wp_send_json_error(array('message' => __('Failed to create ticket.', 'prospectra-ticketing-system')));
        }
        
        // Save meta
        update_post_meta($ticket_id, '_pts_ticket_email_id', $email_id);
        update_post_meta($ticket_id, '_pts_ticket_status', 'Open');
        update_post_meta($ticket_id, '_pts_ticket_priority', $priority);
        update_post_meta($ticket_id, '_pts_ticket_category', $category);
        update_post_meta($ticket_id, '_pts_ticket_attachments', $attachments);
        
        // Send notification
        PTS_Notification_Manager::send_notification($ticket_id, 'new_ticket');
        
        wp_send_json_success(array(
            'message' => __('Ticket created successfully.', 'prospectra-ticketing-system'),
            'ticket_id' => $ticket_id,
            'redirect_url' => add_query_arg('ticket_id', $ticket_id, get_permalink()),
        ));
    }
    
    /**
     * Filter tickets
     */
    public static function filter_tickets() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        if (!current_user_can('view_tickets')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'prospectra-ticketing-system')));
        }
        
        $status = isset($_POST['status']) ?  sanitize_text_field($_POST['status']) : '';
        $priority = isset($_POST['priority']) ? sanitize_text_field($_POST['priority']) : '';
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        
        $args = array(
            'post_type' => 'pts_ticket',
            'posts_per_page' => 20,
            'meta_query' => array('relation' => 'AND'),
        );
        
        if ($status) {
            $args['meta_query'][] = array(
                'key' => '_pts_ticket_status',
                'value' => $status,
            );
        }
        
        if ($priority) {
            $args['meta_query'][] = array(
                'key' => '_pts_ticket_priority',
                'value' => $priority,
            );
        }
        
        $tickets = get_posts($args);
        
        ob_start();
        foreach ($tickets as $ticket) {
            include PTS_PLUGIN_DIR .  'templates/partials/ticket-row.php';
        }
        $html = ob_get_clean();
        
        wp_send_json_success(array('html' => $html));
    }
    
    /**
     * Global search
     */
    public static function search() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        $query = isset($_POST['query']) ?  sanitize_text_field($_POST['query']) : '';
        
        if (empty($query)) {
            wp_send_json_error(array('message' => __('Search query is empty.', 'prospectra-ticketing-system')));
        }
        
        $results = array();
        
        // Search tickets
        if (current_user_can('view_tickets')) {
            $tickets = get_posts(array(
                'post_type' => 'pts_ticket',
                's' => $query,
                'posts_per_page' => 5,
            ));
            
            foreach ($tickets as $ticket) {
                $results[] = array(
                    'type' => 'ticket',
                    'title' => $ticket->post_title,
                    'url' => add_query_arg('ticket_id', $ticket->ID, get_permalink()),
                    'excerpt' => wp_trim_words($ticket->post_content, 20),
                );
            }
        }
        
        // Search shift reports
        if (current_user_can('view_shift_reports')) {
            $reports = get_posts(array(
                'post_type' => 'pts_shift_report',
                's' => $query,
                'posts_per_page' => 5,
            ));
            
            foreach ($reports as $report) {
                $results[] = array(
                    'type' => 'report',
                    'title' => $report->post_title,
                    'url' => get_permalink($report->ID),
                    'excerpt' => get_the_date('', $report->ID),
                );
            }
        }
        
        wp_send_json_success(array('results' => $results));
    }
    
    /**
     * Calculate metrics
     */
    public static function calculate_metrics() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        $metrics = PTS_Metrics_Calculator::calculate_all();
        
        wp_send_json_success(array('metrics' => $metrics));
    }
    
    /**
     * Mark task complete
     */
    public static function mark_task_complete() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        if (!current_user_can('manage_tickets')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'prospectra-ticketing-system')));
        }
        
        $task_id = isset($_POST['task_id']) ? absint($_POST['task_id']) : 0;
        
        if (!$task_id) {
            wp_send_json_error(array('message' => __('Invalid task ID.', 'prospectra-ticketing-system')));
        }
        
        update_post_meta($task_id, '_pts_task_completed', true);
        update_post_meta($task_id, '_pts_task_completed_at', current_time('mysql'));
        update_post_meta($task_id, '_pts_task_completed_by', get_current_user_id());
        
        wp_send_json_success(array('message' => __('Task marked as complete.', 'prospectra-ticketing-system')));
    }
    
    /**
     * Render comment HTML
     */
    private static function render_comment($comment) {
        $user = get_user_by('id', $comment['user_id']);
        ob_start();
        ?>
        <div class="pts-comment" id="comment-<?php echo $comment['id']; ?>">
            <div class="pts-comment-avatar">
                <?php echo get_avatar($comment['user_id'], 40); ?>
            </div>
            <div class="pts-comment-content">
                <div class="pts-comment-header">
                    <strong><?php echo esc_html($user->display_name); ?></strong>
                    <span class="pts-comment-role"><?php echo esc_html(ucfirst($user->roles[0])); ?></span>
                    <span class="pts-comment-time"><?php echo esc_html($comment['created_at']); ? ></span>
                </div>
                <div class="pts-comment-body">
                    <? php echo wp_kses_post($comment['content']); ?>
                </div>
                <?php if (! empty($comment['attachments'])): ?>
                    <div class="pts-comment-attachments">
                        <? php foreach ($comment['attachments'] as $attachment_id): ?>
                            <a href="<?php echo wp_get_attachment_url($attachment_id); ?>" target="_blank">
                                <?php echo basename(get_attached_file($attachment_id)); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Create shift report
     */
    public static function create_report() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        if (!current_user_can('create_shift_reports')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'prospectra-ticketing-system')));
        }
        
        $shift_leader = isset($_POST['shift_leader']) ? absint($_POST['shift_leader']) : get_current_user_id();
        $shift_type = isset($_POST['shift_type']) ? sanitize_text_field($_POST['shift_type']) : '';
        $shift_date = isset($_POST['shift_date']) ? sanitize_text_field($_POST['shift_date']) : '';
        $production_plan = isset($_POST['production_plan']) ? $_POST['production_plan'] : array();
        $upcoming_production = isset($_POST['upcoming_production']) ? $_POST['upcoming_production'] : array();
        $followup_tasks = isset($_POST['followup_tasks']) ? $_POST['followup_tasks'] : array();
        $issues_summary = isset($_POST['issues_summary']) ? $_POST['issues_summary'] : array();
        $key_notes = isset($_POST['key_notes']) ? wp_kses_post($_POST['key_notes']) : '';
        $recipients = isset($_POST['recipients']) ?  array_map('sanitize_email', $_POST['recipients']) : array();
        $export_format = isset($_POST['export_format']) ? sanitize_text_field($_POST['export_format']) : 'pdf';
        
        $title = sprintf(__('Shift Report - %s - %s', 'prospectra-ticketing-system'), $shift_type, $shift_date);
        
        $report_id = wp_insert_post(array(
            'post_type' => 'pts_shift_report',
            'post_title' => $title,
            'post_status' => 'publish',
            'post_author' => $shift_leader,
        ));
        
        if (is_wp_error($report_id)) {
            wp_send_json_error(array('message' => __('Failed to create report.', 'prospectra-ticketing-system')));
        }
        
        // Save meta
        update_post_meta($report_id, '_pts_shift_leader', $shift_leader);
        update_post_meta($report_id, '_pts_shift_type', $shift_type);
        update_post_meta($report_id, '_pts_shift_date', $shift_date);
        update_post_meta($report_id, '_pts_production_plan', $production_plan);
        update_post_meta($report_id, '_pts_upcoming_production', $upcoming_production);
        update_post_meta($report_id, '_pts_followup_tasks', $followup_tasks);
        update_post_meta($report_id, '_pts_issues_summary', $issues_summary);
        update_post_meta($report_id, '_pts_key_notes', $key_notes);
        
        // Export and send if recipients exist
        if (!empty($recipients)) {
            if ($export_format === 'pdf') {
                $file_path = PTS_PDF_Generator::generate($report_id);
            } else {
                $file_path = PTS_Excel_Generator::generate($report_id);
            }
            
            foreach ($recipients as $recipient) {
                wp_mail($recipient, $title, __('Please find the shift report attached.', 'prospectra-ticketing-system'), array(), array($file_path));
            }
        }
        
        wp_send_json_success(array(
            'message' => __('Report created successfully.', 'prospectra-ticketing-system'),
            'report_id' => $report_id,
        ));
    }
    
    /**
     * Export report
     */
    public static function export_report() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        if (!current_user_can('export_shift_reports')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'prospectra-ticketing-system')));
        }
        
        $report_id = isset($_POST['report_id']) ? absint($_POST['report_id']) : 0;
        $format = isset($_POST['format']) ?  sanitize_text_field($_POST['format']) : 'pdf';
        
        if (!$report_id) {
            wp_send_json_error(array('message' => __('Invalid report ID.', 'prospectra-ticketing-system')));
        }
        
        if ($format === 'pdf') {
            $file_url = PTS_PDF_Generator::generate($report_id);
        } else {
            $file_url = PTS_Excel_Generator::generate($report_id);
        }
        
        wp_send_json_success(array('download_url' => $file_url));
    }
    
    /**
     * Filter reports
     */
    public static function filter_reports() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        if (!current_user_can('view_shift_reports')) {
            wp_send_json_error(array('message' => __('Permission denied. ', 'prospectra-ticketing-system')));
        }
        
        $time_range = isset($_POST['time_range']) ? sanitize_text_field($_POST['time_range']) : 'last_24_hours';
        $shift_type = isset($_POST['shift_type']) ? sanitize_text_field($_POST['shift_type']) : '';
        $search = isset($_POST['search']) ?  sanitize_text_field($_POST['search']) : '';
        
        $date_query = array();
        
        switch ($time_range) {
            case 'last_24_hours':
                $date_query = array(
                    'after' => '24 hours ago',
                );
                break;
            case 'last_week':
                $date_query = array(
                    'after' => '1 week ago',
                );
                break;
        }
        
        $args = array(
            'post_type' => 'pts_shift_report',
            'posts_per_page' => 20,
            'date_query' => $date_query,
            's' => $search,
        );
        
        if ($shift_type) {
            $args['meta_query'] = array(
                array(
                    'key' => '_pts_shift_type',
                    'value' => $shift_type,
                ),
            );
        }
        
        $reports = get_posts($args);
        
        ob_start();
        foreach ($reports as $report) {
            include PTS_PLUGIN_DIR . 'templates/partials/report-row.php';
        }
        $html = ob_get_clean();
        
        wp_send_json_success(array('html' => $html));
    }
    
    /**
     * Delete comment
     */
    public static function delete_comment() {
        check_ajax_referer('pts_nonce', 'nonce');
        
        $comment_id = isset($_POST['comment_id']) ? absint($_POST['comment_id']) : 0;
        
        if (! $comment_id) {
            wp_send_json_error(array('message' => __('Invalid comment ID.', 'prospectra-ticketing-system')));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'pts_comments';
        
        $comment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $comment_id));
        
        if (!$comment) {
            wp_send_json_error(array('message' => __('Comment not found.', 'prospectra-ticketing-system')));
        }
        
        // Check if user owns the comment or has manage_tickets capability
        if ($comment->user_id != get_current_user_id() && !current_user_can('manage_tickets')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'prospectra-ticketing-system')));
        }
        
        $deleted = $wpdb->delete($table, array('id' => $comment_id));
        
        if (! $deleted) {
            wp_send_json_error(array('message' => __('Failed to delete comment.', 'prospectra-ticketing-system')));
        }
        
        wp_send_json_success(array('message' => __('Comment deleted successfully.', 'prospectra-ticketing-system')));
    }
}
