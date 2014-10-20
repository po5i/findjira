<?
require_once ("service.php");
require_once("class_xml2json.php");
require_once("class_unicode.php");
	
/*
CLASE : service_live 
DETALLES: provee los servicios necesarios para leer busquedas de imagenes,  blogs y web de live , y los convierte a formato de repositorio FindJira
*/
	
class service_live extends service 
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
		
		$service_params .= "&first=".$this->resultado_inicial;
		
		$out = $URL.urlencode($this->query).$service_params;
		
		return $out;
	}


	
	//FUNCIONES DE BUSQUEDA
	
	/*
	FUNCION : web_search
	DETALLES: setea el url para la busqueda de sitios, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a
	*/
	
	function web_search()
	{
		$SERVICE_URL = "http://search.live.com/results.aspx?format=xml&q=";

		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->web_parse();				//web_parse utiliza respuesta_raw para transformarla
		
	}
	
	/*
	FUNCION : indexof_search
	DETALLES: setea el url para la busqueda, setea los parametros necesarios para restringir la busqueda a directorios web, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a
	NOTAS: el formato de wiki_search es el mismo que el de web, por ende, se llama a web_parse
	*/	
	
	function indexof_search()
	{
		$SERVICE_URL = "http://search.live.com/results.aspx?format=xml&q=";
		
		//elaborar el url adecuado para este servicio		
		
		$this->query." \"index of\" -php -asp -txt -pls";
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->web_parse();	
	
	}
	
	/*
	FUNCION : image_search
	DETALLES: setea el url para la busqueda de imagenes, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a
	*/
	
	function image_search()
	{

		$SERVICE_URL = "http://search.live.com/images/results.aspx?format=xml&q=";
		
		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->image_parse();				//web_parse utiliza respuesta_raw para transformarla
		
	}
	
	/*
	FUNCION : blog_search
	DETALLES: setea el url para la busqueda de blogs, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a
	NOTAS: el formato de blog_search es el mismo que el de web, por ende, se llama a web_parse
	*/
	
	function blog_search()
	{

		$SERVICE_URL = "http://search.live.com/feeds/results.aspx?format=xml&q=";
		
		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->blog_parse();				//web_parse utiliza respuesta_raw para transformarla
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
		
		if($this->respuesta_php["searchresult"]["documentset"] == "")
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		if($this->respuesta_php["searchresult"]["documentset"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		//indice de los resultados	
		$monarch_key = $this->obtener_monarch_key($this->respuesta_php["searchresult"]["documentset"],10);
		
		if($monarch_key == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		if ($this->respuesta_php["searchresult"]["documentset"][$monarch_key]["document"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;			
		}
		
		foreach ($this->respuesta_php["searchresult"]["documentset"][$monarch_key]["document"] as &$value)
		{
		    //servicio de crop			
			$thumbnail = 	$this->FINDJIRA_PATH."util_recortar.php?w=".$this->thumbnail_width.
							"&h=".$this->thumbnail_height.
							"&src=".urlencode($this->obtener_web_thumbnail($value["url"])).
							"&ext=jpg";
			
			$respuesta[] = array (
					    			"titulo" 		=> utf8_to_unicode_entities_encode($value["title"]),
					    			"raw_url" 		=> $this->obtener_URL_sin_ultimo_slash($value["url"]),
					    			"display_url" 	=> $value["captionurl"], 
					    			"cache_url" 	=> null,
					    			"feed"			=> null,
									"resumen" 		=> utf8_to_unicode_entities_encode($value["desc"]),
					    			"thumb_url" 	=> $this->obtener_web_thumbnail($value["url"]),
									"crop_url"		=> $thumbnail,
					    			"embed_thumb"   => $this->obtener_embed_image($this->obtener_web_thumbnail($value["url"]),$this->thumbnail_width,$this->thumbnail_height),
					    			"origen" 		=> "live",
					    			"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
					    			);
			$i++;
		}
		
		return $respuesta;

	}
	
	/*
	FUNCION : image_parse
	DETALLES: realiza la conversion del formato retornado por el servicio a formato findjira
	RECIBE: n/a
	RETORNA:  arreglo php serializado listo para ser añadido al repositorio
	*/
	
	function image_parse()
	{
		$this->respuesta_json = xml2json::transformXmlStringToJson($this->respuesta_raw);
		
		$this->respuesta_json = utf8_encode($this->respuesta_json);		
		
		$this->respuesta_php = json_decode($this->respuesta_json,true);	
		
		$i = $this->resultado_inicial;
		
		if($this->respuesta_php["searchresult"]["documentset"] == "")
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		if($this->respuesta_php["searchresult"]["documentset"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		
		//indice de los resultados	
		$monarch_key = $this->obtener_monarch_key($this->respuesta_php["searchresult"]["documentset"],1);
		
		if($monarch_key == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		if($this->respuesta_php["searchresult"]["documentset"][$monarch_key]["document"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}	

		
		foreach ($this->respuesta_php["searchresult"]["documentset"][$monarch_key]["document"] as &$value)
		{		
			//servicio de crop			
			$thumbnail = 	$this->FINDJIRA_PATH."util_recortar.php?w=".$this->thumbnail_width.
							"&h=".$this->thumbnail_height.
							"&src=".urlencode($value["thumbnailurl"]).
							"&ext=jpg";
			
			$respuesta[] = array (
									"titulo" 		=> $this->obtener_filename($value["imageurl"]),
									"display_url" 	=> $value["displayurl"],
									"raw_url" 		=> $value["imageurl"],
									"container_url"	=> $value["url"],
									"file_size" 	=> $value["imagefilesize"],
									"file_format" 	=> $this->obtener_extension($value["imageurl"]),
									"width" 		=> $value["width"],
									"height" 		=> $value["height"],
									"thumb_url" 	=> $value["thumbnailurl"],
									"crop_url"		=> $thumbnail,
									"embed_thumb" 	=> $this->obtener_embed_image($thumbnail,$this->thumbnail_width,$this->thumbnail_height),
									"origen" 		=> "live",
									"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
									);
			$i++;
		}		
		
		return $respuesta;
		
	}
	
	/*
	FUNCION : blog_parse
	DETALLES: realiza la conversion del formato retornado por el servicio a formato findjira
	RECIBE: n/a
	RETORNA:  arreglo php serializado listo para ser añadido al repositorio
	*/
	
	function blog_parse()
	{
		//respuesta_raw será recorrida y transformada al formato findjira	
		
		$this->respuesta_json = xml2json::transformXmlStringToJson($this->respuesta_raw);
		
		$this->respuesta_json = utf8_encode($this->respuesta_json);		
		
		$this->respuesta_php = json_decode($this->respuesta_json,true);	
		
		$i = $this->resultado_inicial;
		
		if($this->respuesta_php["searchresult"]["documentset"] == "")
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		if($this->respuesta_php["searchresult"]["documentset"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		//indice de los resultados		
		$monarch_key = $this->obtener_monarch_key($this->respuesta_php["searchresult"]["documentset"],9);
		
		if($monarch_key == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		if ($this->respuesta_php["searchresult"]["documentset"][$monarch_key]["document"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;			
		}
		
		foreach ($this->respuesta_php["searchresult"]["documentset"][$monarch_key]["document"] as &$value)
		{
		    //servicio de crop			
			$thumbnail = 	$this->FINDJIRA_PATH."util_recortar.php?w=".$this->thumbnail_width.
							"&h=".$this->thumbnail_height.
							"&src=".urlencode($this->obtener_web_thumbnail($this->obtener_dominio($value["url"]))).
							"&ext=jpg";
						
			$respuesta[] = array (
					    			"titulo" 		=> utf8_to_unicode_entities_encode($value["title"]),
					    			"raw_url" 		=> $this->obtener_URL_sin_ultimo_slash($value["url"]),
					    			"display_url" 	=> $value["captionurl"], 
					    			"cache_url" 	=> null,
					    			"feed"			=> $value["url"],	//igual que raw_url ya que live no da el permalink
									"resumen" 		=> utf8_to_unicode_entities_encode($value["desc"]),
					    			"thumb_url" 	=> $this->obtener_web_thumbnail($this->obtener_dominio($value["url"])),
									"crop_url"		=> $thumbnail,
					    			"embed_thumb"   => $this->obtener_embed_image($this->obtener_web_thumbnail($this->obtener_dominio($value["url"])),$this->thumbnail_width,$this->thumbnail_height),
					    			"origen" 		=> "live",
					    			"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
					    			);
			$i++;
		}
		
		return $respuesta;

	}
	
	//externa: para encontrar el indice de FEDERATOR_MONARCH exclusivo de live	
	
	/*
	FUNCION : obtener_monarch_key
	DETALLES: obtiene la clave  para extraer resultados de los servicios de live
	RECIBE: el arreglo de datos al cual estraer la clave
	RETORNA: la clave en cuestion
	*/
	
	function obtener_monarch_key($array,$default_key)
	{		
		$monarch_key = $default_key;
		
		while (list($key, $val) = each($array))
		{
		    if ($val["@attributes"]["source"] == "FEDERATOR_MONARCH") 
			{
				$monarch_key = $key;
				break;
			}
		}
		return $monarch_key;
	}
} 

?>