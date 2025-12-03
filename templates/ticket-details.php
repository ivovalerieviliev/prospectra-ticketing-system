<? php
/**
 * Ticket Details Screen (Screen 1/Image 1)
 */

if (! defined('ABSPATH')) {
    exit;
}

$ticket_id = isset($ticket) ? $ticket->ID : (isset($_GET['ticket_id']) ? absint($_GET['ticket_id']) : 0);
$ticket = get_post($ticket_id);

if (!$ticket || $ticket->post_type !== 'pts_ticket') {
    echo '<p>' . __('Ticket not found.', 'prospectra-ticketing-system') . '</p>';
    return;
}

// Get ticket meta
$status = get_post_meta($ticket_id, '_pts_ticket_status', true);
$priority = get_post_meta($ticket_id, '_pts_ticket_priority', true);
$category = get_post_meta($ticket_id, '_pts_ticket_category', true);
$assignee_id = get_post_meta($ticket_id, '_pts_ticket_assignee', true);
$due_date = get_post_meta($ticket_id, '_pts_ticket_due_date', true);
$email_id = get_post_meta($ticket_id, '_pts_ticket_email_id', true);
$attachments = get_post_meta($ticket_id, '_pts_ticket_attachments', true);

// Get requester
$requester = get_user_by('id', $ticket->post_author);

// Get settings for dropdowns
$settings = get_option('pts_settings', array());
$statuses = isset($settings['tickets']['statuses']) ? $settings['tickets']['statuses'] : array();
$priorities = isset($settings['tickets']['priorities']) ? $settings['tickets']['priorities'] : array();

// Get assignable users
$assignable_users = PTS_Capabilities::get_users_by_capability('manage_tickets');

// Get comments
global $wpdb;
$comments_table = $wpdb->prefix .  'pts_comments';
$comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $comments_table WHERE ticket_id = %d ORDER BY created_at DESC", $ticket_id));
?>

<div class="pts-wrapper">
    <? php include PTS_PLUGIN_DIR .  'templates/partials/global-header.php'; ?>
    
    <div class="pts-content">
        
        <!-- Page Header -->
        <div class="pts-page-header">
            <div class="pts-page-header-left">
                <button class="pts-back-btn" onclick="history.back()">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <h1><? php _e('Ticket Details', 'prospectra-ticketing-system'); ?></h1>
            </div>
            <div class="pts-page-header-right">
                <span class="pts-text-muted">
                    <? php _e('Created on', 'prospectra-ticketing-system'); ? > 
                    <strong><? php echo get_the_date('d/m/Y H:i T', $ticket); ?></strong>
                </span>
            </div>
        </div>
        
        <!-- Ticket Info Bar -->
        <div class="pts-ticket-info-bar">
            <div class="pts-ticket-info-item">
                <label><?php _e('Ticket ID', 'prospectra-ticketing-system'); ?></label>
                <span class="pts-ticket-id">#<?php echo $ticket_id; ?></span>
            </div>
            
            <div class="pts-ticket-info-item">
                <label><?php _e('Category', 'prospectra-ticketing-system'); ?></label>
                <select class="pts-inline-edit" data-ticket-id="<?php echo $ticket_id; ? >" data-meta-key="_pts_ticket_category">
                    <option value=""><?php _e('Select Category', 'prospectra-ticketing-system'); ?></option>
                    <option value="Maintenance" <?php selected($category, 'Maintenance'); ?>><?php _e('Maintenance', 'prospectra-ticketing-system'); ?></option>
                    <option value="Safety" <?php selected($category, 'Safety'); ?>><?php _e('Safety', 'prospectra-ticketing-system'); ?></option>
                    <option value="Quality" <?php selected($category, 'Quality'); ?>><?php _e('Quality', 'prospectra-ticketing-system'); ?></option>
                    <option value="Other" <?php selected($category, 'Other'); ?>><?php _e('Other', 'prospectra-ticketing-system'); ?></option>
                </select>
            </div>
            
            <div class="pts-ticket-info-item">
                <label><?php _e('Priority', 'prospectra-ticketing-system'); ?></label>
                <select class="pts-inline-edit" data-ticket-id="<?php echo $ticket_id; ?>" data-meta-key="_pts_ticket_priority">
                    <? php foreach ($priorities as $p): ?>
                        <option value="<?php echo esc_attr($p['name']); ?>" <?php selected($priority, $p['name']); ?>><?php echo esc_html($p['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="pts-ticket-info-item">
                <label><?php _e('Status', 'prospectra-ticketing-system'); ?></label>
                <select class="pts-inline-edit" data-ticket-id="<?php echo $ticket_id; ?>" data-meta-key="_pts_ticket_status">
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?php echo esc_attr($s['name']); ?>" <?php selected($status, $s['name']); ?>><?php echo esc_html($s['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="pts-ticket-info-item">
                <label><?php _e('Date', 'prospectra-ticketing-system'); ?></label>
                <input type="date" class="pts-inline-edit" data-ticket-id="<?php echo $ticket_id; ?>" data-meta-key="_pts_ticket_due_date" value="<?php echo esc_attr($due_date); ?>">
            </div>
            
            <div class="pts-ticket-info-item">
                <label><?php _e('Assign to', 'prospectra-ticketing-system'); ?></label>
                <select class="pts-inline-edit" data-ticket-id="<?php echo $ticket_id; ?>" data-meta-key="_pts_ticket_assignee">
                    <option value=""><?php _e('Unassigned', 'prospectra-ticketing-system'); ?></option>
                    <?php foreach ($assignable_users as $user): ?>
                        <option value="<?php echo $user->ID; ?>" <?php selected($assignee_id, $user->ID); ? >>
                            <?php echo esc_html($user->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Ticket Content Card -->
        <div class="pts-card pts-ticket-content">
            <div class="pts-ticket-header">
                <div class="pts-requester-info">
                    <? php echo get_avatar($ticket->post_author, 50); ?>
                    <div>
                        <strong><?php echo esc_html($requester ?  $requester->display_name : __('Unknown', 'prospectra-ticketing-system')); ? ></strong>
                        <div class="pts-ticket-meta">
                            <span class="pts-badge pts-badge-<?php echo strtolower($status); ?>">
                                <?php printf(__('Status: %s', 'prospectra-ticketing-system'), $status); ?>
                            </span>
                            <span class="pts-badge"><?php echo esc_html($category); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="pts-ticket-body">
                <h2 class="pts-ticket-title"><?php echo esc_html($ticket->post_title); ?></h2>
                <div class="pts-ticket-description">
                    <?php echo wpautop($ticket->post_content); ?>
                </div>
                
                <? php if (! empty($attachments)): ?>
                    <div class="pts-ticket-attachments">
                        <h3><?php _e('Attachments', 'prospectra-ticketing-system'); ? ></h3>
                        <div class="pts-attachments-grid">
                            <?php foreach ($attachments as $attachment_id): 
                                $file_url = wp_get_attachment_url($attachment_id);
                                $file_name = basename(get_attached_file($attachment_id));
                                $file_type = get_post_mime_type($attachment_id);
                            ?>
                                <a href="<?php echo esc_url($file_url); ?>" class="pts-attachment-item" target="_blank" title="<?php echo esc_attr($file_name); ?>">
                                    <? php if (strpos($file_type, 'image') !== false): ?>
                                        <img src="<? php echo esc_url($file_url); ?>" alt="<? php echo esc_attr($file_name); ?>">
                                    <?php else: ?>
                                        <span class="dashicons dashicons-media-document"></span>
                                        <span class="pts-file-name"><?php echo esc_html($file_name); ? ></span>
                                    <? php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Comment Composer -->
        <? php if (current_user_can('comment_on_ticket')): ?>
        <div class="pts-card pts-comment-composer">
            <div class="pts-card-body">
                <h3><?php _e('Add a Reply', 'prospectra-ticketing-system'); ?></h3>
                
                <div class="pts-rich-editor-toolbar">
                    <button type="button" class="pts-editor-btn" data-command="bold" title="<? php esc_attr_e('Bold', 'prospectra-ticketing-system'); ?>"><strong>B</strong></button>
                    <button type="button" class="pts-editor-btn" data-command="italic" title="<?php esc_attr_e('Italic', 'prospectra-ticketing-system'); ?>"><em>I</em></button>
                    <button type="button" class="pts-editor-btn" data-command="underline" title="<?php esc_attr_e('Underline', 'prospectra-ticketing-system'); ?>"><u>U</u></button>
                    <span class="pts-toolbar-separator"></span>
                    <button type="button" class="pts-editor-btn" data-command="insertUnorderedList" title="<?php esc_attr_e('Bullet List', 'prospectra-ticketing-system'); ?>">
                        <span class="dashicons dashicons-editor-ul"></span>
                    </button>
                    <button type="button" class="pts-editor-btn" data-command="insertOrderedList" title="<? php esc_attr_e('Numbered List', 'prospectra-ticketing-system'); ?>">
                        <span class="dashicons dashicons-editor-ol"></span>
                    </button>
                    <button type="button" class="pts-editor-btn" data-command="createLink" title="<?php esc_attr_e('Insert Link', 'prospectra-ticketing-system'); ?>">
                        <span class="dashicons dashicons-admin-links"></span>
                    </button>
                </div>
                
                <div id="pts-comment-editor" class="pts-rich-editor" contenteditable="true" placeholder="<?php esc_attr_e('Type your reply here...', 'prospectra-ticketing-system'); ?>"></div>
                
                <div class="pts-editor-actions">
                    <div class="pts-editor-tools">
                        <button type="button" class="pts-icon-btn" id="pts-attach-file" title="<?php esc_attr_e('Attach file', 'prospectra-ticketing-system'); ?>">
                            <span class="dashicons dashicons-paperclip"></span>
                        </button>
                        <button type="button" class="pts-icon-btn" id="pts-emoji" title="<?php esc_attr_e('Insert emoji', 'prospectra-ticketing-system'); ?>">
                            <span class="dashicons dashicons-smiley"></span>
                        </button>
                        <button type="button" class="pts-icon-btn" id="pts-mention" title="<?php esc_attr_e('Mention user', 'prospectra-ticketing-system'); ?>">
                            <span class="dashicons dashicons-admin-users"></span>
                        </button>
                        <input type="file" id="pts-file-input" multiple style="display: none;">
                    </div>
                    
                    <button type="button" class="pts-btn pts-btn-primary" id="pts-submit-reply" data-ticket-id="<?php echo $ticket_id; ?>">
                        <?php _e('Reply', 'prospectra-ticketing-system'); ?>
                    </button>
                </div>
                
                <div id="pts-attached-files" class="pts-attached-files"></div>
            </div>
        </div>
        <?php else: ?>
            <div class="pts-card">
                <div class="pts-card-body">
                    <p class="pts-info-message"><?php _e('You don\'t have permission to comment on this ticket.', 'prospectra-ticketing-system'); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Conversation Timeline -->
        <div class="pts-card pts-timeline">
            <div class="pts-card-header">
                <h3><? php _e('Conversation History', 'prospectra-ticketing-system'); ?></h3>
            </div>
            <div class="pts-card-body">
                <div id="pts-comments-list" class="pts-comments-list">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): 
                            $comment_user = get_user_by('id', $comment->user_id);
                            $comment_attachments = get_comment_meta($comment->id, '_pts_attachments', true);
                        ?>
                            <div class="pts-comment <? php echo $comment->is_system_event ? 'pts-system-event' : ''; ? >" id="comment-<?php echo $comment->id; ?>">
                                <div class="pts-comment-avatar">
                                    <?php echo get_avatar($comment->user_id, 40); ?>
                                </div>
                                <div class="pts-comment-content">
                                    <div class="pts-comment-header">
                                        <strong><?php echo esc_html($comment_user ? $comment_user->display_name : __('System', 'prospectra-ticketing-system')); ?></strong>
                                        <? php if ($comment_user && ! $comment->is_system_event): ?>
                                            <span class="pts-comment-role"><?php echo esc_html(ucfirst($comment_user->roles[0])); ?></span>
                                        <?php endif; ?>
                                        <span class="pts-comment-time"><?php echo esc_html(date('d/m/Y H:i', strtotime($comment->created_at))); ?></span>
                                        
                                        <?php if (! $comment->is_system_event && ($comment->user_id == get_current_user_id() || current_user_can('manage_tickets'))): ?>
                                            <div class="pts-comment-actions">
                                                <button class="pts-icon-btn pts-delete-comment" data-comment-id="<?php echo $comment->id; ? >" title="<?php esc_attr_e('Delete', 'prospectra-ticketing-system'); ?>">
                                                    <span class="dashicons dashicons-trash"></span>
                                                </button>
                                            </div>
                                        <? php endif; ?>
                                    </div>
                                    <div class="pts-comment-body">
                                        <?php echo wp_kses_post($comment->content); ?>
                                    </div>
                                    
                                    <?php if (!empty($comment_attachments)): ?>
                                        <div class="pts-comment-attachments">
                                            <? php foreach ($comment_attachments as $attachment_id): 
                                                $file_url = wp_get_attachment_url($attachment_id);
                                                $file_name = basename(get_attached_file($attachment_id));
                                            ?>
                                                <a href="<?php echo esc_url($file_url); ?>" target="_blank" class="pts-attachment-link">
                                                    <span class="dashicons dashicons-media-document"></span>
                                                    <? php echo esc_html($file_name); ?>
                                                </a>
                                            <? php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <? php else: ?>
                        <p class="pts-empty-state"><?php _e('No comments yet. Be the first to reply!', 'prospectra-ticketing-system'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    </div>
</div>
