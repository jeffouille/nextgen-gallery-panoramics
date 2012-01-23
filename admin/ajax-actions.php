<?php
//TODO Avoid direct call !!!
//if(preg_match("#".basename(__FILE__)."#", $_SERVER["PHP_SELF"])) {die("You are not allowed to call this page directly.");}
//or with this :
// check if we have all needed parameter
//if ( !defined('ABSPATH') || (!isset($_GET['galleryid']) || !is_numeric($_GET['galleryid'])) || (!isset($_GET['p']) || !is_numeric($_GET['p'])) || !isset($_GET['type'])){
//    // if it's not ajax request, back to main page
//    if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')
//        header('Location: http://'. $_SERVER['HTTP_HOST']);
//    die();    
//}
// OR WITH check referer

//ini_set('display_errors', '1');
//ini_set('error_reporting', E_ALL);
// look up for the path
require_once( dirname( dirname(__FILE__) ) . '/nggpano-config.php');
require_once(NGGPANOGALLERY_ABSPATH . '/lib/nggpanoEXIF.class.php');
require_once(NGGPANOGALLERY_ABSPATH . '/lib/functions.php');
//require_once(NGGPANOGALLERY_ABSPATH . '/admin/ngg-extend.php');

require_once(NGGPANOGALLERY_ABSPATH . '/lib/nggpanoPano.class.php');

//ini_set('error_reporting', E_ALL);
require_once (dirname (__FILE__) . '/nggpanoAdmin.class.php');
if (isset ( $_GET['mode']) ) {
    $mode = $_GET['mode'];
    
    switch ($mode) {
        case 'extractgps':
            if (isset ( $_GET['id']) ) {
                nggpano_extractgps_and_save($_GET['id']);
            }

            break;
        case 'save-gps':
            check_admin_referer('pick-gps');
            if($_POST['pid']) {
                $pid = $_POST['pid'];
                $gps_array = array();
                //Get paramaters
                $lat    = isset ($_POST['picture_lat']) ? $_POST['picture_lat'] : '';
                $lng    = isset ($_POST['picture_lng']) ? $_POST['picture_lng'] : '';
                $alt    = isset ($_POST['picture_alt']) ? $_POST['picture_alt'] : '';
                $gps_array["latitude"]  = $lat;
                $gps_array["longitude"] = $lng;
                $gps_array["altitude"]  = $alt;
                
                //Save gps values
                nggpano_savegps($pid, $gps_array); 
            }
            break;
            
            
        case 'extractfov':
            if (isset ( $_GET['id']) ) {
                nggpano_extractfov($_GET['id']);
            }

            break;
        case 'build-pano':
            check_admin_referer('build-pano');
            if($_POST['pid']) {
                $pid = $_POST['pid'];
                $gid = $_POST['gid'];
                //Get paramaters
                $hfov       = isset ($_POST['hfov']) ? $_POST['hfov'] : '';
                $vfov       = isset ($_POST['vfov']) ? $_POST['vfov'] : '';
                $voffset    = isset ($_POST['voffset']) ? $_POST['voffset'] : '';
                
                //Create pano
                nggpanoAdmin::build_pano($pid, $gid, $hfov, $vfov, $voffset);          
            }
            
        case 'edit-pano':
            check_admin_referer('edit-pano');
            if($_POST['pid']) {
                $pid = $_POST['pid'];
                $gid = $_POST['gid'];
                //Get paramaters
                $hfov       = isset ($_POST['hfov']) ? $_POST['hfov'] : '';
                $vfov       = isset ($_POST['vfov']) ? $_POST['vfov'] : '';
                $voffset    = isset ($_POST['voffset']) ? $_POST['voffset'] : '';
                $xml_configuration = isset ($_POST['xml_configuration']) ? $_POST['xml_configuration'] : '';
                $is_partial = isset ($_POST['is_partial']) ? $_POST['is_partial'] : '0';
                
                //Save pano values
  		if(! class_exists('nggpanoPano'))
                    require_once(NGGPANOGALLERY_ABSPATH . '/lib/nggpanoPano.class.php' );              
                //Create pano
                $pano = new nggpanoPano($pid, $gid);
                $pano->loadFromDB();
                $pano->setHFov($hfov);
                $pano->setVFov($vfov);
                $pano->setVOffset($voffset);
                $pano->setXmlConfiguration($xml_configuration);
                $pano->setIsPartial($is_partial);
                
                $pano->save();
                $result = array();
                $message = ''; 
                if($pano->error) {
                        $result['error']    = true;
                        $result['message']  = $pano->errmsg;
                } else {
                        $result['error']    = false;
                        $result['message']  = $pano->errmsg;
                }
                
                echo json_encode($result);
                //nggpano_savegps($pid, $gps_array); 
            }
            break;

            break;
        case 'delete-pano':
            if (isset ( $_GET['id']) && isset ( $_GET['gid'])) {
                nggpano_delete_pano($_GET['id'],$_GET['gid']);
            }
            break;
        case 'resize-preview-pano':
            check_admin_referer('resize-preview-pano');
            if($_POST['pid']) {
                $pid = $_POST['pid'];
                $gid = $_POST['gid'];
                //Get paramaters
                $imgWidth       = isset ($_POST['imgWidth']) ? $_POST['imgWidth'] : '0';
                $imgHeight      = isset ($_POST['imgHeight']) ? $_POST['imgHeight'] : '0';
                $backup    = isset ($_POST['backup']) ? $_POST['backup'] : false;
                
                //Create pano
                nggpanoAdmin::resize_preview_pano($pid, $gid, $imgWidth, $imgHeight, $backup);          
            }
            break;
        default:
            break;
    }

}


/**
 * Extract gps data from picture and save it to database
 * @param string $pid the image id
 * @return void
 */
function nggpano_extractgps_and_save($pid) {
    global $wpdb;
        if($pid) {
            //echo "pid=".$pid;
                $result = array();
                $message = '';
                $error = true;
                //Get gps info from exif
                $gps_array = nggpano_get_exif_gps($pid, true);
                
                if($gps_array) {
                
                    $lat = (strlen($gps_array["latitude"]) == 0) ? 'NULL' : $gps_array["latitude"];
                    $lng = (strlen($gps_array["longitude"]) == 0) ? 'NULL' : $gps_array["longitude"];
                    $alt = (strlen($gps_array["altitude"]) == 0) ? 'NULL' : round($gps_array["altitude"],0);
    //                
                    //echo json_encode($gps_array);


                    if(nggpano_getImagePanoramicOptions($pid)) {
                        if($wpdb->query("UPDATE ".$wpdb->prefix."nggpano_panoramic SET gps_lat = ".$lat.", gps_lng = ".$lng.", gps_alt = ".$alt." WHERE pid = '".$wpdb->escape($pid)."'") !== false) {
                            $error = false; 
                            $message = __('GPS datas successfully saved','nggpano');
                        } else {
                            $message = 'Error with database';
                        };
                    }else{
                        $image = nggdb::find_image( $id );
                        $gid = $image->galleryid;
                        if($wpdb->query("INSERT INTO ".$wpdb->prefix."nggpano_panoramic (id, pid, gid, gps_lat, gps_lng, gps_alt) VALUES (null, '".$wpdb->escape($pid)."', '".$wpdb->escape($gid)."', ".$lat.", ".$lng.", ".$alt.")") !== false) {
                            $error = false;
                            $message = __('GPS datas successfully saved','nggpano');
                        } else {
                            $message = 'Error with database';
                        };
                    }
                        $result['error']    = $error;
                        $result['message']  = $message;
                        $result['gps_data'] = $gps_array;
                //nggpano_update_gallery(null, $post);

                } else {
                    $result['error']= true;
                    $result['message']=__('No GPS datas in picture','nggpano');
                    $result['gps_data'] = array();
                }
                $result['pid'] = $pid;
                //nggpano_picture[1][lat]
                
                echo json_encode($result);
        }
}

/**
 * Delete pano information in database and remove all krpano files
 * @param string $pid the image id
 * @param string $pid the gallery id
 * @return void
 */
function nggpano_delete_pano($pid, $gid) {
        

        $result = array();
        $message = '';
        $error = true;
        
        $pano = new nggpanoPano($pid, $gid);
        $pano->delete(true);
        
//        $result['error']= false;
//        $result['message']=__('Panoramic successfully deleted','nggpano');
//        $result['pid'] = $pid;
//        
        
        //echo json_encode($result);
        
             echo nggpanoAdmin::krpano_image_form($pid, __('Panoramic successfully deleted','nggpano'));
    
    
}

/**
 * Extract foc data from picture
 * @param string $pid the image id
 * @return void
 */
function nggpano_extractfov($pid) {
    
    $exif_data = new nggpanoEXIF($pid);
    $fov_data = $exif_data->getFOVInformations();
    
    echo json_encode($fov_data);

//    $hfov       = isset ($fov_data['hfov']) ? $fov_data['hfov'] : '';
//    $vfov       = isset ($fov_data['vfov']) ? $fov_data['vfov'] : '';
//    $voffset    = isset ($fov_data['voffset']) ? $fov_data['voffset'] : '';
    
}


/**
 * Save gps data in database
 * @param string $pid the image id
 * @param array  $gps_array array with latitude, longitude, altitude values
 * 
 * @return void
 */
function nggpano_savegps($pid, $gps_array) {
    global $wpdb;
        if($pid) {
            //echo "pid=".$pid;
                $result = array();
                $message = '';
                $error = true;
                //Get gps info from array
                if($gps_array) {
                
                    $lat = (strlen($gps_array["latitude"]) == 0) ? 'NULL' : $gps_array["latitude"];
                    $lng = (strlen($gps_array["longitude"]) == 0) ? 'NULL' : $gps_array["longitude"];
                    $alt = (strlen($gps_array["altitude"]) == 0) ? 'NULL' : round($gps_array["altitude"],0);
        
                    //echo json_encode($gps_array);


                    if(nggpano_getImagePanoramicOptions($pid)) {
                        if($wpdb->query("UPDATE ".$wpdb->prefix."nggpano_panoramic SET gps_lat = ".$lat.", gps_lng = ".$lng.", gps_alt = ".$alt." WHERE pid = '".$wpdb->escape($pid)."'") !== false) {
                            $error = false; 
                            $message = __('GPS datas successfully saved','nggpano');
                        } else {
                            $message = 'Error with database';
                        };
                    }else{
                        $image = nggdb::find_image( $id );
                        $gid = $image->galleryid;
                        if($wpdb->query("INSERT INTO ".$wpdb->prefix."nggpano_panoramic (id, pid, gid, gps_lat, gps_lng, gps_alt) VALUES (null, '".$wpdb->escape($pid)."', '".$wpdb->escape($gid)."', ".$lat.", ".$lng.", ".$alt.")") !== false) {
                            $error = false;
                            $message = __('GPS datas successfully saved','nggpano');
                        } else {
                            $message = 'Error with database';
                        };
                    }
                        $result['error']    = $error;
                        $result['message']  = $message;
                        $result['gps_data'] = $gps_array;
                //nggpano_update_gallery(null, $post);

                } else {
                    $result['error']= true;
                    $result['message']=__('No GPS datas in paramater','nggpano');
                    $result['gps_data'] = array();
                }
                $result['pid'] = $pid;
                //nggpano_picture[1][lat]
                
                echo json_encode($result);
        }
}

function debug_ratio($id) {

    // let's get the image data
    $picture = nggdb::find_image($id);

    //Check if fov data is in exif infos (if file directly from autopano
    $exif_data = new nggpanoEXIF($id);
    $fov_data = $exif_data->getFOVInformations();

    echo $picture->filename;
    
    echo '<hr/>';
    
    echo json_encode($exif_data->exif_data['EXIF']['UserComment']);
    
    echo '<hr/>';
    
    $ratio = $exif_data->getImageRatio(true);
    echo $ratio;
    
    echo '<hr/>';
    
    $datafilename = $exif_data->getFOVInformationsFromFilename();
    echo json_encode($datafilename);
    
    echo '<hr/>';
    
    

//    $hfov       = isset ($fov_data['hfov']) ? $fov_data['hfov'] : '';
//    $vfov       = isset ($fov_data['vfov']) ? $fov_data['vfov'] : '';
//    $voffset    = isset ($fov_data['voffset']) ? $fov_data['voffset'] : '';
echo json_encode($fov_data);
}




?>
