<?php
namespace app\controller;

class Sale extends Base
{
    public function finalizar($request, $response)
    {
        $data = $request->getParsedBody();

        // Validação
        if (empty($data['cart']) || count($data['cart']) === 0) {
            return $response->withJson(['status' => false, 'msg' => 'Carrinho vazio!'], 400);
        }

        if (!isset($data['paymentMethod']) || $data['paymentMethod']['type'] !== 'pix') {
            return $response->withJson(['status' => false, 'msg' => 'Pagamento inválido!'], 400);
        }

        // Simula salvar venda no banco
        $pedido = [
            'cart' => $data['cart'],
            'discount' => $data['discount'],
            'paymentMethod' => $data['paymentMethod'],
            'total' => $data['total'],
            'data_hora' => date('Y-m-d H:i:s')
        ];

        // Aqui você faria algo como:
        // $this->db->table('sales')->insert($pedido);

        return $response->withJson(['status' => true, 'msg' => 'Venda finalizada via PIX com sucesso!']);
    }
}
