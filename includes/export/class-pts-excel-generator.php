<?php
/**
 * Excel Generator using PhpSpreadsheet
 */

if (! defined('ABSPATH')) {
    exit;
}

class PTS_Excel_Generator {
    
    /**
     * Generate Excel for shift report
     */
    public static function generate($report_id) {
        // Note: PhpSpreadsheet would need to be installed via Composer
        // For simplicity, this is a basic CSV export that Excel can open
        
        $report = get_post($report_id);
        if (!$report) {
            return false;
        }
        
        $upload_dir = wp_upload_dir();
        $filename = 'shift-report-' . $report_id . '-' . time() . '.csv';
        $filepath = $upload_dir['path'] . '/' . $filename;
        
        $file = fopen($filepath, 'w');
        
        // Report header
        fputcsv($file, array($report->post_title));
        fputcsv($file, array());
        
        // Metadata
        $shift_leader = get_user_by('id', get_post_meta($report_id, '_pts_shift_leader', true));
        $shift_type = get_post_meta($report_id, '_pts_shift_type', true);
        $shift_date = get_post_meta($report_id, '_pts_shift_date', true);
        
        fputcsv($file, array(__('Shift Leader', 'prospectra-ticketing-system'), $shift_leader->display_name));
        fputcsv($file, array(__('Shift Type', 'prospectra-ticketing-system'), $shift_type));
        fputcsv($file, array(__('Date', 'prospectra-ticketing-system'), $shift_date));
        fputcsv($file, array());
        
        // Production Plan
        $production_plan = get_post_meta($report_id, '_pts_production_plan', true);
        if (!empty($production_plan)) {
            fputcsv($file, array(__('PRODUCTION PLAN', 'prospectra-ticketing-system')));
            fputcsv($file, array(__('Job ID', 'prospectra-ticketing-system'), __('Customer', 'prospectra-ticketing-system'), __('Time', 'prospectra-ticketing-system'), __('Quantity', 'prospectra-ticketing-system'), __('Machine', 'prospectra-ticketing-system')));
            
            foreach ($production_plan as $plan) {
                fputcsv($file, array(
                    $plan['job_id'],
                    $plan['customer'],
                    $plan['start_finish'],
                    $plan['quantity'],
                    $plan['machine'],
                ));
            }
            fputcsv($file, array());
        }
        
        // Follow-up Tasks
        $tasks = get_post_meta($report_id, '_pts_followup_tasks', true);
        if (!empty($tasks)) {
            fputcsv($file, array(__('FOLLOW-UP TASKS', 'prospectra-ticketing-system')));
            fputcsv($file, array(__('Task ID', 'prospectra-ticketing-system'), __('Date & Time', 'prospectra-ticketing-system'), __('Issued by', 'prospectra-ticketing-system'), __('Task', 'prospectra-ticketing-system'), __('Priority', 'prospectra-ticketing-system')));
            
            foreach ($tasks as $task) {
                fputcsv($file, array(
                    $task['task_id'],
                    $task['date_time'],
                    $task['issued_by'],
                    $task['task'],
                    $task['priority'],
                ));
            }
            fputcsv($file, array());
        }
        
        // Key Notes
        $notes = get_post_meta($report_id, '_pts_key_notes', true);
        if (!empty($notes)) {
            fputcsv($file, array(__('KEY NOTES / INSTRUCTIONS', 'prospectra-ticketing-system')));
            fputcsv($file, array(strip_tags($notes)));
        }
        
        fclose($file);
        
        return $upload_dir['url'] .  '/' . $filename;
    }
}
