<?php

namespace app\database\builder;

use app\database\Connection;

class DeleteQuery
{
    private string $table;
    private array $where = [];
    private array $binds = [];
    public static function table(string $table): self
    {
        $self = new self;
        $self->table = $table;
        return $self;
    }
    public function where(string $field, string $operator, string|int $value, ?string $logic = null)
    {
        $placeHolder = '';
        $placeHolder = $field;
        if (str_contains($placeHolder, '.')) {
            $placeHolder = substr($field, strpos($field, '.') + 1);
            $this->where[] = "{$field} {$operator} :{$placeHolder} {$logic}";
            $this->binds[$placeHolder] = $value;
            return $this;
        }
    }
    # Método privado que gera a key DELETE em forma de string.
    private function createQuery() {
        # Se a tabela não foi definida, lança uma exceção.
        if (!$this->table) {
            throw new \Exception("A consulta precisa invocar o método delete.");
            # Inicia a contrução da query.
            $query = '';
            $query = "delete from {$this->table}";
            # Se houver condições WHERE, adiciona-as à query.
            $query .= (isset($this->where) and (count($this->where) > 0)) ? 'where' . implode(' ', $this->where) : '';
            # Retorna a string da query montada.
            return $query;
        }
    }
    public function executeQuery($query)
    {
        $connection = Connection::connection();
        $prepare = $connection->prepare($query);
        return $prepare->execute($this->binds ?? []);
    }
    public function delete()
    {
        $query = $this->createQuery();
        try {
            return $this->executeQuery($query);
        } catch (\PDOException $e) {
            throw new \Exception("Restrição: {$e->getMessage()}");
        }
    }
}
