<?php

namespace app\controller;

use app\database\builder\SelectQuery;
use app\database\builder\InsertQuery;
use app\database\builder\UpdateQuery;
use app\database\builder\DeleteQuery;

class Product extends Base
{
    public function lista($request, $response)
    {
        try {
            $products = SelectQuery::select()->from('product')->order('id', 'desc')->fetchAll();
            $dadosTemplate = [
                'titulo' => 'Pesquisa de produtos',
                'products' => $products
            ];
            return $this->getTwig()
                ->render($response, $this->setView('listproduct'), $dadosTemplate)
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
                'titulo' => 'Cadastro e edição de produtos'
            ];
            return $this->getTwig()
                ->render($response, $this->setView('product'), $dadosTemplate)
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
            $product = SelectQuery::select()->from('product')->where('id', '=', $id)->fetch();
            $dadosTemplate = [
                'acao' => 'e',
                'id' => $id,
                'titulo' => 'Cadastro e edição de produtos',
                'product' => $product
            ];
            return $this->getTwig()
                ->render($response, $this->setView('product'), $dadosTemplate)
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

            // Sanitize numeric fields: convert comma to dot for PostgreSQL
            $normalizarPreco = function ($valor) {
                if (is_string($valor)) {
                    $valor = str_replace('.', '', $valor);
                    $valor = str_replace(',', '.', $valor);
                }
                return floatval($valor) ?? 0;
            };

            $FieldAndValues = [
                'nome' => $form['nome'] ?? '',
                'codigo' => $form['codigo'] ?? '',
                'codigo_barra' => $form['codigo_barra'] ?? '',
                'descricao' => $form['descricao'] ?? '',
                'preco_custo' => $normalizarPreco($form['preco_custo'] ?? 0),
                'preco_venda' => $normalizarPreco($form['preco_venda'] ?? 0),
                'ativo' => (isset($form['ativo']) && $form['ativo'] === '1') ? true : false
            ];
            if (isset($form['fornecedor_id']) && $form['fornecedor_id'] !== '') {
                $FieldAndValues['fornecedor_id'] = $form['fornecedor_id'];
            }
            $IsSave = InsertQuery::table('product')->save($FieldAndValues);
            if (!$IsSave) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao inserir: ' . $IsSave, 'id' => 0], 403);
            }
            $product = SelectQuery::select('id')->from('product')->order('id', 'desc')->fetch();
            if (!$product || !isset($product['id'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: não foi possível recuperar o ID do registro inserido', 'id' => 0], 500);
            }
            return $this->SendJson($response, ['status' => true, 'msg' => 'Salvo com sucesso', 'id' => $product['id']], 201);
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

            // Sanitize numeric fields: convert comma to dot for PostgreSQL
            $normalizarPreco = function ($valor) {
                if (is_string($valor)) {
                    $valor = str_replace('.', '', $valor);
                    $valor = str_replace(',', '.', $valor);
                }
                return floatval($valor) ?? 0;
            };

            $FieldAndValues = [
                'nome' => $form['nome'] ?? '',
                'codigo' => $form['codigo'] ?? '',
                'codigo_barra' => $form['codigo_barra'] ?? '',
                'descricao' => $form['descricao'] ?? '',
                'preco_custo' => $normalizarPreco($form['preco_custo'] ?? 0),
                'preco_venda' => $normalizarPreco($form['preco_venda'] ?? 0),
                'fornecedor_id' => $form['fornecedor_id'] ?? null,
                'ativo' => $form['ativo'] ?? true
            ];
            $IsUpdate = UpdateQuery::table('product')->set($FieldAndValues)->where('id', '=', $id)->update();
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
            $IsDelete = DeleteQuery::table('product')->where('id', '=', $id)->delete();
            if (!$IsDelete) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao deletar o produto', 'id' => 0], 403);
            }
            return $this->SendJson($response, ['status' => true, 'msg' => 'Produto excluído com sucesso!', 'id' => $id], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }
    public function print($request, $response)
    {
        try {
            $products = SelectQuery::select()->from('product')->order('id', 'desc')->fetchAll();
            $dadosTemplate = [
                'titulo' => 'Relatório de Produtos',
                'products' => $products
            ];
            return $this->getTwig()
                ->render($response, $this->setView('printproduct'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function listproductdata($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            $search = $form['search'] ?? '';
            $page = isset($form['page']) ? intval($form['page']) : 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;

            // Se há termo de busca, filtra por nome, código ou código de barras
            if (!empty($search)) {
                $products = SelectQuery::select('id, nome, codigo, codigo_barra, preco_venda')
                    ->from('product')
                    ->where('ativo', '=', 'true')
                    ->order('nome', 'asc')
                    ->limit($perPage, $offset)
                    ->fetchAll();
                
                // Filtro manual em PHP para buscar no nome, código ou código de barras
                $products = array_filter($products, function($product) use ($search) {
                    $searchLower = strtolower($search);
                    return strpos(strtolower($product['nome']), $searchLower) !== false ||
                           strpos(strtolower($product['codigo']), $searchLower) !== false ||
                           strpos(strtolower($product['codigo_barra']), $searchLower) !== false;
                });
            } else {
                $products = SelectQuery::select('id, nome, codigo, codigo_barra, preco_venda')
                    ->from('product')
                    ->where('ativo', '=', 'true')
                    ->order('nome', 'asc')
                    ->limit($perPage, $offset)
                    ->fetchAll();
            }

            // Formata os dados para o Select2
            $results = [];
            foreach ($products as $product) {
                $results[] = [
                    'id' => $product['id'],
                    'text' => $product['nome'] . ' - ' . $product['codigo'] . ' (R$ ' . number_format($product['preco_venda'], 2, ',', '.') . ')',
                    'nome' => $product['nome'],
                    'codigo' => $product['codigo'],
                    'codigo_barra' => $product['codigo_barra'],
                    'preco_venda' => $product['preco_venda']
                ];
            }

            return $this->SendJson($response, [
                'status' => true,
                'results' => $results
            ], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get current stock for a product
     */
    public function getStock($request, $response, $args)
    {
        try {
            $id = $args['id'] ?? null;
            
            if (is_null($id) || empty($id)) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Por favor informe o ID do produto'
                ], 400);
            }

            // Buscar produto
            $product = SelectQuery::select('id, nome, codigo')
                ->from('product')
                ->where('id', '=', $id)
                ->fetch();

            if (!$product) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Produto não encontrado'
                ], 404);
            }

            // Calcular estoque atual a partir dos movimentos
            $movements = SelectQuery::select()
                ->from('stock_movement')
                ->where('id_produto', '=', $id)
                ->fetchAll();

            $estoque_atual = 0;
            foreach ($movements as $mov) {
                $entrada = floatval($mov['quantidade_entrada'] ?? 0);
                $saida = floatval($mov['quantidade_saida'] ?? 0);
                $estoque_atual += $entrada - $saida;
            }

            return $this->SendJson($response, [
                'status' => true,
                'data' => [
                    'id' => $product['id'],
                    'nome' => $product['nome'],
                    'codigo' => $product['codigo'],
                    'estoque_atual' => $estoque_atual
                ]
            ], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adjust stock for a product
     */
    public function adjustStock($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            
            $id_produto = $form['id_produto'] ?? null;
            $quantidade = floatval(str_replace(',', '.', $form['quantidade'] ?? 0));
            $tipo = $form['tipo'] ?? 'entrada'; // 'entrada' or 'saida'
            $observacao = $form['observacao'] ?? '';

            if (is_null($id_produto) || empty($id_produto)) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Por favor informe o ID do produto'
                ], 400);
            }

            if ($quantidade <= 0) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'A quantidade deve ser maior que zero'
                ], 400);
            }

            // Buscar produto
            $product = SelectQuery::select('id, nome')
                ->from('product')
                ->where('id', '=', $id_produto)
                ->fetch();

            if (!$product) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Produto não encontrado'
                ], 404);
            }

            // Calcular estoque atual
            $movements = SelectQuery::select()
                ->from('stock_movement')
                ->where('id_produto', '=', $id_produto)
                ->fetchAll();

            $estoque_atual = 0;
            foreach ($movements as $mov) {
                $entrada = floatval($mov['quantidade_entrada'] ?? 0);
                $saida = floatval($mov['quantidade_saida'] ?? 0);
                $estoque_atual += $entrada - $saida;
            }

            // Determinar quantidade de entrada ou saída
            if ($tipo === 'saida') {
                $quantidade_entrada = null;
                $quantidade_saida = $quantidade;
                $novo_estoque = $estoque_atual - $quantidade;
            } else {
                $quantidade_entrada = $quantidade;
                $quantidade_saida = null;
                $novo_estoque = $estoque_atual + $quantidade;
            }

            // Criar registro de movimento de estoque
            $FieldAndValues = [
                'id_produto' => $id_produto,
                'quantidade_entrada' => $quantidade_entrada,
                'quantidade_saida' => $quantidade_saida,
                'estoque_atual' => $novo_estoque,
                'observacao' => $observacao,
                'tipo' => $tipo,
                'origem_movimento' => 'ajuste'
            ];

            $IsInserted = InsertQuery::table('stock_movement')->save($FieldAndValues);

            if (!$IsInserted) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Erro ao registrar movimento de estoque'
                ], 403);
            }

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Estoque ajustado com sucesso!',
                'data' => [
                    'estoque_anterior' => $estoque_atual,
                    'estoque_atual' => $novo_estoque,
                    'quantidade' => $quantidade,
                    'tipo' => $tipo
                ]
            ], 201);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }
}
