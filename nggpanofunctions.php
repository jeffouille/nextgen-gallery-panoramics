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
 * nggpanoSinglePano() - show a single pano based on the id
 * 
 * @access public 
 * @param int $imageID, db-ID of the image
 * @param int (optional) $width, width of the pano
 * @param int (optional) $height, height of the pano
 * @param string $float (optional) could be none, left, right
 * @param string $template (optional) name for a template file, look for singlepano-$template
 * @param string $caption (optional) additional caption text
 * @param string $link (optional) link to a other url instead the full image
 * $param string (optional) $captionmode, display or not the caption, could be full, none, title, description. Default none
 * @param int (optional) $mapwidth, width of the map
 * @param int (optional) $mapheight, height of the map
 * @param int (optional) $mapzoom, zoom level of the map
 * @param string (optional) $maptype, type of the map could be HYBRID, ROADMAP, SATELLITE, TERRAIN
 * @return the content
 */
function nggpanoSinglePano($imageID, $width = '100%', $height = '100%', $float = '' , $template = '', $caption = '', $link = '', $mapwidth = 250, $mapheight = 250, $mapzoom = 10, $maptype = 'HYBRID', $captionmode = '') {
    global $post;
    
    require_once (dirname (__FILE__) . '/lib/nggpanoPano.class.php');
    //$ngg_options = get_option('ngg_options');
    //get the next Gen Gallery Options
    //$ngg_options = nggGallery::get_option('ngg_options');
    
    
    // get the ngg Panoramic plugin options
    $nggpano_options = get_option('nggpano_options');
    
    // get picturedata
    $picture = nggdb::find_image($imageID);
    
    // if we didn't get some data, exit now
    if ($picture == null)
        return __('[SinglePano not found]','nggpano');
    
    //Get galleryid
    $gid = $picture->galleryid;

    //new pano from pictureid
    $pano = new nggpanoPano($imageID, $gid);
    // if we didn't get pano, exit now
    if (!$pano->exists())
        return __('[Pano not build]','nggpano');
    //show the pano in the correct div
    //$out = $pano->show("panocontent" ,$width, $height, true);
    
    //get all infos from DB
    $pano = $pano->getObjectFromDB();
    
    if (!$pano)
        return __('[Pano not found in database]','nggpano');
    
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
    
    
    // add more variables for render output
    $pano->title = html_entity_decode( stripslashes(nggPanoramic::i18n($picture->alttext, 'pano_' . $picture->pid . '_alttext')) );
    $pano->description = html_entity_decode( stripslashes(nggPanoramic::i18n($picture->description, 'pano_' . $picture->pid . '_description')) );
    $pano->caption = nggPanoramic::i18n($caption);
    $pano->classname = 'nggpano-singlepano'. $floatpano;
    //random id for pano div
    $pano->contentdiv = 'panocontent_' . rand() . '_' . $picture->pid;
    $pano->swfid = 'krpanoSWFObject_' . rand() . '_' . $picture->pid;
    
    $pano->krpano_path    = trailingslashit($pano->krpanoFolderURL) . $pano->krpanoSWF;
    $pano->krpano_xml     = NGGPANOGALLERY_URLPATH . 'xml/krpano.php?pano=single_'.$pano->pid;
    
    
    //Height and Width for pano div
    //get width and size
    $widthpano = getSizeForPano($width);
    $heightpano = getSizeForPano($height);
    $panosize = array('width' => $widthpano, 'height' => $heightpano);
    
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

    // look for singlepano-$template.php or pure singlepano.php
    $filename = ( empty($template) ) ? 'singlepano' : 'singlepano-' . $template;

    // create the output
    $out = nggPanoramic::capture ( $filename, array (
                                               'pano' => $pano,
                                               'gps' => $gps,
                                               'panosize' => $panosize,
                                               'captionmode' => $captionmode,
                                               'mapinfos' => $mapinfos,
                                               'float' => $floatpano)
                                );

    //$out = apply_filters('nggpano_show_singlepano_content', $out, $picture );
    
    return $out;
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
    $links_array = split('&', strtoupper($links));
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
    $links_array = split('&', strtoupper($links));
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
 * Return a script for the Imagerotator flash slideshow. Can be used in any template with <?php echo nggShowSlideshow($galleryID, $width, $height) ?>
 * Require the script swfobject.js in the header or footer
 * 
 * @access public 
 * @param integer $galleryID ID of the gallery
 * @param integer $irWidth Width of the flash container
 * @param integer $irHeight Height of the flash container
 * @return the content
 */
function nggpanoShowSlideshow($galleryID, $width, $height) {
    
    require_once (dirname (__FILE__).'/lib/swfobject.php');

    $ngg_options = nggGallery::get_option('ngg_options');

    // remove media file from RSS feed
    if ( is_feed() ) {
        $out = '[' . nggGallery::i18n($ngg_options['galTextSlide']) . ']'; 
        return $out;
    }

    //Redirect all calls to the JavaScript slideshow if wanted
    if ( $ngg_options['enableIR'] !== '1' || nggGallery::detect_mobile_phone() === true )
        return nggShow_JS_Slideshow($galleryID, $width, $height);
    
    // If the Imagerotator didn't exist, skip the output
    if ( NGGALLERY_IREXIST == false ) 
        return; 
        
    if (empty($width) ) $width  = (int) $ngg_options['irWidth'];
    if (empty($height)) $height = (int) $ngg_options['irHeight'];
    // Doesn't work fine with zero
    $ngg_options['irRotatetime'] = ($ngg_options['irRotatetime'] == 0) ? 5 : $ngg_options['irRotatetime'];
    // init the flash output
    $swfobject = new swfobject( $ngg_options['irURL'] , 'so' . $galleryID, $width, $height, '7.0.0', 'false');

    $swfobject->message = '<p>'. __('The <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> and <a href="http://www.mozilla.com/firefox/">a browser with Javascript support</a> are needed.', 'nggallery').'</p>';
    $swfobject->add_params('wmode', 'opaque');
    $swfobject->add_params('allowfullscreen', 'true');
    $swfobject->add_params('bgcolor', $ngg_options['irScreencolor'], 'FFFFFF', 'string', '#');
    $swfobject->add_attributes('styleclass', 'slideshow');
    $swfobject->add_attributes('name', 'so' . $galleryID);

    // adding the flash parameter   
    $swfobject->add_flashvars( 'file', urlencode ( trailingslashit ( home_url() ) . 'index.php?callback=imagerotator&gid=' . $galleryID ) );
    $swfobject->add_flashvars( 'shuffle', $ngg_options['irShuffle'], 'true', 'bool');
    // option has oposite meaning : true should switch to next image
    $swfobject->add_flashvars( 'linkfromdisplay', !$ngg_options['irLinkfromdisplay'], 'false', 'bool');
    $swfobject->add_flashvars( 'shownavigation', $ngg_options['irShownavigation'], 'true', 'bool');
    $swfobject->add_flashvars( 'showicons', $ngg_options['irShowicons'], 'true', 'bool');
    $swfobject->add_flashvars( 'kenburns', $ngg_options['irKenburns'], 'false', 'bool');
    $swfobject->add_flashvars( 'overstretch', $ngg_options['irOverstretch'], 'false', 'string');
    $swfobject->add_flashvars( 'rotatetime', $ngg_options['irRotatetime'], 5, 'int');
    $swfobject->add_flashvars( 'transition', $ngg_options['irTransition'], 'random', 'string');
    $swfobject->add_flashvars( 'backcolor', $ngg_options['irBackcolor'], 'FFFFFF', 'string', '0x');
    $swfobject->add_flashvars( 'frontcolor', $ngg_options['irFrontcolor'], '000000', 'string', '0x');
    $swfobject->add_flashvars( 'lightcolor', $ngg_options['irLightcolor'], '000000', 'string', '0x');
    $swfobject->add_flashvars( 'screencolor', $ngg_options['irScreencolor'], '000000', 'string', '0x');
    if ($ngg_options['irWatermark'])
        $swfobject->add_flashvars( 'logo', $ngg_options['wmPath'], '', 'string'); 
    $swfobject->add_flashvars( 'audio', $ngg_options['irAudio'], '', 'string');
    $swfobject->add_flashvars( 'width', $width, '260');
    $swfobject->add_flashvars( 'height', $height, '320');   
    // create the output
    $out  = '<div class="slideshow">' . $swfobject->output() . '</div>';
    // add now the script code
    $out .= "\n".'<script type="text/javascript" defer="defer">';
    // load script via jQuery afterwards
    // $out .= "\n".'jQuery.getScript( "'  . NGGALLERY_URLPATH . 'admin/js/swfobject.js' . '", function() {} );';
    if ($ngg_options['irXHTMLvalid']) $out .= "\n".'<!--';
    if ($ngg_options['irXHTMLvalid']) $out .= "\n".'//<![CDATA[';
    $out .= $swfobject->javascript();
    if ($ngg_options['irXHTMLvalid']) $out .= "\n".'//]]>';
    if ($ngg_options['irXHTMLvalid']) $out .= "\n".'-->';
    $out .= "\n".'</script>';

    $out = apply_filters('ngg_show_slideshow_content', $out, $galleryID, $width, $height);
            
    return $out;    
}

/**
 * Return a script for the jQuery based slideshow. Can be used in any template with <?php echo nggShow_JS_Slideshow($galleryID, $width, $height) ?>
 * Require the script jquery.cycle.all.js
 * 
 * @since 1.6.0 
 * @access public
 * @param integer $galleryID ID of the gallery
 * @param integer $width Width of the slideshow container
 * @param integer $height Height of the slideshow container
 * @param string $class Classname of the div container
 * @return the content
 */
function nggpanoShow_JS_Slideshow($galleryID, $width, $height, $class = 'ngg-slideshow') {
	
    global $slideCounter;
   
    $ngg_options = nggGallery::get_option('ngg_options');
    
    // we need to know the current page id
    $current_page = (get_the_ID() == false) ? rand(5, 15) : get_the_ID();
	// look for a other slideshow instance
	if ( !isset($slideCounter) ) $slideCounter = 1; 
    // create unique anchor
    $anchor = 'ngg-slideshow-' . $galleryID . '-' . $current_page . '-' . $slideCounter++;
    
    if (empty($width) ) $width  = (int) $ngg_options['irWidth'];
    if (empty($height)) $height = (int) $ngg_options['irHeight'];
    
    //filter to resize images for mobile browser
    list($width, $height) = apply_filters('ngg_slideshow_size', array( $width, $height ) );
    
    $width  = (int) $width;
    $height = (int) $height;
            
    $out  = '<div id="' . $anchor . '" class="' . $class . '" style="height:' . $height . 'px;width:' . $width . 'px;">';
    $out .= "\n". '<div id="' . $anchor . '-loader" class="ngg-slideshow-loader" style="height:' . $height . 'px;width:' . $width . 'px;">';
    $out .= "\n". '<img src="'. NGGALLERY_URLPATH . 'images/loader.gif" alt="" />';
    $out .= "\n". '</div>';
    $out .= '</div>'."\n";
    $out .= "\n".'<script type="text/javascript" defer="defer">';
    $out .= "\n" . 'jQuery(document).ready(function(){ ' . "\n" . 'jQuery("#' . $anchor . '").nggSlideshow( {' .
            'id: '      . $galleryID    . ',' . 
            'fx:"'      . $ngg_options['slideFx'] . '",' .
            'width:'    . $width        . ',' . 
            'height:'   . $height       . ',' .
            'domain: "' . trailingslashit ( home_url() ) . '",' .
            'timeout:'  . $ngg_options['irRotatetime'] * 1000 .
            '});' . "\n" . '});';
    $out .= "\n".'</script>';

    return $out;
}

/**
 * nggpanoShowGallery() - return a gallery  
 * 
 * @access public 
 * @param int | string ID or slug from a gallery
 * @param string $template (optional) name for a template file, look for gallery-$template
 * @param int $images (optional) number of images per page
 * @return the content
 */
function nggpanoShowGallery( $galleryID, $template = '', $images = false ) {
    
    global $nggRewrite;

    $ngg_options = nggGallery::get_option('ngg_options');
    
    //Set sort order value, if not used (upgrade issue)
    $ngg_options['galSort'] = ($ngg_options['galSort']) ? $ngg_options['galSort'] : 'pid';
    $ngg_options['galSortDir'] = ($ngg_options['galSortDir'] == 'DESC') ? 'DESC' : 'ASC';
    
    // get gallery values
    //TODO: Use pagination limits here to reduce memory needs
    $picturelist = nggdb::get_gallery($galleryID, $ngg_options['galSort'], $ngg_options['galSortDir']);

    if ( !$picturelist )
        return __('[Gallery not found]','nggallery');

    // If we have we slug instead the id, we should extract the ID from the first image
    if ( !is_numeric($galleryID) ) {
        $first_image = current($picturelist);
        $galleryID = intval($first_image->gid);
    }

    // $_GET from wp_query
    $show    = get_query_var('show');
    $pid     = get_query_var('pid');
    $pageid  = get_query_var('pageid');
    
    // set $show if slideshow first
    if ( empty( $show ) AND ($ngg_options['galShowOrder'] == 'slide')) {
        if ( is_home() ) 
            $pageid = get_the_ID();
        
        $show = 'slide';
    }

    // filter to call up the imagebrowser instead of the gallery
    // use in your theme : add_action( 'ngg_show_imagebrowser_first', create_function('', 'return true;') );
    if ( apply_filters('ngg_show_imagebrowser_first', false, $galleryID ) && $show != 'thumbnails' )  {
        $out = nggShowImageBrowser( $galleryID, $template );
        return $out;
    }

    // go on only on this page
    if ( !is_home() || $pageid == get_the_ID() ) { 
            
        // 1st look for ImageBrowser link
        if ( !empty($pid) && $ngg_options['galImgBrowser'] && ($template != 'carousel') )  {
            $out = nggShowImageBrowser( $galleryID, $template );
            return $out;
        }
        
        // 2nd look for slideshow
        if ( $show == 'slide' ) {
            $args['show'] = "gallery";
            $out  = '<div class="ngg-galleryoverview">';
            $out .= '<div class="slideshowlink"><a class="slideshowlink" href="' . $nggRewrite->get_permalink($args) . '">'.nggGallery::i18n($ngg_options['galTextGallery']).'</a></div>';
            $out .= nggShowSlideshow($galleryID, $ngg_options['irWidth'], $ngg_options['irHeight']);
            $out .= '</div>'."\n";
            $out .= '<div class="ngg-clear"></div>'."\n";
            return $out;
        }
    }

    // get all picture with this galleryid
    if ( is_array($picturelist) )
        $out = nggCreateGallery($picturelist, $galleryID, $template, $images);
    
    $out = apply_filters('ngg_show_gallery_content', $out, intval($galleryID));
    return $out;
}

/**
 * Build a gallery output
 * 
 * @access internal
 * @param array $picturelist
 * @param bool $galleryID, if you supply a gallery ID, you can add a slideshow link
 * @param string $template (optional) name for a template file, look for gallery-$template
 * @param int $images (optional) number of images per page
 * @return the content
 */
function nggpanoCreateGallery($picturelist, $galleryID = false, $template = '', $images = false) {
    global $nggRewrite;

    require_once (dirname (__FILE__) . '/lib/media-rss.php');
    
    $ngg_options = nggGallery::get_option('ngg_options');

    //the shortcode parameter will override global settings, TODO: rewrite this to a class
    $ngg_options['galImages'] = ( $images === false ) ? $ngg_options['galImages'] : (int) $images;  
    
    $current_pid = false;
        
    // $_GET from wp_query
    $nggpage  = get_query_var('nggpage');
    $pageid   = get_query_var('pageid');
    $pid      = get_query_var('pid');
    
    // in case of permalinks the pid is a slug, we need the id
    if( !is_numeric($pid) && !empty($pid) ) {
        $picture = nggdb::find_image($pid);        
        $pid = $picture->pid;
    }   
    
    // we need to know the current page id
    $current_page = (get_the_ID() == false) ? 0 : get_the_ID();

    if ( !is_array($picturelist) )
        $picturelist = array($picturelist);
    
    // Populate galleries values from the first image           
    $first_image = current($picturelist);
    $gallery = new stdclass;
    $gallery->ID = (int) $galleryID;
    $gallery->show_slideshow = false;
    $gallery->show_piclens = false;
    $gallery->name = stripslashes ( $first_image->name  );
    $gallery->title = stripslashes( $first_image->title );
    $gallery->description = html_entity_decode(stripslashes( $first_image->galdesc));
    $gallery->pageid = $first_image->pageid;
    $gallery->anchor = 'ngg-gallery-' . $galleryID . '-' . $current_page;
    reset($picturelist);

    $maxElement  = $ngg_options['galImages'];
    $thumbwidth  = $ngg_options['thumbwidth'];
    $thumbheight = $ngg_options['thumbheight'];     
    
    // fixed width if needed
    $gallery->columns    = intval($ngg_options['galColumns']);
    $gallery->imagewidth = ($gallery->columns > 0) ? 'style="width:' . floor(100/$gallery->columns) . '%;"' : '';
    
    // obsolete in V1.4.0, but kept for compat reason
	// pre set thumbnail size, from the option, later we look for meta data. 
    $thumbsize = ($ngg_options['thumbfix']) ? $thumbsize = 'width="' . $thumbwidth . '" height="'.$thumbheight . '"' : '';
    
    // show slideshow link
    if ($galleryID) {
        if ($ngg_options['galShowSlide']) {
            $gallery->show_slideshow = true;
            $gallery->slideshow_link = $nggRewrite->get_permalink(array ( 'show' => 'slide') );
            $gallery->slideshow_link_text = nggGallery::i18n($ngg_options['galTextSlide']);
        }
        
        if ($ngg_options['usePicLens']) {
            $gallery->show_piclens = true;
            $gallery->piclens_link = "javascript:PicLensLite.start({feedUrl:'" . htmlspecialchars( nggMediaRss::get_gallery_mrss_url($gallery->ID) ) . "'});";
        }
    }

    // check for page navigation
    if ($maxElement > 0) {
        
        if ( !is_home() || $pageid == $current_page )
            $page = ( !empty( $nggpage ) ) ? (int) $nggpage : 1;
        else 
            $page = 1;
         
        $start = $offset = ( $page - 1 ) * $maxElement;
        
        $total = count($picturelist);

		//we can work with display:hidden for some javascript effects
        if (!$ngg_options['galHiddenImg']){
	        // remove the element if we didn't start at the beginning
	        if ($start > 0 ) 
	            array_splice($picturelist, 0, $start);
	        
	        // return the list of images we need
	        array_splice($picturelist, $maxElement);
        }

        $nggNav = new nggNavigation;    
        $navigation = $nggNav->create_navigation($page, $total, $maxElement);
    } else {
        $navigation = '<div class="ngg-clear"></div>';
    } 
	  
    //we cannot use the key as index, cause it's filled with the pid
	$index = 0;
    foreach ($picturelist as $key => $picture) {

		//needed for hidden images (THX to Sweigold for the main idea at : http://wordpress.org/support/topic/228743/ )
		$picturelist[$key]->hidden = false;	
		$picturelist[$key]->style  = $gallery->imagewidth;
		
		if ($maxElement > 0 && $ngg_options['galHiddenImg']) {
	  		if ( ($index < $start) || ($index > ($start + $maxElement -1)) ){
				$picturelist[$key]->hidden = true;	
				$picturelist[$key]->style  = ($gallery->columns > 0) ? 'style="width:' . floor(100/$gallery->columns) . '%;display: none;"' : 'style="display: none;"';
			}
  			$index++;
		}
		
        // get the effect code
        if ($galleryID)
            $thumbcode = ($ngg_options['galImgBrowser']) ? '' : $picture->get_thumbcode('set_' . $galleryID);
        else
            $thumbcode = ($ngg_options['galImgBrowser']) ? '' : $picture->get_thumbcode(get_the_title());

        // create link for imagebrowser and other effects
        $args ['nggpage'] = empty($nggpage) || ($template != 'carousel') ? false : $nggpage;  // only needed for carousel mode
        $args ['pid']     = ($ngg_options['usePermalinks']) ? $picture->image_slug : $picture->pid;
        $picturelist[$key]->pidlink = $nggRewrite->get_permalink( $args );
        
        // generate the thumbnail size if the meta data available
        if ( isset($picturelist[$key]->meta_data['thumbnail']) && is_array ($size = $picturelist[$key]->meta_data['thumbnail']) )
        	$thumbsize = 'width="' . $size['width'] . '" height="' . $size['height'] . '"';
        
        // choose link between imagebrowser or effect
        $link = ($ngg_options['galImgBrowser']) ? $picturelist[$key]->pidlink : $picture->imageURL; 
        // bad solution : for now we need the url always for the carousel, should be reworked in the future
        $picturelist[$key]->url = $picture->imageURL;
        // add a filter for the link
        $picturelist[$key]->imageURL = apply_filters('ngg_create_gallery_link', $link, $picture);
        $picturelist[$key]->thumbnailURL = $picture->thumbURL;
        $picturelist[$key]->size = $thumbsize;
        $picturelist[$key]->thumbcode = $thumbcode;
        $picturelist[$key]->caption = ( empty($picture->description) ) ? '&nbsp;' : html_entity_decode ( stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')) );
        $picturelist[$key]->description = ( empty($picture->description) ) ? ' ' : htmlspecialchars ( stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')) );
        $picturelist[$key]->alttext = ( empty($picture->alttext) ) ?  ' ' : htmlspecialchars ( stripslashes(nggGallery::i18n($picture->alttext, 'pic_' . $picture->pid . '_alttext')) );
        
        // filter to add custom content for the output
        $picturelist[$key] = apply_filters('ngg_image_object', $picturelist[$key], $picture->pid);

        //check if $pid is in the array
        if ($picture->pid == $pid) 
            $current_pid = $picturelist[$key];
    }
    reset($picturelist);

    //for paged galleries, take the first image in the array if it's not in the list
    $current_pid = ( empty($current_pid) ) ? current( $picturelist ) : $current_pid;
    
    // look for gallery-$template.php or pure gallery.php
    $filename = ( empty($template) ) ? 'gallery' : 'gallery-' . $template;
    
    //filter functions for custom addons
    $gallery     = apply_filters( 'ngg_gallery_object', $gallery, $galleryID );
    $picturelist = apply_filters( 'ngg_picturelist_object', $picturelist, $galleryID );
    
    //additional navigation links
    $next = ( empty($nggNav->next) ) ? false : $nggNav->next;
    $prev = ( empty($nggNav->prev) ) ? false : $nggNav->prev;

    // create the output
    $out = nggGallery::capture ( $filename, array ('gallery' => $gallery, 'images' => $picturelist, 'pagination' => $navigation, 'current' => $current_pid, 'next' => $next, 'prev' => $prev) );
    
    // apply a filter after the output
    $out = apply_filters('ngg_gallery_output', $out, $picturelist);
    
    return $out;
}

/**
 * nggpanoShowAlbum() - return a album based on the id
 * 
 * @access public 
 * @param int | string $albumID
 * @param string (optional) $template
 * @param string (optional) $gallery_template
 * @return the content
 */
function nggpanoShowAlbum($albumID, $template = 'extend', $gallery_template = '') {
    
    // $_GET from wp_query
    $gallery  = get_query_var('gallery');
    $album    = get_query_var('album');

    // in the case somebody uses the '0', it should be 'all' to show all galleries
    $albumID  = ($albumID == 0) ? 'all' : $albumID;

    // first look for gallery variable 
    if (!empty( $gallery ))  {
        
        // subalbum support only one instance, you can't use more of them in one post
        //TODO: causes problems with SFC plugin, due to a second filter callback
        if ( isset($GLOBALS['subalbum']) || isset($GLOBALS['nggShowGallery']) )
                return;
                
        // if gallery is submit , then show the gallery instead 
        $out = nggShowGallery( $gallery, $gallery_template );
        $GLOBALS['nggShowGallery'] = true;
        
        return $out;
    }
    
    if ( (empty( $gallery )) && (isset($GLOBALS['subalbum'])) )
        return;

    //redirect to subalbum only one time        
    if (!empty( $album )) {
        $GLOBALS['subalbum'] = true;
        $albumID = $album;          
    }

    // lookup in the database
    $album = nggdb::find_album( $albumID );

    // still no success ? , die !
    if( !$album ) 
        return __('[Album not found]','nggallery');
    
    if ( is_array($album->gallery_ids) )
        $out = nggCreateAlbum( $album->gallery_ids, $template, $album );
    
    $out = apply_filters( 'ngg_show_album_content', $out, $album->id );

    return $out;
}

/**
 * create a gallery overview output
 * 
 * @access internal
 * @param array $galleriesID
 * @param string (optional) $template name for a template file, look for album-$template
 * @param object (optional) $album result from the db
 * @return the content
 */
function nggpanoCreateAlbum( $galleriesID, $template = 'extend', $album = 0) {

    global $wpdb, $nggRewrite, $nggdb;
    
    // $_GET from wp_query
    $nggpage  = get_query_var('nggpage');   
    
    $ngg_options = nggGallery::get_option('ngg_options');
    
    //this option can currently only set via the custom fields
    $maxElement  = (int) $ngg_options['galPagedGalleries'];

    $sortorder = $galleriesID;
    $galleries = array();
    
    // get the galleries information    
    foreach ($galleriesID as $i => $value)
        $galleriesID[$i] = addslashes($value);

    $unsort_galleries = $wpdb->get_results('SELECT * FROM '.$wpdb->nggallery.' WHERE gid IN (\''.implode('\',\'', $galleriesID).'\')', OBJECT_K);

    //TODO: Check this, problem exist when previewpic = 0 
    //$galleries = $wpdb->get_results('SELECT t.*, tt.* FROM '.$wpdb->nggallery.' AS t INNER JOIN '.$wpdb->nggpictures.' AS tt ON t.previewpic = tt.pid WHERE t.gid IN (\''.implode('\',\'', $galleriesID).'\')', OBJECT_K);

    // get the counter values   
    $picturesCounter = $wpdb->get_results('SELECT galleryid, COUNT(*) as counter FROM '.$wpdb->nggpictures.' WHERE galleryid IN (\''.implode('\',\'', $galleriesID).'\') AND exclude != 1 GROUP BY galleryid', OBJECT_K);
    if ( is_array($picturesCounter) ) {
        foreach ($picturesCounter as $key => $value)
            $unsort_galleries[$key]->counter = $value->counter;
    }
    
    // get the id's of the preview images
    $imagesID = array();
    if ( is_array($unsort_galleries) ) {
        foreach ($unsort_galleries as $gallery_row)
            $imagesID[] = $gallery_row->previewpic;
    }   
    $albumPreview = $wpdb->get_results('SELECT pid, filename FROM '.$wpdb->nggpictures.' WHERE pid IN (\''.implode('\',\'', $imagesID).'\')', OBJECT_K);

    // re-order them and populate some 
    foreach ($sortorder as $key) {
		       
        //if we have a prefix 'a' then it's a subalbum, instead a gallery
        if (substr( $key, 0, 1) == 'a') { 
            // get the album content
             if ( !$subalbum = $nggdb->find_album(substr( $key, 1)) )
                continue;
            
            //populate the sub album values
            $galleries[$key]->counter = 0;
            if ($subalbum->previewpic > 0)
                $image = $nggdb->find_image( $subalbum->previewpic );
            $galleries[$key]->previewpic = $subalbum->previewpic;
            $galleries[$key]->previewurl = isset($image->thumbURL) ? $image->thumbURL : '';
            $galleries[$key]->previewname = $subalbum->name;
            
            //link to the subalbum
            $args['album'] = ( $ngg_options['usePermalinks'] ) ? $subalbum->slug : $subalbum->id;
            $args['gallery'] = false; 
            $args['nggpage'] = false;
            $pageid = (isset($subalbum->pageid) ? $subalbum->pageid : 0);
            $galleries[$key]->pagelink = ($pageid > 0) ? get_permalink($pageid) : $nggRewrite->get_permalink($args);
            $galleries[$key]->galdesc = html_entity_decode ( nggGallery::i18n($subalbum->albumdesc) );
            $galleries[$key]->title = html_entity_decode ( nggGallery::i18n($subalbum->name) ); 
            
            // apply a filter on gallery object before the output
            $galleries[$key] = apply_filters('ngg_album_galleryobject', $galleries[$key]);
            
            continue;
        }
		
		// If a gallery is not found it should be ignored
        if (!$unsort_galleries[$key])
        	continue;
		
		// Add the counter value if avaible
        $galleries[$key] = $unsort_galleries[$key];
    	
        // add the file name and the link 
        if ($galleries[$key]->previewpic  != 0) {
            $galleries[$key]->previewname = $albumPreview[$galleries[$key]->previewpic]->filename;
            $galleries[$key]->previewurl  = site_url().'/' . $galleries[$key]->path . '/thumbs/thumbs_' . $albumPreview[$galleries[$key]->previewpic]->filename;
        } else {
            $first_image = $wpdb->get_row('SELECT * FROM '. $wpdb->nggpictures .' WHERE exclude != 1 AND galleryid = '. $key .' ORDER by pid DESC limit 0,1');
            $galleries[$key]->previewpic  = $first_image->pid;
            $galleries[$key]->previewname = $first_image->filename;
            $galleries[$key]->previewurl  = site_url() . '/' . $galleries[$key]->path . '/thumbs/thumbs_' . $first_image->filename;
        }

        // choose between variable and page link
        if ($ngg_options['galNoPages']) {
            $args['album'] = ( $ngg_options['usePermalinks'] ) ? $album->slug : $album->id; 
            $args['gallery'] = ( $ngg_options['usePermalinks'] ) ? $galleries[$key]->slug : $key;
            $args['nggpage'] = false;
            $galleries[$key]->pagelink = $nggRewrite->get_permalink($args);
            
        } else {
            $galleries[$key]->pagelink = get_permalink( $galleries[$key]->pageid );
        }
        
        // description can contain HTML tags
        $galleries[$key]->galdesc = html_entity_decode ( nggGallery::i18n( stripslashes($galleries[$key]->galdesc) ) ) ;

        // i18n
        $galleries[$key]->title = html_entity_decode ( nggGallery::i18n( stripslashes($galleries[$key]->title) ) ) ;
        
        // apply a filter on gallery object before the output
        $galleries[$key] = apply_filters('ngg_album_galleryobject', $galleries[$key]);
    }
    
    // apply a filter on gallery object before paging starts
    $galleries = apply_filters('ngg_album_galleries_before_paging', $galleries, $album);
    
    // check for page navigation
    if ($maxElement > 0) {
        if ( !is_home() || $pageid == get_the_ID() ) {
            $page = ( !empty( $nggpage ) ) ? (int) $nggpage : 1;
        }
        else $page = 1;
         
        $start = $offset = ( $page - 1 ) * $maxElement;
        
        $total = count($galleries);
        
        // remove the element if we didn't start at the beginning
        if ($start > 0 ) array_splice($galleries, 0, $start);
        
        // return the list of images we need
        array_splice($galleries, $maxElement);
        
        $nggNav = new nggNavigation;    
        $navigation = $nggNav->create_navigation($page, $total, $maxElement);
    } else {
        $navigation = '<div class="ngg-clear"></div>';
    }

    // apply a filter on $galleries before the output
    $galleries = apply_filters('ngg_album_galleries', $galleries);
    
    // if sombody didn't enter any template , take the extend version
    $filename = ( empty($template) ) ? 'album-extend' : 'album-' . $template ;

    // create the output
    $out = nggGallery::capture ( $filename, array ('album' => $album, 'galleries' => $galleries, 'pagination' => $navigation) );

    return $out;
    
}

/**
 * nggpanoShowImageBrowser()
 * 
 * @access public 
 * @param int|string $galleryID or gallery name
 * @param string $template (optional) name for a template file, look for imagebrowser-$template
 * @return the content
 */
function nggpanoShowImageBrowser($galleryID, $template = '') {
    
    global $wpdb;
    
    $ngg_options = nggGallery::get_option('ngg_options');
    
    //Set sort order value, if not used (upgrade issue)
    $ngg_options['galSort'] = ($ngg_options['galSort']) ? $ngg_options['galSort'] : 'pid';
    $ngg_options['galSortDir'] = ($ngg_options['galSortDir'] == 'DESC') ? 'DESC' : 'ASC';
    
    // get the pictures
    $picturelist = nggdb::get_gallery($galleryID, $ngg_options['galSort'], $ngg_options['galSortDir']);
    
    if ( is_array($picturelist) )
        $out = nggCreateImageBrowser($picturelist, $template);
    else
        $out = __('[Gallery not found]','nggallery');
    
    $out = apply_filters('ngg_show_imagebrowser_content', $out, $galleryID);
    
    return $out;
    
}

/**
 * nggpanoCreateImageBrowser()
 * 
 * @access internal
 * @param array $picturelist
 * @param string $template (optional) name for a template file, look for imagebrowser-$template
 * @return the content
 */
function nggpanoCreateImageBrowser($picturelist, $template = '') {

    global $nggRewrite, $ngg;
    
    require_once( dirname (__FILE__) . '/lib/meta.php' );
    
    // $_GET from wp_query
    $pid  = get_query_var('pid');
    
    // we need to know the current page id
    $current_page = (get_the_ID() == false) ? 0 : get_the_ID();
    
    // create a array with id's for better walk inside
    foreach ($picturelist as $picture)
        $picarray[] = $picture->pid;

    $total = count($picarray);

    if ( !empty( $pid )) {
        if ( is_numeric($pid) )     
            $act_pid = intval($pid);
        else {
            // in the case it's a slug we need to search for the pid
            foreach ($picturelist as $key => $picture) {
                if ($picture->image_slug == $pid) {
                    $act_pid = $key;
                    break;
                }
            }           
        }
    } else {
        reset($picarray);
        $act_pid = current($picarray);
    }
    
    // get ids for back/next
    $key = array_search($act_pid, $picarray);
    if (!$key) {
        $act_pid = reset($picarray);
        $key = key($picarray);
    }
    $back_pid = ( $key >= 1 ) ? $picarray[$key-1] : end($picarray) ;
    $next_pid = ( $key < ($total-1) ) ? $picarray[$key+1] : reset($picarray) ;
    
    // get the picture data
    $picture = nggdb::find_image($act_pid);
    
    // if we didn't get some data, exit now
    if ($picture == null)
        return;
        
    // add more variables for render output
    $picture->href_link = $picture->get_href_link();
    $args ['pid'] = ($ngg->options['usePermalinks']) ? $picturelist[$back_pid]->image_slug : $back_pid;
    $picture->previous_image_link = $nggRewrite->get_permalink( $args );
    $picture->previous_pid = $back_pid;
    $args ['pid'] = ($ngg->options['usePermalinks']) ? $picturelist[$next_pid]->image_slug : $next_pid;
    $picture->next_image_link  = $nggRewrite->get_permalink( $args );
    $picture->next_pid = $next_pid;
    $picture->number = $key + 1;
    $picture->total = $total;
    $picture->linktitle = htmlspecialchars( stripslashes($picture->description) );
    $picture->alttext = html_entity_decode( stripslashes($picture->alttext) );
    $picture->description = html_entity_decode( stripslashes($picture->description) );
    $picture->anchor = 'ngg-imagebrowser-' . $picture->galleryid . '-' . $current_page;
    
    // filter to add custom content for the output
    $picture = apply_filters('ngg_image_object', $picture, $act_pid);
    
    // let's get the meta data
    $meta = new nggMeta($act_pid);
    $exif = $meta->get_EXIF();
    $iptc = $meta->get_IPTC();
    $xmp  = $meta->get_XMP();
    $db   = $meta->get_saved_meta();
    
    //if we get no exif information we try the database 
    $exif = ($exif == false) ? $db : $exif;
        
    // look for imagebrowser-$template.php or pure imagebrowser.php
    $filename = ( empty($template) ) ? 'imagebrowser' : 'imagebrowser-' . $template;

    // create the output
    $out = nggGallery::capture ( $filename , array ('image' => $picture , 'meta' => $meta, 'exif' => $exif, 'iptc' => $iptc, 'xmp' => $xmp, 'db' => $db) );
    
    return $out;
    
}

/**
 * nggpanoShowGalleryTags() - create a gallery based on the tags
 * 
 * @access public 
 * @param string $taglist list of tags as csv
 * @return the content
 */
function nggpanoShowGalleryTags($taglist) { 

    // $_GET from wp_query
    $pid    = get_query_var('pid');
    $pageid = get_query_var('pageid');
    
    // get now the related images
    $picturelist = nggTags::find_images_for_tags($taglist , 'ASC');

    // look for ImageBrowser if we have a $_GET('pid')
    if ( $pageid == get_the_ID() || !is_home() )  
        if (!empty( $pid ))  {
            $out = nggCreateImageBrowser( $picturelist );
            return $out;
        }

    // go on if not empty
    if ( empty($picturelist) )
        return;
    
    // show gallery
    if ( is_array($picturelist) )
        $out = nggCreateGallery($picturelist, false);
    
    $out = apply_filters('ngg_show_gallery_tags_content', $out, $taglist);
    return $out;
}

/**
 * nggpanoShowRelatedGallery() - create a gallery based on the tags
 * 
 * @access public 
 * @param string $taglist list of tags as csv
 * @param integer $maxImages (optional) limit the number of images to show
 * @return the content
 */ 
function nggpanoShowRelatedGallery($taglist, $maxImages = 0) {
    
    $ngg_options = nggGallery::get_option('ngg_options');
    
    // get now the related images
    $picturelist = nggTags::find_images_for_tags($taglist, 'RAND');

    // go on if not empty
    if ( empty($picturelist) )
        return;
    
    // cut the list to maxImages
    if ( $maxImages > 0 )
        array_splice($picturelist, $maxImages);

    // *** build the gallery output
    $out   = '<div class="ngg-related-gallery">';
    foreach ($picturelist as $picture) {

        // get the effect code
        $thumbcode = $picture->get_thumbcode( __('Related images for', 'nggallery') . ' ' . get_the_title());

        $out .= '<a href="' . $picture->imageURL . '" title="' . stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')) . '" ' . $thumbcode . ' >';
        $out .= '<img title="' . stripslashes(nggGallery::i18n($picture->alttext, 'pic_' . $picture->pid . '_alttext')) . '" alt="' . stripslashes(nggGallery::i18n($picture->alttext, 'pic_' . $picture->pid . '_alttext')) . '" src="' . $picture->thumbURL . '" />';
        $out .= '</a>' . "\n";
    }
    $out .= '</div>' . "\n";
    
    $out = apply_filters('ngg_show_related_gallery_content', $out, $taglist);
    
    return $out;
}

/**
 * nggpanoShowAlbumTags() - create a gallery based on the tags
 * 
 * @access public 
 * @param string $taglist list of tags as csv
 * @return the content
 */
function nggpanoShowAlbumTags($taglist) {
    
    global $wpdb, $nggRewrite;

    // $_GET from wp_query
    $tag            = get_query_var('gallerytag');
    $pageid         = get_query_var('pageid');
    
    // look for gallerytag variable 
    if ( $pageid == get_the_ID() || !is_home() )  {
        if (!empty( $tag ))  {
    
            // avoid this evil code $sql = 'SELECT name FROM wp_ngg_tags WHERE slug = \'slug\' union select concat(0x7c,user_login,0x7c,user_pass,0x7c) from wp_users WHERE 1 = 1';
            $slug = esc_attr( $tag );
            $tagname = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM $wpdb->terms WHERE slug = %s", $slug ) );
            $out  = '<div id="albumnav"><span><a href="' . get_permalink() . '" title="' . __('Overview', 'nggallery') .' ">'.__('Overview', 'nggallery').'</a> | '.$tagname.'</span></div>';
            $out .=  nggShowGalleryTags($slug);
            return $out;
    
        } 
    }
    
    // get now the related images
    $picturelist = nggTags::get_album_images($taglist);

    // go on if not empty
    if ( empty($picturelist) )
        return;
    
    // re-structure the object that we can use the standard template    
    foreach ($picturelist as $key => $picture) {
        $picturelist[$key]->previewpic  = $picture->pid;
        $picturelist[$key]->previewname = $picture->filename;
        $picturelist[$key]->previewurl  = site_url() . '/' . $picture->path . '/thumbs/thumbs_' . $picture->filename;
        $picturelist[$key]->counter     = $picture->count;
        $picturelist[$key]->title       = $picture->name;
        $picturelist[$key]->pagelink    = $nggRewrite->get_permalink( array('gallerytag'=>$picture->slug) );
    }
        
    //TODO: Add pagination later
    $navigation = '<div class="ngg-clear"></div>';
    
    // create the output
    $out = nggGallery::capture ('album-compact', array ('album' => 0, 'galleries' => $picturelist, 'pagination' => $navigation) );
    
    $out = apply_filters('ngg_show_album_tags_content', $out, $taglist);
    
    return $out;
}

/**
 * nggpanoShowRelatedImages() - return related images based on category or tags
 * 
 * @access public 
 * @param string $type could be 'tags' or 'category'
 * @param integer $maxImages of images
 * @return the content
 */
function nggpanoShowRelatedImages($type = '', $maxImages = 0) {
    $ngg_options = nggGallery::get_option('ngg_options');

    if ($type == '') {
        $type = $ngg_options['appendType'];
        $maxImages = $ngg_options['maxImages'];
    }

    $sluglist = array();

    switch ($type) {
        case 'tags':
            if (function_exists('get_the_tags')) { 
                $taglist = get_the_tags();
                
                if (is_array($taglist)) {
                    foreach ($taglist as $tag) {
                        $sluglist[] = $tag->slug;
                    }
                }
            }
        break;
            
        case 'category':
            $catlist = get_the_category();
            
            if (is_array($catlist)) {
                foreach ($catlist as $cat) {
                    $sluglist[] = $cat->category_nicename;
                }
            }
        break;
    }
    
    $sluglist = implode(',', $sluglist);
    $out = nggShowRelatedGallery($sluglist, $maxImages);
    
    return $out;
}

/**
 * nggpanoShowRandomRecent($type, $maxImages, $template, $galleryId) - return recent or random images
 * 
 * @access public
 * @param string $type 'id' (for latest addition to DB), 'date' (for image with the latest date), 'sort' (for image sorted by user order) or 'random'
 * @param integer $maxImages of images
 * @param string $template (optional) name for a template file, look for gallery-$template
 * @param int $galleryId Limit to a specific gallery
 * @return the content
 */
function nggpanoShowRandomRecent($type, $maxImages, $template = '', $galleryId = 0) {
    
    // $_GET from wp_query
    $pid    = get_query_var('pid');
    $pageid = get_query_var('pageid');
    
    // get now the recent or random images
    switch ($type) {
        case 'random':
            $picturelist = nggdb::get_random_images($maxImages, $galleryId);
            break;
        case 'id':
            $picturelist = nggdb::find_last_images(0, $maxImages, true, $galleryId, 'id');
            break;
        case 'date':
            $picturelist = nggdb::find_last_images(0, $maxImages, true, $galleryId, 'date');
            break;
        case 'sort':
            $picturelist = nggdb::find_last_images(0, $maxImages, true, $galleryId, 'sort');
            break;
        default:
            // default is by pid
            $picturelist = nggdb::find_last_images(0, $maxImages, true, $galleryId, 'id');
    }

    // look for ImageBrowser if we have a $_GET('pid')
    if ( $pageid == get_the_ID() || !is_home() )  
        if (!empty( $pid ))  {
            $out = nggCreateImageBrowser( $picturelist );
            return $out;
        }

    // go on if not empty
    if ( empty($picturelist) )
        return;
    
    // show gallery
    if ( is_array($picturelist) )
        $out = nggCreateGallery($picturelist, false, $template);

    $out = apply_filters('ngg_show_images_content', $out, $picturelist);
    
    return $out;
}

/**
 * nggpanoTagCloud() - return a tag cloud based on the wp core tag cloud system
 * 
 * @param array $args
 * @param string $template (optional) name for a template file, look for gallery-$template
 * @return the content
 */
function nggpanoTagCloud($args ='', $template = '') {
    global $nggRewrite;

    // $_GET from wp_query
    $tag     = get_query_var('gallerytag');
    $pageid  = get_query_var('pageid');
    
    // look for gallerytag variable 
    if ( $pageid == get_the_ID() || !is_home() )  {
        if (!empty( $tag ))  {
    
            $slug =  esc_attr( $tag );
            $out  =  nggShowGalleryTags( $slug );
            return $out;
        } 
    }
    
    $defaults = array(
        'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 45,
        'format' => 'flat', 'orderby' => 'name', 'order' => 'ASC',
        'exclude' => '', 'include' => '', 'link' => 'view', 'taxonomy' => 'ngg_tag'
    );
    $args = wp_parse_args( $args, $defaults );

    $tags = get_terms( $args['taxonomy'], array_merge( $args, array( 'orderby' => 'count', 'order' => 'DESC' ) ) ); // Always query top tags

    foreach ($tags as $key => $tag ) {

        $tags[ $key ]->link = $nggRewrite->get_permalink(array ('gallerytag' => $tag->slug));
        $tags[ $key ]->id = $tag->term_id;
    }
    
    $out = '<div class="ngg-tagcloud">' . wp_generate_tag_cloud( $tags, $args ) . '</div>';
    
    return $out;
}
?>