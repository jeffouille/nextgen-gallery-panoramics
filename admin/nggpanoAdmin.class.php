<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

include_once ( dirname (__FILE__) . '/../lib/nggpanoPano.class.php' ); //nggpanoPano Class

/**
 * nggpanoAdmin - Class for admin operation
 * 
 * @package NGG Panoramic
 * @author Geoffroy DELEURY
 * @copyright 2011
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
                    $actions['extractgps']  = '<a class="extractgps" href="' . NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=extractgps&id=' . $pid.'" onclick="javascript:check=confirm( \'' . esc_attr(sprintf(__('Replace current GPS data for "%s" ?' , 'nggpano'), $picture->filename)). '\');if(check==false) return false;">' . __('Get GPS from picture' , 'nggpano') . '</a>';
                    $actions['pickgps']     = '<a class="nggpano-dialog" href="' . NGGPANOGALLERY_URLPATH . 'admin/pick-gps.php?id=' . $pid . '&h=600" title="' . __('Pick GPS on map','nggpano') . '">' . __('Pick GPS on map','nggpano') . '</a>';
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
                
                //URL to build the pano
                $url_build = NGGPANOGALLERY_URLPATH . 'admin/build-pano.php?id=' . $pid . '&h=340';
                
                //URL to publish the pano
                $url_publish = NGGPANOGALLERY_URLPATH . 'admin/publish-pano.php?id=' . $pid . '&h=500';
                
                //URL to publish with featured image and infocus theme meta options the pano
                $url_quickpublish = NGGPANOGALLERY_URLPATH . 'admin/publish-pano-infocus.php?id=' . $pid . '&h=500';
                
                //URL to show Pano
                $url_show = NGGPANOGALLERY_URLPATH . 'admin/show-pano.php?gid=' . $gid . '&pid=' . $pid. '&h=500&w=800';
                //$url_show = NGGPANOGALLERY_URLPATH . 'nggpanoshow.php?gid=' . $gid . '&pid=' . $pid. '&h=500&w=800';
                
                //URL to delete pano
                $url_delete = NGGPANOGALLERY_URLPATH . 'admin/ajax-actions.php?mode=delete-pano&gid=' . $gid . '&id=' . $pid;
                
                //URL to edit pano
                $url_edit = NGGPANOGALLERY_URLPATH . 'admin/edit-pano.php?id=' . $pid . '&h=500';
                
                //URL to replace original image with a redim preview
                $url_makepreview = NGGPANOGALLERY_URLPATH . 'admin/resize-preview-pano.php?id=' . $pid. '&h=200&w=800';
                
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
                    $actions['build'] = '<a class="nggpano-dialog" href="' . $url_build . '" title="' . __('Build the panoramic from this image','nggpano') . '">' . __('Build', 'nggpano') . '</a>';
                    if(!$pano_exist)
                        $actions['add']  = '<a class="nggpano-dialog" href="' . $url_edit . '" title="' . __('Add manually the panoramic datas for this image','nggpano') . '">' . __('Add manually', 'nggpano') . '</a>';
                    if($pano_exist) {
                        $actions['edit']  = '<a class="nggpano-dialog" href="' . $url_edit . '" title="' . __('Edit the panoramic datas for this image','nggpano') . '">' . __('Edit', 'nggpano') . '</a>';
                        $actions['delete']      = '<a class="submitdelete delete-pano" href="' . $url_delete. '" onclick="javascript:check=confirm( \'' . esc_attr(sprintf(__('Delete panoramas files for "%s" ?' , 'nggpano'), $picture->filename)). '\');if(check==false) return false;">' . __('Delete Pano' , 'nggpano') . '</a>';
                        $actions['show']        = '<a class="nggpano-dialog" href="' . $url_show .'" title="' . esc_attr(sprintf(__('Panorama for "%s" ?' , 'nggpano'), $picture->filename)) . '">' . __('Show', 'nggpano') . '</a>';
                        if ( current_user_can( 'publish_posts' ) )
                            $actions['publish']     = '<a class="nggpano-dialog" href="' . $url_publish .'" title="' . esc_attr(sprintf(__('Publish Panorama for "%s" ?' , 'nggpano'), $picture->filename)) . '">' . __('Publish', 'nggpano') . '</a>';
                        if( current_user_can('publish_posts') && get_current_theme() == 'inFocus')
                            $actions['quick-publish'] = '<a class="nggpano-dialog" href="' . $url_quickpublish .'" title="' . esc_attr(sprintf(__('Publish Panorama for "%s" ?' , 'nggpano'), $picture->filename)) . '">' . __('Create Article', 'nggpano') . '</a>';
                        $actions['makepreview'] = '<a class="nggpano-dialog" href="' . $url_makepreview .'" title="' . esc_attr(sprintf(__('Resize image for "%s" ?' , 'nggpano'), $picture->filename)) . '">' . __('Resize Preview', 'nggpano') . '</a>';  
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
	 * @param int $pid, Id of the image
	 * @param int (optional) $gid, id of the gallery
	 * @param decimal (optional)  $hfov, Horizontal Field Of View
         * @param decimal (optional)  $vfov, Vertical Field Of View
         * @param decimal (optional)  $voffset, Vertical Offset
	 * @return void
	 */
   	function build_pano($pid, $gid = '', $hfov = '', $vfov = '', $voffset = '') {
   	    global $wpdb;

            if($pid) {
		if(! class_exists('nggpanoPano'))
                    require_once(NGGPANOGALLERY_ABSPATH . '/lib/nggpanoPano.class.php' );              
                
                //Create pano
                $pano = new nggpanoPano($pid, $gid, $hfov, $vfov, $voffset);
                $pano->createTiles();
                echo nggpanoAdmin::krpano_image_form($pano);
            }
        }
        
        
	/**
	 * nggpanoAdmin::resize_preview_pano() - create a new image preview for panoramic, based on the height /width
	 * 
	 * @class nggpanoAdmin
	 * 
	 * @param int $pid, Id of the image
	 * @param int (optional) $gid, id of the gallery
	 * @param int (optional)  $width, Width of the new image
         * @param int (optional)  $height, Height of the new image
         * @param boolean (optional)  $backup, Make a backup of the initial image
	 * @return void
	 */
	function resize_preview_pano($pid, $gid = '', $width = 0, $height = 0, $backup = false) {
		
		global $ngg, $nggpano;
		
		if(! class_exists('ngg_Thumbnail'))
                    require_once( nggGallery::graphic_library() );

		if ( is_numeric($pid) )
                    $image = nggdb::find_image( $pid );
		
		if ( !is_object($image) ) 
                    return __('Object didn\'t contain correct data','nggallery');	
		
                //TODO : Verify the pano exist or not, if exists, do not make the new thumbnail
                if(! class_exists('nggpanoPano'))
                    require_once(NGGPANOGALLERY_ABSPATH . '/lib/nggpanoPano.class.php' );              
                //get pano
                $pano = new nggpanoPano($pid, $gid);
                $pano_exist = $pano->exists();
                
                $message = '';
                
                if ($pano_exist) {

                    // before we start we import the meta data to database (required for uploads before V1.4.0)
                    nggpanoAdmin::maybe_import_meta( $image->pid );

                    // if no parameter is set, take global settings
                    $width  = ($width  == 0) ? $nggpano->options['widthPreview']  : $width;
                    $height = ($height == 0) ? $nggpano->options['heightPreview'] : $height;

                    if (!is_writable($image->imagePath))
                            $message = ' <strong>' . $image->filename . __(' is not writeable','nggallery') . '</strong>';

                    $file = new ngg_Thumbnail($image->imagePath, TRUE);

                    // skip if file is not there
                    if (!$file->error) {

                            // If required save a backup copy of the file
                            if ( ($backup == 1) && (!file_exists($image->imagePath . '_backup')) )
                                @copy ($image->imagePath, $image->imagePath . '_backup');

                            $file->resize($width, $height);
                            //With Watermark
                            /*
                            if ($ngg->options['wmType'] == 'image') {
                                $file->watermarkImgPath = $ngg->options['wmPath'];
                                $file->watermarkImage($ngg->options['wmPos'], $ngg->options['wmXpos'], $ngg->options['wmYpos']); 
                            }
                            if ($ngg->options['wmType'] == 'text') {
                                $file->watermarkText = $ngg->options['wmText'];
                                $file->watermarkCreateText($ngg->options['wmColor'], $ngg->options['wmFont'], $ngg->options['wmSize'], $ngg->options['wmOpaque']);
                                $file->watermarkImage($ngg->options['wmPos'], $ngg->options['wmXpos'], $ngg->options['wmYpos']);  
                            }
                            
                             */
                            $file->save($image->imagePath, $ngg->options['imgQuality']);
                            // read the new sizes
                            $size = @getimagesize ( $image->imagePath );
                            // add them to the database
                            nggdb::update_image_meta($image->pid, array( 'width' => $size[0], 'height' => $size[1] ) );
                            $file->destruct();
                            $message = __('Preview make successfully','nggpano');
                    } else {
                        $file->destruct();
                        $message = ' <strong>' . $image->filename . ' (Error : ' . $file->errmsg . ')</strong>';
                    }
                    echo nggpanoAdmin::krpano_image_form($pano, $message);
                } else {
                    echo nggpanoAdmin::krpano_image_form($pano, __('You need build the pano before','nggpano'));
                }
	}
        
	/**
	 * Maybe import some meta data to the database. The functions checks the flag 'saved'
	 * and if based on compat reason (pre V1.4.0) we save then some meta datas to the database
	 * 
	 * @since V1.4.0
	 * @param int $id
	 * @return result
	 */
	function maybe_import_meta( $id ) {
				
		require_once(NGGALLERY_ABSPATH . '/lib/meta.php');
				
		$meta_obj = new nggMeta( $id );
        
		if ( $meta_obj->image->meta_data['saved'] != true ) {
            $common = $meta_obj->get_common_meta();
            //this flag will inform us that the import is already one time performed
            $common['saved']  = true; 
			$result = nggdb::update_image_meta($id, $common);
		} else
			return false;
		
		return $result;		

	}
	/**
	 * Publish a new post with the shortcode from the selected pano
	 * 
	 * @return void
	 */
    function publish_pano() {


            // Create a WP page
            global $user_ID, $ngg;

            $ngg->options['publish_width']  = (int) $_POST['width'];
            $ngg->options['publish_height'] = (int) $_POST['height'];
            $ngg->options['publish_align'] = $_POST['align'];
            $align = ( $ngg->options['publish_align'] == 'none') ? '' : 'float='.$ngg->options['publish_align'];
            
            //parameters for shortcode
            $mapw_attr       = isset ($_POST['mapw']) ? 'mapw='.$_POST['mapw'] : '';
            $maph_attr       = isset ($_POST['maph']) ? 'maph='.$_POST['maph'] : '';
            $mapz_attr       = isset ($_POST['mapz']) ? 'mapz='.$_POST['mapz'] : '';
            $maptype_attr    = isset ($_POST['maptype']) ? 'maptype='.$_POST['maptype'] : '';
            $captiontype_attr= isset ($_POST['captiontype']) && $_POST['captiontype'] != 'none' ? 'caption='.$_POST['captiontype'] : '';
                   
            //with link
            //$links_attr       = isset ($_POST['links']) ? 'links='.$_POST['links'] : '';
            $links_choice       = isset ($_POST['links']) && $_POST['links'] != 'none' ? $_POST['links'] : '';
            if ($links_choice == 'all') {
                $links_attr = 'links=all';
            } elseif ($links_choice != '') {

                $links_picture  = isset ($_POST['links_picture']) ? 'picture' : '';
                $links_map      = isset ($_POST['links_map']) ? 'map' : '';
                $links_pano     = isset ($_POST['links_pano']) ? 'pano' : '';
                
                $links_attr = ($links_picture == '') ? '' : $links_picture;
                
                $links_attr .= ($links_map == '') ? '' : ($links_attr == '' ? $links_map : '-'.$links_map);
                
                $links_attr .= ($links_pano == '') ? '' : ($links_attr == '' ? $links_pano : '-'.$links_pano);
                
               
                $links_attr = ($links_attr == '') ? '' : 'links='.$links_attr;
            }
            
            $mainlink_attr   = isset ($_POST['mainlink']) ? 'mainlink='.$_POST['mainlink'] : '';
            
            //parameters for singlmap shortcode
            $singlemapw_attr       = isset ($_POST['mapw']) ? 'w='.$_POST['mapw'] : '';
            $singlemaph_attr       = isset ($_POST['maph']) ? 'h='.$_POST['maph'] : '';
            $singlemapz_attr       = isset ($_POST['mapz']) ? 'zoom='.$_POST['mapz'] : '';
            $thumbw_attr           = 'thumbw='.$ngg->options['publish_width'];
            $thumbh_attr           = 'thumbh='.$ngg->options['publish_height'];

            //save the new values for the next operation
            update_option('ngg_options', $ngg->options);
            
            //get shortcode
            $post_content_shortcode = "";
            switch ($_POST['shortcode']) {
                case 'singlepanowithmap':
                    $post_content_shortcode = '[singlepanowithmap id=' . intval($_POST['pid']) . ' w=' . $ngg->options['publish_width'] . ' h=' . $ngg->options['publish_height'] . ' ' . $align . ' '.$captiontype_attr.' '.$mapw_attr.' '.$maph_attr.' '.$mapz_attr.' '.$maptype_attr.']';
                    break;
                
                case 'singlepicwithmap':
                    $post_content_shortcode = '[singlepicwithmap id=' . intval($_POST['pid']) . ' w=' . $ngg->options['publish_width'] . ' h=' . $ngg->options['publish_height'] . ' ' . $align . ' '.$captiontype_attr.' '.$mapw_attr.' '.$maph_attr.' '.$mapz_attr.' '.$maptype_attr.']';
                    break;
                
                case 'singlepicwithlinks':
                    $post_content_shortcode = '[singlepicwithlinks id=' . intval($_POST['pid']) . ' w=' . $ngg->options['publish_width'] . ' h=' . $ngg->options['publish_height'] . ' ' . $align . ' '.$captiontype_attr.' '.$mapz_attr.' '.$maptype_attr.' '.$links_attr.' '.$mainlink_attr.']';
                    break;
                
                case 'singlemap':
                    $post_content_shortcode = '[singlemap id=' . intval($_POST['pid']) . ' ' . $align . ' '.$singlemapw_attr.' '.$singlemaph_attr.' '.$singlemapz_attr.' '.$maptype_attr.' '.$links_attr.' '. $mainlink_attr.' '.$captiontype_attr.' '. $thumbw_attr . ' '.$thumbh_attr.']';
                    break;
                
                case 'singlepano':
                default:
                    $post_content_shortcode = '[singlepano id=' . intval($_POST['pid']) . ' w=' . $ngg->options['publish_width'] . ' h=' . $ngg->options['publish_height'] . ' ' . $align . ' ' . $captiontype_attr.']';
                    
                    break;
            }

            $post['post_type']    = 'post';
            $post['post_content'] = $post_content_shortcode;
            $post['post_author']  = $user_ID;
            $post['post_status']  = isset ( $_POST['publish_state'] ) && $_POST['publish_state'] == 'true' ? 'publish' : 'draft';
            $post['post_title']   = $_POST['post_title'];
            
            //tags
            $with_tags = (isset ($_POST['with_tags']) && $_POST['with_tags'] == '1') ? true : false;
            if ($with_tags) {
                // let's get the image data
                $picture = nggdb::find_image($_POST['pid']);
                $tags = $picture->get_tags();
                $tag_names = '';
                foreach ($tags as $tag) {
                        $tag_names .= ($tag_names=='' ? $tag->name : ', ' . $tag->name);
                }
                $post['tags_input']   =  $tag_names;
            }
            
            //Exif Date
            $use_exif_date = (isset ($_POST['exif_date']) && $_POST['exif_date'] == '1') ? true : false;
            if($picture) {
                if($use_exif_date) {
                    if($picture->imagedate) {
                        $picturedate = mysql2date('Y-m-d H:i:s', $picture->imagedate);
                        $post['post_date'] = $picturedate;
                    //'post_date' => [ Y-m-d H:i:s ]
                    }
                }
            }
            
            //category
            $category = (isset ($_POST['category'])) ? $_POST['category'] : '';
            $post['post_category'] = $category;
            
            $post = apply_filters('ngg_add_new_post', $post, $_POST['pid']);
            
            
            $post_id = wp_insert_post ($post);
            
            // Add featured image
            $featured = (isset ($_POST['featured_image']) && $_POST['featured_image'] == '1') ? true : false;
            if ($featured) {
                add_post_meta($post_id, '_thumbnail_id', 'ngg-'.$_POST['pid']);
            }

            if ($post_id != 0)
        nggGallery::show_message( __('Published a new post','nggallery') );

    }  

	/**
	 * Publish a new post with the shortcode from the selected pano with infocus post meta
	 * 
	 * @param int $pid, Id of the image
	 * @param int (optional) $gid, id of the gallery
	 * @param decimal (optional)  $hfov, Horizontal Field Of View
         * @param decimal (optional)  $vfov, Vertical Field Of View
         * @param decimal (optional)  $voffset, Vertical Offset
	 * @return void
	 */
    function publish_pano_infocus() {


            // Create a WP page
            global $user_ID, $ngg;

            $post['post_type']    = 'post';
            $post['post_content'] = $_POST['post_content'];
            $post['post_author']  = $user_ID;
            $post['post_status']  = isset ( $_POST['publish_state'] ) && $_POST['publish_state'] == 'true' ? 'publish' : 'draft';
            $post['post_title']   = $_POST['post_title'];
            
            // let's get the image data
            $picture = nggdb::find_image($_POST['pid']);
            if($picture) {
                //tags
                $with_tags = (isset ($_POST['with_tags']) && $_POST['with_tags'] == '1') ? true : false;
                if ($with_tags) {

                    $tags = $picture->get_tags();
                    $tag_names = '';
                    foreach ($tags as $tag) {
                            $tag_names .= ($tag_names=='' ? $tag->name : ', ' . $tag->name);
                    }
                    $post['tags_input']   =  $tag_names;
                }
            }
            
            //Exif Date
            $use_exif_date = (isset ($_POST['exif_date']) && $_POST['exif_date'] == '1') ? true : false;
            if($picture) {
                if($use_exif_date) {
                    if($picture->imagedate) {
                        $picturedate = mysql2date('Y-m-d H:i:s', $picture->imagedate);
                        $post['post_date'] = $picturedate;
                    //'post_date' => [ Y-m-d H:i:s ]
                    }
                }
            }
            
            //category
            $category = (isset ($_POST['category'])) ? $_POST['category'] : '';
            $post['post_category'] = $category;
            
            $post = apply_filters('ngg_add_new_post', $post, $_POST['pid']);
            
            
            $post_id = wp_insert_post ($post);
            
            //Add infocus theme option
            $post_intro_panoramic_html = '[singlepano id=' . intval($_POST['pid']) .']';
            
            $mysite_infocus_options_array = array(
                    '_intro_panoramic_html' => $post_intro_panoramic_html,
                    '_intro_text'           => 'panoramic',
                    '_disable_post_image'   => array('true'),
                    '_disable_breadcrumbs'  => array('true'),
                    '_layout'               => 'full_width'
          
            );

            # save the meta boxes
            foreach ( $mysite_infocus_options_array as $key => $value ) {

                    $old = get_post_meta( $post_id, $key, true );

                    if ( $value && $value != $old ) {
                            update_post_meta( $post_id, $key, $value );
                    } elseif ('' == $value && $old) {
                            delete_post_meta( $post_id, $key, $old );
                    }
            }
            
            // Add featured image
            $featured = (isset ($_POST['featured_image']) && $_POST['featured_image'] == '1') ? true : false;
            if ($featured) {
                add_post_meta($post_id, '_thumbnail_id', 'ngg-'.$_POST['pid']);
            }
            
            
            if ($post_id != 0)
        nggGallery::show_message( __('Published a new post','nggallery') );

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