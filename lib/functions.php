<?php
// stop direct call
if(preg_match("#".basename(__FILE__)."#", $_SERVER["PHP_SELF"])) {die("You are not allowed to call this page directly.");}

    /**
     * Gets the gallery options for a ngg panoplugin
     * @param int $gid The NextGEN Gallery ID
     * @return object of options on success, empty array on failure
     */
    function nggpano_getGalleryOptions($gid) {
            global $wpdb;
            $opts = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."nggpano_gallery WHERE gid = '".$wpdb->escape($gid)."'");
            return $opts ? $opts : array();
    }

    
   /**
     * Get all the galleries
     * 
     * @param string $order_by
     * @param string $order_dir
     * @param bool $counter (optional) Select true  when you need to count the images
     * @param int $limit number of paged galleries, 0 shows all galleries
     * @param int $start the start index for paged galleries
     * @param bool $exclude
     * @return array $galleries
     */
    function nggpano_find_all_galleries_with_order($order_dir = 'ASC', $counter = false, $exclude = true, $gallery_exclude = true) {      
        global $wpdb; 
        $return_galleries = array();
        // Check for the exclude setting
        $exclude_clause = ($exclude) ? ' AND exclude<>1 ' : '';
        $order_by = 'NGPG.order, NGG.name';
        $order_dir = ( $order_dir == 'DESC') ? 'DESC' : 'ASC';

        //gallery exclude clause
        $gallery_exclude_clause = ($gallery_exclude) ? ' AND NGPG.exclude<>1 ' : '';
        
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM $wpdb->nggallery NGG, ".$wpdb->prefix."nggpano_gallery NGPG WHERE NGG.gid = NGPG.gid".$gallery_exclude_clause." ORDER BY {$order_by} {$order_dir}";
        
        $return_galleries = $wpdb->get_results( $query , OBJECT_K );
        
        if ( !$return_galleries )
            return array();
        
        // get the galleries information    
        foreach ($return_galleries as $key => $value) {
            $galleriesID[] = $key;
            // init the counter values
            $return_galleries[$key]->counter = 0;
            $return_galleries[$key]->title = stripslashes($return_galleries[$key]->title);
            $return_galleries[$key]->galdesc  = stripslashes($return_galleries[$key]->galdesc);
            $return_galleries[$key]->abspath = WINABSPATH . $return_galleries[$key]->path;
            wp_cache_add($key, $return_galleries[$key], 'ngg_gallery');      
        }

        // if we didn't need to count the images then stop here
        if ( !$counter )
            return $return_galleries;
        
        // get the counter values   
        $picturesCounter = $wpdb->get_results('SELECT galleryid, COUNT(*) as counter FROM '.$wpdb->nggpictures.' WHERE galleryid IN (\''.implode('\',\'', $galleriesID).'\') ' . $exclude_clause . ' GROUP BY galleryid', OBJECT_K);

        if ( !$picturesCounter )
            return $return_galleries;
        
        // add the counter to the gallery objekt    
        foreach ($picturesCounter as $key => $value) {
            $return_galleries[$value->galleryid]->counter = $value->counter;
            wp_cache_set($value->galleryid, $return_galleries[$value->galleryid], 'ngg_gallery');
        }
        
        return $return_galleries;
    }
    
    

    /**
     * Gets the panoramics options for a specific image
     * @param int $pid The image ID
     * @return object of options on success, empty array on failure
     */		
    function nggpano_getImagePanoramicOptions($pid) {
            global $wpdb;
            $opts = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."nggpano_panoramic WHERE pid = '".$wpdb->escape($pid)."'");
            if (isset($opts->pid)) {
                return is_numeric($pid) && $opts->pid == $pid ? $opts : array();	
            } else {
                return array();
            }
    }
    
    /**
    * nggpano_getPanoConfig() - Get all panoramic options
    * 
    * @access public 
    * @param int $gallery_id, id of the gallery

    * @return array array with all option
    */

    function nggpano_getPanoConfig($gallery_id) {

        global $nggpano_options;
            //****get folder for  krpano files ****/
        //Get option from Gallery pano config
        $gallery_viewertemplate = '';
        if(is_numeric($gallery_id)) {
            $gallery_pano_options = nggpano_getGalleryOptions($gallery_id);
            $gallery_viewertemplate = (isset($gallery_pano_options->skin) && $gallery_pano_options->skin <> '') ? $gallery_pano_options->skin : '';
        }
        // get the ngg Panoramic plugin options
        if(!isset($nggpano_options))
            $nggpano_options = get_option('nggpano_options');

        $return_array = array();

        //Krpano Viewer Folder
        $return_array['krpanoFolder']       =   trailingslashit($nggpano_options['krpanoFolder']);              //	= plugin_dir_path("nextgen-gallery-panoramics")."/krpano/";
        $return_array['krpanoFolderURL']    = trailingslashit(site_url()) . $return_array['krpanoFolder'];//site_url() . NGGPANO_PLUGIN_DIR . $krpanoFolder; 
        $return_array['krpanoFolderPath']   = ABSPATH . $return_array['krpanoFolder'];
        //Krpano Viewer Plugin Folder
        $return_array['pluginFolder']       =   trailingslashit($nggpano_options['pluginFolder']);              //	= plugin_dir_path("nextgen-gallery-panoramics")."/krpano_plugins/";
        $return_array['pluginFolderURL']    = trailingslashit(site_url()) . $return_array['pluginFolder']; 
        $return_array['pluginFolderPath']   = ABSPATH . $return_array['pluginFolder'];    
        //Krpano Skin Folder
        $return_array['skinFolder']        =   trailingslashit($nggpano_options['skinFolder']);     // append related images //    = plugin_dir_path("nextgen-gallery-panoramics")."/krpano_skins/";
        $return_array['skinFolderURL']      = trailingslashit(site_url()) . $return_array['skinFolder']; 
        $return_array['skinFolderPath']     = ABSPATH . $return_array['skinFolder'];
        //Skin File
        $return_array['defaultSkinFile']    =   $nggpano_options['defaultSkinFile'];     // set default skin for krpano.swf 
        $return_array['viewerTemplate']     = ($gallery_viewertemplate == '') ? $return_array['defaultSkinFile'] : $gallery_viewertemplate ;     //Complete Path to the kmultires config file
        $return_array['viewerTemplateURL']  = $return_array['skinFolderURL'] .$return_array['viewerTemplate']; 
        $return_array['viewerTemplatePath'] = $return_array['skinFolderPath'] .$return_array['viewerTemplate'];



        return $return_array;
    }
    
    /**
    * Get skin xml node
    *
    * @return XML
    */
    function nggpano_getSkinXML($gallery_id = 'several')
    {

        $panoconfig = nggpano_getPanoConfig($gallery_id);
        
        $xml_skin = file_get_contents($panoconfig['viewerTemplatePath']);
        $xml_skin = str_replace('%PLUGINDIR%/', $panoconfig['pluginFolderURL'], $xml_skin);
        $xml_skin = str_replace('%SKINDIR%/',$panoconfig['skinFolderURL'], $xml_skin);

        return $xml_skin;

    }


    /**
     * Reset all galleries with a specific skin file
     * @param string templatefile
     * @return true or ERRROR
     */		
    function nggpano_resetGalleriesTemplate($skinfile) {
            global $wpdb;
            
            $qry = "UPDATE ".$wpdb->prefix."nggpano_gallery SET skin = '".$wpdb->escape($skinfile)."';";//' WHERE id = '".$wpdb->escape($config["id"])."'";
            if($wpdb->query($qry) !== false) {
                    return true;
            }else{
                    return "ERROR: Failed to save";
            }		
    }
    
    /**
     * Recursively delete a directory
     *
     * @param string $dir Directory name
     * @param boolean $deleteRootToo Delete specified top-level directory as well
     */
    function nggpano_unlinkRecursive($dir, $deleteRootToo)
    {
        if(!$dh = @opendir($dir))
        {
            return;
        }
        while (false !== ($obj = readdir($dh)))
        {
            if($obj == '.' || $obj == '..')
            {
                continue;
            }

            if (!@unlink($dir . '/' . $obj))
            {
                nggpano_unlinkRecursive($dir.'/'.$obj, true);
            }
        }

        closedir($dh);

        if ($deleteRootToo)
        {
            @rmdir($dir);
        }

        return;
    } 
    

// ### Code from wordpress plugin import
// read in the skin template files
    function nggpano_get_viewerskinfiles() {
            global $skinfiles;

            if (isset ($skinfiles)) {
                    return $skinfiles;
            }
            
            $nggpano_options = get_option('nggpano_options');
            
            $skinfiles = array ();

            // Files in wp-content/plugins/nextgen-gallery-panoramics/krpano_skins directory
            //$plugin_root = NGGPANOGALLERY_ABSPATH . $nggpano_options['skinFolder'];
            $plugin_root = ABSPATH . $nggpano_options['skinFolder'];
            
            $plugins_dir = @dir($plugin_root);
            if ($plugins_dir) {
                    while (($file = $plugins_dir->read()) !== false) {
                            if (preg_match('|^\.+$|', $file))
                                    continue;
                            if (is_dir($plugin_root.'/'.$file)) {
                                    $plugins_subdir = @ dir($plugin_root.'/'.$file);
                                    if ($plugins_subdir) {
                                            while (($subfile = $plugins_subdir->read()) !== false) {
                                                    if (preg_match('|^\.+$|', $subfile))
                                                            continue;
                                                    if (preg_match('|\.xml$|', $subfile))
                                                            $plugin_files[] = "$file/$subfile";
                                            }
                                    }
                            } else {
                                    if (preg_match('|\.xml$|', $file))
                                            $plugin_files[] = $file;
                            }
                    }
            }

            if ( !$plugins_dir || !$plugin_files )
                    return $skinfiles;

            foreach ( $plugin_files as $plugin_file ) {
                    //if ( !is_readable("$plugin_root/$plugin_file"))
                    //        continue;

                    $plugin_data = nggpano_get_viewerskinfiles_data("$plugin_root/$plugin_file");

                    if ( empty ($plugin_data['Name']) )
                            continue;

                    $skinfiles[plugin_basename($plugin_file)] = $plugin_data;
            }

            uasort($skinfiles, create_function('$a, $b', 'return strnatcasecmp($a["Name"], $b["Name"]);'));

            return $skinfiles;
    }

    
    // parse the Header information
    function nggpano_get_viewerskinfiles_data($plugin_file) {
            $plugin_data = implode('', file($plugin_file));
            preg_match("|Skin Name:(.*)|i", $plugin_data, $plugin_name);
            preg_match("|Description:(.*)|i", $plugin_data, $description);
            preg_match("|Author:(.*)|i", $plugin_data, $author_name);
            if (preg_match("|Version:(.*)|i", $plugin_data, $version))
                    $version = trim($version[1]);
            else
                    $version = '';

            $description = wptexturize(trim($description[1]));

            $name = trim($plugin_name[1]);
            $author = trim($author_name[1]);

            return array ('Name' => $name, 'Description' => $description, 'Author' => $author, 'Version' => $version );
    }

// read in the kmakemultires config files
    function nggpano_get_kmakemultiresfiles() {
            global $configfiles;

            if (isset ($configfiles)) {
                    return $configfiles;
            }
            
            $nggpano_options = get_option('nggpano_options');
            
            $configfiles = array ();

            // Files in wp-content/plugins/nextgen-gallery-panoramics/krpanotools_configs directory
            $plugin_root = ABSPATH . $nggpano_options['kmakemultiresConfigFolder'];
            
            $plugins_dir = @dir($plugin_root);
            if ($plugins_dir) {
                    while (($file = $plugins_dir->read()) !== false) {
                            if (preg_match('|^\.+$|', $file))
                                    continue;
                            if (is_dir($plugin_root.'/'.$file)) {
                                    $plugins_subdir = @ dir($plugin_root.'/'.$file);
                                    if ($plugins_subdir) {
                                            while (($subfile = $plugins_subdir->read()) !== false) {
                                                    if (preg_match('|^\.+$|', $subfile))
                                                            continue;
                                                    if (preg_match('|\.config$|', $subfile))
                                                            $plugin_files[] = "$file/$subfile";
                                            }
                                    }
                            } else {
                                    if (preg_match('|\.config$|', $file))
                                            $plugin_files[] = $file;
                            }
                    }
            }

            if ( !$plugins_dir || !$plugin_files )
                    return $configfiles;

            foreach ( $plugin_files as $plugin_file ) {
                    //if ( !is_readable("$plugin_root/$plugin_file"))
                    //        continue;

                    $plugin_data = nggpano_get_kmakemultiresfiles_data("$plugin_root/$plugin_file");

                    if ( empty ($plugin_data['Name']) )
                            continue;

                    $configfiles[plugin_basename($plugin_file)] = $plugin_data;
            }

            uasort($configfiles, create_function('$a, $b', 'return strnatcasecmp($a["Name"], $b["Name"]);'));

            return $configfiles;
    }

    
    // parse the Header information
    function nggpano_get_kmakemultiresfiles_data($plugin_file) {
            $plugin_data = implode('', file($plugin_file));
            preg_match("|Config Name:(.*)|i", $plugin_data, $plugin_name);
            preg_match("|Description:(.*)|i", $plugin_data, $description);
            preg_match("|Author:(.*)|i", $plugin_data, $author_name);
            if (preg_match("|Version:(.*)|i", $plugin_data, $version))
                    $version = trim($version[1]);
            else
                    $version = '';

            $description = wptexturize(trim($description[1]));

            $name = trim($plugin_name[1]);
            $author = trim($author_name[1]);

            return array ('Name' => $name, 'Description' => $description, 'Author' => $author, 'Version' => $version );
    }
    

    function nggpano_get_exif_gps($pic_id, $assoc = false) {
       
        $meta = new nggpanoEXIF($pic_id);
        $gps_data = $meta->get_Exif_GPS($assoc);
        return $gps_data;

    }  
    
// read in the colorbox config files
    function nggpano_get_colorboxcssfile() {
            global $cssfiles;

            if (isset ($cssfiles)) {
                    return $cssfiles;
            }
            
            $nggpano_options = get_option('nggpano_options');
            
            $cssfiles = array ();

            // Files in wp-content/plugins/nextgen-gallery-panoramics/krpanotools_configs directory
            $plugin_root = ABSPATH . "wp-content/plugins/".NGGPANOFOLDER . "/colorbox/css/";
            
            $plugins_dir = @dir($plugin_root);
            if ($plugins_dir) {
                    while (($file = $plugins_dir->read()) !== false) {
                            if (preg_match('|^\.+$|', $file))
                                    continue;
                            if (is_dir($plugin_root.'/'.$file)) {
                                    $plugins_subdir = @ dir($plugin_root.'/'.$file);
                                    if ($plugins_subdir) {
                                            while (($subfile = $plugins_subdir->read()) !== false) {
                                                    if (preg_match('|^\.+$|', $subfile))
                                                            continue;
                                                    if (preg_match('|\.css$|', $subfile))
                                                            $plugin_files[] = "$file/$subfile";
                                            }
                                    }
                            } else {
                                    if (preg_match('|\.css$|', $file))
                                            $plugin_files[] = $file;
                            }
                    }
            }

            if ( !$plugins_dir || !$plugin_files )
                    return $cssfiles;

            foreach ( $plugin_files as $plugin_file ) {
                    $cssfiles[plugin_basename($plugin_file)] = $plugin_file;
            }
            asort($cssfiles);
            //uasort($cssfiles, create_function('$a, $b', 'return strnatcasecmp($a["Name"], $b["Name"]);'));

            return $cssfiles;
    }
    
//    /**
//     * Gets the voting options for a specific gallery
//     * @param int $gid The NextGEN Gallery ID
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return object of options on success, empty array on failure
//     */
//    function nggv_getVotingOptions($gid) {
//            global $wpdb;
//            $opts = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."nggv_settings WHERE gid = '".$wpdb->escape($gid)."'");
//            return $opts ? $opts : array();
//    }
//
//    /**
//     * Gets the voting options for a specific image
//     * @param int $pid The image ID
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return object of options on success, empty array on failure
//     */		
//    function nggv_getImageVotingOptions($pid) {
//            global $wpdb;
//            $opts = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."nggv_settings WHERE pid = '".$wpdb->escape($pid)."'");
//            return is_numeric($pid) && $opts->pid == $pid ? $opts : array();			
//    }
//
//    /**
//     Checks if the current user can vote on a gallery
//     * @param int $gid The NextGEN Gallery ID
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return true if the user can vote, string of reason if the user can not vote
//     */
//    function nggv_canVote($gid) {
//            $options = nggv_getVotingOptions($gid);
//
//            if(!$options) {
//                    return false;
//            }
//
//            if(!$options->enable) {
//                    return "VOTING NOT ENABLED";
//            }
//
//            if($options->force_login) {
//                    global $current_user;
//                    get_currentuserinfo();
//
//                    if(!$current_user->ID) {
//                            return "NOT LOGGED IN";
//                    }
//            }
//
//            if($options->force_once) {
//                    if($options->force_login) { //force login, so check userid has voted already
//                            if(nggv_userHasVoted($gid, $current_user->ID)) {
//                                    return "USER HAS VOTED";
//                            }
//                    }else{ //no forced login, so just check the IP for a vote
//                            if(nggv_ipHasVoted($gid)) {
//                                    return "IP HAS VOTED";
//                            }
//                    }
//            }
//
//            return true;
//    }
//
//    /**
//     Checks if the current user can vote on an image (current almost identical to nggv_canVote(), but is seperate function for scalability)
//     * @param int $pid The image ID
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return true if the user can vote, string of reason if the user can not vote
//     */
//    function nggv_canVoteImage($pid) {
//            $options = nggv_getImageVotingOptions($pid);
//
//            if(!$options) {
//                    return false;
//            }
//
//            if(!$options->enable) {
//                    return "VOTING NOT ENABLED";
//            }
//
//            if($options->force_login) {
//                    global $current_user;
//                    get_currentuserinfo();
//
//                    if(!$current_user->ID) {
//                            return "NOT LOGGED IN";
//                    }
//            }
//
//            if($options->force_once == 1) {
//                    if($options->force_login) { //force login, so check userid has voted already
//                            if(nggv_userHasVotedImage($pid, $current_user->ID)) {
//                                    return "USER HAS VOTED";
//                            }
//                    }else{ //no forced login, so just check the IP for a vote
//                            if(nggv_ipHasVotedImage($pid)) {
//                                    return "IP HAS VOTED";
//                            }
//                    }
//            }else if($options->force_once == 2) {
//                    if($options->force_login) { //force login, so check userid has voted already
//                            if(nggv_userHasVotedOnGalleryImage($pid, $current_user->ID)) {
//                                    return "USER HAS VOTED";
//                            }
//                    }else{ //no forced login, so just check the IP for a vote
//                            if(nggv_ipHasVotedOnGalleryImage($pid)) {
//                                    return "IP HAS VOTED";
//                            }
//                    }
//            }
//
//            return true;
//    }
//
//    /**
//     * Save the vote.  Checks nggv_canVote() to be sure you aren't being sneaky
//     * @param array $config The array that makes up a valid vote
//     *  int config[gid] : The NextGEN Gallery ID
//     *  int config[vote] : The cast vote, must be between 0 and 100 (inclusive)
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return true on success, false on DB failure, string on nggv_canVote() not returning true
//     */
//    function nggv_saveVote($config) {
//            if(is_numeric($config["gid"]) && $config["vote"] >= 0 && $config["vote"] <= 100) {
//                    if(($msg = nggv_canVote($config["gid"])) === true) {
//                            global $wpdb, $current_user;
//                            get_currentuserinfo();
//                            $ip = getUserIp();
//                            if($wpdb->query("INSERT INTO ".$wpdb->prefix."nggv_votes (id, pid, gid, vote, user_id, ip, proxy, dateadded) VALUES (null, 0, '".$wpdb->escape($config["gid"])."', '".$wpdb->escape($config["vote"])."', '".$current_user->ID."', '".$ip["ip"]."', '".$ip["proxy"]."', '".date("Y-m-d H:i:s", time())."')")) {
//                                    return true;
//                            }else{
//                                    return false;
//                            }
//                    }else{
//                            return $msg;
//                    }
//            }
//    }
//
//    /**
//            * Save the vote.  Checks nggv_canVoteImage() to be sure you aren't being sneaky
//            * @param array $config The array that makes up a valid vote
//            *  int config[pid] : The image id
//            *  int config[vote] : The cast vote, must be between 0 and 100 (inclusive)
//            * @author Shaun <shaunalberts@gmail.com>
//            * @return true on success, false on DB failure, string on nggv_canVoteImage() not returning true
//            */
//    function nggv_saveVoteImage($config) {
//            if(is_numeric($config["pid"]) && $config["vote"] >= 0 && $config["vote"] <= 100) {
//                    if(($msg = nggv_canVoteImage($config["pid"])) === true) {
//                            global $wpdb, $current_user;
//                            get_currentuserinfo();
//                            $ip = getUserIp();
//                            if($wpdb->query("INSERT INTO ".$wpdb->prefix."nggv_votes (id, gid, pid, vote, user_id, ip, proxy, dateadded) VALUES (null, 0, '".$wpdb->escape($config["pid"])."', '".$wpdb->escape($config["vote"])."', '".$current_user->ID."', '".$ip["ip"]."', '".$ip["proxy"]."', '".date("Y-m-d H:i:s", time())."')")) {
//                                    return true;
//                            }else{
//                                    return false;
//                            }
//                    }else{
//                            return $msg;
//                    }
//            }
//    }
//
//    //gets the users actual IP even if they are behind a proxy (if the proxy is nice enough to let us know their actual IP of course)
//    /**
//     * Get a users IP.  If the users proxy allows, we get their actual IP, not just the proxies
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return array("ip"=>string The IP found[might be proxy IP, sorry], "proxy"=>string The proxy IP if the proxy was nice enough to tell us it)
//     */
//    function getUserIp() {
//            if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
//                    if ($_SERVER["HTTP_CLIENT_IP"]) {
//                            $proxy = $_SERVER["HTTP_CLIENT_IP"];
//                    } else {
//                            $proxy = $_SERVER["REMOTE_ADDR"];
//                    }
//                    $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
//            } else {
//                    if ($_SERVER["HTTP_CLIENT_IP"]) {
//                            $ip = $_SERVER["HTTP_CLIENT_IP"];
//                    } else {
//                            $ip = $_SERVER["REMOTE_ADDR"];
//                    }
//            }
//
//            //if comma list of IPs, get the LAST one
//            if($proxy) {
//                    $proxy = explode(",", $proxy);
//                    $proxy = trim(array_pop($proxy));
//            }
//            if($ip) {
//                    $ip = explode(",", $ip);
//                    $ip = trim(array_pop($ip));
//            }
//
//            return array("ip"=>$ip, "proxy"=>$proxy);
//    }
//
//    /**
//     * Check if a user has voted on a gallery before 
//     * @param int $gid The NextGEN Gallery ID
//     * @param int $userid The users id to check
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return object of all the votes the user has cast for this gallery, or blank array
//     */
//    function nggv_userHasVoted($gid, $userid) {
//            global $wpdb;
//
//            if($votes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggv_votes WHERE gid = '".$wpdb->escape($gid)."' AND user_id = '".$wpdb->escape($userid)."'")) {
//                    return $votes;
//            }else{
//                    return array();
//            }
//    }
//
//    /**
//     * Check if an IP has voted on a gallery before 
//     * @param int $gid The NextGEN Gallery ID
//     * @param string The IP to check.  If not passed, the current users IP will be assumed
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return object of all the votes this IP has cast for this gallery, or blank array
//     */
//    function nggv_ipHasVoted($gid, $ip=null) {
//            global $wpdb;
//            if(!$ip) {
//                    $tmp = getUserIp();
//                    $ip = $tmp["ip"];
//            }
//
//            if($votes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggv_votes WHERE gid = '".$wpdb->escape($gid)."' AND ip = '".$wpdb->escape($ip)."'")) {
//                    return $votes;
//            }else{
//                    return array();
//            }
//
//    }
//
//    /**
//            * Check if a user has voted on an image before 
//            * @param int $pid The image ID to check
//            * @param int $userid The users id to check
//            * @author Shaun <shaunalberts@gmail.com>
//            * @return object of all the votes the user has cast for this image, or blank array
//            */
//    function nggv_userHasVotedImage($pid, $userid) {
//            global $wpdb;
//
//            if($votes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' AND user_id = '".$wpdb->escape($userid)."'")) {
//                    return $votes;
//            }else{
//                    return array();
//            }
//    }
//
//    /**
//            * Check if a user has voted on any image in this $pid gallery before
//            * @param int $pid The image ID to check
//            * @param int $userid The users id to check
//            * @author Shaun <shaunalberts@gmail.com>
//            * @return bool true if the user has voted on any image in the same gallery as this $pid, false of not
//            */
//    function nggv_userHasVotedOnGalleryImage($pid, $userid) {
//            global $wpdb;
//
//            if(!$image = nggdb::find_image($pid)) {
//                    return true; //huh, cant find image, so dont let the person vote to be safe (this should never happen)
//            }
//
//            $picturelist = nggdb::get_gallery($image->gid);
//            foreach ((array)$picturelist as $key=>$val) {
//                    if($v = nggv_userHasVotedImage($val->pid, $userid)) {
//                            return true; //aha! there was a vote somewhere in this gallery.
//                    }
//            }
//
//            return false; //cant find any votes, so seems safe
//
//    }
//
//    /**
//     * Check if an IP has voted on an image before 
//     * @param int $pid The image ID
//     * @param string The IP to check.  If not passed, the current users IP will be assumed
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return object of all the votes this IP has cast for this image, or blank array
//     */
//    function nggv_ipHasVotedImage($pid, $ip=null) {
//            global $wpdb;
//            if(!$ip) {
//                    $tmp = getUserIp();
//                    $ip = $tmp["ip"];
//            }
//
//            if($votes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' AND ip = '".$wpdb->escape($ip)."'")) {
//                    return $votes;
//            }else{
//                    return array();
//            }
//
//    }
//
//    /**
//     * Check if an IP has voted on any images in the gallery of the $pid passed
//     * @param int $pid The image ID
//     * @param string The IP to check.  If not passed, the current users IP will be assumed
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return bool true if the $ip has voted on any image in the same gallery as this $pid, false of not
//     */
//    function nggv_ipHasVotedOnGalleryImage($pid, $ip=null) {
//            global $wpdb;
//
//            if(!$image = nggdb::find_image($pid)) {
//                    return true; //huh, cant find image, so dont let the person vote to be safe (this should never happen)
//            }
//
//            $picturelist = nggdb::get_gallery($image->gid);
//            foreach ((array)$picturelist as $key=>$val) {
//                    if($v = nggv_ipHasVotedImage($val->pid, $ip)) {
//                            return true; //aha! there was a vote somewhere in this gallery.
//                    }
//            }
//
//            return false; //cant find any votes, so seems safe
//    }
//
//    /**
//     * Get the voting results of a gallery
//     * @param int $gid The NextGEN Gallery ID
//     * @param array $type The type of results to return (can limti number of queries if you only need the avg for example)
//     *  bool type[avg] : Get average vote
//     *  bool type[list] : Get all the votes for the gallery
//     *  bool type[number] : Get the number of votes for the gallery
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return array("avg"=>double average for gallery, "list"=>array of objects of all votes of the gallery, "number"=>integer the number of votes for the gallery)
//     */
//    function nggv_getVotingResults($gid, $type=array("avg"=>true, "list"=>true, "number"=>true, "likes"=>true, "dislikes"=>true)) {
//            if(is_numeric($gid)) {
//                    global $wpdb;
//
//                    if($type["avg"]) {
//                            $avg = $wpdb->get_row("SELECT SUM(vote) / COUNT(vote) AS avg FROM ".$wpdb->prefix."nggv_votes WHERE gid = '".$wpdb->escape($gid)."' GROUP BY gid");
//                    }
//                    if($type["list"]) {
//                            $list = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggv_votes WHERE gid = '".$wpdb->escape($gid)."' ORDER BY dateadded DESC");
//                    }
//                    if($type["num"]) {
//                            $num = $wpdb->get_row("SELECT COUNT(vote) AS num FROM ".$wpdb->prefix."nggv_votes WHERE gid = '".$wpdb->escape($gid)."' GROUP BY gid");
//                    }
//                    if($type["likes"]) {
//                            $likes = $wpdb->get_row("SELECT COUNT(vote) AS num FROM ".$wpdb->prefix."nggv_votes WHERE gid = '".$wpdb->escape($gid)."' AND vote = 100 GROUP BY gid");
//                    }
//                    if($type["dislikes"]) {
//                            $dislikes = $wpdb->get_row("SELECT COUNT(vote) AS num FROM ".$wpdb->prefix."nggv_votes WHERE gid = '".$wpdb->escape($gid)."' AND vote = 0 GROUP BY gid");
//                    }
//
//                    return array("avg"=>$avg->avg, "list"=>$list, "number"=>$num->num, "likes"=>($likes->num ? $likes->num : 0), "dislikes"=>($dislikes->num ? $dislikes->num : 0));
//            }else{
//                    return array();
//            }
//    }
//
//    /**
//     * Get the voting results of an image
//     * @param int $pid The image ID
//     * @param array $type The type of results to return (can limit number of queries if you only need the avg for example)
//     *  bool type[avg] : Get average vote
//     *  bool type[list] : Get all the votes for the gallery
//     *  bool type[number] : Get the number of votes for the gallery
//     * @author Shaun <shaunalberts@gmail.com>
//     * @return array("avg"=>double average for image, "list"=>array of objects of all votes of the image, "number"=>integer the number of votes for the image)
//     */
//    function nggv_getImageVotingResults($pid, $type=array("avg"=>true, "list"=>true, "number"=>true, "likes"=>true, "dislikes"=>true)) {
//            if(is_numeric($pid)) {
//                    global $wpdb;
//
//                    if($type["avg"]) {
//                            $avg = $wpdb->get_row("SELECT SUM(vote) / COUNT(vote) AS avg FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' GROUP BY pid");
//                    }
//                    if($type["list"]) {
//                            $list = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' ORDER BY dateadded DESC");
//                    }
//                    if($type["num"]) {
//                            $num = $wpdb->get_row("SELECT COUNT(vote) AS num FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' GROUP BY pid");
//                    }
//                    if($type["likes"]) {
//                            $likes = $wpdb->get_row("SELECT COUNT(vote) AS num FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' AND vote = 100 GROUP BY pid");
//                    }
//                    if($type["dislikes"]) {
//                            $dislikes = $wpdb->get_row("SELECT COUNT(vote) AS num FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' AND vote = 0 GROUP BY pid");
//                    }
//
//                    return array("avg"=>$avg->avg, "list"=>$list, "number"=>$num->num, "likes"=>($likes->num ? $likes->num : 0), "dislikes"=>($dislikes->num ? $dislikes->num : 0));
//            }else{
//                    return array();
//            }
//    }
?>
