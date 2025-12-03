<?php
/**
 * Capabilities Management
 * Defines all custom capabilities for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Capabilities {
    
    /**
     * Get all custom capabilities
     */
    public static function get_all_capabilities() {
        return array(
            // Ticket capabilities
            'view_tickets',
            'create_ticket',
            'edit_ticket',
            'delete_ticket',
            'manage_tickets',
            'comment_on_ticket',
            'assign_ticket',
            
            // Shift report capabilities
            'view_shift_reports',
            'create_shift_reports',
            'edit_shift_reports',
            'delete_shift_reports',
            'export_shift_reports',
            
            // Order capabilities
            'view_orders',
            'create_orders',
            'edit_orders',
            'delete_orders',
            
            // Settings
            'manage_pts_settings',
        );
    }
    
    /**
     * Check if user has capability
     */
    public static function user_can($capability, $user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, $capability);
    }
    
    /**
     * Get users by capability
     */
    public static function get_users_by_capability($capability) {
        $users = get_users(array(
            'capability' => $capability,
        ));
        
        return $users;
    }
}
