<?php 
/**
Template Page for the single pano

Follow variables are useable :

	$pano : Contain all about the pano 
        $gps   : Array with all gps data array( 'lat' => '', 'lng' => '', 'alt' => '')
        $mapinfos : Array with all infos about the map (zoom, matype, width, height)
        $panosize : Array size of the pano
        $mode : display caption or not

 You can check the content when you insert the tag <?php var_dump($variable) ?>
 If you would like to show the timestamp of the image ,you can use <?php echo $exif['created_timestamp'] ?>
**/
?>
<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php if (!empty ($pano)) : ?>

<div id="<?php echo $pano->contentdiv ?>" class="<?php echo $pano->classname ?>" style="width:<?php echo $panosize['width'] ?>; height:<?php echo $panosize['height'] ?>;">...Loading Panoramic...</div>
<?php if ($mode == 'caption') : ?>
<?php if (!empty ($pano->title)) : ?><span class="nggpano-title"><?php echo $pano->title ?></span><?php endif; ?>
<?php if (!empty ($pano->caption)) : ?><span class="nggpano-caption"><?php echo $pano->caption ?></span><?php endif; ?>
<?php if (!empty ($pano->description)) : ?><span class="nggpano-description"><?php echo $pano->description ?></span><?php endif; ?>
<?php endif; ?> 


<script type="text/javascript">
    var viewer = createPanoViewer({swf:"<?php echo $pano->krpano_path ?>", wmode:"opaque", id:"<?php echo $pano->swfid ?>"});
    viewer.addVariable("xml", "<?php echo $pano->krpano_xml ?>");
    viewer.embed("<?php echo $pano->contentdiv ?>");
</script>

<?php endif; ?>   