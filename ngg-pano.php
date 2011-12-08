<?php
/*
Plugin Name: NextGEN Gallery Panoramics
Plugin URI: todo
Description: This plugin adds the ability to create panoramics viewer using krpano (www.krpano.com) from NextGen Images
Version: 1.0
Author: Geoffroy Deleury
Author URI: http://geoffroydeleuryphotography.com
License: GPL2
*/
/*
Copyright 2011  Geoffroy Deleury  (email : geoffroy@deleury.fr)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
//
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);
if (!class_exists('nggPanoLoader')) {
class nggPanoLoader {
	
	var $version     = '1.0.0';
	var $dbversion   = '1.0.0';
	var $minium_WP   = '3.1';
	var $options     = '';
	var $manage_page;
	var $add_PHP5_notice = false;
	
	function nggPanoLoader() {

            // Stop the plugin if we missed the requirements
            if ( ( !$this->required_version() ) || ( !$this->check_memory_limit() ) )
                    return;

            // Get some constants first
            $this->load_options();
            $this->define_constant();
            $this->define_tables();
            $this->load_dependencies();

            $this->plugin_name = basename(dirname(__FILE__)).'/'.basename(__FILE__);

            // Init options & tables during activation & deregister init option
            register_activation_hook( $this->plugin_name, array(&$this, 'activate') );
            register_deactivation_hook( $this->plugin_name, array(&$this, 'deactivate') );	

            // Register a uninstall hook to remove all tables & option automatic
            register_uninstall_hook( $this->plugin_name, array('nggPanoLoader', 'uninstall') );

            // Start this plugin once all other plugins are fully loaded
            add_action( 'plugins_loaded', array(&$this, 'start_plugin') );

            // Add a message for PHP4 Users, can disable the update message later on
            if (version_compare(PHP_VERSION, '5.0.0', '<'))
                add_filter('transient_update_plugins', array(&$this, 'disable_upgrade'));
		
		
	}
	
	function start_plugin() {

		//global $nggRewrite;

		// Load the language file
		$this->load_textdomain();
		
                // Check for upgrade
               // $this->check_for_upgrade();
				
		// Content Filters
		//add_filter('ngg_gallery_name', 'sanitize_title');

		// Check if we are in the admin area
		if ( is_admin() ) {	
		add_action('template_redirect', array(&$this, 'load_scripts') );
				
                    
			// Pass the init check or show a message
			if (get_option( 'nggpano_init_check' ) != false )
				add_action( 'admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>' . get_option( "nggpano_init_check" ) . '</strong></p></div>\';') );
				
		} else {			
			
			// Add MRSS to wp_head
//			if ( $this->options['useMediaRSS'] )
//				add_action('wp_head', array('nggMediaRss', 'add_mrss_alternate_link'));
//			
//			// If activated, add PicLens/Cooliris javascript to footer
//			if ( $this->options['usePicLens'] )
//				add_action('wp_head', array('nggMediaRss', 'add_piclens_javascript'));
//                
            // Look for XML request, before page is render
            //add_action('parse_request',  array(&$this, 'check_request') );    
				
			// Add the script and style files
			add_action('template_redirect', array(&$this, 'load_scripts') );
			add_action('template_redirect', array(&$this, 'load_styles') );
			
		}	
	}

    function check_request( $wp ) {
    	
    	if ( !array_key_exists('callback', $wp->query_vars) )
    		return;
        
        if ( $wp->query_vars['callback'] == 'imagerotator') {
            require_once (dirname (__FILE__) . '/xml/imagerotator.php');
            exit();
        }

        if ( $wp->query_vars['callback'] == 'json') {
            require_once (dirname (__FILE__) . '/xml/json.php');
            exit();
        }

        if ( $wp->query_vars['callback'] == 'image') {
            require_once (dirname (__FILE__) . '/nggshow.php');
            exit();
        }
        
		//TODO:see trac #12400 could be an option for WP3.0 
        if ( $wp->query_vars['callback'] == 'ngg-ajax') {
            require_once (dirname (__FILE__) . '/xml/ajax.php');
            exit();
        }
        
    }
	
	function required_version() {
		
		global $wp_version;
			
		// Check for WP version installation
		$wp_ok  =  version_compare($wp_version, $this->minium_WP, '>=');
		
		if ( ($wp_ok == FALSE) ) {
			add_action(
				'admin_notices', 
				create_function(
					'', 
					'global $nggpano; printf (\'<div id="message" class="error"><p><strong>\' . __(\'Sorry, NextGEN Gallery Panoramics works only under WordPress %s or higher\', "nggpano" ) . \'</strong></p></div>\', $nggpano->minium_WP );'
				)
			);
			return false;
		}
		
		return true;
		
	}
	
	function check_memory_limit() {
        
        // get the real memory limit before some increase it
		$this->memory_limit = ini_get('memory_limit');
        
        // PHP docs : Note that to have no memory limit, set this directive to -1.
        if ($this->memory_limit == -1 ) return true;
        
        // Yes, we reached Gigabyte limits, so check if it's a megabyte limit
        if (strtolower( substr($this->memory_limit, -1) ) == 'm') {
            
            $this->memory_limit = (int) substr( $this->memory_limit, 0, -1);
        
    		//This works only with enough memory, 16MB is silly, wordpress requires already 16MB :-)
    		if ( ($this->memory_limit != 0) && ($this->memory_limit < 16 ) ) {
    			add_action(
    				'admin_notices', 
    				create_function(
    					'', 
    					'echo \'<div id="message" class="error"><p><strong>' . __('Sorry, NextGEN Gallery Panoramic works only with a Memory Limit of 16 MB or higher', 'nggpano') . '</strong></p></div>\';'
    				)
    			);
    			return false;
    		}
        }
		
		return true;
		
	}

	function check_for_upgrade() {

		// Inform about a database upgrade
		if( get_option( 'nggpano_db_version' ) != NGGPANO_DBVERSION ) {
            if ( isset ($_GET['page']) && $_GET['page'] == NGGPANOFOLDER ) return;
			add_action(
				'admin_notices', 
				create_function(
					'', 
					'echo \'<div id="message" class="error"><p><strong>' . __('Please update the database of NextGEN Gallery Panoramics.', 'nggpano') . ' <a href="admin.php?page=nextgen-gallery">' . __('Click here to proceed.', 'nggpano') . '</a>' . '</strong></p></div>\';'
				)
			);
		}
        
		return;		
	}
	
	function define_tables() {		
		global $wpdb;
		
		// add database pointer 
		$wpdb->nggpano_gallery					= $wpdb->prefix . 'nggpano_gallery';
		$wpdb->nggpano_panoramic				= $wpdb->prefix . 'nggpano_panoramic';

	}
	
	
	function define_constant() {
	   
		global $wp_version;
        
		//TODO:SHOULD BE REMOVED LATER
		define('NGGPANOVERSION', $this->version);
		// Minimum required database version
		define('NGGPANO_DBVERSION', $this->dbversion);

		// required for Windows & XAMPP
		define('NGGPANOWINABSPATH', str_replace("\\", "/", ABSPATH) );
			
		// define URL
		define('NGGPANOFOLDER', basename( dirname(__FILE__) ) );
		
		define('NGGPANOGALLERY_ABSPATH', trailingslashit( str_replace("\\","/", WP_PLUGIN_DIR . '/' . NGGPANOFOLDER ) ) );
		define('NGGPANOGALLERY_URLPATH', trailingslashit( plugins_url( NGGPANOFOLDER ) ) );
                
                
                define('NGGPANO_PLUGIN_DIR', 'wp-content/plugins');
		
		// get value for safe mode
		if ( (gettype( ini_get('safe_mode') ) == 'string') ) {
			// if sever did in in a other way
			if ( ini_get('safe_mode') == 'off' ) define('NGGPANO_SAFE_MODE', FALSE);
			else define( 'NGGPANO_SAFE_MODE', ini_get('safe_mode') );
		} else
		define( 'NGGPANO_SAFE_MODE', ini_get('safe_mode') );
        
        if ( version_compare($wp_version, '3.2.999', '>') )
            define('IS_WP_3_3', TRUE);
		
	}
	
	function load_dependencies() {
	
            // Load global libraries												// average memory usage (in bytes)
            require_once (dirname (__FILE__) . '/lib/core.php');					//  94.840
            require_once (dirname (__FILE__) . '/lib/functions.php');
            require_once (dirname (__FILE__) . '/lib/nggpanoEXIF.class.php');
            require_once (dirname (__FILE__) . '/lib/nggpanoPano.class.php');
//            require_once (dirname (__FILE__) . '/lib/ngg-db.php');					// 132.400
//            require_once (dirname (__FILE__) . '/lib/image.php');					//  59.424
//            require_once (dirname (__FILE__) . '/lib/tags.php');				    // 117.136
//            require_once (dirname (__FILE__) . '/lib/post-thumbnail.php');			//  n.a.
//            require_once (dirname (__FILE__) . '/widgets/widgets.php');				// 298.792
//            require_once (dirname (__FILE__) . '/lib/multisite.php');
//            require_once (dirname (__FILE__) . '/lib/sitemap.php');

            // Load frontend libraries							
            //require_once (dirname (__FILE__) . '/lib/navigation.php');		        // 242.016
            require_once (dirname (__FILE__) . '/nggpanofunctions.php');		        // n.a.
            require_once (dirname (__FILE__) . '/lib/shortcodes.php'); 		        // 92.664

            //Just needed if you access remote to WordPress
            //if ( defined('XMLRPC_REQUEST') )
            //    require_once (dirname (__FILE__) . '/lib/xmlrpc.php');

            // We didn't need all stuff during a AJAX operation
            if ( defined('DOING_AJAX') ) {
                //require_once (dirname (__FILE__) . '/admin/ajax.php');
            } else {
                //require_once (dirname (__FILE__) . '/lib/meta.php');				// 131.856
                //require_once (dirname (__FILE__) . '/lib/media-rss.php');			//  82.768
                //require_once (dirname (__FILE__) . '/lib/rewrite.php');				//  71.936
                //include_once (dirname (__FILE__) . '/admin/tinymce/tinymce.php'); 	//  22.408

                // Load backend libraries
                if ( is_admin() ) {	
                    require_once (dirname (__FILE__) . '/admin/admin.php');
                    //require_once (dirname (__FILE__) . '/admin/ngg-extend.php');
                    require_once (dirname (__FILE__) . '/admin/ngg-extend.php');
                    //require_once (dirname (__FILE__) . '/admin/media-upload.php');
                    //if ( defined('IS_WP_3_3') )
                        //require_once (dirname (__FILE__) . '/admin/pointer.php');
                    $this->nggpanoAdminPanel = new nggpanoAdminPanel();
                }	
            }
	}
	
	function load_textdomain() {
		
		load_plugin_textdomain('nggpano', false, NGGPANOFOLDER . '/lang');

	}
	
	function load_scripts() {
            //global $ngg;
		// activate swfkrpano.js
                wp_register_script( 'swfkrpano', NGGPANOGALLERY_URLPATH . 'krpano/swfkrpano.js',array(), '1' );
                wp_enqueue_script( 'swfkrpano' );
            	//	activate Thickbox
			wp_enqueue_script( 'thickbox' );
			// Load the thickbox images after all other scripts
			add_action( 'wp_footer', array(&$this, 'load_thickbox_images'), 11 );

//
//		// activate modified Shutter reloaded if not use the Shutter plugin
//		if ( ($ngg->options['thumbEffect'] == "shutter") && !function_exists('srel_makeshutter') ) {
//			wp_register_script('shutter', NGGPANOGALLERY_URLPATH .'shutter/shutter-reloaded.js', false ,'1.3.3');
//			wp_localize_script('shutter', 'shutterSettings', array(
//						'msgLoading' => __('L O A D I N G', 'nggallery'),
//						'msgClose' => __('Click to Close', 'nggallery'),
//						'imageCount' => '1'				
//			) );
//			wp_enqueue_script( 'shutter' );
//	    }
//		
//		// required for the slideshow
//		if ( NGGALLERY_IREXIST == true && $this->options['enableIR'] == '1' && nggGallery::detect_mobile_phone() === false ) 
//			wp_enqueue_script('swfobject', NGGPANOGALLERY_URLPATH .'admin/js/swfobject.js', FALSE, '2.2');
//        else {
//            wp_register_script('jquery-cycle', NGGPANOGALLERY_URLPATH .'js/jquery.cycle.all.min.js', array('jquery'), '2.9995');
//            wp_enqueue_script('ngg-slideshow', NGGPANOGALLERY_URLPATH .'js/ngg.slideshow.min.js', array('jquery-cycle'), '1.05'); 
//                    
//        }   
//            
//		// Load AJAX navigation script, works only with shutter script as we need to add the listener
//		if ( $this->options['galAjaxNav'] ) { 
//			if ( ($this->options['thumbEffect'] == "shutter") || function_exists('srel_makeshutter') ) {
//				wp_enqueue_script ( 'ngg_script', NGGPANOGALLERY_URLPATH . 'js/ngg.js', array('jquery'), '2.1');
//				wp_localize_script( 'ngg_script', 'ngg_ajax', array('path'		=> NGGPANOGALLERY_URLPATH,
//                                                                    'callback'  => home_url() . '/' . 'index.php?callback=ngg-ajax',
//																	'loading'	=> __('loading', 'nggallery'),
//				) );
//			}
//		}
		
	}
	
	function load_thickbox_images() {
		// WP core reference relative to the images. Bad idea
		echo "\n" . '<script type="text/javascript">tb_pathToImage = "' . site_url() . '/wp-includes/js/thickbox/loadingAnimation.gif";tb_closeImage = "' . site_url() . '/wp-includes/js/thickbox/tb-close.png";</script>'. "\n";			
	}
	
	function load_styles() {
		
		// check first the theme folder for a nggallery.css
		if ( nggPanoramic::get_theme_css_file() )
			wp_enqueue_style('NextGENPanoramics', nggPanoramic::get_theme_css_file() , false, '1.0.0', 'screen'); 
		else if ($this->options['activateCSS'])
			wp_enqueue_style('NextGENPanoramics', NGGPANOGALLERY_URLPATH . 'css/' . $this->options['CSSfile'], false, '1.0.0', 'screen'); 
		
		//	activate Thickbox
		wp_enqueue_style( 'thickbox');

		// activate modified Shutter reloaded if not use the Shutter plugin
//		if ( ($this->options['thumbEffect'] == 'shutter') && !function_exists('srel_makeshutter') )
//			wp_enqueue_style('shutter', NGGPANOGALLERY_URLPATH .'shutter/shutter-reloaded.css', false, '1.3.3', 'screen');
//		
	}
	
	function load_options() {
		// Load the options
		$this->options = get_option('nggpano_options');
	}
	
		
	function activate() {
            global $wpdb;
            //Starting from version 1.8.0 it's works only with PHP5.2
            if (version_compare(PHP_VERSION, '5.2.0', '<')) { 
                    deactivate_plugins($this->plugin_name); // Deactivate ourself
                    wp_die("Sorry, but you can't run this plugin, it requires PHP 5.2 or higher."); 
                                    return; 
            } 

            include_once (dirname (__FILE__) . '/admin/install.php');
		
		// check for tables
		nggpano_install();
		// remove the update message
		delete_option( 'nggpano_update_exists' );
		
	}
	
	function deactivate() {
		
		// remove & reset the init check option
		delete_option( 'nggpano_init_check' );
		delete_option( 'nggpano_update_exists' );
	}

	function uninstall() {
            include_once (dirname (__FILE__) . '/admin/install.php');
            nggpano_uninstall();
	}
	
    
	function disable_upgrade($option){
	 	
		// PHP5.2 is required for NGG V1.4.0 
		if ( version_compare($option->response[ $this->plugin_name ]->new_version, '1.4.0', '>=') )
			return $option;

	    if( isset($option->response[ $this->plugin_name ]) ){
	        //Clear it''s download link
	        $option->response[ $this->plugin_name ]->package = '';
	        
	        //Add a notice message
	        if ($this->add_PHP5_notice == false){
   	    		add_action( "in_plugin_update_message-$this->plugin_name", create_function('', 'echo \'<br /><span style="color:red">Please update to PHP5.2 as soon as possible, the plugin is not tested under PHP4 anymore</span>\';') );
	    		$this->add_PHP5_notice = true;
			}
		}
	    return $option;
	}
	
    
//    // Check for the header / footer, parts taken from Matt Martz (http://sivel.net/)
//    function test_head_footer_init() {
//    
//    	// If test-head query var exists hook into wp_head
//    	if ( isset( $_GET['test-head'] ) )
//    		add_action( 'wp_head', create_function('', 'echo \'<!--wp_head-->\';'), 99999 );
//    
//    	// If test-footer query var exists hook into wp_footer
//    	if ( isset( $_GET['test-footer'] ) )
//    		add_action( 'wp_footer', create_function('', 'echo \'<!--wp_footer-->\';'), 99999 );
//    }
	
}
	// Let's start the holy plugin
	global $nggpano;
	$nggpano = new nggPanoLoader();
}
?>