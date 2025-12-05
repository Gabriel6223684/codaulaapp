<?php

namespace app\middleware;

use app\database\builder\SelectQuery;

class Middleware
{
    public static function route()
    {
        # Retorna uma closure (função anônima) que será executada para cada requisição
        $middleware = function ($request, $handler) {
            #A linha $handler->handle($request) é como dizer: "Continua o processo!" - 
            #ela passa a bola para o próximo jogador do time até chegar no gol (resposta final). 🎯
            $response = $handler->handle($request);
            # Captura o método HTTP da requisição (GET, POST, PUT, DELETE, etc.)
            $method = $request->getMethod();
            # Captura a URI da página solicitada pelo usuário (ex: '/login', '/dashboard')
            $pagina = $request->getRequestTarget();
            # Verifica se o método da requisição é GET
            if ($method === 'GET') {
                # Verifica se o usuário NÃO está autenticado
                # Condições: sessão vazia OU flag 'logado' false OU inexistente
                $usuarioNaoLogado = empty($_SESSION['usuario']) || empty($_SESSION['usuario']['logado']);
                # Se usuário não está logado E não está tentando acessar a página de login
                if ($usuarioNaoLogado && $pagina !== '/login') {
                    # Destroi a sessão para limpar qualquer dado residual
                    session_destroy();
                    # Redireciona para a página de login com status HTTP 302 (redirecionamento temporário)
                    return $response->withHeader('Location', HOME . '/login')->withStatus(302);
                }
                # Se a página solicitada é a de login
                if ($pagina === '/login') {
                    # Verifica se o usuário JÁ está autenticado
                    if (!empty($_SESSION['usuario']) && !empty($_SESSION['usuario']['logado'])) {
                        # Se já está logado, redireciona para a home (evita acesso desnecessário ao login)
                        return $response->withHeader('Location', HOME)->withStatus(302);
                    }
                    # Se não está logado, destroi qualquer sessão residual
                    session_destroy();
                    # Permite o acesso à página de login processando a requisição normalmente
                    return $handler->handle($request);
                }
                # Busca os dados completos do usuário no banco de dados usando o ID da sessão
                $usuario = SelectQuery::select()
                    ->from('usuario')
                    ->where('id', '=', $_SESSION['usuario']['id'])
                    ->fetch();
                # Verifica se o usuário está inativo no banco de dados
                if (empty($usuario['ativo'])) {
                    # Destroi a sessão do usuário inativo
                    session_destroy();
                    # Redireciona para a página de login
                    return $response->withHeader('Location', HOME . '/login')->withStatus(302);
                }
                # Se chegou até aqui, o usuário está autenticado e ativo
                # Processa a requisição normalmente através da cadeia de middlewares/handlers
                return $handler->handle($request);
            }
            # Para requisições que NÃO são GET (POST, PUT, DELETE, etc.)
            # Processa a requisição normalmente sem validações de autenticação
            return $handler->handle($request);
        };
        # Retorna o middleware para ser registrado no Slim Framework
        return $middleware;
    }
}