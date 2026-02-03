<?php

namespace app\controller;

class PaymentTerms extends Base
{
    // Lista os termos de pagamento
    public function lista($request, $response)
    {
        $templateData = [
            'titulo' => 'Lista de termos de pagamento'
        ];

        return $this->getTwig()
            ->render($response, $this->setView('listpaymentterms.html'), $templateData)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    // Cadastro de termos de pagamento
    public function cadastro($request, $response)
    {
        $templateData = [
            'titulo' => 'Cadastro de termos de pagamento'
        ];

        return $this->getTwig()
            ->render($response, $this->setView('paymentterms.html'), $templateData)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
}
