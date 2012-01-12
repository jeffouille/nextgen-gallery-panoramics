<?php 
/**
Template Page for the single pic with link to map, pano, image in a lightbox (prettyPhoto)

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
<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><?php if (!empty ($image)) : ?>
<a <?php echo $mainlink ?> title="<?php echo $image->linktitle ?>" <?php //echo $image->thumbcode ?> >
	<img class="<?php echo $image->classname ?>" src="<?php echo $image->thumbnailURL ?>" alt="<?php echo $image->alttext ?>" title="<?php echo $image->alttext ?>" />
</a>

<?php endif; ?>
<?php if ($captionmode == 'caption') : ?>
<?php if (!empty ($pano->title)) : ?><span class="nggpano-title<?php echo $float; ?>"><?php echo $pano->title ?></span><?php endif; ?>
<?php if (!empty ($pano->caption)) : ?><span class="nggpano-caption<?php echo $float; ?>"><?php echo $pano->caption ?></span><?php endif; ?>
<?php if (!empty ($pano->description)) : ?><span class="nggpano-description<?php echo $float; ?>"><?php echo $pano->description ?></span><?php endif; ?>
<?php endif; ?> 
<?php
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
echo '<div class="nggpano-picture-actions' . $float . '">';
foreach ( $actions as $action => $link ) {
        ++$i;
        ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
        echo "<span class='$action'>$link$sep</span>";
}
echo '</div>';

?>


        <div style="clear:both"></div>