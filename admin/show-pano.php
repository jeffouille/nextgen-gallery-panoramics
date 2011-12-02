<?php
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);
// Load wp-config
if ( !defined('ABSPATH') ) 
	require_once( dirname(__FILE__) . '/../nggpano-config.php');


if ( !is_user_logged_in() )
	die(__('Cheatin&#8217; uh?'));
	
if ( !current_user_can('NGG Panoramics Manage gallery') ) 
	die(__('Cheatin&#8217; uh?'));



require_once(NGGPANOGALLERY_ABSPATH . '/lib/nggpanoPano.class.php');

//require_once(NGGPANOGALLERY_ABSPATH . '/lib/core.php');

// get the ngg Panoramic plugin options
$nggpano_options = get_option('nggpano_options');

//get the next Gen Gallery Options
$ngg_options = get_option('ngg_options');

// Some parameters from the URL
if ( !isset($_GET['pid']) )
    exit;
    
$pid = (int) $_GET['pid'];
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';

// let's get the image data
$picture  = nggdb::find_image( $pid );

if ( !is_object($picture) )
    exit;

//Get galleryid
$gid = $picture->galleryid;

//new pano from pictureid
$pano = new nggpanoPano($pid, $gid);
//show the pano in the correct div
$pano->show("panocontent");

?>