<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PaymentTerms extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('payment_terms', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'biginteger', ['identity' => true, 'null' => false])
            ->create();
    }
}
