<? php
/**
 * Global Header - Logo, Search, User Menu
 */

if (! defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$user_initials = strtoupper(substr($current_user->display_name, 0, 1));
if (strpos($current_user->display_name, ' ') !== false) {
    $name_parts = explode(' ', $current_user->display_name);
    $user_initials = strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
}
?>

<header class="pts-global-header">
    <div class="pts-header-left">
        <div class="pts-logo">
            <a href="<?php echo home_url(); ?>">openpack. </a>
        </div>
    </div>
    
    <div class="pts-header-center">
        <div class="pts-global-search">
            <span class="dashicons dashicons-search pts-search-icon"></span>
            <input 
                type="text" 
                id="pts-global-search-input" 
                placeholder="<?php esc_attr_e('Search documents, machines, spare parts etc.', 'prospectra-ticketing-system'); ?>"
                autocomplete="off"
            >
            <div id="pts-search-dropdown" class="pts-search-dropdown" style="display: none;"></div>
        </div>
    </div>
    
    <div class="pts-header-right">
        <button class="pts-header-icon-btn" id="pts-notifications-btn" title="<?php esc_attr_e('Notifications', 'prospectra-ticketing-system'); ?>">
            <span class="dashicons dashicons-bell"></span>
            <span class="pts-notification-badge">3</span>
        </button>
        
        <button class="pts-header-icon-btn" id="pts-help-btn" title="<?php esc_attr_e('Help', 'prospectra-ticketing-system'); ?>">
            <span class="dashicons dashicons-editor-help"></span>
        </button>
        
        <div class="pts-user-menu-wrapper">
            <button class="pts-user-avatar-btn" id="pts-user-menu-btn">
                <span class="pts-user-avatar"><?php echo esc_html($user_initials); ?></span>
            </button>
            
            <div class="pts-user-dropdown" id="pts-user-dropdown" style="display: none;">
                <div class="pts-user-info">
                    <strong><?php echo esc_html($current_user->display_name); ?></strong>
                    <span><? php echo esc_html($current_user->user_email); ? ></span>
                </div>
                <div class="pts-dropdown-divider"></div>
                <a href="<?php echo admin_url('profile.php'); ? >" class="pts-dropdown-item">
                    <span class="dashicons dashicons-admin-users"></span>
                    <?php _e('Profile', 'prospectra-ticketing-system'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=pts-settings'); ?>" class="pts-dropdown-item">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Account Settings', 'prospectra-ticketing-system'); ?>
                </a>
                <div class="pts-dropdown-divider"></div>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="pts-dropdown-item">
                    <span class="dashicons dashicons-exit"></span>
                    <?php _e('Log out', 'prospectra-ticketing-system'); ?>
                </a>
            </div>
        </div>
    </div>
</header>
