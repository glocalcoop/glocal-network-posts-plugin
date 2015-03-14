<?php

add_action('admin_head', 'glocal_add_network_sites_button');

function glocal_add_network_sites_button() {
    global $typenow;
    // check user permissions
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
    return;
    }
    // verify the post type
    if( ! in_array( $typenow, array( 'post', 'page' ) ) )
        return;
    // check if WYSIWYG is enabled
    if ( get_user_option('rich_editing') == 'true') {
        add_filter("mce_external_plugins", "glocal_network_sites_button_plugin");
        add_filter('mce_buttons', 'glocal_register_network_sites_button');
    }
}

function glocal_network_sites_button_plugin($plugin_array) {
    $plugin_array['glocal_network_sites_button'] = plugins_url( '/js/glocal-network-sites-plugin.js', __FILE__ ); 
    return $plugin_array;
}

function glocal_register_network_sites_button($buttons) {
   array_push($buttons, "glocal_network_sites_button");
   return $buttons;
}

function glocal_ns_tinymce_css() {
    wp_enqueue_style('glocal-tinymce', plugins_url('/stylesheets/css/editor-styles.css', __FILE__));
}
 
add_action('admin_enqueue_scripts', 'glocal_ns_tinymce_css');

?>