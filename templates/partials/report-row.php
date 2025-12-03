<? php
/**
 * Single Report Row for Tables
 */

if (!defined('ABSPATH')) {
    exit;
}

$shift_leader_id = get_post_meta($report->ID, '_pts_shift_leader', true);
$shift_type = get_post_meta($report->ID, '_pts_shift_type', true);
$shift_date = get_post_meta($report->ID, '_pts_shift_date', true);
$issues_summary = get_post_meta($report->ID, '_pts_issues_summary', true);
$key_notes = get_post_meta($report->ID, '_pts_key_notes', true);

$shift_leader = get_user_by('id', $shift_leader_id);
$issues_count = is_array($issues_summary) ?  count($issues_summary) : 0;

// Get shift time range from settings
$settings = get_option('pts_settings', array());
$shift_types = isset($settings['shift_reports']['shift_types']) ? $settings['shift_reports']['shift_types'] : array();
$time_range = '';
foreach ($shift_types as $shift) {
    if ($shift['name'] === $shift_type) {
        $time_range = $shift['start'] . ' – ' . $shift['end'];
        break;
    }
}
?>

<tr class="pts-report-row" data-report-id="<?php echo $report->ID; ?>" onclick="window.location.href='? page=report-details&report_id=<?php echo $report->ID; ?>'">
    <td>
        <strong><? php echo get_the_date('d. m.Y', $report); ? ></strong><br>
        <span class="pts-text-muted"><?php echo get_the_time('H:i', $report); ?></span>
    </td>
    <td>
        <strong><?php echo esc_html($shift_type); ?></strong><br>
        <span class="pts-text-muted"><?php echo esc_html($time_range); ?></span>
    </td>
    <td>
        <? php echo $shift_leader ? esc_html($shift_leader->display_name) : '-'; ? ><br>
        <span class="pts-text-muted"><?php echo $shift_leader ? esc_html(ucfirst($shift_leader->roles[0])) : ''; ?></span>
    </td>
    <td>
        <a href="? page=report-details&report_id=<?php echo $report->ID; ? >#issues" class="pts-issues-link" onclick="event.stopPropagation();">
            <? php printf(_n('%d issue', '%d issues', $issues_count, 'prospectra-ticketing-system'), $issues_count); ?>
        </a>
    </td>
    <td>
        <div class="pts-key-notes-preview" title="<?php echo esc_attr(wp_strip_all_tags($key_notes)); ?>">
            <? php echo esc_html(wp_trim_words(wp_strip_all_tags($key_notes), 10)); ?>
        </div>
    </td>
    <td>
        <div class="pts-report-actions" onclick="event.stopPropagation();">
            <button class="pts-icon-btn pts-export-pdf" data-report-id="<?php echo $report->ID; ? >" data-format="pdf" title="<?php esc_attr_e('Export as PDF', 'prospectra-ticketing-system'); ?>">
                <span class="dashicons dashicons-media-document"></span>
            </button>
            <button class="pts-icon-btn pts-export-excel" data-report-id="<? php echo $report->ID; ? >" data-format="excel" title="<?php esc_attr_e('Export as Excel', 'prospectra-ticketing-system'); ?>">
                <span class="dashicons dashicons-media-spreadsheet"></span>
            </button>
            <div class="pts-action-menu">
                <button class="pts-icon-btn pts-action-menu-btn">
                    <span class="dashicons dashicons-ellipsis"></span>
                </button>
                <div class="pts-action-dropdown">
                    <button class="pts-dropdown-item" onclick="window.location.href='? page=report-details&report_id=<?php echo $report->ID; ?>'">
                        <?php _e('View', 'prospectra-ticketing-system'); ?>
                    </button>
                    <button class="pts-dropdown-item pts-duplicate-report" data-report-id="<?php echo $report->ID; ?>">
                        <?php _e('Duplicate', 'prospectra-ticketing-system'); ?>
                    </button>
                    <button class="pts-dropdown-item pts-delete-report" data-report-id="<?php echo $report->ID; ?>">
                        <?php _e('Delete', 'prospectra-ticketing-system'); ?>
                    </button>
                    <button class="pts-dropdown-item pts-share-report" data-report-id="<?php echo $report->ID; ?>">
                        <?php _e('Share link', 'prospectra-ticketing-system'); ?>
                    </button>
                </div>
            </div>
        </div>
    </td>
</tr>
