<?php
/**
 * General Settings Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('pts_settings', array());
$general = isset($settings['general']) ? $settings['general'] : array();
?>

<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Enable Tickets', 'prospectra-ticketing-system'); ?></th>
        <td>
            <label>
                <input type="checkbox" name="pts_settings[general][enable_tickets]" value="1" <?php checked(! empty($general['enable_tickets'])); ?>>
                <?php _e('Enable ticketing system', 'prospectra-ticketing-system'); ?>
            </label>
        </td>
    </tr>
    
    <tr>
        <th scope="row"><?php _e('Enable Shift Reports', 'prospectra-ticketing-system'); ?></th>
        <td>
            <label>
                <input type="checkbox" name="pts_settings[general][enable_shift_reports]" value="1" <?php checked(!empty($general['enable_shift_reports'])); ?>>
                <?php _e('Enable shift handover reports', 'prospectra-ticketing-system'); ?>
            </label>
        </td>
    </tr>
    
    <tr>
        <th scope="row"><?php _e('Enable Shift Overview', 'prospectra-ticketing-system'); ? ></th>
        <td>
            <label>
                <input type="checkbox" name="pts_settings[general][enable_shift_overview]" value="1" <?php checked(!empty($general['enable_shift_overview'])); ? >>
                <?php _e('Enable shift overview dashboard', 'prospectra-ticketing-system'); ?>
            </label>
        </td>
    </tr>
    
    <tr>
        <th scope="row"><?php _e('Timezone', 'prospectra-ticketing-system'); ?></th>
        <td>
            <select name="pts_settings[general][timezone]">
                <?php
                $timezones = timezone_identifiers_list();
                $current_tz = isset($general['timezone']) ? $general['timezone'] : 'UTC';
                foreach ($timezones as $tz) {
                    printf('<option value="%s" %s>%s</option>', esc_attr($tz), selected($current_tz, $tz, false), esc_html($tz));
                }
                ?>
            </select>
        </td>
    </tr>
    
    <tr>
        <th scope="row"><?php _e('Date Format', 'prospectra-ticketing-system'); ?></th>
        <td>
            <input type="text" name="pts_settings[general][date_format]" value="<?php echo esc_attr(isset($general['date_format']) ? $general['date_format'] : 'd/m/Y'); ?>" class="regular-text">
            <p class="description"><?php _e('PHP date format (e.g., d/m/Y for 31/12/2025)', 'prospectra-ticketing-system'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row"><?php _e('Time Format', 'prospectra-ticketing-system'); ?></th>
        <td>
            <input type="text" name="pts_settings[general][time_format]" value="<?php echo esc_attr(isset($general['time_format']) ? $general['time_format'] : 'H:i'); ?>" class="regular-text">
            <p class="description"><?php _e('PHP time format (e.g., H:i for 14:30)', 'prospectra-ticketing-system'); ?></p>
        </td>
    </tr>
</table>
