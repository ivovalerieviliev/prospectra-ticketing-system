<? php
/**
 * Create Handover Report (Screen 4/Image 3)
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('pts_settings', array());
$shift_types = isset($settings['shift_reports']['shift_types']) ? $settings['shift_reports']['shift_types'] : array();

$current_user = wp_get_current_user();
$current_date = current_time('d/m/Y');
$current_time = current_time('H:i');

// Get shift leaders
$shift_leaders = get_users(array('role__in' => array('pts_shift_leader', 'pts_team_leader', 'administrator')));

// Get orders for production plan
$orders = get_posts(array('post_type' => 'pts_order', 'posts_per_page' => 20));
?>

<div class="pts-wrapper">
    <?php include PTS_PLUGIN_DIR . 'templates/partials/global-header.php'; ?>
    
    <div class="pts-content">
        
        <!-- Page Header -->
        <div class="pts-page-header">
            <div class="pts-page-header-left">
                <button class="pts-back-btn" onclick="history.back()">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <h1><?php _e('Create Handover Report', 'prospectra-ticketing-system'); ?></h1>
            </div>
            <div class="pts-page-header-right">
                <div class="pts-date-selector">
                    <button class="pts-btn pts-btn-sm pts-btn-outline"><?php _e('Today', 'prospectra-ticketing-system'); ?></button>
                    <span class="pts-current-datetime">
                        <?php echo $current_date; ? > – <?php echo $current_time; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <form id="pts-handover-report-form" class="pts-handover-form">
            
            <!-- Section 1: Handover Details -->
            <div class="pts-card">
                <div class="pts-card-header">
                    <h2><?php _e('Handover Details', 'prospectra-ticketing-system'); ?></h2>
                </div>
                <div class="pts-card-body">
                    <div class="pts-form-grid pts-grid-3">
                        
                        <div class="pts-form-group">
                            <label for="pts-shift-leader"><?php _e('Shift Leader', 'prospectra-ticketing-system'); ?> <span class="pts-required">*</span></label>
                            <select id="pts-shift-leader" name="shift_leader" class="pts-select" required>
                                <? php foreach ($shift_leaders as $leader): ?>
                                    <option value="<?php echo $leader->ID; ?>" <? php selected($leader->ID, $current_user->ID); ? >>
                                        <?php echo esc_html($leader->display_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="pts-form-group">
                            <label for="pts-shift-type"><?php _e('Shift Type', 'prospectra-ticketing-system'); ? > <span class="pts-required">*</span></label>
                            <select id="pts-shift-type" name="shift_type" class="pts-select" required>
                                <option value=""><?php _e('Select', 'prospectra-ticketing-system'); ?></option>
                                <?php foreach ($shift_types as $shift): ?>
                                    <option value="<?php echo esc_attr($shift['name']); ?>" data-start="<?php echo esc_attr($shift['start']); ?>" data-end="<? php echo esc_attr($shift['end']); ?>">
                                        <?php echo esc_html($shift['name'] . ' (' . $shift['start'] . ' – ' . $shift['end'] .  ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="pts-form-group">
                            <label for="pts-handover-datetime"><?php _e('Handover Date & Time', 'prospectra-ticketing-system'); ?> <span class="pts-required">*</span></label>
                            <input type="datetime-local" id="pts-handover-datetime" name="handover_datetime" class="pts-input" value="<?php echo current_time('Y-m-d\TH:i'); ?>" required>
                        </div>
                        
                    </div>
                </div>
            </div>
            
            <!-- Section 2: Production Plan -->
            <div class="pts-card">
                <div class="pts-card-header">
                    <h2><?php _e('Production Plan', 'prospectra-ticketing-system'); ?></h2>
                    <button type="button" class="pts-btn pts-btn-sm pts-btn-primary" id="pts-add-production-plan">
                        <span class="dashicons dashicons-plus"></span>
                        <? php _e('Add Plan', 'prospectra-ticketing-system'); ?>
                    </button>
                </div>
                <div class="pts-card-body">
                    <div class="pts-table-responsive">
                        <table class="pts-table pts-editable-table" id="pts-production-plan-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Job ID', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Customer Name', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Start – Finish', 'prospectra-ticketing-system'); ?></th>
                                    <th><? php _e('Produced / Planned quantity [m]', 'prospectra-ticketing-system'); ?></th>
                                    <th><? php _e('Machine / Tools used', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Priority Level', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Special instructions', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Action', 'prospectra-ticketing-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($orders, 0, 5) as $order): 
                                    $job_id = get_post_meta($order->ID, '_pts_order_job_id', true);
                                    $customer = get_post_meta($order->ID, '_pts_order_customer', true);
                                    $start_time = get_post_meta($order->ID, '_pts_order_start_time', true);
                                    $end_time = get_post_meta($order->ID, '_pts_order_end_time', true);
                                    $produced = get_post_meta($order->ID, '_pts_order_produced', true);
                                    $planned = get_post_meta($order->ID, '_pts_order_planned', true);
                                    $machine = get_post_meta($order->ID, '_pts_order_machine', true);
                                    $priority = get_post_meta($order->ID, '_pts_order_priority', true);
                                ?>
                                    <tr data-order-id="<?php echo $order->ID; ?>">
                                        <td><input type="text" class="pts-input-cell" value="<?php echo esc_attr($job_id); ? >" name="production_plan[<?php echo $order->ID; ?>][job_id]"></td>
                                        <td><input type="text" class="pts-input-cell" value="<? php echo esc_attr($customer); ?>" name="production_plan[<?php echo $order->ID; ?>][customer]"></td>
                                        <td><input type="text" class="pts-input-cell" value="<? php echo esc_attr($start_time .  ' – ' . $end_time); ?>" name="production_plan[<?php echo $order->ID; ?>][start_finish]"></td>
                                        <td><input type="text" class="pts-input-cell" value="<?php echo esc_attr($produced .  ' / ' . $planned); ? >" name="production_plan[<? php echo $order->ID; ?>][quantity]"></td>
                                        <td><input type="text" class="pts-input-cell" value="<?php echo esc_attr($machine); ?>" name="production_plan[<?php echo $order->ID; ?>][machine]"></td>
                                        <td>
                                            <select class="pts-select-cell" name="production_plan[<?php echo $order->ID; ?>][priority]">
                                                <option value="Low" <?php selected($priority, 'Low'); ?>><?php _e('Low', 'prospectra-ticketing-system'); ?></option>
                                                <option value="Medium" <?php selected($priority, 'Medium'); ?>><?php _e('Medium', 'prospectra-ticketing-system'); ?></option>
                                                <option value="High" <?php selected($priority, 'High'); ?>><?php _e('High', 'prospectra-ticketing-system'); ?></option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="pts-input-cell" name="production_plan[<?php echo $order->ID; ?>][instructions]" placeholder="<?php esc_attr_e('Add instructions', 'prospectra-ticketing-system'); ?>"></td>
                                        <td>
                                            <button type="button" class="pts-icon-btn pts-remove-row" title="<? php esc_attr_e('Remove', 'prospectra-ticketing-system'); ?>">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Section 3: Upcoming Production -->
            <div class="pts-card">
                <div class="pts-card-header">
                    <h2><?php _e('Upcoming Production', 'prospectra-ticketing-system'); ?></h2>
                    <button type="button" class="pts-btn pts-btn-sm pts-btn-primary" id="pts-add-upcoming-task">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add Upcoming Task', 'prospectra-ticketing-system'); ?>
                    </button>
                </div>
                <div class="pts-card-body">
                    <div class="pts-table-responsive">
                        <table class="pts-table pts-editable-table" id="pts-upcoming-production-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Job ID', 'prospectra-ticketing-system'); ?></th>
                                    <th><? php _e('Customer Name', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Start – Finish', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Planned quantity [m]', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Machine / Tools', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Priority', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Action', 'prospectra-ticketing-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="pts-empty-row">
                                    <td colspan="7" class="pts-empty-state">
                                        <? php _e('No upcoming production tasks.  Click "Add Upcoming Task" to add one.', 'prospectra-ticketing-system'); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Section 4: Follow-Up Tasks -->
            <div class="pts-card">
                <div class="pts-card-header">
                    <h2><?php _e('Follow-Up Tasks', 'prospectra-ticketing-system'); ?></h2>
                    <button type="button" class="pts-btn pts-btn-sm pts-btn-primary" id="pts-add-followup-task">
                        <span class="dashicons dashicons-plus"></span>
                        <? php _e('Add Task', 'prospectra-ticketing-system'); ?>
                    </button>
                </div>
                <div class="pts-card-body">
                    <div class="pts-table-responsive">
                        <table class="pts-table pts-editable-table" id="pts-followup-tasks-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Task ID', 'prospectra-ticketing-system'); ?></th>
                                    <th><? php _e('Date & Time', 'prospectra-ticketing-system'); ?></th>
                                    <th><? php _e('Issued by', 'prospectra-ticketing-system'); ?></th>
                                    <th><? php _e('Task', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Category', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Priority', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Assign to', 'prospectra-ticketing-system'); ?></th>
                                    <th><? php _e('Action', 'prospectra-ticketing-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="pts-empty-row">
                                    <td colspan="8" class="pts-empty-state">
                                        <?php _e('No follow-up tasks.  Click "Add Task" to create one.', 'prospectra-ticketing-system'); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Section 5: Issues Summary -->
            <div class="pts-card">
                <div class="pts-card-header">
                    <h2><?php _e('Issues Summary', 'prospectra-ticketing-system'); ?></h2>
                    <button type="button" class="pts-btn pts-btn-sm pts-btn-primary" id="pts-add-issue">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add Issue', 'prospectra-ticketing-system'); ?>
                    </button>
                </div>
                <div class="pts-card-body">
                    <div class="pts-table-responsive">
                        <table class="pts-table pts-editable-table" id="pts-issues-summary-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Title', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Machine', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Date & Time', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Issued by', 'prospectra-ticketing-system'); ?></th>
                                    <th><? php _e('Category', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Priority', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Status', 'prospectra-ticketing-system'); ?></th>
                                    <th><?php _e('Action', 'prospectra-ticketing-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="pts-empty-row">
                                    <td colspan="8" class="pts-empty-state">
                                        <?php _e('No issues reported. Click "Add Issue" to add one.', 'prospectra-ticketing-system'); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Section 6: Key Notes / Instructions -->
            <div class="pts-card">
                <div class="pts-card-header">
                    <h2><?php _e('Key Notes / Instructions', 'prospectra-ticketing-system'); ?></h2>
                </div>
                <div class="pts-card-body">
                    <div class="pts-rich-editor-toolbar">
                        <button type="button" class="pts-editor-btn" data-command="bold"><strong>B</strong></button>
                        <button type="button" class="pts-editor-btn" data-command="italic"><em>I</em></button>
                        <button type="button" class="pts-editor-btn" data-command="insertUnorderedList">
                            <span class="dashicons dashicons-editor-ul"></span>
                        </button>
                        <button type="button" class="pts-editor-btn" data-command="insertOrderedList">
                            <span class="dashicons dashicons-editor-ol"></span>
                        </button>
                    </div>
                    <div 
                        id="pts-key-notes" 
                        class="pts-rich-editor pts-notes-editor" 
                        contenteditable="true" 
                        data-placeholder="<?php esc_attr_e('Add key notes or special instructions for the next shift...', 'prospectra-ticketing-system'); ?>"
                    ></div>
                    <div class="pts-word-counter">
                        <span id="pts-word-count">0</span> <? php _e('words', 'prospectra-ticketing-system'); ?>
                    </div>
                </div>
            </div>
            
            <!-- Section 7: Share Report (Optional) -->
            <div class="pts-card">
                <div class="pts-card-header">
                    <h2><? php _e('Share Report (Optional)', 'prospectra-ticketing-system'); ?></h2>
                </div>
                <div class="pts-card-body">
                    
                    <div class="pts-form-group">
                        <label><? php _e('Recipient Email', 'prospectra-ticketing-system'); ?></label>
                        <div class="pts-email-input-wrapper">
                            <input 
                                type="email" 
                                id="pts-recipient-email" 
                                class="pts-input" 
                                placeholder="<?php esc_attr_e('Type email and press Enter', 'prospectra-ticketing-system'); ?>"
                            >
                            <button type="button" class="pts-btn pts-btn-sm" id="pts-add-recipient-btn">
                                <? php _e('Add recipient', 'prospectra-ticketing-system'); ?>
                            </button>
                        </div>
                        <div id="pts-email-chips" class="pts-email-chips"></div>
                    </div>
                    
                    <div class="pts-form-group">
                        <label><?php _e('Distribution List', 'prospectra-ticketing-system'); ?></label>
                        <select class="pts-select" id="pts-distribution-list">
                            <option value=""><?php _e('Select a distribution list', 'prospectra-ticketing-system'); ?></option>
                            <?php
                            $organizations = get_terms(array('taxonomy' => 'pts_organization', 'hide_empty' => false));
                            foreach ($organizations as $org):
                            ?>
                                <option value="<?php echo $org->term_id; ?>"><?php echo esc_html($org->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="pts-form-group">
                        <label><?php _e('Export Format', 'prospectra-ticketing-system'); ?></label>
                        <div class="pts-checkbox-group">
                            <label class="pts-checkbox-label">
                                <input type="checkbox" name="export_format[]" value="pdf" checked>
                                <?php _e('PDF', 'prospectra-ticketing-system'); ?>
                            </label>
                            <label class="pts-checkbox-label">
                                <input type="checkbox" name="export_format[]" value="excel">
                                <?php _e('Excel', 'prospectra-ticketing-system'); ?>
                            </label>
                        </div>
                        <p class="pts-help-text"><?php _e('At least one format must be selected to share via email', 'prospectra-ticketing-system'); ?></p>
                    </div>
                    
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="pts-form-actions">
                <button type="button" class="pts-btn pts-btn-outline" id="pts-cancel-report">
                    <? php _e('Cancel', 'prospectra-ticketing-system'); ?>
                </button>
                <button type="submit" class="pts-btn pts-btn-primary" id="pts-create-report-submit">
                    <?php _e('Create Report', 'prospectra-ticketing-system'); ?>
                </button>
            </div>
            
        </form>
        
    </div>
</div>

<!-- Autosave indicator -->
<div id="pts-autosave-indicator" class="pts-autosave-indicator" style="display: none;">
    <span class="dashicons dashicons-cloud"></span>
    <span class="pts-autosave-text"><?php _e('Saved as draft', 'prospectra-ticketing-system'); ?></span>
</div>
