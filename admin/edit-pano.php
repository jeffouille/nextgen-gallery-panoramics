<?php

require_once( dirname( dirname(__FILE__) ) . '/nggpano-config.php');
//require_once(NGGPANOGALLERY_ABSPATH . '/lib/nggpanoPano.class.php' );

if ( !is_user_logged_in() )
	die(__('Cheatin&#8217; uh?'));
	
if ( !current_user_can('NGG Panoramics Manage gallery') ) 
	die(__('Cheatin&#8217; uh?'));

global $wpdb;

$id = (int) $_GET['id'];

// let's get the image data
$picture = nggdb::find_image($id);

$pano_infos = nggpano_getImagePanoramicOptions($id);

// use defaults the first time
//if($pano_infos && isset ($pano_infos->hfov)) {
    $hfov       = isset ($pano_infos->hfov) ? $pano_infos->hfov : '';
    $vfov       = isset ($pano_infos->vfov) ? $pano_infos->vfov : '';
    $voffset    = isset ($pano_infos->voffset) ? $pano_infos->voffset : '';
    $xml_configuration    = isset ($pano_infos->xml_configuration) ? $pano_infos->xml_configuration : '';
    $is_partial    = isset ($pano_infos->is_partial) ? $pano_infos->is_partial : '0';
    $panoFolder = isset ($pano_infos->pano_directory) ? $pano_infos->pano_directory : '';

$action_filepath = NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=edit-pano&id=' . $id;


$paged= isset($_GET['paged']) ? (int) $_GET['paged'] : '';

$page_admin = admin_url()."admin.php?page=nggallery-manage-gallery&mode=edit&gid=".$picture->galleryid."&paged=".$paged;

?>

<form id="form-edit-pano" method="POST" accept-charset="utf-8" action="<?php echo $action_filepath; ?>">
<?php wp_nonce_field('edit-pano') ?>
<input type="hidden" name="page" value="edit-pano" />
<input type="hidden" name="pid" value="<?php echo $picture->pid; ?>" />
<input type="hidden" name="gid" value="<?php echo $picture->galleryid; ?>" />
<input type="hidden" name="pageredirect" value="<?php echo $page_admin; ?>" />
<table width="100%" border="0" cellspacing="3" cellpadding="3" >
	<tr valign="top">
            <th align="left"><?php _e('Horizontal FOV x Vertical FOV (in degrees)','nggpano') ?></th>
            <td>
                <input type="text" size="5" id="hfov" name="hfov" value="<?php echo $hfov; ?>" />* x <input type="text" size="5" id="vfov" name="vfov" value="<?php echo $vfov; ?>" />
                <br /><small><?php _e('Panoramic Field of View (fov)','nggpano') ?></small>
            </td>
	</tr>
	<tr valign="top">
            <th align="left"><?php _e('Vertical Offset (in degrees)','nggpano') ?></th>
            <td>
                <input type="text" size="5" id="voffset" name="voffset" value="<?php echo $voffset; ?>" />
                <br /><small><?php _e('(Optional) vertical shift away from the horizon (+/- degrees)','nggpano') ?></small>
            </td>
	</tr>
	<tr valign="top">
            <th align="left"><?php _e('Partial Panoramic','nggpano') ?></th>
            <td>
                <input type="checkbox" name="is_partial" <?php echo ($is_partial == "0" ? '' : 'checked="checked"'); ?> value="1" />
            </td>
	</tr>
	<tr valign="top">
            <th align="left"><?php _e('Folder with panoramic tiles','nggpano') ?></th>
            <td>
                <input type="text" size="40" id="panoFolder" name="panoFolder" value="<?php echo $panoFolder; ?>" />
                <br /><small><?php _e('Put here all the tiles of your panoramic','nggpano') ?></small>
            </td>
	</tr>
	<tr valign="top">
            <th colspan="2" align="left"><?php _e('Xml configuration','nggpano') ?></th>
	</tr>
	<tr valign="top">
            <td colspan="2">
                <textarea name="xml_configuration" id="xml_configuration" cols="30" rows="10" style="width: 100%" ><?php echo $xml_configuration; ?></textarea>
            </td>
	</tr>
  	<tr align="right">
            <td align="center"><img class="nggpano-fov-loader" id="nggpano-fov-loader" src="<?php echo NGGPANOGALLERY_URLPATH ; ?>admin/images/loading.gif" style="display:none;" /><div id="form-edit-pano-error" class="nggpano-error" style="display:none;"></div></td>
            <td class="submit">
    		<input class="button-primary" type="submit" name="save" value="&nbsp;<?php _e('Update', 'nggalery');?>&nbsp;" onclick="if ( !checkEditForm() ) return false;" />
            </td>
	</tr>
</table>
</form>


<script type="text/javascript"> 
<!--

// this function check that hfov is here and valid, if vfov and offset are here check validity
function checkEditForm() {
    
    var errormessage = '';
    
    var hfov = jQuery('#hfov').val();
    var vfov = jQuery('#vfov').val();
    var voffset = jQuery('#voffset').val();
    var xmlconfiguration = jQuery('#xml_configuration').val();
    
    if (hfov == ""){
        
        errormessage = '<?php _e('Horizontal FOV is required','nggpano') ?>';
        jQuery('#form-edit-pano-error').removeClass('success').addClass('error').text(errormessage).show(1000);
        return false                                       
    }
    
    if(!isNumber(hfov) || (!isNumber(vfov) && vfov != "") || (!isNumber(voffset) && voffset != "")) {
        errormessage = '<?php _e('Decimal Number are required ex. 12.56','nggpano') ?>';
        jQuery('#form-edit-pano-error').removeClass('success').addClass('error').text(errormessage).show(1000);
        return false  
    }
    
    if (jQuery.trim(xmlconfiguration) == ""){
        errormessage = '<?php _e('XML Configuration is required','nggpano') ?>';
        jQuery('#form-edit-pano-error').removeClass('success').addClass('error').text(errormessage).show(1000);
        return false                                       
    }
    
    var check=confirm( '<?php echo esc_attr(sprintf(__('Save data for %s ?' , 'nggpano'), $picture->filename)); ?>');
    if(check==false)
        return false;
    
    return true;

}

   
jQuery('#form-edit-pano').submit(function(e) {
    

// 
    
    /* stop form from submitting normally */
    e.preventDefault();
    
    //get pid
    var pid = jQuery(this).children('input[name="pid"]').val();
    //get dom to modify (column with nggpano_krpano_fields class in active line)
    var container = jQuery('#picture-'+pid+' > td.nggpano_krpano_fields');
    //container.find('img.nggpano-pano-loader').show();

    var url = jQuery(this).attr('action');

    jQuery('#nggpano-fov-loader').show();

    jQuery.ajax({
                    url: url,
                    data: jQuery(this).serialize(),
                    dataType : 'json',
                    type: 'POST',
                    success: function(data, textStatus, XMLHttpRequest) {
                        //{"error":false,"message":"GPS datas successfully saved","gps_data":{"latitude":48.27710215,"longitude":-4.59594487998,"altitude":7,"timestamp":"9:44:45"}}
                            if(data) {
                                //$('#waiting').hide(500);
                                //container.find('div.nggpano-error').removeClass((data.error === true) ? 'success' : 'error').addClass((data.error === true) ? 'error' : 'success').text(data.message).show(1000,function(){jQuery(this).delay(2000).hide(500);});
                                return true;
                                //container.html(data);
//                                                            if (data.error === false) {
//                                                                //remove thumbnail
//                                                                container.find('a.shutter').remove();
//                                                                
//                                                                //remove link to delete
//                                                                currentdiv.remove();
//                                                                
//                                                            }
                                //console.log('delete ok');
                                //jQuery('div.nggpano-error').show(1000,function(){jQuery(this).delay(2000).hide(500);});
                                //container.find('div.nggpano-error').removeClass('error').addClass('class').show(1000,function(){jQuery(this).delay(2000).hide(500);});

                            }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        errormessage = '<?php esc_attr(_e('Problem in update, please try again in a few moments.','nggpano') ); ?>';
                        jQuery('#form-edit-pano-error').removeClass('success').addClass('error').text(errormessage).show(1000);
                    },
                    complete: function() {
                        jQuery('#nggpano-fov-loader').hide().delay(2000);
                        jQuery('#form-edit-pano').closest('.ui-dialog').dialog('destroy'); 
                        jQuery('#nggpano-dialog').remove(); 
                       
                    }
    });
     //cloase dialog box
                        
    return false;
});
//-->
</script>