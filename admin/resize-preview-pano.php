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

$action_filepath = NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=resize-preview-pano&id=' . $id;

?>

<form id="form-resize-preview-pano" method="POST" accept-charset="utf-8" action="<?php echo $action_filepath; ?>">
<?php wp_nonce_field('resize-preview-pano') ?>
<input type="hidden" name="page" value="resize-preview-pano" />
<input type="hidden" name="pid" value="<?php echo $picture->pid; ?>" />
<input type="hidden" name="gid" value="<?php echo $picture->galleryid; ?>" />
<input type="hidden" name="initWidth" value="<?php echo $picture->meta_data['width']; ?>" />
<input type="hidden" name="initHeight" value="<?php echo $picture->meta_data['height']; ?>" />
<table width="100%" border="0" cellspacing="3" cellpadding="3" >
        <tr valign="top">
            <td>
                <strong><?php _e('Resize flat preview to', 'nggpano'); ?>:</strong> 
            </td>
            <td>
                <input type="text" size="5" id="imgWidth" name="imgWidth" value="<?php echo $nggpano->options['widthPreview']; ?>" /> x <input type="text" size="5" id="imgHeight" name="imgHeight" value="<?php echo $nggpano->options['heightPreview']; ?>" />
                <br /><small><?php _e('Width x height (in pixel). NextGEN Gallery Pano will keep ratio size','nggpano') ?></small>
            </td>
        </tr>
        <tr valign="top">
                <th align="left"><?php _e('Make backup', 'nggpano'); ?></th>
                <td><input type="checkbox" value="1" name="backup">
                <br><small><?php _e('If not checked, initial image will be destroy', 'nggpano'); ?></small></td>
        </tr>
        <tr valign="top" align="right">
            <td colspan="2" valign="center" align="center"><img class="nggpano-preview-loader" id="nggpano-preview-loader" src="<?php echo NGGPANOGALLERY_URLPATH ; ?>admin/images/loading.gif" style="display:none;" /></td>
        </tr>
        <tr align="right">
            <td align="center"><div id="form-resize-preview-pano-error" class="nggpano-error" style="display:none;"></div></td>
            <td class="submit">
                <input class="button-primary" type="submit" name="make-preview" value="&nbsp;<?php _e('OK', 'nggpano');?>&nbsp;" onclick="if ( !checkPreviewForm() ) return false;" />
            </td>
        </tr>
</table>
</form>


<script type="text/javascript"> 
<!--

// this function check that width and height are here and valid
function checkPreviewForm() {
    
    var errormessage = '';
    
    var imgWidth = jQuery('#imgWidth').val();
    var imgHeight = jQuery('#imgHeight').val();
    
    if (imgWidth == ""){
        
        errormessage = '<?php _e('Width is required','nggpano') ?>';
        jQuery('#form-resize-preview-pano-error').removeClass('success').addClass('error').text(errormessage).show(1000);
        return false                                       
    }
    
    if(!isNumber(imgWidth) || (!isNumber(imgHeight) && imgHeight != "")) {

        errormessage = '<?php _e('Number are required ex. 2000','nggpano') ?>';
        jQuery('#form-resize-preview-pano-error').removeClass('success').addClass('error').text(errormessage).show(1000);
        return false  
    }
    
    var check=confirm( '<?php echo esc_attr(sprintf(__('make preview for %s ?' , 'nggpano'), $picture->filename)); ?>');
    if(check==false)
        return false;
    
    return true;

}

   
jQuery('#form-resize-preview-pano').submit(function(e) {
    

// 
    
    /* stop form from submitting normally */
    e.preventDefault();
    
    //get pid
    var pid = jQuery(this).children('input[name="pid"]').val();
    //get dom to modify (column with nggpano_krpano_fields class in active line)
    var container = jQuery('#picture-'+pid+' > td.nggpano_krpano_fields');
    //get dom to modify dimension information (column with nggpano_krpano_fields class in active line)
    var containerimage = jQuery('#picture-'+pid+' > td.column-filename');
    
    var containerimageinitialcontent = containerimage.html();
    
    var initwidth = jQuery(this).children('input[name="initWidth"]').val();
    var initheight = jQuery(this).children('input[name="initHeight"]').val();
    var initsizeinfo = initwidth + ' x ' + initheight + ' <?php _e('pixel', 'nggallery'); ?>';
    
    var imgWidth = jQuery('#imgWidth').val();
    var imgHeight = jQuery('#imgHeight').val();
    var newsizeinfo = imgWidth + ' x ' + imgHeight + ' <?php _e('pixel', 'nggallery'); ?>';
    
    var containerimagenewcontent = containerimageinitialcontent.replace(initsizeinfo, newsizeinfo);
    //container.find('img.nggpano-pano-loader').show();

    var url = jQuery(this).attr('action');

    jQuery('#nggpano-preview-loader').show();

    jQuery.ajax({
                    url: url,
                    data: jQuery(this).serialize(),
                    dataType : 'html',
                    type: 'POST',
                    success: function(data, textStatus, XMLHttpRequest) {
                        if(data) {
                            container.html(data);
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        errormessage = '<?php esc_attr(_e('Problem in Preview creation, please try again in a few moments.','nggpano') ); ?>';
                        jQuery('#form-resize-preview-pano-error').removeClass('success').addClass('error').text(errormessage).show(1000);
                    },
                    complete: function() {
                        jQuery('#nggpano-preview-loader').hide().delay(2000);
                        jQuery('#form-resize-preview-pano').closest('.ui-dialog').dialog('destroy'); 
                        jQuery('#nggpano-dialog').remove();
                        //change size info content
                        containerimage.html(containerimagenewcontent);
                       
                    }
    });
                        
    return false;
});
//-->
</script>