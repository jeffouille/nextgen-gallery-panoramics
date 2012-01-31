<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * creates all tables for the panoramic plugin needed on activation 
 * called during register_activation hook
 * 
 * @access internal
 * @return void
 */
function nggpano_install () {
	
   	global $wpdb , $wp_roles, $wp_version;
   	
	// Check for capability
	if ( !current_user_can('activate_plugins') ) 
		return;
	
	// Set the capabilities for the administrator
	$role = get_role('administrator');
	// We need this role, no other chance
	if ( empty($role) ) {
		update_option( "nggpano_init_check", __('Sorry, NextGEN Gallery Panoramics Plugin works only with a role called administrator',"nggpano") );
		return;
	}
	
	$role->add_cap('NGG Panoramics overview');
//	$role->add_cap('NextGEN Use TinyMCE');
//	$role->add_cap('NextGEN Upload images');
	$role->add_cap('NGG Panoramics Manage gallery');
//	$role->add_cap('NextGEN Manage tags');
//	$role->add_cap('NextGEN Manage others gallery');
//	$role->add_cap('NextGEN Edit album');
	$role->add_cap('NGG Panoramics Change style');
//	$role->add_cap('NextGEN Change options');
	
	// upgrade function changed in WordPress 2.3	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	// add charset & collate like wp core
	$charset_collate = '';

	if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
	}
		
   	$nggpano_gallery					= $wpdb->prefix . 'nggpano_gallery';
	$nggpano_panoramic                                      = $wpdb->prefix . 'nggpano_panoramic';

    // could be case senstive : http://dev.mysql.com/doc/refman/5.1/en/identifier-case-sensitivity.html

	if( !$wpdb->get_var( "SHOW TABLES LIKE '$nggpano_gallery'" )) {
      
		$sql = "CREATE TABLE " . $nggpano_gallery . " (
		id BIGINT(19) NOT NULL AUTO_INCREMENT,
		gid BIGINT NOT NULL DEFAULT 0,
		skin VARCHAR(255) ,
                gps_region VARCHAR(255) ,
                path MEDIUMTEXT NULL ,
		PRIMARY KEY id (id)
		) $charset_collate;";
	
      dbDelta($sql);
   }
        if( !$wpdb->get_var( "SHOW TABLES LIKE '$nggpano_panoramic'" ) ) {
      
		$sql = "CREATE TABLE " . $nggpano_panoramic . " (
                id BIGINT(19) NOT NULL AUTO_INCREMENT,
                pid BIGINT NOT NULL,
                gid BIGINT NOT NULL,
		gps_lat FLOAT(18,14) NULL,
                gps_lng FLOAT(18,14) NULL,
                gps_alt BIGINT NULL,
		pano_directory VARCHAR(255),
                xml_configuration LONGTEXT,
		is_partial TINYINT NULL DEFAULT '0' ,
                hfov DECIMAL(8,2) NULL,
                vfov DECIMAL(8,2) NULL,
                voffset DECIMAL(8,2) NULL,
		PRIMARY KEY id (id),
		KEY pid (pid)
		) $charset_collate;";
	
      dbDelta($sql);
    }



	// check one table again, to be sure
	if( !$wpdb->get_var( "SHOW TABLES LIKE '$nggpano_panoramic'" ) ) {
		update_option( "nggpano_init_check", __('NextGEN Gallery Panoramics : Tables could not created, please check your database settings',"nggpano") );
		return;
	}
	//TODO Remove this delete line after test
        delete_option( 'nggpano_options' );
	$options = get_option('nggpano_options');
	// set the default settings, if we didn't upgrade
	if ( empty( $options ) )	
 		nggpano_default_options();
 	
	// if all is passed , save the DBVERSION
	//add_option("nggpano_db_version", NGG_DBVERSION);

}

/**
 * Setup the default option array for the gallery panoramics
 * 
 * @access internal
 * @since version 0.33 
 * @return void
 */
function nggpano_default_options() {
	
	global $blog_id, $nggpano;

	//Krpano Tool
        $nggpano_options['toolConfigFile']              = 'default_kmakemultires.config';  	// set default config file for krapnotool
	$nggpano_options['krpanoToolsTempFolder']	= get_temp_dir()."/temp/";		// default temp path to for krpanotool works
        $nggpano_options['kmakemultiresFolder']         = "wp-content/plugins/".NGGPANOFOLDER."/krpanotools/";
        $nggpano_options['kmakemultiresConfigFolder']	= "wp-content/plugins/".NGGPANOFOLDER."/krpanotools_configs/";
        $nggpano_options['kmakemultiresXMLConfig']	= "wp-content/plugins/".NGGPANOFOLDER."/krpanotools_xml_config/default.xml";
        
        //Lightbox Script
        $nggpano_options['lightboxEffect']              = 'thickbox';
        $nggpano_options['colorboxCSSfile']                 = 'colorbox-1.css';
        
	
	//Krpano Viewer
	$nggpano_options['defaultSkinFile']	= 'default_template_krpano.xml';		// append related images
        $nggpano_options['krpanoFolder']	= "wp-content/plugins/".NGGPANOFOLDER . "/krpano/";
        $nggpano_options['skinFolder']          = "wp-content/plugins/".NGGPANOFOLDER . "/krpano_skins/";
        $nggpano_options['pluginFolder']	= "wp-content/plugins/".NGGPANOFOLDER . "/krpano_plugins/";
        
        
        // Directory and prefix for pano creation
	$nggpano_options['panoPrefix']          =	'pano_';	// FolderPrefix to the panos
	$nggpano_options['panoFolder']          =	'/panos/';	// Foldername to the panos // subdirectory to store panofile
        
        // Size for preview
	$nggpano_options['widthPreview']          =	'2000';	// max width size for image preview
	$nggpano_options['heightPreview']         =	'1000';	// max height size for image preview

	// CSS Style
	$nggpano_options['activateCSS']			= true;							// activate the CSS file
	$nggpano_options['CSSfile']			= 'nggpano.css';  			// set default css filename
        
	// special overrides for WPMU	
//	if (is_multisite()) {
//		// get the site options
//		$nggpano_wpmu_options = get_site_option('nggpano_options');
//		
//		// get the default value during first installation
//		if (!is_array($ngg_wpmu_options)) {
//			update_site_option('nggpano_options', $nggpano_wpmu_options);
//		}
//		
//		$ngg_options['gallerypath']  		= str_replace("%BLOG_ID%", $blog_id , $ngg_wpmu_options['gallerypath']);
//		$ngg_options['CSSfile']				= $ngg_wpmu_options['wpmuCSSfile'];
//	} 
	
	update_option('nggpano_options', $nggpano_options);

}

/**
 * Deregister a capability from all classic roles
 * 
 * @access internal
 * @param string $capability name of the capability which should be deregister
 * @return void
 */
function nggpano_remove_capability($capability){
	// this function remove the $capability only from the classic roles
	$check_order = array("subscriber", "contributor", "author", "editor", "administrator");

	foreach ($check_order as $role) {

		$role = get_role($role);
		$role->remove_cap($capability) ;
	}

}

/**
 * Uninstall all settings and tables
 * Called via Setup and register_unstall hook
 * 
 * @access internal
 * @return void
 */
function nggpano_uninstall() {
	global $wpdb;
	
	// first remove all tables
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}nggpano_gallery");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}nggpano_panoramic");
	
	// then remove all options
	delete_option( 'nggpano_options' );
	delete_option( 'nggpano_db_version' );
	delete_option( 'nggpano_update_exists' );
	delete_option( 'nggpano_next_update' );

	// now remove the capability
	ngg_remove_capability("NGG Panoramics overview");
//	ngg_remove_capability("NextGEN Use TinyMCE");
//	ngg_remove_capability("NextGEN Upload images");
	ngg_remove_capability("NGG Panoramics Manage gallery");
//	ngg_remove_capability("NextGEN Edit album");
//	ngg_remove_capability("NextGEN Change style");
//	ngg_remove_capability("NextGEN Change options");
}

?>