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

        add_menu_page( 'NGG Panoramics', 'NGG Panoramics', 'NGG Panoramics overview', NGGPANOFOLDER, array (&$this, 'show_menu') );
//        add_submenu_page( NGGFOLDER , __('Overview', 'nggallery'), __('Overview', 'nggallery'), 'NGG Panoramics overview', NGGFOLDER, array (&$this, 'show_menu'));
//        add_submenu_page( NGGFOLDER , __('Add Gallery / Images', 'nggallery'), __('Add Gallery / Images', 'nggallery'), 'NextGEN Upload images', 'nggallery-add-gallery', array (&$this, 'show_menu'));
//        add_submenu_page( NGGFOLDER , __('Manage Gallery', 'nggallery'), __('Manage Gallery', 'nggallery'), 'NGG Panoramics Manage gallery', 'nggallery-manage-gallery', array (&$this, 'show_menu'));
//        add_submenu_page( NGGFOLDER , _n( 'Album', 'Albums', 1, 'nggallery' ), _n( 'Album', 'Albums', 1, 'nggallery' ), 'NextGEN Edit album', 'nggallery-manage-album', array (&$this, 'show_menu'));
//        add_submenu_page( NGGFOLDER , __('Tags', 'nggallery'), __('Tags', 'nggallery'), 'NextGEN Manage tags', 'nggallery-tags', array (&$this, 'show_menu'));
//        add_submenu_page( NGGFOLDER , __('Options', 'nggallery'), __('Options', 'nggallery'), 'NextGEN Change options', 'nggallery-options', array (&$this, 'show_menu'));
//        if ( wpmu_enable_function('wpmuStyle') )
//                    add_submenu_page( NGGFOLDER , __('Style', 'nggallery'), __('Style', 'nggallery'), 'NextGEN Change style', 'nggallery-style', array (&$this, 'show_menu'));
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
                case "nggallery-manage-gallery" :
                default :
                        include_once ( dirname (__FILE__) . '/nggpanoAdmin.class.php' );	// admin functions
                        break;
        }
        
    	if ( isset($_POST['updateoption']) ) {	
    		check_admin_referer('nggpano_settings');
    		// get the hidden option fields, taken from WP core
    		if ( $_POST['page_options'] )	
    			$options = explode(',', stripslashes($_POST['page_options']));

    		if ($options) {
                    foreach ($options as $option) {
                            $option = trim($option);
                            $value = isset($_POST[$option]) ? trim($_POST[$option]) : false;
            //		$value = sanitize_option($option, $value); // This does stripslashes on those that need it
                            $nggpano->options[$option] = $value;
                    }
                
                    // do not allow a empty string
    //                if ( empty ( $nggpano->options['toolConfigFile'] ) ) {
    //                    $nggpano->options['toolConfigFile'] = 'krpanotool.config';
    //                }
                    // do not allow a empty string
                    if ( empty ( $nggpano->options['krpanoToolsTempFolder'] ) ) {
                        $nggpano->options['krpanoToolsTempFolder'] = '/usr/local/bin/';
                    }
                    // the path should always end with a slash	
                    $nggpano->options['krpanoToolsTempFolder']      = trailingslashit($nggpano->options['krpanoToolsTempFolder']);
                    $nggpano->options['kmakemultiresFolder']        = trailingslashit($nggpano->options['kmakemultiresFolder']);
                    $nggpano->options['kmakemultiresConfigFolder']  = trailingslashit($nggpano->options['kmakemultiresConfigFolder']);
                    $nggpano->options['krpanoFolder']               = trailingslashit($nggpano->options['krpanoFolder']);
                    $nggpano->options['skinFolder']                 = trailingslashit($nggpano->options['skinFolder']);
                    
                    //Preview Size
                    if ( empty ( $nggpano->options['widthPreview'] ) ||!is_numeric($nggpano->options['widthPreview']) ) {
                        $nggpano->options['widthPreview'] = '2000';
                    }
                    if ( empty ( $nggpano->options['heightPreview'] ) || !is_numeric($nggpano->options['heightPreview']) ) {
                        $nggpano->options['heightPreview'] = '1000';
                    }

                }
    		// Save options
    		update_option('nggpano_options', $nggpano->options);
                
                // Reset all galleries with the default template file
                if (isset($_POST['force_reset'])) {
                    nggpano_resetGalleriesTemplate($_POST['defaultSkinFile']);
                }
    
    		
    	 	nggPanoramic::show_message(__('Update Successfully','nggpano'));
    	}

                    $filepath = admin_url()."admin.php?page=".$_GET["page"];
                    ?>
<div class="wrap">
    <h2><?php _e('Welcome to NextGEN Gallery Panoramics', 'nggpano'); ?></h2>
    <p><?php _e('This plugin adds the ability to create panoramics viewer using krpano (www.krpano.com) from NextGen Images', 'nggpano'); ?>
<?php
//    
//            $nggpano_options['toolConfigFile']	= 'default_kmakemultires.config';  	// set default config file for krapnotool
	/*
            $nggpano_options['krpanoToolsTempFolder']		= get_temp_dir()."/temp/";			// default temp path to for krpanotool works
        $nggpano_options['kmakemultiresFolder']         = "wp-content/plugins/".NGGPANOFOLDER."/krpanotools/";
        $nggpano_options['kmakemultiresConfigFolder']	= "wp-content/plugins/".NGGPANOFOLDER."/krpanotools_configs/";
        $nggpano_options['kmakemultiresXMLConfig']	= "wp-content/plugins/".NGGPANOFOLDER."/krpanotools_xml_config/default.xml";
        
	
	//Krpano Viewer
	$nggpano_options['defaultSkinFile']	= 'default_template.xml';		// append related images
        $nggpano_options['krpanoFolder']	= "wp-content/plugins/".NGGPANOFOLDER . "/krpano/";
        $nggpano_options['skinFolder']          = "wp-content/plugins/".NGGPANOFOLDER . "/krpano_skins/";
        $nggpano_options['pluginFolder']	= "wp-content/plugins/".NGGPANOFOLDER . "/krpano_plugins/";
        
        */
    ?></p>
    <div id="poststuff">
        <form id="" method="POST" action="<?php echo $filepath; ?>" accept-charset="utf-8" >
            <?php wp_nonce_field('nggpano_settings') ?>
            <input type="hidden" name="page_options" value="toolConfigFile,krpanoToolsTempFolder,kmakemultiresFolder,kmakemultiresConfigFolder,defaultSkinFile,krpanoFolder,skinFolder,pluginFolder,widthPreview,heightPreview,lightboxEffect,colorboxCSSfile" />	
<!--            <input type="hidden" name="force" value="1" />  this will just force _POST['nggpano'] even if all checkboxes are unchecked -->
            <div class="postbox">
                <h3><?php _e('Preview Options', 'nggpano'); ?></h3>
                <p class="inside"><?php _e('Here you can set the default size for the preview of a panoramic', 'nggpano'); ?></p>
                <table class="form-table">
                    <tr valign="top">
                        <th colspan="2" align="left"><p class="inside" style="text-align:left"><?php _e('Once the panoramic is built, you can reduce the original image to have nice preview', 'nggpano'); ?></th>
                        <th colspan="2" align="left"><p class="inside" style="text-align:left"><?php _e('Script for LightBox effect', 'nggpano'); ?></th>
                    </tr>
                    <tr valign="top">
                        <th align="left"><?php _e('Preview Size','nggpano'); ?></th>
                        <td>
                            <input type="text" size="5" name="widthPreview" value="<?php echo $nggpano->options['widthPreview']; ?>" />
                            x <input type="text" size="5" name="heightPreview" value="<?php echo $nggpano->options['heightPreview']; ?>" />
                            <span class="setting-description"><?php _e('Max Width and Height in Pixel for the preview','nggpano') ?></span>
                        </td>
                        <th align="left"><?php _e('Script','nggpano'); ?></th>
                        <td>
                            <select name="lightboxEffect" id="lightboxEffect" style="margin: 0pt; padding: 0pt;">
                                <?php
                                $act_scriptfile = $nggpano->options['lightboxEffect'];
                                ?>
                                <option value="thickbox" <?php echo ($act_scriptfile == "thickbox" ) ? "selected='selected'" : "" ?>>Default Wordpress Thickbox</option>
                                <option value="colorbox" <?php echo ($act_scriptfile == "colorbox" ) ? "selected='selected'" : "" ?>>ColorBox (http://jacklmoore.com/colorbox/)</option>
<!--                                <option value="prettyphoto" <?php echo ($act_scriptfile == "prettyphoto" ) ? "selected='selected'" : "" ?>>PrettyPhoto (http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/)</option>-->
                                <option value="fancybox" <?php echo ($act_scriptfile == "fancybox" ) ? "selected='selected'" : "" ?>>FancyBox (http://fancyapps.com/fancybox/)</option>
                            </select>
                            <br/><span class="setting-description"><?php _e('For FancyBox in Creative Commons Attribution-NonCommercial 3.0 put the code source in the directory nextgen-gallery-panoramics/fancybox','nggpano') ?></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th align="left"></th>
                        <td></td>
                        <th align="left"><?php _e('css for ColorBox','nggpano'); ?></th>
                        <td>
                            <select name="colorboxCSSfile" id="colorboxCSSfile" style="margin: 0pt; padding: 0pt;">
                                <?php
                                $act_colorboxcss = $nggpano->options['colorboxCSSfile'];
				$configlist = nggpano_get_colorboxcssfile();
				foreach ($configlist as $key =>$configfile) {
					if ($key == $act_colorboxcss) {
						$file_show = $key;
						$selected = " selected='selected'";
					}
					else $selected = '';
					$configfile = esc_attr($configfile);
					echo "\n\t<option value=\"$key\" $selected>$configfile</option>";
				}
                                ?>
                            </select></td>
                    </tr>
                </table>               
                <h3><?php _e('KrpanoTools Options', 'nggpano'); ?></h3>
                <p class="inside"><?php _e('Here you can set the default options for <strong>krpanoTools</strong>', 'nggpano'); ?></p>
                <table class="form-table">
                    <tr valign="top">
                        <th colspan="2" align="left"><p class="inside" style="text-align:left"><?php _e('<strong>krpanoTools</strong> default options', 'nggpano'); ?></th>
                        <th colspan="2" align="left"><p class="inside" style="text-align:left"><?php _e('Folders for <strong>krpanoTools</strong>', 'nggpano'); ?></th>
                    </tr>
                    <tr valign="top">
                        <th align="left"><?php _e('kmakemultires Config File','nggpano'); ?></th>
                        <td>
                            <select name="toolConfigFile" id="toolConfigFile" style="margin: 0pt; padding: 0pt;">
                                <?php
                                $act_configfile = $nggpano->options['toolConfigFile'];
				$configlist = nggpano_get_kmakemultiresfiles();
				foreach ($configlist as $key =>$a_configfile) {
					$config_name = $a_configfile['Name'];
					if ($key == $act_configfile) {
						$file_show = $key;
						$selected = " selected='selected'";
						$act_config_description = $a_configfile['Description'];
						$act_config_author = $a_configfile['Author'];
						$act_config_version = $a_configfile['Version'];
					}
					else $selected = '';
					$config_name = esc_attr($config_name);
					echo "\n\t<option value=\"$key\" $selected>$config_name</option>";
				}
                                ?>
                            </select>
                            <span class="setting-description"><?php _e('This is the default config file to build all panoramics','nggpano') ?></span>
                        </td>
                        <th align="left"><?php _e('Folder for krpanoTools (kmakemultires)','nggpano'); ?></th>
                        <td>
                            <input type="text" size="60" name="kmakemultiresFolder" value="<?php echo $nggpano->options['kmakemultiresFolder']; ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th align="left"><?php _e('Temp directory','nggpano'); ?></th>
                        <td>
                            <input type="text" size="60" name="krpanoToolsTempFolder" value="<?php echo $nggpano->options['krpanoToolsTempFolder']; ?>" />
                            <span class="setting-description"><?php _e('Directory where krpanotools will work','nggpano') ?></span>
                        </td>
                        <th align="left"><?php _e('Folder for krpanoTools config files','nggpano'); ?></th>
                        <td>
                            <input type="text" size="60" name="kmakemultiresConfigFolder" value="<?php echo $nggpano->options['kmakemultiresConfigFolder']; ?>" />
                        </td>
                    </tr>
                </table>
                <h3><?php _e('Krpano Viewer Options', 'nggpano'); ?></h3>
                <p class="inside"><?php _e('Here you can set the default options for <strong>krpano viewer</strong>', 'nggpano'); ?></p>
                <table class="form-table">
                    <tr valign="top">
                        <th align="left"><?php _e('Default Skin file','nggpano'); ?></th>
                        <td>
                            <select name="defaultSkinFile" id="defaultSkinFile" style="margin: 0pt; padding: 0pt;">
                                <?php
                                $act_templatefile = $nggpano->options['defaultSkinFile'];
				$templatelist = nggpano_get_viewerskinfiles();
				foreach ($templatelist as $key =>$a_templatefile) {
					$template_name = $a_templatefile['Name'];
					if ($key == $act_templatefile) {
						$file_show = $key;
						$selected = " selected='selected'";
						$act_template_description = $a_templatefile['Description'];
						$act_template_author = $a_templatefile['Author'];
						$act_template_version = $a_templatefile['Version'];
					}
					else $selected = '';
					$template_name = esc_attr($template_name);
					echo "\n\t<option value=\"$key\" $selected>$template_name</option>";
				}
                                ?>
                            </select>
                            <span class="setting-description"><?php _e('This is the default skin/template use by krpano','nggpano') ?></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th align="left"><?php _e('Reset all galleries with this skin file','nggpano'); ?></th>
                        <td>
                            <input type="checkbox" name="force_reset" />
                            <span class="setting-description"><?php _e('Check this if you want set all galleries with this template file','nggpano') ?></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th align="left"><?php _e('Folder for krpano viewer (krpano.swf)','nggpano'); ?></th>
                        <td>
                            <input type="text" size="60"  name="krpanoFolder" value="<?php echo $nggpano->options['krpanoFolder']; ?>" />
                            <span class="setting-description"><?php _e('This is were krpano flash animations (krpano.swf) is locatedd','nggpano') ?></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th align="left"><?php _e('Folder for krpano viewer skins','nggpano'); ?></th>
                        <td>
                            <input type="text" size="60"  name="skinFolder" value="<?php echo $nggpano->options['skinFolder']; ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th align="left"><?php _e('Folder for krpano plugins','nggpano'); ?></th>
                        <td>
                            <input type="text" size="60" name="pluginFolder" value="<?php echo $nggpano->options['pluginFolder']; ?>" />
                        </td>
                    </tr>
                </table>
                <table class="form-table">
                    <tr>
                        <td colspan="2">
                            <div class="submit">
                                <input class="button-primary" type="submit" name="updateoption" value="<?php esc_attr_e('Save Changes'); ?>"/>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    </div>
</div>
<?php
//            }
    }


	
	function load_scripts() {
		global $wp_version;
        
		// no need to go on if it's not a plugin page
		if( !isset($_GET['page']) )
			return;

		wp_register_script('nggpano-ajax', NGGPANOGALLERY_URLPATH . 'admin/js/nggpano.admin.js', array('jquery'), '1.4.1');
                
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
				wp_enqueue_script( 'nggpano-ajax' );
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

		}
	}		
	
	function load_styles() {
        // load the icon for the navigation menu
        //wp_enqueue_style( 'nggmenu', NGGPANOGALLERY_URLPATH .'admin/css/menu.css', array() );
		wp_register_style( 'nggpanoadmin', NGGPANOGALLERY_URLPATH .'admin/css/nggpanoadmin.css', false, '1.0.0', 'screen' );
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
//                if ( !defined('IS_WP_3_3') )
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
                            wp_enqueue_style('thickbox');
//			case "nggallery-roles" :
//			case "nggallery-manage-album" :
//				wp_enqueue_style( 'ngg-jqueryui' );
//				wp_enqueue_style( 'nggadmin' );
			break;
//			case "nggallery-tags" :
//				wp_enqueue_style( 'nggtags', NGGPANOGALLERY_URLPATH .'admin/css/tags-admin.css', false, '2.6.1', 'screen' );
//				break;
//			case "nggallery-style" :
//				wp_admin_css( 'css/theme-editor' );
//				wp_enqueue_style('nggcolorpicker', NGGPANOGALLERY_URLPATH.'admin/js/colorpicker/css/colorpicker.css', false, '1.0', 'screen');
//				wp_enqueue_style('nggadmincp', NGGPANOGALLERY_URLPATH.'admin/css/nggColorPicker.css', false, '1.0', 'screen');
//			break;
		}	
	}
    
    
    
}


?>