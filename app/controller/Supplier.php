<?php

namespace app\controller;

use app\database\builder\SelectQuery;
use app\database\builder\InsertQuery;
use app\database\builder\UpdateQuery;
use app\database\builder\DeleteQuery;

class Supplier extends Base
{
    public function lista($request, $response)
    {
        try {
            $suppliers = SelectQuery::select()->from('supplier')->order('id', 'desc')->fetchAll();
            $dadosTemplate = [
                'titulo' => 'Pesquisa de fornecedores',
                'suppliers' => $suppliers
            ];
            return $this->getTwig()
                ->render($response, $this->setView('listsupplier'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function cadastro($request, $response)
    {
        try {
            $dadosTemplate = [
                'acao' => 'c',
                'titulo' => 'Cadastro e edição de fornecedores'
            ];
            return $this->getTwig()
                ->render($response, $this->setView('supplier'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function alterar($request, $response, $args)
    {
        try {
            $id = $args['id'];
            $supplier = SelectQuery::select()->from('supplier')->where('id', '=', $id)->fetch();
            $dadosTemplate = [
                'acao' => 'e',
                'id' => $id,
                'titulo' => 'Cadastro e edição de fornecedores',
                'supplier' => $supplier
            ];
            return $this->getTwig()
                ->render($response, $this->setView('supplier'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function insert($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            
            if (!$form || !is_array($form)) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: corpo da requisição vazio ou inválido', 'id' => 0], 400);
            }
            
            $FieldAndValues = [
                'nome' => $form['nome'] ?? '',
                'cnpj' => $form['cnpj'] ?? '',
                'email' => $form['email'] ?? '',
                'telefone' => $form['telefone'] ?? '',
                'endereco' => $form['endereco'] ?? '',
                'ativo' => $form['ativo'] ?? true
            ];
            
            $IsSave = InsertQuery::table('supplier')->save($FieldAndValues);
            if (!$IsSave) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao inserir: ' . $IsSave, 'id' => 0], 403);
            }
            $supplier = SelectQuery::select('id')->from('supplier')->order('id', 'desc')->fetch();
            if (!$supplier || !isset($supplier['id'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: não foi possível recuperar o ID do registro inserido', 'id' => 0], 500);
            }
            return $this->SendJson($response, ['status' => true, 'msg' => 'Salvo com sucesso', 'id' => $supplier['id']], 201);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function update($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            $id = $form['id'] ?? null;
            if (is_null($id) || empty($id)) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Por favor informe o ID', 'id' => 0], 500);
            }
            $FieldAndValues = [
                'nome' => $form['nome'] ?? '',
                'cnpj' => $form['cnpj'] ?? '',
                'email' => $form['email'] ?? '',
                'telefone' => $form['telefone'] ?? '',
                'endereco' => $form['endereco'] ?? '',
                'ativo' => $form['ativo'] ?? true
            ];
            $IsUpdate = UpdateQuery::table('supplier')->set($FieldAndValues)->where('id', '=', $id)->update();
            if (!$IsUpdate) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Restrição: ' . $IsUpdate, 'id' => 0], 403);
            }
            return $this->SendJson($response, ['status' => true, 'msg' => 'Atualizado com sucesso!', 'id' => $id]);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function deletar($request, $response, $args)
    {
        try {
            $id = $args['id'];
            if (is_null($id) || empty($id)) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Por favor informe o ID', 'id' => 0], 400);
            }
            $IsDelete = DeleteQuery::table('supplier')->where('id', '=', $id)->delete();
            if (!$IsDelete) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao deletar o fornecedor', 'id' => 0], 403);
            }
            return $this->SendJson($response, ['status' => true, 'msg' => 'Fornecedor excluído com sucesso!', 'id' => $id], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function print($request, $response)
    {
        try {
            $suppliers = SelectQuery::select()->from('supplier')->order('id', 'desc')->fetchAll();
            $dadosTemplate = [
                'titulo' => 'Relatório de Fornecedores',
                'suppliers' => $suppliers
            ];
            return $this->getTwig()
                ->render($response, $this->setView('printsupplier'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
