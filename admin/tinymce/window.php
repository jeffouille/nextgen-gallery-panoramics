<?php

if ( !defined('ABSPATH') )
    die('You are not allowed to call this page directly.');
    
global $wpdb, $nggdb;

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
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.core.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.widget.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.position.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/jquery.ui.autocomplete.min.js"></script>
<!--	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui/jquery.ui.core.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui/jquery.ui.widget.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui/jquery.ui.position.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui/jquery.ui.autocomplete.min.js"></script>-->
    <script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/nggpano.autocomplete.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/tinymce/tinymce.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo NGGALLERY_URLPATH ?>admin/css/jquery.ui.css" media="all" />
    <base target="_self" />
</head>
<script type="text/javascript">
jQuery(document).ready(function(){ 
    jQuery("#gallerytag").nggpanoAutocomplete( {
        type: 'gallery',domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>"
    });
    jQuery("#singlepictag").nggpanoAutocomplete( {
        type: 'image', domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>"
    });
    jQuery("#singlepanotag").nggpanoAutocompleteMultiple( {
        type: 'image', domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>", multiple: true
    });
});
</script>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">
    <form name="NGGPano" action="#">
        <div class="tabs">
            <ul>
                <li id="singlepic_tab" class="current"><span><a href="javascript:mcTabs.displayTab('singlepic_tab','singlepic_panel');" onmousedown="return false;"><?php _e('Picture', 'nggpano'); ?></a></span></li>
                <li id="gallery_tab"><span><a href="javascript:mcTabs.displayTab('gallery_tab','gallery_panel');" onmousedown="return false;"><?php echo _n( 'Gallery', 'Galleries', 1, 'nggpano' ) ?></a></span></li>
            </ul>
        </div>
	
	<div class="panel_wrapper">

            <!-- single pic panel -->
            <div id="singlepic_panel" class="panel current">
                <br />
                <table border="0" cellpadding="4" cellspacing="0">
                    <tr>
                        <td nowrap="nowrap"><label for="singlepictag"><?php _e("Picture", 'nggpano'); ?></label></td>
                        <td>
                            <select id="singlepictag" name="singlepictag" style="width: 200px">
                                <option value="0" selected="selected"><?php _e("Select or enter picture", 'nggpano'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap"><label for="singlepanotag"><?php _e("Panoramic", 'nggpano'); ?></label></td>
                        <td>
                            <input id="singlepanotag" name="singlepanotag" />
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap"><?php _e("Width x Height", 'nggpano'); ?></td>
                        <td>
                            <input type="text" size="5" id="imgWidth" name="imgWidth" value="320" /> x <input type="text" size="5" id="imgHeight" name="imgHeight" value="240" />
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" valign="top"><?php _e("Effect", 'nggpano'); ?></td>
                        <td>
                            <label>
                                <select id="imgeffect" name="imgeffect">
                                    <option value="none"><?php _e("No effect", 'nggpano'); ?></option>
                                    <option value="watermark"><?php _e("Watermark", 'nggpano'); ?></option>
                                    <option value="web20"><?php _e("Web 2.0", 'nggpano'); ?></option>
                                </select>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" valign="top"><?php _e("Float", 'nggpano'); ?></td>
                        <td>
                            <label>
                                <select id="imgfloat" name="imgfloat">
                                    <option value=""><?php _e("No float", 'nggpano'); ?></option>
                                    <option value="left"><?php _e("Left", 'nggpano'); ?></option>
                                    <option value="center"><?php _e("Center", 'nggpano'); ?></option>
                                    <option value="right"><?php _e("Right", 'nggpano'); ?></option>
                                </select>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- single pic panel -->
            
            <!-- gallery panel -->
            <div id="gallery_panel" class="panel">
                <br />
                <table border="0" cellpadding="4" cellspacing="0">
                    <tr>
                        <td nowrap="nowrap"><label for="gallerytag"><?php _e("Gallery", 'nggpano'); ?></label></td>
                        <td>
                            <select id="gallerytag" name="gallerytag" style="width: 200px">
                            <option value="0" selected="selected"><?php _e("Select or enter gallery", 'nggpano'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" valign="top"><label for="showtype"><?php _e("Show as", 'nggpano'); ?></label></td>
                        <td>
                            <label><input name="showtype" type="radio" value="nggallery" checked="checked" /> <?php _e('Image list', 'nggpano');?></label><br />
                            <label><input name="showtype" type="radio" value="slideshow"  /> <?php _e('Slideshow', 'nggpano');?></label><br />
                            <label><input name="showtype" type="radio" value="imagebrowser"  /> <?php _e('Imagebrowser', 'nggpano');?></label>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- gallery panel -->

	</div>

	<div class="mceActionPanel">
            <div style="float: left">
                <input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'nggpano'); ?>" onclick="tinyMCEPopup.close();" />
            </div>

            <div style="float: right">
                <input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'nggpano'); ?>" onclick="insertNGGPANOLink();" />
            </div>
	</div>
</form>
</body>
</html>