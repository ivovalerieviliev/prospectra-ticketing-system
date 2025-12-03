<?php
/**
 * Create Issue Ticket Modal (Screen 2/Image 5)
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('pts_settings', array());
$priorities = isset($settings['tickets']['priorities']) ? $settings['tickets']['priorities'] : array();

// Get shift leaders
$shift_leaders = get_users(array(
    'role__in' => array('pts_shift_leader', 'pts_team_leader', 'administrator'),
));

$current_user = wp_get_current_user();
?>

<div class="pts-create-ticket-form">
    <div class="pts-modal-header">
        <h2><?php _e('Create Issue Ticket', 'prospectra-ticketing-system'); ?></h2>
        <button class="pts-close-modal" type="button">
            <span class="dashicons dashicons-no-alt"></span>
        </button>
    </div>
    
    <form id="pts-ticket-form" class="pts-modal-body">
        
        <!-- Ticket Title -->
        <div class="pts-form-group">
            <label for="pts-ticket-title"><?php _e('Ticket title', 'prospectra-ticketing-system'); ?> <span class="pts-required">*</span></label>
            <input 
                type="text" 
                id="pts-ticket-title" 
                name="title" 
                class="pts-input" 
                placeholder="<?php esc_attr_e('Add a title', 'prospectra-ticketing-system'); ?>"
                required
            >
        </div>
        
        <!-- Shift Leader -->
        <div class="pts-form-group">
            <label for="pts-shift-leader"><?php _e('Shift Leader', 'prospectra-ticketing-system'); ?></label>
            <select id="pts-shift-leader" name="shift_leader" class="pts-select">
                <? php foreach ($shift_leaders as $leader): ?>
                    <option value="<?php echo $leader->ID; ?>" <?php selected($leader->ID, $current_user->ID); ? >>
                        <?php echo esc_html($leader->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Email ID -->
        <div class="pts-form-group">
            <label for="pts-email-id"><?php _e('Email ID', 'prospectra-ticketing-system'); ?></label>
            <input 
                type="email" 
                id="pts-email-id" 
                name="email_id" 
                class="pts-input" 
                placeholder="<?php esc_attr_e('Placeholder Text', 'prospectra-ticketing-system'); ?>"
                value="<?php echo esc_attr($current_user->user_email); ?>"
            >
        </div>
        
        <!-- Category and Priority -->
        <div class="pts-form-row">
            <div class="pts-form-group">
                <label for="pts-category"><? php _e('Category', 'prospectra-ticketing-system'); ?></label>
                <select id="pts-category" name="category" class="pts-select">
                    <option value=""><?php _e('Select', 'prospectra-ticketing-system'); ?></option>
                    <option value="Maintenance"><?php _e('Maintenance', 'prospectra-ticketing-system'); ?></option>
                    <option value="Safety"><?php _e('Safety', 'prospectra-ticketing-system'); ?></option>
                    <option value="Quality"><?php _e('Quality', 'prospectra-ticketing-system'); ?></option>
                    <option value="Production"><?php _e('Production', 'prospectra-ticketing-system'); ?></option>
                    <option value="Other"><?php _e('Other', 'prospectra-ticketing-system'); ?></option>
                </select>
            </div>
            
            <div class="pts-form-group">
                <label for="pts-priority"><?php _e('Priority', 'prospectra-ticketing-system'); ?></label>
                <select id="pts-priority" name="priority" class="pts-select">
                    <option value=""><?php _e('Select', 'prospectra-ticketing-system'); ?></option>
                    <? php foreach ($priorities as $priority): ?>
                        <option value="<?php echo esc_attr($priority['name']); ?>">
                            <?php echo esc_html($priority['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Ticket Description -->
        <div class="pts-form-group">
            <label for="pts-ticket-description">
                <?php _e('Ticket Description', 'prospectra-ticketing-system'); ? > 
                <span class="pts-required">*</span>
            </label>
            
            <div class="pts-rich-editor-toolbar pts-editor-toolbar-compact">
                <button type="button" class="pts-editor-btn" data-command="bold"><strong>B</strong></button>
                <button type="button" class="pts-editor-btn" data-command="italic"><em>i</em></button>
                <button type="button" class="pts-editor-btn" data-command="underline"><u>U</u></button>
                <button type="button" class="pts-editor-btn" data-command="strikethrough"><s>S</s></button>
                <span class="pts-toolbar-separator"></span>
                <button type="button" class="pts-editor-btn" data-command="insertUnorderedList">
                    <span class="dashicons dashicons-editor-ul"></span>
                </button>
                <button type="button" class="pts-editor-btn" data-command="insertOrderedList">
                    <span class="dashicons dashicons-editor-ol"></span>
                </button>
                <button type="button" class="pts-editor-btn" data-command="indent">
                    <span class="dashicons dashicons-editor-indent"></span>
                </button>
                <button type="button" class="pts-editor-btn" data-command="outdent">
                    <span class="dashicons dashicons-editor-outdent"></span>
                </button>
                <button type="button" class="pts-editor-btn" data-command="createLink">
                    <span class="dashicons dashicons-admin-links"></span>
                </button>
            </div>
            
            <div 
                id="pts-ticket-description" 
                class="pts-rich-editor pts-ticket-editor" 
                contenteditable="true" 
                data-placeholder="<?php esc_attr_e('Add ticket description', 'prospectra-ticketing-system'); ?>"
            ></div>
            
            <div class="pts-char-counter">
                <span id="pts-char-count">0</span>/200
            </div>
            <p class="pts-help-text"><?php _e('Minimum 20 characters required', 'prospectra-ticketing-system'); ?></p>
        </div>
        
        <!-- File Upload -->
        <div class="pts-form-group">
            <div class="pts-file-upload-area" id="pts-file-drop-zone">
                <div class="pts-upload-icon">
                    <span class="dashicons dashicons-upload"></span>
                </div>
                <p class="pts-upload-text">
                    <?php _e('Select a file or drag and drop here', 'prospectra-ticketing-system'); ?>
                </p>
                <p class="pts-upload-hint">
                    <?php _e('JPG, PNG or PDF, file size no more than 2MB', 'prospectra-ticketing-system'); ?>
                </p>
                <button type="button" class="pts-btn pts-btn-primary" id="pts-select-file-btn">
                    <? php _e('Select File', 'prospectra-ticketing-system'); ?>
                </button>
                <input type="file" id="pts-file-upload-input" multiple accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
            </div>
            
            <div id="pts-upload-progress" class="pts-upload-progress" style="display: none;">
                <div class="pts-upload-item">
                    <div class="pts-upload-info">
                        <span class="pts-upload-filename">your-file-here. PDF</span>
                        <button type="button" class="pts-upload-cancel">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                    <div class="pts-progress-bar">
                        <div class="pts-progress-fill" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            
            <div id="pts-uploaded-files" class="pts-uploaded-files"></div>
        </div>
        
        <!-- Form Actions -->
        <div class="pts-modal-footer">
            <button type="button" class="pts-btn pts-btn-outline pts-cancel-btn">
                <?php _e('Cancel', 'prospectra-ticketing-system'); ?>
            </button>
            <button type="submit" class="pts-btn pts-btn-primary" id="pts-create-ticket-submit">
                <span class="dashicons dashicons-plus"></span>
                <?php _e('Create Ticket', 'prospectra-ticketing-system'); ?>
            </button>
        </div>
        
    </form>
</div>
