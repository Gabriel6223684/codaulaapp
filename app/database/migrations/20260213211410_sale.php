<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Sale extends AbstractMigration
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
        $table = $this->table('sale', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'biginteger', ['identity' => true, 'null' => false])
            ->addColumn('id_cliente', 'biginteger', ['null' => true])
            ->addColumn('id_usuario', 'biginteger', ['null' => true])
            ->addColumn('total_bruto', 'decimal', ['precision' => 18, 'scale' => 4, 'null' => true])
            ->addColumn('total_liquido', 'decimal', [
                'precision' => 18,
                'scale' => 4,
                'null' => true,
                'comment' => 'Valor a ser pago pelo cliente.'
            ])
            ->addColumn('desconto', 'decimal', ['precision' => 18, 'scale' => 4, 'null' => true])
            ->addColumn('acrescimo', 'decimal', ['precision' => 18, 'scale' => 4, 'null' => true])
            ->addColumn('observacao', 'text', ['null' => true])
            ->addColumn('data_cadastro', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('data_atualizacao', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_cliente', 'customer', 'id', ['delete' => 'CASCADE', 'update' => 'NO ACTION'])
            ->addForeignKey('id_usuario', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO ACTION'])
            ->create();
    }
}
