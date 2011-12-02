<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

include_once ( dirname (__FILE__) . '/../lib/nggpanoPano.class.php' ); //nggpanoPano Class

/**
 * nggpanoAdmin - Class for admin operation
 * 
 * @package NextGEN Gallery
 * @author Alex Rabe
 * @copyright 2007-2010
 * @access public
 */
class nggpanoAdmin{

	/**
	 * nggpanoAdmin::extract_gps() - extract gps data from image
	 * 
	 * @class nggpanoAdmin
	 * @param object | int $image contain all information about the image or the id
	 * @return string result code
	 */
	function extract_gps($image) {
		
		global $ngg, $wpdb;
		
		if ( is_numeric($image) )
			$image = nggdb::find_image( $image );
		
		if ( !is_object($image) ) 
			return __('Object didn\'t contain correct data','nggallery');	
                
                $pid = $image->pid;
                $gid = $image->galleryid;
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

                    if(nggpano_getImagePanoramicOptions($pid)) {
                        if($wpdb->query("UPDATE ".$wpdb->prefix."nggpano_panoramic SET gps_lat = ".$lat.", gps_lng = ".$lng.", gps_alt = ".$alt." WHERE pid = '".$wpdb->escape($pid)."'") !== false) {
                            return '1';
                        } else {
                            return ' <strong>' . $image->filename . ' (Error : Error with database)</strong>';
                        };
                    }else{
                        if($wpdb->query("INSERT INTO ".$wpdb->prefix."nggpano_panoramic (id, pid, gid, gps_lat, gps_lng, gps_alt) VALUES (null, '".$wpdb->escape($pid)."', '".$wpdb->escape($gid)."', ".$lat.", ".$lng.", ".$alt.")") !== false) {
                            return '1';
                        } else {
                            return ' <strong>' . $image->filename . ' (Error : Error with database)</strong>';
                        };
                    }

                } else {
                    return ' <strong>' . $image->filename . ' (Error : No GPS datas in picture)</strong>';
                }

		//return '1';
	}
        
        function gps_image_form($pid) {
                            //Get GPS values for the current image
                $image_values = nggpano_getImagePanoramicOptions($pid);
                $lat = isset($image_values->gps_lat) ? $image_values->gps_lat : '';
                $lng = isset($image_values->gps_lng) ? $image_values->gps_lng : '';
                $alt = isset($image_values->gps_alt) ? $image_values->gps_alt : '';
                
                //Get picture obj
                $picture = nggdb::find_image( $pid );
                
?>
                <table>
                    <tr>
                        <td class="nggpano"><?php _e('Latitude','nggpano');?></td>
                        <td><input type="text" id="nggpano_picture_lat_<?php echo $pid ?>" name="nggpano_picture[<?php echo $pid ?>][lat]" style="width:95%;" value="<?php echo $lat; ?>" /></td>
                    </tr>
                    <tr>
                        <td><?php _e('Longitude','nggpano');?></td>
                        <td><input type="text" id="nggpano_picture_lng_<?php echo $pid ?>" name="nggpano_picture[<?php echo $pid ?>][lng]" style="width:95%;" value="<?php echo $lng; ?>" /></td>
                    </tr>
                    <tr>
                        <td><?php _e('Altitude','nggpano');?></td>
                        <td><input type="text" id="nggpano_picture_alt_<?php echo $pid ?>" name="nggpano_picture[<?php echo $pid ?>][alt]" style="width:95%;" value="<?php echo $alt; ?>" /></td>
                    </tr>
                </table>
                <p>
                    <img class="nggpano-gps-loader" src="<?php echo NGGPANOGALLERY_URLPATH ; ?>admin/images/loading.gif" style="display:none;" />
                    <?php
                    $actions = array();
                    //$actions['extractgps']   = '<a class="shutter" href="' . $picture->imageURL . '" title="' . esc_attr(sprintf(__('View "%s"'), $picture->filename)) . '">' . __('View', 'nggallery') . '</a>';
                    //$actions['meta']   = '<a class="nggpano-dialog" href="' . NGGALLERY_URLPATH . 'admin/showmeta.php?id=' . $pid . '" title="' . __('Show Meta data','nggallery') . '">' . __('Meta', 'nggallery') . '</a>';
                    //$actions['custom_thumb']   = '<a class="nggpano-dialog" href="' . NGGALLERY_URLPATH . 'admin/edit-thumbnail.php?id=' . $pid . '" title="' . __('Customize thumbnail','nggallery') . '">' . __('Edit thumb', 'nggallery') . '</a>';							
                    //$actions['rotate'] = '<a class="nggpano-dialog" href="' . NGGALLERY_URLPATH . 'admin/rotate.php?id=' . $pid . '" title="' . __('Rotate','nggallery') . '">' . __('Rotate', 'nggallery') . '</a>';
                    //if ( current_user_can( 'publish_posts' ) )
//$actions['publish'] = '<a class="nggpano-dialog" href="' . NGGPANOGALLERY_URLPATH . 'admin/publish.php?id=' . $pid . '&h=230" title="' . __('Publish this image','nggallery') . '">' . __('Publish', 'nggallery') . '</a>';
                    //if ( file_exists( $picture->imagePath . '_backup' ) )	
//$actions['recover']   = '<a class="confirmrecover" href="' .wp_nonce_url("admin.php?page=nggallery-manage-gallery&amp;mode=recoverpic&amp;gid=" . $act_gid . "&amp;pid=" . $pid, 'ngg_recoverpicture'). '" title="' . __('Recover','nggallery') . '" onclick="javascript:check=confirm( \'' . esc_attr(sprintf(__('Recover "%s" ?' , 'nggallery'), $picture->filename)). '\');if(check==false) return false;">' . __('Recover', 'nggallery') . '</a>';
                    $actions['extractgps']  = '<a class="extractgps" href="' . NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=extractgps&id=' . $pid.'" onclick="javascript:check=confirm( \'' . esc_attr(sprintf(__('Replace current GPS data for "%s" ?' , 'nggpano'), $picture->filename)). '\');if(check==false) return false;">' . __('Get GPS from picture' , 'nggpano') . '</a>';
                    $actions['pickgps']     = '<a class="nggpano-dialog" href="' . NGGPANOGALLERY_URLPATH . 'admin/pick-gps.php?id=' . $pid . '" title="' . __('Pick GPS on map','nggpano') . '">' . __('Pick GPS on map','nggpano') . '</a>';
                    //$actions['debug']       = nggpano_get_exif_gps($pid);
                    $action_count = count($actions);
                    $i = 0;
                    echo '<div class="row-actions">';
                    foreach ( $actions as $action => $link ) {
                            ++$i;
                            ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
                            echo "<span class='$action'>$link$sep</span>";
                    }
                    echo '</div>';
                    ?>
                    <div class="nggpano-error" style="display:none;"></div>
                </p>

                <?php
        }

        function krpano_image_form($pano, $message = '') {
                
                if(is_numeric($pano)) {
                    $pid = $pano;
                    //Get krpano values for the current image
                    $image_values = nggpano_getImagePanoramicOptions($pid);
                    $gid = isset($image_values->gid) ? $image_values->gid : '';

                    //get pano
                    $pano = new nggpanoPano($pid, $gid);
                } else {
                    $pid = $pano->pid;
                    $gid = $pano->gid;
                }
                
                //Get picture obj
                $picture = nggdb::find_image( $pid );
                $pano_exist = $pano->exists();
                
                //URL to show Pano
                $url_show = NGGPANOGALLERY_URLPATH . 'admin/show-pano.php?gid=' . $gid . '&pid=' . $pid. '&h=500&w=800';
                //$url_show = NGGPANOGALLERY_URLPATH . 'nggpanoshow.php?gid=' . $gid . '&pid=' . $pid. '&h=500&w=800';
                
                ?>
                <div class="admin-pano-thumb">
                <?php if($pano_exist) : ?>
                <a href="<?php echo $url_show; ?>" class="nggpano-dialog" title="<?php echo esc_attr(sprintf(__('Panorama for "%s" ?' , 'nggpano'), $picture->filename)) ?>">
                    <img class="thumb" src="<?php echo add_query_arg('i', mt_rand(), $picture->thumbURL); ?>" id="thumb<?php echo $pid ?>" />
                </a>
                <?php endif; ?>
                </div>
                <p>
                    <img class="nggpano-pano-loader" src="<?php echo NGGPANOGALLERY_URLPATH ; ?>admin/images/loading.gif" style="display:none;" />
                    <?php
                    $actions = array();
                    $actions['build'] = '<a class="nggpano-dialog" href="' . NGGPANOGALLERY_URLPATH . 'admin/build-pano.php?id=' . $pid . '&h=340" title="' . __('Build the panoramic from this image','nggpano') . '">' . __('Build', 'nggpano') . '</a>';
                    if($pano_exist) {
                        $actions['delete']  = '<a class="submitdelete delete-pano" href="' . NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=delete-pano&gid=' . $gid . '&id=' . $pid. '" onclick="javascript:check=confirm( \'' . esc_attr(sprintf(__('Delete panoramas files for "%s" ?' , 'nggpano'), $picture->filename)). '\');if(check==false) return false;">' . __('Delete Pano' , 'nggpano') . '</a>';
                        $actions['show']    = '<a class="nggpano-dialog" href="' . $url_show .'" title="' . esc_attr(sprintf(__('Panorama for "%s" ?' , 'nggpano'), $picture->filename)) . '">' . __('Show', 'nggpano') . '</a>';
                        $actions['publish']    = '<a class="nggpano-dialog" href="' . $url_show .'" title="' . esc_attr(sprintf(__('Publish Panorama for "%s" ?' , 'nggpano'), $picture->filename)) . '">' . __('Publish', 'nggpano') . '</a>';
                    
                        
                    }
                    $action_count = count($actions);
                    $i = 0;
                    echo '<div class="row-actions">';
                    foreach ( $actions as $action => $link ) {
                            ++$i;
                            ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
                            echo "<span class='$action'>$link$sep</span>";
                    }
                    echo '</div>';
                    ?>
                
                    <div class="nggpano-error" style="<?php ($message <> "" || $pano->error) ? '' : 'display:none;' ?>"><?php echo $message; ?><?php echo $pano->errmsg; ?></div>
                </p>


                <?php
        }
        
   	/**
   	 * Build panoramic using krpanotools
         * 
   	 * @return void
   	 */
   	function build_pano() {
   	    global $wpdb;
            
   	    
            
            if($_POST['pid']) {
                $pid = $_POST['pid'];
                
                $gid = $_POST['gid'];
            
		if(! class_exists('nggpanoPano'))
			require_once(NGGPANOGALLERY_ABSPATH . '/lib/nggpanoPano.class.php' );
		
                //Get paramaters
                $hfov       = isset ($_POST['hfov']) ? $_POST['hfov'] : '';
                $vfov       = isset ($_POST['vfov']) ? $_POST['vfov'] : '';
                $voffset    = isset ($_POST['voffset']) ? $_POST['voffset'] : '';
                
                //Create pano
                $pano = new nggpanoPano($pid, $gid, $hfov, $vfov, $voffset);
                $pano->createTiles();
                
                echo nggpanoAdmin::krpano_image_form($pano);
                
            }
    }

	function create_thumbnail($image) {
		
		global $ngg;
		
		if(! class_exists('ngg_Thumbnail'))
			require_once( nggGallery::graphic_library() );
		
		if ( is_numeric($image) )
			$image = nggdb::find_image( $image );

		if ( !is_object($image) ) 
			return __('Object didn\'t contain correct data','nggallery');
                
		// before we start we import the meta data to database (required for uploads before V1.4.0)
		nggpanoAdmin::maybe_import_meta( $image->pid );
        		
		// check for existing thumbnail
		if (file_exists($image->thumbPath))
			if (!is_writable($image->thumbPath))
				return $image->filename . __(' is not writeable ','nggallery');

		$thumb = new ngg_Thumbnail($image->imagePath, TRUE);

		// skip if file is not there
		if (!$thumb->error) {
			if ($ngg->options['thumbfix'])  {

				// calculate correct ratio
				$wratio = $ngg->options['thumbwidth'] / $thumb->currentDimensions['width'];
				$hratio = $ngg->options['thumbheight'] / $thumb->currentDimensions['height'];
				
				if ($wratio > $hratio) {
					// first resize to the wanted width
					$thumb->resize($ngg->options['thumbwidth'], 0);
					// get optimal y startpos
					$ypos = ($thumb->currentDimensions['height'] - $ngg->options['thumbheight']) / 2;
					$thumb->crop(0, $ypos, $ngg->options['thumbwidth'],$ngg->options['thumbheight']);	
				} else {
					// first resize to the wanted height
					$thumb->resize(0, $ngg->options['thumbheight']);	
					// get optimal x startpos
					$xpos = ($thumb->currentDimensions['width'] - $ngg->options['thumbwidth']) / 2;
					$thumb->crop($xpos, 0, $ngg->options['thumbwidth'],$ngg->options['thumbheight']);	
				}
			//this create a thumbnail but keep ratio settings	
			} else {
				$thumb->resize($ngg->options['thumbwidth'],$ngg->options['thumbheight']);	
			}
			
			// save the new thumbnail
			$thumb->save($image->thumbPath, $ngg->options['thumbquality']);
			nggpanoAdmin::chmod ($image->thumbPath); 
			
			//read the new sizes
			$new_size = @getimagesize ( $image->thumbPath );
			$size['width'] = $new_size[0];
			$size['height'] = $new_size[1]; 
			
			// add them to the database
			nggdb::update_image_meta($image->pid, array( 'thumbnail' => $size) );
		} 
				
		$thumb->destruct();
		
		if ( !empty($thumb->errmsg) )
			return ' <strong>' . $image->filename . ' (Error : '.$thumb->errmsg .')</strong>';
		
		// success
		return '1'; 
	}
	
	/**
	 * nggpanoAdmin::resize_image() - create a new image, based on the height /width
	 * 
	 * @class nggpanoAdmin
	 * @param object | int $image contain all information about the image or the id
	 * @param integer $width optional 
	 * @param integer $height optional
	 * @return string result code
	 */
	function resize_image($image, $width = 0, $height = 0) {
		
		global $ngg;
		
		if(! class_exists('ngg_Thumbnail'))
			require_once( nggGallery::graphic_library() );

		if ( is_numeric($image) )
			$image = nggdb::find_image( $image );
		
		if ( !is_object($image) ) 
			return __('Object didn\'t contain correct data','nggallery');	
		
		// before we start we import the meta data to database (required for uploads before V1.4.0)
		nggpanoAdmin::maybe_import_meta( $image->pid );
		
		// if no parameter is set, take global settings
		$width  = ($width  == 0) ? $ngg->options['imgWidth']  : $width;
		$height = ($height == 0) ? $ngg->options['imgHeight'] : $height;
		
		if (!is_writable($image->imagePath))
			return ' <strong>' . $image->filename . __(' is not writeable','nggallery') . '</strong>';
		
		$file = new ngg_Thumbnail($image->imagePath, TRUE);

		// skip if file is not there
		if (!$file->error) {
			
			// If required save a backup copy of the file
			if ( ($ngg->options['imgBackup'] == 1) && (!file_exists($image->imagePath . '_backup')) )
				@copy ($image->imagePath, $image->imagePath . '_backup');
			
			$file->resize($width, $height);
			$file->save($image->imagePath, $ngg->options['imgQuality']);
			// read the new sizes
			$size = @getimagesize ( $image->imagePath );
			// add them to the database
			nggdb::update_image_meta($image->pid, array( 'width' => $size[0], 'height' => $size[1] ) );
			$file->destruct();
		} else {
            $file->destruct();
			return ' <strong>' . $image->filename . ' (Error : ' . $file->errmsg . ')</strong>';
		}

		return '1';
	}
	
	/**
	 * Rotated/Flip an image based on the orientation flag or a definded angle
	 * 
	 * @param int|object $image
	 * @param string (optional) $dir, CW (clockwise)or CCW (counter clockwise), if set to false, the exif flag will be used
	 * @param string (optional)  $flip, could be either false | V (flip vertical) | H (flip horizontal)
	 * @return string result code
	 */
	function rotate_image($image, $dir = false, $flip = false) {

		global $ngg;

		if(! class_exists('ngg_Thumbnail'))
			require_once( nggGallery::graphic_library() );
		
		if ( is_numeric($image) )
			$image = nggdb::find_image( $image );
		
		if ( !is_object($image) ) 
			return __('Object didn\'t contain correct data','nggallery');		
	
		if (!is_writable($image->imagePath))
			return ' <strong>' . $image->filename . __(' is not writeable','nggallery') . '</strong>';
		
		// if you didn't define a rotation, we look for the orientation flag in EXIF
		if ( $dir === false ) {
			$meta = new nggMeta( $image->pid );
			$exif = $meta->get_EXIF();
	
			if (isset($exif['Orientation'])) {
				
				switch ($exif['Orientation']) {
					case 5 : // vertical flip + 90 rotate right
						$flip = 'V';
					case 6 : // 90 rotate right
						$dir = 'CW';
						break;
					case 7 : // horizontal flip + 90 rotate right
						$flip = 'H';
					case 8 : // 90 rotate left
						$dir = 'CCW';
						break;
					case 4 : // vertical flip
						$flip = 'V';
						break;
					case 3 : // 180 rotate left
						$dir = 180;
						break;
					case 2 : // horizontal flip
						$flip = 'H';
						break;						
					case 1 : // no action in the case it doesn't need a rotation
					default:
						return '0';
						break; 
				}
			} else
                return '0';
		}
		$file = new ngg_Thumbnail( $image->imagePath, TRUE );
		
		// skip if file is not there
		if (!$file->error) {

			// If required save a backup copy of the file
			if ( ($ngg->options['imgBackup'] == 1) && (!file_exists($image->imagePath . '_backup')) )
				@copy ($image->imagePath, $image->imagePath . '_backup');

			// before we start we import the meta data to database (required for uploads before V1.4.X)
			nggpanoAdmin::maybe_import_meta( $image->pid );

			if ( $dir !== 0 )
				$file->rotateImage( $dir );
			if ( $dir === 180)
				$file->rotateImage( 'CCW' ); // very special case, we rotate the image two times
			if ( $flip == 'H')
				$file->flipImage(true, false);
			if ( $flip == 'V')
				$file->flipImage(false, true);
					
			$file->save($image->imagePath, $ngg->options['imgQuality']);
			
			// read the new sizes
			$size = @getimagesize ( $image->imagePath );
			// add them to the database
			nggdb::update_image_meta($image->pid, array( 'width' => $size[0], 'height' => $size[1] ) );
			
		}
		
		$file->destruct();

		if ( !empty($file->errmsg) )
			return ' <strong>' . $image->filename . ' (Error : '.$file->errmsg .')</strong>';		

		return '1';
		
	}

	/**
	 * nggpanoAdmin::set_watermark() - set the watermark for the image
	 * 
	 * @class nggpanoAdmin
	 * @param object | int $image contain all information about the image or the id
	 * @return string result code
	 */
	function set_watermark($image) {
		
		global $ngg;

		if(! class_exists('ngg_Thumbnail'))
			require_once( nggGallery::graphic_library() );
		
		if ( is_numeric($image) )
			$image = nggdb::find_image( $image );
		
		if ( !is_object($image) ) 
			return __('Object didn\'t contain correct data','nggallery');		

		// before we start we import the meta data to database (required for uploads before V1.4.0)
		nggpanoAdmin::maybe_import_meta( $image->pid );	

		if (!is_writable($image->imagePath))
			return ' <strong>' . $image->filename . __(' is not writeable','nggallery') . '</strong>';
		
		$file = new ngg_Thumbnail( $image->imagePath, TRUE );

		// skip if file is not there
		if (!$file->error) {
			
			// If required save a backup copy of the file
			if ( ($ngg->options['imgBackup'] == 1) && (!file_exists($image->imagePath . '_backup')) )
				@copy ($image->imagePath, $image->imagePath . '_backup');
			
			if ($ngg->options['wmType'] == 'image') {
				$file->watermarkImgPath = $ngg->options['wmPath'];
				$file->watermarkImage($ngg->options['wmPos'], $ngg->options['wmXpos'], $ngg->options['wmYpos']); 
			}
			if ($ngg->options['wmType'] == 'text') {
				$file->watermarkText = $ngg->options['wmText'];
				$file->watermarkCreateText($ngg->options['wmColor'], $ngg->options['wmFont'], $ngg->options['wmSize'], $ngg->options['wmOpaque']);
				$file->watermarkImage($ngg->options['wmPos'], $ngg->options['wmXpos'], $ngg->options['wmYpos']);  
			}
			$file->save($image->imagePath, $ngg->options['imgQuality']);
		}
		
		$file->destruct();

		if ( !empty($file->errmsg) )
			return ' <strong>' . $image->filename . ' (Error : '.$file->errmsg .')</strong>';		

		return '1';
	}

	/**
	 * Recover image from backup copy and reprocess it
	 * 
	 * @class nggpanoAdmin
	 * @since 1.5.0
	 * @param object | int $image contain all information about the image or the id
	 * @return string result code
	 */
	
	function recover_image($image) {

		global $ngg;
		
		if ( is_numeric($image) )
			$image = nggdb::find_image( $image );
		
		if ( !is_object( $image ) ) 
			return __('Object didn\'t contain correct data','nggallery');		
			
		if (!is_writable( $image->imagePath ))
			return ' <strong>' . $image->filename . __(' is not writeable','nggallery') . '</strong>';
		
		if (!file_exists( $image->imagePath . '_backup' )) {
			return ' <strong>'.__('File do not exists','nggallery').'</strong>';
		}

		if (!@copy( $image->imagePath . '_backup' , $image->imagePath) )
			return ' <strong>'.__('Couldn\'t restore original image','nggallery').'</strong>';
		
		require_once(NGGALLERY_ABSPATH . '/lib/meta.php');
		
		$meta_obj = new nggMeta( $image->pid );
					
        $common = $meta_obj->get_common_meta();
        $common['saved']  = true; 
		$result = nggdb::update_image_meta($image->pid, $common);			
		
		return '1';
		
	}
		
	/**
	 * Add images to database
	 * 
	 * @class nggpanoAdmin
	 * @param int $galleryID
	 * @param array $imageslist
	 * @return array $image_ids Id's which are sucessful added
	 */
	function add_Images($galleryID, $imageslist) {
		
		global $wpdb, $ngg;
		
		$image_ids = array();
		
		if ( is_array($imageslist) ) {
			foreach($imageslist as $picture) {
				
                // filter function to rename/change/modify image before
                $picture = apply_filters('ngg_pre_add_new_image', $picture, $galleryID);
                
				// strip off the extension of the filename
				$path_parts = pathinfo( $picture );
				$alttext = ( !isset($path_parts['filename']) ) ? substr($path_parts['basename'], 0,strpos($path_parts['basename'], '.')) : $path_parts['filename'];
				// save it to the database
                $pic_id = nggdb::add_image( $galleryID, $picture, '', $alttext ); 

				if ( !empty($pic_id) ) 
					$image_ids[] = $pic_id;

				// add the metadata
				nggpanoAdmin::import_MetaData( $pic_id );
				
				// auto rotate
				nggpanoAdmin::rotate_image( $pic_id );		

				// Autoresize image if required
                if ($ngg->options['imgAutoResize']) {
                	$imagetmp = nggdb::find_image( $pic_id );
                	$sizetmp = @getimagesize ( $imagetmp->imagePath );
                	$widthtmp  = $ngg->options['imgWidth'];
                	$heighttmp = $ngg->options['imgHeight'];
                	if (($sizetmp[0] > $widthtmp && $widthtmp) || ($sizetmp[1] > $heighttmp && $heighttmp)) {
                			nggpanoAdmin::resize_image( $pic_id );
                	}
                }
				
				// action hook for post process after the image is added to the database
				$image = array( 'id' => $pic_id, 'filename' => $picture, 'galleryID' => $galleryID);
				do_action('ngg_added_new_image', $image);
									
			} 
		} // is_array
        
        // delete dirsize after adding new images
        delete_transient( 'dirsize_cache' );
        
		do_action('ngg_after_new_images_added', $galleryID, $image_ids );
		
		return $image_ids;
		
	}

	/**
	 * Set correct file permissions (taken from wp core)
	 * 
	 * @class nggpanoAdmin
	 * @param string $filename
	 * @return bool $result
	 */
	function chmod($filename = '') {

		$stat = @ stat( dirname($filename) );
		$perms = $stat['mode'] & 0000666; // Remove execute bits for files
		if ( @chmod($filename, $perms) )
			return true;
			
		return false;
	}
	
	/**
	 * Check UID in folder and Script
	 * Read http://www.php.net/manual/en/features.safe-mode.php to understand safe_mode
	 * 
	 * @class nggpanoAdmin
	 * @param string $foldername
	 * @return bool $result
	 */
	function check_safemode($foldername) {

		if ( SAFE_MODE ) {
			
			$script_uid = ( ini_get('safe_mode_gid') ) ? getmygid() : getmyuid();
			$folder_uid = fileowner($foldername);

			if ($script_uid != $folder_uid) {
				$message  = sprintf(__('SAFE MODE Restriction in effect! You need to create the folder <strong>%s</strong> manually','nggallery'), $foldername);
				$message .= '<br />' . sprintf(__('When safe_mode is on, PHP checks to see if the owner (%s) of the current script matches the owner (%s) of the file to be operated on by a file function or its directory','nggallery'), $script_uid, $folder_uid );
				nggGallery::show_error($message);
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Capability check. Check is the ID fit's to the user_ID
	 * 
	 * @class nggpanoAdmin
	 * @param int $check_ID is the user_id
	 * @return bool $result
	 */
	function can_manage_this_gallery($check_ID) {
		
		global $user_ID, $wp_roles;
		
		if ( !current_user_can('NextGEN Manage others gallery') ) {
			// get the current user ID
			get_currentuserinfo();
			
			if ( $user_ID != $check_ID)
				return false;
		}
		
		return true;
	
	}
	
	/**
	 * Move images from one folder to another
	 * 
	 * @class nggpanoAdmin
	 * @param array|int $pic_ids ID's of the images
	 * @param int $dest_gid destination gallery
	 * @return void
	 */
	function move_images($pic_ids, $dest_gid) {

		$errors = '';
		$count = 0;

		if ( !is_array($pic_ids) )
			$pic_ids = array($pic_ids);
		
		// Get destination gallery
		$destination  = nggdb::find_gallery( $dest_gid );
		$dest_abspath = WINABSPATH . $destination->path;
		
		if ( $destination == null ) {
			nggGallery::show_error(__('The destination gallery does not exist','nggallery'));
			return;
		}
		
		// Check for folder permission
		if ( !is_writeable( $dest_abspath ) ) {
			$message = sprintf(__('Unable to write to directory %s. Is this directory writable by the server?', 'nggallery'), $dest_abspath );
			nggGallery::show_error($message);
			return;				
		}
				
		// Get pictures
		$images = nggdb::find_images_in_list($pic_ids);

		foreach ($images as $image) {		
			
			$i = 0;
			$tmp_prefix = '';
			
			$destination_file_name = $image->filename;
			// check if the filename already exist, then we add a copy_ prefix
			while (file_exists( $dest_abspath . '/' . $destination_file_name)) {
				$tmp_prefix = 'copy_' . ($i++) . '_';
				$destination_file_name = $tmp_prefix . $image->filename;
			}
			
			$destination_path = $dest_abspath . '/' . $destination_file_name;
			$destination_thumbnail = $dest_abspath . '/thumbs/thumbs_' . $destination_file_name;

			// Move files
			if ( !@rename($image->imagePath, $destination_path) ) {
				$errors .= sprintf(__('Failed to move image %1$s to %2$s','nggallery'), 
					'<strong>' . $image->filename . '</strong>', $destination_path) . '<br />';
				continue;				
			}
			
            // Move backup file, if possible
            @rename($image->imagePath . '_backup', $destination_path . '_backup');
			// Move the thumbnail, if possible
			@rename($image->thumbPath, $destination_thumbnail);
			
			// Change the gallery id in the database , maybe the filename
			if ( nggdb::update_image($image->pid, $dest_gid, $destination_file_name) )
				$count++;

		}

		if ( $errors != '' )
			nggGallery::show_error($errors);

		$link = '<a href="' . admin_url() . 'admin.php?page=nggallery-manage-gallery&mode=edit&gid=' . $destination->gid . '" >' . $destination->title . '</a>';
		$messages  = sprintf(__('Moved %1$s picture(s) to gallery : %2$s .','nggallery'), $count, $link);
		nggGallery::show_message($messages);

		return;
	}
	
	/**
	 * Copy images to another gallery
	 * 
	 * @class nggpanoAdmin
	 * @param array|int $pic_ids ID's of the images
	 * @param int $dest_gid destination gallery
	 * @return void
	 */
	function copy_images($pic_ids, $dest_gid) {
	   
        require_once(NGGALLERY_ABSPATH . '/lib/meta.php');
		
		$errors = $messages = '';
		
		if (!is_array($pic_ids))
			$pic_ids = array($pic_ids);
		
		// Get destination gallery
		$destination = nggdb::find_gallery( $dest_gid );
		if ( $destination == null ) {
			nggGallery::show_error(__('The destination gallery does not exist','nggallery'));
			return;
		}
		
		// Check for folder permission
		if (!is_writeable(WINABSPATH.$destination->path)) {
			$message = sprintf(__('Unable to write to directory %s. Is this directory writable by the server?', 'nggallery'), WINABSPATH.$destination->path);
			nggGallery::show_error($message);
			return;				
		}
				
		// Get pictures
		$images = nggdb::find_images_in_list($pic_ids);
		$destination_path = WINABSPATH . $destination->path;
		
		foreach ($images as $image) {		
			// WPMU action
			if ( nggWPMU::check_quota() )
				return;
			
			$i = 0;
			$tmp_prefix = ''; 
			$destination_file_name = $image->filename;
			while (file_exists($destination_path . '/' . $destination_file_name)) {
				$tmp_prefix = 'copy_' . ($i++) . '_';
				$destination_file_name = $tmp_prefix . $image->filename;
			}
			
			$destination_file_path = $destination_path . '/' . $destination_file_name;
			$destination_thumb_file_path = $destination_path . '/' . $image->thumbFolder . $image->thumbPrefix . $destination_file_name;

			// Copy files
			if ( !@copy($image->imagePath, $destination_file_path) ) {
				$errors .= sprintf(__('Failed to copy image %1$s to %2$s','nggallery'), 
					$image->filename, $destination_file_path) . '<br />';
				continue;				
			}
			
            // Copy backup file, if possible
            @copy($image->imagePath . '_backup', $destination_file_path . '_backup');
            // Copy the thumbnail if possible
			@copy($image->thumbPath, $destination_thumb_file_path);
			
			// Create new database entry for the image
			$new_pid = nggdb::insert_image( $destination->gid, $destination_file_name, $image->alttext, $image->description, $image->exclude);

			if (!isset($new_pid)) {				
				$errors .= sprintf(__('Failed to copy database row for picture %s','nggallery'), $image->pid) . '<br />';
				continue;				
			}
				
			// Copy tags
			nggTags::copy_tags($image->pid, $new_pid);
            
            // Copy meta information
            $meta = new nggMeta($image->pid);
            nggdb::update_image_meta( $new_pid, $meta->image->meta_data);
			
			if ( $tmp_prefix != '' ) {
				$messages .= sprintf(__('Image %1$s (%2$s) copied as image %3$s (%4$s) &raquo; The file already existed in the destination gallery.','nggallery'),
					 $image->pid, $image->filename, $new_pid, $destination_file_name) . '<br />';
			} else {
				$messages .= sprintf(__('Image %1$s (%2$s) copied as image %3$s (%4$s)','nggallery'),
					 $image->pid, $image->filename, $new_pid, $destination_file_name) . '<br />';
			}

		}
		
		// Finish by showing errors or success
		if ( $errors == '' ) {
			$link = '<a href="' . admin_url() . 'admin.php?page=nggallery-manage-gallery&mode=edit&gid=' . $destination->gid . '" >' . $destination->title . '</a>';
			$messages .= '<hr />' . sprintf(__('Copied %1$s picture(s) to gallery: %2$s .','nggallery'), count($images), $link);
		} 

		if ( $messages != '' )
			nggGallery::show_message($messages);

		if ( $errors != '' )
			nggGallery::show_error($errors);

		return;
	}
	
	/**
	 * Initate the Ajax operation
	 * 
	 * @class nggpanoAdmin	 
	 * @param string $operation name of the function which should be executed
	 * @param array $image_array
	 * @param string $title name of the operation
	 * @return string the javascript output
	 */
	function do_ajax_operation( $operation, $image_array, $title = '' ) {
		
		if ( !is_array($image_array) || empty($image_array) )
			return;

		$js_array  = implode('","', $image_array);
                
//                         foreach ($image_array as $value) {
//                    nggpanoAdmin::extract_gps($value);
//                }
		
		
		// send out some JavaScript, which initate the ajax operation
		?>
<!--		<script type="text/javascript">
                        console.log("do_ajax_operation");
			Images = new Array("<?php echo $js_array; ?>");

			nggAjaxOptions = {
				operation: "<?php echo $operation; ?>",
				ids: Images,		
			  	header: "<?php echo $title; ?>",
			  	maxStep: Images.length
			};
			
			jQuery(document).ready( function(){ 
				nggProgressBar.init( nggAjaxOptions );
				nggAjax.init( nggAjaxOptions );
			} );
		</script>-->
		
		<?php	
	}

	/**
	 * Return a JSON coded array of Image ids for a requested gallery
	 * 
	 * @class nggpanoAdmin
	 * @param int $galleryID
	 * @return arry (JSON)
	 */
	function get_image_ids( $galleryID ) {
		
		if ( !function_exists('json_encode') )
			return(-2);
		
		$gallery = nggdb::get_ids_from_gallery($galleryID, 'pid', 'ASC', false);

		header('Content-Type: text/plain; charset=' . get_option('blog_charset'), true);
		$output = json_encode($gallery);
		
		return $output;
	}

} // END class nggpanoAdmin

/**
 * TODO: Cannot be member of a class ? Check PCLZIP later...
 * 
 * @param mixed $p_event
 * @param mixed $p_header
 * @return
 */
function nggpano_getOnlyImages($p_event, &$p_header)	{
	return nggpanoAdmin::getOnlyImages($p_event, $p_header);
}

/**
 * Ensure after zip extraction that it could be only a image file
 * 
 * @param mixed $p_event
 * @param mixed $p_header
 * @return 1
 */
function nggpano_checkExtract($p_event, &$p_header)	{
	
    // look for valid extraction
    if ($p_header['status'] == 'ok') {
        // check if it's any image file, delete all other files
        if ( !@getimagesize ( $p_header['filename'] ))
            unlink($p_header['filename']);
    }
	
    return 1;
}
?>