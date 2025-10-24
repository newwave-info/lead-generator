<?php

function codex_product_init() {
    $labels = array(
        'name'               => _x( 'Products', 'Post type general name', 'nw-cpt' ),
        'singular_name'      => _x( 'Product', 'Post type singular name', 'nw-cpt' ),
        'menu_name'          => _x( 'Products', 'Admin menu', 'nw-cpt' ),
        'name_admin_bar'     => _x( 'Product', 'Add new on admin bar', 'nw-cpt' ),
        'add_new'            => _x( 'Add New', 'nw-cpt' ),
        'add_new_item'       => __( 'Add New Product', 'nw-cpt' ),
        'new_item'           => __( 'New Product', 'nw-cpt' ),
        'edit_item'          => __( 'Edit Product', 'nw-cpt' ),
        'view_item'          => __( 'View Product', 'nw-cpt' ),
        'all_items'          => __( 'All Products', 'nw-cpt' ),
        'search_items'       => __( 'Search Products', 'nw-cpt' ),
        'parent_item_colon'  => __( 'Parent Products:', 'nw-cpt' ),
        'not_found'          => __( 'No Products found.', 'nw-cpt' ),
        'not_found_in_trash' => __( 'No Products found in Trash.', 'nw-cpt' )
    );
    $args = array(
        'labels'                => $labels,
        'description'           => __( 'Products', 'nw-cpt' ),
        'public'                => true,
        'publicly_queryable'    => true,
        'exclude_from_search'   => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => null,
        'menu_icon'             => 'dashicons-admin-post',
        'query_var'             => true,
        'has_archive'           => false,
        'rewrite'               => array( 'slug' => 'products', 'with_front' => false ),
        'capability_type'       => 'post',
        'hierarchical'          => false,
        //'taxonomies'            => array( 'product_cat' ),
        'supports'              => array( 'title', 'thumbnail' )
    );
    register_post_type( 'product', $args );
}
//add_action( 'init', 'codex_product_init' );




// function codex_exhibitor_init() {
//     $labels = array(
//         'name'               => _x( 'Exhibitors', 'Post type general name', 'nw-cpt' ),
//         'singular_name'      => _x( 'Exhibitor', 'Post type singular name', 'nw-cpt' ),
//         'menu_name'          => _x( 'Exhibitors', 'Admin menu', 'nw-cpt' ),
//         'name_admin_bar'     => _x( 'Exhibitor', 'Add new on admin bar', 'nw-cpt' ),
//         'add_new'            => _x( 'Add New', 'nw-cpt' ),
//         'add_new_item'       => __( 'Add New Exhibitor', 'nw-cpt' ),
//         'new_item'           => __( 'New Exhibitor', 'nw-cpt' ),
//         'edit_item'          => __( 'Edit Exhibitor', 'nw-cpt' ),
//         'view_item'          => __( 'View Exhibitor', 'nw-cpt' ),
//         'all_items'          => __( 'All Exhibitors', 'nw-cpt' ),
//         'search_items'       => __( 'Search Exhibitors', 'nw-cpt' ),
//         'parent_item_colon'  => __( 'Parent Exhibitors:', 'nw-cpt' ),
//         'not_found'          => __( 'No Exhibitors found.', 'nw-cpt' ),
//         'not_found_in_trash' => __( 'No Exhibitors found in Trash.', 'nw-cpt' )
//     );
//     $args = array(
//         'labels'                => $labels,
//         'description'           => __( 'Exhibitors', 'nw-cpt' ),
//         'public'                => true,
//         'publicly_queryable'    => true,
//         'exclude_from_search'   => false,
//         'show_ui'               => true,
//         'show_in_menu'          => true,
//         'menu_position'         => null,
//         'menu_icon'             => 'dashicons-store',
//         'query_var'             => true,
//         'has_archive'           => true,
//         'rewrite'               => array( 'slug' => 'exhibitors', 'with_front' => false ),
//         'capability_type'       => 'post',
//         'hierarchical'          => false,
//         'taxonomies'            => array( 'exhibitor_cat', 'exhibitor_venue' ),
//         'supports'              => array( 'title', 'thumbnail', 'revisions' )
//     );
//     register_post_type( 'exhibitor', $args );



//     // disabling the single but removing any links to it from the admin screens
//     function remove_single_exhibitor( $is_viewable, $post_type ) {
//         if ( 'exhibitor' === $post_type->name ) {
//             return false;
//         }
//         return $is_viewable;
//     }
//     add_filter( 'is_post_type_viewable', 'remove_single_exhibitor', 10, 2 );

//     // This line will remove cpt rewrite rules for single view
//     remove_rewrite_tag( '%exhibitor%' ); 

// }
// add_action( 'init', 'codex_exhibitor_init' );