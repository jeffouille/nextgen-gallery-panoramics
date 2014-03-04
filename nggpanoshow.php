<?php
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);
// Load wp-config
if ( !defined('ABSPATH') ) 
	require_once( dirname(__FILE__) . '/nggpano-config.php');
// reference nggpanoPano class
include_once('lib/nggpanoPano.class.php');

//include_once('lib/core.php');

// Some parameters from the URL
if ( !isset($_GET['pid']) )
    exit;
    
$pid = (int) $_GET['pid'];
// let's get the image data
$picture  = nggdb::find_image( $pid );

if ( !is_object($picture) )
    exit;

//Get galleryid
$gid = $picture->galleryid;

//new pano from pictureid
$pano = new nggpanoPano($pid, $gid);
$pano->loadFromDB();
//show the pano in the correct div
//$pano->show("panocontent");
$krpano_path    = trailingslashit($pano->krpanoFolderURL) . $pano->krpanoSWF;
$krpano_xml     = NGGPANOGALLERY_URLPATH . 'xml/krpano.php?pano=single_'.$pano->pid;

?>
<input type="hidden" id="krpano_path" value="<?php echo $krpano_path ?>" />
<input type="hidden" id="krpano_xml" value="<?php echo $krpano_xml ?>" />
<div id="panocontent" style="width:100%;height:500px;"></div>

<script type="text/javascript">
function initializePano() {
 var viewer = createPanoViewer({swf:"<?php echo $krpano_path ?>", wmode:"opaque"});
 viewer.addVariable("xml", "<?php echo $krpano_xml ?>");
 viewer.useHTML5("auto");
 viewer.embed("panocontent");
}
initializePano();

/*embedpano({swf:"<?php echo $krpano_path ?>", xml:"<?php echo $krpano_xml ?>", target:"panocontent", html5:"prefer",consolelog:true});*/

</script>
