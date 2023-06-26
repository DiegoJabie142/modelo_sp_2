<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;

require_once "accesoDatos.php";
require_once __DIR__ . "/autentificadora.php";
require_once __DIR__ . "/islimeable.php";
require_once __DIR__ . "/imiddleware.php";

class Usuario
{
	public int $id;
 	public string $correo;
  	public string $clave;
  	public string $nombre;
    public string $apellido;
    public string $perfil;
    public string $foto = "";


//*********************************************************************************************//
/* IMPLEMENTO LAS FUNCIONES PARA SLIM */
//*********************************************************************************************//

	public function mostrarTodos(Request $request, Response $response, array $args): Response 
	{
        $obj_rta = new stdClass();
        $obj_rta->exito = false;
        $obj_rta->mensaje = "Error al traer los usuarios";
        $obj_rta->tabla = "";
        $obj_rta->status = 424;
		$todosLosCds = Usuario::traerTodoLosUsuarios();

        if(isset($todosLosCds)){
            $obj_rta->exito = true;
            $obj_rta->mensaje = "Se pudieron traer todos los usuarios";
            $obj_rta->tabla = $todosLosCds;
            $obj_rta->status = 200;
        }

		$newResponse = $response->withStatus($obj_rta->status);
		$newResponse->getBody()->write(json_encode($obj_rta));

		return $newResponse->withHeader('Content-Type', 'application/json');	
	}

	/*public function traerUno(Request $request, Response $response, array $args): Response 
	{
     	$id = $args['id'];
    	$elCd = Cd::traerUnCd($id);

		$newResponse = $response->withStatus(200, "OK");
		$newResponse->getBody()->write(json_encode($elCd));	

		return $newResponse->withHeader('Content-Type', 'application/json');
	}*/
	
	public function agregarUno(Request $request, Response $response, array $args): Response 
	{
        $obj_rta = new stdClass();
        $obj_rta->exito = false;
        $obj_rta->mensaje = "Fallo al agregar";
        $obj_rta->status = 418;

        $params = $request->getParsedBody();
        $obj = json_decode($params["user"]);

        $usuario = new Usuario();
        $usuario->correo = $obj->correo;
        $usuario->clave = $obj->clave;
        $usuario->nombre = $obj->nombre;		
		$usuario->apellido = $obj->apellido;		
        $usuario->perfil = $obj->perfil;

        $id_agregado = $usuario->insertarUsuario();

        if(isset($id_agregado)){
            $obj_rta->exito = true;
            $obj_rta->mensaje = "Se pudo agregar sin foto";
            $obj_rta->status = 200;
        }

		$archivos = $request->getUploadedFiles();

        if(isset($archivos['foto'])){

            $destino = "./fotos/";

            $nombreAnterior = $archivos['foto']->getClientFilename();
    
            $extension = explode(".", $nombreAnterior);
    
            $extension = array_reverse($extension);
    
            $archivos['foto']->moveTo($destino . $obj->correo. '_' . $id_agregado . "." . $extension[0]);
           
            $foto = $destino . $obj->correo. '_' . $id_agregado . "." . $extension[0];
    
            $usuario->foto = $foto;
            $usuario->id = $id_agregado;
    
            $usuario->modificarUsuario();

            $obj_rta->mensaje = "Se pudo agregar con foto";
        }

        $newResponse = $response->withStatus($obj_rta->status);

        $newResponse->getBody()->write(json_encode($obj_rta));

      	return $response;
    }
	
	/*public function modificarUno(Request $request, Response $response, array $args): Response
	{
		$obj = json_decode(($args["cadenaJson"]));   

	    $micd = new Cd();
	    $micd->id = $obj->id;
	    $micd->titulo = $obj->titulo;
	    $micd->cantante = $obj->cantante;
	    $micd->año = $obj->anio;

		$resultado = $micd->modificarCd();
		   
	   	$objDelaRespuesta = new stdclass();
		$objDelaRespuesta->resultado = $resultado;

		$newResponse = $response->withStatus(200, "OK");
		$newResponse->getBody()->write(json_encode($objDelaRespuesta));

		return $newResponse->withHeader('Content-Type', 'application/json');		
	}/*
	
	/*public function borrarUno(Request $request, Response $response, array $args): Response 
	{		 
     	$id = $args['id'];
		 
		$cd = new Cd();
		$cd->id = $id;
		 
     	$cantidadDeBorrados = $cd->borrarCd();

     	$objDeLaRespuesta = new stdclass();
		$objDeLaRespuesta->cantidad = $cantidadDeBorrados;
		
	    if($cantidadDeBorrados>0)
	    {
	    	$objDeLaRespuesta->resultado = "...algo borró!!!";
	    }
	    else
	    {
	    	$objDeLaRespuesta->resultado = "...no borró nada!!!";
		}

		$newResponse = $response->withStatus(200, "OK");
		$newResponse->getBody()->write(json_encode($objDeLaRespuesta));	

		return $newResponse->withHeader('Content-Type', 'application/json');
    }*/
	
//*********************************************************************************************//
/* FIN - AGREGO FUNCIONES PARA SLIM */
//*********************************************************************************************//

	public static function traerTodoLosUsuarios()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->retornarConsulta("select id as id, correo as correo, nombre as nombre, apellido as apellido, perfil as perfil, foto as foto from usuarios");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "Usuario");		
	}

	/*public static function traerUnCd($id) 
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->retornarConsulta("select id, titel as titulo, interpret as cantante, jahr as año from cds where id = $id");
		$consulta->execute();
		$cdBuscado= $consulta->fetchObject('cd');
		return $cdBuscado;		
	}*/

	public function insertarUsuario()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->retornarConsulta("INSERT into usuarios (correo,clave,nombre,apellido,perfil,foto)values(:correo,:clave,:nombre,:apellido,:perfil,:foto)");
		$consulta->bindValue(':correo',$this->correo, PDO::PARAM_STR);
		$consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
		$consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':perfil', $this->apellido, PDO::PARAM_INT);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_INT);

		$consulta->execute();

		return $objetoAccesoDato->retornarUltimoIdInsertado();
	}

	public function modificarUsuario()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->retornarConsulta("
				update usuarios
				set correo=:correo,
				clave=:clave,
				nombre=:nombre,
                apellido=:apellido,
                foto = :foto,
                perfil = :perfil
				WHERE id=:id");

        $consulta->bindValue(':id',$this->id, PDO::PARAM_INT);
		$consulta->bindValue(':correo',$this->correo, PDO::PARAM_STR);
		$consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
		$consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':perfil', $this->foto, PDO::PARAM_INT);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);

		return $consulta->execute();
	 }

	/*public function borrarCd()
	{
	 	$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->RetornarConsulta("delete from cds	WHERE id=:id");	
		$consulta->bindValue(':id',$this->id, PDO::PARAM_INT);		
		$consulta->execute();
		return $consulta->rowCount();
	}*/

    public function VerificarUsuario(Request $request, Response $response, array $args) : Response{

        $retorno = new stdClass();
        
        $params = $request->getParsedBody();
        $obj_json = json_decode($params["user"]);
        $correo = $obj_json->correo;
        $clave = $obj_json->clave;

        $usuario = Usuario::verificar($correo, $clave);

        if(isset($usuario->id)){
            $token = Autentificadora::crearJWT(json_encode($usuario),600);
            $newResponse = $response->withStatus(200);
            $status = 200;
        }else{
            $token = Autentificadora::crearJWT(null, 0);
            $newResponse = $response->withStatus(403);
            $status = 403;
        }
        $retorno->jwt = $token;
        $retorno->status = $status;

        $newResponse->getBody()->write(json_encode($retorno));
        
        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    static function Verificar($correo, $clave){

        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta = $objetoAccesoDato->retornarConsulta("SELECT id, nombre, correo, nombre, apellido, perfil, foto  FROM usuarios WHERE correo = :correo AND clave = :clave");        
        $consulta->bindValue(':correo', $correo, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $clave, PDO::PARAM_STR);			
        $consulta->execute();

        $usuario= $consulta->fetchObject('Usuario');

		return $usuario;	
    }

    public function ValidarParametrosUsuario(Request $request, RequestHandler $handler) : ResponseMW{
        
        $mensajeRtn = "";
        $error = false;
        $obj_datos = new stdClass();

        $params = $request->getParsedBody();
        $obj_json = isset($params["user"]) ? json_decode($params["user"]) : NULL;
        $correo = isset($obj_json->correo) ? $obj_json->correo : NULL;
        $clave = isset($obj_json->clave) ? $obj_json->clave : NULL;

        if(!isset($obj_json)){
           $mensajeRtn = "El parametro obj_json no fue recibido o su formato es incorrecto.";
           $error = true;
        }

        if(isset($obj_json) && (!isset($correo) || !isset($clave))){
            $mensajeRtn = "La clave y/o el correo no fue recibido.";
            $error = true;
        }

        if($error){
            $obj_datos->mensaje = $mensajeRtn;
            $status = 403;
        }else{
            $response = $handler->handle($request);
            $obj_datos->VerificarUsuario = json_decode($response->getBody());
            $status = 200;
        }

        $obj_datos->status = $status;

        $response = new ResponseMW($status);
        $response->getBody()->write(json_encode($obj_datos));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function VerificarQueNoEstenVacios(Request $request, RequestHandler $handler) : ResponseMW{

        $mensajeRtn = "";
        $error = false;
        $obj_datos = new stdClass();

        $params = $request->getParsedBody();
        $obj_json = isset($params["user"]) ? json_decode($params["user"]) : NULL;
        $correo = $obj_json->correo;
        $clave =$obj_json->clave;

        if($clave == "" || $correo == ""){
            $error = true;
            $mensajeRtn = "La clave y/o el correo están vacíos.";
        }
        
        if($error){
            $obj_datos->mensaje = $mensajeRtn;
            $status = 409;
        }else{
            $response = $handler->handle($request);
            $obj_datos->NextHandle = json_decode($response->getBody());
            $status = 200;
        }

        $obj_datos->status = $status;

        $response = new ResponseMW($status);
        $response->getBody()->write(json_encode($obj_datos));

        return $response->withHeader('Content-Type', 'application/json');
    }

}