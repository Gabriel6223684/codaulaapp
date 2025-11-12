<?php

namespace app\controller;

class User extends Base
{
    public function lista($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Lista de usuário'
        ];

        return $this->getTwig()
            ->render($response, $this->setView('listuser'), $dadosTemplate);
    }

    public function cadastro($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Cadastro de usuário'
        ];

        return $this->getTwig()
            ->render($response, $this->setView('user'), $dadosTemplate);
    }
}
