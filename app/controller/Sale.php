<?php

namespace app\controller;

use app\database\builder\InsertQuery;
use app\database\builder\SelectQuery;
use app\database\builder\UpdateQuery;
use app\database\builder\DeleteQuery;

class Sale extends Base
{
    public function cadastro($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Página inicial',
            'acao' => 'c'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('sale'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function lista($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Página inicial'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('listsale'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function get($request, $response, $args)
    {
        try {
            $id = $args['id'] ?? null;
            
            if (is_null($id) || empty($id)) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Por favor informe o ID da venda'
                ], 400);
            }

            // Buscar dados da venda
            $sale = SelectQuery::select()
                ->from('sale')
                ->where('id', '=', $id)
                ->fetch();

            if (!$sale) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Venda não encontrada'
                ], 404);
            }

            // Buscar itens da venda
            $items = SelectQuery::select('item_sale.id, item_sale.id_produto, item_sale.quantidade, item_sale.preco_unitario, item_sale.total')
                ->from('item_sale')
                ->where('item_sale.id_venda', '=', $id)
                ->fetchAll();
            
            // Buscar dados dos produtos separadamente
            foreach ($items as &$item) {
                $product = SelectQuery::select('nome, codigo')
                    ->from('product')
                    ->where('id', '=', $item['id_produto'])
                    ->fetch();
                $item['produto_nome'] = $product['nome'] ?? '';
                $item['produto_codigo'] = $product['codigo'] ?? '';
            }

            return $this->SendJson($response, [
                'status' => true,
                'data' => [
                    'sale' => $sale,
                    'items' => $items
                ]
            ], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function insert($request, $response)
    {
        $form = $request->getParsedBody();
        
        $customer = SelectQuery::select('id')
            ->from('customer')
            ->order('id', 'asc')
            ->limit(1)
            ->fetch();
        
        if (!$customer) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Restrição: Nenhum cliente encontrado!',
                'id' => 0
            ], 403);
        }
        
        $id_customer = $customer['id'];
        
        $FieldAndValue = [
            'id_cliente' => $id_customer,
            'total_bruto' => 0,
            'total_liquido' => 0,
            'desconto' => 0,
            'acrescimo' => 0,
            'observacao' => ''
        ];
        
        try {
            $IsInserted = InsertQuery::table('sale')->save($FieldAndValue);
            if (!$IsInserted) {
                return $this->SendJson(
                    $response,
                    [
                        'status' => false,
                        'msg' => 'Restrição: Falha ao inserir a venda!',
                        'id' => 0
                    ],
                    403
                );
            }
            $sale = SelectQuery::select('id')
                ->from('sale')
                ->order('id', 'desc')
                ->limit(1)
                ->fetch();
            if (!$sale) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Restrição: Nenhuma venda encontrada!',
                    'id' => 0
                ], 403);
            }
            $id_sale = $sale["id"];
            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Venda inserida com sucesso!',
                'id' => $id_sale
            ], 201);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Restrição: ' . $e->getMessage(),
                'id' => 0
            ], 500);
        }
    }

    public function update($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            $id = $form['id'] ?? null;
            
            if (is_null($id) || empty($id)) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Por favor informe o ID da venda',
                    'id' => 0
                ], 500);
            }

            $normalizarValor = function ($valor) {
                if (is_string($valor)) {
                    $valor = str_replace('.', '', $valor);
                    $valor = str_replace(',', '.', $valor);
                }
                return floatval($valor) ?? 0;
            };

            $FieldAndValues = [
                'desconto' => $normalizarValor($form['desconto'] ?? 0),
                'acrescimo' => $normalizarValor($form['acrescimo'] ?? 0),
                'observacao' => $form['observacao'] ?? '',
                'data_atualizacao' => date('Y-m-d H:i:s')
            ];

            if (isset($form['id_usuario']) && !empty($form['id_usuario'])) {
                $FieldAndValues['id_usuario'] = $form['id_usuario'];
            }

            if (isset($form['id_cliente']) && !empty($form['id_cliente'])) {
                $FieldAndValues['id_cliente'] = $form['id_cliente'];
            }

            $IsUpdate = UpdateQuery::table('sale')
                ->set($FieldAndValues)
                ->where('id', '=', $id)
                ->update();

            if (!$IsUpdate) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Restrição: Falha ao atualizar a venda',
                    'id' => 0
                ], 403);
            }

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Venda atualizada com sucesso!',
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Restrição: ' . $e->getMessage(),
                'id' => 0
            ], 500);
        }
    }

    public function insertItem($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            
            $id_venda = $form['id_venda'] ?? null;
            $id_produto = $form['id_produto'] ?? null;
            $quantidade = floatval($form['quantidade'] ?? 1);
            $preco_unitario = floatval(str_replace(',', '.', str_replace('.', '', $form['preco_unitario'] ?? 0)));

            if (is_null($id_venda) || empty($id_venda)) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Restrição: O ID da venda é obrigatório!'
                ], 403);
            }

            if (is_null($id_produto) || empty($id_produto)) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Restrição: O ID do produto é obrigatório!'
                ], 403);
            }

            $product = SelectQuery::select('preco_venda')
                ->from('product')
                ->where('id', '=', $id_produto)
                ->fetch();

            if (!$product) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Restrição: Produto não encontrado!'
                ], 403);
            }

            if ($preco_unitario == 0) {
                $preco_unitario = floatval($product['preco_venda']);
            }

            $total = $preco_unitario * $quantidade;

            $FieldAndValue = [
                'id_venda' => $id_venda,
                'id_produto' => $id_produto,
                'quantidade' => $quantidade,
                'preco_unitario' => $preco_unitario,
                'total' => $total
            ];

            $IsInserted = InsertQuery::table('item_sale')->save($FieldAndValue);

            if (!$IsInserted) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Restrição: Falha ao inserir o item!'
                ], 403);
            }

            $this->updateSaleTotals($id_venda);

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Item inserido com sucesso!'
            ], 201);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Restrição: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteItem($request, $response, $args)
    {
        try {
            $id = $args['id'];
            
            if (is_null($id) || empty($id)) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Por favor informe o ID do item'
                ], 400);
            }

            $item = SelectQuery::select('id_venda')
                ->from('item_sale')
                ->where('id', '=', $id)
                ->fetch();

            if (!$item) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Item não encontrado'
                ], 404);
            }

            $id_venda = $item['id_venda'];

            $IsDelete = DeleteQuery::table('item_sale')
                ->where('id', '=', $id)
                ->delete();

            if (!$IsDelete) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Erro ao deletar o item'
                ], 403);
            }

            $this->updateSaleTotals($id_venda);

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Item excluído com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Restrição: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deletar($request, $response, $args)
    {
        try {
            $id = $args['id'];
            
            if (is_null($id) || empty($id)) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Por favor informe o ID da venda'
                ], 400);
            }

            $IsDelete = DeleteQuery::table('sale')
                ->where('id', '=', $id)
                ->delete();

            if (!$IsDelete) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Erro ao deletar a venda'
                ], 403);
            }

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Venda excluída com sucesso!',
                'id' => $id
            ], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Restrição: ' . $e->getMessage()
            ], 500);
        }
    }

    public function alterar($request, $response, $args = [])
    {
        $id = $args['id'] ?? null;
        
        $dadosTemplate = [
            'titulo' => 'Página inicial',
            'acao' => 'a',
            'id' => $id
        ];
        
        return $this->getTwig()
            ->render($response, $this->setView('sale'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function excluir($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Página inicial',
            'acao' => 'e'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('sale'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function print($request, $response)
    {
        try {
            $dadosTemplate = [
                'titulo' => 'Impressão de Venda'
            ];
            return $this->getTwig()
                ->render($response, $this->setView('printsale'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Método auxiliar para atualizar os totais da venda
     */
    private function updateSaleTotals($id_venda)
    {
        try {
            // Calcular o total dos itens - usando fetchAll e somando manualmente
            $items = SelectQuery::select('total')
                ->from('item_sale')
                ->where('id_venda', '=', $id_venda)
                ->fetchAll();

            $total_bruto = 0;
            foreach ($items as $item) {
                $total_bruto += floatval($item['total'] ?? 0);
            }

            // Buscar desconto e acréscimo da venda
            $sale = SelectQuery::select('desconto, acrescimo')
                ->from('sale')
                ->where('id', '=', $id_venda)
                ->fetch();

            $desconto = floatval($sale['desconto'] ?? 0);
            $acrescimo = floatval($sale['acrescimo'] ?? 0);

            $total_liquido = $total_bruto - $desconto + $acrescimo;

            // Atualizar a venda
            UpdateQuery::table('sale')
                ->set([
                    'total_bruto' => $total_bruto,
                    'total_liquido' => $total_liquido,
                    'data_atualizacao' => date('Y-m-d H:i:s')
                ])
                ->where('id', '=', $id_venda)
                ->update();

            return true;
        } catch (\Exception $e) {
            error_log('Erro ao atualizar totais da venda: ' . $e->getMessage());
            return false;
        }
    }
}
