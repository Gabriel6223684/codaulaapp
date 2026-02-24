<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddItemSaleFields extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('item_sale');
        
        // Adicionar os campos necessários para itens de venda
        $table->addColumn('quantidade', 'decimal', ['precision' => 18, 'scale' => 4, 'null' => true, 'default' => 1])
            ->addColumn('preco_unitario', 'decimal', ['precision' => 18, 'scale' => 4, 'null' => true])
            ->addColumn('valor_total', 'decimal', ['precision' => 18, 'scale' => 4, 'null' => true])
            ->update();
    }
}
