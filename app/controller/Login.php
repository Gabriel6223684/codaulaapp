<?php

namespace app\controller;

use app\database\builder\InsertQuery;
use app\database\builder\SelectQuery;

class Login extends Base
{
    public function login($request, $response)
    {
        try {
            $dadosTemplate = [
                'titulo' => 'Autenticação'
            ];
            return $this->getTwig()
                ->render($response, $this->setView('login'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die;
        }
    }

    public function precadastro($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            $dadosUsuario = [
                'nome' => $form['nome'],
                'sobrenome' => $form['sobrenome'],
                'cpf' => $form['cpf'],
                'rg' => $form['rg'],
                'senha' => password_hash($form['senha'], PASSWORD_DEFAULT)
            ];
            $IsInseted = InsertQuery::table('usuario')->save($dadosUsuario);
            if (!$IsInseted) {
                return $this->SendJson(
                    $response,
                    ['status' => false, 'msg' => 'Restrição: ', $IsInseted, 'id' => 0],
                    403
                );
            }
            $id = SelectQuery::select('id')->from('usuario')->order('id', 'desc')->fetch();
            $id_usuario = $id['id'];
        } catch (\Exception $e) {
        }
    }

    public function autenticar($request, $response)
    {
        try {
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die;
        }
    }
}
