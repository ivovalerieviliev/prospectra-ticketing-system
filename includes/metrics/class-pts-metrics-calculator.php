<? php
/**
 * Metrics Calculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Metrics_Calculator {
    
    /**
     * Calculate all metrics
     */
    public static function calculate_all() {
        return array(
            'open_tickets' => self::get_open_tickets_count(),
            'closed_tickets' => self::get_closed_tickets_count(),
            'tickets_per_user' => self::get_tickets_per_user(),
            'avg_comments_per_ticket' => self::get_avg_comments_per_ticket(),
            'issues_reported' => self::get_issues_reported_today(),
            'production_volume' => self::get_production_volume(),
            'efficiency' => self::calculate_efficiency(),
        );
    }
    
    /**
     * Get open tickets count
     */
    private static function get_open_tickets_count() {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(p.ID) 
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'pts_ticket'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_pts_ticket_status'
            AND pm.meta_value != %s
        ", 'Closed'));
        
        return absint($count);
    }
    
    /**
     * Get closed tickets count
     */
    private static function get_closed_tickets_count() {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(p.ID) 
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'pts_ticket'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_pts_ticket_status'
            AND pm.meta_value = %s
        ", 'Closed'));
        
        return absint($count);
    }
    
    /**
     * Get tickets per user
     */
    private static function get_tickets_per_user() {
        global $wpdb;
        
        $results = $wpdb->get_results("
            SELECT pm.meta_value as user_id, COUNT(p.ID) as ticket_count
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm. post_id
            WHERE p. post_type = 'pts_ticket'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_pts_ticket_assignee'
            GROUP BY pm. meta_value
        ");
        
        $data = array();
        foreach ($results as $row) {
            $user = get_user_by('id', $row->user_id);
            if ($user) {
                $data[$user->display_name] = absint($row->ticket_count);
            }
        }
        
        return $data;
    }
    
    /**
     * Get average comments per ticket per user
     */
    private static function get_avg_comments_per_ticket() {
        global $wpdb;
        $comments_table = $wpdb->prefix . 'pts_comments';
        
        $results = $wpdb->get_results("
            SELECT c.user_id, COUNT(c.id) as comment_count, COUNT(DISTINCT c.ticket_id) as ticket_count
            FROM {$comments_table} c
            WHERE c.is_system_event = 0
            GROUP BY c.user_id
        ");
        
        $data = array();
        foreach ($results as $row) {
            $user = get_user_by('id', $row->user_id);
            if ($user && $row->ticket_count > 0) {
                $avg = round($row->comment_count / $row->ticket_count, 2);
                $data[$user->display_name] = $avg;
            }
        }
        
        return $data;
    }
    
    /**
     * Get issues reported today
     */
    private static function get_issues_reported_today() {
        $args = array(
            'post_type' => 'pts_ticket',
            'post_status' => 'publish',
            'date_query' => array(
                array(
                    'after' => '24 hours ago',
                ),
            ),
            'fields' => 'ids',
        );
        
        $tickets = get_posts($args);
        return count($tickets);
    }
    
    /**
     * Get production volume (mock data - would integrate with actual system)
     */
    private static function get_production_volume() {
        // This would pull from actual production system or orders
        // For now, calculate from completed orders today
        global $wpdb;
        
        $total = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(CAST(pm.meta_value AS UNSIGNED))
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm. post_id
            WHERE p. post_type = 'pts_order'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_pts_order_produced'
            AND p.post_date >= %s
        ", date('Y-m-d 00:00:00')));
        
        return $total ?  absint($total) : 0;
    }
    
    /**
     * Calculate efficiency
     */
    private static function calculate_efficiency() {
        global $wpdb;
        
        // Efficiency = (Produced / Planned) * 100
        $produced = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(CAST(pm.meta_value AS UNSIGNED))
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'pts_order'
            AND pm.meta_key = '_pts_order_produced'
            AND p.post_date >= %s
        ", date('Y-m-d 00:00:00')));
        
        $planned = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(CAST(pm.meta_value AS UNSIGNED))
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p. ID = pm.post_id
            WHERE p.post_type = 'pts_order'
            AND pm.meta_key = '_pts_order_planned'
            AND p.post_date >= %s
        ", date('Y-m-d 00:00:00')));
        
        if (! $planned || $planned == 0) {
            return 0;
        }
        
        return round(($produced / $planned) * 100, 2);
    }
    
    /**
     * Cache metrics
     */
    public static function cache_metrics($user_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'pts_metrics_cache';
        
        $metrics = self::calculate_all();
        
        foreach ($metrics as $metric_type => $value) {
            $wpdb->replace($table, array(
                'user_id' => $user_id,
                'metric_type' => $metric_type,
                'value' => is_array($value) ? json_encode($value) : $value,
                'calculated_at' => current_time('mysql'),
            ));
        }
    }
    
    /**
     * Get cached metrics
     */
    public static function get_cached_metrics($user_id = null) {
        global $wpdb;
        $table = $wpdb->prefix .  'pts_metrics_cache';
        
        $cache_duration = 5 * MINUTE_IN_SECONDS;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT metric_type, value, calculated_at
            FROM $table
            WHERE user_id = %d
            AND calculated_at >= %s
        ", $user_id, date('Y-m-d H:i:s', time() - $cache_duration)));
        
        if (empty($results)) {
            self::cache_metrics($user_id);
            return self::calculate_all();
        }
        
        $metrics = array();
        foreach ($results as $row) {
            $value = json_decode($row->value, true);
            $metrics[$row->metric_type] = $value !== null ? $value : $row->value;
        }
        
        return $metrics;
    }
}
