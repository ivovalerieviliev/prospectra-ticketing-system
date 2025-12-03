<?php
/**
 * Register Custom Post Types
 * Handles registration of pts_ticket, pts_shift_report, and pts_order CPTs
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Post_Types {
    
    /**
     * Register all custom post types
     */
    public static function register() {
        self::register_ticket();
        self::register_shift_report();
        self::register_order();
    }
    
    /**
     * Register Ticket CPT
     */
    private static function register_ticket() {
        $labels = array(
            'name' => __('Tickets', 'prospectra-ticketing-system'),
            'singular_name' => __('Ticket', 'prospectra-ticketing-system'),
            'add_new' => __('Add New Ticket', 'prospectra-ticketing-system'),
            'add_new_item' => __('Add New Ticket', 'prospectra-ticketing-system'),
            'edit_item' => __('Edit Ticket', 'prospectra-ticketing-system'),
            'new_item' => __('New Ticket', 'prospectra-ticketing-system'),
            'view_item' => __('View Ticket', 'prospectra-ticketing-system'),
            'search_items' => __('Search Tickets', 'prospectra-ticketing-system'),
            'not_found' => __('No tickets found', 'prospectra-ticketing-system'),
            'not_found_in_trash' => __('No tickets found in trash', 'prospectra-ticketing-system'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'author', 'custom-fields'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'rewrite' => array('slug' => 'ticket'),
        );
        
        register_post_type('pts_ticket', $args);
    }
    
    /**
     * Register Shift Report CPT
     */
    private static function register_shift_report() {
        $labels = array(
            'name' => __('Shift Reports', 'prospectra-ticketing-system'),
            'singular_name' => __('Shift Report', 'prospectra-ticketing-system'),
            'add_new' => __('Add New Report', 'prospectra-ticketing-system'),
            'add_new_item' => __('Add New Shift Report', 'prospectra-ticketing-system'),
            'edit_item' => __('Edit Shift Report', 'prospectra-ticketing-system'),
            'new_item' => __('New Shift Report', 'prospectra-ticketing-system'),
            'view_item' => __('View Shift Report', 'prospectra-ticketing-system'),
            'search_items' => __('Search Shift Reports', 'prospectra-ticketing-system'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'show_in_menu' => false,
            'show_in_rest' => true,
            'supports' => array('title', 'author', 'custom-fields'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'rewrite' => array('slug' => 'shift-report'),
        );
        
        register_post_type('pts_shift_report', $args);
    }
    
    /**
     * Register Order CPT
     */
    private static function register_order() {
        $labels = array(
            'name' => __('Orders', 'prospectra-ticketing-system'),
            'singular_name' => __('Order', 'prospectra-ticketing-system'),
            'add_new' => __('Add New Order', 'prospectra-ticketing-system'),
            'edit_item' => __('Edit Order', 'prospectra-ticketing-system'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_in_menu' => false,
            'show_in_rest' => true,
            'supports' => array('title', 'custom-fields'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'rewrite' => array('slug' => 'order'),
        );
        
        register_post_type('pts_order', $args);
    }
}
