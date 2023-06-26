<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;


interface IMiddleware{

    function verificarToken(Request $request, RequestHandler $handler) : ResponseMW;   
}