<?php
require_once( dirname( dirname(__FILE__) ) . '/nggpano-config.php');

if ( !is_user_logged_in() )
	die(__('Cheatin&#8217; uh?'));
	
if ( !current_user_can('NGG Panoramics Manage gallery') ) 
	die(__('Cheatin&#8217; uh?'));

	
if ( !current_user_can('NextGEN Manage gallery') ) 
	die(__('Cheatin&#8217; uh?'));

if ( !current_user_can( 'publish_posts' ) )
    die(__('Cheatin&#8217; uh?'));


//require_once( dirname( dirname(__FILE__) ) . '/ngg-config.php');
//require_once( NGGALLERY_ABSPATH . '/lib/image.php' );

global $wpdb;

$id = (int) $_GET['id'];

// let's get the image data
$picture = nggdb::find_image($id);

// use defaults the first time
$width  = empty ($ngg->options['publish_width'])  ? $ngg->options['thumbwidth'] : $ngg->options['publish_width'];
$height = empty ($ngg->options['publish_height']) ? $ngg->options['thumbheight'] : $ngg->options['publish_height'];
$align  = empty ($ngg->options['publish_align'])  ? 'none' : $ngg->options['publish_align'];

$default_maptype = 'hybrid';

$default_caption = 'none';

$default_links = 'none';

$default_mainlink = 'picture';

$action_filepath = NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=publish-pano-infocus&id=' . $id;

?>

<form id="form-publish-pano-infocus" method="POST" accept-charset="utf-8" action="<?php echo $action_filepath; ?>">
<?php wp_nonce_field('publish-pano-infocus') ?>
<input type="hidden" name="page" value="publish-pano-infocus" />
<input type="hidden" name="pid" value="<?php echo $picture->pid; ?>" />
<input type="hidden" name="publish_state" id="publish_state" value="" />
<table width="100%" border="0" cellspacing="3" cellpadding="3" >
	<tr valign="top">
            <th align="left"><?php _e('Post title','nggallery') ?></th>
            <td>
                <input type="text" size="70" id="post_title" name="post_title" value="<?php echo stripslashes($picture->alttext);  ?>" />
                <br /><small><?php _e('Enter the post title ','nggallery') ?></small>
            </td>
	</tr>
	<tr valign="top">
            <th align="left"><?php _e('Post content','nggpano') ?></th>
            <td>
                <textarea rows="2" style="width:95%; margin-top: 2px;" id="post_content" name="post_content"><?php echo stripslashes($picture->description);  ?></textarea>
                <br /><small><?php _e('Enter the post content ','nggpano') ?></small>
            </td>
	</tr>
	<tr valign="top">
            <th align="left"><?php _e('Category','nggpano') ?></th>
            <td>
                <?php $args = array(
                        'show_option_all'    => '',
                        'show_option_none'   => '',
                        'orderby'            => 'NAME', 
                        'order'              => 'ASC',
                        'show_count'         => 1,
                        'hide_empty'         => 0, 
                        'child_of'           => 0,
                        'exclude'            => '',
                        'echo'               => 0,
                        'selected'           => 0,
                        'hierarchical'       => 1, 
                        'name'               => 'category[]',
                        'id'                 => 'category_select',
                        'class'              => 'category_select',
                        'depth'              => 3,
                        'tab_index'          => 0,
                        'taxonomy'           => 'category',
                        'hide_if_empty'      => false );
                    $select_cats = wp_dropdown_categories( $args );
                    $select_cats = str_replace( 'id=', 'multiple="multiple" id=', $select_cats );
                    echo $select_cats;
                ?>
            </td>
	</tr>
	<tr valign="top">
            <th align="left"><?php _e('Use EXIF date','nggpano') ?></th>
            <td>
                <input type="checkbox" name="exif_date" checked="checked" value="1" />
            </td>
	</tr>
        
	<tr valign="top">
            <th align="left"><?php _e('Add Featured Image','nggpano') ?></th>
            <td>
                <input type="checkbox" name="featured_image" checked="checked" value="1" />
                <br /><small><?php _e('Use this picture as the featured image of the article ','nggpano') ?></small>
            </td>
	</tr>
	<tr valign="top">
            <th align="left"><?php _e('With tags','nggpano') ?></th>
            <td>
                <input type="checkbox" name="with_tags" checked="checked" value="1" />
                <br /><small><?php _e('Use all image\'s the keyword for the article ','nggpano') ?></small>
            </td>
	</tr>
  	<tr align="right">
            <td><div id="form-publish-pano-infocus-error" class="nggpano-error" style="display:none;"></div></td>
            <td class="submit">
                <img class="nggpano-fov-loader" id="nggpano-fov-loader" src="<?php echo NGGPANOGALLERY_URLPATH ; ?>admin/images/loading.gif" style="display:none;" />
                    <input class="button-primary" type="submit" name="publish" value="<?php _e('Publish', 'nggallery');?>" onclick="if ( !checkPublishForm(true) ) return false;" />
                    &nbsp;
                    <input class="button-secondary" type="submit" name="draft" value="&nbsp;<?php _e('Draft', 'nggallery'); ?>&nbsp;"  onclick="if ( !checkPublishForm(false) ) return false;"  />
            </td>
	</tr>
</table>
</form>


<script type="text/javascript">
    //<![CDATA[

// this function check that hfov is here and valid, if vfov and offset are here check validity
function checkPublishForm(publish) {
    
    var errormessage = '';
    
    var post_title = jQuery('#post_title').val();
    
    jQuery('#publish_state').val(publish);
    
    if (jQuery.trim(post_title) == ""){
        
            errormessage = '<?php esc_js('Article title is required','nggpano') ?>';
        jQuery('#form-publish-pano-infocus-error').removeClass('success').addClass('error').text(errormessage).show(1000);
        return false                                       
    }

    var check=confirm( '<?php echo esc_attr(sprintf(__('Publish the article for panoramic %s ?' , 'nggpano'), $picture->filename)); ?>');
    if(check==false)
        return false;
    
    return true;

}

  
jQuery('#form-publish-pano-infocus').submit(function(e) {
    

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
                    dataType : 'html',
                    type: 'POST',
                    success: function(data, textStatus, XMLHttpRequest) {
                        //{"error":false,"message":"GPS datas successfully saved","gps_data":{"latitude":48.27710215,"longitude":-4.59594487998,"altitude":7,"timestamp":"9:44:45"}}
                            if(data) {
                                container.html(data);
                            }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        errormessage = '<?php esc_attr(_e('Problem in update, please try again in a few moments.','nggpano') ); ?>';
                        jQuery('#form-publish-pano-infocus-error').removeClass('success').addClass('error').text(errormessage).show(1000);
                    },
                    complete: function() {
                        jQuery('#nggpano-fov-loader').hide().delay(2000);
                        jQuery('#form-publish-pano-infocus').closest('.ui-dialog').dialog('destroy'); 
                        jQuery('#nggpano-dialog').remove(); 
                       
                    }
    });
     //cloase dialog box
                        
    return false;
});
//]]> 
</script>