<?php

namespace app\middleware;

use Slim\Psr7\Response;

class Middleware
{
    public static function authentication()
    {
<<<<<<< HEAD
        return function ($request, $handler) {

            $path = rtrim($request->getUri()->getPath(), '/');
            if ($path === '') {
                $path = '/';
            }

            // rotas públicas
            $rotasPublicas = [
                '/login',
                '/ping'
            ];

            $rotaPublica = false;
            foreach ($rotasPublicas as $rota) {
                if ($path === $rota || str_starts_with($path, $rota . '/')) {
                    $rotaPublica = true;
                    break;
=======
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
>>>>>>> 8aded88d298a548d561d72516e794fe63515a8fb
                }
            }

            $logado = !empty($_SESSION['usuario']['logado']);

            // ❌ não logado tentando acessar rota protegida
            if (!$logado && !$rotaPublica) {
                session_destroy();
                return new Response(302, ['Location' => '/login']);
            }

            // 🔁 logado tentando acessar login
            if ($logado && str_starts_with($path, '/login')) {
                return new Response(302, ['Location' => '/']);
            }

            // 🚫 usuário inativo
            if ($logado && empty($_SESSION['usuario']['ativo'])) {
                session_destroy();
                return new Response(302, ['Location' => '/login']);
            }

            return $handler->handle($request);
        };
    }
}
