<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SaleInstallments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('sale_installments', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'biginteger', ['identity' => true, 'null' => false])
            ->addColumn('id_venda', 'biginteger', ['null' => false])
            ->addColumn('numero_parcela', 'integer', ['null' => false, 'comment' => 'Número da parcela'])
            ->addColumn('valor_parcela', 'decimal', ['precision' => 18, 'scale' => 4, 'null' => false, 'comment' => 'Valor da parcela'])
            ->addColumn('data_vencimento', 'date', ['null' => false, 'comment' => 'Data de vencimento'])
            ->addColumn('data_pagamento', 'date', ['null' => true, 'comment' => 'Data de pagamento'])
            ->addColumn('status', 'string', ['null' => false, 'default' => 'pendente', 'comment' => 'pendente, pago, atrasado'])
            ->addColumn('data_cadastro', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('data_atualizacao', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_venda', 'sale', 'id', ['delete' => 'CASCADE', 'update' => 'NO ACTION'])
            ->create();
    }
}
