<?php
/**
 * Register Custom Post Types
 */

if (! defined('ABSPATH')) {
    exit;
}

class PTS_Post_Types {
    
    public static function register() {
        self::register_ticket();
        self::register_shift_report();
        self::register_order();
    }
    
    private static function register_ticket() {
        $labels = array(
            'name' => __('Tickets', 'prospectra-ticketing-system'),
            'singular_name' => __('Ticket', 'prospectra-ticketing-system'),
            'add_new' => __('Add New Ticket', 'prospectra-ticketing-system'),
            'add_new_item' => __('Add New Ticket', 'prospectra-ticketing-system'),
            'edit_item' => __('Edit Ticket', 'prospectra-ticketing-system'),
            'view_item' => __('View Ticket', 'prospectra-ticketing-system'),
            'search_items' => __('Search Tickets', 'prospectra-ticketing-system'),
        );
        
        register_post_type('pts_ticket', array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'author'),
            'capability_type' => 'post',
            'rewrite' => array('slug' => 'ticket'),
        ));
    }
    
    private static function register_shift_report() {
        $labels = array(
            'name' => __('Shift Reports', 'prospectra-ticketing-system'),
            'singular_name' => __('Shift Report', 'prospectra-ticketing-system'),
        );
        
        register_post_type('pts_shift_report', array(
            'labels' => $labels,
            'public' => true,
            'show_in_menu' => false,
            'show_in_rest' => true,
            'supports' => array('title', 'author'),
            'capability_type' => 'post',
            'rewrite' => array('slug' => 'shift-report'),
        ));
    }
    
    private static function register_order() {
        register_post_type('pts_order', array(
            'labels' => array('name' => __('Orders', 'prospectra-ticketing-system')),
            'public' => true,
            'show_in_menu' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
        ));
    }
}
