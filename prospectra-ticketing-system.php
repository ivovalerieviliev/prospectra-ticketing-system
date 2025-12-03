<?php
/**
 * Plugin Name: Prospectra Ticketing System
 * Plugin URI: https://github.com/ivovalerieviliev/prospectra-ticketing-system
 * Description: A comprehensive ticketing and shift handover management system
 * Version: 1.0.0
 * Author: ivovalerieviliev
 * Text Domain: prospectra-ticketing-system
 * Requires at least: 6.4
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('PTS_VERSION', '1.0.0');
define('PTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PTS_PLUGIN_BASENAME', plugin_basename(__FILE__));

class Prospectra_Ticketing_System {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        $files = array(
            'includes/core/class-pts-post-types.php',
            'includes/core/class-pts-taxonomies.php',
            'includes/core/class-pts-roles.php',
            'includes/core/class-pts-capabilities. php',
            'includes/core/class-pts-loader.php',
            'includes/admin/class-pts-admin-menu. php',
            'includes/admin/class-pts-settings.php',
            'includes/frontend/class-pts-shortcodes. php',
            'includes/frontend/class-pts-ajax-handlers.php',
            'includes/metrics/class-pts-metrics-calculator.php',
            'includes/notifications/class-pts-notification-manager.php',
            'includes/attachments/class-pts-file-handler.php',
        );
        
        foreach ($files as $file) {
            $filepath = PTS_PLUGIN_DIR . $file;
            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
    }
    
    public function activate() {
        $this->create_tables();
        
        if (class_exists('PTS_Post_Types')) {
            PTS_Post_Types::register();
        }
        
        if (class_exists('PTS_Taxonomies')) {
            PTS_Taxonomies::register();
        }
        
        if (class_exists('PTS_Roles')) {
            PTS_Roles::create_roles();
        }
        
        $this->set_default_settings();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $comments_table = $wpdb->prefix . 'pts_comments';
        $comments_sql = "CREATE TABLE IF NOT EXISTS $comments_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            content longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_system_event tinyint(1) DEFAULT 0,
            parent_id bigint(20) UNSIGNED DEFAULT NULL,
            PRIMARY KEY (id),
            KEY ticket_id (ticket_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        dbDelta($comments_sql);
        
        $metrics_table = $wpdb->prefix . 'pts_metrics_cache';
        $metrics_sql = "CREATE TABLE IF NOT EXISTS $metrics_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            metric_type varchar(100) NOT NULL,
            value text NOT NULL,
            calculated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY metric_type (metric_type)
        ) $charset_collate;";
        
        dbDelta($metrics_sql);
    }
    
    private function set_default_settings() {
        $defaults = array(
            'general' => array(
                'enable_tickets' => true,
                'enable_shift_reports' => true,
                'enable_shift_overview' => true,
                'timezone' => 'UTC',
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
            ),
            'tickets' => array(
                'statuses' => array(
                    array('name' => 'Open', 'color' => '#3b82f6'),
                    array('name' => 'In Progress', 'color' => '#f59e0b'),
                    array('name' => 'Resolved', 'color' => '#10b981'),
                    array('name' => 'Closed', 'color' => '#6b7280'),
                ),
                'priorities' => array(
                    array('name' => 'Low', 'color' => '#10b981'),
                    array('name' => 'Medium', 'color' => '#f59e0b'),
                    array('name' => 'High', 'color' => '#ef4444'),
                ),
            ),
        );
        
        if (! get_option('pts_settings')) {
            add_option('pts_settings', $defaults);
        }
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('prospectra-ticketing-system', false, dirname(PTS_PLUGIN_BASENAME) . '/languages');
    }
    
    public function init() {
        if (class_exists('PTS_Post_Types')) {
            PTS_Post_Types::register();
        }
        
        if (class_exists('PTS_Taxonomies')) {
            PTS_Taxonomies::register();
        }
        
        if (class_exists('PTS_Shortcodes')) {
            PTS_Shortcodes::init();
        }
        
        if (class_exists('PTS_Ajax_Handlers')) {
            PTS_Ajax_Handlers::init();
        }
    }
    
    public function register_admin_menu() {
        add_menu_page(
            __('Prospectra Ticketing', 'prospectra-ticketing-system'),
            __('Prospectra Ticketing', 'prospectra-ticketing-system'),
            'read',
            'prospectra-ticketing',
            array($this, 'dashboard_page'),
            'dashicons-tickets-alt',
            30
        );
        
        add_submenu_page(
            'prospectra-ticketing',
            __('Dashboard', 'prospectra-ticketing-system'),
            __('Dashboard', 'prospectra-ticketing-system'),
            'read',
            'prospectra-ticketing',
            array($this, 'dashboard_page')
        );
        
        add_submenu_page(
            'prospectra-ticketing',
            __('Settings', 'prospectra-ticketing-system'),
            __('Settings', 'prospectra-ticketing-system'),
            'manage_options',
            'pts-settings',
            array('PTS_Settings', 'render_page')
        );
    }
    
    public function dashboard_page() {
        $ticket_count = wp_count_posts('pts_ticket');
        $report_count = wp_count_posts('pts_shift_report');
        ?>
        <div class="wrap">
            <h1><?php _e('Prospectra Ticketing System', 'prospectra-ticketing-system'); ?></h1>
            
            <div class="card" style="max-width: 800px;">
                <h2><?php _e('Welcome! ', 'prospectra-ticketing-system'); ?></h2>
                <p><?php _e('Your ticketing system is active.  Use these shortcodes on your pages:', 'prospectra-ticketing-system'); ?></p>
                
                <table class="widefat" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th><strong>Shortcode</strong></th>
                            <th><strong>Description</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[pts_shift_overview]</code></td>
                            <td>Main shift overview dashboard</td>
                        </tr>
                        <tr>
                            <td><code>[pts_create_ticket]</code></td>
                            <td>Create ticket button with modal</td>
                        </tr>
                        <tr>
                            <td><code>[pts_ticket_details id="123"]</code></td>
                            <td>View specific ticket</td>
                        </tr>
                        <tr>
                            <td><code>[pts_report_history]</code></td>
                            <td>Shift report history</td>
                        </tr>
                        <tr>
                            <td><code>[pts_create_handover]</code></td>
                            <td>Create handover report form</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3 style="margin-top: 30px;"><?php _e('Quick Stats', 'prospectra-ticketing-system'); ?></h3>
                <p>
                    <strong><? php _e('Tickets:', 'prospectra-ticketing-system'); ?></strong> 
                    <?php echo $ticket_count ?  $ticket_count->publish : 0; ? > | 
                    <strong><?php _e('Reports:', 'prospectra-ticketing-system'); ?></strong> 
                    <?php echo $report_count ? $report_count->publish : 0; ?>
                </p>
                
                <div style="margin-top: 20px; padding: 15px; background: #d1ecf1; border: 1px solid #0c5460; border-radius: 4px;">
                    <p style="margin: 0;"><strong>🎯 Next Steps:</strong></p>
                    <ol style="margin: 10px 0 0 20px;">
                        <li>Create a new page and add the <code>[pts_shift_overview]</code> shortcode</li>
                        <li>Visit the page to see your dashboard</li>
                        <li>Click "Create Ticket" to test ticket creation</li>
                        <li>Customize settings in Settings tab</li>
                    </ol>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function enqueue_frontend_assets() {
        // CSS
        wp_enqueue_style('pts-main', PTS_PLUGIN_URL . 'assets/css/pts-main.css', array(), PTS_VERSION);
        wp_enqueue_style('pts-tickets', PTS_PLUGIN_URL . 'assets/css/pts-tickets.css', array('pts-main'), PTS_VERSION);
        wp_enqueue_style('pts-reports', PTS_PLUGIN_URL . 'assets/css/pts-reports. css', array('pts-main'), PTS_VERSION);
        
        // WordPress Dashicons
        wp_enqueue_style('dashicons');
        
        // JavaScript
        wp_enqueue_script('pts-main', PTS_PLUGIN_URL .  'assets/js/pts-main.js', array('jquery'), PTS_VERSION, true);
        wp_enqueue_script('pts-tickets', PTS_PLUGIN_URL . 'assets/js/pts-tickets.js', array('jquery', 'pts-main'), PTS_VERSION, true);
        
        // Localize script
        wp_localize_script('pts-main', 'ptsAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pts_nonce'),
            'strings' => array(
                'confirm_delete' => __('Are you sure? ', 'prospectra-ticketing-system'),
                'error' => __('An error occurred', 'prospectra-ticketing-system'),
                'success' => __('Success', 'prospectra-ticketing-system'),
            ),
        ));
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'prospectra-ticketing') !== false) {
            wp_enqueue_style('pts-admin', PTS_PLUGIN_URL . 'assets/css/pts-main.css', array(), PTS_VERSION);
        }
    }
}

// Initialize
function pts_init() {
    return Prospectra_Ticketing_System::get_instance();
}

pts_init();
