<?php
require_once( dirname( dirname(__FILE__) ) . '/nggpano-config.php');
//require_once(NGGPANOGALLERY_ABSPATH . '/lib/nggpanoPano.class.php' );

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


$action_filepath = NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=publish-pano&id=' . $id;

?>

<form id="form-publish-pano" method="POST" accept-charset="utf-8" action="<?php echo $action_filepath; ?>">
<?php wp_nonce_field('publish-pano') ?>
<input type="hidden" name="page" value="publish-pano" />
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
            <th align="left"><?php _e('Width x height (in pixel)','nggallery') ?></th>
            <td>
                <input type="text" size="5" maxlength="5" name="width" value="<?php echo $width; ?>" /> x <input type="text" size="5" maxlength="5" name="height" value="<?php echo $height; ?>" />
                <br /><small><?php _e('Size of the image, the map or the panoramic','nggpano') ?></small>
            </td>
	</tr>
	<tr valign="top">
            <th align="left"><?php _e('Alignment','nggallery') ?></th>
            <td>
                <input type="radio" value="none" <?php checked('none', $align); ?> id="image-align-none" name="align"/>
                <label class="align" for="image-align-none"><?php _e('None','nggallery'); ?></label>
                <input type="radio" value="left" <?php checked('left', $align); ?> id="image-align-left" name="align"/>
                <label class="align" for="image-align-left"><?php _e('Left','nggallery'); ?></label>
                <input type="radio" value="center" <?php checked('center', $align); ?> id="image-align-center" name="align"/>
                <label class="align" for="image-align-center"><?php _e('Center','nggallery'); ?></label>
                <input type="radio" value="right" <?php checked('right', $align); ?> id="image-align-right" name="align"/>
                <label class="align" for="image-align-right"><?php _e('Right','nggallery'); ?></label>
            </td>
	</tr>
        <tr valign="top">
            <th align="left"><?php _e('Caption Mode','nggpano') ?></th>
            <td>
                <input type="radio" value="none" <?php checked('none', $default_caption); ?> id="captiontype-none" name="captiontype"/>
                <label class="align" for="captiontype-none"><?php _e('None','nggpano'); ?></label>
                <input type="radio" value="full" <?php checked('full', $default_caption); ?> id="captiontype-full" name="captiontype"/>
                <label class="align" for="captiontype-full"><?php _e('Full','nggpano'); ?></label>
                <input type="radio" value="title" <?php checked('title', $default_caption); ?> id="captiontype-title" name="captiontype"/>
                <label class="align" for="captiontype-title"><?php _e('Title','nggpano'); ?></label>
                <input type="radio" value="description" <?php checked('description', $default_caption); ?> id="captiontype-description" name="captiontype"/>
                <label class="align" for="captiontype-description"><?php _e('Description','nggpano'); ?></label>
                <br /><small><?php _e('Caption to show under the item','nggpano') ?></small>
            </td>
        </tr>
        <tr valign="top">
            <th align="left"><?php _e('Shortcode','nggpano') ?></th>
            <td>
                <select style="margin: 0pt; padding: 0pt;" id="shortcode" name="shortcode">
                    <option selected="selected" value="singlepano"><?php _e('Panoramic','nggpano') ?> [singlepano]</option>
                    <option value="singlepanowithmap"><?php _e('Panoramic with map','nggpano') ?> [singlepanowithmap]</option>
                    <option value="singlepicwithmap"><?php _e('Picture with map','nggpano') ?> [singlepicwithmap]</option>
                    <option value="singlepicwithlinks"><?php _e('Picture with links','nggpano') ?> [singlepicwithlinks]</option>
                    <option value="singlemap"><?php _e('Map with infowindow','nggpano') ?> [singlemap]</option>
                </select>
            </td>
        </tr>
        <tr valign="top" class="shortcode_options singlepanowithmap singlepicwithmap singlemap">
            <th align="left"><?php _e('Map Width x height (in pixel)','nggpano') ?></th>
            <td>
                <input type="text" size="5" maxlength="5" name="mapw" value="" /> x <input type="text" size="5" maxlength="5" name="maph" value="" />
                <br /><small><?php _e('Size of the map','nggpano') ?></small>
            </td>
        </tr>
        <tr valign="top" class="shortcode_options singlepanowithmap singlepicwithmap singlepicwithlinks singlemap" id="mapzoom">
            <th align="left"><?php _e('Map Zoom','nggpano') ?></th>
            <td>
                <select style="margin: 0pt; padding: 0pt;" id="mapz" name="mapz">
                    <?php 
                    for ($i = 1; $i < 19; $i++) {
                        echo '<option '.(($i == 12) ? 'selected="selected"' : '') .' value="'.$i.'">'.$i.'</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr valign="top" class="shortcode_options singlepanowithmap singlepicwithmap singlepicwithlinks singlemap">
            <th align="left"><?php _e('Map Type','nggpano') ?></th>
            <td>
                <input type="radio" value="hybrid" <?php checked('hybrid', $default_maptype); ?> id="maptype-hybrid" name="maptype"/>
                <label class="align" for="maptype-hybrid"><?php _e('Hybrid','nggpano'); ?></label>
                <input type="radio" value="roadmap" <?php checked('roadmap', $default_maptype); ?> id="maptype-roadmap" name="maptype"/>
                <label class="align" for="maptype-roadmap"><?php _e('Roadmap','nggpano'); ?></label>
                <input type="radio" value="satellite" <?php checked('satellite', $default_maptype); ?> id="maptype-satellite" name="maptype"/>
                <label class="align" for="maptype-satellite"><?php _e('Satellite','nggpano'); ?></label>
                <input type="radio" value="terrain" <?php checked('terrain', $default_maptype); ?> id="maptype-terrain" name="maptype"/>
                <label class="align" for="maptype-terrain"><?php _e('Terrain','nggpano'); ?></label>
            </td>
        </tr>
        <tr valign="top" class="shortcode_options singlepicwithlinks singlemap">
            <th align="left"><?php _e('Links to show','nggpano') ?></th>
            <td>
                <input type="radio" value="none" <?php checked('none', $default_links); ?> id="links-none" name="links"/>
                <label class="align" for="links-none"><?php _e('None','nggpano'); ?></label>
                <input type="radio" value="all" <?php checked('all', $default_links); ?> id="links-all" name="links"/>
                <label class="align" for="links-all"><?php _e('All links','nggpano'); ?></label>
                <input type="radio" value="select" <?php checked('select', $default_links); ?> id="links-select" name="links"/>
                <label class="align" for="links-select"><?php _e('Selected links','nggpano'); ?></label>
                
                <div id="choice_links">
                    <input type="checkbox" value="picture" <?php checked('picture', $default_links); ?> id="links-picture" name="links_picture"/>
                    <label class="align" for="links-picture"><?php _e('Picture','nggpano'); ?></label>
                    <input type="checkbox" value="map" <?php checked('map', $default_links); ?> id="links-map" name="links_map"/>
                    <label class="align" for="links-map"><?php _e('Map','nggpano'); ?></label>
                    <input type="checkbox" value="pano" <?php checked('pano', $default_links); ?> id="links-pano" name="links_pano"/>
                    <label class="align" for="links-pano"><?php _e('Panoramic','nggpano'); ?></label>
                </div>
                    <small><?php _e('Links to show under the item','nggpano') ?></small>
            </td>
        </tr>
        <tr valign="top" class="shortcode_options singlepicwithlinks singlemap">
            <th align="left"><?php _e('Main Link','nggpano') ?></th>
            <td>
                <input type="radio" value="none" <?php checked('none', $default_caption); ?> id="mainlink-none" name="mainlink"/>
                <label class="align" for="mainlink-none"><?php _e('None','nggpano'); ?></label>
                <input type="radio" value="picture" <?php checked('picture', $default_caption); ?> id="mainlink-picture" name="mainlink"/>
                <label class="align" for="mainlink-picture"><?php _e('Picture','nggpano'); ?></label>
                <input type="radio" value="map" <?php checked('map', $default_caption); ?> id="mainlink-map" name="mainlink"/>
                <label class="align" for="mainlink-map"><?php _e('Map','nggpano'); ?></label>
                <input type="radio" value="pano" <?php checked('pano', $default_caption); ?> id="mainlink-pano" name="mainlink"/>
                <label class="align" for="mainlink-pano"><?php _e('Panoramic','nggpano'); ?></label>
                <br /><small><?php _e('Link when click on the item','nggpano') ?></small>
            </td>
        </tr>
  	<tr align="right">
            <td><div id="form-publish-pano-error" class="nggpano-error" style="display:none;"></div></td>
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
jQuery(document).ready(function(){
    jQuery("tr.shortcode_options").hide();
    jQuery("#choice_links").hide();


    jQuery("#shortcode").change(function ()
    {
        var selected_shortcode = jQuery(this).attr('value');
        jQuery("tr.shortcode_options").hide();
        jQuery("tr."+selected_shortcode).show();
 
     }).change();

    jQuery("input[name='links']").change( function() {
        if (jQuery(this).attr('value')=='select') {
            jQuery("#choice_links").show();
        } else {
            jQuery("#choice_links").hide();
        }

    });


});


// this function check that hfov is here and valid, if vfov and offset are here check validity
function checkPublishForm(publish) {
    
    var errormessage = '';
    
    var post_title = jQuery('#post_title').val();
    
    jQuery('#publish_state').val(publish);
    
    if (jQuery.trim(post_title) == ""){
        
        errormessage = '<?php _e('Article title is required','nggpano') ?>';
        jQuery('#form-publish-pano-error').removeClass('success').addClass('error').text(errormessage).show(1000);
        return false                                       
    }

    var check=confirm( '<?php echo esc_attr(sprintf(__('Publish the article for panoramic %s ?' , 'nggpano'), $picture->filename)); ?>');
    if(check==false)
        return false;
    
    return true;

}

  
jQuery('#form-publish-pano').submit(function(e) {
    

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
                                return true;
                            }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        errormessage = '<?php esc_attr(_e('Problem in update, please try again in a few moments.','nggpano') ); ?>';
                        jQuery('#form-publish-pano-error').removeClass('success').addClass('error').text(errormessage).show(1000);
                    },
                    complete: function() {
                        jQuery('#nggpano-fov-loader').hide().delay(2000);
                        jQuery('#form-publish-pano').closest('.ui-dialog').dialog('destroy'); 
                        jQuery('#nggpano-dialog').remove(); 
                       
                    }
    });
     //cloase dialog box
                        
    return false;
});
//]]> 
</script>