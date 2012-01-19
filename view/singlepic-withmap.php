<?php 
/**
Template Page for the single pic with map

Follow variables are useable :

	$image : Contain all about the image 
	$meta  : Contain the raw Meta data from the image 
	$exif  : Contain the clean up Exif data from file
	$iptc  : Contain the clean up IPTC data from file 
	$xmp   : Contain the clean up XMP data  from file
	$db    : Contain the clean up META data from the database (should be imported during upload)
        $gps   : Array with all gps data array( 'lat' => '', 'lng' => '', 'alt' => '')
        $mapinfos : Array with all infos about the map (zoom, matype, width, height, div_id)
        $captionmode : - caption display or not the caption full|none|title|description

Please note : A Image resize or watermarking operation will remove all meta information, exif will in this case loaded from database 

 You can check the content when you insert the tag <?php var_dump($variable) ?>
 If you would like to show the timestamp of the image ,you can use <?php echo $exif['created_timestamp'] ?>
**/
?>
<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><?php if (!empty ($image)) : ?>

<a href="<?php echo $image->imageURL ?>" title="<?php echo $image->linktitle ?>" <?php echo $image->thumbcode ?> >
	<img class="<?php echo $image->classname ?>" src="<?php echo $image->thumbnailURL ?>" alt="<?php echo $image->alttext ?>" title="<?php echo $image->alttext ?>" />
</a>

<?php if ($captionmode <> '') : ?>
<?php if (!empty ($image->title) && ($captionmode == 'full' || $captionmode == 'title' )) : ?><span class="nggpano-title nggpano-<?php echo $float; ?>"><?php echo $image->title ?></span><?php endif; ?>
<?php if (!empty ($image->description) && ($captionmode == 'full' || $captionmode == 'description' )) : ?><span class="nggpano-description nggpano-<?php echo $float; ?>"><?php echo $image->description ?></span><?php endif; ?>
<?php endif; ?>
<?php if (!empty ($image->caption)) : ?><span class="nggpano-caption nggpano-<?php echo $float; ?>"><?php echo $image->caption ?></span><?php endif; ?>

    <?php
    if(is_array($gps) && (isset($gps["lat"]) && strlen($gps["lat"]) > 0) && (isset($gps["lng"]) && strlen($gps["lng"]) > 0) ) : ?>
        <div id="<?php echo $mapinfos['div_id'] ?>" class="<?php echo $mapinfos['classname'] ?>" style="width:<?php echo $mapinfos['width'] ?>; height:<?php echo $mapinfos['height'] ?>;"></div>


        <script type="text/javascript">
        jQuery('#<?php echo $mapinfos['div_id'] ?>').gmap3(
            {   action:'init',
                options:{
                    center:[<?php echo $gps["lat"] ?>,<?php echo $gps["lng"] ?>],
                    zoom: <?php echo $mapinfos['zoom'] ?>,
                    mapTypeId : google.maps.MapTypeId.<?php echo $mapinfos['maptype'] ?>
                }
            },
            { action: 'addMarkers',
                markers:[
                        {lat:<?php echo $gps["lat"] ?>, lng:<?php echo $gps["lng"] ?>, data:'<div class="map_infowindow"><span class="thumb"><img src="<?php echo $mapinfos['thumbinfowindow'] ?>" /></span><span class="title"><?php echo $image->alttext ; ?></span></div>'}
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
        </script>

    <?php endif; ?>
<?php endif; ?>