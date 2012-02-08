<?php

require_once( dirname( dirname(__FILE__) ) . '/nggpano-config.php');
//require_once(NGGPANOGALLERY_ABSPATH . '/lib/nggpanoPano.class.php' );

if ( !is_user_logged_in() )
	die(__('Cheatin&#8217; uh?'));
	
if ( !current_user_can('NGG Panoramics Manage gallery') ) 
	die(__('Cheatin&#8217; uh?'));

global $wpdb;

$gid = (int) $_GET['gid'];

// let's get the gallery data
$gallery = nggdb::find_gallery($gid);

$gallery_values = nggpano_getGalleryOptions($gid);

// use defaults the first time
if($gallery_values) {
    $gps_region = isset($gallery_values->gps_region) ? $gallery_values->gps_region : '';
    $gps_region_array = unserialize($gps_region);
}
//var_dump($gps_region_array);
$action_filepath = NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=save-gps-region&gid=' . $gid;

$action_ajax_gpsregionextract_path = NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=extractgpsregion&gid=' . $gid;

$paged= isset($_GET['paged']) ? (int) $_GET['paged'] : '';

$page_admin = admin_url()."admin.php?page=nggallery-manage-gallery&mode=edit&gid=".$gid."&paged=".$paged;


$alt = 0;
$lat = '46.578498';
$lng ='2.457275';
$zoom = 5;
if($gps_region == '' || (isset($gps_region_array['sw']['lat']) && $gps_region_array['sw']['lat'] == '') ) {
$init_gps_region['sw']['lat'] = '42.18457289250683';
$init_gps_region['sw']['lng'] = '-6.2438968750000186';
$init_gps_region['ne']['lat'] = '51.344126998057234';
$init_gps_region['ne']['lng'] = '10.3388671875';
$act_gps_region['sw']['lat'] = '';
$act_gps_region['sw']['lng'] = '';
$act_gps_region['ne']['lat'] = '';
$act_gps_region['ne']['lng'] = '';
} else {
    $init_gps_region = $gps_region_array;
    $act_gps_region = $gps_region_array;
}

// get all images from gallery
    // get the pictures
    $picturelist = nggdb::get_gallery($gid);
?>

<form id="form-gps-region" method="POST" accept-charset="utf-8" action="<?php echo $action_filepath; ?>">
<?php wp_nonce_field('pick-gps-region') ?>
<input type="hidden" name="page" value="pick-gps-region" />
<input type="hidden" name="gid" value="<?php echo $gid; ?>" />
<input type="hidden" name="pageredirect" value="<?php echo $page_admin; ?>" />
<?php esc_html_e('Select the area for the region from the map.', 'nggpano'); ?><img class="nggpano-gps-loader" id="nggpano-gps-loader" src="<?php echo NGGPANOGALLERY_URLPATH ; ?>admin/images/loading.gif" style="display:none;" />
<div class="gps_region_tools">
  <div id="tools">
      <span id="show-photo"><?php _e('Show photos', 'nggpano'); ?></span>
      <span id="fit-photo" class="enabled"><?php _e('Fit region to photos', 'nggpano'); ?></span>
      <span id="toggle-region"><?php _e('Remove region', 'nggpano'); ?></span>
  </div>
</div>
<div id="map_canvas" style="width: 570px; height: 420px;"></div>	
<table width="100%" align="center" style="border:1px solid #DADADA">
	<tr style="border-top:1px solid #DADADA">
            <td style="vertical-align:top;">
                <label for="address"><?php _e('Enter an Address','nggpano') ?></label><input type="text" id="address" name="address" />
            </td>
        </tr>
</table>
<table width="100%" align="center" style="border:1px solid #DADADA">
        <tr>
            <td align="center" width="100%">
                <table width="100%">
                    <tr>
                        <td class="nggpano-gps-label"></td>
                        <td><?php _e('Latitude','nggpano');?></td>
                        <td><?php _e('Longitude','nggpano');?></td>
                    </tr>
                    <tr>
                        <td class="nggpano-gps-label"><?php _e('South West Corner','nggpano');?></td>
                        <td><input type="text" id="picture_sw_lat" name="picture_sw_lat" style="width:95%;" value="<?php echo $init_gps_region['sw']['lat']; ?>" /></td>
                        <td><input type="text" id="picture_sw_lng" name="picture_sw_lng" style="width:95%;" value="<?php echo $init_gps_region['sw']['lng'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="nggpano-gps-label"></td>
                        <td colspan="2"><small><?php _e('Recorded Coordinates','nggpano') ?> : <?php echo $act_gps_region['sw']['lat']; ?> - <?php echo $act_gps_region['sw']['lng']; ?></small></td>
                    </tr>
                    <tr>
                        <td class="nggpano-gps-label"><?php _e('North East Corner','nggpano');?></td>
                        <td><input type="text" id="picture_ne_lat" name="picture_ne_lat" style="width:95%;" value="<?php echo $init_gps_region['ne']['lat'] ?>" /></td>
                        <td><input type="text" id="picture_ne_lng" name="picture_ne_lng" style="width:95%;" value="<?php echo $init_gps_region['ne']['lng'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="nggpano-gps-label"></td>
                        <td colspan="2"><small><?php _e('Recorded Coordinates','nggpano') ?> :<?php echo $act_gps_region['ne']['lat']; ?> - <?php echo $act_gps_region['ne']['lng']; ?></small></td>
                    </tr>
                </table>
            </td>
	</tr>
        <tr align="right">
            <td align="right">
                <div id="form-gps-region-error" class="nggpano-error" style="display:none;"></div>
                    <input class="button-primary" type="submit" name="build" value="&nbsp;<?php esc_attr_e('Update', 'nggallery');?>&nbsp;"  />
            </td>
        </tr>
</table>

</form>
<script type="text/javascript"> 
    var pictureList = [
        <?php if ( is_array($picturelist) ) {
             foreach ($picturelist as $picture) {
                 $image_values = nggpano_getImagePanoramicOptions($picture->pid);
                 $picture_title = html_entity_decode( nggGallery::i18n($picture->alttext, 'pic_' . $picture->pid . '_alttext') );
                if($image_values) {
                    if(isset($image_values->gps_lat) && $image_values->gps_lng) {
                        echo '{lat:'.$image_values->gps_lat.',lng:'.$image_values->gps_lng.',data:{picto:true,name:"'.$picture_title.'"}},';
                    }

                }

             }         
        }
        //$out = nggCreateImageBrowser($picturelist, $template);
        
        ?>
    ];
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
        action: 'addRectangle',
        rectangle:{
            options:{
                bounds: new google.maps.LatLngBounds(
                    new google.maps.LatLng(<?php echo $init_gps_region['sw']['lat'] ?>, <?php echo $init_gps_region['sw']['lng'] ?>),
                    new google.maps.LatLng(<?php echo $init_gps_region['ne']['lat'] ?>, <?php echo $init_gps_region['ne']['lng'] ?>)          
                ),
                fillColor : "#008BB2",
                strokeColor : "#005BB7",
                clickable:true,
                editable:true
            },
            events:{
                bounds_changed: function(rectangle){
                    new_bounds = rectangle.getBounds()
                    jQuery("input#picture_ne_lat").val( new_bounds.getNorthEast().lat() );
                    jQuery("input#picture_ne_lng").val( new_bounds.getNorthEast().lng() );
                    jQuery("input#picture_sw_lat").val( new_bounds.getSouthWest().lat() );
                    jQuery("input#picture_sw_lng").val( new_bounds.getSouthWest().lng() );
                }
            }
        },
        map:{
            center: true//,
            //zoom:12
        }
    },
    
    {
        action: 'addMarkers',
        radius:100,
        markers: pictureList,
       /* clusters:{
            // This style will be used for clusters with more than 0 markers
            0: {
            content: '<div class="cluster cluster-1">CLUSTER_COUNT</div>',
            width: 18,
            height: 52
            },
            // This style will be used for clusters with more than 20 markers
            20: { //20
            content: '<div class="cluster cluster-2">CLUSTER_COUNT</div>',
            width: 23,
            height: 55
            },
            // This style will be used for clusters with more than 50 markers
            50: { //50
            content: '<div class="cluster cluster-3">CLUSTER_COUNT</div>',
            width: 27,
            height: 65
            }
        },*/
        marker: {
            options: {
                icon: new google.maps.MarkerImage('<?php echo NGGPANOGALLERY_URLPATH ?>admin/css/images/marker-blue-pin.png')
            },
            events:{ 
                mouseover: function(marker, event, data){
                    jQuery(this).gmap3(
                        { action:'clear', name:'overlay'},
                        { action:'addOverlay',
                        latLng: marker.getPosition(),
                        content:  '<div class="infobulle'+(data.picto ? ' picto' : '')+'">' +
                        '<div class="bg"></div>' +
                        '<div class="text">' + data.name + '</div>' +
                        '</div>' +
                        '<div class="arrow"></div>',
                        offset: {
                            x:-46,
                            y:-73
                            }
                        }
                    );
                },
                mouseout: function(){
                    jQuery(this).gmap3({action:'clear', name:'overlay'});
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
               // marker = jQuery("#map_canvas").gmap3({action:'get', name:'marker'});
                var map = jQuery("#map_canvas").gmap3('get');
                var latLng = item.geometry.location;
                
                map.setCenter(latLng);
               // marker.setPosition(latLng);
                //trigger the dragend event
               // google.maps.event.trigger(marker, 'dragend'); 
            }
        }
    });
    
   
    //action to show or not markers
    jQuery('#show-photo').click(function(){
        checked = jQuery(this).hasClass('checked');
        map = jQuery("#map_canvas").gmap3('get');
        toggle_markers(map, !checked);
        jQuery(this).toggleClass("checked");

    });
    
    //action to fit rectangle to marker
    jQuery('#fit-photo').click(function(){
        if(!jQuery('#show-photo').hasClass('checked')) {
        jQuery('#show-photo').click();
        }
        enabled = jQuery(this).hasClass('enabled');
        if(enabled) {
            //  Create a new viewpoint bound
            var bounds = new google.maps.LatLngBounds ();
            //get all markers
            markers = jQuery("#map_canvas").gmap3({
                action:'get',
                name:'marker',
                all: true
            });

            jQuery.each(markers, function(i, marker){
                bounds.extend(marker.getPosition());
            });
            rectangle = jQuery("#map_canvas").gmap3({action:'get', name:'rectangle'});
            rectangle.setBounds(bounds);
            map = jQuery("#map_canvas").gmap3('get');
            map.setCenter(bounds.getCenter());
            map.fitBounds(bounds);
        }
    });
    
       
    //action remove region
    jQuery('#toggle-region').click(function(){
        checked = jQuery(this).hasClass('checked'),
        map = jQuery("#map_canvas").gmap3('get');
        if(!checked) {
            jQuery("#map_canvas").gmap3({action:'clear', name:'rectangle'});
            jQuery(this).html("<?php _e('Add region', 'nggpano'); ?>");
            jQuery("input#picture_ne_lat").val( '' );
            jQuery("input#picture_ne_lng").val( '' );
            jQuery("input#picture_sw_lat").val( '' );
            jQuery("input#picture_sw_lng").val( '' );
            jQuery('#fit-photo').removeClass('enabled');
            
        } else {
            jQuery("#map_canvas").gmap3({
                    action: 'addRectangle',
                    rectangle:{
                        options:{
                            bounds: new google.maps.LatLngBounds(
                                new google.maps.LatLng(<?php echo $init_gps_region['sw']['lat'] ?>, <?php echo $init_gps_region['sw']['lng'] ?>),
                                new google.maps.LatLng(<?php echo $init_gps_region['ne']['lat'] ?>, <?php echo $init_gps_region['ne']['lng'] ?>)          
                            ),
                            fillColor : "#008BB2",
                            strokeColor : "#005BB7",
                            clickable:true,
                            editable:true
                        },
                        events:{
                            bounds_changed: function(rectangle){
                                new_bounds = rectangle.getBounds()
                                jQuery("input#picture_ne_lat").val( new_bounds.getNorthEast().lat() );
                                jQuery("input#picture_ne_lng").val( new_bounds.getNorthEast().lng() );
                                jQuery("input#picture_sw_lat").val( new_bounds.getSouthWest().lat() );
                                jQuery("input#picture_sw_lng").val( new_bounds.getSouthWest().lng() );
                            }
                        }
                    },
                    map:{
                        center: true//,
                        //zoom:12
                    }
                })
            jQuery(this).html("<?php _e('Remove region', 'nggpano'); ?>");
            var rectangle = jQuery("#map_canvas").gmap3({action:'get', name:'rectangle'});
            google.maps.event.trigger(rectangle, 'bounds_changed'); 
            jQuery('#fit-photo').addClass('enabled');
                                
        }
        

        jQuery(this).toggleClass("checked");

    });
    
    
    //After map loaded
        //Fit map to rectangle bound
        var map = jQuery("#map_canvas").gmap3('get');
        var rectangle = jQuery("#map_canvas").gmap3({action:'get', name:'rectangle'});
        var bounds= rectangle.getBounds();
        map.setCenter(bounds.getCenter());
        map.fitBounds(bounds);
        //Hide markers
        toggle_markers(map, false);
        jQuery('#show-photo').removeClass("checked");
    
});

function toggle_markers(map, checked) {
        markers = jQuery("#map_canvas").gmap3({
            action:'get',
            name:'marker',
            all: true
        });

        jQuery.each(markers, function(i, marker){
            marker.setMap( checked ? map : null);
        });
}

//form submiting  
jQuery('#form-gps-region').submit(function(e) {

    // stop form from submitting normally
    e.preventDefault();
    
    //get pid
    var pid = jQuery(this).children('input[name="pid"]').val();
    var url = jQuery(this).attr('action');

    jQuery('#nggpano-gps-loader').show();

    jQuery.ajax({
            url: url,
            data: jQuery(this).serialize(),
            //dataType : 'json',
            type: 'POST',
            success: function(data, textStatus, XMLHttpRequest) {
                    if(data) {
                        jQuery('#form-gps-region-error').removeClass('success').addClass('error').text(data);
                        if (data.error === false) {
                            //update input in gallery form
//                            jQuery('#nggpano_picture_lat_' + data.pid).val(data.gps_data['latitude']);
//                            jQuery('#nggpano_picture_lng_' + data.pid).val(data.gps_data['longitude']);
//                            jQuery('#nggpano_picture_alt_' + data.pid).val(data.gps_data['altitude']);

                        }
                    }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                errormessage = '<?php esc_attr(_e('Problem in GPS data saving, please try again in a few moments.','nggpano') ); ?>';
                jQuery('#form-gps-region-error').removeClass('success').addClass('error').text(errormessage).show(1000);
            },
            complete: function() {
                var map = jQuery("#map_canvas").gmap3('get');
                var rectangle = jQuery("#map_canvas").gmap3({action:'get', name:'rectangle'});
                var map2 = jQuery("#map_gps_region").gmap3('get');
                if(map2) {
                    jQuery("#map_gps_region").gmap3({action:'clear', name:'rectangle'});
                    if(rectangle) {
                    //jQuery("#map_gps_region").gmap3({action:'addRectangle', rectangle:rectangle});
                    jQuery("#map_gps_region").gmap3({
                                            action:'addRectangle',
                                            rectangle:{
                                                options:{
                                                    bounds:rectangle.getBounds(),
                                                    editable:false,
                                                    fillColor : "#008BB2",
                                                    strokeColor : "#005BB7"
                                                }
                                            }
                                        });

                    //map2.setZoom(15);
                    var bounds= rectangle.getBounds();
                    map2.setCenter(bounds.getCenter());
                    map2.fitBounds(bounds);
                    }
                }
                jQuery('#nggpano-gps-loader').hide().delay(2000);
                jQuery('#form-gps-region').closest('.ui-dialog').dialog('destroy'); 
                jQuery('#nggpano-dialog').remove(); 
            }
    });
                       
    return false;
});
//]]> 
</script>