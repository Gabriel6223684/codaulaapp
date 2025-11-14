<?php

namespace app\database\builder;

use app\database\Connection;
use PDOException;
use Exception;

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

    public function where(string $field, string $operator, string|int $value, ?string $logic = null): self
    {
        $placeHolder = $field;

        // Se tiver "tabela.campo", usa só o nome do campo no placeholder
        if (str_contains($placeHolder, '.')) {
            $placeHolder = substr($field, strpos($field, '.') + 1);
        }

        $this->where[] = "{$field} {$operator} :{$placeHolder}" . ($logic ? " {$logic}" : "");
        $this->binds[$placeHolder] = $value;

        return $this;
    }

    private function createQuery(): string
    {
        if (empty($this->table)) {
            throw new Exception("É necessário definir uma tabela antes de executar o delete.");
        }

        $query = "DELETE FROM {$this->table}";

        if (count($this->where) > 0) {
            $query .= ' WHERE ' . implode(' ', $this->where);
        }

        return $query;
    }

    public function executeQuery(string $query): bool
    {
        $connection = Connection::connection();
        $prepare = $connection->prepare($query);
        return $prepare->execute($this->binds ?? []);
    }

    public function delete(): bool
    {
        $query = $this->createQuery();

        try {
            return $this->executeQuery($query);
        } catch (PDOException $e) {
            throw new Exception("Erro ao executar DELETE: " . $e->getMessage());
        }
    }
}
