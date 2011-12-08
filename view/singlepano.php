<?php 
/**
Template Page for the single pano

Follow variables are useable :

	$image : Contain all about the image 
	$meta  : Contain the raw Meta data from the image 
	$exif  : Contain the clean up Exif data from file
	$iptc  : Contain the clean up IPTC data from file 
	$xmp   : Contain the clean up XMP data  from file
	$db    : Contain the clean up META data from the database (should be imported during upload)

Please note : A Image resize or watermarking operation will remove all meta information, exif will in this case loaded from database 

 You can check the content when you insert the tag <?php var_dump($variable) ?>
 If you would like to show the timestamp of the image ,you can use <?php echo $exif['created_timestamp'] ?>
**/
?>
<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php if (!empty ($pano)) : ?>
<?php 
            $krpano_path    = trailingslashit($pano->krpanoFolderURL) . $pano->krpanoSWF;
            $krpano_xml     = NGGPANOGALLERY_URLPATH . 'xml/krpano.php?pano=single_'.$pano->pid;
?>

<div id="<?php echo $pano->contentdiv ?>" class="<?php echo $pano->classname ?>" style="width:<?php echo $panosize['width'] ?>; height:<?php echo $panosize['height'] ?>;">...Loading Panoramic...</div>
<?php if ($mode == 'caption') : ?>
<?php if (!empty ($pano->title)) : ?><span class="nggpano-title"><?php echo $pano->title ?></span><?php endif; ?>
<?php if (!empty ($pano->caption)) : ?><span class="nggpano-caption"><?php echo $pano->caption ?></span><?php endif; ?>
<?php if (!empty ($pano->description)) : ?><span class="nggpano-description"><?php echo $pano->description ?></span><?php endif; ?>
<?php endif; ?> 


<script>
    var viewer = createPanoViewer({swf:"<?php echo $krpano_path ?>", wmode:"opaque"});
    viewer.addVariable("xml", "<?php echo $krpano_xml ?>");
    viewer.embed("<?php echo $pano->contentdiv ?>");
</script>

<?php endif; ?>   