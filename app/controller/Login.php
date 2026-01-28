<?php

namespace app\controller;

class Login extends Base
{
    public function login($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Login'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('login'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
}
