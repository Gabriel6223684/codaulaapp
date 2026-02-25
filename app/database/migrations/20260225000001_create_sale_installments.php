<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSaleInstallments extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('sale_installments', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'biginteger', ['identity' => true, 'null' => false])
            ->addColumn('id_venda', 'biginteger', ['null' => false])
            ->addColumn('numero_parcela', 'integer', ['null' => false, 'comment' => 'Número da parcela (1, 2, 3...)'])
            ->addColumn('valor_parcela', 'decimal', ['precision' => 18, 'scale' => 4, 'null' => false, 'comment' => 'Valor de cada parcela'])
            ->addColumn('data_vencimento', 'date', ['null' => false, 'comment' => 'Data de vencimento da parcela'])
            ->addColumn('data_pagamento', 'date', ['null' => true, 'comment' => 'Data em que foi paga a parcela'])
            ->addColumn('status', 'string', ['null' => true, 'default' => 'pendente', 'comment' => 'pendente, pago, cancelado'])
            ->addColumn('observacao', 'text', ['null' => true])
            ->addColumn('data_cadastro', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('data_atualizacao', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_venda', 'sale', 'id', ['delete' => 'CASCADE', 'update' => 'NO ACTION'])
            ->create();
    }
}
