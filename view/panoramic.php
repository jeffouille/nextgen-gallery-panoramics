<?php 
/**
Template Page for the panoramic

Follow variables are useable :

        $panodiv : Array with config for div with panoramic(s)
            panodiv : array (
                ‘classname’ => ‘nggpano-panoramic nggpano-center’,
                ‘contentdiv’ => ‘panocontent_800318051′,
                ‘swfid’ => ‘krpanoSWFObject_917959209′,
                ‘krpano_path’ => ‘http://domain.com/wp-content/plugins/nextgen-gallery-panoramics/krpano/krpano.swf’,
                ‘krpano_xml’ => ‘http://domain.com/wp-content/plugins/nextgen-gallery-panoramics/xml/krpano.php?pano=multiple_245-227′,
                ‘size’ =>
                    array (
                        ‘width’ => ’500px’,
                        ‘height’ => ’400px’,
                    ),
                ) 
        $pano_list   : Array with all panos
            pano_list : array (
                245 => object pano,
                247 => object pano
            );
        $captionmode : - caption display or not the caption full|none|title|description
        $mapdiv : Array with all infos about the map (zoom, matype, width, height, list)
            mapdiv : array (
                'available' => true or false
                ‘width’ => ’250px’,
                ‘height’ => ’250px’,
                ‘zoom’ => 10,
                ‘classname’ => ‘nggpano-map nggpano-map-center’,
                ‘maptype’ => ‘HYBRID’,
                ‘div_id’ => ‘map_pic_1227869611′,
                ‘thumbinfowindowurl’ => ‘http://domaine.com/index.php?callback=image&pid=245,227,247,517&width=200′,
                ‘map_list’ =>
                    array (
                        245 =>
                            array (
                                ‘lat’ => ’48.86200332641602′,
                                ‘lng’ => ’2.32489275932312′,
                                ‘alt’ => ’29′,
                            ),
                        227 =>
                            array (
                                ‘lat’ => ’52.96222686767578′,
                                ‘lng’ => ‘-9.43039321899414′,
                                ‘alt’ => ’138′,
                            ),
                    ),
            ) 
        $float : display item in left, center or right
        $caption : caption in shortcode


 You can check the content when you insert the tag <?php var_dump($variable) ?>
**/
?>
<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<!-- PANORAMIC -->
<?php if (sizeof($pano_list)>0) : ?>

    <?php if (!empty ($panodiv)) : ?>

        <div id="<?php echo $panodiv['contentdiv'] ?>" class="<?php echo $panodiv['classname'] ?>" style="width:<?php echo $panodiv['size']['width'] ?>; height:<?php echo $panodiv['size']['height'] ?>;">...Loading Panoramic...</div>
        <?php if ($captionmode <> '') : ?>
            <?php if(sizeof($pano_list) == 1) : //Caption détail for only one panoramic ?>
                <?php foreach ($pano_list as $pano_id => $pano) { ?>
                    <?php if (!empty ($pano->title) && ($captionmode == 'full' || $captionmode == 'title' )) : ?><span class="nggpano-title nggpano-<?php echo $float; ?>"><?php echo $pano->title ?></span><?php endif; ?>
                    <?php if (!empty ($pano->description) && ($captionmode == 'full' || $captionmode == 'description' )) : ?><span class="nggpano-description nggpano-<?php echo $float; ?>"><?php echo $pano->description ?></span><?php endif; ?>
                <?php } ?>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!empty ($caption)) : ?><span class="nggpano-caption nggpano-<?php echo $float; ?>"><?php echo $caption ?></span><?php endif; ?>

        <script type="text/javascript">
            var viewer = createPanoViewer({swf:"<?php echo $panodiv['krpano_path'] ?>", wmode:"opaque", id:"<?php echo $panodiv['swfid'] ?>"});
            viewer.addVariable("xml", "<?php echo $panodiv['krpano_xml'] ?>");
            //viewer.addParam("class", "krpanoSWFObject");
            viewer.embed("<?php echo $panodiv['contentdiv'] ?>");
        </script>

    <?php endif; ?>

<?php endif; ?>   