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
<!--	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/jquery.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.core.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.widget.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.draggable.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.droppable.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.sortable.js"></script>-->

	
        <!--<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.position.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/jquery.ui.autocomplete.min.js"></script>-->

<!--        <script language="javascript" type="text/javascript" src="js/jquery-1.4.2.min.js"></script>-->
        <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/jquery-ui-1.8.custom.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/plugins/localisation/jquery.localisation-min.js"></script>
<!--	<script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/plugins/tmpl/jquery.tmpl.1.1.1.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/plugins/blockUI/jquery.blockUI.js"></script>-->
	<script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/ui.multiselect.js"></script>
        
        

<!--        <script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/nggpano.autocomplete.js"></script>-->
	<script language="javascript" type="text/javascript" src="<?php echo NGGPANOGALLERY_URLPATH ?>admin/tinymce/tinymce.js"></script>
        
        
        
    <link rel="stylesheet" type="text/css" href="<?php echo NGGALLERY_URLPATH ?>admin/css/jquery.ui.css" media="all" />
    <link type="text/css" rel="stylesheet" href="<?php echo NGGPANOGALLERY_URLPATH ?>admin/css/themes/smoothness/jquery-ui-1.7.1.custom.css" />
    <link type="text/css" href="<?php echo NGGPANOGALLERY_URLPATH ?>admin/css/ui.multiselect.css" rel="stylesheet" />
    <base target="_self" />


    </head>
    
<script type="text/javascript">
jQuery(document).ready(function(){ 

    /*
    jQuery("#gallerytag").nggpanoAutocomplete( {
        type: 'gallery',domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>"
    });
    jQuery("#singlepictag").nggpanoAutocomplete( {
        type: 'image', domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>"
    });
    jQuery("#singlepanotag").nggpanoAutocompleteMultiple( {
        type: 'image', domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>", multiple: true
    });
    
    
    */
    //jQuery.localise('ui.multiselect', {/*language: 'en',/* */ path: '<?php echo NGGPANOGALLERY_URLPATH ?>admin/js/locale/'});

//    // local
    jQuery("#countries").multiselect();
    // remote
   /*
   jQuery("#panoramics").multiselect({
            remoteUrl: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http') . "?type=image&format=json&callback=json&method=autocomplete"; ?>"
    });
   */
   
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
                        <td nowrap="nowrap"><label for="countries"><?php _e("Picture", 'nggpano'); ?></label></td>
                        <td>
                            <select id="countries" class="multiselect" multiple="multiple" name="countries[]">
								<option value="AFG">Afghanistan</option>
								<option value="ALB">Albania</option>
								<option value="DZA">Algeria</option>
								
								<option value="AND">Andorra</option>
								<option value="ARG">Argentina</option>
								<option value="ARM">Armenia</option>
								<option value="ABW">Aruba</option>
								<option value="AUS">Australia</option>
								<option value="AUT" selected="selected">Austria</option>

								<option value="AZE">Azerbaijan</option>
								<option value="BGD">Bangladesh</option>
								<option value="BLR">Belarus</option>
								<option value="BEL">Belgium</option>
								<option value="BIH">Bosnia and Herzegovina</option>
								<option value="BRA">Brazil</option>
								<option value="BRN">Brunei</option>
								<option value="BGR">Bulgaria</option>
								<option value="CAN">Canada</option>

								<option value="CHN">China</option>
								<option value="COL">Colombia</option>
								<option value="HRV">Croatia</option>
								<option value="CYP">Cyprus</option>
								<option value="CZE">Czech Republic</option>
								<option value="DNK">Denmark</option>
								<option value="EGY">Egypt</option>
								<option value="EST">Estonia</option>
								<option value="FIN">Finland</option>

								<option value="FRA">France</option>
								<option value="GEO">Georgia</option>
								<option value="DEU" selected="selected">Germany</option>
								<option value="GRC">Greece</option>
								<option value="HKG">Hong Kong</option>
								<option value="HUN">Hungary</option>
								<option value="ISL">Iceland</option>
								<option value="IND">India</option>
								<option value="IDN">Indonesia</option>

								<option value="IRN">Iran</option>
								<option value="IRL">Ireland</option>
								<option value="ISR">Israel</option>
								<option value="ITA">Italy</option>
								<option value="JPN">Japan</option>
								<option value="JOR">Jordan</option>
								<option value="KAZ">Kazakhstan</option>
								<option value="KWT">Kuwait</option>
								<option value="KGZ">Kyrgyzstan</option>

								<option value="LVA">Latvia</option>
								<option value="LBN">Lebanon</option>
								<option value="LIE">Liechtenstein</option>
								<option value="LTU">Lithuania</option>
								<option value="LUX">Luxembourg</option>
								<option value="MAC">Macau</option>
								<option value="MKD">Macedonia</option>
								<option value="MYS">Malaysia</option>
								<option value="MLT">Malta</option>

								<option value="MEX">Mexico</option>
								<option value="MDA">Moldova</option>
								<option value="MNG">Mongolia</option>
								<option value="NLD" selected="selected">Netherlands</option>
								<option value="NZL">New Zealand</option>
								<option value="NGA">Nigeria</option>
								<option value="NOR">Norway</option>
								<option value="PER">Peru</option>
								<option value="PHL">Philippines</option>

								<option value="POL">Poland</option>
								<option value="PRT">Portugal</option>
								<option value="QAT">Qatar</option>
								<option value="ROU">Romania</option>
								<option value="RUS">Russia</option>
								<option value="SMR">San Marino</option>
								<option value="SAU">Saudi Arabia</option>
								<option value="CSG">Serbia and Montenegro</option>
								<option value="SGP">Singapore</option>

								<option value="SVK">Slovakia</option>
								<option value="SVN">Slovenia</option>
								<option value="ZAF">South Africa</option>
								<option value="KOR">South Korea</option>
								<option value="ESP">Spain</option>
								<option value="LKA">Sri Lanka</option>
								<option value="SWE">Sweden</option>
								<option value="CHE">Switzerland</option>
								<option value="SYR">Syria</option>

								<option value="TWN">Taiwan</option>
								<option value="TJK">Tajikistan</option>
								<option value="THA">Thailand</option>
								<option value="TUR">Turkey</option>
								<option value="TKM">Turkmenistan</option>
								<option value="UKR">Ukraine</option>
								<option value="ARE">United Arab Emirates</option>
								<option value="GBR">United Kingdom</option>
								<option value="USA" selected="selected">United States</option>

								<option value="UZB">Uzbekistan</option>
								<option value="VAT">Vatican City</option>
								<option value="VNM">Vietnam</option>
							</select>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap"><label for="panoramics"><?php _e("Panoramic", 'nggpano'); ?></label></td>
                        <td>
                            <select id="panoramics" class="multiselect" multiple="multiple" name="panoramics[]">
                                
                            </select>

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