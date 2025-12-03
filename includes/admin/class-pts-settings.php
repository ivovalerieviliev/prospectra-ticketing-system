<? php
/**
 * Settings Page Management
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Settings {
    
    /**
     * Initialize
     */
    public static function init() {
        add_action('admin_init', array(__CLASS__, 'register_settings'));
    }
    
    /**
     * Register settings
     */
    public static function register_settings() {
        register_setting('pts_settings_group', 'pts_settings', array(__CLASS__, 'sanitize_settings'));
    }
    
    /**
     * Render settings page
     */
    public static function render_page() {
        if (! current_user_can('manage_pts_settings')) {
            wp_die(__('You do not have permission to access this page.', 'prospectra-ticketing-system'));
        }
        
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        ?>
        <div class="wrap">
            <h1><?php _e('Prospectra Ticketing Settings', 'prospectra-ticketing-system'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="? page=pts-settings&tab=general" class="nav-tab <? php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General', 'prospectra-ticketing-system'); ?>
                </a>
                <a href="? page=pts-settings&tab=tickets" class="nav-tab <?php echo $active_tab === 'tickets' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Tickets', 'prospectra-ticketing-system'); ?>
                </a>
                <a href="?page=pts-settings&tab=shift-reports" class="nav-tab <?php echo $active_tab === 'shift-reports' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Shift Reports', 'prospectra-ticketing-system'); ?>
                </a>
                <a href="?page=pts-settings&tab=organizations" class="nav-tab <?php echo $active_tab === 'organizations' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Organizations', 'prospectra-ticketing-system'); ?>
                </a>
                <a href="?page=pts-settings&tab=permissions" class="nav-tab <?php echo $active_tab === 'permissions' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Permissions', 'prospectra-ticketing-system'); ?>
                </a>
                <a href="?page=pts-settings&tab=notifications" class="nav-tab <?php echo $active_tab === 'notifications' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Notifications', 'prospectra-ticketing-system'); ?>
                </a>
                <a href="?page=pts-settings&tab=exports" class="nav-tab <?php echo $active_tab === 'exports' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Exports', 'prospectra-ticketing-system'); ?>
                </a>
            </h2>
            
            <form method="post" action="options. php">
                <?php
                settings_fields('pts_settings_group');
                
                switch ($active_tab) {
                    case 'general':
                        include PTS_PLUGIN_DIR . 'includes/admin/views/settings-general.php';
                        break;
                    case 'tickets':
                        include PTS_PLUGIN_DIR .  'includes/admin/views/settings-tickets.php';
                        break;
                    case 'shift-reports':
                        include PTS_PLUGIN_DIR . 'includes/admin/views/settings-shift-reports.php';
                        break;
                    case 'organizations':
                        include PTS_PLUGIN_DIR . 'includes/admin/views/settings-organizations.php';
                        break;
                    case 'permissions':
                        include PTS_PLUGIN_DIR . 'includes/admin/views/settings-permissions.php';
                        break;
                    case 'notifications':
                        include PTS_PLUGIN_DIR .  'includes/admin/views/settings-notifications.php';
                        break;
                    case 'exports':
                        include PTS_PLUGIN_DIR . 'includes/admin/views/settings-exports.php';
                        break;
                }
                
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Sanitize settings
     */
    public static function sanitize_settings($input) {
        $sanitized = array();
        
        // Sanitize each section
        if (isset($input['general'])) {
            $sanitized['general'] = array(
                'enable_tickets' => ! empty($input['general']['enable_tickets']),
                'enable_shift_reports' => !empty($input['general']['enable_shift_reports']),
                'enable_shift_overview' => !empty($input['general']['enable_shift_overview']),
                'timezone' => sanitize_text_field($input['general']['timezone']),
                'date_format' => sanitize_text_field($input['general']['date_format']),
                'time_format' => sanitize_text_field($input['general']['time_format']),
            );
        }
        
        if (isset($input['tickets'])) {
            $sanitized['tickets'] = array(
                'statuses' => array_map(function($status) {
                    return array(
                        'name' => sanitize_text_field($status['name']),
                        'color' => sanitize_hex_color($status['color']),
                    );
                }, $input['tickets']['statuses']),
                'priorities' => array_map(function($priority) {
                    return array(
                        'name' => sanitize_text_field($priority['name']),
                        'color' => sanitize_hex_color($priority['color']),
                    );
                }, $input['tickets']['priorities']),
                'attachment_max_size' => absint($input['tickets']['attachment_max_size']),
                'allowed_mime_types' => array_map('sanitize_text_field', $input['tickets']['allowed_mime_types']),
                'max_files_per_ticket' => absint($input['tickets']['max_files_per_ticket']),
            );
        }
        
        // Merge with existing settings to preserve other tabs
        $existing = get_option('pts_settings', array());
        return array_merge($existing, $sanitized);
    }
}

PTS_Settings::init();
