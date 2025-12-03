<?php
/**
 * Admin Menu Management
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Admin_Menu {
    
    /**
     * Initialize
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'register_menu'));
    }
    
    /**
     * Register admin menu
     */
    public static function register_menu() {
        // Main menu
        add_menu_page(
            __('Prospectra Ticketing', 'prospectra-ticketing-system'),
            __('Prospectra Ticketing', 'prospectra-ticketing-system'),
            'view_tickets',
            'prospectra-ticketing',
            array(__CLASS__, 'dashboard_page'),
            'dashicons-tickets-alt',
            30
        );
        
        // Tickets submenu
        add_submenu_page(
            'prospectra-ticketing',
            __('Tickets', 'prospectra-ticketing-system'),
            __('Tickets', 'prospectra-ticketing-system'),
            'view_tickets',
            'edit.php?post_type=pts_ticket'
        );
        
        // Shift Reports submenu
        add_submenu_page(
            'prospectra-ticketing',
            __('Shift Reports', 'prospectra-ticketing-system'),
            __('Shift Reports', 'prospectra-ticketing-system'),
            'view_shift_reports',
            'edit.php?post_type=pts_shift_report'
        );
        
        // Orders submenu
        add_submenu_page(
            'prospectra-ticketing',
            __('Orders', 'prospectra-ticketing-system'),
            __('Orders', 'prospectra-ticketing-system'),
            'view_orders',
            'edit.php?post_type=pts_order'
        );
        
        // Settings submenu
        add_submenu_page(
            'prospectra-ticketing',
            __('Settings', 'prospectra-ticketing-system'),
            __('Settings', 'prospectra-ticketing-system'),
            'manage_pts_settings',
            'pts-settings',
            array('PTS_Settings', 'render_page')
        );
    }
    
    /**
     * Dashboard page
     */
    public static function dashboard_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Prospectra Ticketing System', 'prospectra-ticketing-system'); ?></h1>
            <p><?php _e('Welcome to your ticketing and shift management system. ', 'prospectra-ticketing-system'); ?></p>
            
            <div class="pts-dashboard-widgets">
                <div class="pts-widget">
                    <h2><?php _e('Quick Stats', 'prospectra-ticketing-system'); ?></h2>
                    <? php
                    $open_tickets = wp_count_posts('pts_ticket');
                    $reports_count = wp_count_posts('pts_shift_report');
                    ?>
                    <ul>
                        <li><? php printf(__('Open Tickets: %d', 'prospectra-ticketing-system'), $open_tickets->publish); ?></li>
                        <li><?php printf(__('Total Reports: %d', 'prospectra-ticketing-system'), $reports_count->publish); ?></li>
                    </ul>
                </div>
                
                <div class="pts-widget">
                    <h2><? php _e('Quick Links', 'prospectra-ticketing-system'); ?></h2>
                    <ul>
                        <li><a href="<?php echo admin_url('post-new.php?post_type=pts_ticket'); ?>"><? php _e('Create New Ticket', 'prospectra-ticketing-system'); ? ></a></li>
                        <li><a href="<?php echo admin_url('post-new.php?post_type=pts_shift_report'); ? >"><?php _e('Create Shift Report', 'prospectra-ticketing-system'); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=pts-settings'); ?>"><?php _e('Plugin Settings', 'prospectra-ticketing-system'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
}

PTS_Admin_Menu::init();
