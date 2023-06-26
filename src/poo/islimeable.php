<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


interface ISlimeable{

    function crear(Request $request, Response $response, array $args) : Response;
    function verificar(Request $request, Response $response, array $args) : Response;
    function obtenerPayLoad(Request $request, Response $response, array $args) : Response;
    function obtenerDatos(Request $request, Response $response, array $args) : Response;
    function verificarPorHeader(Request $request, Response $response, array $args) : Response;
    function obtenerAutosJson(Request $request, Response $response, array $args) : Response;
}