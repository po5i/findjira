<?
require_once ("service.php");
require_once ("class_validateSyntax.php");
require_once ("class_unicode.php");

/*
CLASE : service_yahoo
DETALLES: provee los servicios necesarios para leer busquedas de imagenes, videos y web de yahoo, y los convierte a formato de repositorio FindJira
*/

class service_yahoo extends service 
{

	// parametros de busqueda (opciones avanzadas del query de busqueda)
	var $site; 
	var $intitle;
	var $inurl;
	var $filetype;
	var $excluidos;
	
	/*
	FUNCION : set_site
	DETALLES: setea string para restringir busqueda a un sitio
	RECIBE: el string del sitio en cuestion
	RETORNA: n/a
	*/
	
	public  function set_site($str)
	{	
		if (validateUrlSyntax ($str))
			$this->site = $str;		
	}
	
	/*
	FUNCION : get_site
	DETALLES: obtiene string seteado previamente para restringir busqueda a un sitio
	RECIBE: n/a
	RETORNA: el string del sitio o parte del mismo
	*/
	
	public  function get_site()
	{
		return $this->site;
	}
	
	/*
	FUNCION : set_intitle
	DETALLES: setea string para restringir busqueda para buscar dentro del titulo de la pagina
	RECIBE: el string en cuestion
	RETORNA: n/a
	*/
	
	public  function set_intitle($str)
	{		
		$this->intitle = $str;
	}
	
	/*
	FUNCION : get_intitle
	DETALLES: obtiene  string para restringir busqueda para buscar dentro del titulo de la pagina
	RECIBE: n/a
	RETORNA: el string en cuestion
	*/
		
	public  function get_intitle()
	{
		return $this->intitle;
	}
	
	/*
	FUNCION : get_inurl
	DETALLES: obtiene string para restringir busqueda para buscar paginas con dicho string en su url
	RECIBE: n/a
	RETORNA: el string en cuestion
	*/
	
	public  function get_inurl()
	{
		return $this->inurl;
	}
	
	/*
	FUNCION : set_inurl
	DETALLES: setea  string para restringir busqueda para buscar paginas con dicho string en su url
	RECIBE:  el string en cuestion
	RETORNA: n/a
	*/
	
	function set_inurl($str)
	{		
		$this->inurl = $str;	
	}
	
	/*
	FUNCION : get_filetype
	DETALLES: obtiene string para restringir busqueda a ciertos tipos de archivo
	RECIBE:  n/a
	RETORNA: el string en cuestion
	*/
	
	public  function get_filetype()
	{
		return $this->filetype;
	}
	
	/*
	FUNCION : set_filetype
	DETALLES: setea  string para restringir busqueda a ciertos tipos de archivo
	RECIBE:  el string en cuestion
	RETORNA: n/a
	*/
	
	public  function set_filetype($str)
	{		
		$this->filetype = $str;
	}
	
	/*
	FUNCION : get_excluidos
	DETALLES: obtiene  string para eliminar cadenas de la busqueda
	RECIBE: n/a
	RETORNA:  el string en cuestion
	*/

	public  function get_excluidos()
	{
		return $this->excluidos;
	}
	
	/*
	FUNCION : set_excluidos
	DETALLES: setea  string para eliminar cadenas de la busqueda
	RECIBE: el string en cuestion
	RETORNA:  n/a
	*/
	
	public  function set_excluidos($str)
	{		
		$this->excluidos = $str;
		
	}
	
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
				
		if ($this->site != "") 									//TODO: validar  url
		{
			$query_params .= " site:".$this->site;			
		}
		if ($this->intitle != "") 
		{
			$query_params .= " intitle:".$this->intitle;			
		}
		if ($this->inurl != "") 
		{
			$query_params .= " inurl:".$this->inurl;				
		}
		if ($this->filetype != "") 
		{
			$query_params .= " format:".$this->filetype;
		}
		if ($this->excluidos != "") 
		{
			$query_params .= " -".$this->excluidos;					
		}
		
		$service_params .= "&start=".$this->resultado_inicial."&results=".$this->resultados_por_pagina;
		
		$out = $URL.urlencode($this->query.$query_params).$service_params;
		
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
		$SERVICE_URL = "http://search.yahooapis.com/WebSearchService/V1/webSearch?appid=20062007&output=json&query=";
		
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
		$SERVICE_URL = "http://search.yahooapis.com/WebSearchService/V1/webSearch?appid=20062007&output=json&query=";
		
		//elaborar el url adecuado para este servicio		
		
		$this->query." \"index of\"";
		$this->set_excluidos("htm -html -php -asp -txt -pls");
		
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
		$SERVICE_URL = "http://search.yahooapis.com/ImageSearchService/V1/imageSearch?appid=20062007&output=json&query=";
		
		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL,$site,$intitle,$inurl,$filetype,$excluidos);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->image_parse();				//web_parse utiliza respuesta_raw para transformarla
		
	}
	
	/*
	FUNCION : video_search
	DETALLES: setea el url para la busqueda de videos, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a
	*/
	
	function video_search()
	{
		$SERVICE_URL = "http://search.yahooapis.com/VideoSearchService/V1/videoSearch?appid=20062007&output=json&query=";
		
		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL,$site,$intitle,$inurl,$filetype,$excluidos);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->video_parse();				//web_parse utiliza respuesta_raw para transformarla
		
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
			//servicio de crop			
			$thumbnail = 	$this->FINDJIRA_PATH."util_recortar.php?w=".$this->thumbnail_width.
							"&h=".$this->thumbnail_height.
							"&src=".urlencode($this->obtener_web_thumbnail($value["Url"])).
							"&ext=jpg";
							
			$respuesta[] = array (
					    			"titulo" 		=> utf8_to_unicode_entities_encode($value["Title"]),
					    			"raw_url" 		=> $this->obtener_URL_sin_ultimo_slash($value["Url"]),
					    			"display_url" 	=> $value["DisplayUrl"], 
					    			"cache_url" 	=> $value["Cache"]["Url"],
					    			"feed"			=> null,
									"resumen" 		=> utf8_to_unicode_entities_encode($value["Summary"]),
					    			"thumb_url" 	=> $this->obtener_web_thumbnail($value["Url"]),
									"crop_url"		=> $thumbnail,
					    			"embed_thumb"   => $this->obtener_embed_image($this->obtener_web_thumbnail($value["Url"]),$this->thumbnail_width,$this->thumbnail_height),
					    			"origen" 		=> "yahoo",
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
		    //servicio de crop			
			$thumbnail = 	$this->FINDJIRA_PATH."util_recortar.php?w=".$this->thumbnail_width.
							"&h=".$this->thumbnail_height.
							"&src=".urlencode($value["Thumbnail"]["Url"]).
							"&ext=jpg";
			
			$respuesta[] = array (
					    			"titulo" 		=> $value["Title"],					    			
					    			"display_url" 	=> $value["DisplayUrl"], 
					    			"raw_url" 		=> $value["Url"],
					    			"container_url"	=> $value["RefererUrl"],
					    			"file_size" 	=> $value["FileSize"],
									"file_format" 	=> $this->obtener_extension($value["Url"]),
									"width" 		=> $value["Width"],
									"height" 		=> $value["Height"],
									"thumb_url" 	=> $value["Thumbnail"]["Url"],
									"crop_url"		=> $thumbnail,
									"embed_thumb" 	=> $this->obtener_embed_image($thumbnail,$this->thumbnail_width,$this->thumbnail_height),
					    			"origen" 		=> "yahoo",
					    			"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
					    			);
			$i++;
		}
		
		return $respuesta;
		
	}
	
	/*
	FUNCION : video_parse
	DETALLES: realiza la conversion del formato retornado por el servicio a formato findjira
	RECIBE: n/a
	RETORNA:  arreglo php serializado listo para ser añadido al repositorio
	*/
	
	function video_parse()
	{
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
		    //servicio de crop			
			$thumbnail = 	$this->FINDJIRA_PATH."util_recortar.php?w=".$this->thumbnail_width.
							"&h=".$this->thumbnail_height.
							"&src=".urlencode($value["Thumbnail"]["Url"]).
							"&ext=jpg";
			
			$respuesta[] = array (
									"titulo" 		=> $value["Title"],					    			
					    			"display_url" 	=> $this->obtener_dominio($value["Url"]), 
					    			"raw_url" 		=> $this->obtener_URL_sin_ultimo_slash($value["Url"]),
					    			"container_url"	=> $value["RefererUrl"],
					    			"download_url"  => $value["Url"],
					    			"file_size" 	=> $value["FileSize"],
									"file_format" 	=> $this->obtener_extension($value["Url"]),
									"width" 		=> $value["Width"],
									"height" 		=> $value["Height"],
									"duracion" 		=> $value["Duration"],
									"resumen" 		=> $value["Summary"],
									"thumb_url" 	=> $value["Thumbnail"]["Url"],
									"crop_url"		=> $thumbnail,
									"embed_thumb" 	=> $this->obtener_embed_image($thumbnail,$this->thumbnail_width,$this->thumbnail_height),
									"embed" 		=> null, //aqui no se como hacer... no hay como saber cuando viene de Yahoo Video o no, ni como filtrar busquedas solo a yahoo video
					    			"origen" 		=> "yahoo",
					    			"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
					    			);
			$i++;
		}
		
		return $respuesta;
	}
} 

?>