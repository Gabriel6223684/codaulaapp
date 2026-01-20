<?php

namespace app\middleware;

use Slim\Psr7\Response;

class Middleware
{
    public static function authentication()
    {
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
