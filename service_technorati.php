<?
require_once ("service.php");
require_once("class_xml2json.php");
require_once("class_unicode.php");
	
/*
CLASE : service_technorati
DETALLES: provee los servicios necesarios para leer busquedas de feeds en technorati, y los convierte a formato de repositorio FindJira
*/

class service_technorati extends service 
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
		
		$service_params .= "&start=".$this->resultado_inicial."&limit=".$this->resultados_por_pagina;
		
		$out = $URL.urlencode($this->query).$service_params;
		
		return $out;
	}


	
	//FUNCIONES DE BUSQUEDA
	
	/*
	FUNCION : blog_search
	DETALLES: setea el url para la busqueda de blogs, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a
	*/
	
	function blog_search()
	{
		$SERVICE_URL = "http://api.technorati.com/search?key=6bf0a6ff8f7ca38ec63519abd90b4fa3&query=";

		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->web_parse();				//web_parse utiliza respuesta_raw para transformarla
		
	}
	
	
	// FUNCIONES DE PARSING
	
	/*
	FUNCION : web_parse
	DETALLES: realiza la conversion del formato retornado por el servicio a formato findjira
	RECIBE: n/a
	RETORNA:  arreglo php serializado listo para ser añadido al repositorio
	*/	
	
	function web_parse()
	{
		//respuesta_raw será recorrida y transformada al formato findjira	
		
		$this->respuesta_json = xml2json::transformXmlStringToJson($this->respuesta_raw);
		
		$this->respuesta_json = utf8_encode($this->respuesta_json);		
		
		$this->respuesta_php = json_decode($this->respuesta_json,true);	
		
		$i = $this->resultado_inicial;
		
		if($this->respuesta_php["tapi"]["document"]["item"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		foreach ($this->respuesta_php["tapi"]["document"]["item"] as &$value)
		{
		    //servicio de crop			
			$thumbnail = 	"util_recortar.php?w=".$this->thumbnail_width.
							"&h=".$this->thumbnail_height.
							"&src=".urlencode($this->obtener_web_thumbnail($value["weblog"]["url"])).
							"&ext=jpg";
			
			$respuesta[] = array (
					    			"titulo" 		=> utf8_to_unicode_entities_encode($value["title"]),
					    			"raw_url" 		=> $this->obtener_URL_sin_ultimo_slash($value["permalink"]),
					    			"display_url" 	=> $value["weblog"]["url"], 
					    			"cache_url" 	=> null,
									"feed"			=> $value["weblog"]["rssurl"] != "" ? $value["weblog"]["rssurl"] : $value["weblog"]["atomurl"],
					    			"resumen" 		=> utf8_to_unicode_entities_encode($value["excerpt"]),
					    			"thumb_url" 	=> $this->obtener_web_thumbnail($value["weblog"]["url"]),	//el thumbnail será de la pagina principal del blog y mas no del permalink
									"crop_url"		=> $thumbnail,
					    			"embed_thumb"   => $this->obtener_embed_image($this->obtener_web_thumbnail($value["weblog"]["url"]),$this->thumbnail_width,$this->thumbnail_height),
					    			"origen" 		=> "technorati",
					    			"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
					    			);
			$i++;
		}
		
		return $respuesta;
	}
} 

?>