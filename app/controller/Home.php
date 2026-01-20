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
            'titulo' => 'Página inicial',
            'usuario' => $_SESSION['usuario'] ?? null // se quiser usar no Twig
        ];

        return $twig->render($response, 'home.html', $dadosTemplate);
    }
}
