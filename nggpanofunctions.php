<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

function getSizeForPano($size = '100%') {
    
    if(strpos($size, '%')) {
        return $size;
    } else {
        return $size . 'px';
    }
    
    
}

/**
 * nggpanoPanoramic() - show several pano based on the id list
 * 
 * @access public 
 * @param int $listIDs, list db-ID of the pano to display ex. : 1,2,5
 * @param int (optional) $width, width of the pano
 * @param int (optional) $height, height of the pano
 * @param string $float (optional) could be none, left, right
 * @param string $template (optional) name for a template file, look for panoramic-$template
 * @param string $caption (optional) additional caption text
 * @param string $link (optional) link to a other url instead the full image
 * $param string (optional) $captionmode, display or not the caption, could be full, none, title, description. Default none
 * @param int (optional) $mapwidth, width of the map
 * @param int (optional) $mapheight, height of the map
 * @param int (optional) $mapzoom, zoom level of the map
 * @param string (optional) $maptype, type of the map could be HYBRID, ROADMAP, SATELLITE, TERRAIN
 * @return the content
 */
function nggpanoPanoramic($listIDs, $width = '100%', $height = '100%', $float = '' , $template = '', $caption = '', $link = '', $captionmode = '', $mapwidth = 500, $mapheight = 500, $mapzoom = 10, $maptype = 'HYBRID') {
    global $post;
    
    require_once (dirname (__FILE__) . '/lib/nggpanoPano.class.php');
    //$ngg_options = get_option('ngg_options');
    //get the next Gen Gallery Options
    //$ngg_options = nggGallery::get_option('ngg_options');
    
    
    // get the ngg Panoramic plugin options
    $nggpano_options = get_option('nggpano_options');
    
    //get panolist
    $pano_array = explode(',',$listIDs);
    
    //remove doublon
    $pano_array = array_unique($pano_array);
    
    //to store the first gallery id of pictures list
    $gallery_id = 0;
    
    $pano_list = array();
    $pano_id_list = array();
    $map_list = array();
    $gallery_list = array();
    $map_datas_available = false;
    
    //filter $pano_array to have only id with existing pano and existing image
    foreach ($pano_array as $pano_id) {
        // get picturedata
        $picture = nggdb::find_image($pano_id);
        // if we didn't get some data, exit now
        if (!($picture == null)) {
            //Get galleryid
            //$pano_list[] = $pano_id;
            $gid = $picture->galleryid;
            $gallery_list[] = $gid;
            //new pano from pictureid
            $pano = new nggpanoPano($pano_id, $gid);
            // if we didn't get pano, exit now
            if ($pano->exists()) {
                //get all infos from DB
                $pano = $pano->getObjectFromDB();
                if ($pano) {
                    // add more variables for render output
//                    $pano->title = html_entity_decode( stripslashes(nggPanoramic::i18n($picture->alttext, 'pano_' . $picture->pid . '_alttext')) );
//                    $pano->description = html_entity_decode( stripslashes(nggPanoramic::i18n($picture->description, 'pano_' . $picture->pid . '_description')) );   
                    
                    $pano_list[$pano->pid] = $pano;
                    $pano_id_list[] = $pano->pid;
                    
                    //GPS infos
                    //Get GPS values for the current image
                    $image_values = nggpano_getImagePanoramicOptions($pano_id);
                    $lat = isset($image_values->gps_lat) ? $image_values->gps_lat : '';
                    $lng = isset($image_values->gps_lng) ? $image_values->gps_lng : '';
                    $alt = isset($image_values->gps_alt) ? $image_values->gps_alt : '';
                    if(isset($image_values->gps_lat) && isset($image_values->gps_lng))
                        $map_datas_available = true;
                    $gps = array(
                        'lat' => $lat,
                        'lng' => $lng,
                        'alt' => $alt
                    );
                    
                    $map_list[$pano->pid] = $gps;

                }
            }
        }
    }
    unset($pano_id);
    
    
    $gallery_id = (sizeof($gallery_list) > 1) ? 'several' : $gallery_list[0]; 

//    
//    $out = $listIDs.'<hr/>';
//    $out .= 'pano_array : ';
//    $out .= var_export($pano_array, true);
//    $out .= '<hr/>';
//    $out .= 'pano_id_list : ';
//    $out .= var_export($pano_id_list, true);
//    $out .= '<hr/>';
//    $out .= 'pano_list : ';
//    $out .= var_export($pano_list, true);
    
    
    if(sizeof($pano_id_list)  == 0)
        return __('[No Panoramic available]','nggpano');
    //return $out;
    
    // add float to pano
    switch ($float) {
        
        case 'left': 
            $floatpano =' nggpano-left';
        break;
        
        case 'right': 
            $floatpano =' nggpano-right';
        break;

        case 'center': 
            $floatpano =' nggpano-center';
        break;
        
        default: 
            $floatpano ='';
        break;
    }
    
    // clean captionmode if needed 
    $captionmode = ( preg_match('/(full|title|description)/i', $captionmode) ) ? $captionmode : '';
    
    $pano_config = nggpano_getPanoConfig($gallery_id);
    
    //url to get xml for krpano
    $url_xml = NGGPANOGALLERY_URLPATH . 'xml/krpano.php?pano=';
    $url_xml .= (sizeof($pano_id_list) > 1) ? 'multiple_'.implode('-', $pano_id_list) : 'single_'.implode('-', $pano_id_list);

    //Height and Width for pano div
    //get width and size
    $widthpano = getSizeForPano($width);
    $heightpano = getSizeForPano($height);
    
    // build panodiv array
    $panodiv = array(
        'classname'     => 'nggpano-panoramic'. $floatpano,
        //random id for pano div
        'contentdiv'    => 'panocontent_' . rand(),
        'swfid'         => 'krpanoSWFObject_' . rand(),
        'krpano_path'   => trailingslashit($pano_config['krpanoFolderURL']) . 'krpano.swf',
        'krpano_xml'    => $url_xml,
        'size'          => array('width' => $widthpano, 'height' => $heightpano)
    );
// 
//    $out .= '<hr/>';
//    $out .= 'panodiv : ';
//    $out .= var_export($panodiv, true);
//    
//    
    /* MAP */
    
    // add float to map
    switch ($float) {    
        case 'left': 
            $floatmap =' nggpano-map-left';
        break; 
        case 'right': 
            $floatmap =' nggpano-map-right';
        break;
        case 'center': 
            $floatmap =' nggpano-map-center';
        break;
        default: 
            $floatmap ='';
        break;
    }
    // clean maptype if needed 
    $maptype = ( preg_match('/(HYBRID|ROADMAP|SATELLITE|TERRAIN)/i', strtoupper($maptype)) ) ? strtoupper($maptype) : '';
    //Get Map infos
    $mapdiv = array(
        'available'             => $map_datas_available,
        'width'                 => getSizeForPano($mapwidth),
        'height'                => getSizeForPano($mapheight),
        'zoom'                  => $mapzoom,
        'classname'             => 'nggpano-map'. $floatmap,
        'maptype'               => $maptype,
        'div_id'                => 'map_pic_' . rand(),
        'thumbinfowindowurl'    => trailingslashit( home_url() ) . 'index.php?callback=image&amp;width=200&amp;pid=',
        'map_list'              => $map_list
    );
    
//    $out .= '<hr/>';
//    $out .= 'mapdiv : ';
//    $out .= var_export($mapdiv, true);
    
    //Caption in shortcode
    $caption = nggPanoramic::i18n($caption);
    
    // look for panoramic-$template.php or pure panoramic.php
    $filename = ( empty($template) ) ? 'panoramic' : 'panoramic-' . $template;

    // create the output
    $out = nggPanoramic::capture ( $filename, array (
                                               'panodiv'       => $panodiv,
                                               'pano_list'     => $pano_list,
                                               'captionmode'   => $captionmode,
                                               'mapdiv'        => $mapdiv,
                                               'float'         => $floatpano,
                                               'caption'       => $caption)
                                );

    //$out = apply_filters('nggpano_show_panoramic_content', $out, $picture );
    
    return $out;
}

/**
 * nggpanoGallery() - show gallery of panoramic
 * 
 * @access public 
 * @param int $gid, gallery id
 * @param int (optional) $width, width of the pano
 * @param int (optional) $height, height of the pano
 * @param string $float (optional) could be none, left, right
 * @param string $template (optional) name for a template file, look for panoramic-$template
 * @param string $caption (optional) additional caption text
 * @param string $link (optional) link to a other url instead the full image
 * $param string (optional) $captionmode, display or not the caption, could be full, none, title, description. Default none
 * @param int (optional) $mapwidth, width of the map
 * @param int (optional) $mapheight, height of the map
 * @param int (optional) $mapzoom, zoom level of the map
 * @param string (optional) $maptype, type of the map could be HYBRID, ROADMAP, SATELLITE, TERRAIN
 * @return the content
 */
function nggpanoGallery($gid, $width = '100%', $height = '100%', $float = '' , $template = '', $caption = '', $link = '', $captionmode = '', $mapwidth = 500, $mapheight = 500, $mapzoom = 10, $maptype = 'HYBRID') {
    global $post;
    
    require_once (dirname (__FILE__) . '/lib/nggpanoPano.class.php');
    //$ngg_options = get_option('ngg_options');
    //get the next Gen Gallery Options
    //$ngg_options = nggGallery::get_option('ngg_options');
    
    
    // get the ngg Panoramic plugin options
    $nggpano_options = get_option('nggpano_options');
    // get the ngggallery options
    $ngg_options = nggGallery::get_option('ngg_options');
    
    //Set sort order value, if not used (upgrade issue)
    $ngg_options['galSort'] = ($ngg_options['galSort']) ? $ngg_options['galSort'] : 'pid';
    $ngg_options['galSortDir'] = ($ngg_options['galSortDir'] == 'DESC') ? 'DESC' : 'ASC';
    
    // get gallery values
    //TODO: Use pagination limits here to reduce memory needs
    $picturelist = nggdb::get_gallery($gid, $ngg_options['galSort'], $ngg_options['galSortDir']);

    if ( !$picturelist )
        return __('[Gallery not found]','nggallery');

    $pano_array = array();
    foreach ($picturelist as $key => $Image) {
        $pano_array[] = $key;
    }
    
    $listIDs = implode(',', $pano_array);

    return nggpanoPanoramic($listIDs, $width , $height , $float , $template , $caption , $link , $captionmode , $mapwidth , $mapheight , $mapzoom , $maptype );

}

/**
 * nggpanoSinglePictureWithMap() - show a single picture based on the id with map under
 * 
 * @access public 
 * @param int $imageID, db-ID of the image
 * @param int (optional) $width, width of the image
 * @param int (optional) $height, height of the image
 * @param string $mode (optional) could be none, watermark, web20
 * @param string $float (optional) could be none, left, right
 * @param string $template (optional) name for a template file, look for singlepic-$template
 * @param string $caption (optional) additional caption text
 * @param string $link (optional) link to a other url instead the full image
 * @param int (optional) $mapwidth, width of the map
 * @param int (optional) $mapheight, height of the map
 * @param int (optional) $mapzoom, zoom level of the map
 * @param string (optional) $maptype, type of the map could be HYBRID, ROADMAP, SATELLITE, TERRAIN
 * $param string (optional) $captionmode, display or not the caption, could be full, none, title, description. Default none
 * @return the content
 */
function nggpanoSinglePictureWithMap($imageID, $width = 250, $height = 250, $mode = '', $float = '' , $template = '', $caption = '', $link = '', $mapwidth = 250, $mapheight = 250, $mapzoom = 10, $maptype = 'HYBRID', $captionmode = '') {
    global $post;
    
    //$ngg_options = nggGallery::get_option('ngg_options');
    
    // get picturedata
    $picture = nggdb::find_image($imageID);
    
    // if we didn't get some data, exit now
    if ($picture == null)
        return __('[SinglePic not found]','nggallery');
            
    // add float to img
    switch ($float) {
        
        case 'left': 
            $floatpic =' ngg-left';
        break;
        
        case 'right': 
            $floatpic =' ngg-right';
        break;

        case 'center': 
            $floatpic =' ngg-center';
        break;
        
        default: 
            $floatpic ='';
        break;
    }
    
    // clean mode if needed 
    $mode = ( preg_match('/(web20|watermark)/i', $mode) ) ? $mode : '';
    
    //let's initiate the url
    $picture->thumbnailURL = false;

    // check fo cached picture
    if ( $post->post_status == 'publish' )
        $picture->thumbnailURL = $picture->cached_singlepic_file($width, $height, $mode );
    
    // if we didn't use a cached image then we take the on-the-fly mode 
    if (!$picture->thumbnailURL) 
        $picture->thumbnailURL = trailingslashit( home_url() ) . 'index.php?callback=image&amp;pid=' . $imageID . '&amp;width=' . $width . '&amp;height=' . $height . '&amp;mode=' . $mode;

    // add more variables for render output
    $picture->imageURL = ( empty($link) ) ? $picture->imageURL : $link;
    $picture->href_link = $picture->get_href_link();
    $picture->alttext = html_entity_decode( stripslashes(nggGallery::i18n($picture->alttext, 'pic_' . $picture->pid . '_alttext')) );
    $picture->linktitle = htmlspecialchars( stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')) );
    $picture->description = html_entity_decode( stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')) );
    $picture->classname = 'ngg-singlepic'. $floatpic;
    $picture->thumbcode = $picture->get_thumbcode( 'singlepic' . $imageID);
    $picture->height = (int) $height;
    $picture->width = (int) $width;
    $picture->caption = nggPanoramic::i18n($caption);

    // filter to add custom content for the output
    $picture = apply_filters('ngg_image_object', $picture, $imageID);

    // let's get the meta data
    $meta = new nggMeta($imageID);
    $meta->sanitize();
    $exif = $meta->get_EXIF();
    $iptc = $meta->get_IPTC();
    $xmp  = $meta->get_XMP();
    $db   = $meta->get_saved_meta();
    
    //if we get no exif information we try the database 
    $exif = ($exif == false) ? $db : $exif;
	       
    // look for singlepic-$template.php or pure singlepic.php
    $filename = ( empty($template) ) ? 'singlepic' : 'singlepic-' . $template;
    
    
    // clean captionmode if needed 
    $captionmode = ( preg_match('/(full|title|description)/i', $captionmode) ) ? $captionmode : '';
    
    /* MAP */

    //Get GPS values for the current image
    $image_values = nggpano_getImagePanoramicOptions($imageID);
    $lat = isset($image_values->gps_lat) ? $image_values->gps_lat : '';
    $lng = isset($image_values->gps_lng) ? $image_values->gps_lng : '';
    $alt = isset($image_values->gps_alt) ? $image_values->gps_alt : '';
    $gps = array(
        'lat' => $lat,
        'lng' => $lng,
        'alt' => $alt
    );
    
    // add float to map
    switch ($float) {
        
        case 'left': 
            $floatmap =' nggpano-map-left';
        break;
        
        case 'right': 
            $floatmap =' nggpano-map-right';
        break;

        case 'center': 
            $floatmap =' nggpano-map-center';
        break;
        
        default: 
            $floatmap ='';
        break;
    }
    
    // clean maptype if needed 
    $maptype = ( preg_match('/(HYBRID|ROADMAP|SATELLITE|TERRAIN)/i', strtoupper($maptype)) ) ? strtoupper($maptype) : '';
    //Get Map infos

    $mapinfos = array(
        'width'     => getSizeForPano($mapwidth),
        'height'    => getSizeForPano($mapheight),
        'zoom'      => $mapzoom,
        'classname' => 'nggpano-map'. $floatmap,
        'maptype'   => $maptype,
        'div_id'    => 'map_pic_' . rand() . '_' . $picture->pid,
        'thumbinfowindow' => trailingslashit( home_url() ) . 'index.php?callback=image&amp;pid=' . $imageID . '&amp;width=200'
    );

    // create the output
    $out = nggPanoramic::capture ( $filename, 
            array (
                'image' => $picture ,
                'meta' => $meta,
                'exif' => $exif,
                'iptc' => $iptc,
                'xmp' => $xmp,
                'db' => $db,
                'mapinfos' => $mapinfos,
                'gps' => $gps,
                'captionmode' => $captionmode,
                'float'    => $float
                )
            );

    $out = apply_filters('ngg_show_singlepic_content', $out, $picture );
    
    return $out;
}

/**
 * nggpanoSinglePictureWithLinks() - show a single picture based on the id
 * 
 * @access public 
 * @param int $imageID, db-ID of the image
 * @param int (optional) $width, width of the image
 * @param int (optional) $height, height of the image
 * @param string $mode (optional) could be none, watermark, web20
 * @param string $float (optional) could be none, left, right
 * @param string $template (optional) name for a template file, look for singlepic-$template
 * @param string $caption (optional) additional caption text
 * @param int (optional) $mapzoom, zoom level of the map
 * @param string (optional) $maptype, type of the map could be HYBRID, ROADMAP, SATELLITE, TERRAIN
 * @param string (optional) $links, links to display under the thumbnail, could be all, picture, map, pano, picture&map, picture&pano, map&pano. Default all
 * @param string (optional) $mainlink, link to follow when click on the thumbnail, could be picture, map, pano, none. Default none
 * $param string (optional) $captionmode, display or not the caption, could be full, none, title, description. Default none
 * @return the content
 */
function nggpanoSinglePictureWithLinks($imageID, $width = 250, $height = 250, $mode = '', $float = '' , $template = '', $caption = '', $mapzoom = 10, $maptype = 'HYBRID', $links = 'ALL', $mainlink = '', $captionmode = '') {
    global $post;
    
    $nggpano_options = nggGallery::get_option('nggpano_options');
    
    /* PICTURE */
    // get picturedata
    $picture = nggdb::find_image($imageID);
    
    // if we didn't get some data, exit now
    if ($picture == null)
        return __('[SinglePic not found]','nggallery');
            
    // add float to img
    switch ($float) {
        
        case 'left': 
            $floatpic =' ngg-left';
            $floatlinks = ' nggpano-links-left';
        break;
        
        case 'right': 
            $floatpic =' ngg-right';
            $floatlinks = ' nggpano-links-right';
        break;

        case 'center': 
            $floatpic =' ngg-center';
            $floatlinks = ' nggpano-links-center';
        break;
        
        default: 
            $floatpic ='';
            $floatlinks = '';
        break;
    }
    
    // clean mode if needed 
    $mode = ( preg_match('/(web20|watermark)/i', $mode) ) ? $mode : '';
    
    //let's initiate the url
    $picture->thumbnailURL = false;

    // check fo cached picture
    if ( $post->post_status == 'publish' )
        $picture->thumbnailURL = $picture->cached_singlepic_file($width, $height, $mode );
    
    // if we didn't use a cached image then we take the on-the-fly mode 
    if (!$picture->thumbnailURL) 
        $picture->thumbnailURL = trailingslashit( home_url() ) . 'index.php?callback=image&amp;pid=' . $imageID . '&amp;width=' . $width . '&amp;height=' . $height . '&amp;mode=' . $mode;

    // add more variables for render output
    $picture->imageURL = ( empty($link) ) ? $picture->imageURL : $link;
    $picture->href_link = $picture->get_href_link();
    $picture->alttext = html_entity_decode( stripslashes(nggGallery::i18n($picture->alttext, 'pic_' . $picture->pid . '_alttext')) );
    $picture->linktitle = htmlspecialchars( stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')) );
    $picture->description = html_entity_decode( stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')) );
    $picture->classname = 'ngg-singlepic'. $floatpic;
    $picture->thumbcode = $picture->get_thumbcode( 'singlepic' . $imageID);
    $picture->height = (int) $height;
    $picture->width = (int) $width;
    $picture->caption = nggPanoramic::i18n($caption);

    // filter to add custom content for the output
    $picture = apply_filters('ngg_image_object', $picture, $imageID);

    // let's get the meta data
    $meta = new nggMeta($imageID);
    $meta->sanitize();
    $exif = $meta->get_EXIF();
    $iptc = $meta->get_IPTC();
    $xmp  = $meta->get_XMP();
    $db   = $meta->get_saved_meta();
    
    //if we get no exif information we try the database 
    $exif = ($exif == false) ? $db : $exif;
	       
    // look for singlepicwithlinks-$template.php or pure singlepicwithlinks.php
    $filename = ( empty($template) ) ? 'singlepicwithlinks' : 'singlepicwithlinks-' . $template;
    
    // clean captionmode if needed 
    $captionmode = ( preg_match('/(full|title|description)/i', $captionmode) ) ? $captionmode : '';

    /* PANO */
    //Get galleryid
    $gid = $picture->galleryid;
    
    //new pano from pictureid
    $pano = new nggpanoPano($imageID, $gid);
    // if we didn't get pano, exit now
    $panoexist = $pano->exists();
    if ($panoexist) {
        $panoobj = $pano->getObjectFromDB();
    
        if ($panoobj) {
            // add more variables for render output
            $pano->title = html_entity_decode( stripslashes(nggPanoramic::i18n($picture->alttext, 'pano_' . $picture->pid . '_alttext')) );
            $pano->description = html_entity_decode( stripslashes(nggPanoramic::i18n($picture->description, 'pano_' . $picture->pid . '_description')) );
            $pano->caption = nggPanoramic::i18n($caption);
            $pano->contentdiv = 'panocontent_' . rand() . '_' . $picture->pid;
            $pano->swfid = 'krpanoSWFObject_' . rand() . '_' . $picture->pid;
            $pano->krpano_path    = trailingslashit($pano->krpanoFolderURL) . $pano->krpanoSWF;
            $pano->krpano_xml     = NGGPANOGALLERY_URLPATH . 'xml/krpano.php?pano=single_'.$pano->pid;
        }
    }
    
    /* MAP */

    //Get GPS values for the current image
    $image_values = nggpano_getImagePanoramicOptions($imageID);
    $lat = isset($image_values->gps_lat) ? $image_values->gps_lat : '';
    $lng = isset($image_values->gps_lng) ? $image_values->gps_lng : '';
    $alt = isset($image_values->gps_alt) ? $image_values->gps_alt : '';
    $gps = array(
        'lat' => $lat,
        'lng' => $lng,
        'alt' => $alt
    );

    
    // clean maptype if needed 
    $maptype = ( preg_match('/(HYBRID|ROADMAP|SATELLITE|TERRAIN)/i', strtoupper($maptype)) ) ? strtoupper($maptype) : '';
    //Get Map infos
    $mapinfos = array(
        'zoom'      => $mapzoom,
        'maptype'   => $maptype,
        'div_id'    => 'map_pic_' . rand() . '_' . $picture->pid
    );
    
    $mapavailable = false;
    if(is_array($gps) && (isset($gps["lat"]) && strlen($gps["lat"]) > 0) && (isset($gps["lng"]) && strlen($gps["lng"]) > 0))
        $mapavailable = true;
    
    
    
    /* LINKS TO SHOW */
    $links_to_show = array(
        'picture'   => array('available' => false, 'url' => ''),
        'map'       => array('available' => false, 'url' => ''),
        'pano'      => array('available' => false, 'url' => '')
    );
    

    // clean links if needed
    $links = ( preg_match('/(ALL|PICTURE|MAP|PANO)/i', strtoupper($links)) ) ? strtoupper($links) : '';
    $links_array = explode('-', strtoupper($links));
    if (in_array('PICTURE', $links_array)) {
        $links_to_show['picture']['available']= true;
    }
    if (in_array('MAP', $links_array)) {
        if($mapavailable)
            $links_to_show['map']['available']= true;
    }
    if (in_array('PANO', $links_array)) {
        if($panoexist && $panoobj)
            $links_to_show['pano']['available']= true;
    }
    if (in_array('ALL', $links_array)) {
        $links_to_show['picture']['available']= true;
        if($mapavailable)
            $links_to_show['map']['available']= true;
        if($panoexist && $panoobj)
            $links_to_show['pano']['available']= true;
    }
    
    //set url
    $picture_href = $picture->imageURL;
    $pano_href = NGGPANOGALLERY_URLPATH . 'nggpanoshow.php?gid=' . $pano->gid . '&pid=' . $pano->pid;
    $map_href = NGGPANOGALLERY_URLPATH . 'nggpanomap.php?pid=' . $pano->pid . '&type=' . $maptype . '&zoom=' . $mapzoom;
    
    //get lightbox effect
    $lightboxEffect = $nggpano_options['lightboxEffect'];

    switch ($lightboxEffect) {
        case 'colorbox':
            $links_to_show['picture']['url'] = 'class="colorbox" rel="colorbox[picture]" href="' . $picture_href . '"' ;
            $links_to_show['map']['url'] = 'class="colorboxmap" rel="colorbox[map]" href="' . $map_href . '"' ;
            $links_to_show['pano']['url'] = 'class="colorboxpano" rel="colorbox[pano]" href="' . $pano_href . '"';
            break;
        
        case 'fancybox':
            $links_to_show['picture']['url'] = 'class="fancybox" rel="fancybox[picture]" href="' . $picture_href . '"' ;
            $links_to_show['map']['url'] = 'class="fancyboxmap" rel="fancybox[map]" href="' . $map_href . '"' ;
            $links_to_show['pano']['url'] = 'class="fancyboxpano" rel="fancybox[pano]" href="' . $pano_href . '"';
            break;

        case 'thickbox':
        default:
            $links_to_show['picture']['url'] = 'class="thickbox" rel="thickbox[picture]" href="' . $picture_href . '"' ;
            $links_to_show['map']['url'] = 'class="thickbox" rel="thickbox[map]" href="' . $map_href . '&init=true&height=600&width=600" ';
            $links_to_show['pano']['url'] = 'class="thickbox" rel="thickbox[pano]" href="' . $pano_href. '&height=600&width=800"';
            break;
    }
    
    /* MAINLINK */
    $mainlink = ( preg_match('/(PICTURE|MAP|PANO)/i', strtoupper($mainlink)) ) ? strtoupper($mainlink) : '';
    switch ($mainlink) {
        case 'PICTURE':
            $mainlink = str_replace('[picture]"', '[mainpicture]"', $links_to_show['picture']['url']);
            break;
        case 'MAP':
            if($mapavailable)
                $mainlink = str_replace('[map]"', '[mainmap]"', $links_to_show['map']['url'] );
            else
                $mainlink = str_replace('[picture]"', '[mainpicture]"', $links_to_show['picture']['url']);
            break;
        case 'PANO':
            if($panoexist && $panoobj)
                $mainlink = str_replace('[pano]"', '[mainpano]"', $links_to_show['pano']['url']);
            else
                $mainlink = str_replace('[picture]"', '[mainpicture]"', $links_to_show['picture']['url']);
            break;
        default:
            $mainlink = '' ;
            break;
    }

    //xml=krpano.xml
    //$links_to_show['pano']['custom_url'] = $pano->krpano_path . "&" . $pano->krpano_xml;
////URL to show Pano
//$url_show = NGGPANOGALLERY_URLPATH . 'admin/show-pano.php?gid=' . $pano->gid . '&pid=' . $pano->pid. '&h=500&w=800';
    //<a rel="prettyPhoto[ajax]" href="/demos/prettyPhoto-jquery-lightbox-clone/xhr_response.html?ajax=true&width=325&height=185">Ajax content</a>
    ////    foreach ($links_array as $link) {
//        $link = ( preg_match('/(ALL|PICTURE|MAP|PANO)/i', strtoupper($link)) ) ? strtoupper($link) : '';
//    }
    
    
    

    // create the output
    $out = nggPanoramic::capture ( $filename, array (
                                                'image'     => $picture ,
                                                'meta'      => $meta,
                                                'exif'      => $exif,
                                                'iptc'      => $iptc,
                                                'xmp'       => $xmp,
                                                'db'        => $db,
                                                'mapinfos'  => $mapinfos,
                                                'gps'       => $gps,
                                                'pano'      => $pano,
                                                'links'     => $links_to_show,
                                                'mainlink'  => $mainlink,
                                                'captionmode' => $captionmode,
                                                'floatlinks'     => $floatlinks,
                                                'float'     => $float
                                              )
                                );

    
    return $out;
}


/**
 * nggpanoSingleMap() - show a map with single picture on infowindow
 * 
 * @access public 
 * @param int $imageID, db-ID of the image
 * @param int (optional) $mapwidth, width of the map
 * @param int (optional) $mapheight, height of the map
 * @param int (optional) $mapzoom, zoom level of the map
 * @param string $float (optional) could be none, left, right
 * @param string $template (optional) name for a template file, look for singlepic-$template
 * @param string $caption (optional) additional caption text
 * @param string (optional) $link, link on the thumbnail picture, map, pano. Default picture
 * @param int (optional) $thumbw, width of the thumbnail inside infowindow
 * @param int (optional) $thumbh, height of the thumbnail inside infowindow
 * @param string (optional) $links, links to display under the thumbnail, could be all, picture, map, pano, picture&map, picture&pano, map&pano. Default all
 * @param string (optional) $mainlink, link to follow when click on the thumbnail, could be picture, map, pano, none. Default none
 * $param string (optional) $captionmode, display or not the caption, could be full, none, title, description. Default none

 * @param string (optional) $maptype, type of the map could be HYBRID, ROADMAP, SATELLITE, TERRAIN
 * @return the content
 */

function nggpanoSingleMap($imageID, $mapwidth = 250, $mapheight = 250, $mapzoom = 10, $maptype = 'HYBRID', $float = '', $template = '', $caption = '', $thumbw = 200, $thumbh = 200, $links = 'ALL', $mainlink = '', $captionmode = '' ) {
    global $post;
    
    $nggpano_options = nggGallery::get_option('nggpano_options');
    
    /* PICTURE */
    // get picturedata
    $picture = nggdb::find_image($imageID);
    
    // if we didn't get some data, exit now
    if ($picture == null)
        return __('[SinglePic not found]','nggallery');
            
    // add float to img
    switch ($float) {
        
        case 'left': 
            $floatpic =' ngg-left';
            $floatlinks = ' nggpano-links-left';
        break;
        
        case 'right': 
            $floatpic =' ngg-right';
            $floatlinks = ' nggpano-links-right';
        break;

        case 'center': 
            $floatpic =' ngg-center';
            $floatlinks = ' nggpano-links-center';
        break;
        
        default: 
            $floatpic ='';
            $floatlinks = '';
        break;
    }
    
    //let's initiate the url
    $picture->thumbnailURL = false;

    // check fo cached picture
    if ( $post->post_status == 'publish' )
        $picture->thumbnailURL = $picture->cached_singlepic_file($thumbw, $thumbh, '' );
    
    // if we didn't use a cached image then we take the on-the-fly mode 
    if (!$picture->thumbnailURL) 
        $picture->thumbnailURL = trailingslashit( home_url() ) . 'index.php?callback=image&amp;pid=' . $imageID . '&amp;width=' . $thumbw . '&amp;height=' . $thumbh;

    // add more variables for render output
    $picture->imageURL = $picture->imageURL;
    $picture->href_link = $picture->get_href_link();
    $picture->alttext = html_entity_decode( stripslashes(nggGallery::i18n($picture->alttext, 'pic_' . $picture->pid . '_alttext')) );
    $picture->linktitle = htmlspecialchars( stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')) );
    $picture->description = html_entity_decode( stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')) );
    $picture->classname = 'ngg-singlepic'. $floatpic;
    $picture->thumbcode = $picture->get_thumbcode( 'singlepic' . $imageID);
    $picture->height = (int) $thumbh;
    $picture->width = (int) $thumbw;
    $picture->caption = nggPanoramic::i18n($caption);

    // filter to add custom content for the output
    $picture = apply_filters('ngg_image_object', $picture, $imageID);

    // let's get the meta data
    $meta = new nggMeta($imageID);
    $meta->sanitize();
    $exif = $meta->get_EXIF();
    $iptc = $meta->get_IPTC();
    $xmp  = $meta->get_XMP();
    $db   = $meta->get_saved_meta();
    
    //if we get no exif information we try the database 
    $exif = ($exif == false) ? $db : $exif;
	       
    // look for singlemap-$template.php or pure singlemap.php
    $filename = ( empty($template) ) ? 'singlemap' : 'singlemap-' . $template;
    
    // clean captionmode if needed 
    $captionmode = ( preg_match('/(full|title|description)/i', $captionmode) ) ? $captionmode : '';

 
    /* PANO */
    //Get galleryid
    $gid = $picture->galleryid;
    
    //new pano from pictureid
    $pano = new nggpanoPano($imageID, $gid);
    // if we didn't get pano, exit now
    $panoexist = $pano->exists();
    if ($panoexist) {
        $panoobj = $pano->getObjectFromDB();
    
        if ($panoobj) {
            // add more variables for render output
//            $pano->title = html_entity_decode( stripslashes(nggPanoramic::i18n($picture->alttext, 'pano_' . $picture->pid . '_alttext')) );
//            $pano->description = html_entity_decode( stripslashes(nggPanoramic::i18n($picture->description, 'pano_' . $picture->pid . '_description')) );
            $pano->caption = nggPanoramic::i18n($caption);
            $pano->contentdiv = 'panocontent_' . rand() . '_' . $picture->pid;
            $pano->swfid = 'krpanoSWFObject_' . rand() . '_' . $picture->pid;
            $pano->krpano_path    = trailingslashit($pano->krpanoFolderURL) . $pano->krpanoSWF;
            $pano->krpano_xml     = NGGPANOGALLERY_URLPATH . 'xml/krpano.php?pano=single_'.$pano->pid;
        }
    }
    
    /* MAP */

    //Get GPS values for the current image
    $image_values = nggpano_getImagePanoramicOptions($imageID);
    $lat = isset($image_values->gps_lat) ? $image_values->gps_lat : '';
    $lng = isset($image_values->gps_lng) ? $image_values->gps_lng : '';
    $alt = isset($image_values->gps_alt) ? $image_values->gps_alt : '';
    $gps = array(
        'lat' => $lat,
        'lng' => $lng,
        'alt' => $alt
    );
    
    // add float to map
    switch ($float) {
        
        case 'left': 
            $floatmap =' nggpano-map-left';
        break;
        
        case 'right': 
            $floatmap =' nggpano-map-right';
        break;

        case 'center': 
            $floatmap =' nggpano-map-center';
        break;
        
        default: 
            $floatmap ='';
        break;
    }
    // clean maptype if needed 
    $maptype = ( preg_match('/(HYBRID|ROADMAP|SATELLITE|TERRAIN)/i', strtoupper($maptype)) ) ? strtoupper($maptype) : '';
    //Get Map infos
    
    $mapinfos = array(
        'width'     => getSizeForPano($mapwidth),
        'height'    => getSizeForPano($mapheight),
        'zoom'      => $mapzoom,
        'classname' => 'nggpano-map'. $floatmap,
        'maptype'   => $maptype,
        'div_id'    => 'map_pic_' . rand() . '_' . $picture->pid,
        'thumbinfowindow' => trailingslashit( home_url() ) . 'index.php?callback=image&amp;pid=' . $imageID . '&amp;width=' . $thumbw . '&amp;height=' . $thumbh
    );
    
    $mapavailable = false;
    if(is_array($gps) && (isset($gps["lat"]) && strlen($gps["lat"]) > 0) && (isset($gps["lng"]) && strlen($gps["lng"]) > 0))
        $mapavailable = true;
    
    
    
    /* LINKS TO SHOW */
    $links_to_show = array(
        'picture'   => array('available' => false, 'url' => ''),
        'map'       => array('available' => false, 'url' => ''),
        'pano'      => array('available' => false, 'url' => '')
    );
    

    // clean links if needed
    $links = ( preg_match('/(ALL|PICTURE|MAP|PANO)/i', strtoupper($links)) ) ? strtoupper($links) : '';
    $links_array = explode('-', strtoupper($links));
    if (in_array('PICTURE', $links_array)) {
        $links_to_show['picture']['available']= true;
    }
    if (in_array('MAP', $links_array)) {
        if($mapavailable)
            $links_to_show['map']['available']= true;
    }
    if (in_array('PANO', $links_array)) {
        if($panoexist && $panoobj)
            $links_to_show['pano']['available']= true;
    }
    if (in_array('ALL', $links_array)) {
        $links_to_show['picture']['available']= true;
        if($mapavailable)
            $links_to_show['map']['available']= true;
        if($panoexist && $panoobj)
            $links_to_show['pano']['available']= true;
    }
    
    //set url
    $picture_href = $picture->imageURL;
    $pano_href = NGGPANOGALLERY_URLPATH . 'nggpanoshow.php?gid=' . $pano->gid . '&pid=' . $pano->pid;
    $map_href = NGGPANOGALLERY_URLPATH . 'nggpanomap.php?pid=' . $pano->pid . '&type=' . $maptype . '&zoom=' . $mapzoom;
    
    //get lightbox effect
    $lightboxEffect = $nggpano_options['lightboxEffect'];

    switch ($lightboxEffect) {
        case 'colorbox':
            $links_to_show['picture']['url'] = 'class="colorbox" rel="colorbox[picture]" href="' . $picture_href . '"' ;
            $links_to_show['map']['url'] = 'class="colorboxmap" rel="colorbox[map]" href="' . $map_href . '"' ;
            $links_to_show['pano']['url'] = 'class="colorboxpano" rel="colorbox[pano]" href="' . $pano_href . '"';
            break;
        
        case 'fancybox':
            $links_to_show['picture']['url'] = 'class="fancybox" rel="fancybox[picture]" href="' . $picture_href . '"' ;
            $links_to_show['map']['url'] = 'class="fancyboxmap" rel="fancybox[map]" href="' . $map_href . '"' ;
            $links_to_show['pano']['url'] = 'class="fancyboxpano" rel="fancybox[pano]" href="' . $pano_href . '"';
            break;

        case 'thickbox':
        default:
            $links_to_show['picture']['url'] = 'class="thickbox" rel="thickbox[picture]" href="' . $picture_href . '"' ;
            $links_to_show['map']['url'] = 'class="thickbox" rel="thickbox[map]" href="' . $map_href . '&init=true&height=600&width=600" ';
            $links_to_show['pano']['url'] = 'class="thickbox" rel="thickbox[pano]" href="' . $pano_href. '&height=600&width=800"';
            break;
    }
    
    /* MAINLINK */
    $mainlink = ( preg_match('/(PICTURE|MAP|PANO)/i', strtoupper($mainlink)) ) ? strtoupper($mainlink) : '';
    switch ($mainlink) {
        case 'PICTURE':
            $mainlink = str_replace('[picture]"', '[mainpicture]"', $links_to_show['picture']['url']);
            break;
        case 'MAP':
            if($mapavailable)
                $mainlink = str_replace('[map]"', '[mainmap]"', $links_to_show['map']['url'] );
            else
                $mainlink = str_replace('[picture]"', '[mainpicture]"', $links_to_show['picture']['url']);
            break;
        case 'PANO':
            if($panoexist && $panoobj)
                $mainlink = str_replace('[pano]"', '[mainpano]"', $links_to_show['pano']['url']);
            else
                $mainlink = str_replace('[picture]"', '[mainpicture]"', $links_to_show['picture']['url']);
            break;
        default:
            $mainlink = '' ;
            break;
    }
  
    // create the output
    $out = nggPanoramic::capture ( $filename, array (
                                                'image'     => $picture ,
                                                'meta'      => $meta,
                                                'exif'      => $exif,
                                                'iptc'      => $iptc,
                                                'xmp'       => $xmp,
                                                'db'        => $db,
                                                'mapinfos'  => $mapinfos,
                                                'gps'       => $gps,
                                                'pano'      => $pano,
                                                'links'     => $links_to_show,
                                                'mainlink'  => $mainlink,
                                                'captionmode' => $captionmode,
                                                'float'     => $floatlinks
                                              )
                                );

    
    return $out;
}


/**
 * nggpanoPanoramic() - show several pano based on the id list
 * 
 * @access public 
 * @param int $listIDs, list db-ID of the pano to display ex. : 1,2,5
 * @param int (optional) $width, width of the pano
 * @param int (optional) $height, height of the pano
 * @param string $float (optional) could be none, left, right
 * @param string $template (optional) name for a template file, look for panoramic-$template
 * @param string $caption (optional) additional caption text
 * @param string $link (optional) link to a other url instead the full image
 * $param string (optional) $captionmode, display or not the caption, could be full, none, title, description. Default none
 * @param int (optional) $mapwidth, width of the map
 * @param int (optional) $mapheight, height of the map
 * @param int (optional) $mapzoom, zoom level of the map
 * @param string (optional) $maptype, type of the map could be HYBRID, ROADMAP, SATELLITE, TERRAIN
 * @return the content
 */
function nggpanoSingleMap_new($listIDs, $mapwidth = 250, $mapheight = 250, $mapzoom = 10, $maptype = 'HYBRID', $float = '', $template = '', $caption = '', $thumbw = 200, $thumbh = 200, $links = 'ALL', $mainlink = '', $captionmode = '' ) {

//function nggpanoPanoramic($listIDs, $width = '100%', $height = '100%', $float = '' , $template = '', $caption = '', $link = '', $captionmode = '', $mapwidth = 500, $mapheight = 500, $mapzoom = 10, $maptype = 'HYBRID') {
    global $post;
    
    require_once (dirname (__FILE__) . '/lib/nggpanoPano.class.php');
    //$ngg_options = get_option('ngg_options');
    //get the next Gen Gallery Options
    //$ngg_options = nggGallery::get_option('ngg_options');
    
    
    // get the ngg Panoramic plugin options
    $nggpano_options = get_option('nggpano_options');
    
    //get panolist
    $pano_array = explode(',',$listIDs);
    
    //remove doublon
    $pano_array = array_unique($pano_array);
    
    //to store the first gallery id of pictures list
    $gallery_id = 0;
    
    $pano_list = array();
    $pano_id_list = array();
    $map_list = array();
    $gallery_list = array();
    $map_datas_available = false;
    
    //filter $pano_array to have only id with existing pano and existing image
    foreach ($pano_array as $pano_id) {
        // get picturedata
        $picture = nggdb::find_image($pano_id);
        // if we didn't get some data, exit now
        if (!($picture == null)) {
            //Get galleryid
            //$pano_list[] = $pano_id;
            $gid = $picture->galleryid;
            $gallery_list[] = $gid;
            //new pano from pictureid
            $pano = new nggpanoPano($pano_id, $gid);
            // if we didn't get pano, exit now
            if ($pano->exists()) {
                //get all infos from DB
                $pano = $pano->getObjectFromDB();
                if ($pano) {
                    // add more variables for render output
//                    $pano->title = html_entity_decode( stripslashes(nggPanoramic::i18n($picture->alttext, 'pano_' . $picture->pid . '_alttext')) );
//                    $pano->description = html_entity_decode( stripslashes(nggPanoramic::i18n($picture->description, 'pano_' . $picture->pid . '_description')) );   
                    
                    $pano_list[$pano->pid] = $pano;
                    $pano_id_list[] = $pano->pid;
                    
                    //GPS infos
                    //Get GPS values for the current image
                    $image_values = nggpano_getImagePanoramicOptions($pano_id);
                    $lat = isset($image_values->gps_lat) ? $image_values->gps_lat : '';
                    $lng = isset($image_values->gps_lng) ? $image_values->gps_lng : '';
                    $alt = isset($image_values->gps_alt) ? $image_values->gps_alt : '';
                    if(isset($image_values->gps_lat) && isset($image_values->gps_lng))
                        $map_datas_available = true;
                    $gps = array(
                        'lat' => $lat,
                        'lng' => $lng,
                        'alt' => $alt
                    );
                    
                    $map_list[$pano->pid] = $gps;

                }
            }
        }
    }
    unset($pano_id);
    
    
    $gallery_id = (sizeof($gallery_list) > 1) ? 'several' : $gallery_list[0]; 

//    
//    $out = $listIDs.'<hr/>';
//    $out .= 'pano_array : ';
//    $out .= var_export($pano_array, true);
//    $out .= '<hr/>';
//    $out .= 'pano_id_list : ';
//    $out .= var_export($pano_id_list, true);
//    $out .= '<hr/>';
//    $out .= 'pano_list : ';
//    $out .= var_export($pano_list, true);
    
    
    if(sizeof($pano_id_list)  == 0)
        return __('[No Panoramic available]','nggpano');
    //return $out;
    
    // add float to pano
    switch ($float) {
        
        case 'left': 
            $floatpano =' nggpano-left';
        break;
        
        case 'right': 
            $floatpano =' nggpano-right';
        break;

        case 'center': 
            $floatpano =' nggpano-center';
        break;
        
        default: 
            $floatpano ='';
        break;
    }
    
    // clean captionmode if needed 
    $captionmode = ( preg_match('/(full|title|description)/i', $captionmode) ) ? $captionmode : '';
    
    $pano_config = nggpano_getPanoConfig($gallery_id);
    
    //url to get xml for krpano
    $url_xml = NGGPANOGALLERY_URLPATH . 'xml/krpano.php?pano=';
    $url_xml .= (sizeof($pano_id_list) > 1) ? 'multiple_'.implode('-', $pano_id_list) : 'single_'.implode('-', $pano_id_list);

    //Height and Width for pano div
    //get width and size
    $widthpano = getSizeForPano($width);
    $heightpano = getSizeForPano($height);
    
    // build panodiv array
    $panodiv = array(
        'classname'     => 'nggpano-panoramic'. $floatpano,
        //random id for pano div
        'contentdiv'    => 'panocontent_' . rand(),
        'swfid'         => 'krpanoSWFObject_' . rand(),
        'krpano_path'   => trailingslashit($pano_config['krpanoFolderURL']) . 'krpano.swf',
        'krpano_xml'    => $url_xml,
        'size'          => array('width' => $widthpano, 'height' => $heightpano)
    );
// 
//    $out .= '<hr/>';
//    $out .= 'panodiv : ';
//    $out .= var_export($panodiv, true);
//    
//    
    /* MAP */
    
    // add float to map
    switch ($float) {    
        case 'left': 
            $floatmap =' nggpano-map-left';
        break; 
        case 'right': 
            $floatmap =' nggpano-map-right';
        break;
        case 'center': 
            $floatmap =' nggpano-map-center';
        break;
        default: 
            $floatmap ='';
        break;
    }
    // clean maptype if needed 
    $maptype = ( preg_match('/(HYBRID|ROADMAP|SATELLITE|TERRAIN)/i', strtoupper($maptype)) ) ? strtoupper($maptype) : '';
    //Get Map infos
    $mapdiv = array(
        'available'             => $map_datas_available,
        'width'                 => getSizeForPano($mapwidth),
        'height'                => getSizeForPano($mapheight),
        'zoom'                  => $mapzoom,
        'classname'             => 'nggpano-map'. $floatmap,
        'maptype'               => $maptype,
        'div_id'                => 'map_pic_' . rand(),
        'thumbinfowindowurl'    => trailingslashit( home_url() ) . 'index.php?callback=image&amp;width=200&amp;pid=',
        'map_list'              => $map_list
    );
    
//    $out .= '<hr/>';
//    $out .= 'mapdiv : ';
//    $out .= var_export($mapdiv, true);
    
    //Caption in shortcode
    $caption = nggPanoramic::i18n($caption);
    
    // look for panoramic-$template.php or pure panoramic.php
    $filename = ( empty($template) ) ? 'panoramic' : 'panoramic-' . $template;

    // create the output
    $out = nggPanoramic::capture ( $filename, array (
                                               'panodiv'       => $panodiv,
                                               'pano_list'     => $pano_list,
                                               'captionmode'   => $captionmode,
                                               'mapdiv'        => $mapdiv,
                                               'float'         => $floatpano,
                                               'caption'       => $caption)
                                );

    //$out = apply_filters('nggpano_show_panoramic_content', $out, $picture );
    
    return $out;
}

?>