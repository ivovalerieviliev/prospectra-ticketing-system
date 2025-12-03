<? php
/**
 * Single Ticket Row for Tables
 */

if (!defined('ABSPATH')) {
    exit;
}

$ticket_status = get_post_meta($ticket->ID, '_pts_ticket_status', true);
$ticket_priority = get_post_meta($ticket->ID, '_pts_ticket_priority', true);
$ticket_category = get_post_meta($ticket->ID, '_pts_ticket_category', true);
$ticket_author = get_user_by('id', $ticket->post_author);
$ticket_due_date = get_post_meta($ticket->ID, '_pts_ticket_due_date', true);

// Count comments
global $wpdb;
$comments_table = $wpdb->prefix .  'pts_comments';
$comment_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $comments_table WHERE ticket_id = %d AND is_system_event = 0", $ticket->ID));
?>

<tr class="pts-ticket-row" data-ticket-id="<?php echo $ticket->ID; ? >" onclick="window.location.href='? page=ticket-details&ticket_id=<?php echo $ticket->ID; ?>'">
    <td><strong>#<?php echo $ticket->ID; ?></strong></td>
    <td>
        <? php echo esc_html($ticket_author ?  $ticket_author->display_name : '-'); ? ><br>
        <span class="pts-text-muted"><?php echo $ticket_author ? esc_html(ucfirst($ticket_author->roles[0])) : ''; ?></span>
    </td>
    <td><strong><?php echo esc_html($ticket->post_title); ?></strong></td>
    <td><? php echo esc_html($ticket_category); ?></td>
    <td>
        <span class="pts-badge pts-badge-<?php echo strtolower($ticket_priority); ?>">
            <?php echo esc_html($ticket_priority); ?>
        </span>
    </td>
    <td>
        <span class="pts-status-badge pts-status-<?php echo strtolower(str_replace(' ', '-', $ticket_status)); ?>">
            <?php echo esc_html($ticket_status); ?>
        </span>
    </td>
    <td><? php echo $ticket_due_date ? esc_html(date('d/m/Y H:i', strtotime($ticket_due_date))) : get_the_date('d/m/Y H:i', $ticket); ?></td>
    <td>
        <div class="pts-ticket-actions" onclick="event.stopPropagation();">
            <span class="pts-comment-count">
                <span class="dashicons dashicons-admin-comments"></span>
                <?php echo absint($comment_count); ?>
            </span>
            <div class="pts-action-menu">
                <button class="pts-icon-btn pts-action-menu-btn">
                    <span class="dashicons dashicons-ellipsis"></span>
                </button>
                <div class="pts-action-dropdown">
                    <button class="pts-dropdown-item pts-change-status" data-ticket-id="<? php echo $ticket->ID; ?>">
                        <? php _e('Change Status', 'prospectra-ticketing-system'); ?>
                    </button>
                    <button class="pts-dropdown-item pts-reassign" data-ticket-id="<?php echo $ticket->ID; ?>">
                        <?php _e('Assign/Reassign', 'prospectra-ticketing-system'); ?>
                    </button>
                    <button class="pts-dropdown-item pts-mark-solved" data-ticket-id="<? php echo $ticket->ID; ?>">
                        <?php _e('Mark as Solved', 'prospectra-ticketing-system'); ?>
                    </button>
                </div>
            </div>
        </div>
    </td>
</tr>
