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
        
        add_shortcode( 'panoramic', array(&$this, 'show_panoramic' ) );
        add_shortcode( 'panoramicwithmap', array(&$this, 'show_panoramic_withmap' ) );
        add_shortcode( 'singlepicwithmap', array(&$this, 'show_singlepicwithmap') );
        add_shortcode( 'singlepicwithlinks', array(&$this, 'show_singlepicwithlinks' ) );
        add_shortcode( 'singlemap', array(&$this, 'show_map' ) );
        add_shortcode( 'panoramicgallery', array(&$this, 'show_panoramic_gallery' ) );
        add_shortcode( 'panoramicgallerywithmap', array(&$this, 'show_panoramic_gallery_withmap' ) );

    }

    /**
     * Function to show panorama(s):
     * 
     *     [panoramic id="10" float="none|left|right" w="" h="" link="url" "template="filename" caption="none|caption" /]
     *
     * where
     *  - id="1,2,4,5," ... id is one or several picture id
     *  - float is the CSS float property to apply to the thumbnail
     *  - width is width of the single picture you want to show (original width if this parameter is missing)
     *  - height is height of the single picture you want to show (original height if this parameter is missing)
     *  - link is optional and could link to a other url instead the full image
     *  - template is a name for a gallery template, which is located in themefolder/nggpano/templates or plugins/nextgen-gallery-panoramics/view
     *  - caption display or not the caption full|none|title|description
     * 
     * If the tag contains some text, this will be inserted as an additional caption to the picture too. Example:
     *      [panoramic id="10"]This is an additional caption[/panoramic]
     * This tag will show a picture with under it two HTML span elements containing respectively the alttext of the picture 
     * and the additional caption specified in the tag. 
     * 
     * @param array $atts
     * @param string $caption text
     * @return the content
     */
    function show_panoramic( $atts, $content = '' ) {
    
        extract(shortcode_atts(array(
            'id'        => 0,
            'w'         => '100%',
            'h'         => '100%',
            'caption'   => '',
            'float'     => '',
            'link'      => '',
            'template'  => ''
        ), $atts ));
        $out = nggpanoPanoramic($id, $w, $h, $float, $template, $content, $link , $caption );
            
        return $out;
    }

    
    /**
     * Function to show gallery of panorama:
     * 
     *     [panoramicgallery id="10" float="none|left|right" w="" h="" link="url" "template="filename" caption="none|caption" /]
     *
     * where
     *  - id="10" ... id of the gallery
     *  - float is the CSS float property to apply to the thumbnail
     *  - width is width of the single picture you want to show (original width if this parameter is missing)
     *  - height is height of the single picture you want to show (original height if this parameter is missing)
     *  - link is optional and could link to a other url instead the full image
     *  - template is a name for a gallery template, which is located in themefolder/nggpano/templates or plugins/nextgen-gallery-panoramics/view
     *  - caption display or not the caption full|none|title|description
     * 
     * If the tag contains some text, this will be inserted as an additional caption to the picture too. Example:
     *      [panoramicgallery id="10"]This is an additional caption[/panoramicgallery]
     * This tag will show a picture with under it two HTML span elements containing respectively the alttext of the picture 
     * and the additional caption specified in the tag. 
     * 
     * @param array $atts
     * @param string $caption text
     * @return the content
     */
    function show_panoramic_gallery( $atts, $content = '' ) {
    
        extract(shortcode_atts(array(
            'id'        => 0,
            'w'         => '100%',
            'h'         => '100%',
            'caption'   => '',
            'float'     => '',
            'link'      => '',
            'template'  => ''
        ), $atts ));

        $out = nggpanoGallery($id, $w, $h, $float, $template, $content, $link , $caption );
            
        return $out;
    }
    
    /**
     * Function to show panorama(s) with map under :
     * 
     *     [panoramicwithmap id="10" float="none|left|right" w="" h="" link="url" "template="filename" caption="full|none|title|description" mapw="" maph="" mapz="" maptype="HYBRID" /]
     *
     * where
     *  - id="1,2,4,5," ... id is one or several picture id
     *  - float is the CSS float property to apply to the thumbnail
     *  - width is width of the single picture you want to show (original width if this parameter is missing)
     *  - height is height of the single picture you want to show (original height if this parameter is missing)
     *  - link is optional and could link to a other url instead the full image
     *  - template is a name for a gallery template, which is located in themefolder/nggpano/templates or plugins/nextgen-gallery-panoramics/view
     *  - caption display or not the caption full|none|title|description
     *  - mapw, maph and mapz are width, height and zoom level for the map
     *  - maptype type of googlemap rendering HYBRID|ROADMAP|SATELLITE|TERRAIN
     * 
     * If the tag contains some text, this will be inserted as an additional caption to the picture too. Example:
     *      [panoramicwithmap id="10"]This is an additional caption[/panoramicwithmap]
     * This tag will show a picture with under it two HTML span elements containing respectively the alttext of the picture 
     * and the additional caption specified in the tag. 
     * 
     * @param array $atts
     * @param string $caption text
     * @return the content
     */
    function show_panoramic_withmap( $atts, $content = '' ) {
    
        extract(shortcode_atts(array(
            'id'        => 0,
            'w'         => '',
            'h'         => '',
            'float'     => '',
            'link'      => '',
            'template'  => 'withmap',
            'mapw'      => '250',
            'maph'      => '250',
            'mapz'      => '13',
            'maptype'   => 'HYBRID',
            'caption'   => ''
        ), $atts ));
        $out = nggpanoPanoramic($id, $w, $h, $float, $template, $content, $link, $caption, $mapw, $maph, $mapz, $maptype);
            
        return $out;
    }
    
    /**
     * Function to show panorama(s) with map under :
     * 
     *     [panoramicgallerywithmap id="10" float="none|left|right" w="" h="" link="url" "template="filename" caption="full|none|title|description" mapw="" maph="" mapz="" maptype="HYBRID" /]
     *
     * where
     *  - id="10" ... id of the gallery
     *  - float is the CSS float property to apply to the thumbnail
     *  - width is width of the single picture you want to show (original width if this parameter is missing)
     *  - height is height of the single picture you want to show (original height if this parameter is missing)
     *  - link is optional and could link to a other url instead the full image
     *  - template is a name for a gallery template, which is located in themefolder/nggpano/templates or plugins/nextgen-gallery-panoramics/view
     *  - caption display or not the caption full|none|title|description
     *  - mapw, maph and mapz are width, height and zoom level for the map
     *  - maptype type of googlemap rendering HYBRID|ROADMAP|SATELLITE|TERRAIN
     * 
     * If the tag contains some text, this will be inserted as an additional caption to the picture too. Example:
     *      [panoramicwithmap id="10"]This is an additional caption[/panoramicwithmap]
     * This tag will show a picture with under it two HTML span elements containing respectively the alttext of the picture 
     * and the additional caption specified in the tag. 
     * 
     * @param array $atts
     * @param string $caption text
     * @return the content
     */
    function show_panoramic_gallery_withmap( $atts, $content = '' ) {
    
        extract(shortcode_atts(array(
            'id'        => 0,
            'w'         => '',
            'h'         => '',
            'float'     => '',
            'link'      => '',
            'template'  => 'withmap',
            'mapw'      => '250',
            'maph'      => '250',
            'mapz'      => '13',
            'maptype'   => 'HYBRID',
            'caption'   => ''
        ), $atts ));
        $out = nggpanoGallery($id, $w, $h, $float, $template, $content, $link, $caption, $mapw, $maph, $mapz, $maptype);
            
        return $out;
    }
    
    
    /**
     * Function to show a single picture:
     * 
     *     [singlepicwithmap id="10" float="none|left|right" w="" h="" mode="none|watermark|web20" caption="full|none|title|description" link="url" "template="filename" mapw="" maph="" mapz="" maptype="HYBRID" /]
     *
     * where
     *  - id is one picture id
     *  - float is the CSS float property to apply to the thumbnail
     *  - width is width of the single picture you want to show (original width if this parameter is missing)
     *  - height is height of the single picture you want to show (original height if this parameter is missing)
     *  - mode is one of none, watermark or web20 (transformation applied to the picture)
     *  - link is optional and could link to a other url instead the full image
     *  - template is a name for a gallery template, which is located in themefolder/nggpano/templates or plugins/nextgen-gallery-panoramics/view
     *  - mapw, maph and mapz are width, height and zoom level for the map
     *  - maptype type of googlemap rendering HYBRID|ROADMAP|SATELLITE|TERRAIN
     *  - caption display or not the caption full|none|title|description
     * 
     * If the tag contains some text, this will be inserted as an additional caption to the picture too. Example:
     *      [singlepicwithmap id="10"]This is an additional caption[/singlepicwithmap]
     * This tag will show a picture with under it two HTML span elements containing respectively the alttext of the picture 
     * and the additional caption specified in the tag. 
     * 
     * @param array $atts
     * @param string $content text
     * @return the content
     */
    function show_singlepicwithmap( $atts, $content = '' ) {
    
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
            'maptype'   => 'HYBRID',
            'caption'   => ''
        ), $atts ));
    
        $out = nggpanoSinglePictureWithMap($id, $w, $h, $mode, $float, $template, $content, $link, $mapw, $maph, $mapz, $maptype, $caption);
            
        return $out;
    }

    /**
     * Function to show a single pic with links:
     *
     *     [singlepicwithlinks id="10" float="none|left|right" w="" h="" mode="none|watermark|web20" "template="filename" mapz="" maptype="HYBRID|ROADMAP|SATELLITE|TERRAIN" links="all|picture|map|pano" mainlink="picture|map|pano|none" caption="full|none|title|description" /]
     *
     * where
     *  - id is one picture id
     *  - float is the CSS float property to apply to the thumbnail
     *  - width is width of the single picture you want to show (original width if this parameter is missing)
     *  - height is height of the single picture you want to show (original height if this parameter is missing)
     *  - mode is one of none, watermark or web20 (transformation applied to the picture)
     *  - template is a name for a gallery template, which is located in themefolder/nggpano/templates or plugins/nextgen-gallery-panoramics/view
     *  - mapz zoom level for the map
     *  - maptype type of googlemap rendering HYBRID|ROADMAP|SATELLITE|TERRAIN
     *  - links links to display with the picture links all|picture|map|pano (possiblity to have 2 links : picture-map
     *  - mainlink link to follow when click on the thumbnail picture|map|pano|none
     *  - caption display or not the caption full|none|title|description
     * 
     * If the tag contains some text, this will be inserted as an additional caption to the picture too. Example:
     *      [singlepicwithlinks id="10"]This is an additional caption[/singlepicwithlinks]
     * This tag will show a picture with under it two HTML span elements containing respectively the alttext of the picture 
     * and the additional caption specified in the tag. 
     * 
     * @param array $atts
     * @param string $caption text
     * @return the content
     */
    function show_singlepicwithlinks( $atts, $content = '' ) {
    
        extract(shortcode_atts(array(
            'id'        => 0,
            'w'         => '100%',
            'h'         => '100%',
            'mode'      => '',
            'float'     => '',
            'template'  => '',
            'mapz'      => '13',
            'maptype'   => 'HYBRID',
            'links'     => 'ALL',
            'mainlink'  => 'PICTURE',
            'caption'   => ''
        ), $atts ));

        $out = nggpanoSinglePictureWithLinks($id, $w, $h, $mode, $float, $template, $content, $mapz, $maptype, $links, $mainlink, $caption);
            
        return $out;
    }
    
    
    /**
     * Function to show a single map with picture in infowindow:
     * 
     *     [singlemap id="10" float="none|left|right" w="" h="" zoom="" maptype="HYBRID|ROADMAP|SATELLITE|TERRAIN" "template="filename" links="all|picture|map|pano" mainlink="picture|map|pano|none" caption="full|none|title|description" thumbw="" thumbh="" /]

     *
     * where
     *  - id is one picture id
     *  - float is the CSS float property to apply to the thumbnail
     *  - template is a name for a gallery template, which is located in themefolder/nggpano/templates or plugins/nextgen-gallery-panoramics/view
     *  - captionmode to display or not captions for the image
     *  - w, h and zoom are width, height and zoom level for the map
     *  - maptype type of googlemap rendering HYBRID|ROADMAP|SATELLITE|TERRAIN
     *  - links links to display with the picture links all|picture|map|pano (possiblity to have 2 links : picture-map
     *  - mainlink link to follow when click on the thumbnail picture|map|pano|none
     * 
     * 
     * @param array $atts
     * @param string $caption text
     * @return the content
     */
    function show_map( $atts, $content = '' ) {
    
        extract(shortcode_atts(array(
            'id'        => 0,
            'w'         => '',
            'h'         => '',
            'zoom'      => '13',
            'maptype'   => 'HYBRID',
            'float'     => '',
            'template'  => '',
            'thumbw'      => '250',
            'thumbh'      => '100',
            'links'     => 'ALL',
            'mainlink'  => 'PICTURE',
            'caption'   => ''
        ), $atts ));
        $out = nggpanoSingleMap($id, $w, $h, $zoom, $maptype, $float, $template, $content, $thumbw, $thumbh, $links, $mainlink, $caption);
            
        return $out;
    }
    
 
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