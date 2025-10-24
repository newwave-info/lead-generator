<?php

/**
 * Registers navigation menu locations
 */
register_nav_menus( array(
    'primary'   => 'Menu principale',
//  'secondary' => 'Menu secondario',
//  'footer'    => 'Menu footer',
) );


/**
 * Allow editor to edit privacy page
 */
// function custom_manage_privacy_options($caps, $cap, $user_id, $args) {
//     if ( 'manage_privacy_options' === $cap ) {
//         $manage_name = is_multisite() ? 'manage_network' : 'manage_options';
//         $caps = array_diff( $caps, [ $manage_name ] );
//     }
//     return $caps;
// }
// add_action( 'map_meta_cap', 'custom_manage_privacy_options', 1, 4 );


/**
 * Remove tags and categories
 */
// function myprefix_unregister_tags() {
//     unregister_taxonomy_for_object_type( 'post_tag', 'post' );
//     unregister_taxonomy_for_object_type( 'category', 'post' );
// }
// add_action( 'init', 'myprefix_unregister_tags' );


/**
 * Remove deafult post type
 */
//function remove_default_post_type() {
//    remove_menu_page( 'edit.php' );
//}
//add_action( 'admin_menu', 'remove_default_post_type' );

//function remove_draft_widget(){
//    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
//}
//add_action( 'wp_dashboard_setup', 'remove_draft_widget', 999 );


/**
 * Remove media buttons
 */
// add_filter( 'wp_editor_settings', function( $settings ) {
//     $current_screen = get_current_screen();

//     // Post types for which the media buttons should be removed.
//     $post_types = array( 'page', 'cpt' );

//     // Bail out if media buttons should not be removed for the current post type.
//     if ( ! $current_screen || ! in_array( $current_screen->post_type, $post_types, true ) ) {
//         return $settings;
//     }

//     $settings['media_buttons'] = false;

//     return $settings;
// } );


/**
 * Remove date dropdown filter
 */
// function remove_date_drop() {
//     $screen = get_current_screen();
//     if ( 'page' == $screen->post_type ) {
//         add_filter('months_dropdown_results', '__return_empty_array');
//     }
// }
// add_action( 'admin_head', 'remove_date_drop' );


/**
 * Hiding ACFML "Field’s value in original language"
*/
//add_filter( 'wpml_custom_field_original_data', '__return_empty_array' );


/**
 * ACF options
 */
add_action('acf/init', 'my_acf_op_init');
function my_acf_op_init() {
	
	if( function_exists('acf_add_options_page') ) {

		$parent = acf_add_options_page(array(
			'page_title' 		=> __('Options', 'nw-acf'),
			'menu_title'		=> __('Options', 'nw-acf'),
			'menu_slug' 		=> 'theme-options',
			'capability'		=> 'edit_posts',
			'position' 			=> '59.9',
			'icon_url' 			=> 'dashicons-tagcloud',
			'redirect'			=> false,
			'update_button' 	=> __('Update', 'nw-acf'),
			'updated_message' 	=> __('Options Updated', 'nw-acf'),

		));

	//	$header = acf_add_options_page(array(
	//		'page_title' 	=> __('Header', 'nw-acf'),
	//		'menu_title'	=> __('Header', 'nw-acf'),
	//		'parent_slug'	=> $parent['menu_slug'],
	//	));
	//
	//	$footer = acf_add_options_page(array(
	//		'page_title' 	=> __('Footer', 'nw-acf'),
	//		'menu_title'	=> __('Footer', 'nw-acf'),
	//		'parent_slug'	=> $parent['menu_slug'],
	//	));

	}
}


/**
 * Gravity Forms text format
 */
function change_notification_format( $notification, $form, $entry ) {
     GFCommon::log_debug( 'gform_notification: change_notification_format() running.' );
    $notification['message_format'] = 'text';
  
    return $notification;
}
add_filter( 'gform_notification', 'change_notification_format', 10, 3 );



/**
 * Remove Gravity Forms check image from notification
 */
add_filter( 'gform_consent_checked_indicator_markup', 'change_markup', 10, 1 );
function change_markup( $checked_indicator_markup ){
    return '✓';
}


/**
 * Auto generate cpt title
 */
// function auto_generate_post_title( $title ) {
//     global $post;
//     if ( isset( $post->ID ) ) {
//         if ( empty( $_POST['post_title'] ) && 'CPT_NAME' == get_post_type( $post->ID ) ) {
//             $id = get_the_ID();
//             $title = 'Post-'.$id;
//         }
//     }
//     return $title; 
// }
// add_filter( 'title_save_pre', 'auto_generate_post_title' );


/**
 * Remove updates for non-admins
 */
add_action( 'admin_head', function() {
    if ( !current_user_can( 'manage_options' ) ) {
        remove_action( 'admin_notices', 'update_nag',      3  );
        remove_action( 'admin_notices', 'maintenance_nag', 10 );
    }
});


/**
 * Hide notifications not admin
 */
function hide_update_notice_to_all_but_admin_users() {
    if ( !current_user_can( 'update_core' ) ) {
        remove_action( 'admin_notices', 'update_nag', 3 );
    }
}
add_action( 'admin_head', 'hide_update_notice_to_all_but_admin_users', 1 );


/**
 * Remove extra from admin bar
 */
function annointed_admin_bar_remove() {

    global $wp_admin_bar;

    /* Remove their stuff */
    $wp_admin_bar->remove_menu('wp-logo');
    $wp_admin_bar->remove_menu('comments');
    $wp_admin_bar->remove_menu('new-content');
    $wp_admin_bar->remove_menu('new-post');
    $wp_admin_bar->remove_menu('updates');
    $wp_admin_bar->remove_menu('wpseo-menu');
    $wp_admin_bar->remove_menu('ate-status-bar');
}
add_action( 'wp_before_admin_bar_render', 'annointed_admin_bar_remove', 0 );


/**
 * Custom login style
 */
function login_css() {
    wp_enqueue_style( 'login_css', get_template_directory_uri() . '/common/css/login.css' );
}
add_action( 'login_head', 'login_css' );


/**
 * Custom admin footer
 */
function change_admin_footer() {
    echo '<span id="footer-note"><i>Developed by <a href="https://www.perspect.it/" target="_blank">Perspect srl</a>.</i></span>';
}
add_filter( 'admin_footer_text', 'change_admin_footer' );


/**
 * Filter: Prevent Rank Math from changing admin_footer_text.
 */
add_action( 'rank_math/whitelabel', '__return_true' );


/**
 * Filter to hide SEO Score for if user not administrator
 */
add_filter( 'rank_math/show_score', '__return_false' );


/**
 * Disable auto-update email notifications for plugins
 */
add_filter( 'auto_plugin_update_send_email', '__return_false' );