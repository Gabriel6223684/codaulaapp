<?php

namespace app\controller;

use app\database\builder\DeleteQuery;
use app\database\builder\InsertQuery;

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
    public function insert($request, $response)
    {
        try {

            $nome = $_POST['nome'] ?? '';
            $cpfcnpj = $_POST['cpfcnpj'] ?? '';
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';

            $FieldsAndValues = [
                'nome_completo' => $nome,
                'cpf' => $cpfcnpj,
                'email' => $email,
                'senha' => $senha
            ];
            // Verifique se a classe InsertQuery existe e foi importada corretamente
            $IsSave = InsertQuery::table('usuario')->save($FieldsAndValues);


            if (!$IsSave) {
                $response->getBody()->write('Erro ao salvar');
                return $response->withStatus(500);
            }

            $response->getBody()->write('Salvo com sucesso!');
            return $response->withStatus(200);
        } catch (\Throwable $th) {
            $response->getBody()->write('Erro: ' . $th->getMessage());
            return $response->withStatus(500);
        }
    }
    public function delete($request, $response)
    {
        try {
            $nome = $_POST['nome'] ?? '';
            $sobrenome = $_POST['sobrenome'] ?? '';
            $cpf = $_POST['cpf'] ?? '';
            $rg = $_POST['rg'] ?? '';

            $FieldsAndValues = [
                'nome_completo' => $nome,
                'sobrenome_razao' => $sobrenome,
                'cpf_cnpj' => $cpf,
                'rg_ie' => $rg
            ];

            // Verifique se a classe InsertQuery existe e foi importada corretamente
            $IsDelete = DeleteQuery::table('usuario')->delete($FieldsAndValues);

            if (!$IsDelete) {
                $response->getBody()->write('Erro ao deletar');
                return $response->withStatus(500);
            }

            $response->getBody()->write('Deletado com sucesso!');
            return $response->withStatus(200);
        } catch (\Throwable $th) {
            $response->getBody()->write('Erro: ' . $th->getMessage());
            return $response->withStatus(500);
        }
    }
}
