<?php 
/**
Template Page for the single map

Follow variables are useable :

	$image : Contain all about the image 
	$meta  : Contain the raw Meta data from the image 
	$exif  : Contain the clean up Exif data from file
	$iptc  : Contain the clean up IPTC data from file 
	$xmp   : Contain the clean up XMP data  from file
	$db    : Contain the clean up META data from the database (should be imported during upload)
	$pano  : Contain all about the pano 
        $gps   : Array with all gps data array( 'lat' => '', 'lng' => '', 'alt' => '')
        $mapinfos : Array with all infos about the map (zoom, matype ,div_id)
        $links : Array with all links to show data array( 'picture' => ['available' => 'true|false', 'url' =>''], 'map' => ['available' => 'true|false', 'url' =>''], 'pano' => ['available' => 'true|false', 'url' =>''])
        $mainlink : link for the thumbnail
        $captionmode : display caption or not
        $float : display item in left, center or right

Please note : A Image resize or watermarking operation will remove all meta information, exif will in this case loaded from database 

 You can check the content when you insert the tag <?php var_dump($variable) ?>
 If you would like to show the timestamp of the image ,you can use <?php echo $exif['created_timestamp'] ?>
**/
?>
<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php $infowindowcontent = '';
    if (!empty ($image)) :
        $infowindowcontent .= '<a '.$mainlink.' title="'.$image->linktitle.'">'.'\n\r';
        $infowindowcontent .= '<img class="'.$image->classname.'" src="'.$image->thumbnailURL.'" alt="'.$image->alttext.'" title="'.$image->alttext.'" />';
        $infowindowcontent .= '</a>';
    endif;
    if ($captionmode <> '') {
        //full|title|description
        if (!empty ($image->title) && ($captionmode == 'full' || $captionmode == 'title' )){
            $infowindowcontent .= '<span class="nggpano-title nggpano-'.$float.'">'.$image->alttext.'</span>';
        }
        if (!empty ($image->description) && ($captionmode == 'full' || $captionmode == 'description' )){
            $infowindowcontent .= '<span class="nggpano-description nggpano-'.$float.'">'.$image->description.'</span>';
        }
    }
    if (!empty ($image->caption)){
        $infowindowcontent .= '<span class="nggpano-caption nggpano-'.$float.'">'.$image->caption.'</span>';
    }
// if($pano_exist) :
$actions = array();
if ($links['picture']['available'])
    $actions['picture'] = '<a ' . $links['picture']['url'] . ' title="' . $image->linktitle . '">' . __('Zoom','nggpano') . '</a>';
if ($links['pano']['available'])
    $actions['pano']    = '<a ' . $links['pano']['url'] .' title="' . __('Panoramic view of ', 'nggpano')  . $image->linktitle . '">' . __('Panoramic', 'nggpano') . '</a>';
if ($links['map']['available'])
    $actions['map']     = '<a ' . $links['map']['url'] . ' title="' . __('Map','nggpano') . '">' . __('Map','nggpano') . '</a>';
$action_count = count($actions);
$i = 0;
$infowindowcontent .='<div class="nggpano-picture-actions' . $float . '">';
foreach ( $actions as $action => $link ) {
        ++$i;
        ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
        $infowindowcontent .='<span class="'.$action.'">'.$link.$sep.'</span>';
}
$infowindowcontent .= '</div>';

?>
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
                    {lat:<?php echo $gps["lat"] ?>, lng:<?php echo $gps["lng"] ?>, data:'<div class="map_infowindow"><?php echo $infowindowcontent ?></div>'}
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
                    }
                }
            }
        }
    );
    </script>


<div style="clear:both"></div>


<?php endif; ?>