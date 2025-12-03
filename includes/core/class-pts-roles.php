<? php
/**
 * Custom Roles Management
 * Creates: B2C Agent, B2B Agent, Shift Leader, Team Leader, Maintenance Team
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Roles {
    
    /**
     * Create custom roles
     */
    public static function create_roles() {
        // Get capabilities
        $caps = PTS_Capabilities::get_all_capabilities();
        
        // B2C Agent - can view and create tickets, add comments
        add_role('pts_b2c_agent', __('B2C Agent', 'prospectra-ticketing-system'), array(
            'read' => true,
            'view_tickets' => true,
            'create_ticket' => true,
            'edit_ticket' => true,
            'comment_on_ticket' => true,
        ));
        
        // B2B Agent - similar to B2C but may have different org access
        add_role('pts_b2b_agent', __('B2B Agent', 'prospectra-ticketing-system'), array(
            'read' => true,
            'view_tickets' => true,
            'create_ticket' => true,
            'edit_ticket' => true,
            'comment_on_ticket' => true,
            'assign_ticket' => true,
        ));
        
        // Shift Leader - full ticket + shift report management
        add_role('pts_shift_leader', __('Shift Leader', 'prospectra-ticketing-system'), array(
            'read' => true,
            'view_tickets' => true,
            'create_ticket' => true,
            'edit_ticket' => true,
            'delete_ticket' => true,
            'manage_tickets' => true,
            'comment_on_ticket' => true,
            'assign_ticket' => true,
            'view_shift_reports' => true,
            'create_shift_reports' => true,
            'edit_shift_reports' => true,
            'export_shift_reports' => true,
        ));
        
        // Team Leader - supervise team, advanced permissions
        add_role('pts_team_leader', __('Team Leader', 'prospectra-ticketing-system'), array(
            'read' => true,
            'view_tickets' => true,
            'create_ticket' => true,
            'edit_ticket' => true,
            'delete_ticket' => true,
            'manage_tickets' => true,
            'comment_on_ticket' => true,
            'assign_ticket' => true,
            'view_shift_reports' => true,
            'create_shift_reports' => true,
            'edit_shift_reports' => true,
            'delete_shift_reports' => true,
            'export_shift_reports' => true,
            'view_orders' => true,
            'create_orders' => true,
            'edit_orders' => true,
        ));
        
        // Maintenance Team - technical focus on tickets
        add_role('pts_maintenance_team', __('Maintenance Team', 'prospectra-ticketing-system'), array(
            'read' => true,
            'view_tickets' => true,
            'edit_ticket' => true,
            'comment_on_ticket' => true,
            'view_orders' => true,
        ));
        
        // Add capabilities to administrator
        $admin = get_role('administrator');
        if ($admin) {
            foreach ($caps as $cap) {
                $admin->add_cap($cap);
            }
        }
    }
    
    /**
     * Remove custom roles
     */
    public static function remove_roles() {
        remove_role('pts_b2c_agent');
        remove_role('pts_b2b_agent');
        remove_role('pts_shift_leader');
        remove_role('pts_team_leader');
        remove_role('pts_maintenance_team');
    }
}
