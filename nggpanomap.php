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
$maptype = $_GET['type'];
// clean maptype if needed 
$maptype = ( preg_match('/(HYBRID|ROADMAP|SATELLITE|TERRAIN)/i', strtoupper($maptype)) ) ? strtoupper($maptype) : 'HYBRID';

//Zoom Level
$zoom = (isset($_GET['zoom']) && strlen($_GET['zoom']) > 0) ? $_GET['zoom'] : '14';

//Init onOpen
$init = (isset($_GET['init']) && $_GET['init'] =="true") ? true : false;

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

<div id="map_canvas" class="map_canvas" style="width:100%;height:100%;"></div>

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
    <?php if ($init) { ?>
      initializeMap();
    <?php } ?>
</script>

        
<?php endif; ?>
