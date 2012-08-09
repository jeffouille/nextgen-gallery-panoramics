<?php
/**
 * nggpanoAdminPanel - Admin Section for NextGEN Gallery Panoramic
 * 
 * @package NextGEN Gallery Panoramics
 */
class nggpanoAdminPanel{
	
    // constructor
    function __construct() {

        // Add the admin menu
        add_action( 'admin_menu', array (&$this, 'add_menu') );

        
       // add_action( 'admin_bar_menu', array(&$this, 'admin_bar_menu'), 99 );

        // Add the script and style files
        add_action('admin_print_scripts', array(&$this, 'load_scripts') );
        add_action('admin_print_styles', array(&$this, 'load_styles') );

        //TODO: remove after release of Wordpress 3.3
//        add_filter('contextual_help', array(&$this, 'show_help'), 10, 2);
//        add_filter('current_screen', array(&$this, 'edit_current_screen'));

        // Add WPML hook to register description / alt text for translation
      //  add_action('ngg_image_updated', array('nggGallery', 'RegisterString') );

    }

    // integrate the menu	
    function add_menu()  {

        add_menu_page( 'Panoramics', 'Panoramics', 'NGG Panoramics overview', NGGPANOFOLDER, array (&$this, 'show_menu'), 'div' );
        add_submenu_page( NGGPANOFOLDER , __('Overview', 'nggpano'), __('Overview', 'nggpano'), 'NGG Panoramics overview', NGGPANOFOLDER, array (&$this, 'show_menu'));
//        add_submenu_page( NGGFOLDER , __('Add Gallery / Images', 'nggallery'), __('Add Gallery / Images', 'nggallery'), 'NextGEN Upload images', 'nggallery-add-gallery', array (&$this, 'show_menu'));
//        add_submenu_page( NGGFOLDER , __('Manage Gallery', 'nggallery'), __('Manage Gallery', 'nggallery'), 'NGG Panoramics Manage gallery', 'nggallery-manage-gallery', array (&$this, 'show_menu'));
//        add_submenu_page( NGGFOLDER , _n( 'Album', 'Albums', 1, 'nggallery' ), _n( 'Album', 'Albums', 1, 'nggallery' ), 'NextGEN Edit album', 'nggallery-manage-album', array (&$this, 'show_menu'));
//        add_submenu_page( NGGFOLDER , __('Tags', 'nggallery'), __('Tags', 'nggallery'), 'NextGEN Manage tags', 'nggallery-tags', array (&$this, 'show_menu'));
//        add_submenu_page( NGGFOLDER , __('Options', 'nggallery'), __('Options', 'nggallery'), 'NextGEN Change options', 'nggallery-options', array (&$this, 'show_menu'));
        //if ( wpmu_enable_function('wpmuStyle') )
                    add_submenu_page( NGGPANOFOLDER , __('Viewer Templates', 'nggpano'), __('Viewer Templates', 'nggpano'), 'NGG Panoramics Change style', 'nggpano-style', array (&$this, 'show_menu'));
        //if ( wpmu_enable_function('wpmuStyle') )
                    add_submenu_page( NGGPANOFOLDER , __('Building Templates', 'nggpano'), __('Building Templates', 'nggpano'), 'NGG Panoramics Change style', 'nggpano-tool-config', array (&$this, 'show_menu'));
//        if ( wpmu_enable_function('wpmuRoles') || wpmu_site_admin() )
//                    add_submenu_page( NGGFOLDER , __('Roles', 'nggallery'), __('Roles', 'nggallery'), 'activate_plugins', 'nggallery-roles', array (&$this, 'show_menu'));
//        add_submenu_page( NGGFOLDER , __('About this Gallery', 'nggallery'), __('About', 'nggallery'), 'NGG Panoramics overview', 'nggallery-about', array (&$this, 'show_menu'));
//
//        if ( !is_multisite() || wpmu_site_admin() ) 
//        add_submenu_page( NGGFOLDER , __('Reset / Uninstall', 'nggallery'), __('Reset / Uninstall', 'nggallery'), 'activate_plugins', 'nggallery-setup', array (&$this, 'show_menu'));
//
//            //register the column fields
//            $this->register_columns();	
    }

          
    
    // load the script for the defined page and load only this code	
    function show_menu() {
        global $nggpano;
        switch ($_GET['page']){
                case NGGPANOFOLDER :    // default page
                    include_once ( dirname (__FILE__) . '/nggpanoAdmin.class.php' );
                    include_once ( dirname(__FILE__) . '/overview.php');
                    nggpano_admin_overview();
                break;
                case "nggpano-style" :
                    include_once ( dirname (__FILE__) . '/style.php' );		// nggpano_admin_style
                    nggpano_admin_style();
                break;
                case "nggpano-tool-config" :
                    include_once ( dirname (__FILE__) . '/tool-config-file.php' );		// nggpano_admin_tool_config_file
                    nggpano_admin_tool_config_file();
                break;
                case "nggallery-manage-gallery" :
                default :
                        include_once ( dirname (__FILE__) . '/nggpanoAdmin.class.php' );	// admin functions
                break;
        }
    }


	
	function load_scripts() {
		global $wp_version;
        
		// no need to go on if it's not a plugin page
		if( !isset($_GET['page']) )
			return;

		wp_register_script('nggpano-admin', NGGPANOGALLERY_URLPATH . 'admin/js/nggpano.admin.js', array('jquery'), '1.4.1');
		// activate gmap3.min.js
                wp_register_script( 'googlemap', 'http://maps.google.com/maps/api/js?sensor=false',array(), '1' ); 
                wp_register_script( 'gmap3', NGGPANOGALLERY_URLPATH . 'js/gmap3.min.js',array('jquery','googlemap'), '4.1' );
                
                wp_register_script( 'jquery-autocomplete', NGGPANOGALLERY_URLPATH . 'admin/js/jquery-autocomplete.min.js',array('jquery'), '4.1' );
                
                        
                // activate swfkrpano.js
                wp_register_script( 'swfkrpano', NGGPANOGALLERY_URLPATH . 'krpano/swfkrpano.js',array() , '1' );
                wp_enqueue_script( 'swfkrpano' );
                
                
		/*wp_localize_script('ngg-ajax', 'nggAjaxSetup', array(
					'url' => admin_url('admin-ajax.php'),
					'action' => 'ngg_ajax_operation',
					'operation' => '',
					'nonce' => wp_create_nonce( 'ngg-ajax' ),
					'ids' => '',
					'permission' => __('You do not have the correct permission', 'nggallery'),
					'error' => __('Unexpected Error', 'nggallery'),
					'failure' => __('A failure occurred', 'nggallery')				
		) );*/
//        wp_register_script( 'ngg-plupload-handler', NGGPANOGALLERY_URLPATH .'admin/js/plupload.handler.js', array('plupload-all'), '0.0.1' );
//    	wp_localize_script( 'ngg-plupload-handler', 'pluploadL10n', array(
//    		'queue_limit_exceeded' => __('You have attempted to queue too many files.'),
//    		'file_exceeds_size_limit' => __('This file exceeds the maximum upload size for this site.'),
//    		'zero_byte_file' => __('This file is empty. Please try another.'),
//    		'invalid_filetype' => __('This file type is not allowed. Please try another.'),
//    		'not_an_image' => __('This file is not an image. Please try another.'),
//    		'image_memory_exceeded' => __('Memory exceeded. Please try another smaller file.'),
//    		'image_dimensions_exceeded' => __('This is larger than the maximum size. Please try another.'),
//    		'default_error' => __('An error occurred in the upload. Please try again later.'),
//    		'missing_upload_url' => __('There was a configuration error. Please contact the server administrator.'),
//    		'upload_limit_exceeded' => __('You may only upload 1 file.'),
//    		'http_error' => __('HTTP error.'),
//    		'upload_failed' => __('Upload failed.'),
//    		'io_error' => __('IO error.'),
//    		'security_error' => __('Security error.'),
//    		'file_cancelled' => __('File canceled.'),
//    		'upload_stopped' => __('Upload stopped.'),
//    		'dismiss' => __('Dismiss'),
//    		'crunching' => __('Crunching&hellip;'),
//    		'deleted' => __('moved to the trash.'),
//    		'error_uploading' => __('&#8220;%s&#8221; has failed to upload due to an error')
//    	) );        
//		wp_register_script('ngg-progressbar', NGGPANOGALLERY_URLPATH .'admin/js/ngg.progressbar.js', array('jquery'), '2.0.1');
//        wp_register_script('jquery-ui-autocomplete', NGGPANOGALLERY_URLPATH .'admin/js/jquery.ui.autocomplete.min.js', array('jquery-ui-core', 'jquery-ui-widget'), '1.8.15');
//		wp_register_script('swfupload_f10', NGGPANOGALLERY_URLPATH .'admin/js/swfupload.js', array('jquery'), '2.2.0');
//       		
		switch ($_GET['page']) {	
			case "nggallery-manage-gallery" :
				//wp_enqueue_script( 'postbox' );
                            wp_enqueue_script( 'nggpano-admin' );
                            wp_enqueue_script( 'googlemap' ); 
                            wp_enqueue_script( 'gmap3' );
                            wp_enqueue_script( 'jquery-autocomplete' );
                            
                            wp_enqueue_script('thickbox'); 
				//wp_enqueue_script( 'ngg-progressbar' );
				//wp_enqueue_script( 'jquery-ui-dialog' );
//    			wp_register_script('shutter', NGGPANOGALLERY_URLPATH .'shutter/shutter-reloaded.js', false ,'1.3.2');
//    			wp_localize_script('shutter', 'shutterSettings', array(
//    						'msgLoading' => __('L O A D I N G', 'nggallery'),
//    						'msgClose' => __('Click to Close', 'nggallery'),
//    						'imageCount' => '1'				
//    			) );
//    			wp_enqueue_script( 'shutter' ); 
			break;
			case "nggpano-style" :
                        case "nggpano-tool-config" :
				wp_enqueue_script( 'codepress' );
				wp_enqueue_script( 'ngg-colorpicker', NGGALLERY_URLPATH .'admin/js/colorpicker/js/colorpicker.js', array('jquery'), '1.0');
			break;

		}
	}		
	
	function load_styles() {
        // load the icon for the navigation menu
                wp_enqueue_style( 'nggpanomenu', NGGPANOGALLERY_URLPATH .'admin/css/menu.css', array() );
		wp_register_style( 'nggpanoadmin', NGGPANOGALLERY_URLPATH .'admin/css/nggpanoadmin.css', false, '1.0.0', 'screen' );
                wp_register_style( 'jquery-autocomplete', NGGPANOGALLERY_URLPATH .'admin/css/jquery-autocomplete.css', false, '1.0.0', 'screen' );
		//wp_register_style( 'ngg-jqueryui', NGGPANOGALLERY_URLPATH .'admin/css/jquery.ui.css', false, '1.8.5', 'screen' );
        
        // no need to go on if it's not a plugin page
		if( !isset($_GET['page']) )
			return;

		switch ($_GET['page']) {
//			case NGGFOLDER :
//				wp_enqueue_style( 'thickbox' );	
//			case "nggallery-about" :
//				wp_enqueue_style( 'nggadmin' );
                //TODO:Remove after WP 3.3 release
//                if ( !defined('NGGPANO_IS_WP_3_3') )
//                    wp_admin_css( 'css/dashboard' );
//			break;
//			case "nggallery-add-gallery" :
//				wp_enqueue_style( 'ngg-jqueryui' );
//				wp_enqueue_style( 'jqueryFileTree', NGGPANOGALLERY_URLPATH .'admin/js/jqueryFileTree/jqueryFileTree.css', false, '1.0.1', 'screen' );
//			case "nggallery-options" :
//				wp_enqueue_style( 'nggtabs', NGGPANOGALLERY_URLPATH .'admin/css/jquery.ui.tabs.css', false, '2.5.0', 'screen' );
//				wp_enqueue_style( 'nggadmin' );
//            break;    
			case "nggallery-manage-gallery" :
                            wp_enqueue_style('nggpanoadmin');
                            wp_enqueue_style('jquery-autocomplete');
                            wp_enqueue_style('thickbox');
//			case "nggallery-roles" :
//			case "nggallery-manage-album" :
//				wp_enqueue_style( 'ngg-jqueryui' );
//				wp_enqueue_style( 'nggadmin' );
			break;
//			case "nggallery-tags" :
//				wp_enqueue_style( 'nggtags', NGGPANOGALLERY_URLPATH .'admin/css/tags-admin.css', false, '2.6.1', 'screen' );
//				break;
			case "nggpano-style" :
                        case "nggpano-tool-config" :
				wp_admin_css( 'css/theme-editor' );
				wp_enqueue_style('nggcolorpicker', NGGALLERY_URLPATH.'admin/js/colorpicker/css/colorpicker.css', false, '1.0', 'screen');
				wp_enqueue_style('nggadmincp', NGGALLERY_URLPATH.'admin/css/nggColorPicker.css', false, '1.0', 'screen');
			break;
		}	
	}
    
    
    
}


?>