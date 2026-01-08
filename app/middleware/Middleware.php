<?php

namespace app\middleware;

class Middleware
{
    public static function authentication()
    {
        #Retorna um closure (função anônima)
        $middleware = function ($request, $handler) {
            #Capturamos o metodo de requisição (GET, POST, PUT, DELETE, ETC).
            $method = $request->getMethod();
            #Capturamos a página que o usuário está tentando acessar (path).
            $pagina = $request->getUri()->getPath();
            if ($method === 'GET') {
                #Verificando se o usuário está autenticado, caso não esteja já direcionamos para o login.
                $usuarioLogado = empty($_SESSION['usuario']) || empty($_SESSION['usuario']['logado']);
                if ($usuarioLogado && $pagina !== '/login') {
                    session_destroy();
                    $response = new \Slim\Psr7\Response();
                    return $response->withHeader('Location', '/login')->withStatus(302);
                }
                if ($pagina === '/login' && !$usuarioLogado) {
                    $response = new \Slim\Psr7\Response();
                    return $response->withHeader('Location', '/')->withStatus(302);
                }
                if (!empty($_SESSION['usuario']) && (empty($_SESSION['usuario']['ativo']) || !$_SESSION['usuario']['ativo'])) {
                    session_destroy();
                    $response = new \Slim\Psr7\Response();
                    return $response->withHeader('Location', '/login')->withStatus(302);
                }
            }
            return $handler->handle($request);
        };
        return $middleware;
    }
}
