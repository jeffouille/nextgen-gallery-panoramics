<?php

if ( !defined('ABSPATH') )
    die('You are not allowed to call this page directly.');
    
global $wpdb, $nggdb, $ngg;


// use defaults the first time
$width  = empty ($ngg->options['publish_width'])  ? $ngg->options['thumbwidth'] : $ngg->options['publish_width'];
$height = empty ($ngg->options['publish_height']) ? $ngg->options['thumbheight'] : $ngg->options['publish_height'];
$align  = empty ($ngg->options['publish_align'])  ? 'none' : $ngg->options['publish_align'];

$default_maptype = 'hybrid';
$default_zoom   = 10;
$default_caption = 'none';
$default_links = 'none';
$default_mainlink = 'picture';

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>NGG Panoramics</title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>

        <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/jquery-ui-1.8.custom.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/plugins/localisation/jquery.localisation-min.js"></script>

	<script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/jquery.multiselect.min.js"></script>

        <script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/jquery.multiselect.filter.min.js"></script>
        
	<script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/tinymce/tinymce.js"></script>
        
    <link type="text/css" rel="stylesheet" href="<?php echo NGGPANOGALLERY_URLPATH ?>admin/css/tinymce.css" /> 
    <link type="text/css" rel="stylesheet" href="<?php echo NGGPANOGALLERY_URLPATH ?>admin/css/themes/smoothness/jquery-ui-1.7.1.custom.css" />
    <link type="text/css" href="<?php echo NGGPANOGALLERY_URLPATH ?>admin/css/jquery.multiselect.css" rel="stylesheet" />
    <link type="text/css" href="<?php echo NGGPANOGALLERY_URLPATH ?>admin/css/jquery.multiselect.filter.css" rel="stylesheet" />
    <base target="_self" />


    </head>

<script type="text/javascript">
jQuery(document).ready(function(){ 
    
    
    //nggpanoinit();

    jQuery.localise('jquery.multiselect', {/*language: 'fr',/* */ path: '<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/i18n/'});
    jQuery.localise('jquery.multiselect.filter', {/*language: 'fr',/* */ path: '<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/i18n/'});

    //Panoramics list
    var photoselect = jQuery("#panoramics").multiselect({
            noneSelectedText: '<?php _e("Select one or more panoramics", 'nggpano'); ?>',
            selectedList: 2
        }).multiselectfilter();//apply the plugin
        
    photoselect.multiselect('disable'); //disable it initially
    
    //Galleries list
    var galleryselect = jQuery("#galleries").multiselect({
            noneSelectedText: '<?php _e("Select one gallery", 'nggpano'); ?>',
            selectedList: 1,
            multiple: false

        }).multiselectfilter();//apply the plugin
        
    galleryselect.multiselect('disable'); //disable it initially

    var settings = { method: 'tinyselect',
                    type: 'image',
                    format: 'json',
                    nggpano_callback: 'json',
                    term: ''};

    var domain = '<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>';
    
    var jqxhr = jQuery.getJSON(domain, settings, function(jsonresponse) {
        if (jsonresponse.length > 0) {

            jQuery.each(jsonresponse, function(index,gallery){
                    
                //panoramics selct populate
                photoselect.multiselect('enable');
                
                var optiongroup = jQuery("<optgroup></optgroup>").attr('label',gallery.label+' ('+gallery.counter+')');

                jQuery.each(gallery.pictures, function(indexp,picture){
                    optiongroup.append(jQuery("<option></option>").val(picture.id).html(picture.label));
                });
                jQuery("#panoramics").append(optiongroup);
                
                //Gallery select populate
                galleryselect.multiselect('enable');
                if (gallery.counter > -1) {
                    jQuery("#galleries").append(jQuery("<option></option>").val(gallery.id).html(gallery.label+' ('+gallery.counter+')'));
                }
 
            })
            
        } else {
            jQuery("#panoramics").empty().append('<option selected="selected" value="0"><?php _e("No panoramic available", 'nggpano'); ?><option>');
            jQuery("#galleries").empty().append('<option selected="selected" value="0"><?php _e("No gallery available", 'nggpano'); ?><option>');
        }
        
        jQuery("#panoramics").multiselect('refresh'); //refresh the select here
        jQuery("#galleries").multiselect('refresh'); //refresh the select here
        
        })
        .error(function() {
                jQuery("#panoramics").empty().append('<option selected="selected" value="0"><?php _e("Error getting panoramics", 'nggpano'); ?><option>');
                jQuery("#galleries").empty().append('<option selected="selected" value="0"><?php _e("Error getting galleries", 'nggpano'); ?><option>');
                jQuery("#panoramics").multiselect('refresh'); //refresh the select here
                jQuery("#galleries").multiselect('refresh'); //refresh the select here
                }
            );
            

    //Albums list
    var albumselect = jQuery("#albums").multiselect({
            noneSelectedText: '<?php _e("Select one album", 'nggpano'); ?>',
            selectedList: 1,
            multiple: false

        }).multiselectfilter();//apply the plugin
        
    albumselect.multiselect('disable'); //disable it initially 
    
    var settingsAlbum = { method: 'tinyselect',
                    type: 'album',
                    format: 'json',
                    nggpano_callback: 'json',
                    term: ''};

    var jqxhAlbum = jQuery.getJSON(domain, settingsAlbum, function(jsonresponse) {
        if (jsonresponse.length > 0) {

            jQuery.each(jsonresponse, function(index, album){

                //Album select populate
                albumselect.multiselect('enable');
                jQuery("#albums").append(jQuery("<option></option>").val(album.id).html(album.label));
 
            })
            
        } else {
            jQuery("#albums").empty().append('<option selected="selected" value="0"><?php _e("No album available", 'nggpano'); ?><option>');
        }

        jQuery("#albums").multiselect('refresh'); //refresh the select here
        
    })
    .error(function() {
            jQuery("#albums").empty().append('<option selected="selected" value="0"><?php _e("Error getting albums", 'nggpano'); ?><option>');
            jQuery("#albums").multiselect('refresh'); //refresh the select here
        }
    );

    //Hide show options
    
    jQuery("tr.shortcode_options").hide();
    jQuery("#choice_links").hide();
    
    jQuery("tr.gallery_shortcode_options").hide();
    jQuery("#gallery-choice_links").hide();
    
    jQuery("tr.album_shortcode_options").hide();
    jQuery("#album-choice_links").hide();
    
    jQuery("input[name='panoShortcodetype']").change(function ()
    {
        
        var selected_shortcode = jQuery(this).attr('value');
        //console.log(selected_shortcode);
        jQuery("tr.shortcode_options").hide();
        jQuery("tr."+selected_shortcode).show();
        
        if(selected_shortcode == 'singlepicwithmap' || selected_shortcode == 'singlepicwithlinks' || selected_shortcode == 'singlemap') {
            if(photoselect.multiselect('option','multiple')) {
                photoselect.multiselect('destroy').multiselectfilter("destroy");
                photoselect = jQuery("#panoramics").multiselect({
                                noneSelectedText: '<?php _e("Select one panoramic", 'nggpano'); ?>',
                                selectedList: 1,
                                multiple: false
                    }).multiselectfilter();
                jQuery("#panoramics").multiselect('uncheckAll');
            }
                
        } else {
            if(!photoselect.multiselect('option','multiple')) {
                photoselect.multiselect('destroy').multiselectfilter("destroy");
                photoselect = jQuery("#panoramics").multiselect({
                                noneSelectedText: '<?php _e("Select one or more panoramics", 'nggpano'); ?>',
                                selectedList: 2,
                                multiple: true
                    }).multiselectfilter();
                jQuery("#panoramics").multiselect('uncheckAll');
            }
        }
        
     });//.change();

    jQuery("input[name='galleryShortcodetype']").change(function ()
    {
        
        var selected_shortcode = jQuery(this).attr('value');
        //console.log(selected_shortcode);
        jQuery("tr.gallery_shortcode_options").hide();
        jQuery("tr."+selected_shortcode).show();
  
     });//.change();

    jQuery("input[name='albumShortcodetype']").change(function ()
    {
        
        var selected_shortcode = jQuery(this).attr('value');
        //console.log(selected_shortcode);
        jQuery("tr.album_shortcode_options").hide();
        jQuery("tr."+selected_shortcode).show();
  
     });//.change();


    jQuery("input[name='panoLinks']").change( function() {
        if (jQuery(this).attr('value')=='select') {
            jQuery("#choice_links").show();
        } else {
            jQuery("#choice_links").hide();
        }

    });
    jQuery("tr.shortcode_options").hide();
    jQuery("tr.panoramic").show();


    jQuery("input[name='galleryLinks']").change( function() {
        if (jQuery(this).attr('value')=='select') {
            jQuery("#gallery-choice_links").show();
        } else {
            jQuery("#gallery-choice_links").hide();
        }

    });

    jQuery("tr.gallery_shortcode_options").hide();
    jQuery("tr.gallery").show();


    jQuery("input[name='albumLinks']").change( function() {
        if (jQuery(this).attr('value')=='select') {
            jQuery("#album-choice_links").show();
        } else {
            jQuery("#album-choice_links").hide();
        }

    });

    jQuery("tr.album_shortcode_options").hide();
    jQuery("tr.album").show();


});
</script>
    
<body id="shortcode" onload="tinyMCEPopup.executeOnLoad('nggpanoinit();');document.body.style.display='';" style="display: none">
    <form name="NGGPano" action="#">
        <div class="tabs">
            <ul>
                <li id="panoramic_tab" class="current"><span><a href="javascript:mcTabs.displayTab('panoramic_tab','panoramic_panel');" onmousedown="return false;"><?php _e('Panoramique', 'nggpano'); ?></a></span></li>
                <li id="gallery_tab"><span><a href="javascript:mcTabs.displayTab('gallery_tab','gallery_panel');" onmousedown="return false;"><?php echo _n( 'Gallery', 'Galleries', 1, 'nggpano' ) ?></a></span></li>
                <li id="album_tab"><span><a href="javascript:mcTabs.displayTab('album_tab','album_panel');" onmousedown="return false;"><?php echo _n( 'Album', 'Albums', 1, 'nggpano' ) ?></a></span></li>		
            </ul>
        </div>
	
	<div class="panel_wrapper">

            <!-- panoramic panel -->
            <div id="panoramic_panel" class="panel current">
                <p class="hidden" id="panoCallback"></p>
                <table border="0" cellpadding="4" cellspacing="0">
                    <!-- Shortcode -->
                    <tr>
                        <td nowrap="nowrap" valign="middle"><label for="panoShortcodetype"><?php _e("Show as", 'nggpano'); ?></label></td>
                        <td>
                            <label><input name="panoShortcodetype" type="radio" value="panoramic" checked="checked" /> <?php _e('Simple Panoramic', 'nggpano');?> [panoramic]</label><br />
                            <label><input name="panoShortcodetype" type="radio" value="panoramicwithmap" /> <?php _e('Panoramic with map','nggpano') ?> [panoramicwithmap]</label><br />
                            <label><input name="panoShortcodetype" type="radio" value="singlepicwithmap" /> <?php _e('Picture with map','nggpano') ?> [singlepicwithmap]</label><br />
                            <label><input name="panoShortcodetype" type="radio" value="singlepicwithlinks" /> <?php _e('Picture with links','nggpano') ?> [singlepicwithlinks]</label><br />
                            <label><input name="panoShortcodetype" type="radio" value="singlemap" /> <?php _e('Map with infowindow','nggpano') ?> [singlemap]</label>
                        </td>
                    </tr>
                    <!-- Panoramic -->
                    <tr>
                        <td nowrap="nowrap" valign="middle"><label for="panoramics"><?php _e("Panoramic", 'nggpano'); ?></label></td>
                        <td>
                            <select id="panoramics" class="multiselect" multiple="multiple" name="panoramics[]"></select>
                        </td>
                    </tr>

                    <!-- Dimensions (Width x Height) -->
                    <tr class="shortcode_options panoramic panoramicwithmap singlepicwithmap singlepicwithlinks">
                        <td nowrap="nowrap" valign="middle"><?php _e("Width x Height", 'nggpano'); ?></td>
                        <td>
                            <input type="text" size="5" id="panoWidth" name="panoWidth" value="<?php echo $width; ?>" /> x <input type="text" size="5" id="panoHeight" name="panoHeight" value="<?php echo $height; ?>" />
                            <br /><small><?php _e('Empty = 100%','nggpano') ?> - <?php _e('Add % sign to get dimension in percentage','nggpano') ?></small>
                        </td>
                    </tr>
                    <!-- Alignement -->
                    <tr>
                        <td nowrap="nowrap" valign="middle"><?php _e('Alignment','nggallery') ?></td>
                        <td>
                            <label for="image-align-none"><input name="panoAlign" type="radio" id="image-align-none" value="none" <?php checked('none', $align); ?> /><?php _e('None','nggpano'); ?></label>
                            <label for="image-align-left"><input name="panoAlign" type="radio" id="image-align-left" value="left" <?php checked('left', $align); ?> /><?php _e('Left','nggpano'); ?></label>
                            <label for="image-align-center"><input name="panoAlign" type="radio" id="image-align-center" value="center" <?php checked('center', $align); ?> /><?php _e('Center','nggpano'); ?></label>
                            <label for="image-align-right"><input name="panoAlign" type="radio" id="image-align-right" value="right" <?php checked('right', $align); ?> /><?php _e('Right','nggpano'); ?></label>
                        </td>
                    </tr>
                    <!-- Legend mode -->
                    <tr>
                        <td nowrap="nowrap" valign="middle"><?php _e('Caption Mode','nggpano') ?></td>
                        <td>
                            <label for="captiontype-none"><input name="panoCaptiontype" type="radio" id="captiontype-none" value="none" <?php checked('none', $default_caption); ?> /><?php _e('None','nggpano'); ?></label>
                            <label for="captiontype-full"><input name="panoCaptiontype" type="radio" id="captiontype-full" value="full" <?php checked('full', $default_caption); ?> /><?php _e('Full','nggpano'); ?></label>
                            <label for="captiontype-title"><input name="panoCaptiontype" type="radio" id="captiontype-title" value="title" <?php checked('title', $default_caption); ?> /><?php _e('Title','nggpano'); ?></label>
                            <label for="captiontype-description"><input name="panoCaptiontype" type="radio" id="captiontype-description" value="description" <?php checked('description', $default_caption); ?> /><?php _e('Description','nggpano'); ?></label>
                            <br /><small><?php _e('Caption to show under the item','nggpano') ?></small>
                        </td>
                    </tr>
                    
                    <!-- Picture Effect -->
                    <tr class="shortcode_options singlepicwithmap singlepicwithlinks">
                        <td nowrap="nowrap" valign="middle"><?php _e("Effect", 'nggpano'); ?></td>
                        <td>
                            <label>
                                <select id="panoEffect" name="panoEffect">
                                    <option value="none"><?php _e("No effect", 'nggpano'); ?></option>
                                    <option value="watermark"><?php _e("Watermark", 'nggpano'); ?></option>
                                    <option value="web20"><?php _e("Web 2.0", 'nggpano'); ?></option>
                                </select>
                            </label>
                        </td>
                    </tr>
                    <!-- Map Dimension -->
                    <tr class="shortcode_options panoramicwithmap singlepicwithmap singlemap">
                        <td nowrap="nowrap" valign="middle"><?php _e("Map", 'nggpano'); ?></td>
                        <td>
                            <table>
                                <tr>
                                    <td valign="top" style="text-align: center; width: 50%"><?php _e('Map Width x height (in pixel)','nggpano') ?></td>
                                    <td nowrap="nowrap" valign="top" style="text-align: center; width: 50%"><?php _e('Map Zoom','nggpano') ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; width: 50%">
                                        <input type="text" size="5" maxlength="5" name="panoMapW" id="panoMapW" value="<?php echo $width; ?>" /> x <input type="text" size="5" maxlength="5" name="panoMapH" id="panoMapH" value="<?php echo $height; ?>" />
                                        <br /><small><?php _e('Size of the map','nggpano') ?></small>
                                    </td>
                                    <td style="text-align: center; width: 50%">
                                        <select style="margin: 0pt; padding: 0pt;" id="panoMapZ" name="panoMapZ">
                                            <?php 
                                            for ($i = 1; $i < 19; $i++) {
                                                echo '<option '.(($i == $default_zoom) ? 'selected="selected"' : '') .' value="'.$i.'">'.$i.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Map Type -->
                    <tr class="shortcode_options panoramicwithmap singlepicwithmap singlepicwithlinks singlemap">
                        <td nowrap="nowrap" valign="middle"><?php _e('Map Type','nggpano') ?></td>
                        <td>
                            <input type="radio" value="hybrid" <?php checked('hybrid', $default_maptype); ?> id="maptype-hybrid" name="panoMaptype"/>
                            <label class="align" for="maptype-hybrid"><?php _e('Hybrid','nggpano'); ?></label>
                            <input type="radio" value="roadmap" <?php checked('roadmap', $default_maptype); ?> id="maptype-roadmap" name="panoMaptype"/>
                            <label class="align" for="maptype-roadmap"><?php _e('Roadmap','nggpano'); ?></label>
                            <input type="radio" value="satellite" <?php checked('satellite', $default_maptype); ?> id="maptype-satellite" name="panoMaptype"/>
                            <label class="align" for="maptype-satellite"><?php _e('Satellite','nggpano'); ?></label>
                            <input type="radio" value="terrain" <?php checked('terrain', $default_maptype); ?> id="maptype-terrain" name="panoMaptype"/>
                            <label class="align" for="maptype-terrain"><?php _e('Terrain','nggpano'); ?></label>
                        </td>
                    </tr>
                    <!-- Links to show -->
                    <tr valign="top" class="shortcode_options singlepicwithlinks singlemap">
                        <td nowrap="nowrap" valign="top"><?php _e('Links to show','nggpano') ?></td>
                        <td>
                            <input type="radio" value="none" <?php checked('none', $default_links); ?> id="links-none" name="panoLinks"/>
                            <label class="align" for="links-none"><?php _e('None','nggpano'); ?></label>
                            <input type="radio" value="all" <?php checked('all', $default_links); ?> id="links-all" name="panoLinks"/>
                            <label class="align" for="links-all"><?php _e('All links','nggpano'); ?></label>
                            <input type="radio" value="select" <?php checked('select', $default_links); ?> id="links-select" name="panoLinks"/>
                            <label class="align" for="links-select"><?php _e('Selected links','nggpano'); ?></label>
                            <br /><small><?php _e('Links to show under the item','nggpano') ?></small>
                            <div id="choice_links">
                                <input type="checkbox" value="picture" <?php checked('picture', $default_links); ?> id="links-picture" class="panoLinksChoice"/>
                                <label class="align" for="links-picture"><?php _e('Picture','nggpano'); ?></label>
                                <input type="checkbox" value="map" <?php checked('map', $default_links); ?> id="links-map" class="panoLinksChoice"/>
                                <label class="align" for="links-map"><?php _e('Map','nggpano'); ?></label>
                                <input type="checkbox" value="pano" <?php checked('pano', $default_links); ?> id="links-pano" class="panoLinksChoice"/>
                                <label class="align" for="links-pano"><?php _e('Panoramic','nggpano'); ?></label>
                            </div>
                            
                        </td>
                    </tr>
                    <!-- Main Link -->
                    <tr valign="top" class="shortcode_options singlepicwithlinks singlemap">
                        <td nowrap="nowrap" valign="middle"><?php _e('Main Link','nggpano') ?></td>
                        <td>
                            <input type="radio" value="none" <?php checked('none', $default_caption); ?> id="mainlink-none" name="panoMainlink"/>
                            <label class="align" for="mainlink-none"><?php _e('None','nggpano'); ?></label>
                            <input type="radio" value="picture" <?php checked('picture', $default_caption); ?> id="mainlink-picture" name="panoMainlink"/>
                            <label class="align" for="mainlink-picture"><?php _e('Picture','nggpano'); ?></label>
                            <input type="radio" value="map" <?php checked('map', $default_caption); ?> id="mainlink-map" name="panoMainlink"/>
                            <label class="align" for="mainlink-map"><?php _e('Map','nggpano'); ?></label>
                            <input type="radio" value="pano" <?php checked('pano', $default_caption); ?> id="mainlink-pano" name="panoMainlink"/>
                            <label class="align" for="mainlink-pano"><?php _e('Panoramic','nggpano'); ?></label>
                            <br /><small><?php _e('Link when click on the item','nggpano') ?></small>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- single pic panel -->
            
            <!-- gallery panel -->
            <div id="gallery_panel" class="panel">
                <p class="hidden" id="galleryCallback"></p>
                <table border="0" cellpadding="4" cellspacing="0">
                    <tr>
                        <td nowrap="nowrap"><label for="galleries"><?php _e("Gallery", 'nggpano'); ?></label></td>
                        <td>
                            <select id="galleries" class="multiselect" multiple="multiple" name="galleries[]"></select>
                        </td>
                    </tr>
                    <!-- Shortcode -->
                    <tr>
                        <td nowrap="nowrap" valign="middle"><label for="galleryShortcodetype"><?php _e("Show as", 'nggpano'); ?></label></td>
                        <td>
                            <label><input name="galleryShortcodetype" type="radio" value="panoramicgallery" checked="checked" /> <?php _e('Gallery of Panoramics', 'nggpano');?> [panoramicgallery]</label><br />
                            <label><input name="galleryShortcodetype" type="radio" value="panoramicgallerywithmap" /> <?php _e('Gallery of Panoramics with map','nggpano') ?> [panoramicgallerywithmap]</label><br />
                            <label><input name="galleryShortcodetype" type="radio" value="gallerymap" /> <?php _e('Map with all panoramics of a gallery','nggpano') ?> [gallerymap]</label><br />
                        </td>
                    </tr>
                    <!-- Dimensions (Width x Height) -->
                    <tr class="gallery_shortcode_options gallery panoramicgallery panoramicgallerywithmap">
                        <td nowrap="nowrap" valign="middle"><?php _e("Width x Height", 'nggpano'); ?></td>
                        <td>
                            <input type="text" size="5" id="galleryWidth" name="galleryWidth" value="<?php echo $width; ?>" /> x <input type="text" size="5" id="galleryHeight" name="galleryHeight" value="<?php echo $height; ?>" />
                            <br /><small><?php _e('Empty = 100%','nggpano') ?> - <?php _e('Add % sign to get dimension in percentage','nggpano') ?></small>
                        </td>
                    </tr>
                    <!-- Alignement -->
                    <tr>
                        <td nowrap="nowrap" valign="middle"><?php _e('Alignment','nggallery') ?></td>
                        <td>
                            <label for="gallery-align-none"><input name="galleryAlign" type="radio" id="gallery-align-none" value="none" <?php checked('none', $align); ?> /><?php _e('None','nggpano'); ?></label>
                            <label for="gallery-align-left"><input name="galleryAlign" type="radio" id="gallery-align-left" value="left" <?php checked('left', $align); ?> /><?php _e('Left','nggpano'); ?></label>
                            <label for="gallery-align-center"><input name="galleryAlign" type="radio" id="gallery-align-center" value="center" <?php checked('center', $align); ?> /><?php _e('Center','nggpano'); ?></label>
                            <label for="gallery-align-right"><input name="galleryAlign" type="radio" id="gallery-align-right" value="right" <?php checked('right', $align); ?> /><?php _e('Right','nggpano'); ?></label>
                        </td>
                    </tr>

                    <!-- Map Dimension -->
                    <tr class="gallery_shortcode_options panoramicgallerywithmap gallerymap">
                        <td nowrap="nowrap" valign="middle"><?php _e("Map", 'nggpano'); ?></td>
                        <td>
                            <table>
                                <tr>
                                    <td valign="top" style="text-align: center; width: 50%"><?php _e('Map Width x height (in pixel)','nggpano') ?></td>
                                    <td nowrap="nowrap" valign="top" style="text-align: center; width: 50%"><?php _e('Map Zoom','nggpano') ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; width: 50%">
                                        <input type="text" size="5" maxlength="5" name="galleryMapW" id="galleryMapW" value="<?php echo $width; ?>" /> x <input type="text" size="5" maxlength="5" name="galleryMapH" id="galleryMapH" value="<?php echo $height; ?>" />
                                        <br /><small><?php _e('Size of the map','nggpano') ?></small>
                                    </td>
                                    <td style="text-align: center; width: 50%">
                                        <select style="margin: 0pt; padding: 0pt;" id="galleryMapZ" name="galleryMapZ">
                                            <?php 
                                            for ($i = 1; $i < 19; $i++) {
                                                echo '<option '.(($i == $default_zoom) ? 'selected="selected"' : '') .' value="'.$i.'">'.$i.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Map Type -->
                    <tr class="gallery_shortcode_options panoramicgallerywithmap gallerymap">
                        <td nowrap="nowrap" valign="middle"><?php _e('Map Type','nggpano') ?></td>
                        <td>
                            <input type="radio" value="hybrid" <?php checked('hybrid', $default_maptype); ?> id="gallery-maptype-hybrid" name="galleryMaptype"/>
                            <label class="align" for="gallery-maptype-hybrid"><?php _e('Hybrid','nggpano'); ?></label>
                            <input type="radio" value="roadmap" <?php checked('roadmap', $default_maptype); ?> id="gallery-maptype-roadmap" name="galleryMaptype"/>
                            <label class="align" for="gallery-maptype-roadmap"><?php _e('Roadmap','nggpano'); ?></label>
                            <input type="radio" value="satellite" <?php checked('satellite', $default_maptype); ?> id="gallery-maptype-satellite" name="galleryMaptype"/>
                            <label class="align" for="gallery-maptype-satellite"><?php _e('Satellite','nggpano'); ?></label>
                            <input type="radio" value="terrain" <?php checked('terrain', $default_maptype); ?> id="gallery-maptype-terrain" name="galleryMaptype"/>
                            <label class="align" for="gallery-maptype-terrain"><?php _e('Terrain','nggpano'); ?></label>
                        </td>
                    </tr>
                    <!-- Links to show -->
                    <tr valign="top" class="gallery_shortcode_options gallerymap">
                        <td nowrap="nowrap" valign="top"><?php _e('Links to show','nggpano') ?></td>
                        <td>
                            <input type="radio" value="none" <?php checked('none', $default_links); ?> id="gallery-links-none" name="galleryLinks"/>
                            <label class="align" for="gallery-links-none"><?php _e('None','nggpano'); ?></label>
                            <input type="radio" value="all" <?php checked('all', $default_links); ?> id="gallery-links-all" name="galleryLinks"/>
                            <label class="align" for="gallery-links-all"><?php _e('All links','nggpano'); ?></label>
                            <input type="radio" value="select" <?php checked('select', $default_links); ?> id="gallery-links-select" name="galleryLinks"/>
                            <label class="align" for="gallery-links-select"><?php _e('Selected links','nggpano'); ?></label>
                            <br /><small><?php _e('Links to show under the item','nggpano') ?></small>
                            <div id="gallery-choice_links">
                                <input type="checkbox" value="picture" <?php checked('picture', $default_links); ?> id="gallery-links-picture" class="galleryLinksChoice"/>
                                <label class="align" for="gallery-links-picture"><?php _e('Picture','nggpano'); ?></label>
                                <input type="checkbox" value="map" <?php checked('map', $default_links); ?> id="gallery-links-map" class="galleryLinksChoice"/>
                                <label class="align" for="gallery-links-map"><?php _e('Map','nggpano'); ?></label>
                                <input type="checkbox" value="pano" <?php checked('pano', $default_links); ?> id="gallery-links-pano" class="galleryLinksChoice"/>
                                <label class="align" for="gallery-links-pano"><?php _e('Panoramic','nggpano'); ?></label>
                            </div>
                            
                        </td>
                    </tr>
                    <!-- Main Link -->
                    <tr valign="top" class="gallery_shortcode_options gallerymap">
                        <td nowrap="nowrap" valign="middle"><?php _e('Main Link','nggpano') ?></td>
                        <td>
                            <input type="radio" value="none" <?php checked('none', $default_caption); ?> id="gallery-mainlink-none" name="galleryMainlink"/>
                            <label class="align" for="gallery-mainlink-none"><?php _e('None','nggpano'); ?></label>
                            <input type="radio" value="picture" <?php checked('picture', $default_caption); ?> id="gallery-mainlink-picture" name="galleryMainlink"/>
                            <label class="align" for="gallery-mainlink-picture"><?php _e('Picture','nggpano'); ?></label>
                            <input type="radio" value="map" <?php checked('map', $default_caption); ?> id="gallery-mainlink-map" name="galleryMainlink"/>
                            <label class="align" for="gallery-mainlink-map"><?php _e('Map','nggpano'); ?></label>
                            <input type="radio" value="pano" <?php checked('pano', $default_caption); ?> id="gallery-mainlink-pano" name="galleryMainlink"/>
                            <label class="align" for="gallery-mainlink-pano"><?php _e('Panoramic','nggpano'); ?></label>
                            <br /><small><?php _e('Link when click on the item','nggpano') ?></small>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- gallery panel -->
            
            <!-- album panel -->
            <div id="album_panel" class="panel">
                <p class="hidden" id="albumCallback"></p>
                <table border="0" cellpadding="4" cellspacing="0">
                    <tr>
                        <td nowrap="nowrap"><label for="galleries"><?php _e("Album", 'nggpano'); ?></label></td>
                        <td>
                            <select id="albums" class="multiselect" multiple="multiple" name="albums[]"></select>
                        </td>
                    </tr>
                    <!-- Shortcode -->
                    <tr>
                        <td nowrap="nowrap" valign="middle"><label for="albumShortcodetype"><?php _e("Show as", 'nggpano'); ?></label></td>
                        <td>
                            <label><input name="albumShortcodetype" type="radio" value="panoramicalbum" checked="checked" /> <?php _e('Album of Panoramics', 'nggpano');?> [panoramicalbum]</label><br />
                            <label><input name="albumShortcodetype" type="radio" value="panoramicalbumwithmap" /> <?php _e('Album of Panoramics with map','nggpano') ?> [panoramicalbumwithmap]</label><br />
                            <label><input name="albumShortcodetype" type="radio" value="albummap" /> <?php _e('Map with all panoramics of an album','nggpano') ?> [albummap]</label><br />
                        </td>
                    </tr>
                    <!-- Dimensions (Width x Height) -->
                    <tr class="album_shortcode_options album panoramicalbum panoramicalbumwithmap">
                        <td nowrap="nowrap" valign="middle"><?php _e("Width x Height", 'nggpano'); ?></td>
                        <td>
                            <input type="text" size="5" id="albumWidth" name="albumWidth" value="<?php echo $width; ?>" /> x <input type="text" size="5" id="albumHeight" name="albumHeight" value="<?php echo $height; ?>" />
                            <br /><small><?php _e('Empty = 100%','nggpano') ?> - <?php _e('Add % sign to get dimension in percentage','nggpano') ?></small>
                        </td>
                    </tr>
                    <!-- Alignement -->
                    <tr>
                        <td nowrap="nowrap" valign="middle"><?php _e('Alignment','nggpano') ?></td>
                        <td>
                            <label for="album-align-none"><input name="albumAlign" type="radio" id="album-align-none" value="none" <?php checked('none', $align); ?> /><?php _e('None','nggpano'); ?></label>
                            <label for="album-align-left"><input name="albumAlign" type="radio" id="album-align-left" value="left" <?php checked('left', $align); ?> /><?php _e('Left','nggpano'); ?></label>
                            <label for="album-align-center"><input name="albumAlign" type="radio" id="album-align-center" value="center" <?php checked('center', $align); ?> /><?php _e('Center','nggpano'); ?></label>
                            <label for="album-align-right"><input name="albumAlign" type="radio" id="album-align-right" value="right" <?php checked('right', $align); ?> /><?php _e('Right','nggpano'); ?></label>
                        </td>
                    </tr>

                    <!-- Map Dimension -->
                    <tr class="album_shortcode_options panoramicalbumwithmap albummap">
                        <td nowrap="nowrap" valign="middle"><?php _e("Map", 'nggpano'); ?></td>
                        <td>
                            <table>
                                <tr>
                                    <td valign="top" style="text-align: center; width: 50%"><?php _e('Map Width x height (in pixel)','nggpano') ?></td>
                                    <td nowrap="nowrap" valign="top" style="text-align: center; width: 50%"><?php _e('Map Zoom','nggpano') ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; width: 50%">
                                        <input type="text" size="5" maxlength="5" name="albumMapW" id="albumMapW" value="<?php echo $width; ?>" /> x <input type="text" size="5" maxlength="5" name="albumMapH" id="albumMapH" value="<?php echo $height; ?>" />
                                        <br /><small><?php _e('Size of the map','nggpano') ?></small>
                                    </td>
                                    <td style="text-align: center; width: 50%">
                                        <select style="margin: 0pt; padding: 0pt;" id="albumMapZ" name="albumMapZ">
                                            <?php 
                                            for ($i = 1; $i < 19; $i++) {
                                                echo '<option '.(($i == $default_zoom) ? 'selected="selected"' : '') .' value="'.$i.'">'.$i.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Map Type -->
                    <tr class="album_shortcode_options panoramicalbumwithmap albummap">
                        <td nowrap="nowrap" valign="middle"><?php _e('Map Type','nggpano') ?></td>
                        <td>
                            <input type="radio" value="hybrid" <?php checked('hybrid', $default_maptype); ?> id="album-maptype-hybrid" name="albumMaptype"/>
                            <label class="align" for="album-maptype-hybrid"><?php _e('Hybrid','nggpano'); ?></label>
                            <input type="radio" value="roadmap" <?php checked('roadmap', $default_maptype); ?> id="album-maptype-roadmap" name="albumMaptype"/>
                            <label class="align" for="album-maptype-roadmap"><?php _e('Roadmap','nggpano'); ?></label>
                            <input type="radio" value="satellite" <?php checked('satellite', $default_maptype); ?> id="album-maptype-satellite" name="albumMaptype"/>
                            <label class="align" for="album-maptype-satellite"><?php _e('Satellite','nggpano'); ?></label>
                            <input type="radio" value="terrain" <?php checked('terrain', $default_maptype); ?> id="album-maptype-terrain" name="albumMaptype"/>
                            <label class="align" for="album-maptype-terrain"><?php _e('Terrain','nggpano'); ?></label>
                        </td>
                    </tr>
                    <!-- Links to show -->
                    <tr valign="top" class="album_shortcode_options albummap">
                        <td nowrap="nowrap" valign="top"><?php _e('Links to show','nggpano') ?></td>
                        <td>
                            <input type="radio" value="none" <?php checked('none', $default_links); ?> id="album-links-none" name="albumLinks"/>
                            <label class="align" for="album-links-none"><?php _e('None','nggpano'); ?></label>
                            <input type="radio" value="all" <?php checked('all', $default_links); ?> id="album-links-all" name="albumLinks"/>
                            <label class="align" for="album-links-all"><?php _e('All links','nggpano'); ?></label>
                            <input type="radio" value="select" <?php checked('select', $default_links); ?> id="album-links-select" name="albumLinks"/>
                            <label class="align" for="album-links-select"><?php _e('Selected links','nggpano'); ?></label>
                            <br /><small><?php _e('Links to show under the item','nggpano') ?></small>
                            <div id="album-choice_links">
                                <input type="checkbox" value="picture" <?php checked('picture', $default_links); ?> id="album-links-picture" class="albumLinksChoice"/>
                                <label class="align" for="album-links-picture"><?php _e('Picture','nggpano'); ?></label>
                                <input type="checkbox" value="map" <?php checked('map', $default_links); ?> id="album-links-map" class="albumLinksChoice"/>
                                <label class="align" for="album-links-map"><?php _e('Map','nggpano'); ?></label>
                                <input type="checkbox" value="pano" <?php checked('pano', $default_links); ?> id="album-links-pano" class="albumLinksChoice"/>
                                <label class="align" for="album-links-pano"><?php _e('Panoramic','nggpano'); ?></label>
                            </div>
                            
                        </td>
                    </tr>
                    <!-- Main Link -->
                    <tr valign="top" class="album_shortcode_options albummap">
                        <td nowrap="nowrap" valign="middle"><?php _e('Main Link','nggpano') ?></td>
                        <td>
                            <input type="radio" value="none" <?php checked('none', $default_caption); ?> id="album-mainlink-none" name="albumMainlink"/>
                            <label class="align" for="album-mainlink-none"><?php _e('None','nggpano'); ?></label>
                            <input type="radio" value="picture" <?php checked('picture', $default_caption); ?> id="album-mainlink-picture" name="albumMainlink"/>
                            <label class="align" for="album-mainlink-picture"><?php _e('Picture','nggpano'); ?></label>
                            <input type="radio" value="map" <?php checked('map', $default_caption); ?> id="album-mainlink-map" name="albumMainlink"/>
                            <label class="align" for="album-mainlink-map"><?php _e('Map','nggpano'); ?></label>
                            <input type="radio" value="pano" <?php checked('pano', $default_caption); ?> id="album-mainlink-pano" name="albumMainlink"/>
                            <label class="align" for="album-mainlink-pano"><?php _e('Panoramic','nggpano'); ?></label>
                            <br /><small><?php _e('Link when click on the item','nggpano') ?></small>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- album panel -->

	</div>

	<div class="mceActionPanel">
            <div style="float: left">
                <input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'nggpano'); ?>" onclick="tinyMCEPopup.close();" />
            </div>

            <div style="float: right">
                <input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'nggpano'); ?>" onclick="insertNGGPANOLink();return false;" />
            </div>
	</div>
</form>
</body>
</html>