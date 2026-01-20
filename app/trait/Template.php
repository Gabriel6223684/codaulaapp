<?php
namespace app\trait;

use Slim\Views\Twig;

trait Template
{
    public function getTwig()
    {
        $twig = Twig::create(DIR_VIEW);
        $twig->getEnvironment()->addGlobal('EMPRESA', 'VentreMinex');
        $twig->getEnvironment()->addGlobal('session', $_SESSION); // disponibiliza session no Twig
        return $twig;
    }

    public function setView($name)
    {
        return $name . EXT_VIEW;
    }

    public function SendJson($response, array $data = [], int $statusCode = 200)
    {
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
