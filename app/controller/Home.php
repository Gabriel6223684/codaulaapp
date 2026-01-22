<?php

namespace app\controller;

use app\trait\Template;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Home extends Base
{
    use Template;

    public function home(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $twig = $this->getTwig();
        $dadosTemplate = [
            'titulo' => 'Dashboard',
            'usuario' => $_SESSION['usuario'] ?? null
        ];

        return $twig->render($response, 'dashboard.html', $dadosTemplate);
    }
}
