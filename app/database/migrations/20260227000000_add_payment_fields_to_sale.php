<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPaymentFieldsToSale extends AbstractMigration
{
    public function change(): void
    {
        // Add tipo_pagamento column if it doesn't exist
        if (!$this->hasTable('sale')) {
            return;
        }

        $table = $this->table('sale');
        
        // Check if columns don't exist before adding
        if (!$table->hasColumn('tipo_pagamento')) {
            $table->addColumn('tipo_pagamento', 'string', [
                'null' => true,
                'default' => 'avista',
                'limit' => 20,
                'after' => 'acrescimo'
            ]);
        }

        if (!$table->hasColumn('metodo_pagamento')) {
            $table->addColumn('metodo_pagamento', 'string', [
                'null' => true,
                'limit' => 20,
                'after' => 'tipo_pagamento'
            ]);
        }

        $table->update();
    }
}
