<?php

function create_type_taxonomies() {
    $labels = array(
        'name'                       => _x( 'Types', 'Taxonomy General Name', 'nw-tax' ),
        'singular_name'              => _x( 'Type', 'Taxonomy Singular Name', 'nw-tax' ),
        'menu_name'                  => __( 'Type', 'nw-tax' ),
        'all_items'                  => __( 'All Types', 'nw-tax' ),
        'parent_item'                => __( 'Parent Type', 'nw-tax' ),
        'parent_item_colon'          => __( 'Parent Type:', 'nw-tax' ),
        'new_item_name'              => __( 'New Type Name', 'nw-tax' ),
        'add_new_item'               => __( 'Add New Type', 'nw-tax' ),
        'edit_item'                  => __( 'Edit Type', 'nw-tax' ),
        'update_item'                => __( 'Update Type', 'nw-tax' ),
        'view_item'                  => __( 'View Type', 'nw-tax' ),
        'popular_items'              => __( 'Popular Types', 'nw-tax' ),
        'search_items'               => __( 'Search Types', 'nw-tax' ),
        'not_found'                  => __( 'Not Found', 'nw-tax' ),
        'no_terms'                   => __( 'No types', 'nw-tax' ),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => false,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_quick_edit'         => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'meta_box_cb'                => false, // post_categories_meta_box
        'rewrite'                    => array( 'slug' => 'cpt-types', 'with_front' => false ),
    );
    register_taxonomy( 'type', array( 'cpt' ), $args );

}
//add_action( 'init', 'create_type_taxonomies', 0 );