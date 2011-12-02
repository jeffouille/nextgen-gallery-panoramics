<?php
/**
*
* Make xml render for krpano : ?method=krpano&id=12
* 
* @require		PHP 5.2.0 or higher
* 
*/
 ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);
// Load wp-config
if ( !defined('ABSPATH') ) 
	require_once( dirname(__FILE__) . '/../nggpano-config.php');
// reference nggpanoPano class
//include_once( nggGallery::graphic_library() );
if(! class_exists('nggpanoPano'))
    include_once('../lib/nggpanoPano.class.php');


class nggpanoKrpanoXML {

    /**
      *	$_GET Variables 
      * 
      * @since 1.5.0
      * @access private
      * @var string
      */
    var $pano       =   false;          // $_GET['pano']  ex: method_type_id (single
    var $method     =   false;		// $_GET['pano']    first item of $_GET['pano']	: single | mutiple
    //var $type       =   false;		// $_GET['type']	: scene | image (required for method scene) TODO (get from method)
    var $id         =   false;		// $_GET['id']      second item of $_GET['pano']    : object id (required for method multiple | single )

    /**
     * Contain the final output
     *
     * @since 1.5.0
     * @access private
     * @var string
     */	
    var $output		=	'';

    /**
     * Holds the requested information as array
     *
     * @since 1.5.0
     * @access private
     * @var array
     */	
    var $result		=	'';

    /**
     * Init the variables
     * 
     */	
    function __construct() {

    //if ( !defined('ABSPATH') )
       // die('You are not allowed to call this page directly.');
            //Get pano and method from $_GET['pano']
       $this->pano  = isset($_GET['pano']) ? strtolower( $_GET['pano'] ) : false; 
       if($this->pano) {
           if (strpos($this->pano, '_')) {
                $splitpano = split('_', $this->pano);
                // Read the parameter on init
                $this->method 	= isset($splitpano[0]) ? strtolower( $splitpano[0] ) : false;
                $this->id 	= isset($splitpano[1]) ? strtolower( $splitpano[1] ) : false;
                //$this->type	= isset($_GET['type'])   ? strtolower( $_GET['type'] ) : false; 
           }
       }
            $this->result	= array();

            $this->start_process();
            $this->render_output();
    }

    function start_process() {

        global $ngg, $nggpano;

        if ( !$this->valid_access() ) 
                return;

        switch ( $this->method ) {

            case 'single' :
                //search for the pano
                $pano = new nggpanoPano($this->id, '');
                $pano->loadFromDB();
                
                $xmlpano = $pano->getXML('','%BASEDIR%/');
                $this->result['xmlpanonode']= $xmlpano;
            break;            
            case 'multiple' :
                //search for all pano of the gallery to make scene node TODO
                //$this->result['images'] = ($this->id == 0) ? nggdb::find_last_images( 0 , 100 ) : nggdb::get_gallery( $this->id, $ngg->options['galSort'], $ngg->options['galSortDir'], true, 0, 0, true );
                $this->result = array ('stat' => 'fail', 'code' => '98', 'message' => 'Method multiple not ready yet.');
                return false;
            break;
            default :
                $this->result = array ('stat' => 'fail', 'code' => '98', 'message' => 'Method not known.');
                return false;	
            break;		
        }

        // result should be fine	
        $this->result['stat'] = 'ok';	
    }

    function valid_access() {
        return true;
    }

    /**
     * Iterates through a multidimensional array
     * 
     * @author Boris Glumpler
     * @param array $arr
     * @return void
     */
    function create_xml_array( &$arr )
    {
        $xml = '';
        
        if( is_object( $arr ) )
            $arr = get_object_vars( $arr );

        foreach( (array)$arr as $k => $v ) {
            if( is_object( $v ) )
                $v = get_object_vars( $v );
            //nodes must contain letters   
            if( is_numeric( $k ) )
                $k = 'id-'.$k;                
            if( is_array( $v ) )
                $xml .= "<$k>\n". $this->create_xml_array( $v ). "</$k>\n";
            else
                $xml .= "<$k>$v</$k>\n";
        }
        
        return $xml;
    }
	
    function render_output() {
        global $ngg, $nggpano;
        
        
        
        header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
        $this->output  = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\n";
        //$this->output .= "<xml>\n";
        //$this->output .= "<debug>" .$this->create_xml_array($this->method) . $this->create_xml_array($this->id) .$this->create_xml_array($this->result) . "</debug>\n";
        $this->output .= $this->result['xmlpanonode'];	
        //$this->output .= "<krpano>" . $this->result['xmlpanonode']  . "</krpano>\n";
        //$this->output .="</xml>";
        
        //$this->output  =json_encode($this->result);

    }

    /**
     * PHP5 style destructor and will run when the class is finished.
     *
     * @return output
     */
    function __destruct() {
            echo $this->output;
    }

}

// let's use it
$nggpanoKrpanoXML = new nggpanoKrpanoXML();
//
//                $pano = new nggpanoPano(72,'');
//                $pano->loadFromDB();
//                
//                $xmlpano = $pano->getXML();
//                echo $xmlpano;