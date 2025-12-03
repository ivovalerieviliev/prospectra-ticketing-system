<?php
/**
 * Tickets Settings Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('pts_settings', array());
$tickets = isset($settings['tickets']) ? $settings['tickets'] : array();
?>

<h3><?php _e('Ticket Statuses', 'prospectra-ticketing-system'); ?></h3>
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th><? php _e('Status Name', 'prospectra-ticketing-system'); ?></th>
            <th><?php _e('Color', 'prospectra-ticketing-system'); ?></th>
            <th><?php _e('Actions', 'prospectra-ticketing-system'); ?></th>
        </tr>
    </thead>
    <tbody id="pts-statuses-list">
        <?php
        $statuses = isset($tickets['statuses']) ? $tickets['statuses'] : array();
        foreach ($statuses as $index => $status) {
            ?>
            <tr>
                <td>
                    <input type="text" name="pts_settings[tickets][statuses][<?php echo $index; ?>][name]" value="<?php echo esc_attr($status['name']); ?>" class="regular-text">
                </td>
                <td>
                    <input type="color" name="pts_settings[tickets][statuses][<?php echo $index; ?>][color]" value="<?php echo esc_attr($status['color']); ?>">
                </td>
                <td>
                    <button type="button" class="button pts-remove-status"><?php _e('Remove', 'prospectra-ticketing-system'); ?></button>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<button type="button" class="button" id="pts-add-status"><?php _e('Add Status', 'prospectra-ticketing-system'); ?></button>

<h3><?php _e('Priority Levels', 'prospectra-ticketing-system'); ?></h3>
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th><?php _e('Priority Name', 'prospectra-ticketing-system'); ?></th>
            <th><?php _e('Color', 'prospectra-ticketing-system'); ? ></th>
            <th><? php _e('Actions', 'prospectra-ticketing-system'); ?></th>
        </tr>
    </thead>
    <tbody id="pts-priorities-list">
        <?php
        $priorities = isset($tickets['priorities']) ? $tickets['priorities'] : array();
        foreach ($priorities as $index => $priority) {
            ?>
            <tr>
                <td>
                    <input type="text" name="pts_settings[tickets][priorities][<?php echo $index; ?>][name]" value="<?php echo esc_attr($priority['name']); ?>" class="regular-text">
                </td>
                <td>
                    <input type="color" name="pts_settings[tickets][priorities][<? php echo $index; ?>][color]" value="<?php echo esc_attr($priority['color']); ?>">
                </td>
                <td>
                    <button type="button" class="button pts-remove-priority"><? php _e('Remove', 'prospectra-ticketing-system'); ?></button>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<button type="button" class="button" id="pts-add-priority"><?php _e('Add Priority', 'prospectra-ticketing-system'); ?></button>

<h3><?php _e('Attachment Settings', 'prospectra-ticketing-system'); ?></h3>
<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Maximum File Size (bytes)', 'prospectra-ticketing-system'); ?></th>
        <td>
            <input type="number" name="pts_settings[tickets][attachment_max_size]" value="<?php echo esc_attr(isset($tickets['attachment_max_size']) ? $tickets['attachment_max_size'] : 2097152); ?>" class="regular-text">
            <p class="description"><?php _e('Default: 2097152 (2MB)', 'prospectra-ticketing-system'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row"><?php _e('Allowed File Types', 'prospectra-ticketing-system'); ?></th>
        <td>
            <? php
            $allowed_types = isset($tickets['allowed_mime_types']) ? $tickets['allowed_mime_types'] : array('image/jpeg', 'image/png', 'application/pdf');
            $mime_types = array(
                'image/jpeg' => 'JPEG Images',
                'image/png' => 'PNG Images',
                'application/pdf' => 'PDF Documents',
                'image/gif' => 'GIF Images',
                'application/msword' => 'Word Documents',
                'application/vnd.ms-excel' => 'Excel Spreadsheets',
            );
            
            foreach ($mime_types as $mime => $label) {
                $checked = in_array($mime, $allowed_types);
                printf(
                    '<label><input type="checkbox" name="pts_settings[tickets][allowed_mime_types][]" value="%s" %s> %s</label><br>',
                    esc_attr($mime),
                    checked($checked, true, false),
                    esc_html($label)
                );
            }
            ?>
        </td>
    </tr>
    
    <tr>
        <th scope="row"><?php _e('Max Files Per Ticket', 'prospectra-ticketing-system'); ?></th>
        <td>
            <input type="number" name="pts_settings[tickets][max_files_per_ticket]" value="<?php echo esc_attr(isset($tickets['max_files_per_ticket']) ? $tickets['max_files_per_ticket'] : 5); ?>" min="1" max="20">
        </td>
    </tr>
</table>
