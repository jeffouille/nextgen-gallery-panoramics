<?php
/**
 * @author Geoffroy DELEURY 
 * @copyright 2011
 * @since 1.0.0
 * @description Use WordPress Shortcode API for more features
 * @Docs http://codex.wordpress.org/Shortcode_API
 */

class NGPano_shortcodes {
    
    // register the new shortcodes
    function NGPano_shortcodes() {
		
        //Long posts should require a higher limit, see http://core.trac.wordpress.org/ticket/8553
        @ini_set('pcre.backtrack_limit', 500000);
    
        // do_shortcode on the_excerpt could causes several unwanted output. Uncomment it on your own risk
        // add_filter('the_excerpt', array(&$this, 'convert_shortcode'));
        // add_filter('the_excerpt', 'do_shortcode', 11);
        
        add_shortcode( 'singlepano', array(&$this, 'show_singlepano' ) );
        add_shortcode( 'singlepanowithmap', array(&$this, 'show_panowithmap' ) );
        add_shortcode( 'nggallery', array(&$this, 'show_gallery') );
        add_shortcode( 'imagebrowser', array(&$this, 'show_imagebrowser' ) );
        add_shortcode( 'slideshow', array(&$this, 'show_slideshow' ) );
        add_shortcode( 'nggtags', array(&$this, 'show_tags' ) );
        add_shortcode( 'thumb', array(&$this, 'show_thumbs' ) );
        add_shortcode( 'random', array(&$this, 'show_random' ) );
        add_shortcode( 'recent', array(&$this, 'show_recent' ) );
        add_shortcode( 'tagcloud', array(&$this, 'show_tagcloud' ) );
    }

    /**
     * Function to show a single panorama:
     * 
     *     [singlepano id="10" float="none|left|right" w="" h="" link="url" "template="filename" mode="none|caption" /]
     *
     * where
     *  - id is one picture id
     *  - float is the CSS float property to apply to the thumbnail
     *  - width is width of the single picture you want to show (original width if this parameter is missing)
     *  - height is height of the single picture you want to show (original height if this parameter is missing)
     *  - link is optional and could link to a other url instead the full image
     *  - template is a name for a gallery template, which is located in themefolder/nggpano/templates or plugins/nextgen-gallery-panoramics/view
     *  - mode
     * 
     * If the tag contains some text, this will be inserted as an additional caption to the picture too. Example:
     *      [singlepano id="10"]This is an additional caption[/singlepano]
     * This tag will show a picture with under it two HTML span elements containing respectively the alttext of the picture 
     * and the additional caption specified in the tag. 
     * 
     * @param array $atts
     * @param string $caption text
     * @return the content
     */
    function show_singlepano( $atts, $content = '' ) {
    
        extract(shortcode_atts(array(
            'id'        => 0,
            'w'         => '100%',
            'h'         => '100%',
            'mode'      => '',
            'float'     => '',
            'link'      => '',
            'template'  => ''
        ), $atts ));

        $out = nggpanoSinglePano($id, $w, $h, $mode, $float, $template, $content, $link );
            
        return $out;
    }
    
    /**
     * Function to show a single panorama:
     * 
     *     [singlepanowithmap id="10" float="none|left|right" w="" h="" link="url" "template="filename" mode="none|caption" mapw="" maph="" mapz="" maptype="HYBRID" /]
     *
     * where
     *  - id is one picture id
     *  - float is the CSS float property to apply to the thumbnail
     *  - width is width of the single picture you want to show (original width if this parameter is missing)
     *  - height is height of the single picture you want to show (original height if this parameter is missing)
     *  - link is optional and could link to a other url instead the full image
     *  - template is a name for a gallery template, which is located in themefolder/nggpano/templates or plugins/nextgen-gallery-panoramics/view
     *  - mode to display or not captions for the image
     *  - mapw, maph and mapz are width, height and zoom level for the map
     *  - maptype type of googlemap rendering HYBRID|ROADMAP|SATELLITE|TERRAIN
     * 
     * If the tag contains some text, this will be inserted as an additional caption to the picture too. Example:
     *      [singlepanowithmap id="10"]This is an additional caption[/singlepanowithmap]
     * This tag will show a picture with under it two HTML span elements containing respectively the alttext of the picture 
     * and the additional caption specified in the tag. 
     * 
     * @param array $atts
     * @param string $caption text
     * @return the content
     */
    function show_panowithmap( $atts, $content = '' ) {
    
        extract(shortcode_atts(array(
            'id'        => 0,
            'w'         => '',
            'h'         => '',
            'mode'      => '',
            'float'     => '',
            'link'      => '',
            'template'  => 'withmap',
            'mapw'      => '250',
            'maph'      => '250',
            'mapz'      => '13',
            'maptype'   => 'HYBRID'
        ), $atts ));
        $out = nggpanoSinglePano($id, $w, $h, $mode, $float, $template, $content, $link, $mapw, $maph, $mapz, $maptype );
            
        return $out;
    }

    /**
     * Function to show a collection of galleries:
     * 
     * [album id="1,2,4,5,..." template="filename" gallery="filename" /]
     * where 
     * - id of a album
     * - template is a name for a album template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
     * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
     * 
     * @param array $atts
     * @return the_content
     */
    function show_album( $atts ) {
    
        extract(shortcode_atts(array(
            'id'        => 0,
            'template'  => 'extend',
            'gallery'   => ''  
        ), $atts ));
        
        $out = nggShowAlbum($id, $template, $gallery);
            
        return $out;
    }
    /**
     * Function to show a thumbnail or a set of thumbnails with shortcode of type:
     * 
     * [gallery id="1,2,4,5,..." template="filename" images="number of images per page" /]
     * where 
     * - id of a gallery
     * - images is the number of images per page (optional), 0 will show all images
     * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
     * 
     * @param array $atts
     * @return the_content
     */
    function show_gallery( $atts ) {
        
        global $wpdb;
        
        extract(shortcode_atts(array(
            'id'        => 0,
            'template'  => '',  
            'images'    => false
        ), $atts ));
        
        // backward compat for user which uses the name instead, still deprecated
        if( !is_numeric($id) )
            $id = $wpdb->get_var( $wpdb->prepare ("SELECT gid FROM $wpdb->nggallery WHERE name = '%s' ", $id) );
            
        $out = nggShowGallery( $id, $template, $images );
            
        return $out;
    }

    function show_imagebrowser( $atts ) {
        
        global $wpdb;
    
        extract(shortcode_atts(array(
            'id'        => 0,
            'template'  => ''   
        ), $atts ));

        $out = nggShowImageBrowser($id, $template);
            
        return $out;
    }
    
    function show_slideshow( $atts ) {
        
        global $wpdb;
    
        extract(shortcode_atts(array(
            'id'        => 0,
            'w'         => '',
            'h'         => ''
        ), $atts ));
        
        if( !is_numeric($id) )
            $id = $wpdb->get_var( $wpdb->prepare ("SELECT gid FROM $wpdb->nggallery WHERE name = '%s' ", $id) );

        if( !empty( $id ) )
            $out = nggShowSlideshow($id, $w, $h);
        else 
            $out = __('[Gallery not found]','nggallery');
            
        return $out;
    }
    
    function show_tags( $atts ) {
    
        extract(shortcode_atts(array(
            'gallery'       => '',
            'album'         => ''
        ), $atts ));
        
        if ( !empty($album) )
            $out = nggShowAlbumTags($album);
        else
            $out = nggShowGalleryTags($gallery);
        
        return $out;
    }

    /**
     * Function to show a thumbnail or a set of thumbnails with shortcode of type:
     * 
     * [thumb id="1,2,4,5,..." template="filename" /]
     * where 
     * - id is one or more picture ids
     * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
     * 
     * @param array $atts
     * @return the_content
     */
    function show_thumbs( $atts ) {
    
        extract(shortcode_atts(array(
            'id'        => '',
            'template'  => ''
        ), $atts));
        
        // make an array out of the ids
        $pids = explode( ',', $id );
        
        // Some error checks
        if ( count($pids) == 0 )
            return __('[Pictures not found]','nggallery');
        
        $picturelist = nggdb::find_images_in_list( $pids );
        
        // show gallery
        if ( is_array($picturelist) )
            $out = nggCreateGallery($picturelist, false, $template);
        
        return $out;
    }

    /**
     * Function to show a gallery of random or the most recent images with shortcode of type:
     * 
     * [random max="7" template="filename" id="2" /]
     * [recent max="7" template="filename" id="3" mode="date" /]
     * where 
     * - max is the maximum number of random or recent images to show
     * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
     * - id is the gallery id, if the recent/random pictures shall be taken from a specific gallery only
     * - mode is either "id" (which takes the latest additions to the databse, default) 
     *               or "date" (which takes the latest pictures by EXIF date) 
     *               or "sort" (which takes the pictures by user sort order)
     * 
     * @param array $atts
     * @return the_content
     */
    function show_random( $atts ) {
    
        extract(shortcode_atts(array(
            'max'       => '',
            'template'  => '',
            'id'        => 0
        ), $atts));
        
        $out = nggShowRandomRecent('random', $max, $template, $id);
        
        return $out;
    }

    function show_recent( $atts ) {
    
        extract(shortcode_atts(array(
            'max'       => '',
            'template'  => '',
            'id'        => 0,
            'mode'      => 'id'
        ), $atts));
        
        $out = nggShowRandomRecent($mode, $max, $template, $id);
        
        return $out;
    }

    /**
     * Shortcode for the Image tag cloud
     * Usage : [tagcloud template="filename" /]
     * 
     * @param array $atts
     * @return the content
     */
    function show_tagcloud( $atts ) {
    
        extract(shortcode_atts(array(
            'template'  => ''
        ), $atts));
        
        $out = nggTagCloud( '', $template );
        
        return $out;
    }

}

// let's use it
$nggpanoShortcodes = new NGPano_shortcodes;    

?>