<?
require_once ("service.php");
require_once("class_xml2json.php");
require_once("class_unicode.php");

/*
CLASE : service_youtube
DETALLES: provee los servicios necesarios para leer busquedas de videos de youtube (resultados subconjuntos de google), y los convierte a formato de repositorio FindJira
*/
	
class service_youtube extends service 
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
				
		$num_pagina = $this->resultado_inicial * $this->resultados_por_pagina;	//como tenemos el indice del resultado inicial y no el # de pagina, calculamos	
		
		
		$service_params .= "&page=".$this->numero_pagina."&per_page=".$this->resultados_por_pagina;
		
		$out = $URL.urlencode($this->query.$query_params).$service_params; //.$service_params
		
		return $out;
	}


	
	//FUNCIONES DE BUSQUEDA
	
	/*
	FUNCION : video_search
	DETALLES: setea el url para la busqueda de videos, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a
	*/
	
	function video_search()
	{
		$SERVICE_URL = "http://www.youtube.com/api2_rest?method=youtube.videos.list_by_tag&dev_id=cShLkhjAbLs&tag=";
		
		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
				
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
				
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->video_parse();				//web_parse utiliza respuesta_raw para transformarla
		
	}
		
	
	// FUNCIONES DE PARSING
	
	/*
	FUNCION : video_parse
	DETALLES: realiza la conversion del formato retornado por el servicio a formato findjira
	RECIBE: n/a
	RETORNA:  arreglo php serializado listo para ser añadido al repositorio
	*/
	
	function video_parse()
	{
		$this->respuesta_json = xml2json::transformXmlStringToJson($this->respuesta_raw);
		
		$this->respuesta_json = utf8_encode($this->respuesta_json);		
		
		$this->respuesta_php = json_decode($this->respuesta_json,true);	
		
		$i = $this->resultado_inicial;
		
		if($this->respuesta_php["ut_response"]["video_list"]["video"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		foreach ($this->respuesta_php["ut_response"]["video_list"]["video"] as &$value)
		{
		    //servicio de crop			
			$thumbnail = 	$this->FINDJIRA_PATH."util_recortar.php?w=".$this->thumbnail_width.
							"&h=".$this->thumbnail_height.
							"&src=".urlencode($value["thumbnail_url"]).
							"&ext=jpg";
							
			$respuesta[] = array (
					    			"titulo" 		=> utf8_to_unicode_entities_encode($value["title"]),					    			
					    			"display_url" 	=> $this->obtener_dominio($value["url"]),
					    			"raw_url" 		=> $this->obtener_URL_sin_ultimo_slash($value["url"]),
					    			"container_url"	=> $this->obtener_container_youtube($value["id"]),	
					    			"download_url"  => $this->obtener_javimoya_dl($value["url"]),
					    			"file_size" 	=> null,	
					    			"file_format" 	=> "flv",	    			
					    			"width" 		=> $this->embed_width,
									"height" 		=> $this->embed_height,
									"duracion" 		=> $value["length_seconds"],
									"resumen" 		=> utf8_to_unicode_entities_encode($value["description"]),
									"thumb_url" 	=> $value["thumbnail_url"],
									"crop_url"		=> $thumbnail,
									"embed_thumb" 	=> $this->obtener_embed_image($thumbnail,$this->thumbnail_width,$this->thumbnail_height),
									"embed" 		=> $this->obtener_embed_youtube($this->obtener_url_param($value["url"],"v"),$this->embed_width,$this->embed_height),
					    			"origen" 		=> "youtube",
					    			"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
					    			);
			$i++;
		}
		
		return $respuesta;
	}
	
	//UTILIDADES	
	
	/*
	FUNCION : obtener_embed_youtube
	DETALLES: ensambla la estructura html necesaria para poder embeber videos de youtube , listos para ser impresos en codigo html
	RECIBE: el id del video, y las dimensiones del reproductor
	RETORNA: el codigo web en una cadena
	*/	
	
	function obtener_embed_youtube($id, $width, $height)
	{
		//var_dump($id);
		$embed = "<object width=\"$width\" height=\"$height\"><param name=\"movie\" value=\"http://www.youtube.com/v/$id\"></param><param name=\"wmode\" value=\"opaque\"></param><embed src=\"http://www.youtube.com/v/$id\" type=\"application/x-shockwave-flash\" wmode=\"opaque\" width=\"$width\" height=\"$height\"></embed></object>";
		return $embed;
	}	

	/*
	FUNCION : obtener_container_youtube
	DETALLES: crea el url necesario para la pagina que contiene el video de youtube
	RECIBE: el id del video
	RETORNA: el codigo web en una cadena
	*/	
	
	function obtener_container_youtube($id)
	{
		$URL = "http://www.youtube.com/watch?v=".$id;
		return $URL;
	}
} 

?>