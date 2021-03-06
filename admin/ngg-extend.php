<?php

// stop direct call
if(preg_match("#".basename(__FILE__)."#", $_SERVER["PHP_SELF"])) {die("You are not allowed to call this page directly.");}

//include_once ( dirname (__FILE__) . '/ajax-actions.php' );	// admin functions
include_once ( dirname (__FILE__) . '/nggpanoAdmin.class.php' ); // admin function
include_once ( dirname (__FILE__) . '/../lib/nggpanoPano.class.php' ); //nggpanoPano Class

/*
This was pretty much my first from-scratch plugin. The code structure is a little all-over-the-place as a result of adding features without while maintaining legacy features
But it works, so I'm not going to do a whole-scale rewrite when I have better things to do. Like drink beer. Mmmm, delicious beer...

Why are you even reading this? Maybe you should consider helping me stock that fridge? http://shauno.co.za/donate/
*/

//{
/*
 * ADMIN GALLERY SETTINGS
 */
        add_action('ngg_manage_gallery_settings', 'nggpano_manage_gallery_settings', 100);	
        
        function nggpano_manage_gallery_settings($act_gid) {
                global $wpdb, $nggpano;

                $default_templatefile = $nggpano->options['defaultSkinFile'];
                $act_gallery_settings = nggpano_getGalleryOptions($act_gid);
                $act_templatefile = isset($act_gallery_settings->skin) ? $act_gallery_settings->skin : '';
                $act_gallery_region = isset($act_gallery_settings->gps_region) ? unserialize($act_gallery_settings->gps_region) : '';
                $act_exclude = isset($act_gallery_settings->exclude) ? $act_gallery_settings->exclude : '';
                $act_gallery_exclude   = ( $act_gallery_settings->exclude ) ? 'checked="checked"' : '';
                $act_order = isset($act_gallery_settings->order) ? $act_gallery_settings->order : '';
                
                ?>
                <tr>
                    <td colspan="4" align="left">
                        <strong><?php _e('Gallery Panoramic Options', 'nggpano'); ?></strong>
                    <hr />
                    <input type="hidden" name="nggpano_gallery[ngg_gallery_id]" value="<?php echo $act_gid ?>" />
                    <!-- TODO -->
<!--                    <input type="hidden" name="nggpano_gallery[gps_region]" value="" />-->
                    </td>
                </tr>
                <tr>
                    <th align="left">
                        <?php _e('Order','nggpano');?> : 
                    </th>
                    <th align="left">
                    <input type="text" name="nggpano_gallery[order]" id="nggpano_gallery[order]" value="<?php echo $act_order ?>" />
                    </th>
                    <th align="left">
                        <?php _e('Exclude','nggpano');?> : 
                    </th>
                    <th align="left">
                    <input name="nggpano_gallery[exclude]" id="nggpano_gallery[exclude]" type="checkbox" value="1" <?php echo $act_gallery_exclude ?> />
                    
                    </th>
                </tr>
                <tr valign="top">
                    <th align="right">
                        <?php _e('Skin file','nggpano');?> : 
                    </th>
                    <th align="left">
                        <select name="nggpano_gallery[skin_file]" id="nggpano_gallery[skin_file]" style="margin: 0pt; padding: 0pt;">
                        <?php               
                        $templatelist = nggpano_get_viewerskinfiles();
                        foreach ($templatelist as $key =>$a_templatefile) {
                                $template_name = $a_templatefile['Name'];
                                if ($key == $act_templatefile) {
                                        $file_show = $key;
                                        $selected = " selected='selected'";
                                        $act_template_description = $a_templatefile['Description'];
                                        $act_template_author = $a_templatefile['Author'];
                                        $act_template_version = $a_templatefile['Version'];
                                
                                } else {
                                    $selected = '';
                                }
                                $template_name = esc_attr($template_name);
                                echo "\n\t<option value=\"$key\" $selected>$template_name</option>";
                        }
                        ?>

                        </select>
                    </th>
                    <th align="right">
                        <?php _e('Map Region','nggpano');?> : 
                    </th>
                    <th align="left">
                        <div id="map_gps_region" style="float: left; width: 300px; height: 200px;"></div>
                        <span class="pickgpsregion">
                           <a class="nggpano-dialog" href="<?php echo NGGPANOGALLERY_URLPATH  ?>admin/pick-gps-region.php?gid=<?php echo $act_gid ?>&h=680" title="<?php _e('Pick GPS Region on map','nggpano') ?>"><?php _e('Pick GPS Region on map','nggpano') ?></a>
                        </span>
<!--                        <input class="button-secondary nggpano-dialog" type="submit" name="pickgpsregion" value="<?php _e('Edit', 'nggpano'); ?>" onclick="    return false;" />-->
                    </th>
                </tr>
                <hr/>
                <tr>
                    <th align="left">
                        <?php _e('Bulk actions','nggpano');?> : 
                    </th>
                    <th colspan="3" align="left">
                        <div class="tablenav top ngg-tablenav">
                            <div class="alignleft actions">
                                <select id="nggpano_bulkaction" name="nggpano_bulkaction">
                                    <option value="no_action" ><?php _e("--Select a Bulk actions--",'nggpano'); ?></option>
                                    <option value="extract_gps" ><?php _e("Extract GPS Data",'nggpano'); ?></option>
                                    <option value="extract_xml_from_file" ><?php _e("Extract Pano Data",'nggpano'); ?></option>
                                </select>
                                <input type="hidden" id="nggpano_imagelist" name="nggpano_imagelist" value="" />
                        	<input type="hidden" id="nggpanobulk_bulkaction" name="TB_bulkaction" value="" />
                                <input class="button-secondary" type="submit" name="updatepictures" value="<?php _e('Apply', 'nggallery'); ?>" onclick="if ( !checkPanoSelected() ) return false;" />

                            </div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th colspan="4" align="left">
                    <hr />
                    </th>
                </tr>

                <script type="text/javascript"> 
//<![CDATA[

                // this function check for a the number of selected images, sumbmit false when no one selected
                function checkPanoSelected() {

                        var numchecked = getNumChecked(document.getElementById('updategallery'));

                    if (typeof document.activeElement == "undefined" && document.addEventListener) {
                        document.addEventListener("focus", function (e) {
                                document.activeElement = e.target;
                        }, true);
                    }

                    if ( document.activeElement.name == 'post_paged' )
                        return true;

                        if(numchecked < 1) { 
                                alert('<?php echo esc_js(__('No images selected', 'nggallery')); ?>');
                                return false; 
                        } 

                        actionId = jQuery('#nggpano_bulkaction').val();



                        switch (actionId) {
                                case "extract_gps":
                                    var selectitems = nggpano_get_picture_id_selected();
                                    jQuery("#nggpano_imagelist").val(selectitems);
                                        //nggpano_showDialog('gps_dialog', '<?php echo esc_js(__('Extract GPS Data...','nggpano')); ?>');
                                        //return false;
                                        break;
                                case "extract_xml_from_file":
                                    var selectitems = nggpano_get_picture_id_selected();
                                    jQuery("#nggpano_imagelist").val(selectitems);
                                        break;
                                case "no_action":
                                        return false;
                                        break;	
                        }

                        return confirm('<?php echo sprintf(esc_js(__("You are about to start the bulk edit for %s images \n \n 'Cancel' to stop, 'OK' to proceed.",'nggallery')), "' + numchecked + '") ; ?>');
                }


                function nggpano_get_picture_id_selected() {
                        var form = document.getElementById('updategallery');
                        var elementlist = "";
                        for (i = 0, n = form.elements.length; i < n; i++) {
                                if(form.elements[i].type == "checkbox") {
                                        if(form.elements[i].name == "doaction[]")
                                                if(form.elements[i].checked == true)
                                                        if (elementlist == "")
                                                                elementlist = form.elements[i].value
                                                        else
                                                                elementlist += "," + form.elements[i].value ;
                                }
                        }
                        console.log(elementlist);
                        return elementlist;
                }

jQuery(document).ready(function(){
    //initalize map
    jQuery('#map_gps_region').gmap3(
    {
        action:'init',
        options:{
            center:['46.578498','2.457275'],
            zoom: 4,
            mapTypeId: google.maps.MapTypeId.TERRAIN
        },
        events:{
            idle: function(map){
                //console.log(map.getCenter());
            
            }
        }
    }
    <?php if ($act_gallery_region <> "" && (isset($act_gallery_region['sw']['lat']) && $act_gallery_region['sw']['lat'] <> '')) : ?>,
    {
        action: 'addRectangle',
        rectangle:{
            options:{
                bounds: new google.maps.LatLngBounds(
                    new google.maps.LatLng(<?php echo $act_gallery_region['sw']['lat'] ?>, <?php echo $act_gallery_region['sw']['lng'] ?>),
                    new google.maps.LatLng(<?php echo $act_gallery_region['ne']['lat'] ?>, <?php echo $act_gallery_region['ne']['lng'] ?>)          
                ),
                fillColor : "#008BB2",
                strokeColor : "#005BB7",
                clickable:true,
                editable:false
            }
        },
        map:{
            center: true//,
            //zoom:12
        }
    }
    <?php endif; ?>

    );
    <?php if ($act_gallery_region <> "" && (isset($act_gallery_region['sw']['lat']) && $act_gallery_region['sw']['lat'] <> '')) : ?>
    var map = jQuery("#map_gps_region").gmap3('get');
    var bounds= new google.maps.LatLngBounds(
                    new google.maps.LatLng(<?php echo $act_gallery_region['sw']['lat'] ?>, <?php echo $act_gallery_region['sw']['lng'] ?>),
                    new google.maps.LatLng(<?php echo $act_gallery_region['ne']['lat'] ?>, <?php echo $act_gallery_region['ne']['lng'] ?>)          
                );
                map.setCenter(bounds.getCenter());
                map.fitBounds(bounds);
                //map.panToBounds(bounds);
    <?php endif; ?>
});
//]]> 
                </script>
                <?php
        }

/*
 * ADMIN NEW GALLERY
 */
        
        //adding new gallery to NGG
        add_action("ngg_add_new_gallery_form", "nggpano_add_new_gallery_form"); //new in ngg 1.4.0a
        add_action("ngg_created_new_gallery", "nggpano_created_new_gallery"); //new in ngg 1.4.0a
        
        function nggpano_add_new_gallery_form() {
            global $wpdb, $nggpano;
            $default_templatefile = $nggpano->options['defaultSkinFile'];
            
            ?>
                <tr valign="top">
                    <th scope="row">
                        <?php _e('Panoramic skin file','nggpano');?>
                    </th>
                    <td>
                        <select name="conf[skin_file]" id="conf[skin_file]" style="margin: 0pt; padding: 0pt;">
                        <?php               
                        $templatelist = nggpano_get_viewerskinfiles();
                        foreach ($templatelist as $key =>$a_templatefile) {
                                $template_name = $a_templatefile['Name'];
                                if ($key == $act_templatefile) {
                                        $file_show = $key;
                                        $selected = " selected='selected'";
                                        $act_template_description = $a_templatefile['Description'];
                                        $act_template_author = $a_templatefile['Author'];
                                        $act_template_version = $a_templatefile['Version'];
                                
                                } else {
                                    $selected = '';
                                }
                                $template_name = esc_attr($template_name);
                                echo "\n\t<option value=\"$key\" $selected>$template_name</option>";
                        }
                        ?>

                        </select>
                    </td>
                </tr>
                <?php
        }

        function nggpano_created_new_gallery($gid) {
                global $wpdb;
                $skinfile = $_POST["conf"]["skin_file"] == '' ? 'null' : $_POST["conf"]["skin_file"];

                $qry = "INSERT INTO ".$wpdb->prefix."nggpano_gallery (`id`, `skin`, `gid` , `gps_region`) VALUES(null, '".$wpdb->escape($skinfile)."', '".$wpdb->escape($gid)."', null)";
                $wpdb->query($qry);
                
        }
        
        
/*
 * IMAGE PANORAMIC FIELD
 */
        //add_action("ngg_manage_image_custom_column", "nggpano_manage_image_gps_column", 10 ,2);
        //add_filter("ngg_manage_images_columns", "nggpano_manage_images_columns");
        
//        //add the col to array of cols
//        function nggpano_manage_images_columns($gallery_columns) {
//                global $wpdb;
//                $fields = nggpano_get_field_list(nggpano_IMAGES, $_GET["gid"]);
//
//                foreach ((array)$fields as $key=>$val) {
//                        $gallery_columns[htmlspecialchars($val->field_name)] = htmlspecialchars($val->field_name);
//                }
//
//                return $gallery_columns;
//        }
//
//        //the field for managing the images in a gallery
//        function nggpano_manage_image_gps_column($gallery_column_key, $pid) {
//                global $wpdb, $ngg_edit_gallery;
//                
//                //Get GPS values for the current image
//                $image_values = nggpano_getImagePanoramicOptions($pid);
//                $lat = $image_values->gps_lat;
//                $lng = $image_values->gps_lng;
//                
//                //htmlspecialchars_decode() as the $gallery_column_key was htmlspecialchars() when adding the custom fields
//                //$custCol = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."nggpano_fields WHERE field_name = '".$wpdb->escape(htmlspecialchars_decode($gallery_column_key))."'");
//                //$value = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."nggpano_field_values WHERE fid = '".$custCol->id."' AND pid = '$pid'");

//
//        } 

        
add_action("ngg_manage_images_columns", "nggpano_add_image_pano_columns");
//}
/**
 * Add a custom field to the images field list.  This give us a place to add the voting options for each image with nggv_add_image_vote_options_field()
 * Also enqueues a script that will add the gallery voting options with js (sneaky, but has to be done)
 * @param array $gallery_columns The array of current fields
 * @author Shaun <shaun@worldwidecreative.co.za>
 * @return array $gallery_columns with an added field
 */
function nggpano_add_image_pano_columns($gallery_columns) {

        $gallery_columns["nggpano_gps_fields"] = __('GPS','nggpano');
        $gallery_columns["nggpano_krpano_fields"] = __('Panoramic','nggpano');
        return $gallery_columns;
} 


add_action("ngg_manage_image_custom_column", "nggpano_add_image_pano_fields", 10 ,2);
//}
/**
 * Add gps and panoramic option on each image
 * @param string $gallery_column_key The key value of the 'custom' fields added by nggpano_add_image_pano_columns()
 * @param string $pid image id
 * @return void
 */
function nggpano_add_image_pano_fields($gallery_column_key, $pid) {
    switch ($gallery_column_key) {
        case "nggpano_gps_fields":
            nggpanoAdmin::gps_image_form($pid);
            break;
        case "nggpano_krpano_fields":
            nggpanoAdmin::krpano_image_form($pid);
            break;
    }
}


//ngg_manage_gallery_columns

add_action("ngg_manage_gallery_columns", "nggpano_add_gallery_pano_columns");
//}
/**
 * Add a custom field to the galleries field list.  
 * @param array $gallery_columns The array of current fields
 * @author Shaun <shaun@worldwidecreative.co.za>
 * @return array $gallery_columns with an added field
 */
function nggpano_add_gallery_pano_columns($gallery_columns) {

        $gallery_columns["nggpano_gallery_order"] = __('Order','nggpano');
        $gallery_columns["nggpano_gallery_exclude"] = __('Excl.', 'nggpano');
        return $gallery_columns;
} 

//ngg_manage_gallery_custom_column
add_action("ngg_manage_gallery_custom_column", "nggpano_add_gallery_pano_fields", 10 ,2);
//}
/**
 * Add gps and panoramic option on each image
 * @param string $gallery_column_key The key value of the 'custom' fields added by nggpano_add_image_pano_columns()
 * @param string $pid image id
 * @return void
 */
function nggpano_add_gallery_pano_fields($gallery_column_key, $gid) {
    switch ($gallery_column_key) {
        case "nggpano_gallery_order":
            nggpanoAdmin::gallery_order_form($gid);
            break;
        case "nggpano_gallery_exclude":
            nggpanoAdmin::gallery_exclude_form($gid);
            break;
    }
}

/*
 * SAVE GALLERY AND PICTURES
 */

        add_action("ngg_update_gallery", "nggpano_update_gallery", 10, 2);
        //save gallery and pictures data (checks if it needs to insert or update)
        function nggpano_update_gallery($gid=null, $post) {
                global $wpdb;
                
                //save gallery
                if (isset($post["nggpano_gallery"])) {
                    if(is_array($post["nggpano_gallery"])) {
                            $galleryId = $wpdb->escape($post["nggpano_gallery"]["ngg_gallery_id"]);
                            unset($post["nggpano_gallery"]["ngg_gallery_id"]);


                            $skinfile   = $wpdb->escape($post["nggpano_gallery"]["skin_file"]);
                            $order   = $wpdb->escape($post["nggpano_gallery"]["order"]);
                            //$exclude = $wpdb->escape($post["nggpano_gallery"]["exclude"]);
                            $exclude = isset ( $post["nggpano_gallery"]["exclude"] ) ? $post["nggpano_gallery"]["exclude"] : '0';
                            //nggpano_gallery[exclude]
                            //echo $skinfile . '-' . $exclude . '<br/>';
                            //echo "UPDATE ".$wpdb->prefix."nggpano_gallery SET `skin` = '".$skinfile."', `order` = '".$order."', `exclude` = '".$exclude."'  WHERE gid = '".$wpdb->escape($galleryId)."'";
                            //$gps_region = $wpdb->escape($post["nggpano_gallery"]["gps_region"]);

                            if(nggpano_getGalleryOptions($galleryId)) {
                                    $wpdb->query("UPDATE ".$wpdb->prefix."nggpano_gallery SET `skin` = '".$skinfile."', `order` = '".$order."', `exclude` = '".$exclude."'  WHERE gid = '".$wpdb->escape($galleryId)."'");
                            }else{
                                    $wpdb->query("INSERT INTO ".$wpdb->prefix."nggpano_gallery (id, `gid`, `skin`, `order`, `exclude`) VALUES (null, '".$wpdb->escape($galleryId)."', '".$skinfile."','".$order."','".$exclude."')");
                            }
                    }
                }
		
                //save pictures
                if (isset($post["nggpano_picture"])) {
                    if (is_array($post["nggpano_picture"])) {
                        foreach ((array)$post["nggpano_picture"] as $pid=>$val) {
                                $lat = (strlen($val["lat"]) == 0) ? 'NULL' : $val["lat"];
                                $lng = (strlen($val["lng"]) == 0) ? 'NULL' : $val["lng"];
                                $alt = (strlen($val["alt"]) == 0) ? 'NULL' : round($val["alt"],0);
                                $postid = (strlen($val["postid"]) == 0) ? '0' : $val["postid"];
                                //nggpano_picture['.$pid.'][postid]"
                               // $pano_directory = $wpdb->escape($val["pano_directory"]);
                               // $xml_configuration = $wpdb->escape($val["xml_configuration"]);
                               // $is_partial = $wpdb->escape($val["is_partial"]) ? "1" : "0";


    //                            if(nggpano_getImagePanoramicOptions($pid)) {
    //                                    $wpdb->query("UPDATE ".$wpdb->prefix."nggpano_panoramic SET gps_lat = ".$lat.", gps_lng = ".$lng.", gps_alt = ".$alt.", pano_directory = '".$pano_directory."', xml_configuration = '".$xml_configuration."', is_partial = '".$is_partial."' WHERE pid = '".$wpdb->escape($pid)."'");
    //                            }else{
    //                                    $wpdb->query("INSERT INTO ".$wpdb->prefix."nggpano_panoramic (id, pid, gid, gps_lat, gps_lng, gps_alt, pano_directory, xml_configuration, is_partial) VALUES (null, '".$wpdb->escape($pid)."', '".$wpdb->escape($gid)."', ".$lat.", ".$lng.", ".$alt.", '".$pano_directory."', '".$xml_configuration."', '".$is_partial."')");
    //                            }

                                if(nggpano_getImagePanoramicOptions($pid)) {
                                        $wpdb->query("UPDATE ".$wpdb->prefix."nggpano_panoramic SET gps_lat = ".$lat.", gps_lng = ".$lng.", gps_alt = ".$alt.", post_id = ".$postid." WHERE pid = '".$wpdb->escape($pid)."'");
                                }else{
                                        $wpdb->query("INSERT INTO ".$wpdb->prefix."nggpano_panoramic (id, pid, gid, gps_lat, gps_lng, gps_alt, post_id) VALUES (null, '".$wpdb->escape($pid)."', '".$wpdb->escape($gid)."', ".$lat.", ".$lng.", ".$alt.", ".$postid.")");
                                }
                        }
                    }
                }
                
                
                if (isset ($post['nggpano_bulkaction']) && isset ($post['nggpano_imagelist']))  {
			switch ($post['nggpano_bulkaction']) {
                            case "extract_gps":
                                    $pic_ids  = explode(',', $post['nggpano_imagelist']);
                                    foreach ($pic_ids as $pic_id) {
                                        nggpanoAdmin::extract_gps($pic_id);
                                    }
                                break;
                            case "extract_xml_from_file":
                                    $pic_ids  = explode(',', $post['nggpano_imagelist']);
                                    //$pic_ids  = explode(',', '304,305,306,307,308,309,310,311,312,313,314,315,316,317,318,319,320,321,323,322,324,325,326,327,328,329,330,331,332,333,334,335,336,337,338,339,340,341,342,343,344,345,346,347,348,349,350,351,352,353,354,355,356,357,358,359,360,361,362,363,364,365,366,367,368,369,370,371,372,373,374,375,376,377,378,379,380,381,382,383,384,385,386,387,388,389,390,391,392,393,394,395,396,397,398,399,400,401,402,403,404,405,406,407,408,409,410,411,412,413,414,415,416,417,418,419,420,421,422,423,424,425,426,427,428,429,430,431,432,433,434,435,436,437,438,439,440,441,442,443,444,445,446,447,448,449,450,451,452,453,454,455,456,476,477,478,479,480,481,482,483,484,485,457,458,459,460,461,462,463,464,465,466,467,468,469,470,471,472,473,474,475,503,504,505,506,507,508,509,510,511,512,513,514,486,487,488,489,490,491,492,493,494,495,496,497,498,499,500,501,502,515,516,517,518,519,520,521,522,523,524,525,526,527,528,529,530');
                                    //$pic_ids = explode(',','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173,174,175,176,177,178,179,180,181,182,183,184,185,186,187,188,189,190,191,192,193,194,195,196,197,198,199,200,201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,224,225,226,227,228,229,230,231,232,233,234,235,236,237,238,239,240,241,242,243,244,245,246,247,248,249,250,251,252,253,254,255,256,257,258,259,260,261,262,263,264,265,266,267,268,269,270,271,272,273,274,275,276,277,278,279,280,281,282,283,284,285,286,287,288,289,290,291,292,293,294,295,296,297,298,299,300,301,302,303,304,476,305,306,307,308,309,310,311,312,313,314,315,316,317,318,319,320,321,322,323,324,325,326,327,328,329,330,331,332,333,334,335,336,337,338,339,340,341,342,343,344,345,346,347,348,349,350,351,352,353,354,355,356,357,358,359,360,361,362,363,364,365,366,367,368,369,370,371,372,373,374,375,376,377,378,379,380,381,382,383,384,385,386,387,388,389,390,391,392,393,394,395,396,397,398,399,400,401,402,403,404,405,406,407,408,409,410,411,412,413,414,415,416,417,418,419,420,421,422,423,424,425,426,427,428,429,430,431,432,433,434,435,436,437,438,439,440,441,442,443,444,445,446,447,448,449,450,451,452,453,454,455,456,457,458,459,460,461,462,463,464,465,466,467,468,469,470,471,472,473,474,475,477,478,479,480,481,482,483,484,485,486,487,488,489,490,491,492,493,494,495,496,497,498,499,500,501,502,503,504,505,506,507,508,509,510,511,512,513,514,515,516,517,518,519,520,521,522,523,524,525,526,527,528,529,530');
                                    foreach ($pic_ids as $pic_id) {
                                        nggpanoAdmin::extract_xml_from_file($pic_id);
                                    }
                                break;
                        }
		}
                
                
       /*         
                if ( is_array($post["nggpano_picture"]) ) {
                        foreach ($post["nggpano_picture"] as $pid=>$fields) {
                            $image_values = nggpano_getImagePanoramicOptions($pid);
                            //if($image_values->gps_lat <> $fields["lat"])
                            $lat = ($fields["lat"] == "") ? "" : $fields["lat"];//->gps_lat;
                            $lng = ($fields["lng"] == "") ? "" : $fields["lng"];//->gps_lng;

                            if($image_values) {
                                $wpdb->query("UPDATE ".$wpdb->prefix."nggpano_panoramic SET gps_lat = '".$wpdb->escape($lat)."', gps_lng = '".$wpdb->escape($lng)."' WHERE pid = '".$wpdb->escape($pid)."'");
                            }else{
                                $wpdb->query("INSERT INTO ".$wpdb->prefix."nggpano_panoramic (id, pid, gps_lat, gps_lng) VALUES (null, '".$wpdb->escape($pid)."', '".$wpdb->escape($lat)."', '".$wpdb->escape($lng)."')");
                            }
                        }
                }
              */  
        }


/*
 * GALLERY DELETED
 */
        
add_action("ngg_delete_gallery", "nggpano_delete_gallery");
/**
 * Delete all fields in database database and panorama files when gallery is deleted from nextgen
 * @param integer $gid Gallery id
 * @return void
 */
function nggpano_delete_gallery($gid) {
    global $nggpano, $wpdb, $ngg;
    
    if($gid) {
        //remove all panos tiles files
        $panolist = $wpdb->get_col("SELECT pano_directory FROM ".$wpdb->prefix."nggpano_panoramic WHERE gid = '$gid' AND pano_directory <>''");
        //var_dump($panolist);
        if ($ngg->options['deleteImg']) {
            $gallery_path = '';
            $panosfolder = $nggpano->options['panoFolder'];
                if (is_array($panolist)) {
                    foreach ($panolist as $directory) {
                        nggpano_unlinkRecursive(WINABSPATH . $directory, true);
                        if($gallery_path == '') {
                            $gallery_path = substr($directory, 0,strpos($directory, $panosfolder));//strstr($directory, $panosfolder, true);
                        }
                    }
                }
                // delete folder
                //var_dump(WINABSPATH . $gallery_path);
                if(isset($gallery_path) && $gallery_path <> '') {
                    if(is_dir(WINABSPATH . $gallery_path)) {
                        @rmdir( WINABSPATH . $gallery_path . $panosfolder );
                        @rmdir( WINABSPATH . $gallery_path );
                        
                        //var_dump(WINABSPATH . $gallery_path);
                        //var_dump(WINABSPATH . $gallery_path . $panosfolder );
                    }
                }
        }

        //delete record in database
        $wpdb->query("DELETE FROM ".$wpdb->prefix."nggpano_panoramic WHERE gid = '".$wpdb->escape($gid)."'");
        $wpdb->query("DELETE FROM ".$wpdb->prefix."nggpano_gallery WHERE gid = '".$wpdb->escape($gid)."'");        
    }

}
        
        
/*
 * NEW IMAGE ADDED
 */

add_action("ngg_added_new_image", "nggpano_add_new_image");
/**
 * Add the image gps and panoramics field for a new image (pulled from the defaaults
 * @param array $image the new image details
 * @author Shaun <shaun@worldwidecreative.co.za>
 * @return void
 */
function nggpano_add_new_image($image) {

        if($image['id']) {
            $pid = $image['id'];
                $post = array();
//                nggpano_picture"] as $pid=>$val) {
//                            $lat = (strlen($val["lat"]) == 0) ? 'NULL' : $val["lat"];
//                            $lng = (strlen($val["lng"]) == 0) ? 'NULL' : $val["lng"];
//                            $pano_directory = $wpdb->escape($val["pano_directory"]);
//                            $skinfile = $wpdb->escape($val["skin_file"]);
//                            $xml_configuration = $wpdb->escape($val["skin_file"]);
//                            $is_partial = $wpdb->escape($val["is_partial"]) ? "1" : "0";

                //Get gps info from exif
                $gps_array = nggpano_get_exif_gps($pid, true);
                
                $post['nggpano_picture'] = array();
                $post['nggpano_picture'][$image['id']] = array();
                $post['nggpano_picture'][$image['id']]['lat'] = $gps_array['latitude'];
                $post['nggpano_picture'][$image['id']]['lng'] = $gps_array['longitude'];
                $post['nggpano_picture'][$image['id']]['alt'] = round($gps_array['altitude'],0);
                
                nggpano_update_gallery($image['galleryID'], $post);
        }
}

/*
 * IMAGE DELETED
 */

add_action("ngg_delete_picture", "nggpano_delete_image");
/**
 * Delete image from database and panorama files when image is deleted from nextgen
 * @param integer $pid Image id
 * @return void
 */
function nggpano_delete_image($pid) {
    global $ngg, $wpdb;
    if($pid) {
        //retrieve gallery id from nggpano_panoramic table before the record is deleted (image already delete by ngg gallery ! )
        $pano_infos = nggpano_getImagePanoramicOptions($pid);
        $gid = $pano_infos->gid;
         //Delete pano tiles files
        $pano = new nggpanoPano($pid, $gid);
        $pano->delete();
        
        //delete record in database
        $wpdb->query("DELETE FROM ".$wpdb->prefix."nggpano_panoramic WHERE pid = '".$wpdb->escape($pid)."'");
        
    }


}


/*
 * POST DELETED
 */
add_action('delete_post', 'nggpano_delete_post', 10);

function nggpano_delete_post($pid) {
  global $wpdb;
  //if ($wpdb->get_var($wpdb->prepare('SELECT post_id FROM '.$wpdb->prefix.'nggpano_panoramic WHERE post_id = %d', $pid))) {
    return $wpdb->query($wpdb->prepare('UPDATE '.$wpdb->prefix.'nggpano_panoramic SET post_id = 0 WHERE post_id = %d', $pid));
  //}
  //return true;
}

/*
 * FRONTEND STUFF
 */
add_filter("ngg_image_object", "nggpano_image_obj", 10, 2); // new in ngg 1.2.1   

//new filter in ngg 1.2.1 allows us to add to the list of images (later to be passed to the templates), while its being created!  Thanks Alex
function nggpano_image_obj($pictureObj, $pid) {
        global $nggpano_panoramics_values;

        nggpano_hold_field_values($pid);

        @$pictureObj->nggpano_fields = array();
        foreach ($nggpano_panoramics_values[$pid] as $key=>$val) {
                @$pictureObj->nggpano_fields[$key] = $val->field_value;
        }

        return $pictureObj;
}

function nggpano_hold_field_values($pid) {
        global $wpdb, $nggpano_panoramics_values;

        //only run the select once (store results in mem for access later if func called again with same pid)
        if(!$nggpano_panoramics_values[$pid]) {
                $value = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggpano_panoramic WHERE pid = '".$wpdb->escape($pid)."'");
                $nggpano_panoramics_values[$pid] = array();
                foreach ((array)$value as $key=>$val) {
                        $nggpano_panoramics_values[$pid][$val->field_name] = $val;
                }
        }
}


//returns a specific fields value for a specific image
function nggpano_get_field($pid, $fname) {
        global $nggpano_panoramics_values;
        nggpano_hold_field_values($pid);

        return $nggpano_panoramics_values[$pid][$fname]->field_value;
}


function nggpano_get_gallery_field($gid, $fname) {
        global $nggpano_galleries_values;

        nggpano_hold_gallery_field_values($gid);

        return $nggpano_galleries_values[$gid][$fname];
}

function nggpano_hold_gallery_field_values($gid) {
        global $wpdb, $nggpano_galleries_values;

        if(!$nggpano_galleries_values[$gid]) {
                $value = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggpano_gallery WHERE gid = '".$wpdb->escape($gid)."'");

                foreach ((array)$value as $key=>$val) {
                        $nggpano_galleries_values[$gid][$val->field_name] = $val->field_value;
                }
        }
}


/*
 * OLD STUFF
 */


		
		//api that saves new custom fields
		function nggpano_save_field($config) {
			global $wpdb;
			
			if($wpdb->escape($config["field_name"]) || $wpdb->escape($config["id"])) {
				if($wpdb->escape($config["id"]) && $wpdb->escape($config["drop_options"])) {
					$qry = "UPDATE ".$wpdb->prefix."nggpano_fields SET drop_options = '".$wpdb->escape($config["drop_options"])."' WHERE id = '".$wpdb->escape($config["id"])."'";
					if($wpdb->query($qry) !== false) {
						return true;
					}else{
						return "ERROR: Failed to save field";
					}
				}else if($wpdb->escape($config["id"]) && $wpdb->escape($config["field_name"])) {
					$qry = "UPDATE ".$wpdb->prefix."nggpano_fields SET field_name = '".$wpdb->escape($config["field_name"])."' WHERE id = '".$wpdb->escape($config["id"])."'";
					if($wpdb->query($qry) !== false) {
						return true;
					}else{
						return "ERROR: Failed to save field name";
					}
				}else if($wpdb->escape($config["id"]) && $wpdb->escape($config["linkedit"])) {
					$links = nggpano_get_linked_galleries($config["id"]); //get current links
					
					//loop current links
					foreach ((array)$links as $key=>$val) {
						//delete if not in post list (the link, and any field value)
						if(!$config["galleries"][$val->gid]) {
							$field = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."nggpano_fields WHERE id = ".$val->nggpano_field_id);
							if($field->ngg_type == nggpano_GALLERY) {
								$wpdb->query("DELETE FROM ".$wpdb->prefix."nggpano_field_values WHERE fid = ".$val->nggpano_field_id." AND pid = ".$val->gid); //remove data from that field in that gallery
							}else if($field->ngg_type == nggpano_IMAGES) {
								$list = array();
								if($fields = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ngg_pictures WHERE galleryid = ".$val->gid)) {
									foreach ($fields as $pic) {
										$list[] = $pic->pid;
									}
									if($list) { //meh, if there was $fields the should always be $list, but this pony isnt built for speed :)
										$wpdb->query("DELETE FROM ".$wpdb->prefix."nggpano_field_values WHERE fid = ".$val->nggpano_field_id." AND pid IN (".implode(", ", $list).")"); //remove data from that field in that gallery
									}
								}
							}
							$wpdb->query("DELETE FROM ".$wpdb->prefix."nggpano_fields_link WHERE id = ".$val->link_id); //remove link
							//$wpdb->query("DELETE FROM ".$wpdb->prefix."nggpano_field_values WHERE fid = ".$val->nggpano_field_id); //remove data from that field in that gallery
						}
						
						//remove from post list if in currnt
						if($config["galleries"][$val->gid]) {
							unset($config["galleries"][$val->gid]);
						}
					}
					
					//insert whats left in post list (the new links)
					foreach ((array)$config["galleries"] as $key=>$val) {
						$qry = "INSERT INTO ".$wpdb->prefix."nggpano_fields_link (`id`, `field_id`, `gid`) VALUES(null, '".$wpdb->escape($config["id"])."', '".$wpdb->escape($key)."')";
						$wpdb->query($qry);
					}
					
					return true;
				}else{
					if($wpdb->get_row("SELECT * FROM ".$wpdb->prefix."nggpano_fields WHERE field_name = '".$wpdb->escape($config["field_name"])."' AND ngg_type = '".$wpdb->escape($config["ngg_type"])."'")) {
						return "ERROR: Field name already exists";
					}
					if($config["field_type"] != nggpano_FIELD_TYPE_SELECT) { //can only have drop opts if it is a drop down
						$config["drop_options"] = "";
					}
					$qry = "INSERT INTO ".$wpdb->prefix."nggpano_fields (`id`, `field_name`, `field_type`, `drop_options`, `ngg_type`) VALUES (null, '".$wpdb->escape($config["field_name"])."', '".$wpdb->escape($config["field_type"])."', '".$wpdb->escape($config["drop_options"])."', '".$wpdb->escape($config["ngg_type"])."')";
					if($wpdb->query($qry)) {
						$linkerr = false;
						$fid = $wpdb->insert_id;
						foreach ((array)$config["galleries"] as $key=>$val) {
							$qry = "INSERT INTO ".$wpdb->prefix."nggpano_fields_link (`id`, `field_id`, `gid`) VALUES(null, '".$wpdb->escape($fid)."', '".$wpdb->escape($key)."')";
							if(!$wpdb->query($qry)) {
								$linkerr = true;
							}
						}
						if($linkerr) {
							return "ERROR: Field was saved successfully, but the system failed to link the field to 1 or more galleries";
						}else{
							return true;
						}
					}else{
						return "ERROR: Failed to save field";
					}
				}
			}else{
				return "ERROR: Bad field name";
			}
		}
		
		//api that deletes a column from the list, and removes all values saved for it
		function nggpano_delete_field($fid) {
			global $wpdb;
			if(is_numeric($fid)) {
				if($wpdb->query("DELETE FROM ".$wpdb->prefix."nggpano_field_values WHERE fid = ".$fid) !== false) {
					if($wpdb->query("DELETE FROM ".$wpdb->prefix."nggpano_fields WHERE id = ".$fid) !== false) {
						return true;
					}
				}
				return false;
			}else{
				return false;
			}
		}

		//get a list of all custom fields
		function nggpano_get_field_list($ngg_type, $gid=null) {
			global $wpdb;
			
			if($gid) {
				$qry = "SELECT field.* FROM ".$wpdb->prefix."nggpano_fields_link AS link LEFT JOIN ".$wpdb->prefix."nggpano_fields AS field ON link.field_id = field.id WHERE field.ngg_type = ".$wpdb->escape($ngg_type)." AND link.gid = '".$wpdb->escape($gid)."'";
			}else{
				$qry = "SELECT * FROM ".$wpdb->prefix."nggpano_fields WHERE ngg_type = ".$wpdb->escape($ngg_type);
			}
			$fields = $wpdb->get_results($qry);
			return $fields;
		}
		
		//get what galleries are linked to a field
		function nggpano_get_linked_galleries($fid) {
			global $wpdb;
			if(is_numeric($fid)) {
				return $wpdb->get_results("SELECT gal.*, link.id AS link_id, link.field_id AS nggpano_field_id FROM ".$wpdb->prefix."nggpano_fields_link AS link LEFT JOIN ".$wpdb->prefix."ngg_gallery AS gal ON link.gid = gal.gid WHERE link.field_id = '".$wpdb->escape($fid)."'");
			}else{
				return false;
			}
		}
		


	
	//front end stuff{

	//}
//}
?>