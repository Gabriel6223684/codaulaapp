<?php

namespace app\controller;

use app\database\builder\InsertQuery;
use app\database\builder\SelectQuery;

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

    public function listacliente($request, $response)
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
            1 => 'nome',
            2 => 'email',
            3 => 'cpf_cnpj',
            3 => 'senha'
        ];
        #Capturamos o nome do capo a ser ordenado.
        $orderField = $fields[$order];
        #O termo pesquisado
        $term = $form['search']['value'];
        $query = SelectQuery::select()
            ->from('cliente');
        if (!is_null($term) && ($term !== '')) {
            $query->where('cliente.nome', 'ilike', "%{$term}%", 'or')
                ->where('cliente.email', 'ilike', "%{$term}%", 'or')
                ->where('cliente.cpf_cnpj', 'ilike', "%{$term}%")
                ->where('cliente.senha', 'ilike', "%{$term}");
        }
        $cliente = $query
            ->order($orderField, $orderType)
            ->limit($length, $start)
            ->fetchAll();
        $clienteData = [];
        foreach ($cliente as $key => $value) {
            $clienteData[$key] = [
                $value['id'],
                $value['nome'],
                $value['cpf_cnpj'],
                $value['email'],
                $value['senha'],
                "<button class='btn btn-warning'>Editar</button>
                <button class='btn btn-danger'>Excluir</button>"
            ];
        }
        $data = [
            'status' => true,
            'recordsTotal' => count($cliente),
            'recordsFiltered' => count($cliente),
            'data' => $clienteData
        ];
        $payload = json_encode($data);

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
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
        $form = $request->getParsedBody();

        // 1. Validação dos campos obrigatórios
        $requiredFields = ['nome', 'cpf_cnpj', 'email', 'senha'];
        foreach ($requiredFields as $field) {
            if (empty($form[$field])) {
                $response->getBody()->write(json_encode([
                    'status' => false,
                    'msg' => "O campo '{$field}' é obrigatório."
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            }
        }

        // 2. Preparar dados
        $dados = [
            'nome'     => $form['nome'],
            'cpf_cnpj' => $form['cpf_cnpj'],
            'email'    => $form['email'],
            'senha'    => password_hash($form['senha'], PASSWORD_DEFAULT)
        ];

        try {
            // 3. Tentar salvar no banco
            $ok = InsertQuery::table('cliente')->save($dados);

            // 4. Resposta de sucesso ou falha
            $response->getBody()->write(json_encode([
                'status' => $ok,
                'msg' => $ok ? 'Cliente salvo com sucesso' : 'Falha ao inserir o cliente'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // 5. Log da exceção para depuração
            error_log("Erro ao inserir cliente: " . $e->getMessage());

            $response->getBody()->write(json_encode([
                'status' => false,
                'msg' => "Erro ao inserir cliente: " . $e->getMessage()
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
