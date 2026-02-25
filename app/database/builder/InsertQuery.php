<?php

namespace app\database\builder;

use app\database\Connection;

class InsertQuery
{
    private string $table;
    private array $FieldsAndValues = [];
    #Obter o nome da tabela onde os dados serão inserido.
    public static function table(string $table): self
    {
        $self = new self;
        $self->table = $table;
        return $self;
    }
    private function createQuery(): string
    {
        $fields  = implode(',', array_keys($this->FieldsAndValues));
        $placeHolder = ':' . implode(',:', array_keys($this->FieldsAndValues));
        return "insert into $this->table ($fields) values ($placeHolder)";
    }
    private function execute(string $query, bool $returnId = false): mixed
    {
        $con = Connection::connection();
        $prepare = $con->prepare($query);
        $result = $prepare->execute($this->FieldsAndValues);
        
        if ($returnId && $result) {
            return $con->lastInsertId();
        }
        
        return $result;
    }
    public function save(array $FieldsAndValues, bool $returnId = false): mixed
    {
        $this->FieldsAndValues = $FieldsAndValues;
        $query = $this->createQuery();
        try {
            return $this->execute($query, $returnId);
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
