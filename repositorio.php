<?
/*
CLASE : 	repositorio
DETALLES: provee de los métodos de manejo del repositorio de datos, tanto como inserción y lectura de elementos del mismo.
*/
class repositorio
{
	var $num_elementos;
	var $elementos;
	
	/*
	FUNCION: 		constructor
	DESCRIPCION: 	Aqui se inicializan todos los componentes 
	USO: 		Automaticamente al crear un objeto de esta clase
	RECIBE: 		n/a
	DEVUELVE:		n/a
	*/
	function __construct()
	{
       $this->num_elementos = 0;
       $this->elementos = array();
    }	
	
	/*
	FUNCION: 		extraer_batch
	DESCRIPCION: 	Extrae un rango de elementos del repositorio
	USO: 		Para extraer dicho rango usandose en orientacion a objetos
	RECIBE: 		indice inicial
				indice final
	DEVUELVE:		un arreglo con los elementos deseados en el rango
	*/
	function extraer_batch($indice_inicial,$indice_final)
	{
		$resultado_out = array();
		
		if ($this->$elementos == null)
		{
			$resultado_out = "Error al extraer resultados del repositorio.";
		}
		
		foreach ($this->elementos as &$value_resultado)
		{			
			if($value_resultado["indice_rep"] >= $indice_inicial and $value_resultado["indice_rep"] <= $indice_final)
			{
				$resultado_out[] = $value_resultado;				
			}
		}		
		
		return $resultado_out;		
	}
	
	/*
	FUNCION: 		extraer_batch_externa
	DESCRIPCION: 	Extrae un rango de elementos del repositorio
	USO: 		Para extraer dicho rango, esta funcion puede ser utilizada desde afuera para cualquier arreglo
	RECIBE: 		un arreglo con elementos a ser recorridos
				indice inicial
				indice final
	DEVUELVE:		un arreglo con los elementos deseados en el rango
	*/
	function extraer_batch_externa($elementos,$indice_inicial,$indice_final)
	{
			
		$resultado_out = array();
		
		if ($elementos == null)
		{
			$resultado_out = "Error al extraer resultados del repositorio.";
		}
		
		foreach ($elementos as &$value_resultado)
		{				
			if($value_resultado["indice_rep"] >= $indice_inicial and $value_resultado["indice_rep"] <= $indice_final)
			{
				$resultado_out[] = $value_resultado;				
			}
		}		
		
		return $resultado_out;		
	}
	
	/*
	FUNCION: 		extraer_elementos
	DESCRIPCION: 	Extrae todos los elementos del repositorio
	USO: 		Casi no es necesario, pero si se desea extraer todo el repositorio con este metodo se lo puede lograr
	RECIBE: 		n/a
	DEVUELVE:		un arreglo con los elementos del repositorio
	*/
	function extraer_elementos()
	{
		return $this->elementos;
	}
	

	/*
	FUNCION: 		insertar
	DESCRIPCION: 	Insercion de un elemento de tipo findjira al repositorio de datos
	USO: 		De uso interno ya que normalmente no se llama a esta funcion desde afuera de la clase
	RECIBE: 		un elemento para ser parte del arreglo asociativo
	DEVUELVE:		true / false
	*/
	function insertar($value_servicio)
	{
		//¿el repositorio esta vacio?
		if($this->num_elementos == 0)
		{
			$this->elementos[] = $value_servicio;
			
			$this->num_elementos = $this->num_elementos + 1;
			
			return true;
		}
		
		//copia del repositorio en variable local
		$resultados = $this->elementos;
		
		$repetido = false; 				//flag que indica si el elemento ya existe en el repositorio
		$existe_indice_mayor = false;	//flag que indica si existe un elemento que comparte su posición
		
		if ($resultados == null)
		{
			return false;
		}
		
		foreach ($resultados as &$value_resultado)
		{
		
			if (($value_servicio["titulo"] == $value_resultado["titulo"]) or ($value_servicio["raw_url"] == $value_resultado["raw_url"] ) )
			{
				//son iguales titulos, no sabemos si son el mismo aun
				if($this->obtener_dominio($value_servicio["raw_url"]) == $this->obtener_dominio($value_resultado["raw_url"]) )
				{
					//son iguales, pero con url ligeramente distinto, el resultado ya existia en el repositorio
					//marcarle el nuevo motor
					$value_resultado["origen"] .= ",".$value_servicio["origen"];
					$explotado = explode(",",$value_resultado["origen"]);
					$arr_temp = array_unique($explotado);
					$value_resultado["origen"] = implode(",",$arr_temp);
					
					if ($value_resultado["cache_url"]== null)
					{
						$value_resultado["cache_url"] = $value_servicio["cache_url"];
					}				
					
					$repetido = true;				
					break;	
				}
				else 
				{
					if( ( $this->obtener_dominio($value_servicio["raw_url"]) == "www.".$this->obtener_dominio($value_resultado["raw_url"]) ) or ( "www.".$this->obtener_dominio($value_servicio["raw_url"]) == $this->obtener_dominio($value_resultado["raw_url"])  ) )
					{
						//son iguales, pero con url ligeramente distinto en el dominio, el resultado ya existia en el repositorio
						//marcarle el nuevo motor
						$value_resultado["origen"] .= ",".$value_servicio["origen"];
						$explotado = explode(",",$value_resultado["origen"]);
						$arr_temp = array_unique($explotado);
						$value_resultado["origen"] = implode(",",$arr_temp);
						
						if ($value_resultado["cache_url"]== null)
						{
							$value_resultado["cache_url"] = $value_servicio["cache_url"];
						}				
						
						$repetido = true;				
						break;	
					}
				}
			}
		
		}
			
		
		//es necesario un motor BASE que empezara como repositorio
		//este motor tiene su campo indice_rep e indice_serv
		//cuando llega un nuevo elemento externo se compararan sus indice_serv y se lo ubicará al final de ese indice
		//y se alterara el indice_rep de ahi en adelante
		//el indice_rep debera mantener un conteno natural
		
		
		
		
		if($repetido == false)		//no existia en el repositorio entonces insertar: $value_servicio en $resultados
		{			
			foreach ($resultados as &$value_resultado)
			{
				//recorremos el repositorio en búsqueda de un indice de servicio mayor al actual
				//para poder partir el repositorio en ese punto.
				
				if($value_resultado["indice_ser"] > $value_servicio["indice_ser"] )
				{
					$size = $value_resultado["indice_rep"] - 1;						
			
					$existe_indice_mayor = true;					
					break;
				}
			}
			
			if($existe_indice_mayor)
			{
				//partir el arreglo en dos un elemento antes de $value_resultado["indice_ser"]
				//ingresar este elemento al final del primero
				//unir los dos arreglos.
				if ($size < 1)
				{
					$size = 1;
				}
				$chunked = array_chunk($resultados,$size);
				
				//despues de la primera parte vamos a ingresar nuestro elemento
				$chunked[0][] = $value_servicio;
								
				//destruimos la variable
				unset($resultados);
				$resultados = array();
				
				//volver a unir todas las piezas.
				foreach ($chunked as &$piece)
				{
					$resultados = array_merge($resultados,$piece);					
				}				
			}
			else 
			{
				$resultados[] = $value_servicio;
			}
			
		}
		
		//rebautizar los indice_rep
		$i = 1;
		foreach ($resultados as &$value_resultado)
		{
			$value_resultado["indice_rep"] = $i;
			$i++;
		}
		
		$this->num_elementos = $i - 1;	//contador auxiliar (de clase)
		
		$this->elementos = $resultados;
		
		return true;
	}
	
	
	/*
	FUNCION: 		insertar_batch
	DESCRIPCION: 	Insertar un arreglo de elementos de tipo findjira al repositorio de datos
	USO: 		De uso interno ya que normalmente no se llama a esta funcion desde afuera de la clase
	RECIBE: 		un rango de elementos para ser parte del arreglo asociativo
	DEVUELVE:		n/a
	*/
	function insertar_batch($servicio)
	{	
		//si la respuesta es erronea no insertamos.
		if($servicio != NULL or count($servicio) < 1)
		{	
			foreach ($servicio as &$value_servicio)
			{			
				if(count($value_servicio) > 0)
				{
					$this->insertar($value_servicio);
				}
			}
		}		
	}
	
	
	/*
	FUNCION: 		limpiar
	DESCRIPCION: 	Vaciar el repositorio
	USO: 		Cada vez que se desee borrar los datos contenidos en el repositorio
	RECIBE: 		n/a
	DEVUELVE:		n/a
	*/
	function limpiar()
	{
		$this->elementos = array();
		$this->num_elementos = 0;
	}
	
	
	/*
	FUNCION: 		obtener_dominio
	DESCRIPCION: 	Obtener un dominio a partir de un URL valido
	USO: 		Metodo utilitario que retorna el dominio de un URL
	RECIBE: 		un string que contiene un URL valido
	DEVUELVE:		un string solo con el dominio
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
} 

?>