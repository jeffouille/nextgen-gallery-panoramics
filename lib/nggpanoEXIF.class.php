<?php

/**
 * Image Informations from EXIF data
 * nggpanonggpanoEXIF.class.php
 * 
 * @author 	Geoffroy Deleury 
 * @copyright 	Copyright 2011
 * 
 */
	  
class nggpanoEXIF{

	/**** Image Data ****/
    var $image			=	'';		// The image object
    var $size			=	false;	// The image size
	var $exif_data 		= 	false;	// EXIF data array

 	/**
 	 * nggpanoEXIF::nggpanoEXIF()
 	 * 
 	 * @param int $image path to a image
 	 * @param bool $onlyEXIF parse only exif if needed
 	 * @return
 	 */
 	function nggpanoEXIF($pic_id) {
 		
            //get the path and other data about the image
            $this->image = nggdb::find_image( $pic_id );

            //$this->image = apply_filters( 'ngg_find_image_meta', $this->image  );		

            if ( !file_exists( $this->image->imagePath ) )
                return false;

            $this->size = @getimagesize ( $this->image->imagePath , $metadata );

            if ($this->size && is_array($metadata)) {

                    // get exif - data
                if ( is_callable('exif_read_data'))
                    $this->exif_data = @exif_read_data($this->image->imagePath , 0, true );

                return true;
            }

            return false;
 	}
	
  /**
   * nggpanoEXIF::get_EXIF()
   * See also http://trac.wordpress.org/changeset/6313
   *
   * @return  EXIF data
   */
	function get_EXIFData($object = false) {
		
		if ( !$this->exif_data )
			return false;
		
                return $this->exif_data;
	
	}
	
	// convert a fraction string to a decimal
	function exif_frac2dec($str) {
		@list( $n, $d ) = explode( '/', $str );
		if ( !empty($d) )
			return $n / $d;
		return $str;
	}
	
	// convert the exif date format to a unix timestamp
	function exif_date2ts($str) {
		// seriously, who formats a date like 'YYYY:MM:DD hh:mm:ss'?
		@list( $date, $time ) = explode( ' ', trim($str) );
		@list( $y, $m, $d ) = explode( ':', $date );
	
		return strtotime( "{$y}-{$m}-{$d} {$time}" );
        }

        function get_Exif_GPS($assoc = false) {
            if ( isset($this->exif_data['GPS']) ) {
                $exif = $this->exif_data['GPS'];
                    //get the Hemisphere multiplier
                    $LatM = 1; $LongM = 1;
                    if($exif["GPSLatitudeRef"] == 'S') {
                            $LatM = -1;
                            }
                    if($exif["GPSLongitudeRef"] == 'W') {
                            $LongM = -1;
                            }
     
                    //get the GPS data
                    $gps['LatDegree']=$exif["GPSLatitude"][0];
                    $gps['LatMinute']=$exif["GPSLatitude"][1];
                    $gps['LatgSeconds']=$exif["GPSLatitude"][2];
                    $gps['LongDegree']=$exif["GPSLongitude"][0];
                    $gps['LongMinute']=$exif["GPSLongitude"][1];
                    $gps['LongSeconds']=$exif["GPSLongitude"][2];
                    $gps['Altitude']=$exif["GPSAltitude"];
                    $gps['TimeHour']=$exif["GPSTimeStamp"][0];
                    $gps['TimeMin']=$exif["GPSTimeStamp"][1];
                    $gps['TimeSec']=$exif["GPSTimeStamp"][2];
                    //$gps['direction']=$exif["GPSImgDirection"];
     
                    //convert strings to numbers
                    
                    
                    foreach($gps as $key => $value) {
                        if($value <> '0/0') {
                            $gps[$key] = $this->exif_frac2dec($value);
                        } else {
                            $gps[$key] = 0;
                        }            
                    }  
     
                    //calculate the decimal degree
                    $result['latitude'] = $LatM * ($gps['LatDegree'] + ($gps['LatMinute'] / 60) + ($gps['LatgSeconds'] / 3600));
                    $result['longitude'] = $LongM * ($gps['LongDegree'] + ($gps['LongMinute'] / 60) + ($gps['LongSeconds'] / 3600));
                    $result['altitude'] = $gps['Altitude'];
                    $result['timestamp'] = $gps['TimeHour'].':'.$gps['TimeMin'].':'.$gps['TimeSec'];
                    //$result['direction'] = $gps['direction'];
                    
     
                    if($assoc) {
                            return $result;
                            }
     
                    return json_encode($result);
            }
            return false;
        }
        
        function getFOVInformations() {
            if ( isset($this->exif_data['EXIF']['UserComment']) ) {
                return $this->getFOVInformationsFromUserComment();
            } else {
                // Ratio 2:1 --> HFOV = 360
                if ($this->getImageRatio(false) == 2) {
                    return array(
                        'hfov'      => 360,
                        'vfov'      => $this->getVfovFromHfov(360),
                        'voffset'   => 0
                      );
                } else {
                    //Last attempt, from the filename formatted like this "Filename -widthxheight - hfovxvfov(voffset)"
                    //Exemple : MyRender - 3106x885 - 122.56x34.95(-15.24)
                    return $this->getFOVInformationsFromFilename();
                }
            }
            return false;
        }
        
        protected function getFOVInformationsFromUserComment() {     

            $result = $this->parsePanoramaComment($this->exif_data['EXIF']['UserComment']);
            if (false === $result)
            {
                return false;
            }
            else
            {
              return $result;
            }

            return false;
        }
        
        
        function getFOVInformationsFromFilename() {     

            $result = $this->parseFilename($this->image->filename);
            if (false === $result)
            {
                return false;
            }
            else
            {
              return $result;
            }

            return false;
        }
        
        
        /**
        * Parse the generated EXIF information to find FOV informations
        *
        * @param String $comment The string to parse
        *
        * @return Array An associative array with ``hfov``,
        *               ``vfov``, and ``voffset``.
        *               If the comment information is incorrect, returns ``false``.
        */
        protected function parsePanoramaComment($comment) {
            
            $segment = '(\-?[0-9]+\.?[0-9]*)';
            if (preg_match('/\| FOV: '.$segment.' x '.$segment.' ~ '.$segment.' \|/', $comment, $vars))
            {
              return array(
                'hfov'      => $vars[1],
                'vfov'      => $vars[2],
                'voffset'   => $vars[3]
              );
            }
            else
            {
              return false;
            }
        }
        
        /**
        * Parse the filename to find FOV informations
        *
        * @param String filename The string to parse
        *
        * @return Array An associative array with ``hfov``,
        *               ``vfov``, and ``voffset``.
        *               If the filename is incorrect, returns ``false``.
        */
        protected function parseFilename($filename) {
            
            $segment = '(\-?[0-9]+\.?[0-9]*)';
            
            //APG 2.5 type hfovxvfov(voffset) ex.: 122.56x34.95(-15.24)
            if (preg_match('/'.$segment.'x'.$segment.'\('.$segment.'\)/', $filename, $vars))
            {
                return array(
                    'hfov'      => $vars[1],
                    'vfov'      => $vars[2],
                    'voffset'   => $vars[3]
                );
            }
            //APG < 2.5 type hfovxvfov ex.: 122.56x34.95 (check if segment find > 360 to see if is not dimension)
            elseif(preg_match_all('/'.$segment.'x'.$segment.'/', $filename, $matches))
            {
                if (isset($matches[1])) {
                //return $matches;
                
                if(isset($matches[1][0]) && $matches[1][0] <= 360) {
                    return array(
                        'hfov'      => isset($matches[1][0]) ? $matches[1][0] : '',
                        'vfov'      => isset($matches[2][0]) ? $matches[2][0] : ''
                    );
                } elseif (isset($matches[1][1]) && $matches[1][1] <= 360) {
                    return array(
                        'hfov'      => isset($matches[1][1]) ? $matches[1][1] : '',
                        'vfov'      => isset($matches[2][1]) ? $matches[2][1] : '',
                    );
                } else {
                    return false;
                }
                }

            } else {
                return false;
            }
        }
        
        
        /**
        * Get Vertical FOV from HFOV and dimensions
        *
        * @param decimal $hfov horizontal FOV
        *
        * @return vfov
        */
        
        function getVfovFromHfov($hfov) {
            
            // L        H
            // hfov     vfov
            
            $sizes = $this->size;
            return $hfov * $sizes[1] / $sizes[0];
            
            
        }   
        
        /**
        * Get the Ratio of the image
        *
        * @param Boolean $withformat return value or string formatted "width:height"
        *
        * @return Ratio of the image
        */
        
        function getImageRatio($withformat = false) {
            
            #      * 2:1 aspect = spherical pano
            #      * 1:1 aspect and six files with the same 'basename' = cubical pano
            $sizes = $this->size;
            if (!$withformat) {
                return $sizes[0]/$sizes[1];
            } else {  
                return $this->getSimplifiedRatio($sizes[0], $sizes[1]);
            }
            
        }
        
        protected function getSimplifiedRatio($a, $b) { 
            $gcd = $this->GCD($a, $b);  
            $a = $a/$gcd;  
            $b = $b/$gcd;  
            
            $ratio = $a . ":" . $b;
            
            return $ratio;
            
        }
        
        //find greatest common divisor(GCD)
        function GCD($a, $b) {  
            while ($b != 0) { 
                $remainder = $a % $b;  
                $a = $b;  
                $b = $remainder;  
            }  
            return abs ($a);  
        }  
}

?>