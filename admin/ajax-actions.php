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
        case 'save-gps-region':
            check_admin_referer('pick-gps-region');
            if (isset ( $_GET['gid']) ) {
                if($_POST['picture_sw_lat'] <>'' && $_POST['picture_sw_lng'] <>'' && $_POST['picture_ne_lat'] <>'' && $_POST['picture_ne_lng'] <>'') {
                $bounds_array = array(
                    'sw' => array('lat' => $_POST['picture_sw_lat'],
                                  'lng' => $_POST['picture_sw_lng']),
                    'ne' => array('lat' => $_POST['picture_ne_lat'],
                                  'lng' => $_POST['picture_ne_lng'])
                    );
                    nggpano_save_gps_region($_GET['gid'], serialize($bounds_array));
                } else {
                    nggpano_save_gps_region($_GET['gid'], null);
                }
            }
            break;
        
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
        case 'extractxml':
            if (isset ( $_GET['id']) ) {
                nggpano_extractxml($_GET['id'],$_GET['fromdb']);
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
                //echo "<H1>TATA</H1>";
                nggpanoAdmin::build_pano($pid, $gid, $hfov, $vfov, $voffset);          
            }
            
            break;
            
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
                $panoFolder = isset ($_POST['panoFolder']) ? $_POST['panoFolder'] : '';
                
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
                $pano->setPanoFolder($panoFolder);
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
                if(isset ( $_GET['tiles'])) {
                    if ($_GET['tiles'] == 'true')
                        nggpano_delete_tiles($_GET['id'],$_GET['gid']);
                } else {
                    nggpano_delete_pano($_GET['id'],$_GET['gid']);
                }
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
            
        case 'publish-pano':
            check_admin_referer('publish-pano');
            // Should be called via a publish dialog	
            if ( isset($_POST['page']) && $_POST['page'] == 'publish-pano' && $_POST['pid'] )
                nggpanoAdmin::publish_pano();
            
            break;
            
        case 'publish-pano-infocus':
            check_admin_referer('publish-pano-infocus');
            // Should be called via a publish dialog	
            if ( isset($_POST['page']) && $_POST['page'] == 'publish-pano-infocus' && $_POST['pid'] )
                nggpanoAdmin::publish_pano_infocus();
            
            break;
        case 'delete-post':
            if (isset ( $_GET['post_id'])) {
                nggpano_delete_article($_GET['post_id'],$_GET['pid']);
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

        echo nggpanoAdmin::krpano_image_form($pid, __('Panoramic successfully deleted','nggpano'));
    
    
}

/**
 * Delete all krpano tiles but keep pano information in database
 * @param string $pid the image id
 * @param string $pid the gallery id
 * @return void
 */
function nggpano_delete_tiles($pid, $gid) {
        
    $result = array();
    $message = '';
    $error = true;

    $pano = new nggpanoPano($pid, $gid);
    $pano->delete(false,true);
        
    echo nggpanoAdmin::krpano_image_form($pid, __('Panoramic tiles successfully deleted','nggpano'));

}





/**
 * Delete post
 * @param string $post_id the post id
 * @return void
 */
function nggpano_delete_article($post_id,$pid) {
        
    global $wpdb;

        $result = array();
        $message = '';
        $error = true;
        
        if(wp_delete_post($post_id, true)) {
            $result['error']= true;
            $result['message']=__('Article successfully deleted','nggpano');
            $result['pid'] = $pid;
            if($wpdb->query("UPDATE ".$wpdb->prefix."nggpano_panoramic SET post_id = NULL WHERE pid = '".$wpdb->escape($pid)."'") !== false) {
                $error = false; 
                $message = __('Post successfully deleted','nggpano');
            } else {
                $message = 'Error with database';
            };
               
        } else {
            $result['error']= false;
            $result['message']=__('Article not deleted successfully','nggpano');
            $result['pid'] = $pid;
        };

//        $result['error']= false;
//        $result['message']=__('Panoramic successfully deleted','nggpano');
//        $result['pid'] = $pid;
//        
        
        //echo json_encode($result);
        
             echo nggpanoAdmin::krpano_image_form($pid, $result['message']);
    
    
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
 * Extract xml configuration from pnao.xml and pano_html5.xml in panofolder
 * @param string $pid the image id
 * @return void
 */
function nggpano_extractxml($pid, $fromdb = false) {
        
    $result = array();
    $message = '';
    $error = false;
    $pano_infos = nggpano_getImagePanoramicOptions($pid);

    // use defaults the first time
    //if($pano_infos && isset ($pano_infos->hfov)) {
    $hfov       = isset ($pano_infos->hfov) ? $pano_infos->hfov : '';
    $vfov       = isset ($pano_infos->vfov) ? $pano_infos->vfov : '';
    $voffset    = isset ($pano_infos->voffset) ? $pano_infos->voffset : '';
    $xml_configuration    = isset ($pano_infos->xml_configuration) ? $pano_infos->xml_configuration : '';
    if($fromdb) {
        $str_retour = $xml_configuration;
    } else {
    
    
        $is_partial    = isset ($pano_infos->is_partial) ? $pano_infos->is_partial : '0';
        $panoFolder = isset ($pano_infos->pano_directory) ? $pano_infos->pano_directory : '';

        $pano_flash_xml = file_get_contents(NGGPANOWINABSPATH.$panoFolder."/pano.xml");
        $pano_html5_xml = file_get_contents(NGGPANOWINABSPATH.$panoFolder."/pano_html5.xml");

        if($pano_flash_xml) {
            $pano_flash_xml = str_replace('devices="flash"', '', $pano_flash_xml);
            $pano_flash_xml = str_replace('<view', '<view devices="flash"', $pano_flash_xml);
            $pano_flash_xml = str_replace('<image', '<image devices="flash"', $pano_flash_xml);
            $pano_flash_xml = str_replace('<preview', '<preview devices="flash"', $pano_flash_xml);
            $str_retour .= $pano_flash_xml;
            if($pano_html5_xml) {
                $pano_html5_xml = str_replace('devices="!flash"', '', $pano_html5_xml);
                $pano_html5_xml = str_replace('<image', '<image devices="!flash"', $pano_html5_xml);
                $pano_html5_xml = str_replace('<preview', '<preview devices="!flash"', $pano_html5_xml);
                $pano_html5_xml = str_replace('<view', '<view devices="!flash"', $pano_html5_xml);
                $str_retour .= $pano_html5_xml;
            } else {
                $error = true;
                $message = "no pano_html5.xml file in ".$panoFolder;
            }
        } else {
            $error = true;
            $message = "no pano.xml file in ".$panoFolder;
        }

    }

    $result['error']    = $error;
    $result['message']  = $message;
    $result['xml_data'] = $str_retour;
            //$pano_flash_xml .= '\n' . $pano_html5_xml;

                //$this->xml_configuration = str_replace('url="', 'url="'.$this->panoFolder.'/', $this->xml_configuration);
                    
//                $pano->loadFromDB();
//                $pano->setHFov($hfov);
//                $pano->setVFov($vfov);
//                $pano->setVOffset($voffset);
//                $pano->setXmlConfiguration($xml_configuration);
//                $pano->setPanoFolder($panoFolder);
//                $pano->setIsPartial($is_partial);
//                
//                $pano->save();
    
    
    echo json_encode($result);

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



/**
 * Save region gps bounds of a gallery in database
 * @param string $gid the gallery id
 * 
 * @return void
 */
function nggpano_save_gps_region($gid, $bounds_object) {
    global $wpdb;
        if($gid) {
            // let's get the gallery data
            $gallery = nggdb::find_gallery($gid);
            if($gallery) {

                $gallery_values = nggpano_getGalleryOptions($gid);
            
            //echo "pid=".$pid;
                $result = array();
                $message = '';
                $error = true;
                //if($bounds_object) {


                    if($gallery_values) {
                        if($wpdb->query("UPDATE ".$wpdb->prefix."nggpano_gallery SET gps_region = '".$bounds_object."' WHERE gid = '".$wpdb->escape($gid)."'") !== false) {
                            $error = false; 
                            $message = __('GPS datas successfully saved','nggpano');
                        } else {
                            $message = 'Error with database1';
                        };
                    }else{
                        $image = nggdb::find_image( $id );
                        $gid = $image->galleryid;
                        if($wpdb->query("INSERT INTO ".$wpdb->prefix."nggpano_gallery (id, gid, gps_region) VALUES (null, '".$wpdb->escape($gid)."', '".$bounds_object."')") !== false) {
                            $error = false;
                            $message = __('GPS datas successfully saved','nggpano');
                        } else {
                            $message = 'Error with database2';
                        };
                    }
                        $result['error']    = $error;
                        $result['message']  = $message;
                        $result['gps_data'] = unserialize($bounds_object);
                //nggpano_update_gallery(null, $post);

//                } else {
//                    $result['error']= true;
//                    $result['message']=__('No GPS datas in paramater','nggpano');
//                    $result['gps_data'] = array();
//                }
                $result['gid'] = $gid;
                //nggpano_picture[1][lat]
                
                echo json_encode($result);
        }
    }
}

?>
