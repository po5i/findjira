<?
/*
CLASE : 	service
DETALLES:	provee todos los métodos necesarios para la conexión con los servicios web así como otras herramientas para su parseo, esta clase es padre de las demás clases de servicios
*/
class service
{
	var $query;
	var $peticion_url;
	var $respuesta_raw;
	var $respuesta_findjira;
	var $resultados_por_pagina;
	var $max_resultados_por_pagina;
	var $resultado_inicial;
	var $thumbnail_width;
	var $thumbnail_height;
	var $embed_width;
	var $embed_height;
	var $respuesta_json;
	var $respuesta_php;
	var $FINDJIRA_PATH;
	
	
	/*
	FUNCION: 		constructor
	DESCRIPCION: 	Aqui se inicializan todos los componentes 
	USO: 		Automaticamente al crear un objeto de esta clase
	RECIBE: 		n/a
	DEVUELVE:		n/a
	*/
	function __construct()
	{
       $this->query = "";
       $this->peticion_url = "";
       $this->respuesta_raw = "";
       $this->respuesta_findjira = "";
       $this->resultados_por_pagina = 10;
	   $this->max_resultados_por_pagina = 10;	//por modificar en cada servicio
       $this->resultado_inicial = 1;
       $this->numero_pagina = 1;
       $this->thumbnail_width = 160;
       $this->thumbnail_height = 120;
       $this->embed_width = 320;
       $this->embed_height = 240;
	   
	   $this->FINDJIRA_PATH = "findjira/";
    }
	
	
	/*
	FUNCION: 		get_findjira_path
	DESCRIPCION: 	Retorna el directorio donde se encuentra ubicado el findjira framework
	USO: 		Para obtención del parámetro
	RECIBE: 		n/a
	DEVUELVE:		un string con el path
	*/
	function get_findjira_path()
	{
		return $this->FINDJIRA_PATH;
	}
	
	
	/*
	FUNCION: 		set_findjira_path
	DESCRIPCION: 	Asigna la ubicacion del directorio de findjira framework
	USO: 		Para asignación del parámetro
	RECIBE: 		un string con el path
	DEVUELVE:		n/a
	*/
	function set_findjira_path($path)
	{
		$this->FINDJIRA_PATH = $path;
	}
	
	
	/*
	FUNCION: 		get_respuesta_findjira
	DESCRIPCION: 	Retorna la respuesta parseada para que sea usada por handler y sea ingresada al repositorio
	USO: 		Para obtención de la respuesta ya procesada del servicio
	RECIBE: 		n/a
	DEVUELVE:		un arreglo de formato findjira que contiene los resultados encontrados por este servicio
	*/
	function get_respuesta_findjira()
	{
		return $this->respuesta_findjira;
	}
		

	/*
	FUNCION: 		get_respuesta_raw
	DESCRIPCION: 	Retorna la respuesta nativa de cada servicio
	USO: 		Para obtención de la respuesta nativa del servicio
	RECIBE: 		n/a
	DEVUELVE:		un string conteniendo la respuesta obtenida por conexion HTTP
	*/
	function get_respuesta_raw()
	{
		return $this->respuesta_raw;
	}	
	
	
	/*
	FUNCION: 		set_query
	DESCRIPCION: 	Asigna el query para la busqueda
	USO: 		De uso interno
	RECIBE: 		un string con el query
	DEVUELVE:		n/a
	*/
	public  function set_query($str)
	{		
		$this->query = $str;		
	}
	
	
	/*
	FUNCION: 		get_query
	DESCRIPCION: 	Retorna el query asignado actualmente
	USO: 		De uso interno
	RECIBE: 		n/a
	DEVUELVE:		un string con el query
	*/
	public  function get_query()
	{
		return $this->query;
	}
	
	
	/*
	FUNCION: 		set_thumbnail_width
	DESCRIPCION: 	Asigna el ancho para la vista previa
	USO: 		De uso interno
	RECIBE: 		la medida del ancho en pixeles
	DEVUELVE:		n/a
	*/
	public  function set_thumbnail_width($width)
	{
		$this->thumbnail_width = $width;
	}
	
	/*
	FUNCION: 		set_thumbnail_height
	DESCRIPCION: 	Asigna el alto para la vista previa
	USO: 		De uso interno
	RECIBE: 		la medida del alto en pixeles
	DEVUELVE:		n/a
	*/
	public  function set_thumbnail_height($height)
	{
    	$this->thumbnail_height = $height;
	}
	
	/*
	FUNCION: 		get_thumbnail_width
	DESCRIPCION: 	Retorna el ancho asignado actualmente para la vista previa
	USO: 		De uso interno
	RECIBE: 		n/a
	DEVUELVE:		la medida del ancho en pixeles
	*/
	public  function get_thumbnail_width()
	{
		return $this->thumbnail_width;
	}
	
	/*
	FUNCION: 		get_thumbnail_height
	DESCRIPCION: 	Retorna el alto asignado actualmente para la vista previa
	USO: 		De uso interno
	RECIBE: 		n/a
	DEVUELVE:		la medida del alto en pixeles
	*/
	public  function get_thumbnail_height()
	{
		return $this->thumbnail_height;
	}
	
	/*
	FUNCION: 		set_resultados_por_pagina
	DESCRIPCION: 	Asigna el numero de resultados por pagina de busqueda
	USO: 		De uso interno
	RECIBE: 		el número de resultados deseados a recibir por respuesta del servicio
	DEVUELVE:		n/a
	*/
	public  function set_resultados_por_pagina($str)
	{
		$this->resultados_por_pagina = $str;			
	}
	
	/*
	FUNCION: 		get_resultados_por_pagina
	DESCRIPCION: 	Retorna el numero de resultados por pagina de busqueda
	USO: 		De uso interno
	RECIBE: 		n/a
	DEVUELVE:		el número de resultados deseados a recibir por respuesta del servicio
	*/
	public  function get_resultados_por_pagina()
	{
		return $this->resultados_por_pagina;
	}
	
	/*
	FUNCION: 		set_max_resultados_por_pagina
	DESCRIPCION: 	Asigna el máximo numero de resultados por pagina de busqueda
	USO: 		De uso interno
	RECIBE: 		el número máximo de resultados deseados a recibir por respuesta del servicio
	DEVUELVE:		n/a
	*/
	public  function set_max_resultados_por_pagina($str)
	{
		$this->max_resultados_por_pagina = $str;
	}
	
	/*
	FUNCION: 		get_max_resultados_por_pagina
	DESCRIPCION: 	Retorna el máximo numero de resultados por pagina de busqueda
	USO: 		De uso interno
	RECIBE: 		n/a
	DEVUELVE:		el número máximo de resultados deseados a recibir por respuesta del servicio
	*/
	public  function get_max_resultados_por_pagina()
	{
		return $this->max_resultados_por_pagina;
	}
	
	
	/*
	FUNCION: 		get_numero_pagina
	DESCRIPCION: 	Funcion necesaria para servicios que piden numero de pagina en lugar de resultado inicial por pagina
	USO: 		De uso interno
	RECIBE: 		n/a
	DEVUELVE:		un numero equivalente al numero de pagina
	*/
	public  function get_numero_pagina()
	{
		$num_pagina = ceil($this->resultado_inicial / $this->resultados_por_pagina);
		return $num_pagina;
	}
	
	
	/*
	FUNCION: 		set_resultado_inicial
	DESCRIPCION: 	Asigna el indice de resultado inicial para la busqueda
	USO: 		De uso interno
	RECIBE: 		indice de resultado inicial deseado en la respuesta del servicio
	DEVUELVE:		n/a
	*/
	public  function set_resultado_inicial($str)
	{
		$this->resultado_inicial = $str ;
	}
	
	/*
	FUNCION: 		get_resultado_inicial
	DESCRIPCION: 	Retorna el indice de resultado inicial para la busqueda
	USO: 		De uso interno
	RECIBE: 		n/a
	DEVUELVE:		indice de resultado inicial deseado en la respuesta del servicio
	*/
	public  function get_resultado_inicial()
	{
		return $this->resultado_inicial;
	}
	
	/*
	FUNCION: 		set_thumbnail_w
	DESCRIPCION: 	Asigna el ancho para la vista previa
	USO: 		De uso interno
	RECIBE: 		la medida del ancho en pixeles
	DEVUELVE:		n/a
	*/
	public  function set_thumbnail_w($str)
	{
		$this->thumbnail_width = $str;
	}	
	
	/*
	FUNCION: 		set_thumbnail_h
	DESCRIPCION: 	Asigna el alto para la vista previa
	USO: 		De uso interno
	RECIBE: 		la medida del alto en pixeles
	DEVUELVE:		n/a
	*/
	public  function set_thumbnail_h($str)
	{
		$this->thumbnail_height = $str;
	}
	
	/*
	FUNCION: 		get_thumbnail_w
	DESCRIPCION: 	Retorna el ancho asignado actualmente para la vista previa
	USO: 		De uso interno
	RECIBE: 		n/a
	DEVUELVE:		la medida del ancho en pixeles
	*/
	public  function get_thumbnail_w()
	{
		return $this->thumbnail_width;
	}
	
	/*
	FUNCION: 		get_thumbnail_h
	DESCRIPCION: 	Retorna el alto asignado actualmente para la vista previa
	USO: 		De uso interno
	RECIBE: 		n/a
	DEVUELVE:		la medida del alto en pixeles
	*/
	public  function get_thumbnail_h()
	{
		return $this->thumbnail_height;
	}
	
	/*
	FUNCION: 		set_thumbnail_res
	DESCRIPCION: 	Asigna el ancho y alto para la vista previa
	USO: 		De uso interno
	RECIBE: 		la medida del ancho y alto en pixeles
	DEVUELVE:		n/a
	*/
	public  function set_thumbnail_res($w,$h)
	{
		$this->thumbnail_width = $w;
		$this->thumbnail_height = $h;
	}	
	
	/*
	FUNCION: 		set_embed_w
	DESCRIPCION: 	Asigna el ancho para la vista previa del embed
	USO: 		De uso interno
	RECIBE: 		la medida del ancho en pixeles
	DEVUELVE:		n/a
	*/
	public  function set_embed_w($str)
	{
		$this->embed_width = $str;
	}	
	
	/*
	FUNCION: 		set_embed_h
	DESCRIPCION: 	Asigna el alto para la vista previa del embed
	USO: 		De uso interno
	RECIBE: 		la medida del alto en pixeles
	DEVUELVE:		n/a
	*/
	public  function set_embed_h($str)
	{
		$this->embed_height = $str;
	}
	
	/*
	FUNCION: 		get_embed_w
	DESCRIPCION: 	Retorna el ancho asignado actualmente para la vista previa del embed
	USO: 		De uso interno
	RECIBE: 		n/a
	DEVUELVE:		la medida del ancho en pixeles
	*/
	public  function get_embed_w()
	{
		return $this->embed_width;
	}
	
	/*
	FUNCION: 		get_embed_h
	DESCRIPCION: 	Retorna el alto asignado actualmente para la vista previa del embed
	USO: 		De uso interno
	RECIBE: 		n/a
	DEVUELVE:		la medida del alto en pixeles
	*/
	public  function get_embed_h()
	{
		return $this->embed_height;
	}
	
	/*
	FUNCION: 		set_embed_res
	DESCRIPCION: 	Asigna el ancho y alto para la vista previa del embed
	USO: 		De uso interno
	RECIBE: 		la medida del ancho y alto en pixeles
	DEVUELVE:		n/a
	*/
	public  function set_embed_res($w,$h)
	{
		$this->embed_width = $w;
		$this->embed_height = $h;
	}	

	
	// UTILIDADES
	
	
    /*
	FUNCION: 		buscar
	DESCRIPCION: 	Conexion a un servidor remoto y obtener respuesta.
	USO: 		Este método es el núcleo de todas las búsquedas y conexiones
	RECIBE: 		la ubicacion url del sitio a conectarse
	DEVUELVE:		un string con la respuesta (en caso de exito)
				false (en caso de falla)
	*/
    function buscar($URL)
	{
		require_once("class_HTTPRetriever.php");
		
		$http = &new HTTPRetriever();
		
		if (!$http->get($URL)) {
		  //echo "HTTP request error: #{$http->result_code}: {$http->result_text}";
		  return false;
		}

		//la cabecera se guarda en: $http->response_headers
		//el body se guarda en: $http->response
	
		return $http->response;
	}
	
	
	/*
	FUNCION: 		obtener_web_thumbnail
	DESCRIPCION: 	Obtener thumbnail del servicio de vista de websites
	USO: 		Cada vez que se desee obtener una imagen de vista previa de un sitio web 
	RECIBE: 		el url del website que se desea obtener su vista previa
	DEVUELVE:		el url de la imagen del website
	*/
	function obtener_web_thumbnail($URL)
	{
		//$websnapr = "http://images.websnapr.com/?url=".$URL."&size=S";	
		$girafa = "http://msnsearch.srv.girafa.com/srv/i?i=%204ac8babd1cf73ace&s=MSNSEARCH&cy=en-us&r=";
		
		return $girafa.$URL;
	}
	
	
	/*
	FUNCION: 		obtener_filename_ext
	DESCRIPCION: 	Obtener el nombre del archivo basado en el ultimo slash
	USO: 		De uso interno
	RECIBE: 		un string de un url valido
	DEVUELVE:		el nombre del archivo con su extension
	*/
	function obtener_filename_ext($URL)
	{
		$reverse = strrev($URL);
		$pos = strpos($reverse,"/");
		$filename_rev = substr($reverse,0,$pos);
		$filename_ext = strrev($filename_rev);
		
		return $filename_ext;
	}
	
	
	/*
	FUNCION: 		obtener_filename
	DESCRIPCION: 	Obtener el nombre del archivo basado en el ultimo slash
	USO: 		De uso interno
	RECIBE: 		un string de un url valido
	DEVUELVE:		el nombre del archivo sin su extension
	*/
	function obtener_filename($URL)
	{
		
		$reverse = strrev($URL);
		$pos = strpos($reverse,"/");
		$filename_rev = substr($reverse,0,$pos);
		$filename_ext = strrev($filename_rev);
		
		$pos = strpos($filename_ext,".");
		$filename = substr($filename_ext,0,$pos);
		
		return $filename;
	}
	
	
	/*
	FUNCION: 		obtener_extension
	DESCRIPCION: 	Obtener la extension del archivo basasdo en el ultimo punto
	USO: 		De uso interno
	RECIBE: 		un string de un url valido
	DEVUELVE:		la extension del archivo
	*/
	function obtener_extension($URL)
	{
		
		$reverse = strrev($URL);
		$pos = strpos($reverse,".");
		$filename_rev = substr($reverse,0,$pos);
		$filename = strrev($filename_rev);
		return $filename;
	}
	
	
	/*
	FUNCION: 		obtener_URL_sin_ultimo_slash
	DESCRIPCION: 	Obtener un URL sacandole el ultimo slash: "/"
	USO: 		De uso interno
	RECIBE: 		un string de un url valido
	DEVUELVE:		el mismo url sin el ultimo slash
	*/
	function obtener_URL_sin_ultimo_slash($URL)
	{
		$reverse = strrev($URL);
		
		$pos = strpos($reverse,"/");

		if($pos === 0)
		{
			$URL_sin_slash_rev = substr($reverse,1);
			$URL_sin_slash = strrev($URL_sin_slash_rev);
			return $URL_sin_slash;
		}
		
		return $URL;
		
	}
	
	
	/*
	FUNCION: 		obtener_dominio
	DESCRIPCION: 	Obtener un dominio a partir de un URL valido
	USO: 		De uso interno
	RECIBE: 		un string de un url valido
	DEVUELVE:		el dominio del url
	*/
	function obtener_dominio($URL) 
	{		
		$pos = strpos($URL,"http://");
		if ($pos === false) 
		{
			$URL_dominio = $URL;
		}
		else
		{
			$URL_dominio = substr($URL,strlen("http://"));			
		}	
		
		
		$pos = strpos($URL_dominio,"/");
		
		
		if ($pos === false) 
		{
			// no hay mas "/"
		}
		else
		{			
			$URL_dominio = substr($URL_dominio,0,$pos);			
		}
		
		return $URL_dominio;
	}
	
	
	/*
	FUNCION: 		obtener_embed_image
	DESCRIPCION: 	Obtener el html de la imagen listo para ser embebido
	USO: 		De uso interno
	RECIBE: 		un string de un url valido
				ancho
				alto
	DEVUELVE:		un string de código HTML formateado y listo para ser incrustado
	*/
	function obtener_embed_image($URL,$w,$h)
	{
		$embed = "<img src=\"$URL\" width=\"$w\" height=\"$h\">";
		return $embed;
	}		
	
	
	/*
	FUNCION: 		obtener_javimoya_dl
	DESCRIPCION: 	Obtener el url de video usando el servicio javimoya
	USO: 		De uso interno
	RECIBE: 		un string de un url valido
	DEVUELVE:		un  url con la ubicacion de la descarga del video
	*/
	function obtener_javimoya_dl($URL)
	{
		$javimoya_string = "http://videodownloader.net/get/?url=".($URL);
		return $javimoya_string;
		
		// Formatos de URL requeridos:
		
		//  http://video.google.com/videoplay?docid=-2985665601878837906
		//  http://www.youtube.com/watch?v=-_CSo1gOd48
		//  http://www.ifilm.com/ifilmdetail/2693003
		//  http://www.metacafe.com/watch/82356/the_real_simpsons/	
	}
	
	
	/*
	FUNCION: 		obtener_url_param
	DESCRIPCION: 	Obtener el contenido de un parametro GET de un url
	USO: 		De uso interno
	RECIBE: 		un string de un url valido
				el nombre de un parametro que exista dentro de un url
	DEVUELVE:		el contenido del parametro GET
	*/
	function obtener_url_param($URL,$param_name)
	{		
		$param_str = $param_name."=";
		$next_param_str = "&";
		
		$pos_param = strpos($URL,$param_str);
		if ($pos_param === false) //no existe
		{
			return " param no encontrado, revisar sintaxis ";
		}


		$pos_next_param = strpos($URL,$next_param_str,$pos_param + strlen($pos_param));
		
		if ($pos_next_param > $pos_param)
		{
			$param_value= substr($URL,$pos_param + strlen($param_str),$pos_next_param - ($pos_param + strlen($param_str)));
		}
		else
		{
			$param_value= substr($URL,$pos_param + strlen($param_str));
		}
		
		return $param_value;	
	}
	
	
	/*
	FUNCION: 		obtener_embed_mp3_player
	DESCRIPCION: 	Obtenerel objeto flash para reproducir mp3
	USO: 		De uso interno
	RECIBE: 		un string de un url valido
				ancho
				alto
	DEVUELVE:		un string de código HTML formateado y listo para ser incrustado
	*/
	function obtener_embed_mp3_player($URL,$width,$height)
	{
		$embed = "<embed src=\"".$this->FINDJIRA_PATH."flash_player/mp3player.swf\" width=\"$width\" height=\"$height\" bgcolor=\"#000000\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" flashvars=\"file=$URL&autostart=false\" />";
		
		return $embed;
	}
	
	
	/*
	FUNCION: 		generar_xspf
	DESCRIPCION: 	Este metodo genera una estructura XML con formato XSPF
	USO: 		Para ser llamada desde cualquier ubicacion cuando se desee generar un XSPF
	RECIBE: 		un arreglo con estructura findjira: obligadamente debe tener el campo "download_url"
	DEVUELVE:		un string con el XML formato XSPF
	*/
	function generar_xspf($elementos)
	{
		$xspf = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<playlist version=\"1\" xmlns=\"http://xspf.org/ns/0/\">
			<trackList>
		";

		foreach($elementos as &$elemento)
		{
			$xspf .= "
				<track>
					<title>".@$elemento["titulo"]."</title>
					<image>".@$elemento["thumb_url"]."</image>
					<location>".$elemento["download_url"]."</location>
				</track>";			
		}
		
		$xspf .= "
			</trackList>
		</playlist>";
		
		return $xspf;
	}
	
	
	/*
	FUNCION: 		obtener_archivos_directorio
	DESCRIPCION: 	Este metodo lee un directorio, realiza el scraping de los archivos del mismo
	USO: 		Para ser llamada desde cualquier ubicacion cuando se desee obtener archivos de un directorio online
	RECIBE: 		un URL de directorio (Index of) 
	DEVUELVE:		un arreglo con los archivos contenidos
	*/
	function obtener_archivos_directorio($URL)
	{	
		$html = service::buscar($URL);	//conexion al servidor y obtener string de respuesta ¿ porque no se puede usar con $this-> ?
		$files = array();
		
		$pos = stripos($html, "parent");	//posicion del texto "parent directory" que siempre esta presente
		
		if ($pos === false) 
		{
			//no existe el string, ni para qué recorrer el resto
			//se podria llenar $files con un solo elemento: un mp3 de nuestro server diciendo "pagina vacia" o algo así
			return null;
		}
		else
		{
			while($pos !== false)
			{
				//calculo de posiciones
				$href_pos = stripos($html, "href=\"", $pos);	//buscar la posicion de "href" desde "parent directory"
				$cierra_pos = stripos($html, "\">", $href_pos);	//buscar la posicion de > desde la anterior pos
				$lenght = $cierra_pos - (6 + $href_pos);
				
				//obtenemos archivo
				$filename = substr($html,$href_pos + 6,$lenght);
				
				//validamos extension mp3
				$extension = substr($filename,-4);
				if( strcasecmp($extension,".mp3") == 0)		//agregar todas las extensiones aceptadas aquí separadas por OR
					$files[]["download_url"] = service::obtener_URL_sin_ultimo_slash($_GET["url"])."/".$filename;
				
				//adelantamos el puntero buscando una nueva ocurrencia de hipervinculo
				$pos = stripos($html, "href=\"", $cierra_pos);
			}
			//retorna el arreglo de archivos formato findjira["download_url"]
			return $files;
		}
	}
	
	
	/*
	FUNCION: 		obtener_favicon_url
	DESCRIPCION: 	Este metodo obtiene el favicon de un dominio
	USO: 		Para ser llamada desde cualquier ubicacion cuando se desee obtener el favicon de un dominio
	RECIBE: 		un url valido
	DEVUELVE:		el url del archivo favicon
	*/
	function obtener_favicon_url($url)
	{
		//fuente http://www.google.com/codesearch?hl=en&q=+lang:php+get+favicon+url+show:t905v7HvmKU:aKpoSCnErz4:ZhxC3i0S5do&sa=N&cd=12&ct=rc&cs_p=http://ftp.osuosl.org/pub/FreeBSD/distfiles/xaraya-1.1.1-base.tar.gz&cs_f=xaraya-1.1.1/html/modules/base/xaruserapi/getfavicon.php#a0
		if(empty($url) || $url == 'http://'){
			return false;
		}
	
		$url_parts = @parse_url($url);
	
		if (empty($url_parts)) {
			return false;
		}
	
		$full_url = "http://$url_parts[host]";
	
		if(isset($url_parts['port'])){
			$full_url .= ":$url_parts[port]";
		}
	
		$favicon_url = $full_url . "/favicon.ico" ;
		return $favicon_url;
	}
	
	
	/*
	FUNCION: 		obtener_feeds_autodiscovery
	DESCRIPCION: 	Este metodo obtiene arreglo de feeds de cualquier url provisto (feed autodiscovery)
	USO: 		Para ser llamada desde cualquier ubicacion cuando se desee obtener los feeds asociados a un url
	RECIBE: 		un url valido
	DEVUELVE:		un arreglo con los feeds asociados a un url
	*/
	function obtener_feeds_autodiscovery($url) {
	
	
		$cnt = $this->buscar($url);
		
		
		$ret = array ();
		//encontrar todos los link tags
		if (preg_match_all('|<link\s+\w*=["\'][^"\']+["\']+[^>]*>|Uis', $cnt, $res)) {
			while (list ($id, $match) = each($res[0])) {
				// solo se desea '<link alternate=...'
				if (strpos(strtolower($match), 'alternate') &&
						!strpos(strtolower($match), 'stylesheet')  && // extraer atributos
						preg_match_all('|([a-zA-Z]*)=["\']([^"\']*)|', $match, $res2, PREG_SET_ORDER)) {
					$tmp = array ();
					//el arreglo de retorno es llenado: attr_name => attr_value
					while (list ($id2, $match2) = each($res2)) {
						$attr = strtolower(trim($match2[1]));
						$val = trim($match2[2]);
						// solo queremos urls absolutos
						if (($attr == "href") && strcasecmp(substr($val, 0, 4), "http") != 0) {
							// verificar si el url relativo inicia con "//"
							if(substr($val,0,2) == "//") {
								$val = preg_replace('/\/\/.*/', $val, $url);
							} else {
								$urlParts = parse_url($url);
								if ($urlParts && is_array($urlParts) && strlen($val)) {
									if ($val[0] != '/') {
										$val = '/'.$val;
									}
									$val = $urlParts['scheme'] . '://'
										   .$urlParts['host'] . $val;
								} else {
									$val = ($url.$val);
								}
							}
						}
						$tmp[$attr] = $val;
					}
					$ret[] = $tmp;
				}
			}
		}
		return $ret;
	}


	
	
	//FUNCION INTERNA PARA MANUAL DEBUG	
	public function test ()
	{	
		echo "<h1>RESPUESTA DECODIFICADA A PHP:</h1><pre>";
		var_dump($this->respuesta_php);
		echo "</pre><br>";
		
		echo "<h1>RESPUESTA FINDJIRA:</h1><pre>";
		var_dump($this->respuesta_findjira);
		echo "</pre>";		
	}
	
	
	
	

} 

?>