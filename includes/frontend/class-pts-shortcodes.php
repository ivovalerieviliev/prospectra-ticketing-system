<?php
/**
 * Shortcodes Registration and Rendering
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Shortcodes {
    
    /**
     * Initialize shortcodes
     */
    public static function init() {
        add_shortcode('pts_shift_overview', array(__CLASS__, 'shift_overview'));
        add_shortcode('pts_ticket_details', array(__CLASS__, 'ticket_details'));
        add_shortcode('pts_create_ticket', array(__CLASS__, 'create_ticket'));
        add_shortcode('pts_report_history', array(__CLASS__, 'report_history'));
        add_shortcode('pts_create_handover', array(__CLASS__, 'create_handover'));
        add_shortcode('pts_search', array(__CLASS__, 'search'));
    }
    
    /**
     * Shift Overview Dashboard
     */
    public static function shift_overview($atts) {
        if (!current_user_can('view_tickets')) {
            return '<p>' . __('You do not have permission to view this content.', 'prospectra-ticketing-system') . '</p>';
        }
        
        ob_start();
        include PTS_PLUGIN_DIR .  'templates/shift-overview.php';
        return ob_get_clean();
    }
    
    /**
     * Ticket Details
     */
    public static function ticket_details($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);
        
        $ticket_id = absint($atts['id']);
        
        if (!$ticket_id && isset($_GET['ticket_id'])) {
            $ticket_id = absint($_GET['ticket_id']);
        }
        
        if (!$ticket_id || !current_user_can('view_tickets')) {
            return '<p>' . __('Invalid ticket or insufficient permissions.', 'prospectra-ticketing-system') . '</p>';
        }
        
        $ticket = get_post($ticket_id);
        
        if (!$ticket || $ticket->post_type !== 'pts_ticket') {
            return '<p>' . __('Ticket not found. ', 'prospectra-ticketing-system') . '</p>';
        }
        
        ob_start();
        include PTS_PLUGIN_DIR . 'templates/ticket-details.php';
        return ob_get_clean();
    }
    
    /**
     * Create Ticket Modal
     */
    public static function create_ticket($atts) {
        if (!current_user_can('create_ticket')) {
            return '<p>' . __('You do not have permission to create tickets.', 'prospectra-ticketing-system') . '</p>';
        }
        
        ob_start();
        include PTS_PLUGIN_DIR . 'templates/create-ticket.php';
        return ob_get_clean();
    }
    
    /**
     * Shift Report History
     */
    public static function report_history($atts) {
        if (!current_user_can('view_shift_reports')) {
            return '<p>' . __('You do not have permission to view reports.', 'prospectra-ticketing-system') . '</p>';
        }
        
        ob_start();
        include PTS_PLUGIN_DIR . 'templates/shift-report-history.php';
        return ob_get_clean();
    }
    
    /**
     * Create Handover Report
     */
    public static function create_handover($atts) {
        if (!current_user_can('create_shift_reports')) {
            return '<p>' . __('You do not have permission to create reports.', 'prospectra-ticketing-system') . '</p>';
        }
        
        ob_start();
        include PTS_PLUGIN_DIR . 'templates/create-handover-report.php';
        return ob_get_clean();
    }
    
    /**
     * Global Search
     */
    public static function search($atts) {
        ob_start();
        ?>
        <div class="pts-search-container">
            <input type="text" id="pts-global-search" placeholder="<?php esc_attr_e('Search documents, machines, spare parts etc.', 'prospectra-ticketing-system'); ?>">
            <button id="pts-search-btn"><span class="dashicons dashicons-search"></span></button>
            <div id="pts-search-results"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}
