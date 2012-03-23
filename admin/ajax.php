<?php

add_action('wp_ajax_nggpano_tinymce', 'nggpano_ajax_tinymce');
/**
 * Call TinyMCE window content via admin-ajax
 * 
 * @since 1.7.0 
 * @return html content
 */
function nggpano_ajax_tinymce() {

    // check for rights
    if ( !current_user_can('edit_pages') && !current_user_can('edit_posts') ) 
    	die(__("You are not allowed to be here"));
        	
   	include_once( dirname( dirname(__FILE__) ) . '/admin/tinymce/window.php');
    
    die();	
}

?>