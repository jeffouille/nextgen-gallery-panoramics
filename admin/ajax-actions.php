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
        case 'extractfov':
            if (isset ( $_GET['id']) ) {
                nggpano_extractfov($_GET['id']);
            }

            break;
        case 'build-pano':
            check_admin_referer('build-pano');
            //do_action('ngg_update_gallery', $_POST['gid'], $_POST);
            nggpanoAdmin::build_pano($_POST);
            //nggPanoramic::show_message( __('Panoramic successfully created','nggpano') );
            break;
        
        case 'delete-pano':
            if (isset ( $_GET['id']) && isset ( $_GET['gid'])) {
                nggpano_delete_pano($_GET['id'],$_GET['gid']);
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