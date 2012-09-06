<?php
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);
// Load wp-config
if ( !defined('ABSPATH') ) 
	require_once( dirname(__FILE__) . '/nggpano-config.php');

//include_once('lib/core.php');

// Some parameters from the URL
if ( !isset($_GET['pid']) )
    exit;
    
$pid = (int) $_GET['pid'];


//mapType
$maptype = $_GET['maptype'];
// clean maptype if needed 
//$maptype = ( preg_match('/(HYBRID|ROADMAP|SATELLITE|TERRAIN)/i', strtoupper($maptype)) ) ? strtoupper($maptype) : 'HYBRID';

//Zoom Level
$zoom = (isset($_GET['mapzoom']) && strlen($_GET['mapzoom']) > 0) ? $_GET['mapzoom'] : '14';

// let's get the image data
$picture  = nggdb::find_image( $pid );

if ( !is_object($picture) )
    exit;

//thumb
$thumbinfowindow = trailingslashit( home_url() ) . 'index.php?callback=image&amp;pid=' . $pid . '&amp;width=200';

//Get galleryid
$gid = $picture->galleryid;

//Get GPS values for the current image
$image_values = nggpano_getImagePanoramicOptions($pid);
$lat = isset($image_values->gps_lat) ? $image_values->gps_lat : '';
$lng = isset($image_values->gps_lng) ? $image_values->gps_lng : '';
$alt = isset($image_values->gps_alt) ? $image_values->gps_alt : '';

$mapavailable = false;
    if((isset($lat) && strlen($lat) > 0) && (isset($lng) && strlen($lng) > 0))
        $mapavailable = true;

if ($mapavailable) :
?>
<input type="hidden" id="maplat" value="<?php echo $lat ?>" />
<input type="hidden" id="maplng" value="<?php echo $lng ?>" />
<input type="hidden" id="mapzoom" value="<?php echo $zoom ?>" />
<input type="hidden" id="maptype" value="<?php echo $maptype ?>" />
<input type="hidden" id="thumbinfowindow" value="<?php echo $thumbinfowindow ?>" />
<input type="hidden" id="picturetitle" value="<?php echo $picture->alttext ?>" />
<input type="hidden" id="iconpath" value="<?php echo NGGPANOGALLERY_URLPATH .'images/icons/gpsmapicons01.png' ?>" />
<div id="map_canvas" class="map_canvas" style="width:100%;height:350px;"></div>

        <script type="text/javascript">
    initializeMap = function() {
        jQuery('#map_canvas').gmap3(
            {   action:'init',
                options:{
                    center:[<?php echo $lat ?>,<?php echo $lng ?>],
                    zoom: <?php echo $zoom ?>,
                    mapTypeId : google.maps.MapTypeId.<?php echo $maptype ?>
                }
            },
            { action: 'addMarkers',

                markers:[
                        {lat:<?php echo $lat ?>, lng:<?php echo $lng ?>, data:'<div class="map_infowindow"><span class="thumb"><img src="<?php echo $thumbinfowindow ?>" /></span><span class="title"><?php echo $picture->alttext ; ?></span></div>'}
                    ],
                marker:{
                    options:{
                        draggable: false,
                        icon:new google.maps.MarkerImage("<?php echo NGGPANOGALLERY_URLPATH ?>images/icons/gpsmapicons01.png", new google.maps.Size(32, 32), new google.maps.Point((0), (0)),new google.maps.Point(16, 32))
                    },
                    events:{
                        click: function(marker, event, data){
                            jQuery(this).gmap3({action : 'clear', name : 'infowindow'});
                            var map = jQuery(this).gmap3('get'),
                            infowindow = jQuery(this).gmap3({action:'get', name:'infowindow'});
                            if (infowindow){
                            infowindow.open(map, marker);
                            infowindow.setContent(data);
                            } else {
                            jQuery(this).gmap3({action:'addinfowindow', anchor:marker, options:{content: data}});
                            }
                        }/*,
                        mouseout: function(){
                            var infowindow = jQuery(this).gmap3({action:'get', name:'infowindow'});
                            if (infowindow){
                            infowindow.close();
                            }
                        }*/
                    }
                }
            }
        );
    }
</script>

        
<?php endif; ?>
