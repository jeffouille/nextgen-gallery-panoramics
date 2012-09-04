<?php
function nggpano_admin_overview() {
    global $nggpano;
    $filepath = admin_url()."admin.php?page=".$_GET["page"];
        
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
    
    ?>
    <div class="wrap">
        <?php screen_icon( 'nextgen-gallery-panoramics' ); ?>
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
             * 
            heightThumbVirtualTour

            */
        ?></p>
        <div id="poststuff">
            <form id="" method="POST" action="<?php echo $filepath; ?>" accept-charset="utf-8" >
                <?php wp_nonce_field('nggpano_settings') ?>
                <input type="hidden" name="page_options" value="toolConfigFile,krpanoToolsTempFolder,kmakemultiresFolder,kmakemultiresConfigFolder,defaultSkinFile,krpanoFolder,skinFolder,pluginFolder,widthPreview,heightPreview,lightboxEffect,colorboxCSSfile,heightThumbVirtualTour,widthThumbVirtualTour,bingmap_key,use_bingmap,use_gyro" />	
    <!--            <input type="hidden" name="force" value="1" />  this will just force _POST['nggpano'] even if all checkboxes are unchecked -->
                <div class="postbox">
                    <h3><?php _e('Preview Options', 'nggpano'); ?></h3>
                    <p class="inside"><?php _e('Here you can set the default size for the preview of a panoramic', 'nggpano'); ?></p>
                    <table class="form-table">
                        <tr valign="top">
                            <th colspan="2" align="left"><p class="inside" style="text-align:left"><?php _e('Once the panoramic is built, you can reduce the original image to have a nice preview', 'nggpano'); ?></th>
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
                        <tr valign="top" id="colorboxCSSfileTR">
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
                                <span class="setting-description"><?php _e('This is the default config file to build all panoramics','nggpano') ?> <a href="?page=nggpano-tool-config"><?php _e('Edit','nggpano'); ?></a></span>
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
                                <span class="setting-description"><?php _e('This is the default skin/template use by krpano','nggpano') ?> <a href="?page=nggpano-style"><?php _e('Edit','nggpano'); ?></a></span>
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
                            <th align="left"><?php _e('Use BingMap plugin','nggpano'); ?></th>
                            <td>
                                <input type="checkbox" name="use_bingmap" value="1" <?php checked('1', $nggpano->options['use_bingmap']); ?> />
                                <span class="setting-description"><?php _e('Check this if you want that Bing Map Plugin appear in all panoramics','nggpano') ?></span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th align="left"><?php _e('Bing Map Key','nggpano'); ?></th>
                            <td>
                                <input type="text" size="80" name="bingmap_key" value="<?php echo $nggpano->options['bingmap_key']; ?>" />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th align="left"><?php _e('Use Gyroscope plugin','nggpano'); ?></th>
                            <td>
                                <input type="checkbox" name="use_gyro" value="1" <?php checked('1', $nggpano->options['use_gyro']); ?> />
                                <span class="setting-description"><?php _e('Check this if you want use gyroscope in mobile for all panoramics','nggpano') ?></span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th align="left"><?php _e('Folder for krpano viewer (krpano.swf)','nggpano'); ?></th>
                            <td>
                                <input type="text" size="60"  name="krpanoFolder" value="<?php echo $nggpano->options['krpanoFolder']; ?>" />
                                <span class="setting-description"><?php _e('This is where krpano flash animation (krpano.swf) is located','nggpano') ?></span>
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
                        <tr valign="top">
                            <th align="left"><?php _e('Thumbnail Size for VirtualTour','nggpano'); ?></th>
                            <td>
                                <input type="text" size="5" name="widthThumbVirtualTour" value="<?php echo $nggpano->options['widthThumbVirtualTour']; ?>" />
                                x <input type="text" size="5" name="heightThumbVirtualTour" value="<?php echo $nggpano->options['heightThumbVirtualTour']; ?>" />
                                <span class="setting-description"><?php _e('Max Width and Height in Pixel for the thumbnail','nggpano') ?></span>
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
<script type="text/javascript">
    //<![CDATA[
jQuery(document).ready(function(){
    jQuery("#colorboxCSSfileTR").hide();

    jQuery("#lightboxEffect").change(function ()
    {
        jQuery("#colorboxCSSfileTR").hide();
        var selected_lightbox = jQuery(this).attr('value');
        
        if(selected_lightbox == 'colorbox')
        jQuery("#colorboxCSSfileTR").show();
 
     }).change();


});

//]]> 
</script>
<?php

}
?>