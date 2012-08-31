<?php
if ( !class_exists('nggpanoPano') ) :
/**
* Pano PHP class for the WordPress plugin NextGEN Gallery Panoramic
* 
* @author 		Geoffroy Deleury
* @copyright 	Copyright 2007-2008
 * 
 * 
 *  * This class manage the Panoramic Object
 * 
 * @property decimal $hfov
 * @property decimal $vfov
 * @property decimal $voffset
 * @property boolean $is_partial
 * @property text $xml_configuration
 * @property Image $Image
*/
class nggpanoPano{
        /**** My Public variables ****/
        
        var $pid                =   '';     //image pid;
        var $gid                =   '';     //Gallery gid;
        
        var $hfov               =   '';     // Horizontal FOV   - hfov set the horizontal field of view of the image
        var $vfov               =   '';     // Vertical FOV     - the vfov value will/can be calculated automatically
        var $voffset            =   '';     // Vertical Offset  -  voffset - vertical shift away from the horizon (+/- degrees)
        var $is_partial         =   '0';     // Check pano is partial - force assuming that the input is a partial sphere   (hfov setting needed!)
        var $xml_configuration  =   '';     // XML Configuration for the pano viewer
        
	// Directory and prefix for pano creation
	var $panoPrefix	=	'pano_';	// FolderPrefix to the panos
	var $panoSubFolder	=	'/panos/';	// Foldername to the panos
        
        var $panoFolder         = '';           // Relative Path where panogeneration will be store
        var $panoFolderURL      = '';           // URL Path where panogeneration will be store
        var $panoFolderPath     = '';           // Server Path where panogeneration will be store
	
	/**** Public variables ****/	
	var $errmsg			=	'';             // Error message to display, if any
	var $error			=	FALSE; 		// Error state
	
        var $xmlKrpano  		=	'';		// Relative Path to the xml
        var $xmlKrpanoURL		=	'';		// URL Path to the xml
        var $xmlKrpanoPath		=	'';		// Server Path to the xml
        
        
	var $thumbURL                   =	'';			// URL Path to the thumbnail (with default dimension)
	var $thumbPath                  =	'';			// Server Path to the thumbnail
        
        var $ImageCustomURL             =       '';                     //URL Path to the thumbnail (with specifix dimension)

	var $galleryFolder		=	'';			// Gallery Folder
        var $imageInputPath             =       '';                     // Original Image Path
        
        
        //Viewer Skin Folder
        var $skinFolder         =       '';
        var $skinFolderURL         =       '';
        var $skinFolderPath         =       '';
        //Viewer Templates / Skin
        var $viewerTemplate     =       '';     //the viewer template
        var $viewerTemplateURL  =       '';     //Complete Path to the viewer template (URL)
        var $viewerTemplatePath =       '';     //Complete Path to the viewer template (ABS PATH)`
        //Viewer Folder
        var $krpanoFolder       =       '';     //Folder to krpano.swf
        var $krpanoFolderURL    =       '';     //
        var $krpanoFolderPath   =       '';
        
        //viewer name (krpano.swf)
        var $krpanoSWF          =       'krpano.swf';
        
        //Plugin Folder
        var $pluginFolder       =       '';     //Folder to all swf plugins
        var $pluginFolderURL    =       '';
        var $pluginFolderPath   =       '';
        
        //KrpanoTools
        //TOOLS
        //Path to kmakemultires
        var $kmakemultiresFolder      =       '';     //Complete Path to the kmultires tool
        var $kmakemultiresFolderPath  =       '';     //Complete Path to the kmultires tool (ABS PATH)
        //Tools name name (kmakemultires)
        var $kmakemultiresFile        =       'kmakemultires';
        //
        //
        //Path to kmakemultires Config Folder
        var $kmakemultiresConfigFolder      =       '';     //Complete Path to the kmultires tool config files
        var $kmakemultiresConfigFolderPath  =       '';     //Complete Path to the kmultires tool config files (ABS PATH)
        //Path to kmakemultires Config Folder
        var $kmakemultiresXMLConfig      =       '';     //Complete Path to the kmultires tool xml config files (default.xml)
        var $kmakemultiresXMLConfigPath  =       '';     //Complete Path to the kmultires tool xml config files (default.xml) (ABS PATH)
        //Path to kmakemultires Config Files
        var $toolConfigFile         =       '';     //the kmultires config file
        var $toolConfigFilePath     =       '';     //Complete Path to the kmultires config file (ABS PATH)
        //Temp Folder
        var $krpanoToolsTempFolder    = '';
        
        //title and description from image
        var $title                  = "";
        var $description            = "";

       
//	var $href			=	'';			// A href link code
	
//	// TODO: remove thumbPrefix and thumbFolder (constants)
//	var $thumbPrefix	=	'thumbs_';	// FolderPrefix to the thumbnail
//	var $thumbFolder	=	'/thumbs/';	// Foldername to the thumbnail
	
//	/**** Image Data ****/
//	var $galleryid		=	0;			// Gallery ID
//	var $pid			=	0;			// Image ID	
//	var $filename		=	'';			// Image filename
//	var $description	=	'';			// Image description	
//	var $alttext		=	'';			// Image alttext	
//	var $imagedate		=	'';			// Image date/time	
//	var $exclude		=	'';			// Image exclude
//	var $thumbcode		=	'';			// Image effect code
//
//	/**** Gallery Data ****/
//	var $name			=	'';			// Gallery name
	
//	var $title			=	'';			// Gallery title
//	var $pageid			=	0;			// Gallery page ID
//	var $previewpic		=	0;			// Gallery preview pic		
//
//	var $permalink		=	'';
//	var $tags			=   '';

        
    /**
     * Constructor
     * 
     * @return void
     */
        
        
    function nggpanoPano($pid, $gid = "", $hfov = "", $vfov = "", $voffset = "") {
        
        global $ngg, $nggpano_options;
        
        //Get default options of the plugin
        if(!$nggpano_options)
            $nggpano_options = get_option('nggpano_options');
        
    	//initialize variables
        $this->pid                  = $pid;
        $this->gid                  = $gid;
        
        $this->errmsg               = '';
        $this->error                = false;
        $this->hfov                 = $hfov;
        $this->vfov                 = $vfov;
        $this->voffset              = $voffset;
        
        
        $Image      = nggdb::find_image( $this->pid );
        
        //find gallerypath by Image
        if($Image) {
            // Input Image
            $this->galleryFolder                 = $Image->path;
            $this->imageInputPath                = $Image->imagePath;
            
            $this->title = html_entity_decode( stripslashes(nggPanoramic::i18n($Image->alttext, 'pano_' . $Image->pid . '_alttext')) );
            $this->description = html_entity_decode( stripslashes(nggPanoramic::i18n($Image->description, 'pano_' . $Image->pid . '_description')) );
            
            $this->thumbURL = $Image->thumbURL;
            
            $this->ImageCustomURL = trailingslashit( home_url() ) . 'index.php?callback=image&amp;pid='.$this->pid;	

            //Gallery id
            $this->gid  = $Image->galleryid;
            
            $filename = $this->imageInputPath;
            //check to see if file exists
            if(!file_exists($filename)) {
                $this->errmsg = 'File not found';
                $this->error = true;
            }
            //check to see if file is readable
            elseif(!is_readable($filename)) {
                $this->errmsg = 'File is not readable';
                $this->error = true;
            }
        } else {
            //find gallerypath by Gallery
            $Gallery    = nggdb::find_gallery($this->gid);
            $this->galleryFolder = $Gallery->path;
            //$this->imageInputPath                   = $Gallery->path;
        }
                
        /*
         * init panoFolder for generation
         */
        // Relative Path where panogeneration will be store
        $this->panoFolder         = $this->galleryFolder . $this->panoSubFolder . $this->panoPrefix . $this->pid;
        $this->setpanoFolderPath();

        
        //Relative Path to xml
        $this->xmlKrpano	= $this->panoFolder . "/" . $this->panoPrefix . $this->pid.".xml";
        //URL path to xml
        $this->xmlKrpanoURL	= site_url() . '/' . $this->xmlKrpano;
        //Absolute path to xml path
        $this->xmlKrpanoPath	= NGGPANOWINABSPATH . $this->xmlKrpano;
        
        
        //Check configuration
        $this->loadConfig();
 
//        if($this->error == false) {
//            //Throw an error if a file doesn't exist
//            //VIEWER
//            //check to see if krpano viewer file exists
//            if(!file_exists(trailingslashit($this->krpanoFolderPath) . $this->krpanoSWF)) {
//                $this->errmsg = $this->krpanoSWF .' file not found in ' . $this->krpanoFolder;
//                $this->error = true;
//            }
//            //check to see if Skin file exists
//            if(!file_exists($this->viewerTemplatePath)) {
//                $this->errmsg = 'Template file '. $this->viewerTemplate .' not found';
//                $this->error = true;
//            }
//            //TOOLS
//            //check to see if kmakemultires
//            elseif(!file_exists(trailingslashit($this->kmakemultiresFolderPath) . $this->kmakemultiresFile)) {
//                $this->errmsg = 'Krpanotool - ' . $this->kmakemultiresFile .' file not found in ' . $this->kmakemultiresFolder;
//                $this->error = true;
//            }
//            //check to see if file is executable
//            elseif(!is_executable (trailingslashit($this->kmakemultiresFolderPath) . $this->kmakemultiresFile)) {
//                $this->errmsg = 'Krpanotool - ' . $this->kmakemultiresFile .' not executable';
//                $this->error = true;
//            }
//            //check to see if krpanotool config file exists
//            elseif(!file_exists($this->toolConfigFilePath)) {
//                $this->errmsg = 'Krpanotool - Config File '. $this->toolConfigFile . ' not found';
//                $this->error = true;
//            }
//            //check to see if krpanotool XML config file exists
//            elseif(!file_exists($this->kmakemultiresXMLConfigPath)) {
//                $this->errmsg = 'Krpanotool - XML Config File '. $this->kmakemultiresXMLConfig . ' not found';
//                $this->error = true;
//            }
//            
//        }

        //return null if an error is detected
        if($this->error == true) {
            return;
        }
    }
    
    protected function setpanoFolderPath() {
        // URL Path where panogeneration will be store
        $this->panoFolderURL      = site_url() . '/' . $this->panoFolder;
        // Absolute Path where panogeneration will be store
        $this->panoFolderPath     = NGGPANOWINABSPATH . $this->panoFolder; 
    }

    

  /**
   * Create the panorama (the tiles creation with krpano)
   *
   */
    public function createTiles($removeXML = true)
    {

        //Check hfov
        if($this->error == false) {
            //Load configuration for pano creation and viewer (default options or gallery configuration)
            if(!(isset($this->hfov)) || $this->hfov == '' || (int)$this->hfov == 0) {
                $this->errmsg = 'Horizontal FOV not OK : ' . $this->hfov;
                $this->error = true;
            }
     
        }
        
        //Check all files for generation
        if($this->error == false) {
            //TOOLS
            //check to see if kmakemultires
            if(!file_exists(trailingslashit($this->kmakemultiresFolderPath) . $this->kmakemultiresFile)) {
                $this->errmsg = 'Krpanotool - ' . $this->kmakemultiresFile .' file not found in ' . $this->kmakemultiresFolder;
                $this->error = true;
            }
            //check to see if file is executable
            elseif(!is_executable (trailingslashit($this->kmakemultiresFolderPath) . $this->kmakemultiresFile)) {
                $this->errmsg = 'Krpanotool - ' . $this->kmakemultiresFile .' not executable';
                $this->error = true;
            }
            //check to see if krpanotool config file exists
            elseif(!file_exists($this->toolConfigFilePath)) {
                $this->errmsg = 'Krpanotool - Config File '. $this->toolConfigFile . ' not found';
                $this->error = true;
            }
            //check to see if krpanotool XML config file exists
            elseif(!file_exists($this->kmakemultiresXMLConfigPath)) {
                $this->errmsg = 'Krpanotool - XML Config File '. $this->kmakemultiresXMLConfig . ' not found';
                $this->error = true;
            }
            
        }
        
        
        if($this->error == false) {
            //Manage Folders
            $this->manageFolders(false);

        
        
        
            if($this->imageInputPath =="" ) {
                $this->errmsg = 'Image not found for id : ' . $this->pid;
                $this->error = true;
                
            } else {

                //Generate and execute command
                $command = $this->generateCommand();

                exec($command, $output, $return);

                if ($return !== 0)
                {
                    $this->errmsg = 'Progam did not finished correctly ! (output : '.implode("\n", $output);
                    $this->error = true;
                  //throw new Exception("Progam did not finished correctly ! (output : ".implode("\n", $output));
                }

                //Store config in database
                $this->is_partial = ($this->hfov == 360) ? 0 : 1;     // Check pano is partial - force assuming that the input is a partial sphere   (hfov setting needed!)
                $this->xml_configuration = file_get_contents($this->xmlKrpanoPath);
                //$this->xml_configuration = str_replace('url="', 'url="'.$this->panoFolder.'/', $this->xml_configuration);
                if($removeXML)
                    unlink($this->xmlKrpanoPath);
                
                $this->save();
                
                //DEBUG
                //echo $command;

                
            }
        }
    }
    
  /**
   * Save parameters in database
   *
   */
    public function save()
    {
        global $wpdb;
        
        $error = true; 
        
        $pid = $this->pid;
        $gid = $this->gid;
        
        //correct values
        $hfov = $this->hfov ? $this->hfov : 'null';
        $vfov = $this->vfov ? $this->vfov : 'null';
        $voffset = $this->voffset ? $this->voffset : 'null';
   
        
        if(nggpano_getImagePanoramicOptions($pid)) {
            $query  = "UPDATE ".$wpdb->prefix."nggpano_panoramic SET ";
            $query .= "pano_directory = '" . $this->panoFolder . "', ";
            $query .= "xml_configuration = '" . $this->xml_configuration . "', ";
            $query .= "is_partial = '" . $this->is_partial . "', ";
            $query .= "hfov = " . $hfov . ", ";
            $query .= "vfov = " . $vfov . ", ";
            $query .= "voffset = " . $voffset . " ";
            $query .= "WHERE pid = '".$wpdb->escape($pid)."'";
            
            if($wpdb->query($query) !== false) {
                
                $error = false; 
                $message = __('Panoramic datas successfully saved','nggpano');
            } else {
                $message = 'Error with database';
            };
        }else{
            $query  = "INSERT INTO ".$wpdb->prefix."nggpano_panoramic (id, pid, gid, pano_directory, xml_configuration, is_partial, hfov, vfov, voffset) ";
            $query .= "VALUES (null, '".$wpdb->escape($pid)."', ";
            $query .= "'" . $wpdb->escape($gid) . "', ";
            $query .= "'" . $this->panoFolder . "', ";
            $query .= "'" . $this->xml_configuration . "', ";
            $query .= "'" . $this->is_partial . "', ";
            $query .= "" . $hfov . ", ";
            $query .= "" . $vfov . ", ";
            $query .= "" . $voffset . " ";
            $query .= ")";
            
            if($wpdb->query($query) !== false) {
                $error = false;
                $message = __('Panoramic datas successfully saved','nggpano');
            } else {
                $message = 'Error with database';
            };
        }
        
        if($this->error == false && $error ) {
            $this->errmsg = $message;
            $this->error = true;
        
        }        
        
//        echo $query;
//        echo "<hr/>";
//        echo $message;
    }

  /**
   * Delete pano
   *
   */
    public function delete($emptydatabase = false, $keepdirectory = false)
    {
        $nggpanopath = $this->panoFolderPath;

        //Check if folder already exist
        if(is_dir($nggpanopath)) {
            //Empty the directory
            $this->unlinkRecursive($nggpanopath, !$keepdirectory);
        }
        
        //remove in database
        if($emptydatabase) {
            $this->panoFolder ='';
            $this->xml_configuration = '';
            $this->is_partial = 0;
            $this->hfov                 = 'null';
            $this->vfov                 = 'null';
            $this->voffset              = 'null';
            
            $this->save();
        }
        
    }
    

  /**
   * Check if pano exists
   *
   * @return Boolean
   */
    public function exists()
    {
        $this->loadFromDB();
        if($this->panoFolderPath == "") {
            return false;
        }
        if(!is_dir($this->panoFolderPath)) {
            return false;
        }
        if($this->xml_configuration == "") {
            return false;
        }


        return true;
    }
    
  /**
   * Load panoinformation from database
   *
   * @return Void
   */
    public function loadFromDB()
    {
        $database_infos = nggpano_getImagePanoramicOptions($this->pid);
        
        if($database_infos) {
            //set properties
            $this->panoFolder = $database_infos->pano_directory;
            $this->setpanoFolderPath();
            $this->xml_configuration = $database_infos->xml_configuration;
            $this->is_partial = $database_infos->is_partial;
            $this->hfov = $database_infos->hfov;
            $this->vfov = $database_infos->vfov;
            $this->voffset = $database_infos->voffset;
            $this->gid = $database_infos->gid;

        }
    }
    
  /**
   * Set HFOV for DB save
   *
   * @return Void
   */
    public function setHFov($hfov)
    {
        if($hfov)
            $this->hfov = $hfov;
    }

  /**
   * Set VFOV for DB save
   *
   * @return Void
   */
    public function setVFov($vfov)
    {
        if($vfov)
            $this->vfov = $vfov;
    }
    
  /**
   * Set Voffset for DB save
   *
   * @return Void
   */
    public function setVOffset($voffset)
    {
        if($voffset)
            $this->voffset = $voffset;
    }
    
  /**
   * Set xml_configuration for DB save
   *
   * @return Void
   */
    public function setXmlConfiguration($xml_configuration)
    {
        if($xml_configuration)
            $this->xml_configuration = $xml_configuration;
    }
    
  /**
   * Set panoFolder for DB save
   *
   * @return Void
   */
    public function setPanoFolder($panoFolder)
    {
        if($panoFolder)
            $this->panoFolder = $panoFolder;
    }
    
  /**
   * Set is_partial for DB save
   *
   * @return Void
   */
    public function setIsPartial($is_partial)
    {
        //if($is_partial)
            $this->is_partial = $is_partial;
    }
    
    /**
    * Load configuration for pano creation and viewer (default options or gallery configuration)
    *
    */
    public function loadConfig()
    {
        global $nggpano_options;
        //Get default options of the plugin
        if(!$nggpano_options)
            $nggpano_options = get_option('nggpano_options');
        
        //TOOLS
        //Krpano Tool Config File
        $defaultToolConfigFile      =   $nggpano_options['toolConfigFile'];  	// set default config file for krapnotool
        //Krpano Tool Config File Folder
        $kmakemultiresConfigFolder =   trailingslashit($nggpano_options['kmakemultiresConfigFolder']);  	// set default config file for krapnotool
        //Krpano Tool Folder
        $kmakemultiresFolder        =   trailingslashit($nggpano_options['kmakemultiresFolder']);
        //Krpano Tool XML Config file
        $kmakemultiresXMLConfig     = $nggpano_options['kmakemultiresXMLConfig'];  // =plugin_dir_path("nextgen-gallery-panoramics")."/krpanotools_xml_config/default.xml";
        //Krpano Tempfolder
        $krpanoToolsTempFolder      =   trailingslashit($nggpano_options['krpanoToolsTempFolder']);      //working dir for krpano
        
        //VIEWER
        //Krpano Viewer Folder
        $krpanoFolder               =   trailingslashit($nggpano_options['krpanoFolder']);              //	= plugin_dir_path("nextgen-gallery-panoramics")."/krpano/";
        //Krpano Viewer Plugin Folder
        $pluginFolder               =   trailingslashit($nggpano_options['pluginFolder']);              //	= plugin_dir_path("nextgen-gallery-panoramics")."/krpano_plugins/";
        
        //Skin File
        $defaultSkinFile            =   $nggpano_options['defaultSkinFile'];     // set default skin for krpano.swf 
        //Krpano Skin Folder
        $skinFolder                 =   trailingslashit($nggpano_options['skinFolder']);     // append related images //    = plugin_dir_path("nextgen-gallery-panoramics")."/krpano_skins/";

        
        //Get option from Gallery pano config
        $gallery_pano_options = nggpano_getGalleryOptions($this->gid);
        
        //
        
        //Set variables
        //VIEWER
        //Viewer Folder
        $this->krpanoFolder       = $krpanoFolder;
        $this->krpanoFolderURL    = trailingslashit(site_url()) . $krpanoFolder;//site_url() . NGGPANO_PLUGIN_DIR . $krpanoFolder; 
        $this->krpanoFolderPath   = ABSPATH . $krpanoFolder;
        //Plugins Folder
        $this->pluginFolder        = $pluginFolder;
        $this->pluginFolderURL    = trailingslashit(site_url()) . $pluginFolder; 
        $this->pluginFolderPath   = ABSPATH . $pluginFolder;
        //Skin Folder
        $this->skinFolder       = $skinFolder;
        $this->skinFolderURL    = trailingslashit(site_url()) . $skinFolder; 
        $this->skinFolderPath   = ABSPATH . $skinFolder;
        //Skin
        $this->viewerTemplate       = (isset($gallery_pano_options->skin) && $gallery_pano_options->skin <> '') ? $gallery_pano_options->skin : $defaultSkinFile;     //Complete Path to the kmultires config file
        $this->viewerTemplateURL    = $this->skinFolderURL .$this->viewerTemplate; 
        $this->viewerTemplatePath   = $this->skinFolderPath .$this->viewerTemplate;

        //TOOLS
        //Path to kmakemultires
        $this->kmakemultiresFolder      = $kmakemultiresFolder;
        $this->kmakemultiresFolderPath  = ABSPATH . $kmakemultiresFolder;
        //Config Folder
        $this->kmakemultiresConfigFolder           = $kmakemultiresConfigFolder;
        $this->kmakemultiresConfigFolderPath       = ABSPATH . $kmakemultiresConfigFolder;
        //Config Files
        $this->toolConfigFile           = $defaultToolConfigFile;
        $this->toolConfigFilePath       = ABSPATH . $kmakemultiresConfigFolder .$this->toolConfigFile;
        //XML Config Files
        $this->kmakemultiresXMLConfig       = $kmakemultiresXMLConfig;  //Complete Path to the kmultires tool xml config files (default.xml)
        $this->kmakemultiresXMLConfigPath   = ABSPATH . $kmakemultiresXMLConfig; 
        //Temp Folder
        $this->krpanoToolsTempFolder    = $krpanoToolsTempFolder;
        
    }
  
    public function test() {
        var_dump(get_object_vars($this));
    }
    
    
    /**
     * Check if directory exist and create then
     * 
     * @class nggpanoPano
     * @param bool $output if the function should show an error messsage or not
     * @return 
     */
    protected function manageFolders($output = false) {
        $nggpanoRoot = NGGPANOWINABSPATH . $this->galleryFolder;
        $nggpanopath = $this->panoFolder;
        
        //Check if folder already exist
        if(is_dir(NGGPANOWINABSPATH . $nggpanopath)) {
            //Empty the directory
            $this->unlinkRecursive(NGGPANOWINABSPATH . $nggpanopath, false);
        }
        
        
        
        $txt = '';
        // check for main folder
        if ( !is_dir($nggpanoRoot) ) {
            if ( !wp_mkdir_p( $nggpanoRoot ) ) {
                $txt  = __('Directory', 'nggallery').' <strong>' . $this->galleryFolder . '</strong> '.__('didn\'t exist. Please create first the main gallery folder ', 'nggallery').'!<br />';
                $txt .= __('Check this link, if you didn\'t know how to set the permission :', 'nggallery').' <a href="http://codex.wordpress.org/Changing_File_Permissions">http://codex.wordpress.org/Changing_File_Permissions</a> ';
                if ($output) nggPanoramic::show_error("1.".$txt);
                return false;
            }
        }

        // check for permission settings, Safe mode limitations are not taken into account. 
        if ( !is_writeable( $nggpanoRoot ) ) {
            $txt  = __('Directory', 'nggallery').' <strong>' . $this->galleryFolder . '</strong> '.__('is not writeable !', 'nggallery').'<br />';
            $txt .= __('Check this link, if you didn\'t know how to set the permission :', 'nggallery').' <a href="http://codex.wordpress.org/Changing_File_Permissions">http://codex.wordpress.org/Changing_File_Permissions</a> ';
            if ($output) nggPanoramic::show_error("2.".$txt);
            return false;
        }
        
        
        
        // 1. Check for existing folder
        if ( !is_dir(NGGPANOWINABSPATH . $nggpanopath)) {
            if ( !wp_mkdir_p (NGGPANOWINABSPATH . $nggpanopath) ) 
              $txt  = "3.".__('Unable to create directory ', 'nggallery').$nggpanopath.'!<br />';
        }
        // 3. Check folder permission
        if ( !is_writeable(NGGPANOWINABSPATH . $nggpanopath ) )
                $txt .= "4.".__('Directory', 'nggallery').' <strong>'.$nggpanopath.'</strong> '.__('is not writeable !', 'nggallery').'<br />';

        if (NGGPANO_SAFE_MODE) {
                $help  = __('The server setting Safe-Mode is on !', 'nggallery');	
                $help .= '<br />'.__('If you have problems, please create directory', 'nggallery').' <strong>' . $nggpanopath . '</strong> ';	
                $help .= __('with permission 777 manually !', 'nggallery');
                if ($output) nggPanoramic::show_message($help);
        }

        // show a error message			
        if ( !empty($txt) ) {
                if (NGGPANO_SAFE_MODE) {
                // for NGGPANO_SAFE_MODE , better delete folder, both folder must be created manually
                        @rmdir(NGGPANOWINABSPATH . $nggpanopath);
                        //@rmdir(NGGPANOWINABSPATH . $nggpanopath);
                }
                if ($output) nggPanoramic::show_error($txt);
                return false;
        }
    }
    
    /**
    * Generate the command to execute
    *
    * @return String
    */
    protected function generateCommand()
    {
        //kmakemultires tool path
        $executable = $this->kmakemultiresFolderPath . $this->kmakemultiresFile;
        //template for xml generation path
        $xmltemplate = $this->kmakemultiresXMLConfigPath;
        
        //Path for tiles generation
        //tilepath=%INPUTPATH%/%BASENAME%.tiles/l%Al[_c]_%Av_%Ah.jpg
        //tilepath=%INPUTPATH%/%BASENAME%.tiles/l%Al/[c]/%Av/l%Al[_c]_%Av_%Ah.jpg
        $tilepath ="%INPUTPATH%" . $this->panoSubFolder . $this->panoPrefix . $this->pid . "/" . $this->panoPrefix . $this->pid . ".tiles/l%Al/[c]/%Av/l%Al[_c]_%Av_%Ah.jpg";
        $tilepath ="%INPUTPATH%" . $this->panoSubFolder . $this->panoPrefix . $this->pid . "/tiles/mres_[c/]l%Al/%Av/l%Al[_c]_%Av_%Ah.jpg";
        //tilepath=%INPUTPATH%/pano_%PANOID%/tiles/[mres_c/]l%Al/%Av/l%Al[_c]_%Av_%Ah.jpg
        //Path for preview generation
        //previewpath=%INPUTPATH%/%BASENAME%.tiles/preview.jpg
        $previewpath ="%INPUTPATH%" . $this->panoSubFolder . $this->panoPrefix . $this->pid . "/tiles/preview.jpg";
        
        //Path for thumb generation
        //thumbpath=%INPUTPATH%/%BASENAME%.tiles/thumb.jpg
        $thumbpath ="%INPUTPATH%" . $this->panoSubFolder . $this->panoPrefix . $this->pid . "/tiles/thumb.jpg";
        
        //Path for ipad3 image
        //customimage[ipad3].path=%INPUTPATH%/pano/tiles/ipad3_%s.jpg
        $customimage_ipad3 ="%INPUTPATH%" . $this->panoSubFolder . $this->panoPrefix . $this->pid . "/tiles/ipad3_%s.jpg";
        
        //Path for mobile image
        //customimage[mobile].path=%INPUTPATH%/pano/tiles/mobile_%s.jpg
        $customimage_mobile ="%INPUTPATH%" . $this->panoSubFolder . $this->panoPrefix . $this->pid . "/tiles/mobile_%s.jpg";
        
        //Path for mobile image
        //customimage[mobile].path=%INPUTPATH%/pano/tiles/mobile_%s.jpg
        $customimage_html5 ="%INPUTPATH%" . $this->panoSubFolder . $this->panoPrefix . $this->pid . "/tiles/html5_%s.jpg";
        
        
        
        //make sure krpanotools are here
        if (!file_exists($executable))
        {
          throw new Exception("Unable to find executable for tiles creation : $executable");
        }

        $cmd = '"' . $executable .'" "-hfov=' . $this->hfov . '" ';
        
        if($this->vfov <> '')
            $cmd .= '"-vfov=' . $this->vfov . '" ';

        if($this->voffset <> '')
            $cmd .= '"-voffset=' . $this->voffset . '" ';

        if($this->krpanoToolsTempFolder <> '')
            $cmd .= '"-tempdir=' . $this->krpanoToolsTempFolder . '" ';
        
        $cmd .= '"-xmlpath=' . $this->xmlKrpanoPath . '" ';
        $cmd .= '"-xmltemplate='. $xmltemplate . '" ';
        $cmd .= '"-tilepath='. $tilepath . '" ';
        $cmd .= '"-previewpath='. $previewpath . '" ';
        $cmd .= '"-thumbpath='. $thumbpath . '" ';
        $cmd .= '"-customimage[mobile].path='. $customimage_mobile . '" ';
        $cmd .= '"-customimage[ipad3].path='. $customimage_ipad3 . '" ';
        $cmd .= '"-customimage[html5].path='. $customimage_html5 . '" ';
        $cmd .= '"'.$this->imageInputPath.'" "' . $this->toolConfigFilePath . '"';
        return $cmd;

    } 
    
    /**
     * Recursively delete a directory
     *
     * @param string $dir Directory name
     * @param boolean $deleteRootToo Delete specified top-level directory as well
     */
    protected function unlinkRecursive($dir, $deleteRootToo)
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
                $this->unlinkRecursive($dir.'/'.$obj, true);
            }
        }

        closedir($dh);

        if ($deleteRootToo)
        {
            @rmdir($dir);
        }

        return;
    }   
    
  /**
   * Get krpano xml to show the pano
   *
   * @param String $URLsearchstring    Url string to search
   * @param String $URLreplacestring    Url string to search
   *
   * @return XML
   */
  public function getXML($URLsearchstring = null, $URLreplacestring = null, $debug = false)
  {
    global $ngg, $nggpano_options;
    
    //Get default options of the plugin
    if(!$nggpano_options)
        $nggpano_options = get_option('nggpano_options');
        
    if (isset($this->xml_configuration)) {
        $this->loadFromDB();   
    }
    $partial = isset($this->is_partial) ? 'ispartialpano="true"' : '';
    
    $xmlConfiguration = $this->xml_configuration;
    //if($URLsearchstring && $URLreplacestring) {
        $xmlConfiguration = str_replace('url="'.$URLsearchstring, 'url="'.$URLreplacestring, $xmlConfiguration);
    //}
    
    //$basedir =  'basedir="'. $this->panoFolderURL . '/"';
//    $basedir =  'basedir="'. site_url() . '/"';
//    
//    $xmlreturn  = '<krpano version="1.0.8.14" '. $partial . ' ' . $basedir . '>';

    //GET Thumbs URL
    //$img_src = trailingslashit( home_url() ) . 'index.php?callback=image&amp;pid=' . $image->pid . '&amp;width=' . $width . '&amp;height=' . $height . '&amp;mode=crop';
    $nextgen_thumb  = $this->thumbURL;
    $square_thumb   = $this->ImageCustomURL . '&amp;width='.$nggpano_options['widthThumbVirtualTour'].'&amp;height='.$nggpano_options['heightThumbVirtualTour'].'&amp;mode=crop';
    $custom_thumb   = $this->ImageCustomURL . '&amp;width='.$nggpano_options['widthThumbVirtualTour'].'&amp;height='.$nggpano_options['heightThumbVirtualTour'].'';
                
		
    //Add scene node
    $xmlreturn  = '<!-- SCENE  -->';
    $xmlreturn  .= '<scene name="scene-'.$this->pid.'" title="'.$this->title.'" defaultthumburl="'.$nextgen_thumb.'" squarethumburl="'.$square_thumb.'" customthumburl="'.$custom_thumb.'" >';
    $xmlreturn  .= $xmlConfiguration;
    //$xmlreturn  .= '<progress showload="bar(midbottom, 100%, 2, 0, 55, shaded, 0x0a0a0a, 0x788794, 0x788794, 0x9f9f9f, 0, 0x9f9f9f, 0)" showreloads="true" showwait="true"/>';

    $xmlreturn  .= '</scene>';
//    if($debug) {
//        $xmlreturn  .= '<plugin name="options" url="'.$this->pluginFolderURL.'options.swf" />';
//    }
    //$xmlreturn .= '</krpano>';

    return $xmlreturn;

  }
  
  
  /**
   * Get skin xml node
   *
   * @return XML
   */
  function getSkinXML()
  {
    $xml_skin = file_get_contents($this->viewerTemplatePath);
    $xml_skin = str_replace('%PLUGINDIR%', $this->pluginFolderURL, $xml_skin);
    $xml_skin = str_replace('%SKINDIR%', $this->skinFolderURL, $xml_skin);
    //%PLUGINDIR% = directory with krpano plugin
    //%SKINDIR% = directory with krpano skin
    
    //$xmlreturn  = '<include url="'.$this->viewerTemplateURL.'" />';

    return $xml_skin;

  }
  

  public function show($divid, $width = '100%', $height = '100%', $no_output = false, $html5='auto') {
        $str_return = '';
        //Check all files for generation
        if($this->error == false) {
            //Throw an error if a file doesn't exist
            //VIEWER
            //check to see if krpano viewer file exists
            if(!file_exists(trailingslashit($this->krpanoFolderPath) . $this->krpanoSWF)) {
                $this->errmsg = $this->krpanoSWF .' file not found in ' . $this->krpanoFolder;
                $this->error = true;
            }
            //check to see if Skin file exists
            elseif(!file_exists($this->viewerTemplatePath)) {
                $this->errmsg = 'Template file '. $this->viewerTemplate .' not found';
                $this->error = true;
            }
            //check to see if pano correctly build
            elseif(!$this->exists()) {
                $this->errmsg = 'Panorama not found';
                $this->error = true;
            }
        }
        
        if($this->error == false) {

            //load pano information from database
            $this->loadFromDB();

            //$is_mobile_phone = nggGallery::detect_mobile_phone();

            $krpano_path    = trailingslashit($this->krpanoFolderURL) . $this->krpanoSWF;
            $krpano_xml     = NGGPANOGALLERY_URLPATH . 'xml/krpano.php?pano=single_'.$this->pid;

            $str_return .='<div id="'.$divid.'" style="width:'.$width.'; height:'.$height.';"></div>';
            
//            $str_return .='<script type="text/javascript">';
//            $str_return .='function initializePano() {';
//            $str_return .=' var viewer = createPanoViewer({swf:"'.$krpano_path.'", wmode:"opaque"});';
//            $str_return .=' viewer.addVariable("xml", "'.$krpano_xml.'");';
//            $str_return .=' viewer.embed("'.$divid.'");';
//            $str_return .='}';
//            $str_return .='initializePano()';
//            $str_return .='</script>';
//            $str_return .='';

            $str_return .='<script type="text/javascript">';
            $str_return .='function initializePano() {';
            $str_return .='embedpano({swf:"'.$krpano_path.'", xml:"'.$krpano_xml.'", target:"'.$divid.'", html5:"'.$html5.'", passQueryParameters:"true"});';
            $str_return .='}';
            $str_return .='initializePano()';
            $str_return .='</script>';

        } else {
            $str_return .='<h1>ERROR</h1>';
            $str_return .='<h4>'. $this->errmsg . '</h4>';
        }
        
        if($no_output) {
            return $str_return;
        } else {
            echo $str_return;
        }
  }
  
  public function getScriptForPrettyPhoto($no_output=false) {
        $str_return = '';
        //Check all files for generation
        if($this->error == false) {
            //Throw an error if a file doesn't exist
            //VIEWER
            //check to see if krpano viewer file exists
            if(!file_exists(trailingslashit($this->krpanoFolderPath) . $this->krpanoSWF)) {
                $this->errmsg = $this->krpanoSWF .' file not found in ' . $this->krpanoFolder;
                $this->error = true;
            }
            //check to see if Skin file exists
            elseif(!file_exists($this->viewerTemplatePath)) {
                $this->errmsg = 'Template file '. $this->viewerTemplate .' not found';
                $this->error = true;
            }
            //check to see if pano correctly build
            elseif(!$this->exists()) {
                $this->errmsg = 'Panorama not found';
                $this->error = true;
            }
        }
        
        if($this->error == false) {

            //load pano information from database
            $this->loadFromDB();

            //$is_mobile_phone = nggGallery::detect_mobile_phone();

            $krpano_path    = trailingslashit($this->krpanoFolderURL) . $this->krpanoSWF;
            $krpano_xml     = NGGPANOGALLERY_URLPATH . 'xml/krpano.php?pano=single_'.$this->pid;

            //$str_return .='<div id="'.$divid.'" style="width:'.$width.'; height:'.$height.';">...Loading Panoramic...</div>';
            
            $str_return .='<script type="text/javascript">';
            $str_return .='function initializePano() {';
            $str_return .=' var viewer = createPanoViewer({swf:"'.$krpano_path.'", wmode:"opaque"});';
            $str_return .=' viewer.addVariable("xml", "'.$krpano_xml.'");';
            $str_return .=' viewer.embed("pano_canvas");';
            $str_return .='}';
            $str_return .='</script>';
            $str_return .='';
            


        } else {
            $str_return .='<h1>ERROR</h1>';
            $str_return .='<h4>'. $this->errmsg . '</h4>';
        }
        
        if($no_output) {
            return $str_return;
        } else {
            echo $str_return;
        }
  }
  
  
  public function getObjectFromDB() {
        //Check all files for generation
        if($this->error == false) {
            //Throw an error if a file doesn't exist
            //VIEWER
            //check to see if krpano viewer file exists
            if(!file_exists(trailingslashit($this->krpanoFolderPath) . $this->krpanoSWF)) {
                $this->errmsg = $this->krpanoSWF .' file not found in ' . $this->krpanoFolder;
                $this->error = true;
            }
            //check to see if Skin file exists
            elseif(!file_exists($this->viewerTemplatePath)) {
                $this->errmsg = 'Template file '. $this->viewerTemplate .' not found';
                $this->error = true;
            }
            //check to see if pano correctly build
            elseif(!$this->exists()) {
                $this->errmsg = 'Panorama not found';
                $this->error = true;
            }
        }
        
        if($this->error == false) {
            //load pano information from database
            $this->loadFromDB();
            return $this;

        } else {
            return false;
        }

  }
    
    function __destruct() {

    }
}
endif;
?>