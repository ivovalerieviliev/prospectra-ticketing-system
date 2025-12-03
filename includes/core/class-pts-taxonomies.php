<?php
/**
 * Register Custom Taxonomies
 * Category, Priority, Shift Type, Organization
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Taxonomies {
    
    /**
     * Register all taxonomies
     */
    public static function register() {
        self::register_category();
        self::register_priority();
        self::register_shift_type();
        self::register_organization();
    }
    
    /**
     * Register Category taxonomy
     */
    private static function register_category() {
        $labels = array(
            'name' => __('Categories', 'prospectra-ticketing-system'),
            'singular_name' => __('Category', 'prospectra-ticketing-system'),
            'add_new_item' => __('Add New Category', 'prospectra-ticketing-system'),
            'edit_item' => __('Edit Category', 'prospectra-ticketing-system'),
            'update_item' => __('Update Category', 'prospectra-ticketing-system'),
            'view_item' => __('View Category', 'prospectra-ticketing-system'),
            'search_items' => __('Search Categories', 'prospectra-ticketing-system'),
        );
        
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'ticket-category'),
        );
        
        register_taxonomy('pts_category', array('pts_ticket'), $args);
    }
    
    /**
     * Register Priority taxonomy
     */
    private static function register_priority() {
        $labels = array(
            'name' => __('Priorities', 'prospectra-ticketing-system'),
            'singular_name' => __('Priority', 'prospectra-ticketing-system'),
        );
        
        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'priority'),
        );
        
        register_taxonomy('pts_priority', array('pts_ticket'), $args);
    }
    
    /**
     * Register Shift Type taxonomy
     */
    private static function register_shift_type() {
        $labels = array(
            'name' => __('Shift Types', 'prospectra-ticketing-system'),
            'singular_name' => __('Shift Type', 'prospectra-ticketing-system'),
        );
        
        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'shift-type'),
        );
        
        register_taxonomy('pts_shift_type', array('pts_shift_report'), $args);
    }
    
    /**
     * Register Organization taxonomy
     */
    private static function register_organization() {
        $labels = array(
            'name' => __('Organizations', 'prospectra-ticketing-system'),
            'singular_name' => __('Organization', 'prospectra-ticketing-system'),
        );
        
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'organization'),
        );
        
        register_taxonomy('pts_organization', array('pts_ticket', 'pts_shift_report'), $args);
    }
}
