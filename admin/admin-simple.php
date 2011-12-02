<?php
// stop direct call
if(preg_match("#".basename(__FILE__)."#", $_SERVER["PHP_SELF"])) {die("You are not allowed to call this page directly.");}

add_action('admin_menu', 'nggv_adminMenu');
function nggv_adminMenu() {
        add_menu_page('NGG Voting Defaults', 'NGG Voting Defaults', 'manage_options', __FILE__, 'nggv_admin_options');
        add_submenu_page(__FILE__, 'NGG Voting Top Rated Images', 'Top Rated Images', 'manage_options', 'nggv-top-rated-images', 'nggv_admin_top_rated_images');
}
function nggv_admin_options() {
        if($_GET["action"] == "get-votes-list") {
                echo '<!--#NGGV START AJAX RESPONSE#-->'; //do not edit this line!!!

                if($_GET["gid"]) {
                        $options = nggv_getVotingOptions($_GET["gid"]);
                        echo 'var nggv_voting_type = '.$options->voting_type.';';

                        $results = nggv_getVotingResults($_GET["gid"]);
                        echo "var nggv_votes_list = [];";
                        foreach ((array)$results["list"] as $key=>$val) {
                                $user_info = $val->user_id ? get_userdata($val->user_id) : array();
                                echo "
                                        nggv_votes_list[nggv_votes_list.length] = [];
                                        nggv_votes_list[nggv_votes_list.length-1][0] = '".$val->vote."';
                                        nggv_votes_list[nggv_votes_list.length-1][1] = '".$val->dateadded."';
                                        nggv_votes_list[nggv_votes_list.length-1][2] = '".$val->ip."';
                                        nggv_votes_list[nggv_votes_list.length-1][3] = [];
                                        nggv_votes_list[nggv_votes_list.length-1][3][0] = '".$val->user_id."';
                                        nggv_votes_list[nggv_votes_list.length-1][3][1] = '".$user_info->user_login."';
                                ";
                        }
                }else if($_GET["pid"]){
                        $options = nggv_getImageVotingOptions($_GET["pid"]);
                        echo 'var nggv_voting_type = '.$options->voting_type.';';

                        $results = nggv_getImageVotingResults($_GET["pid"]);

                        echo "var nggv_votes_list = [];";
                        foreach ((array)$results["list"] as $key=>$val) {
                                $user_info = $val->user_id ? get_userdata($val->user_id) : array();
                                echo "
                                        nggv_votes_list[nggv_votes_list.length] = [];
                                        nggv_votes_list[nggv_votes_list.length-1][0] = '".$val->vote."';
                                        nggv_votes_list[nggv_votes_list.length-1][1] = '".$val->dateadded."';
                                        nggv_votes_list[nggv_votes_list.length-1][2] = '".$val->ip."';
                                        nggv_votes_list[nggv_votes_list.length-1][3] = [];
                                        nggv_votes_list[nggv_votes_list.length-1][3][0] = '".$val->user_id."';
                                        nggv_votes_list[nggv_votes_list.length-1][3][1] = '".$user_info->user_login."';
                                ";
                        }
                }else{
                        //error num?
                }

                exit;
        }else{
                if($_POST['nggv']) {
                        //Gallery
                        if(get_option('nggv_gallery_enable') === false) { //bool false means does not exists
                                add_option('nggv_gallery_enable', ($_POST['nggv']['gallery']['enable'] ? '1' : '0'), null, 'no');
                        }else{
                                update_option('nggv_gallery_enable', ($_POST['nggv']['gallery']['enable'] ? '1' : '0'));
                        }
                        if(get_option('nggv_gallery_force_login') === false) { //bool false means does not exists
                                add_option('nggv_gallery_force_login', ($_POST['nggv']['gallery']['force_login'] ? '1' : '0'), null, 'no');
                        }else{
                                update_option('nggv_gallery_force_login', ($_POST['nggv']['gallery']['force_login'] ? '1' : '0'));
                        }
                        if(get_option('nggv_gallery_force_once') === false) { //bool false means does not exists
                                add_option('nggv_gallery_force_once', ($_POST['nggv']['gallery']['force_once'] ? '1' : '0'), null, 'no');
                        }else{
                                update_option('nggv_gallery_force_once', ($_POST['nggv']['gallery']['force_once'] ? '1' : '0'));
                        }
                        if(get_option('nggv_gallery_user_results') === false) { //bool false means does not exists
                                add_option('nggv_gallery_user_results', ($_POST['nggv']['gallery']['user_results'] ? '1' : '0'), null, 'no');
                        }else{
                                update_option('nggv_gallery_user_results', ($_POST['nggv']['gallery']['user_results'] ? '1' : '0'));
                        }
                        if(get_option('nggv_gallery_voting_type') === false) { //bool false means does not exists
                                add_option('nggv_gallery_voting_type', $_POST['nggv']['gallery']['voting_type'], null, 'no');
                        }else{
                                update_option('nggv_gallery_voting_type', $_POST['nggv']['gallery']['voting_type']);
                        }

                        //Images
                        if(get_option('nggv_image_enable') === false) { //bool false means does not exists
                                add_option('nggv_image_enable', ($_POST['nggv']['image']['enable'] ? '1' : '0'), null, 'no');
                        }else{
                                update_option('nggv_image_enable', ($_POST['nggv']['image']['enable'] ? '1' : '0'));
                        }
                        if(get_option('nggv_image_force_login') === false) { //bool false means does not exists
                                add_option('nggv_image_force_login', ($_POST['nggv']['image']['force_login'] ? '1' : '0'), null, 'no');
                        }else{
                                update_option('nggv_image_force_login', ($_POST['nggv']['image']['force_login'] ? '1' : '0'));
                        }
                        if(get_option('nggv_image_force_once') === false) { //bool false means does not exists
                                add_option('nggv_image_force_once', ($_POST['nggv']['image']['force_once'] <= 2 ? $_POST['nggv']['image']['force_once'] : '0'), null, 'no');
                        }else{
                                update_option('nggv_image_force_once', ($_POST['nggv']['image']['force_once'] <= 2 ? $_POST['nggv']['image']['force_once'] : '0'));
                        }
                        if(get_option('nggv_image_user_results') === false) { //bool false means does not exists
                                add_option('nggv_image_user_results', ($_POST['nggv']['image']['user_results'] ? '1' : '0'), null, 'no');
                        }else{
                                update_option('nggv_image_user_results', ($_POST['nggv']['image']['user_results'] ? '1' : '0'));
                        }
                        if(get_option('nggv_image_voting_type') === false) { //bool false means does not exists
                                add_option('nggv_image_voting_type', $_POST['nggv']['image']['voting_type'], null, 'no');
                        }else{
                                update_option('nggv_image_voting_type', $_POST['nggv']['image']['voting_type']);
                        }
                }

                $filepath = admin_url()."admin.php?page=".$_GET["page"];
                ?>
                <div class="wrap">
                        <h2>Welcome to NextGEN Gallery Voting</h2>
                        <p>This plugin adds the ability for users to vote on NextGEN Galleries and Images.  If you need any help or find any bugs, please create a post at the Wordpress plugin support forum, with the tag '<a href="http://wordpress.org/tags/nextgen-gallery-voting?forum_id=10" target="_blank">nextgen-gallery-voting</a>'</p>

                        <h2>Default Options</h2>
                        <p>Here you can set the default voting options for <strong>new</strong> Galleries and Images.  Setting these options will not affect any existing Galleries or Images</p>
                        <div id="poststuff">
                                <form id="" method="POST" action="<?php echo $filepath; ?>" accept-charset="utf-8" >
                                        <input type="hidden" name="nggv[force]" value="1" /> <!-- this will just force _POST['nggv'] even if all checkboxes are unchecked -->
                                        <div class="postbox">
                                                <table class="form-table" style="width:550px;">
                                                        <tr>
                                                                <td colspan="2" style="text-align:right;"><h3>Gallery</h3></th>
                                                                <td style="text-align:center;"><h3>Image</h3></th>
                                                        </tr>
                                                        <tr valign="top">
                                                                <th style="width:250px;">Enable:</th>
                                                                <td style="width:100px; text-align:center;"><input type="checkbox" name="nggv[gallery][enable]" <?php echo (get_option('nggv_gallery_enable') ? 'checked="checked"' : ''); ?> /></td>
                                                                <td style="width:200px; text-align:center;"><input type="checkbox" name="nggv[image][enable]" <?php echo (get_option('nggv_image_enable') ? 'checked="checked"' : ''); ?> /></td>
                                                        </tr>

                                                        <tr valign="top">
                                                                <th>Only allow logged in users to vote:</th>
                                                                <td style="text-align:center;"><input type="checkbox" name="nggv[gallery][force_login]" <?php echo (get_option('nggv_gallery_force_login') ? 'checked="checked"' : ''); ?> /></td>
                                                                <td style="text-align:center;"><input type="checkbox" name="nggv[image][force_login]" <?php echo (get_option('nggv_image_force_login') ? 'checked="checked"' : ''); ?> /></td>
                                                        </tr>

                                                        <tr valign="top">
                                                                <th>Number of votes allowed<br ><em>(IP or userid is used to stop multiple)</em></th>
                                                                <td style="text-align:center;"><input type="checkbox" name="nggv[gallery][force_once]" <?php echo (get_option('nggv_gallery_force_once') ? 'checked="checked"' : ''); ?> /></td>
                                                                <td style="text-align:center;">
                                                                        <input type="radio" name="nggv[image][force_once]" <?php echo (get_option('nggv_image_force_once') == 0 ? 'checked="checked"' : ''); ?> value="0" />Unlimited votes<br />
                                                                        <input type="radio" name="nggv[image][force_once]" <?php echo (get_option('nggv_image_force_once') == 1 ? 'checked="checked"' : ''); ?> value="1" />One per image<br />
                                                                        <input type="radio" name="nggv[image][force_once]" <?php echo (get_option('nggv_image_force_once') == 2 ? 'checked="checked"' : ''); ?> value="2" />One per gallery image is in
                                                                </td>
                                                        </tr>

                                                        <tr valign="top">
                                                                <th>Allow users to see results:</th>
                                                                <td style="text-align:center;"><input type="checkbox" name="nggv[gallery][user_results]" <?php echo (get_option('nggv_gallery_user_results') ? 'checked="checked"' : ''); ?> /></td>
                                                                <td style="text-align:center;"><input type="checkbox" name="nggv[image][user_results]" <?php echo (get_option('nggv_image_user_results') ? 'checked="checked"' : ''); ?> /></td>
                                                        </tr>

                                                        <tr valign="top">
                                                                <th>Rating Type:</th>
                                                                <td style="text-align:center;">
                                                                        <select name="nggv[gallery][voting_type]">
                                                                                <option value="1" <?php echo (get_option('nggv_gallery_voting_type') == 1 ? 'selected="selected"' : ''); ?>>Drop Down</option>
                                                                                <option value="2" <?php echo (get_option('nggv_gallery_voting_type') == 2 ? 'selected="selected"' : ''); ?>>Star Rating</option>
                                                                                <option value="3" <?php echo (get_option('nggv_gallery_voting_type') == 3 ? 'selected="selected"' : ''); ?>>Like / Dislike</option>
                                                                        </select>
                                                                </td>
                                                                <td style="text-align:center;">
                                                                        <select name="nggv[image][voting_type]">
                                                                                <option value="1" <?php echo (get_option('nggv_image_voting_type') == 1 ? 'selected="selected"' : ''); ?>>Drop Down</option>
                                                                                <option value="2" <?php echo (get_option('nggv_image_voting_type') == 2 ? 'selected="selected"' : ''); ?>>Star Rating</option>
                                                                                <option value="3" <?php echo (get_option('nggv_image_voting_type') == 3 ? 'selected="selected"' : ''); ?>>Like / Dislike</option>
                                                                        </select>

                                                                </td>
                                                        </tr>

                                                        <tr>
                                                                <td colspan="2">
                                                                        <div class="submit"><input class="button-primary" type="submit" value="Save Defaults"/>
                                                                        </div>
                                                                </td>
                                                        </tr>
                                                </table>
                                        </div>
                                </form>
                        </div>
                </div>
                <?php
        }
}

add_action('ngg_update_gallery', 'nggv_save_gallery_options', 10, 2);
/**
 * Save the options for a gallery and/or images
 * @param int $gid The NextGEN Gallery ID
 * @param array $post the _POST array from the gallery save form. We have added the following fields for our options
 *  bool (int 1/0) post["nggv"]["enable"] : Enable voting for the gallery
 *  bool (int 1/0) post["nggv"]["force_login"] : Force the user to login to cast vote
 *  bool (int 1/0) post["nggv"]["force_once"] : Only allow a user to vote once
 *  bool (int 1/0) post["nggv"]["user_results"] : If users see results
 *  bool (int 1/0) post["nggv_image"][image ID]["enable"] : Enable voting for the image
 *  bool (int 1/0) post["nggv_image"][image ID]["force_login"] : Only allow a user to vote once
 *  integer (0, 1, 2) post["nggv_image"][image ID]["force_once"] : Only allow a user to vote once(1), Only allow user to vote once per image in this gallery(2)
 *  bool (int 1/0) post["nggv_image"][image ID]["user_results"] : If users see results
 * @param bool $noReload If set to true, this function will act like an api and simply let the code execution continue after being called.
 *  If false (default), this funtion uses a js hack to reload the page
 * @author Shaun <shaunalberts@gmail.com>
 * @return void
 */
function nggv_save_gallery_options($gid, $post, $noReload=false) {
        global $wpdb;

        if($post["nggv"]) { //gallery options
                $enable = $post["nggv"]["enable"] ? "1" : "0";
                $login = $post["nggv"]["force_login"] ? "1" : "0";
                $once = $post["nggv"]["force_once"] ? "1" : "0";
                $user_results = $post["nggv"]["user_results"] ? "1" : "0";
                $voting_type = is_numeric($post["nggv"]["voting_type"]) ? $post["nggv"]["voting_type"] : 1;

                if(nggv_getVotingOptions($gid)) {
                        $wpdb->query("UPDATE ".$wpdb->prefix."nggv_settings SET force_login = '".$login."', force_once = '".$once."', user_results = '".$user_results."', enable = '".$enable."', voting_type = '".$voting_type."' WHERE gid = '".$wpdb->escape($gid)."'");
                }else{
                        $wpdb->query("INSERT INTO ".$wpdb->prefix."nggv_settings (id, gid, enable, force_login, force_once, user_results, voting_type) VALUES (null, '".$wpdb->escape($gid)."', '".$enable."', '".$login."', '".$once."', '".$user_results."', '".$voting_type."')");
                }
        }

        if($post["nggv_image"]) { //image options
                foreach ((array)$post["nggv_image"] as $pid=>$val) {
                        $enable = $wpdb->escape($val["enable"]) ? "1" : "0";
                        $login = $wpdb->escape($val["force_login"]) ? "1" : "0";
                        $once = $wpdb->escape($val["force_once"]) <= 2 ? $wpdb->escape($val["force_once"]) : "0";
                        $user_results = $wpdb->escape($val["user_results"]) ? "1" : "0";
                        $voting_type = is_numeric($val["voting_type"]) ? $val["voting_type"] : 1;

                        if(nggv_getImageVotingOptions($pid)) {
                                $wpdb->query("UPDATE ".$wpdb->prefix."nggv_settings SET force_login = '".$login."', force_once = '".$once."', user_results = '".$user_results."', enable = '".$enable."', voting_type = '".$voting_type."' WHERE pid = '".$wpdb->escape($pid)."'");
                        }else{
                                $wpdb->query("INSERT INTO ".$wpdb->prefix."nggv_settings (id, pid, enable, force_login, force_once, user_results, voting_type) VALUES (null, '".$wpdb->escape($pid)."', '".$enable."', '".$login."', '".$once."', '".$user_results."', '".$voting_type."')");
                        }
                }
        }

        if(!$noReload) {
                //gotta force a reload or the js globals declared in nggv_add_vote_options() are set to the pre-saved values, and the checkboxes are ticked incorrectly (hack hackity hack hack hack)
                echo "<script>window.location = window.location;</script>";
                exit;
        }
}

// in version 1.7.0 ngg renamed the filter name
//if(version_compare(NGGVERSION, '1.6.99', '<')) {
        //add_action("ngg_manage_gallery_columns", "nggv_add_image_vote_options_field");
//}else{
        add_action("ngg_manage_images_columns", "nggv_add_image_vote_options_field");
//}
/**
 * Add a custom field to the images field list.  This give us a place to add the voting options for each image with nggv_add_image_vote_options_field()
 * Also enqueues a script that will add the gallery voting options with js (sneaky, but has to be done)
 * @param array $gallery_columns The array of current fields
 * @author Shaun <shaun@worldwidecreative.co.za>
 * @return array $gallery_columns with an added field
 */
function nggv_add_image_vote_options_field($gallery_columns) {
        if(version_compare(NGGVERSION, '1.8.0', '>=')) {
                global $nggv_scripted_tag;
                if(!$nggv_scripted_tag) {
                        $nggv_scripted_tag = true;
                        echo '<script src="'.WP_PLUGIN_URL.'/nextgen-gallery-voting/js/gallery_options.js"></script>';
                }
        }else{ //the old way of doing it (sheesh, i didnt read those docs)
                wp_enqueue_script('nggc_gallery_options', WP_PLUGIN_URL . '/nextgen-gallery-voting/js/gallery_options.js', array('jquery'), false, true);
        }
        $gallery_columns["nggv_image_vote_options"] = "Image Voting Options";
        return $gallery_columns;
}

// in version 1.7.0 ngg renamed the filter name
//if(version_compare(NGGVERSION, '1.6.99', '<')) {
        //add_action("ngg_manage_gallery_custom_column", "nggv_add_image_voting_options", 10 ,2);
//}else{
        add_action("ngg_manage_image_custom_column", "nggv_add_image_voting_options", 10 ,2);
//}
/**
 * Add the voing options to the gallery (sneaky js) and each image
 * @param string $gallery_column_key The key value of the 'custom' fields added by nggv_add_image_vote_options_field()
 * @author Shaun <shaun@worldwidecreative.co.za>
 * @return void
 */
function nggv_add_image_voting_options($gallery_column_key, $pid) {
        global $nggv_scripted;

        if(!$nggv_scripted) { //its a hack, so just check that its only called once :)
                $nggv_scripted = true;
                $options = nggv_getVotingOptions($_GET["gid"]);
                $results = nggv_getVotingResults($_GET["gid"], array("avg"=>true, "num"=>true, "likes"=>true, "dislikes"=>true));

                $uri = $_SERVER["REQUEST_URI"];
                $info = parse_url($uri);
                $dirName = plugin_basename(dirname(__FILE__));
                $popup = $info["path"]."?page=".$dirName."/".basename(__FILE__)."&action=get-votes-list";

                echo "<script>
                var nggv_gid = parseInt(".$_GET["gid"].");
                var nggv_enable = parseInt(".$options->enable.");
                var nggv_login = parseInt(".$options->force_login.");
                var nggv_once = parseInt(".$options->force_once.");
                var user_results = parseInt(".$options->user_results.");
                var voting_type = parseInt(".$options->voting_type.");
                var nggv_avg = Math.round(".($results["avg"] ? $results["avg"] : 0).") / 10;
                var nggv_num_votes = parseInt(".($results["number"] ? $results["number"] : 0).");
                var nggv_num_likes = parseInt(".($results["likes"] ? $results["likes"] : 0).");
                var nggv_num_dislikes = parseInt(".($results["dislikes"] ? $results["dislikes"] : 0).");

                var nggv_more_url = '".$popup."';
                </script>";

                //the popup window for results
                echo '<div id="nggvShowList" style="display:none;">';
                echo '<span style="float:right;" width: 100px; height: 40px; border:>';
                echo '<a href="#" id="nggv_more_results_close">Close Window</a>';
                echo '</span>';
                echo '<div style="clear:both;"></div>';

                echo '<div id="nggvShowList_content">';
                echo '<img src="'.WP_PLUGIN_URL."/".$dirName."/images/loading.gif".'" />';
                echo '</div>';
                echo '</div>';
        }

        if($gallery_column_key == "nggv_image_vote_options") {
                $opts = nggv_getImageVotingOptions($pid);
                echo "<table width='100%'";
                echo "<tr><td width='1px'><input type='checkbox' name='nggv_image[".$pid."][enable]' value=1 ".($opts->enable ? "checked" : "")." /></td><td>Enable for image</td></tr>";
                echo "<tr><td width='1px'><input type='checkbox' name='nggv_image[".$pid."][force_login]' value=1 ".($opts->force_login ? "checked" : "")." /></td><td>Only allow logged in users</td></tr>";
                //echo "<tr><td width='1px'><input type='checkbox' name='nggv_image[".$pid."][force_once]' value=1 ".($opts->force_once ? "checked" : "")." /></td><td>Only allow 1 vote per person</td></tr>";
                echo "<tr><td width='1px'><input type='radio' name='nggv_image[".$pid."][force_once]' value=3 ".(!$opts->force_once ? "checked" : "")." /></td><td>Unlimited votes for this image</td></tr>";
                echo "<tr><td width='1px'><input type='radio' name='nggv_image[".$pid."][force_once]' value=1 ".($opts->force_once == 1 ? "checked" : "")." /></td><td>Only allow 1 vote per person for this image</td></tr>";
                echo "<tr><td width='1px'><input type='radio' name='nggv_image[".$pid."][force_once]' value=2 ".($opts->force_once == 2 ? "checked" : "")." /></td><td>Only allow 1 vote per person for this gallery</td></tr>";
                echo "<tr><td width='1px'><input type='checkbox' name='nggv_image[".$pid."][user_results]' value=1 ".($opts->user_results ? "checked" : "")." /></td><td>Allow users to see results</td></tr>";

                echo "<tr><td colspan=2>";
                echo "Rating Type: <select name='nggv_image[".$pid."][voting_type]'>";
                echo "<option value='1' ".($opts->voting_type == 1 || !$opts->voting_type ? "selected" : "").">Drop Down</option>";
                echo "<option value='2' ".($opts->voting_type == 2 ? "selected" : "").">Star Rating</option>";
                echo "<option value='3' ".($opts->voting_type == 3 ? "selected" : "").">Like / Dislike</option>";
                echo "</select>";
                echo "</td></tr>";


                echo "</table>";
                if($opts->voting_type == 3) {
                        $results = nggv_getImageVotingResults($pid, array("likes"=>true, "dislikes"=>true));
                        echo "Current Votes: ";
                        echo "<a href='' class='nggv_mote_results_image' id='nggv_more_results_image_".$pid."'>";
                        echo $results['likes'].' ';
                        echo $results['likes'] == 1 ? 'Like, ' : 'Likes, ';
                        echo $results['dislikes'].' ';
                        echo $results['dislikes'] == 1 ? 'Dislike' : 'Dislikes';
                        echo "</a>";
                }else{
                        $results = nggv_getImageVotingResults($pid, array("avg"=>true, "num"=>true));
                        echo "Current Avg: ".round(($results["avg"] / 10), 1)." / 10 <a href='' class='nggv_mote_results_image' id='nggv_more_results_image_".$pid."'>(".($results["number"] ? $results["number"] : "0")." votes cast)</a>";
                }
        }
}

add_action("ngg_add_new_gallery_form", "nggv_new_gallery_form"); //new in ngg 1.4.0a
/**
 * Adds the default voting options for a new gallery.  Can be tweaked for the specif gallery without affecting the defaults
 * @author Shaun <shaun@worldwidecreative.co.za>
 * @return void
 */
function nggv_new_gallery_form() {
        ?>
        <tr valign="top">
        <th scope="row">Gallery Voting Options:<br /><em>(Pre-set from <a href="<?php echo admin_url(); ?>admin.php?page=nextgen-gallery-voting/ngg-voting.php">here</a>)</em></th> 
        <td>
                <input type="checkbox" name="nggv[gallery][enable]" <?php echo (get_option('nggv_gallery_enable') ? 'checked="checked"' : ''); ?> />
                Enable<br />

                <input type="checkbox" name="nggv[gallery][force_login]" <?php echo (get_option('nggv_gallery_force_login') ? 'checked="checked"' : ''); ?> />
                Only allow logged in users to vote<br />

                <input type="checkbox" name="nggv[gallery][force_once]" <?php echo (get_option('nggv_gallery_force_once') ? 'checked="checked"' : ''); ?> />
                Only allow 1 vote per person <em>(IP or userid is used to stop multiple)</em><br />

                <input type="checkbox" name="nggv[gallery][user_results]" <?php echo (get_option('nggv_gallery_user_results') ? 'checked="checked"' : ''); ?> />
                Allow users to see results<br />

                Rating Type:
                <select name="nggv[gallery][voting_type]">
                        <option value="1" <?php echo (get_option('nggv_gallery_voting_type') == 1 ? 'selected="selected"' : ''); ?>>Drop Down</option>
                        <option value="2" <?php echo (get_option('nggv_gallery_voting_type') == 2 ? 'selected="selected"' : ''); ?>>Star Rating</option>
                        <option value="3" <?php echo (get_option('nggv_gallery_voting_type') == 3 ? 'selected="selected"' : ''); ?>>Like / Dislike</option>
                </select>
        </td>
        </tr>
        <?php
}

add_action("ngg_created_new_gallery", "nggv_add_new_gallery"); //new in ngg 1.4.0a
/**
 * Saves the voting options for the new gallery
 * @param int $gid the gallery id
 * @author Shaun <shaun@worldwidecreative.co.za>
 * @return voide
 */
function nggv_add_new_gallery($gid) {
        if($gid) {
                $post = array();
                $post['nggv'] = $_POST['nggv']['gallery'];
                nggv_save_gallery_options($gid, $post, true);
        }
}

add_action("ngg_added_new_image", "nggv_add_new_image");
/**
 * Add the image voting options for a new image (pulled from the defaaults
 * @param array $image the new image details
 * @author Shaun <shaun@worldwidecreative.co.za>
 * @return void
 */
function nggv_add_new_image($image) {
        if($image['id']) {
                $post = array();
                $post['nggv_image'] = array();
                $post['nggv_image'][$image['id']] = array();
                $post['nggv_image'][$image['id']]['enable'] = get_option('nggv_image_enable');
                $post['nggv_image'][$image['id']]['force_login'] = get_option('nggv_image_force_login');
                $post['nggv_image'][$image['id']]['force_once'] = get_option('nggv_image_force_once');
                $post['nggv_image'][$image['id']]['user_results'] = get_option('nggv_image_user_results');
                $post['nggv_image'][$image['id']]['voting_type'] = get_option('nggv_image_voting_type');

                nggv_save_gallery_options($image['galleryID'], $post, true);
        }
}

function nggv_admin_top_rated_images() {
        global $nggdb, $wpdb;
        $gallerylist = $nggdb->find_all_galleries('gid', 'asc', false, 0, 0, false);

        $_GET['nggv']['limit'] = is_numeric($_GET['nggv']['limit']) ? $_GET['nggv']['limit'] : 25;
        $_GET['nggv']['order'] = $_GET['nggv']['order'] ? $_GET['nggv']['order'] : 'DESC';

        $qry = 'SELECT pid, SUM(vote) AS total, AVG(vote) AS avg, MIN(vote) AS min, MAX(vote) AS max, COUNT(vote) AS num'; //yes, no joins for now. performance isnt an issue (i hope...)
        $qry .= ' FROM '.$wpdb->prefix.'nggv_votes';
        $qry .= ' WHERE';
        $qry .= ' pid > 0';
        $qry .= ' GROUP BY pid';
        $qry .= ' ORDER BY avg '.$_GET['nggv']['order'];
        $qry .= ' LIMIT 0, '.$_GET['nggv']['limit'];

        $list = $wpdb->get_results($qry);
        ?>
        <div class="wrap">
                <h2>Top Rated Images</h2>

                <div id="poststuff">
                        <form id="" method="GET" action="" accept-charset="utf-8">
                                <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
                                <div class="postbox">
                                        <h3>Filter</h3>
                                        <table class="form-table">
                                                <tr>
                                                        <th>Limit</th>
                                                        <td>
                                                                <input type="text" name="nggv[limit]" value="<?php echo $_GET['nggv']['limit'] ?>" />
                                                        </td>

                                                        <th style="width:20%;">Order</th>
                                                        <td style="width:30%;">
                                                                <select name="nggv[order]">
                                                                        <option value="desc" <?php echo ($_GET['nggv']['order'] == 'desc' ? 'selected' : ''); ?>>Highest to Lowest</option>
                                                                        <option value="asc" <?php echo ($_GET['nggv']['order'] == 'asc' ? 'selected' : ''); ?>>Lowest to Highest</option>
                                                                </select>
                                                        </td>
                                                </tr>

                                                <tr>
                                                        <td colspan=4>
                                                                <input class="button-primary" type="submit" value="Filter Images" />
                                                        </td>
                                                </tr>
                                        </table>
                                </div>
                        </form>
                </div>

                <?php if($list) { ?>
                        <div class="updated below-h2">
                                Wow, check all those awesome people voing for your images! Have you returned the favour by <a target="_blank" href="http://wordpress.org/extend/plugins/nextgen-gallery-voting/">rating NGG Voting</a>?<br />
                                Maybe you're even more awesomer and might consider <a target="_blank" href="http://shauno.co.za/donate/">donating</a>?
                        </div>
                <?php } ?>

                <table cellspacing="0" class="wp-list-table widefat fixed">
                <thead>
                        <tr>
                                <th style="width:30px;">pid</th>
                                <th>Gallery Name</th>
                                <th>Filename</th>
                                <th>Avg / 10</th>
                                <th>Max / 10</th>
                                <th>Min / 10</th>
                                <th>Number Votes</th>
                                <th></th>
                        </tr>
                </thead>
                <?php if($list) { ?>
                        <tbody>
                                <?php if($list) { ?>
                                        <?php $cnt = 0; ?>
                                        <?php foreach ($list as $key=>$val) { ?>
                                                <?php $image = nggdb::find_image($val->pid); ?>
                                                        <tr <?php echo $cnt % 2 == 0 ? 'class="alternate"' : '' ?>>
                                                                <td><?php echo $val->pid ?></td>
                                                                <td><?php echo $image->title; ?></td>
                                                                <td><?php echo $image->filename; ?></td>
                                                                <td><?php echo round($val->avg / 10, 2) ?></td>
                                                                <td><?php echo round($val->max / 10, 2) ?></td>
                                                                <td><?php echo round($val->min / 10, 2) ?></td>
                                                                <td><?php echo $val->num ?>	</td>
                                                                <td><img src="<?php echo $image->thumbURL; ?>" /></td>
                                                        </tr>
                                                        <?php $cnt++; ?>
                                                <?php } ?>
                                <?php }else{ ?>
                                        <tr>
                                                <td colspan="4">No records found. <a href="<?php echo $this->pluginUrl; ?>page=sf-gallery-add">Click here</a> to add your first gallery.</td>
                                        </tr>
                                <?php } ?>
                        </tbody>
                <?php }else{ ?>
                        <td colspan=6>No results found</td>
                <?php } ?>
        </table>

        </div>


        <?php
}
?>
