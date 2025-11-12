<?php

namespace app\controller;

use app\database\builder\InsertQuery;

class Cliente extends Base
{
    public function lista($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Lista de cliente'
        ];

        $response = $this->getTwig()->render(
            $response,
            $this->setView('listacliente'),
            $dadosTemplate
        );

        return $response->withHeader('Content-Type', 'text/html')->withStatus(200);
    }

    public function cadastro($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Cadastro de cliente'
        ];

        $response = $this->getTwig()->render(
            $response,
            $this->setView('cliente'),
            $dadosTemplate
        );

        return $response->withHeader('Content-Type', 'text/html')->withStatus(200);
    }

    public function insert($request, $response)
    {
        try {
            $nome = $_POST['nome'] ?? '';
            $sobrenome = $_POST['sobrenome'] ?? '';
            $cpf = $_POST['cpf'] ?? '';
            $rg = $_POST['rg'] ?? '';

            $FieldsAndValues = [
                'nome_fantasia' => $nome,
                'sobrenome_razao' => $sobrenome,
                'cpf_cnpj' => $cpf,
                'rg_ie' => $rg
            ];

            // Verifique se a classe InsertQuery existe e foi importada corretamente
            $IsSave = InsertQuery::table('cliente')->save($FieldsAndValues);

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
}
