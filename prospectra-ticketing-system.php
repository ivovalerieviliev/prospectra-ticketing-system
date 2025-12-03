<?php
/**
 * Plugin Name: Prospectra Ticketing System
 * Plugin URI: https://github.com/ivovalerieviliev/prospectra-ticketing-system
 * Description: A comprehensive ticketing and shift handover management system for WordPress with email integration, real-time metrics, and PDF/Excel export capabilities.
 * Version: 1.0.0
 * Author: ivovalerieviliev
 * Author URI: https://github.com/ivovalerieviliev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: prospectra-ticketing-system
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 8.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PTS_VERSION', '1.0.0');
define('PTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PTS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class - Prospectra Ticketing System
 */
class Prospectra_Ticketing_System {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Core classes
        require_once PTS_PLUGIN_DIR . 'includes/core/class-pts-loader.php';
        require_once PTS_PLUGIN_DIR . 'includes/core/class-pts-post-types.php';
        require_once PTS_PLUGIN_DIR . 'includes/core/class-pts-taxonomies.php';
        require_once PTS_PLUGIN_DIR . 'includes/core/class-pts-roles.php';
        require_once PTS_PLUGIN_DIR . 'includes/core/class-pts-capabilities.php';
        
        // Admin classes
        require_once PTS_PLUGIN_DIR . 'includes/admin/class-pts-admin-menu.php';
        require_once PTS_PLUGIN_DIR . 'includes/admin/class-pts-settings.php';
        
        // Frontend classes
        require_once PTS_PLUGIN_DIR . 'includes/frontend/class-pts-shortcodes.php';
        require_once PTS_PLUGIN_DIR . 'includes/frontend/class-pts-ajax-handlers.php';
        
        // Feature classes
        require_once PTS_PLUGIN_DIR . 'includes/email/class-pts-email-connector.php';
        require_once PTS_PLUGIN_DIR . 'includes/email/class-pts-email-parser.php';
        require_once PTS_PLUGIN_DIR . 'includes/email/class-pts-email-sender.php';
        require_once PTS_PLUGIN_DIR . 'includes/export/class-pts-pdf-generator.php';
        require_once PTS_PLUGIN_DIR . 'includes/export/class-pts-excel-generator.php';
        require_once PTS_PLUGIN_DIR . 'includes/metrics/class-pts-metrics-calculator.php';
        require_once PTS_PLUGIN_DIR . 'includes/attachments/class-pts-file-handler.php';
        require_once PTS_PLUGIN_DIR . 'includes/notifications/class-pts-notification-manager.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create custom database tables
        $this->create_tables();
        
        // Register post types and taxonomies
        PTS_Post_Types::register();
        PTS_Taxonomies::register();
        
        // Create custom roles
        PTS_Roles::create_roles();
        
        // Set default settings
        $this->set_default_settings();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Create custom database tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Comments table
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
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        dbDelta($comments_sql);
        
        // Metrics cache table
        $metrics_table = $wpdb->prefix . 'pts_metrics_cache';
        $metrics_sql = "CREATE TABLE IF NOT EXISTS $metrics_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            metric_type varchar(100) NOT NULL,
            value text NOT NULL,
            calculated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY metric_type (metric_type),
            KEY calculated_at (calculated_at)
        ) $charset_collate;";
        
        dbDelta($metrics_sql);
    }
    
    /**
     * Set default settings
     */
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
                    array('name' => 'Emergency', 'color' => '#dc2626'),
                ),
                'attachment_max_size' => 2097152,
                'allowed_mime_types' => array('image/jpeg', 'image/png', 'application/pdf'),
                'max_files_per_ticket' => 5,
            ),
            'shift_reports' => array(
                'shift_types' => array(
                    array('name' => 'Morning', 'start' => '08:00', 'end' => '14:00'),
                    array('name' => 'Afternoon', 'start' => '14:00', 'end' => '20:00'),
                    array('name' => 'Evening', 'start' => '20:00', 'end' => '02:00'),
                    array('name' => 'Night', 'start' => '02:00', 'end' => '08:00'),
                ),
                'enabled_sections' => array('production_plan', 'upcoming_production', 'followup_tasks', 'issues_summary'),
            ),
            'notifications' => array(
                'new_ticket' => true,
                'status_changed' => true,
                'ticket_assigned' => true,
                'new_comment' => true,
                'new_report' => true,
            ),
            'exports' => array(
                'default_format' => 'pdf',
                'company_logo' => '',
                'footer_text' => '',
            ),
        );
        
        add_option('pts_settings', $defaults);
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'prospectra-ticketing-system',
            false,
            dirname(PTS_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        PTS_Post_Types::register();
        PTS_Taxonomies::register();
        PTS_Shortcodes::init();
        PTS_Ajax_Handlers::init();
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style('pts-main', PTS_PLUGIN_URL . 'assets/css/pts-main.css', array(), PTS_VERSION);
        wp_enqueue_style('pts-tickets', PTS_PLUGIN_URL . 'assets/css/pts-tickets.css', array('pts-main'), PTS_VERSION);
        wp_enqueue_style('pts-reports', PTS_PLUGIN_URL . 'assets/css/pts-reports.css', array('pts-main'), PTS_VERSION);
        
        wp_enqueue_script('pts-main', PTS_PLUGIN_URL . 'assets/js/pts-main.js', array('jquery'), PTS_VERSION, true);
        wp_enqueue_script('pts-tickets', PTS_PLUGIN_URL . 'assets/js/pts-tickets.js', array('jquery', 'pts-main'), PTS_VERSION, true);
        wp_enqueue_script('pts-tickets-attachments', PTS_PLUGIN_URL . 'assets/js/pts-tickets-attachments.js', array('jquery', 'pts-tickets'), PTS_VERSION, true);
        wp_enqueue_script('pts-handover', PTS_PLUGIN_URL . 'assets/js/pts-handover.js', array('jquery', 'pts-main'), PTS_VERSION, true);
        wp_enqueue_script('pts-overview', PTS_PLUGIN_URL . 'assets/js/pts-overview.js', array('jquery', 'pts-main'), PTS_VERSION, true);
        wp_enqueue_script('pts-inline-edit', PTS_PLUGIN_URL . 'assets/js/pts-inline-edit.js', array('jquery', 'pts-main'), PTS_VERSION, true);
        
        wp_localize_script('pts-main', 'ptsAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pts_nonce'),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this?', 'prospectra-ticketing-system'),
                'unsaved_changes' => __('You have unsaved changes. Are you sure you want to leave?', 'prospectra-ticketing-system'),
                'file_too_large' => __('File size exceeds maximum allowed size.', 'prospectra-ticketing-system'),
                'invalid_file_type' => __('Invalid file type.', 'prospectra-ticketing-system'),
            ),
        ));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'prospectra-ticketing') === false) {
            return;
        }
        
        wp_enqueue_style('pts-admin', PTS_PLUGIN_URL . 'assets/css/pts-admin.css', array(), PTS_VERSION);
        wp_enqueue_script('pts-admin', PTS_PLUGIN_URL . 'assets/js/pts-admin.js', array('jquery'), PTS_VERSION, true);
        
        wp_localize_script('pts-admin', 'ptsAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pts_admin_nonce'),
        ));
    }
}

/**
 * Initialize the plugin
 */
function pts_init() {
    return Prospectra_Ticketing_System::get_instance();
}

pts_init();