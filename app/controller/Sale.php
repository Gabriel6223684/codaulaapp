<?php

namespace app\controller;

use app\database\builder\SelectQuery;
use app\database\builder\InsertQuery;
use app\database\builder\UpdateQuery;
use app\database\builder\DeleteQuery;

class Sale extends Base
{
    public function lista($request, $response)
    {
        try {
            $sales = SelectQuery::select()->from('sale')->order('id', 'desc')->fetchAll();
            $dadosTemplate = [
                'titulo' => 'Pesquisa de vendas',
                'sales' => $sales
            ];
            return $this->getTwig()
                ->render($response, $this->setView('listsale'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function cadastro($request, $response)
    {
        try {
            $products = SelectQuery::select()->from('product')->order('id', 'asc')->fetchAll();
            $dadosTemplate = [
                'acao' => 'c',
                'titulo' => 'Cadastro e edição de vendas',
                'products' => $products
            ];
            return $this->getTwig()
                ->render($response, $this->setView('sale'), $dadosTemplate)
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
            $sale = SelectQuery::select()->from('sale')->where('id', '=', $id)->fetch();
            $items = SelectQuery::select()->from('sale_items')->where('sale_id', '=', $id)->fetchAll();
            $products = SelectQuery::select()->from('product')->order('id', 'asc')->fetchAll();
            $dadosTemplate = [
                'acao' => 'e',
                'id' => $id,
                'titulo' => 'Cadastro e edição de vendas',
                'sale' => $sale,
                'items' => $items,
                'products' => $products
            ];
            return $this->getTwig()
                ->render($response, $this->setView('sale'), $dadosTemplate)
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
                'numero_nota' => $form['numero_nota'] ?? '',
                'descricao' => $form['descricao'] ?? '',
                'total' => $form['total'] ?? 0
            ];
            
            $IsSave = InsertQuery::table('sale')->save($FieldAndValues);
            if (!$IsSave) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao inserir: ' . $IsSave, 'id' => 0], 403);
            }
            $sale = SelectQuery::select('id')->from('sale')->order('id', 'desc')->fetch();
            if (!$sale || !isset($sale['id'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: não foi possível recuperar o ID do registro inserido', 'id' => 0], 500);
            }
            return $this->SendJson($response, ['status' => true, 'msg' => 'Salvo com sucesso', 'id' => $sale['id']], 201);
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
                'numero_nota' => $form['numero_nota'] ?? '',
                'descricao' => $form['descricao'] ?? '',
                'total' => $form['total'] ?? 0
            ];
            $IsUpdate = UpdateQuery::table('sale')->set($FieldAndValues)->where('id', '=', $id)->update();
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
            $IsDelete = DeleteQuery::table('sale')->where('id', '=', $id)->delete();
            if (!$IsDelete) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao deletar a venda', 'id' => 0], 403);
            }
            return $this->SendJson($response, ['status' => true, 'msg' => 'Venda excluída com sucesso!', 'id' => $id], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }

    public function print($request, $response)
    {
        try {
            $sales = SelectQuery::select()->from('sale')->order('id', 'desc')->fetchAll();
            $dadosTemplate = [
                'titulo' => 'Relatório de Vendas',
                'sales' => $sales
            ];
            return $this->getTwig()
                ->render($response, $this->setView('printsale'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function insertItem($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            
            if (!$form || !is_array($form)) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: corpo da requisição vazio ou inválido'], 400);
            }
            
            $saleId = $form['sale_id'] ?? 0;
            // If no sale_id provided, create a new sale
            if (empty($saleId) || $saleId == 0) {
                $IsSaleSave = InsertQuery::table('sale')->save(['numero_nota' => '', 'descricao' => '', 'total' => 0]);
                if (!$IsSaleSave) {
                    return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao criar venda'], 500);
                }
                $sale = SelectQuery::select('id')->from('sale')->order('id', 'desc')->fetch();
                if (!$sale || !isset($sale['id'])) {
                    return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao recuperar id da venda'], 500);
                }
                $saleId = $sale['id'];
            }

            $quantidade = intval($form['quantidade'] ?? 1);
            // sanitize numeric input: remove thousand separators and replace comma decimal
            $rawPreco = $form['preco_unitario'] ?? 0;
            if (is_string($rawPreco)) {
                $rawPreco = str_replace('.', '', $rawPreco);
                $rawPreco = str_replace(',', '.', $rawPreco);
            }
            $preco = floatval($rawPreco);
            $FieldAndValues = [
                'sale_id' => $saleId,
                'product_id' => $form['product_id'] ?? 0,
                'quantidade' => $quantidade,
                'preco_unitario' => $preco,
                'subtotal' => $quantidade * $preco
            ];

            $IsSave = InsertQuery::table('sale_items')->save($FieldAndValues);
            if (!$IsSave) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao inserir item'], 403);
            }
            return $this->SendJson($response, ['status' => true, 'msg' => 'Item adicionado com sucesso', 'sale_id' => $saleId], 201);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage()], 500);
        }
    }

    public function deleteItem($request, $response, $args)
    {
        try {
            $id = $args['id'];
            if (is_null($id) || empty($id)) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Por favor informe o ID'], 400);
            }
            $IsDelete = DeleteQuery::table('sale_items')->where('id', '=', $id)->delete();
            if (!$IsDelete) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao deletar o item'], 403);
            }
            return $this->SendJson($response, ['status' => true, 'msg' => 'Item removido com sucesso!'], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage()], 500);
        }
    }
}