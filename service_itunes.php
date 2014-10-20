<?
require_once ("service.php");
require_once ("class_validateSyntax.php");

/*
CLASE : service_itunes
DETALLES: provee los servicios necesarios para leer busquedas de imagenes, videos, blogs y web de searchmash (resultados de google, en formato json), y los convierte a formato de repositorio FindJira
*/

class service_itunes extends service 
{
	/*
	FUNCION : elaborar_url
	DETALLES: crea el url de busqueda listo para crear un html get, con los parametros necesarios
	RECIBE: el url del servicio para el cual se realizara la busqueda
	RETORNA:  el url completo listo para hacer el get y obtener la pagina de respuesta
	*/
	
	function elaborar_url($URL)
	{	
		$service_params = "";
		$query_params = "";
		
		$out = $URL.urlencode('&term='.$this->query.$query_params.'&entity=podcast&country=us&limit=40');
		
		return $out;
	}

	
	//FUNCIONES DE BUSQUEDA
	
	/*
	FUNCION : audio_search
	DETALLES: setea el url para la busqueda de podcasts, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a
	*/
	
	function audio_search()
	{
		//$SERVICE_URL = "http://www.apple.com/itunes/scripts/itmsSearch.php?searchTerm=";
		$SERVICE_URL = "http://www.apple.com/global/scripts/ajax_proxy.php?s=12&r=";
		
		//elaborar el url adecuado para este servicio				
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->audio_parse();
	}
	
	// FUNCIONES DE PARSING
	
	/*
	FUNCION : audio_parse
	DETALLES: realiza la conversion del formato retornado por el servicio a formato findjira
	RECIBE: n/a
	RETORNA:  arreglo php serializado listo para ser añadido al repositorio
	*/
	
	function audio_parse()
	{
		//respuesta_raw será recorrida y transformada al formato findjira	
		
		$this->respuesta_json = utf8_encode($this->respuesta_raw);
		
		$this->respuesta_php = json_decode($this->respuesta_json,true);
		
		$i = $this->resultado_inicial;
		
		if($this->respuesta_php["results"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		foreach ($this->respuesta_php["results"] as &$value)
		{
			
		  	 //servicio de crop						    	
			$thumbnail = 	$this->FINDJIRA_PATH."util_recortar.php?w=".$this->thumbnail_width.
							"&h=".$this->thumbnail_height.
							"&src=".urlencode($value["artworkUrl100"]).
							"&ext=jpg";
							
			$respuesta[] = array (
					    			"titulo" 		=> $value["itemName"],
					    			"raw_url" 		=> $value["previewUrl"],
					    			"display_url" 	=> $this->obtener_dominio($value["itemLinkUrl"]), 
									"container_url"	=> $value["itemLinkUrl"],	
					    			"download_url"  => $value["previewUrl"],
					    			"file_size" 	=> null,	
					    			"file_format" 	=> $this->obtener_extension($value["previewUrl"]),	    			
									"duracion" 		=> $value["trackTime"],									
									"resumen" 		=> "By: ".$value["artistName"].", Genre:  ".$value["primaryGenreName"],
					    			"thumb_url" 	=> $value["artworkUrl100"],
									"crop_url"		=> $thumbnail,
					    			"embed_thumb"   => $this->obtener_embed_image($thumbnail,$this->thumbnail_width,$this->thumbnail_height),
					    			"embed" 		=> $this->obtener_embed_mp3_player($value["previewUrl"],$this->embed_width,$this->embed_height),
					    			"origen" 		=> "itunes",
					    			"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
					    			);				    			
					    			
			$i++;
		}
		
		return $respuesta;
	}
} 

?>