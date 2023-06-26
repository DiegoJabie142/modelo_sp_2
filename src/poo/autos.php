<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once "accesoDatos.php";

class Auto{

    public int $id;
    public string $color;
    public string $marca;
    public float $precio;
    public string $modelo;

    public function mostrarTodos(Request $request, Response $response, array $args): Response 
	{
        $obj_rta = new stdClass();
        $obj_rta->exito = false;
        $obj_rta->mensaje = "Error al traer los autos";
        $obj_rta->tabla = "";
        $obj_rta->status = 424;
		$todosLosCds = Auto::traerTodoLosAutos();
        
        if(isset($todosLosCds)){
            $obj_rta->exito = true;
            $obj_rta->mensaje = "Se pudieron traer todos los autos";
            $obj_rta->tabla = $todosLosCds;
            $obj_rta->status = 200;
        }

		$newResponse = $response->withStatus($obj_rta->status);
		$newResponse->getBody()->write(json_encode($obj_rta));

		return $newResponse->withHeader('Content-Type', 'application/json');	
	}

    public static function traerTodoLosAutos()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->retornarConsulta("select id as id, color as color, marca as marca, precio as precio, modelo as modelo from autos");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "Auto");		
	}

    public function agregarUno(Request $request, Response $response, array $args): Response 
	{
        $obj_rta = new stdClass();
        $obj_rta->exito = false;
        $obj_rta->mensaje = "Fallo al agregar";
        $obj_rta->status = 418;

        $arrayDeParametros = $request->getParsedBody();
		$obj_json = json_decode($arrayDeParametros['obj_json']);
        
        $auto = new Auto();

        $auto->color = $obj_json->color;
        $auto->marca = $obj_json->marca;
        $auto->precio = $obj_json->precio;
        $auto->modelo = $obj_json->modelo;

        $id_agregado = $auto->insertarAuto();

        if(isset($id_agregado)){
            $obj_rta->exito = true;
            $obj_rta->mensaje = "Se pudo agregar el auto";
            $obj_rta->status = 200;
        }

        $newResponse = $response->withStatus($obj_rta->status);

        $newResponse->getBody()->write(json_encode($obj_rta));

      	return $response;
    }

    public function insertarAuto()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->retornarConsulta("INSERT into autos (color,marca,precio,modelo)values(:color,:marca,:precio,:modelo)");
		$consulta->bindValue(':color',$this->color, PDO::PARAM_STR);
		$consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
		$consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
		$consulta->execute();

		return $objetoAccesoDato->retornarUltimoIdInsertado();
	}

}