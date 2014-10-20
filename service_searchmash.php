<?
require_once ("service.php");
require_once ("class_validateSyntax.php");
require_once ("class_unicode.php");

/*
CLASE : service_searchmash
DETALLES: provee los servicios necesarios para leer busquedas de imagenes, videos, blogs y web de searchmash (resultados de google, en formato json), y los convierte a formato de repositorio FindJira
*/

class service_searchmash extends service 
{
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
			$query_params .= " filetype:".$this->filetype;
		}
		if ($this->excluidos != "") 
		{
			$query_params .= " -".$this->excluidos;					
		}
		
		$service_params .= "?i=".$this->resultado_inicial."&n=".$this->resultados_por_pagina;
		
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
		$SERVICE_URL = "http://www.searchmash.com/results/";
		
		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira

		$this->respuesta_findjira = $this->web_parse();				//web_parse utiliza respuesta_raw para transformarla
		
	}
	
	/*
	FUNCION : image_search
	DETALLES: setea el url para la busqueda de imagenes, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a
	*/
	
	function image_search()
	{
		$SERVICE_URL = "http://www.searchmash.com/results/images:";
		
		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
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
		$SERVICE_URL = "http://www.searchmash.com/results/video:";
		
		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->video_parse();				//web_parse utiliza respuesta_raw para transformarla
		
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
		$SERVICE_URL = "http://www.searchmash.com/results/blogs:";
		
		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->web_parse();				//web_parse utiliza respuesta_raw para transformarla
		
	}
	
	/*
	FUNCION : blog_search
	DETALLES: setea el url para la busqueda de blogs, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a
	NOTAS: el formato de wiki_search es el mismo que el de web, por ende, se llama a web_parse
	*/
	
	function wiki_search()
	{
		$SERVICE_URL = "http://www.searchmash.com/results/";
		
		//elaborar el url adecuado para este servicio		
		
		$this->set_inurl("wiki");
		
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
		$SERVICE_URL = "http://www.searchmash.com/results/";
		
		//elaborar el url adecuado para este servicio		
		
		$this->set_intitle("index.of");
		$this->set_excluidos("html|htm|php|asp|txt|pls");
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->web_parse();		
	
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
							"&src=".urlencode($this->obtener_web_thumbnail($value["rawUrl"])).
							"&ext=jpg";
			
			$respuesta[] = array (
					    			"titulo" 		=> utf8_to_unicode_entities_encode($value["title"]),
					    			"raw_url" 		=> $this->obtener_URL_sin_ultimo_slash($value["rawUrl"]),
					    			"display_url" 	=> $value["site"], //displayUrl es sin www en searchmash
					    			"cache_url" 	=> $value["cacheUrl"],//para blogs este campo es null
					    			"feed"			=> null,
									"resumen" 		=> utf8_to_unicode_entities_encode($value["snippet"]),
					    			"thumb_url" 	=> $this->obtener_web_thumbnail($value["rawUrl"]),
									"crop_url"		=> $thumbnail,
					    			"embed_thumb"   => $this->obtener_embed_image($this->obtener_web_thumbnail($value["rawUrl"]),$this->thumbnail_width,$this->thumbnail_height),
					    			"origen" 		=> "google",
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
							"&src=".urlencode($value["thumbnailUrl"]).
							"&ext=jpg";
			
			$respuesta[] = array (
									"titulo" 		=> $this->obtener_filename($value["imageUrl"]),
									"display_url" 	=> $value["site"],
									"raw_url" 		=> $value["imageUrl"],
									"container_url"	=> $value["url"],
									"file_size" 	=> null,
									"file_format" 	=> $this->obtener_extension($value["imageUrl"]),
									"width" 		=> $value["imageWidth"],
									"height" 		=> $value["imageHeight"],
									"thumb_url" 	=> $value["thumbnailUrl"],
									"crop_url"		=> $thumbnail,
									"embed_thumb" 	=> $this->obtener_embed_image($thumbnail,$this->thumbnail_width,$this->thumbnail_height),
									"origen" 		=> "google",
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
							"&src=".urlencode($value["thumbnailUrl"]).
							"&ext=jpg";
							
			$from_google = true;
							
			if ( strpos($value["videoUrl"],"google") === false) // no es de google
			{
				//no es google, sino youtube, etc
				$from_google = false;									
			}
			
		
			
			$respuesta[] = array (
									"titulo" 		=> utf8_to_unicode_entities_encode($value["title"]),
									"display_url" 	=> $value["site"],
									"raw_url" 		=> $this->obtener_URL_sin_ultimo_slash($value["videoUrl"]),
									"container_url"	=> $value["url"],
									"download_url" 	=> ($from_google)? ($this->obtener_javimoya_dl($this->obtener_formato_dl($this->obtener_id_google_video($value["videoUrl"]) ) ))  : ($this->obtener_javimoya_dl($value["videoUrl"]) ),
									"file_size" 	=> null,
									"file_format" 	=> "flv",
									"width" 		=> $this->embed_width,
									"height" 		=> $this->embed_height,
									"duracion" 		=> $value["duration"],
									"resumen" 		=> utf8_to_unicode_entities_encode($value["snippet"]),
									"thumb_url" 	=> $value["thumbnailUrl"],
									"crop_url"		=> $thumbnail,
									"embed_thumb" 	=> $this->obtener_embed_image($thumbnail,$this->thumbnail_width,$this->thumbnail_height),
									"embed" 		=> ($from_google) ? ( $this->obtener_embed_google_video($this->obtener_id_google_video($value["videoUrl"]),$this->embed_width,$this->embed_height) ) : ( $this->obtener_embed_youtube_url($value["videoUrl"],$this->embed_width,$this->embed_height) ) ,
									"origen" 		=> "google",
									"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
									);
			$i++;
		}
				
		return $respuesta;
	}
	
	/*
	FUNCION : obtener_formato_dl
	DETALLES: obtiene el url necesario para poder descargar videos desde google video
	RECIBE: id del video a concatenar con su url
	RETORNA:  cadena completa, con el url y el id del video
	*/
	
	function obtener_formato_dl($id)
	{
		$video_page_URL = "http://video.google.com/videoplay?docid=";
		return $video_page_URL.$id;
	}
	
	/*
	FUNCION : file_parse
	DETALLES: permitira parsear archivos retornados para su descarga, no soportada por searchmash
	RECIBE: n/a
	RETORNA: n/a
	*/

	function file_parse()
	{
	//aquí el código del método
	}
	

	/*
	FUNCION : obtener_id_google_video
	DETALLES: permite obtener el id de un video del url de google video
	RECIBE: el url completo
	RETORNA: el id del video
	*/
	
	function obtener_id_google_video($URL)
	{
		$doc_id_str = "docId"; // sin = despues ni un & o ? antes
		
		$video_id = $this->obtener_url_param($URL,$doc_id_str); // case sensitive			
		
		return $video_id;
	}
	
	//externa: 
	
	/*
	FUNCION : obtener_embed_google_video
	DETALLES: ensambla la estructura html necesaria para poder embeber videos de google video, listos para ser impresos en codigo html
	RECIBE: el id del video, y las dimensiones del reproductor
	RETORNA: el codigo web en una cadena
	*/
	
	function obtener_embed_google_video ($id, $width, $height)
	{		
		$embed = "<embed style=\"width:".$width."px; height:".$height."px;\" id=\"VideoPlayback\" type=\"application/x-shockwave-flash\" src=\"http://video.google.com/googleplayer.swf?docId=".$id."&hl=en\" flashvars=\"\"> </embed>";					
		
		return $embed;
	}
	
	
		//UTILIDADES	
	
	/*
	FUNCION : obtener_embed_youtube_url
	DETALLES: ensambla la estructura html necesaria para poder embeber videos de youtube , listos para ser impresos en codigo html
	RECIBE: el url del video, y las dimensiones del reproductor
	RETORNA: el codigo web en una cadena
	*/	
	
	function obtener_embed_youtube_url($url, $width, $height)
	{
		//var_dump($id);
		$embed = "<object width=\"$width\" height=\"$height\"><param name=\"movie\" value=\"$url\"></param><param name=\"wmode\" value=\"window\"></param><embed src=\"$url\" type=\"application/x-shockwave-flash\" wmode=\"window\" width=\"$width\" height=\"$height\"></embed></object>";
		return $embed;
	}		
	
} 

?>