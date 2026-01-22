<?php
namespace app\middleware;

use Slim\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthMiddleware
{
    public function __invoke(ServerRequestInterface $request, $handler): ResponseInterface
    {
        $path = '/' . trim($request->getUri()->getPath(), '/');
        if ($path === '') $path = '/';

        $rotasPublicas = ['/login', '/ping'];
        $logado = !empty($_SESSION['usuario']['logado']);

        // Usuário não logado tentando acessar rota protegida
        if (!$logado && !in_array($path, $rotasPublicas)) {
            session_destroy();
            $response = new Response();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        // Usuário logado tentando acessar login
        if ($logado && str_starts_with($path, '/login')) {
            $response = new Response();
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        // Usuário logado mas inativo
        if ($logado && empty($_SESSION['usuario']['ativo'])) {
            session_destroy();
            $response = new Response();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        return $handler->handle($request);
    }
}
