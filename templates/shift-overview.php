<? php
/**
 * Shift Overview Dashboard (Screen 5/Image 4)
 */

if (! defined('ABSPATH')) {
    exit;
}

// Get current shift data
$current_user_id = get_current_user_id();
$current_time = current_time('timestamp');
$settings = get_option('pts_settings', array());
$shift_types = isset($settings['shift_reports']['shift_types']) ? $settings['shift_reports']['shift_types'] : array();

// Determine current shift
$current_shift = null;
foreach ($shift_types as $shift) {
    // Simple time check - in production this would be more sophisticated
    $current_shift = $shift;
    break;
}

// Get metrics
$metrics = PTS_Metrics_Calculator::get_cached_metrics();

// Get urgent tasks
$urgent_tasks_args = array(
    'post_type' => 'pts_ticket',
    'post_status' => 'publish',
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => '_pts_ticket_priority',
            'value' => 'High',
        ),
        array(
            'key' => '_pts_ticket_status',
            'value' => 'Closed',
            'compare' => '!=',
        ),
    ),
    'posts_per_page' => 5,
);
$urgent_tasks = get_posts($urgent_tasks_args);

// Get orders for current shift
$orders_args = array(
    'post_type' => 'pts_order',
    'post_status' => 'publish',
    'posts_per_page' => 10,
);
$orders = get_posts($orders_args);

// Get tickets
$tickets_args = array(
    'post_type' => 'pts_ticket',
    'post_status' => 'publish',
    'posts_per_page' => 20,
);
$tickets = get_posts($tickets_args);

// Count tickets by status
$status_counts = array(
    'all' => count($tickets),
    'pending' => 0,
    'in_process' => 0,
    'solved' => 0,
    'archived' => 0,
);

foreach ($tickets as $ticket) {
    $status = get_post_meta($ticket->ID, '_pts_ticket_status', true);
    $status_key = strtolower(str_replace(' ', '_', $status));
    if (isset($status_counts[$status_key])) {
        $status_counts[$status_key]++;
    }
}
?>

<div class="pts-wrapper">
    <? php include PTS_PLUGIN_DIR . 'templates/partials/global-header.php'; ?>
    
    <div class="pts-content">
        <? php include PTS_PLUGIN_DIR .  'templates/partials/page-header.php'; ?>
        
        <div class="pts-shift-overview">
            
            <!-- Top Bar -->
            <div class="pts-page-header-actions">
                <button class="pts-btn pts-btn-outline" onclick="window.location.href='? page=report-history'"><?php _e('Report History', 'prospectra-ticketing-system'); ?></button>
                <button class="pts-btn pts-btn-primary" onclick="window.location.href='?page=create-handover'"><span class="dashicons dashicons-plus"></span> <?php _e('Create Report', 'prospectra-ticketing-system'); ?></button>
            </div>
            
            <!-- Section 1: Urgent Tasks -->
            <div class="pts-card pts-urgent-tasks">
                <div class="pts-card-header pts-urgent-header">
                    <span class="dashicons dashicons-warning"></span>
                    <h2><?php _e('Take Action: Ongoing Tasks', 'prospectra-ticketing-system'); ?></h2>
                </div>
                <div class="pts-card-body">
                    <? php if (!empty($urgent_tasks)): ?>
                        <table class="pts-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Task', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Issued by', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Category', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Priority', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Completion Date & Time', 'prospectra-ticketing-system'); ?></th>
                                    <th><? php _e('Action', 'prospectra-ticketing-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($urgent_tasks as $task): 
                                    $priority = get_post_meta($task->ID, '_pts_ticket_priority', true);
                                    $category = get_post_meta($task->ID, '_pts_ticket_category', true);
                                    $due_date = get_post_meta($task->ID, '_pts_ticket_due_date', true);
                                    $author = get_user_by('id', $task->post_author);
                                ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($task->post_title); ?></strong></td>
                                        <td>
                                            <? php echo esc_html($author->display_name); ? ><br>
                                            <span class="pts-text-muted"><?php echo esc_html(ucfirst($author->roles[0])); ?></span>
                                        </td>
                                        <td><?php echo esc_html($category); ? ></td>
                                        <td><span class="pts-badge pts-badge-<?php echo strtolower($priority); ? >"><?php echo esc_html($priority); ?></span></td>
                                        <td><? php echo $due_date ? esc_html(date('d/m/Y H:i', strtotime($due_date))) : '-'; ?></td>
                                        <td>
                                            <button class="pts-icon-btn pts-mark-complete" data-task-id="<?php echo $task->ID; ? >" title="<?php esc_attr_e('Mark as done', 'prospectra-ticketing-system'); ?>">
                                                <span class="dashicons dashicons-yes"></span>
                                            </button>
                                            <button class="pts-icon-btn" onclick="window.location.href='? page=ticket-details&ticket_id=<?php echo $task->ID; ?>'" title="<?php esc_attr_e('View ticket', 'prospectra-ticketing-system'); ?>">
                                                <span class="dashicons dashicons-admin-links"></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="pts-empty-state"><?php _e('No urgent tasks at this time.', 'prospectra-ticketing-system'); ? ></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Section 2 & 3: Shift Details and Real-Time Metrics -->
            <div class="pts-grid pts-grid-2">
                
                <!-- Shift Details -->
                <div class="pts-card">
                    <div class="pts-card-header">
                        <h2><?php _e('Shift Details', 'prospectra-ticketing-system'); ? ></h2>
                    </div>
                    <div class="pts-card-body">
                        <div class="pts-shift-info">
                            <div class="pts-shift-info-row">
                                <span class="pts-label"><?php _e('Shift Leader', 'prospectra-ticketing-system'); ?></span>
                                <span class="pts-value"><?php echo esc_html(wp_get_current_user()->display_name); ? ></span>
                            </div>
                            <div class="pts-shift-info-row">
                                <span class="pts-label"><?php _e('Shift Type', 'prospectra-ticketing-system'); ? ></span>
                                <span class="pts-value"><?php echo $current_shift ? esc_html($current_shift['name']) : '-'; ?></span>
                            </div>
                            <div class="pts-shift-info-row">
                                <span class="pts-label"><?php _e('Shift Timing', 'prospectra-ticketing-system'); ?></span>
                                <span class="pts-value"><?php echo $current_shift ? esc_html($current_shift['start'] . ' - ' . $current_shift['end']) : '-'; ? ></span>
                            </div>
                            <div class="pts-shift-info-row">
                                <span class="pts-label"><? php _e('Staff', 'prospectra-ticketing-system'); ?></span>
                                <span class="pts-value">13</span>
                            </div>
                            <div class="pts-shift-info-row">
                                <span class="pts-label"><? php _e('Remaining Time', 'prospectra-ticketing-system'); ?></span>
                                <span class="pts-value pts-countdown" data-end-time="<?php echo $current_shift ? esc_attr($current_shift['end']) : ''; ?>">02:32:54</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Real-Time Metrics -->
                <div class="pts-card">
                    <div class="pts-card-header">
                        <h2><?php _e('Real-Time Metrics', 'prospectra-ticketing-system'); ?></h2>
                    </div>
                    <div class="pts-card-body">
                        <div class="pts-metrics-grid">
                            <div class="pts-metric">
                                <div class="pts-metric-value"><?php echo isset($metrics['production_volume']) ? esc_html($metrics['production_volume']) : '0'; ?> <span class="pts-metric-unit">m</span></div>
                                <div class="pts-metric-label"><?php _e('Production Volume', 'prospectra-ticketing-system'); ?></div>
                            </div>
                            <div class="pts-metric">
                                <div class="pts-metric-value"><?php echo isset($metrics['issues_reported']) ? esc_html($metrics['issues_reported']) : '0'; ?></div>
                                <div class="pts-metric-label"><? php _e('Issues Reported', 'prospectra-ticketing-system'); ?></div>
                            </div>
                            <div class="pts-metric">
                                <div class="pts-metric-value">87. 3<span class="pts-metric-unit">%</span></div>
                                <div class="pts-metric-label"><?php _e('Runtime', 'prospectra-ticketing-system'); ?></div>
                            </div>
                            <div class="pts-metric">
                                <div class="pts-metric-value"><?php echo isset($metrics['efficiency']) ? esc_html($metrics['efficiency']) : '0'; ?><span class="pts-metric-unit">%</span></div>
                                <div class="pts-metric-label"><?php _e('Efficiency', 'prospectra-ticketing-system'); ? ></div>
                                <span class="pts-metric-badge pts-badge-success"><?php _e('Production', 'prospectra-ticketing-system'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Section 4: Order List -->
            <div class="pts-card">
                <div class="pts-card-header">
                    <h2><?php _e('Order List', 'prospectra-ticketing-system'); ?></h2>
                    <div class="pts-card-actions">
                        <button class="pts-btn pts-btn-sm" id="pts-filter-orders"><span class="dashicons dashicons-filter"></span> <?php _e('Filters', 'prospectra-ticketing-system'); ?></button>
                        <a href="#" class="pts-link"><?php _e('View all', 'prospectra-ticketing-system'); ?></a>
                    </div>
                </div>
                <div class="pts-card-body">
                    <? php if (!empty($orders)): ?>
                        <table class="pts-table pts-orders-table">
                            <thead>
                                <tr>
                                    <th><? php _e('Production Run', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Customer', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Start – Finish', 'prospectra-ticketing-system'); ?></th>
                                    <th><? php _e('Machine', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Produced / Planned [m]', 'prospectra-ticketing-system'); ?></th>
                                    <th><? php _e('Trim', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Grade', 'prospectra-ticketing-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($orders, 0, 5) as $order): 
                                    $job_id = get_post_meta($order->ID, '_pts_order_job_id', true);
                                    $customer = get_post_meta($order->ID, '_pts_order_customer', true);
                                    $start_time = get_post_meta($order->ID, '_pts_order_start_time', true);
                                    $end_time = get_post_meta($order->ID, '_pts_order_end_time', true);
                                    $machine = get_post_meta($order->ID, '_pts_order_machine', true);
                                    $produced = get_post_meta($order->ID, '_pts_order_produced', true);
                                    $planned = get_post_meta($order->ID, '_pts_order_planned', true);
                                    $progress = $planned > 0 ? round(($produced / $planned) * 100) : 0;
                                ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($job_id); ?></strong></td>
                                        <td><? php echo esc_html($customer); ?></td>
                                        <td><?php echo esc_html($start_time .  ' – ' . $end_time); ? ></td>
                                        <td><?php echo esc_html($machine); ?></td>
                                        <td>
                                            <div class="pts-progress-cell">
                                                <span><? php echo esc_html($produced . ' / ' . $planned); ?></span>
                                                <div class="pts-progress-bar">
                                                    <div class="pts-progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                                </div>
                                                <span class="pts-progress-percent"><?php echo $progress; ?>%</span>
                                            </div>
                                        </td>
                                        <td>4. 3%</td>
                                        <td>1 E E</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="pts-empty-state"><?php _e('No orders for current shift.', 'prospectra-ticketing-system'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Section 5: Reported Issue Tickets -->
            <div class="pts-card">
                <div class="pts-card-header">
                    <h2><?php _e('Reported Issue Tickets (Today)', 'prospectra-ticketing-system'); ?></h2>
                    <div class="pts-card-actions">
                        <button class="pts-btn pts-btn-primary" id="pts-create-ticket-btn"><span class="dashicons dashicons-plus"></span> <?php _e('Create Ticket', 'prospectra-ticketing-system'); ?></button>
                        <a href="#" class="pts-link"><?php _e('View all', 'prospectra-ticketing-system'); ?></a>
                    </div>
                </div>
                
                <!-- Tabs -->
                <div class="pts-tabs">
                    <button class="pts-tab active" data-status="all">
                        <?php _e('All', 'prospectra-ticketing-system'); ? > 
                        <span class="pts-tab-badge"><?php echo $status_counts['all']; ? ></span>
                    </button>
                    <button class="pts-tab" data-status="pending">
                        <?php _e('Pending', 'prospectra-ticketing-system'); ?>
                        <? php if ($status_counts['pending'] > 0): ?>
                            <span class="pts-tab-badge"><?php echo $status_counts['pending']; ?></span>
                        <?php endif; ?>
                    </button>
                    <button class="pts-tab" data-status="in_process">
                        <?php _e('In-Process', 'prospectra-ticketing-system'); ?>
                        <?php if ($status_counts['in_process'] > 0): ?>
                            <span class="pts-tab-badge"><? php echo $status_counts['in_process']; ?></span>
                        <?php endif; ?>
                    </button>
                    <button class="pts-tab" data-status="solved">
                        <?php _e('Solved', 'prospectra-ticketing-system'); ?>
                        <?php if ($status_counts['solved'] > 0): ?>
                            <span class="pts-tab-badge"><? php echo $status_counts['solved']; ?></span>
                        <?php endif; ?>
                    </button>
                    <button class="pts-tab" data-status="archived">
                        <?php _e('Archived', 'prospectra-ticketing-system'); ?>
                        <?php if ($status_counts['archived'] > 0): ?>
                            <span class="pts-tab-badge"><?php echo $status_counts['archived']; ? ></span>
                        <?php endif; ?>
                    </button>
                </div>
                
                <div class="pts-card-body">
                    <table class="pts-table pts-tickets-table" id="pts-tickets-table">
                        <thead>
                            <tr>
                                <th><?php _e('Ticket ID', 'prospectra-ticketing-system'); ?></th>
                                <th><?php _e('Issued by', 'prospectra-ticketing-system'); ?></th>
                                <th><?php _e('Issue title', 'prospectra-ticketing-system'); ?></th>
                                <th><?php _e('Category', 'prospectra-ticketing-system'); ? ></th>
                                <th><?php _e('Priority', 'prospectra-ticketing-system'); ?></th>
                                <th><?php _e('Status', 'prospectra-ticketing-system'); ?></th>
                                <th><?php _e('Date & Time', 'prospectra-ticketing-system'); ?></th>
                                <th><?php _e('Reply & Action', 'prospectra-ticketing-system'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($tickets, 0, 10) as $ticket): 
                                include PTS_PLUGIN_DIR . 'templates/partials/ticket-row.php';
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Create Ticket Modal -->
<div id="pts-create-ticket-modal" class="pts-modal" style="display: none;">
    <div class="pts-modal-overlay"></div>
    <div class="pts-modal-content">
        <? php include PTS_PLUGIN_DIR . 'templates/create-ticket.php'; ?>
    </div>
</div>
