<?php

namespace app\middleware;

use Slim\Psr7\Response;
use Slim\Psr7\Headers;

class Middleware
{
    public static function authentication()
    {
        return function ($request, $handler) {

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $path = rtrim($request->getUri()->getPath(), '/');
            if ($path === '') {
                $path = '/';
            }

            $logado = !empty($_SESSION['usuario']['logado']);
            $ativo  = !empty($_SESSION['usuario']['ativo']);

            // 🔁 PRIMEIRO: Logado tentando acessar login (tem prioridade)
            if ($logado && str_starts_with($path, '/login')) {
                $headers = new Headers();
                $headers->addHeader('Location', '/dashboard');
                return new Response(302, $headers);
            }

            // Rotas públicas
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

            // ❌ Não logado tentando acessar rota protegida
            if (!$logado && !$rotaPublica) {
                session_destroy();
                $headers = new Headers();
                $headers->addHeader('Location', '/login');
                return new Response(302, $headers);
            }

            // 🚫 Usuário inativo
            if ($logado && !$ativo) {
                session_destroy();
                $headers = new Headers();
                $headers->addHeader('Location', '/login');
                return new Response(302, $headers);
            }

            return $handler->handle($request);
        };
    }
}
