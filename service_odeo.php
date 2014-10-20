<?
require_once ("service.php");
require_once ("class_validateSyntax.php");
require_once ("class_unicode.php");

/*
CLASE : service_odeo
DETALLES: provee los servicios necesarios para leer busquedas depodcasts de odeo (resultados de yahoo search, en formato json, son reparseados para el servicio de detalles de odeo), y los convierte a formato de repositorio FindJira
*/

class service_odeo extends service 
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
		$query_params = " inurl:odeo.com/audio/";
		
		$service_params .= "&start=".$this->resultado_inicial."&results=".$this->resultados_por_pagina."&similar_ok=1";
		
		$out = $URL.urlencode($this->query.$query_params).$service_params;
		
		return $out;
	}

	
	//FUNCIONES DE BUSQUEDA
	// usa yahoo api para bscar dentro de paginas de odeo y obtine resultados de odeo con json
	
	/*
	FUNCION : audio_search
	DETALLES: setea el url para la busqueda de web de yahoo, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a	
	*/
	
	function audio_search()
	{
		$SERVICE_URL = "http://search.yahooapis.com/WebSearchService/V1/webSearch?appid=20062007&output=json&query=";
		
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
	NOTAS: como la busqueda es provista por yahoo, cada raw_url de yahoo es utilizado para un nuevo request
	el  segundo request se realiza en los servidores de odeo y obtiene en formato json los detalles de cada resultado de podcast
	los resultados de odeo son los almacenados en el arreglo para ser añadido al repositorio
	*/
	
	function audio_parse()
	{
		//respuesta_raw será recorrida y transformada al formato findjira	
		
		$this->respuesta_json = utf8_encode($this->respuesta_raw);
		
		$this->respuesta_php = json_decode($this->respuesta_json,true);
		
		$i = $this->resultado_inicial;
		
		if($this->respuesta_php["ResultSet"]["Result"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		foreach ($this->respuesta_php["ResultSet"]["Result"] as &$value)
		{
		    $this->respuesta_info = $this->buscar($this->obtener_odeo_url($this->obtener_odeo_id($value["Url"])));
		    $this->respuesta_info = utf8_encode($this->respuesta_info);		
			$this->respuesta_info = json_decode($this->respuesta_info,true);
			
			//servicio de crop				
			
			if ($this->respuesta_info["image_url"] !=null)
			{
				$thumbnail = 	$this->FINDJIRA_PATH."util_recortar.php?w=".$this->thumbnail_width.
								"&h=".$this->thumbnail_height.
								"&src=".urlencode($this->respuesta_info["image_url"]).
								"&ext=".$this->obtener_extension($this->respuesta_info["image_url"]);
			}
			else
			{
				$thumbnail = 	$this->FINDJIRA_PATH."util_recortar.php?w=".$this->thumbnail_width.
								"&h=".$this->thumbnail_height.
								"&src=".urlencode($this->obtener_web_thumbnail($this->respuesta_info["external_url"])).
								"&ext=jpg";							
			}

							
			$respuesta[] = array (
					    			"titulo" 		=> utf8_to_unicode_entities_encode($this->respuesta_info["title"]),
					    			"raw_url" 		=> $this->respuesta_info["external_url"],
					    			"display_url" 	=> $this->obtener_dominio($this->respuesta_info["external_url"]), 
									"container_url"	=> $this->obtener_container_odeo($this->respuesta_info["id"]),	
					    			"download_url"  => $this->respuesta_info["external_url"],
					    			"file_size" 	=> null,	
					    			"file_format" 	=> $this->obtener_extension($this->respuesta_info["external_url"]),	    			
									"duracion" 		=> $this->respuesta_info["duration"],									
									"resumen" 		=> utf8_to_unicode_entities_encode($value["Summary"]),
					    			"thumb_url" 	=> $this->respuesta_info["image_url"],
									"crop_url"		=> $thumbnail,
					    			"embed_thumb"   => $this->obtener_embed_image($thumbnail,$this->thumbnail_width,$this->thumbnail_height),
					    			"embed" 		=> $this->obtener_embed_mp3_player($this->respuesta_info["external_url"],$this->embed_width,$this->embed_height),
					    			"origen" 		=> "odeo",
					    			"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
					    			);				    			
					    			
			$i++;
		}
		
		return $respuesta;
	}
	
	//recibe el nombre de un parametro que exista dentro de un url y retorna el contenido del mismo 
	
	/*
	FUNCION : obtener_odeo_id
	DETALLES: permite obtener el id de un podcast del url de odeo
	RECIBE: el url completo
	RETORNA: el id del video
	*/
	
	function obtener_odeo_id($URL)
	{		
		$param_str = "audio/";
		$next_param_str = "/";
		
		$pos_param = strpos($URL,$param_str);
		if ($pos_param === false) //no existe
		{
			return " param no encontrado, revisar sintaxis ";
		}
		
		$param_value= substr($URL,$pos_param + strlen($param_str));		
		
		$pos_next_param = strpos($param_value,$next_param_str);
		
		if ($pos_next_param > 0)
		{			
			$param_value= substr($param_value,0,$pos_next_param );
		}

		return $param_value;	
	}
	
	/*
	FUNCION : obtener_odeo_url
	DETALLES: permite obtener el url para la peticion de los detalles de cada archivo de audio en formato json
	RECIBE: el url completo
	RETORNA: el id del video
	*/
	
	function obtener_odeo_url($id)
	{
		$URL = "http://odeo.com/audio/".$id."/json";
		return $URL;			
	}
	
	/*
	FUNCION : obtener_container_odeo
	DETALLES: permite obtener el url para la pagina en odeo de cada archivo de audio en formato json
	RECIBE: el url completo
	RETORNA: el id del video
	*/
	
	function obtener_container_odeo($id)
	{
		$URL = "http://odeo.com/audio/".$id."/view";
		return $URL;			
	}
	
	/*
	FUNCION : obtener_embed_odeo
	DETALLES: ensambla la estructura html necesaria para poder embeber audio de odeo, listo para ser impresos en codigo html
	esta funcion no esta siendo utilizada, pues se utiliza el player embebible default
	RECIBE: el id del video, y las dimensiones del reproductor
	RETORNA: el codigo web en una cadena
	*/
	
	function obtener_embed_odeo ($id, $width, $height)
	{	
		//default para este player es 322 x 54			
		$embed = "<embed src=\"http://odeo.com/flash/audio_player_black.swf\" quality=\"high\" width=\".$width.\" height=\"$height\" name=\"odeo_player_black\" align=\"middle\" allowScriptAccess=\"always\" wmode=\"transparent\"  type=\"application/x-shockwave-flash\" flashvars=\"type=audio&id=.$id.\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" /></embed>   		<br /><a style=\"font-size: 9px; padding-left: 110px; color: #f39; letter-spacing: -1px; text-decoration: none\" href=\"http://odeo.com/audio/.$id./view\">powered by <strong>ODEO</strong></a>";
		
		return $embed;
	}
} 

?>