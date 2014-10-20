<?php


/**
* Google Maps // based on Phppgle ;aps by Justin Johnson
* Uses Google Maps Mapping API to create customizable
* Google Maps that can be embedded on your website
*
*/

 class GoogleMap{
/**
* validPoints : array
* Holds addresses and HTML Messages for points that are valid (ie: have longitude and latitutde)
*/
    var $validPoints = array();
/**
* invalidPoints : array
* Holds addresses and HTML Messages for points that are invalid (ie: don't have longitude and latitutde)
*/
    var $invalidPoints = array();
/**
* mapWidth
* width of the Google Map, in pixels
*/
    var $mapWidth = 300;
/**
* mapHeight
* height of the Google Map, in pixels
*/
    var $mapHeight = 300;

/**
* apiKey
* Google API Key
*/
	//default: google api for localhost
    var $apiKey = "ABQIAAAAS92FhP9_3QBHPMGXCjiYERT2yXp_ZAY8_ufC3CFXhHIE1NvwkxSERkUmS3n_pB-rqXP--pub0Sko9g";

/**
* geocode apiKey
* Yahoo Geocode API Key
*/
    var $geocode_apiKey = "YahooDemo";

/**
* showControl
* True/False whether to show map controls or not
*/
    var $showControl = true;
	
/**
* showType
* True/False whether to show map type controls or not
*/
    var $showType = true;
/**
* controlType
* string: can be 'small' or 'large'
* display's either the large or the small controls on the map, small by default
*/

    var $controlType = 'large';
    
/**
* zoomLevel
* int: 0-17
* set's the initial zoom level of the map
*/

    var $zoomLevel = 4;




/**
* @function     addGeoPoint
* @description  Add's an address to be displayed on the Google Map using latitude/longitude
*               early version of this function, considered experimental
*/

public function addGeoPoint($lat,$long,$infoHTML){
    $pointer = count($this->validPoints);
        $this->validPoints[$pointer]['lat'] = $lat;
        $this->validPoints[$pointer]['long'] = $long;
        $this->validPoints[$pointer]['htmlMessage'] = $infoHTML;
    }
    
/**
* @function     centerMap
* @description  center's Google Map on a specific point
*               (thus eliminating the need for two different show methods from version 1.0)
*/

public function centerMap($lat,$long){
    $this->centerMap = "map.centerAndZoom(new GPoint(".$long.",".$lat."), ".$this->zoomLevel.");\n";
}
    
    
/**
* @function     addAddress
* @param        $address:string
* @returns      Boolean True:False (True if address has long/lat, false if it doesn't)
* @description  Add's an address to be displayed on the Google Map
*               (thus eliminating the need for two different show methods from version 1.0)
*/
public function addAddress($address,$htmlMessage=null){
	 if (!is_string($address)){
		die("All Addresses must be passed as a string");
	  }
		$apiURL = "http://api.local.yahoo.com/MapsService/V1/geocode?appid=".$this->geocode_apiKey."&location=";
		$addressData = file_get_contents($apiURL.urlencode($address));
	
		$results = $this->xml2array($addressData);
		
		if (empty($results['ResultSet']['Result']['Address'])){
			$pointer = count($this->invalidPoints);
			$this->invalidPoints[$pointer]['lat']= $results['ResultSet']['Result']['Latitude'];
			$this->invalidPoints[$pointer]['long']= $results['ResultSet']['Result']['Longitude'];
			$this->invalidPoints[$pointer]['passedAddress'] = $address;
			$this->invalidPoints[$pointer]['htmlMessage'] = $htmlMessage;
		  }else{
			$pointer = count($this->validPoints);
			$this->validPoints[$pointer]['lat']= $results['ResultSet']['Result']['Latitude'];
			$this->validPoints[$pointer]['long']= $results['ResultSet']['Result']['Longitude'];
			$this->validPoints[$pointer]['passedAddress'] = $address;
			$this->validPoints[$pointer]['htmlMessage'] = $htmlMessage;
		}
	
	}
/**
* @function     showValidPoints
* @param        $displayType:string
* @param        $css_id:string
* @returns      nothing
* @description  Displays either a table or a list of the address points that are valid.
*               Mainly used for debugging but could be useful for showing a list of addresses
*               on the map
*/
public function showValidPoints($displayType,$css_id){
    $total = count($this->validPoints);
      if ($displayType == "table"){
        echo "<table id=\"".$css_id."\">\n<tr>\n\t<td>Address</td>\n</tr>\n";
        for ($t=0; $t<$total; $t++){
            echo"<tr>\n\t<td>".$this->validPoints[$t]['passedAddress']."</td>\n</tr>\n";
        }
        echo "</table>\n";
        }
      if ($displayType == "list"){
        echo "<ul id=\"".$css_id."\">\n";
      for ($lst=0; $lst<$total; $lst++){
        echo "<li>".$this->validPoints[$lst]['passedAddress']."</li>\n";
        }
        echo "</ul>\n";
       }
	}
/**
* @function     showInvalidPoints
* @param        $displayType:string
* @param        $css_id:string
* @returns      nothing
* @description  Displays either a table or a list of the address points that are invalid.
*               Mainly used for debugging shows only the points that are NOT on the map
*/
public function showInvalidPoints($displayType,$css_id){
      $total = count($this->invalidPoints);
      if ($displayType == "table"){
        echo "<table id=\"".$css_id."\">\n<tr>\n\t<td>Address</td>\n</tr>\n";
        for ($t=0; $t<$total; $t++){
            echo"<tr>\n\t<td>".$this->invalidPoints[$t]['passedAddress']."</td>\n</tr>\n";
        }
        echo "</table>\n";
        }
      if ($displayType == "list"){
        echo "<ul id=\"".$css_id."\">\n";
      for ($lst=0; $lst<$total; $lst++){
        echo "<li>".$this->invalidPoints[$lst]['passedAddress']."</li>\n";
        }
        echo "</ul>\n";
       }
	}
/**
* @function     setWidth
* @param        $width:int
* @returns      nothing
* @description  Sets the width of the map to be displayed
*/
public function setWidth($width){
		$this->mapWidth = $width;
	}
/**
* @function     setHeight
* @param        $height:int
* @returns      nothing
* @description  Sets the height of the map to be displayed
*/
public function setHeight($height){
		$this->mapHeight = $height;
	}
/**
* @function     setAPIkey
* @param        $key:string
* @returns      nothing
* @description  Stores the API Key acquired from Google
*/
public function setAPIkey($key){
		$this->apiKey = $key;
	}
	
/**
* @function     setAPIkey
* @param        $key:string
* @returns      nothing
* @description  Stores the API Key acquired from Google
*/
public function setGeocodeAPIkey($key){
		$this->geocode_apiKey = $key;
	}
	
/**
* @function     printGoogleJS
* @returns      nothing
* @description  Adds the necessary Javascript for the Google Map to function
*               should be called in between the html <head></head> tags
*/
public function printJS(){
		echo "\n<script src=\"http://maps.google.com/maps?file=api&v=2&key=".$this->apiKey."\" type=\"text/javascript\"></script>\n";
	}
/**
* @function     showMap
* @description  Displays the Google Map on the page
*/
public function showMap(){
		echo "\n<div id=\"map\" style=\"width: ".$this->mapWidth."px; height: ".$this->mapHeight."px\">\n</div>\n";
		echo "    <script type=\"text/javascript\">\n
    	function showmap(){
				//<![CDATA[\n
    		if (GBrowserIsCompatible()) {\n
      		var map = new GMap(document.getElementById(\"map\"));\n";
      		if (empty($this->centerMap)){
             echo "map.centerAndZoom(new GPoint(".$this->validPoints[0]['long'].",".$this->validPoints[0]['lat']."), ".$this->zoomLevel.");\n";
             }else{
               echo $this->centerMap;
               }
		    echo "}\n
			var icon = new GIcon();
			icon.image = \"http://labs.google.com/ridefinder/images/mm_20_red.png\";
			icon.shadow = \"http://labs.google.com/ridefinder/images/mm_20_shadow.png\";
			icon.iconSize = new GSize(12, 20);
			icon.shadowSize = new GSize(22, 20);
			icon.iconAnchor = new GPoint(6, 20);
			icon.infoWindowAnchor = new GPoint(5, 1);
			
			";
		if ($this->showControl){
          if ($this->controlType == 'small'){echo "map.addControl(new GSmallMapControl());\n";}
          if ($this->controlType == 'large'){echo "map.addControl(new GLargeMapControl());\n";}
		
			}
		if ($this->showType){
		echo "map.addControl(new GMapTypeControl());\n";
		}
	
    $numPoints = count($this->validPoints);
    for ($g = 0; $g<$numPoints; $g++){
        echo "var point".$g." = new GPoint(".$this->validPoints[$g]['long'].",".$this->validPoints[$g]['lat'].")\n;
              var marker".$g." = new GMarker(point".$g.");\n
              map.addOverlay(marker".$g.")\n
              GEvent.addListener(marker".$g.", \"click\", function() {\n";
              if ($this->validPoints[$g]['htmlMessage']!=null){
              echo "marker".$g.".openInfoWindowHtml(\"".$this->validPoints[$g]['htmlMessage']."\");\n";
              }else{
             echo "marker".$g.".openInfoWindowHtml(\"".$this->validPoints[$g]['passedAddress']."\");\n";
                }
              echo "});\n";
	}
		echo "    	//]]>\n
		}
		window.onload = showmap;
    	</script>\n";
		}
 ///////////THIS BLOCK OF CODE IS FROM Roger Veciana's CLASS (assoc_array2xml) OBTAINED FROM PHPCLASSES.ORG//////////////
  function xml2array($xml){
		$this->depth=-1;
		$this->xml_parser = xml_parser_create();
		xml_set_object($this->xml_parser, $this);
		xml_parser_set_option ($this->xml_parser,XML_OPTION_CASE_FOLDING,0);//Don't put tags uppercase
		xml_set_element_handler($this->xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($this->xml_parser,"characterData");
		xml_parse($this->xml_parser,$xml,true);
		xml_parser_free($this->xml_parser);
		return $this->arrays[0];

    }
    function startElement($parser, $name, $attrs){
		   $this->keys[]=$name; 
		   $this->node_flag=1;
		   $this->depth++;
     }
    function characterData($parser,$data){
       $key=end($this->keys);
       $this->arrays[$this->depth][$key]=$data;
       $this->node_flag=0; 
     }
    function endElement($parser, $name)
     {
       $key=array_pop($this->keys);
       if($this->node_flag==1){
         $this->arrays[$this->depth][$key]=$this->arrays[$this->depth+1];
         unset($this->arrays[$this->depth+1]);
       }
       $this->node_flag=1;
       $this->depth--;
     }
//////////////////END CODE FROM Roger Veciana's CLASS (assoc_array2xml) /////////////////////////////////


}//End Of Class


?>