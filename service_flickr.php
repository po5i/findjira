<?
require_once ("service.php");
require_once ("class_unicode.php");

/*
CLASE : service_flickr
DETALLES: provee los servicios necesarios para leer busquedas de imagenes, videos, blogs y web de searchmash (resultados de google, en formato json), y los convierte a formato de repositorio FindJira
*/

class service_flickr extends service 
{
	var $respuesta_info;
	var $respuesta_sizes;
	
	/*
	FUNCION : elaborar_url
	DETALLES: crea el url de busqueda listo para crear un html get, con los parametros necesarios
	RECIBE: el url del servicio para el cual se realizara la busqueda
	RETORNA:  el url completo listo para hacer el get y obtener la pagina de respuesta
	*/
	
	function elaborar_url($URL)
	{
		
		$service_params = "";		
		
		$num_pagina = $this->get_numero_pagina();
		
		$service_params .= "&per_page=".$this->resultados_por_pagina."&page=".$num_pagina;
		
		$out = $URL.urlencode($this->query).$service_params;

		return $out;
	}
	
	
	//FUNCIONES DE BUSQUEDA
	
	/*
	FUNCION : image_search
	DETALLES: setea el url para la busqueda de imagenes, elabora el url, lo convierte a formato findjira y lo almacena en las variables de clase
	RECIBE: n/a
	RETORNA:  n/a
	*/
	
	function image_search()
	{
		
		$SERVICE_URL = "http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=a6ca80c5d45a9a3bf2c99fa5aed9582e&nojsoncallback=1&format=json&text=";		
		
		//elaborar el url adecuado para este servicio
		
		$peticion_url = $this->elaborar_url($SERVICE_URL);
		
		//conectarse y obtener la respuesta en formato nativo
		
		$this->respuesta_raw = $this->buscar($peticion_url);		//TODO: validar una respuesta exitosa		
		
		//transformar la respuesta raw a finjira
		
		$this->respuesta_findjira = $this->image_parse();			//web_parse utiliza respuesta_raw para transformarla
	}
	
	/*
	FUNCION : image_parse
	DETALLES: realiza la conversion del formato retornado por el servicio a formato findjira
	RECIBE: n/a
	RETORNA:  arreglo php serializado listo para ser añadido al repositorio
	NOTAS: los servidores de flickr deben ser llamados varias veces para obtener los detalles necesarios, en varias respuestas, esto afecta la velocidad del servicio
	*/
	
	function image_parse()
	{
		$this->respuesta_json = utf8_encode($this->respuesta_raw);
		
		$this->respuesta_php = json_decode($this->respuesta_json,true);
		
		$i = $this->resultado_inicial;
		
		if($this->respuesta_php["photos"]["photo"] == null)
		{
			$respuesta[] = array ();
				return $respuesta;		
		}
		
		foreach ($this->respuesta_php["photos"]["photo"] as &$value)
		{
			//peticiones adicionales para obtencion de más metadata
			/*$this->respuesta_info = $this->buscar($this->obtener_flickr_info($value["id"],$value["secret"]));	//TODO: validar una respuesta exitosa			
			$this->respuesta_info = utf8_encode($this->respuesta_info);		
			$this->respuesta_info = json_decode($this->respuesta_info,true);*/
			
			//tamaños de la  imagen, 
			/*
			$this->respuesta_sizes = $this->buscar($this->obtener_flickr_sizes($value["id"]));					//TODO: validar una respuesta exitosa			
			$this->respuesta_sizes = utf8_encode($this->respuesta_sizes);		
			$this->respuesta_sizes = json_decode($this->respuesta_sizes,true);
			*/
			
			//servicio de crop			
			$thumbnail = 	$this->FINDJIRA_PATH."util_recortar.php?w=".$this->thumbnail_width.
							"&h=".$this->thumbnail_height.
							"&src=".urlencode($this->obtener_flickr_url($value["farm"],$value["server"],$value["id"],$value["secret"],"t","jpg")).
							"&ext=jpg";

			$respuesta[] = array (
									"titulo" 		=> utf8_to_unicode_entities_encode($value["title"]),	
									"display_url" 	=> "www.flickr.com",
									"raw_url" 		=> $this->obtener_flickr_url($value["farm"],$value["server"],$value["id"],$value["secret"],"m","jpg"),
									"container_url"	=> $this->obtener_container_url($value["owner"],$value["id"]),
									"file_size" 	=> null,
									"file_format" 	=> "jpg",	//$this->respuesta_info["photo"]["originalformat"],
									//"width" 		=> $this->respuesta_sizes["sizes"]["size"][5]["width"],
									//"height" 		=> $this->respuesta_sizes["sizes"]["size"][5]["height"],
									"width" 		=> $this->embed_width,
									"height" 		=> $this->embed_height,
									"thumb_url" 	=> $this->obtener_flickr_url($value["farm"],$value["server"],$value["id"],$value["secret"],"t","jpg"),
									"crop_url"		=> $thumbnail,
									"embed_thumb" 	=> $this->obtener_embed_image($thumbnail,$this->thumbnail_width,$this->thumbnail_height),
									"origen" 		=> "flickr",
									"indice_ser"	=> $i,
					    			"indice_rep"	=> $i
									);
			$i++;
		}
		
		return $respuesta;
	}

	
	/*
	FUNCION : obtener_flickr_info
	DETALLES: permite fabricar el url de peticion de informacion de la foto
	RECIBE: el id de la imagen, el secret para acceder a la base de datos
	RETORNA: el url de la peticion completa en una cadena
	*/

	function obtener_flickr_info($id,$secret)
	{
		$petition_url = "http://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key=a6ca80c5d45a9a3bf2c99fa5aed9582e&photo_id=".$id."&secret=".$secret."&nojsoncallback=1&format=json";
		return $petition_url;
	}
	
	
	/*
	FUNCION : obtener_flickr_sizes
	DETALLES: permite fabricar el url de peticion de tamaños de la foto
	RECIBE: el id de la imagen
	RETORNA: el url de la peticion completa en una cadena
	*/

	function obtener_flickr_sizes($id)
	{
		$petition_url = "http://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=a6ca80c5d45a9a3bf2c99fa5aed9582e&photo_id=".$id."&nojsoncallback=1&format=json";
		return $petition_url;
	}
	
	/*
	FUNCION : obtener_flickr_url
	DETALLES: permite fabricar el url de flickr
	RECIBE: el farm-id, server-id, el id de la imagen, secret, tamaño y extension deseada
	RETORNA: el url de la peticion completa en una cadena
	*/

	function obtener_flickr_url($farm,$server,$id,$secret,$size,$extension)
	{
		$photo_url = "http://farm".$farm.".static.flickr.com/".$server."/".$id."_".$secret."_".$size.".".$extension;
		return $photo_url;
	}
	
	/*
	FUNCION : obtener_container_url
	DETALLES: permite fabricar el url de flickr que encierra a la foto
	RECIBE: el id del owner o usuario, el id de la imagen
	RETORNA: el url de la peticion completa en una cadena
	*/

	function obtener_container_url($owner,$id)
	{
		$photo_url = "http://www.flickr.com/photos/".$owner."/".$id;
		return $photo_url;
	}

} 

?>