<? php
/**
 * PDF Generator using TCPDF
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_PDF_Generator {
    
    /**
     * Generate PDF for shift report
     */
    public static function generate($report_id) {
        require_once(PTS_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/tcpdf.php');
        
        $report = get_post($report_id);
        if (!$report) {
            return false;
        }
        
        $settings = get_option('pts_settings', array());
        $logo_id = isset($settings['exports']['company_logo']) ? $settings['exports']['company_logo'] : '';
        $footer_text = isset($settings['exports']['footer_text']) ? $settings['exports']['footer_text'] : '';
        
        // Create PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        
        // Set document information
        $pdf->SetCreator('Prospectra Ticketing System');
        $pdf->SetAuthor(get_bloginfo('name'));
        $pdf->SetTitle($report->post_title);
        
        // Set margins
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, 25);
        
        // Add page
        $pdf->AddPage();
        
        // Logo
        if ($logo_id) {
            $logo_path = get_attached_file($logo_id);
            if ($logo_path) {
                $pdf->Image($logo_path, 15, 10, 30);
            }
        }
        
        // Title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 15, $report->post_title, 0, 1, 'C');
        
        // Report metadata
        $pdf->SetFont('helvetica', '', 10);
        $shift_leader = get_post_meta($report_id, '_pts_shift_leader', true);
        $shift_type = get_post_meta($report_id, '_pts_shift_type', true);
        $shift_date = get_post_meta($report_id, '_pts_shift_date', true);
        
        $user = get_user_by('id', $shift_leader);
        
        $pdf->Cell(0, 5, sprintf(__('Shift Leader: %s', 'prospectra-ticketing-system'), $user->display_name), 0, 1);
        $pdf->Cell(0, 5, sprintf(__('Shift Type: %s', 'prospectra-ticketing-system'), $shift_type), 0, 1);
        $pdf->Cell(0, 5, sprintf(__('Date: %s', 'prospectra-ticketing-system'), $shift_date), 0, 1);
        $pdf->Ln(5);
        
        // Production Plan
        self::add_production_plan_section($pdf, $report_id);
        
        // Upcoming Production
        self::add_upcoming_production_section($pdf, $report_id);
        
        // Follow-up Tasks
        self::add_followup_tasks_section($pdf, $report_id);
        
        // Issues Summary
        self::add_issues_summary_section($pdf, $report_id);
        
        // Key Notes
        self::add_key_notes_section($pdf, $report_id);
        
        // Footer
        if ($footer_text) {
            $pdf->SetY(-15);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->Cell(0, 10, $footer_text, 0, 0, 'C');
        }
        
        // Save PDF
        $upload_dir = wp_upload_dir();
        $filename = 'shift-report-' . $report_id . '-' . time() . '.pdf';
        $filepath = $upload_dir['path'] . '/' . $filename;
        
        $pdf->Output($filepath, 'F');
        
        return $upload_dir['url'] . '/' . $filename;
    }
    
    /**
     * Add Production Plan section
     */
    private static function add_production_plan_section($pdf, $report_id) {
        $production_plan = get_post_meta($report_id, '_pts_production_plan', true);
        
        if (empty($production_plan)) {
            return;
        }
        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, __('Production Plan', 'prospectra-ticketing-system'), 0, 1);
        $pdf->SetFont('helvetica', '', 9);
        
        // Table header
        $pdf->SetFillColor(37, 99, 235);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(30, 7, __('Job ID', 'prospectra-ticketing-system'), 1, 0, 'C', true);
        $pdf->Cell(40, 7, __('Customer', 'prospectra-ticketing-system'), 1, 0, 'C', true);
        $pdf->Cell(35, 7, __('Time', 'prospectra-ticketing-system'), 1, 0, 'C', true);
        $pdf->Cell(30, 7, __('Quantity', 'prospectra-ticketing-system'), 1, 0, 'C', true);
        $pdf->Cell(45, 7, __('Machine', 'prospectra-ticketing-system'), 1, 1, 'C', true);
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(245, 245, 245);
        
        $fill = false;
        foreach ($production_plan as $plan) {
            $pdf->Cell(30, 6, $plan['job_id'], 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, $plan['customer'], 1, 0, 'L', $fill);
            $pdf->Cell(35, 6, $plan['start_finish'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, $plan['quantity'], 1, 0, 'C', $fill);
            $pdf->Cell(45, 6, $plan['machine'], 1, 1, 'C', $fill);
            $fill = !$fill;
        }
        
        $pdf->Ln(5);
    }
    
    /**
     * Add other sections (similar structure)
     */
    private static function add_upcoming_production_section($pdf, $report_id) {
        // Similar to production plan
        $upcoming = get_post_meta($report_id, '_pts_upcoming_production', true);
        if (! empty($upcoming)) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 7, __('Upcoming Production', 'prospectra-ticketing-system'), 0, 1);
            // Add table... 
            $pdf->Ln(5);
        }
    }
    
    private static function add_followup_tasks_section($pdf, $report_id) {
        $tasks = get_post_meta($report_id, '_pts_followup_tasks', true);
        if (!empty($tasks)) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 7, __('Follow-Up Tasks', 'prospectra-ticketing-system'), 0, 1);
            // Add table...
            $pdf->Ln(5);
        }
    }
    
    private static function add_issues_summary_section($pdf, $report_id) {
        $issues = get_post_meta($report_id, '_pts_issues_summary', true);
        if (!empty($issues)) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 7, __('Issues Summary', 'prospectra-ticketing-system'), 0, 1);
            // Add table...
            $pdf->Ln(5);
        }
    }
    
    private static function add_key_notes_section($pdf, $report_id) {
        $notes = get_post_meta($report_id, '_pts_key_notes', true);
        if (! empty($notes)) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 7, __('Key Notes / Instructions', 'prospectra-ticketing-system'), 0, 1);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 5, strip_tags($notes), 0, 'L');
            $pdf->Ln(5);
        }
    }
}
