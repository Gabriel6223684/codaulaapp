<?php

namespace app\controller;

use app\database\builder\DeleteQuery;
use app\database\builder\InsertQuery;
use app\database\builder\SelectQuery;

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
    public function listuser($request, $response)
    {
        $form = $request->getParseBody();
        $order = $form['order'][0]['column'];
        $orderType = $form['order'][0]['dir'];
        $start = $form['start'];
        $length = $form['length'];
        $term = $form['search']['value'];
        var_dump($term);

        $query = SelectQuery::select('id,nome,sobrenome')->from('usuario');
        /*if (!is_null($term) && ($term !== '')) {
            $query->where('usuario.nome', 'ilike', "{%$term%}", 'or')
                ->where('usuario.sobrenome', 'ilike', "{%$term%}");
        }*/
        $users = $query->fetchAll();
        $userData = [];
        foreach ($users as $key => $value) {
            $userData[$key] = [
                $value['id'],
                $value['nome'],
                $value['sobrenome'],
                "<button class='btn btn-warning'>Editar</button>
                <button class='btn btn-danger'>Excluir</button>"
            ];
        }
        $data = [
            'status' => true,
            'recordsTotal' => count($users),
            'recordsFiltered' => count($users),
            'data' => $userData
        ];
    }
}
