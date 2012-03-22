<?php

/**
 * add_nggpano_button
 * 
 * @package NGG Panoramics
 * @title TinyMCE V3 Button Integration (for WP2.5 and higher)
 * @author Geoffroy Deleury
 * @access public
 */
class add_nggpano_button {
	
	var $pluginname = 'NGGPano';
	var $path = '';
	var $internalVersion = 200;
	
	/**
	 * add_nggpano_button::add_nggpano_button()
	 * the constructor
	 * 
	 * @return void
	 */
	function add_nggpano_button()  {
		
		// Set path to editor_plugin.js
		$this->path = NGGPANOGALLERY_URLPATH . 'admin/tinymce/';		
		
		// Modify the version when tinyMCE plugins are changed.
		add_filter('tiny_mce_version', array (&$this, 'change_tinymce_version') );

		// init process for button control
		add_action('init', array (&$this, 'addbuttons') );
	}

	/**
	 * add_nggpano_button::addbuttons()
	 * 
	 * @return void
	 */
	function addbuttons() {
	
		// Don't bother doing this stuff if the current user lacks permissions
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) 
			return;

		// Check for NGGPano capability
		if ( !current_user_can('NGG Panoramics Use TinyMCE') ) 
			return;
		
		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
		 
			// add the button for wp2.5 in a new way
			add_filter("mce_external_plugins", array (&$this, 'add_tinymce_plugin' ), 5);
			add_filter('mce_buttons', array (&$this, 'register_button' ), 5);
		}
	}
	
	/**
	 * add_nggpano_button::register_button()
	 * used to insert button in wordpress 2.5x editor
	 * 
	 * @return $buttons
	 */
	function register_button($buttons) {
	
		array_push($buttons, 'separator', $this->pluginname );
	
		return $buttons;
	}
	
	/**
	 * add_nggpano_button::add_tinymce_plugin()
	 * Load the TinyMCE plugin : editor_plugin.js
	 * 
	 * @return $plugin_array
	 */
	function add_tinymce_plugin($plugin_array) {    
	
		$plugin_array[$this->pluginname] =  $this->path . 'editor_plugin.js';
		
		return $plugin_array;
	}
	
	/**
	 * add_nggpano_button::change_tinymce_version()
	 * A different version will rebuild the cache
	 * 
	 * @return $versio
	 */
	function change_tinymce_version($version) {
			$version = $version + $this->internalVersion;
		return $version;
	}
	
}

// Call it now
$tinymce_button = new add_nggpano_button ();

?>