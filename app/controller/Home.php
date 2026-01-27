<?php

namespace app\controller;

class Home extends Base
{
    public function home($request, $response)
    {
        $twig = $this->getTwig();
        $dadosTemplate = [
            'titulo' => 'Dashboard',
            'usuario' => $_SESSION['usuario'] ?? null
        ];
        return $twig->render($response, 'dashboard.html', $dadosTemplate);
    }
}
