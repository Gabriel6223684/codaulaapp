<?php

namespace app\controller;

use app\database\builder\SelectQuery;
use app\database\builder\InsertQuery;
use app\database\builder\UpdateQuery;
use app\database\builder\DeleteQuery;

class Company extends Base
{
    public function lista($request, $response)
    {
        try {
            $empresas = SelectQuery::select()->from('empresa')->order('id', 'desc')->fetchAll();
            $dadosTemplate = [
                'titulo' => 'Pesquisa de empresas',
                'empresas' => $empresas
            ];
            return $this->getTwig()
                ->render($response, $this->setView('listcompany'), $dadosTemplate)
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
                'titulo' => 'Cadastro e edição'
            ];
            return $this->getTwig()
                ->render($response, $this->setView('company'), $dadosTemplate)
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
            $empresa = SelectQuery::select()->from('empresa')->where('id', '=', $id)->fetch();
            $dadosTemplate = [
                'acao' => 'e',
                'id' => $id,
                'titulo' => 'Cadastro e edição',
                'empresa' => $empresa
            ];
            return $this->getTwig()
                ->render($response, $this->setView('company'), $dadosTemplate)
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
                'endereco' => $form['endereco'] ?? ''
            ];
            
            $IsSave = InsertQuery::table('empresa')->save($FieldAndValues);
            if (!$IsSave) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao inserir: ' . $IsSave, 'id' => 0], 403);
            }
            $empresa = SelectQuery::select('id')->from('empresa')->order('id', 'desc')->fetch();
            if (!$empresa || !isset($empresa['id'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: não foi possível recuperar o ID do registro inserido', 'id' => 0], 500);
            }
            return $this->SendJson($response, ['status' => true, 'msg' => 'Salvo com sucesso', 'id' => $empresa['id']], 201);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }
    public function update($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            $id = $form['id'];
            if (is_null($id) || empty($id)) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Por favor informe o ID', 'id' => 0], 500);
            }
            $FieldAndValues = [
                'nome' => $form['nome'],
                'cnpj' => $form['cnpj'],
                'email' => $form['email'],
                'telefone' => $form['telefone'],
                'endereco' => $form['endereco']
            ];
            $IsUpdate = UpdateQuery::table('empresa')->set($FieldAndValues)->where('id', '=', $id)->update();
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
            $IsDelete = DeleteQuery::table('empresa')->where('id', '=', $id)->delete();
            if (!$IsDelete) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao deletar a empresa', 'id' => 0], 403);
            }
            return $this->SendJson($response, ['status' => true, 'msg' => 'Empresa excluída com sucesso!', 'id' => $id], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }
    public function print($request, $response)
    {
        try {
            $empresas = SelectQuery::select()->from('empresa')->order('id', 'desc')->fetchAll();
            $dadosTemplate = [
                'titulo' => 'Relatório de Empresas',
                'empresas' => $empresas
            ];
            return $this->getTwig()
                ->render($response, $this->setView('printcompany'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
