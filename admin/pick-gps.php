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

$image_values = nggpano_getImagePanoramicOptions($id);

// use defaults the first time
if($image_values) {

    $lat = isset($image_values->gps_lat) ? $image_values->gps_lat : '46.578498';
    $lng = isset($image_values->gps_lng) ? $image_values->gps_lng : '2.457275';
    $alt = isset($image_values->gps_alt) ? $image_values->gps_alt : '0';
    
    $zoom = isset($image_values->gps_lat) ? '14' : '5';
}
$action_filepath = NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=save-gps&id=' . $id;

$action_ajax_fovextract_path = NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=extractfov&id=' . $id;

$paged= isset($_GET['paged']) ? (int) $_GET['paged'] : '';

$page_admin = admin_url()."admin.php?page=nggallery-manage-gallery&mode=edit&gid=".$picture->galleryid."&paged=".$paged;

?>

<form id="form-gps-pano" method="POST" accept-charset="utf-8" action="<?php echo $action_filepath; ?>">
<?php wp_nonce_field('pick-gps') ?>
<input type="hidden" name="page" value="pick-gps" />
<input type="hidden" name="pid" value="<?php echo $picture->pid; ?>" />
<input type="hidden" name="gid" value="<?php echo $picture->galleryid; ?>" />
<input type="hidden" name="pageredirect" value="<?php echo $page_admin; ?>" />

<?php esc_html_e('Move picker to the Location or Enter an address.', 'nggpano'); ?><img class="nggpano-gps-loader" id="nggpano-gps-loader" src="<?php echo NGGPANOGALLERY_URLPATH ; ?>admin/images/loading.gif" style="display:none;" />
<div id="map_canvas" style="width: 570px; height: 390px;"></div>	
<table width="100%" align="center" style="border:1px solid #DADADA">

	<tr style="border-top:1px solid #DADADA">
            <td style="vertical-align:top;">
                <table width="100%">
                    <tr>
                        <td>
                            <label for="address"><?php _e('Enter an Address','nggpano') ?></label><input type="text" id="address" name="address" />
                        </td>
                    </tr>
                    <tr height="80px" >
                        <td style="vertical-align: top;">
                            <div id="geolocalised_address"><?php _e('Geolocalized Address','nggpano');?></div>
                        </td>
                    </tr>
                    <tr align="right">
                        <td align="center">
                            <div id="form-gps-pano-error" class="nggpano-error" style="display:none;"></div>
                                <input class="button-primary" type="submit" name="build" value="&nbsp;<?php esc_attr_e('Update', 'nggallery');?>&nbsp;"  />
                        </td>
                        <td class="">
                            
                        </td>
                    </tr>
                </table>
            </td>
            <td align="center" width="40%">
                <table>
                    <tr>
                        <td class="nggpano-gps-label"><?php _e('Latitude','nggpano');?></td>
                        <td>
                            <input type="text" id="picture_lat" name="picture_lat" style="width:95%;" value="<?php echo $lat; ?>" /><br/>
                        </td>
                    </tr>
                    <tr><td colspan="2"><small><?php _e('Recorded Latitude','nggpano') ?> : <?php echo $lat; ?></small></td></tr>
                    <tr>
                        <td class="nggpano-gps-label"><?php _e('Longitude','nggpano');?></td>
                        <td>
                            <input type="text" id="picture_lng" name="picture_lng" style="width:95%;" value="<?php echo $lng; ?>" />
                        </td>
                    </tr>
                    <tr><td colspan="2"><small><?php _e('Recorded Longitude','nggpano') ?> : <?php echo $lng; ?></small></td></tr>
                    <tr>
                        <td class="nggpano-gps-label"><?php _e('Altitude','nggpano');?></td>
                        <td>
                            <input type="text" id="picture_alt" name="picture_alt" style="width:95%;" value="<?php echo $alt; ?>" />
                            
                        </td>
                    </tr>
                    <tr><td colspan="2"><small><?php _e('Recorded Altitude','nggpano') ?> : <?php echo $alt; ?></small></td></tr>
                </table>
            </td>
	</tr>

</table>

</form>
<script type="text/javascript"> 
//<![CDATA[
jQuery(document).ready(function(){
    //initalize map
    jQuery('#map_canvas').gmap3(
    {
        action:'init',
        options:{
            center:[<?php echo $lat; ?>,<?php echo $lng; ?>],
            zoom: <?php echo $zoom; ?>,
            mapTypeId: google.maps.MapTypeId.TERRAIN
        }
    },
    {
        action: 'addMarker',
        latLng: [<?php echo $lat; ?>,<?php echo $lng; ?>],
        map:{
            center: true,
            zoom: <?php echo $zoom; ?>,
            mapTypeId: google.maps.MapTypeId.TERRAIN
        },
        marker:{
            options:{
                draggable:true
            },
            events:{
                dragend: function(marker){
                    jQuery(this).gmap3(
                        {
                            action:'getAddress',
                            latLng:marker.getPosition(),
                            callback:function(results){
                                var map = jQuery(this).gmap3('get'),
                                infowindow = jQuery(this).gmap3({action:'get', name:'infowindow'}),
                                content = results && results[1] ? results && results[1].formatted_address : 'no address';
                                var position = marker.getPosition();
                                content = content + "<br/>" + "lat : " + position.lat() + "<br/>" + "lng : " + position.lng();
                                if (infowindow){
                                    infowindow.open(map, marker);
                                    infowindow.setContent(content);
                                } else {
                                    jQuery(this).gmap3({action:'addinfowindow', anchor:marker, options:{content: content}});
                                }
                            }
                        },
                        {
                            action:'getElevation',
                            latLng:marker.getPosition(),
                            callback:function(results){
                                latLng = marker.getPosition();
                                content = results && results[0] ? results && results[0].elevation : 'no elevation';
                                jQuery('#picture_alt').val(Math.round(results[0].elevation));
                                jQuery('#picture_lat').val(latLng.lat());
                                jQuery('#picture_lng').val(latLng.lng());
                            }
                        }
                    );
                }
            }
        }
    }
    );
    
    //adress input
    jQuery('#address').autocomplete({
        source: function() {
            jQuery("#map_canvas").gmap3({
                action:'getAddress',
                address: jQuery(this).val(),
                callback:function(results){
                    if (!results) return;
                    jQuery('#address').autocomplete(
                        'display',
                        results,
                        false
                    );
                }
            });
        },
        cb:{
            cast: function(item){
                return item.formatted_address;
            },
            select: function(item) {
                marker = jQuery("#map_canvas").gmap3({action:'get', name:'marker'});
                var map = jQuery("#map_canvas").gmap3('get');
                var latLng = item.geometry.location;
                var address_display = "<?php _e('Geolocalized Address','nggpano');?>" + " :" + "<br/>" + item.formatted_address;
                jQuery("#geolocalised_address").html(address_display);

                map.setCenter(latLng);
                marker.setPosition(latLng);
                //trigger the dragend event
                google.maps.event.trigger(marker, 'dragend'); 
            }
        }
    });

});

//form submiting  
jQuery('#form-gps-pano').submit(function(e) {

    /* stop form from submitting normally */
    e.preventDefault();
    
    //get pid
    var pid = jQuery(this).children('input[name="pid"]').val();
    var url = jQuery(this).attr('action');

    jQuery('#nggpano-gps-loader').show();

    jQuery.ajax({
            url: url,
            data: jQuery(this).serialize(),
            dataType : 'json',
            type: 'POST',
            success: function(data, textStatus, XMLHttpRequest) {
                    if(data) {
                        if (data.error === false) {
                            //update input in gallery form
                            jQuery('#nggpano_picture_lat_' + data.pid).val(data.gps_data['latitude']);
                            jQuery('#nggpano_picture_lng_' + data.pid).val(data.gps_data['longitude']);
                            jQuery('#nggpano_picture_alt_' + data.pid).val(data.gps_data['altitude']);

                        }
                    }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                errormessage = '<?php esc_attr(_e('Problem in GPS data saving, please try again in a few moments.','nggpano') ); ?>';
                jQuery('#form-gps-pano-error').removeClass('success').addClass('error').text(errormessage).show(1000);
            },
            complete: function() {
                jQuery('#nggpano-gps-loader').hide().delay(2000);
                jQuery('#form-gps-pano').closest('.ui-dialog').dialog('destroy'); 
                jQuery('#nggpano-dialog').remove(); 

            }
    });
                        
    return false;
});
//]]> 
</script>