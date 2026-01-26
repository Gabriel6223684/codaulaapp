<?php

namespace app\controller;

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
            ->render($response, $this->setView('listuser'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function cadastro($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Cadastro de usuário'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('user'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function listuser($request, $response)
    {
        #Captura todas a variaveis de forma mais segura VARIAVEIS POST.
        $form = $request->getParsedBody();
        #Qual a coluna da tabela deve ser ordenada.
        $order = $form['order'][0]['column'];
        #Tipo de ordenação
        $orderType = $form['order'][0]['dir'];
        #Em qual registro se inicia o retorno dos registro, OFFSET
        $start = $form['start'];
        #Limite de registro a serem retornados do banco de dados LIMIT
        $length = $form['length'];
        $fields = [
            0 => 'id',
            1 => 'nome_completo',
            2 => 'email',
            3 => 'cpf'
        ];
        #Capturamos o nome do capo a ser ordenado.
        $orderField = $fields[$order];
        #O termo pesquisado
        $term = $form['search']['value'];
        $query = SelectQuery::select()
            ->from('usuario');
        if (!is_null($term) && ($term !== '')) {
            $query->where('usuario.nome_completo', 'ilike', "%{$term}%", 'or')
                ->where('usuario.email', 'ilike', "%{$term}%", 'or')
                ->where('usuario.cpf', 'ilike', "%{$term}%");
        }
        $users = $query
            ->order($orderField, $orderType)
            ->limit($length, $start)
            ->fetchAll();
        $userData = [];
        foreach ($users as $key => $value) {
            $userData[$key] = [
                $value['id'],
                $value['nome_completo'],
                $value['cpf'],
                $value['email'],
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
        $payload = json_encode($data);

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
    public function insert($request, $response)
    {
        $form = $request->getParsedBody();

        $dados = [
            'nome_completo' => $form['nome'],
            'cpf'           => $form['cpfcnpj'],
            'email'         => $form['email'],
            'senha'         => password_hash($form['senha'], PASSWORD_DEFAULT)
        ];

        try {
            $ok = InsertQuery::table('usuario')->save($dados);

            $response->getBody()->write(json_encode([
                'status' => $ok,
                'msg' => $ok ? 'Usuário salvo com sucesso' : 'Falha ao inserir'
            ]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            $response->getBody()->write(json_encode([
                'status' => false,
                'msg' => $e->getMessage()
            ]));

            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    public function editar($request, $response)
    {
        $form = $request->getParsedBody();

        $id = $form['id'] ?? null;
        $nome = $form['nome'] ?? null;
        $cpfcnpj = $form['cpfcnpj'] ?? null;
        $email = $form['email'] ?? null;
        $senha = $form['senha'] ?? null;

        if (!$id || !$nome || !$cpfcnpj || !$email) {
            $response->getBody()->write(json_encode(['status' => false, 'msg' => 'Campos obrigatórios faltando']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $dados = [
            'nome_completo' => $nome,
            'cpf' => $cpfcnpj,
            'email' => $email
        ];

        if (!empty($senha)) {
            $dados['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }

        try {
            $ok = \app\database\builder\UpdateQuery::table('usuario')
                ->set($dados)
                ->where('id', '=', $id)
                ->update();

            $response->getBody()->write(json_encode([
                'status' => $ok,
                'msg' => $ok ? 'Usuário atualizado' : 'Nenhuma alteração feita'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['status' => false, 'msg' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    public function excluir($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        if (!$id) {
            $response->getBody()->write(json_encode(['status' => false, 'msg' => 'ID inválido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $ok = \app\database\builder\DeleteQuery::table('usuario')
                ->where('id', '=', $id)
                ->delete();

            $response->getBody()->write(json_encode([
                'status' => $ok,
                'msg' => $ok ? 'Usuário excluído' : 'Nenhum usuário encontrado'
            ]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['status' => false, 'msg' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}