<?php

namespace App\Core;

class QueryBuilder
{
    private string $table = '';
    private string $select = '*';
    private array $wheres = [];

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(string ...$columns): self
    {
        $this->select = implode(', ', $columns);
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->wheres[] = "$column $operator " . $this->escapeValue($value);
        return $this;
    }
    
    private function escapeValue(mixed $value): string
    {
        if (is_string($value)) {
            return "'$value'";
        }
        return (string)$value;
    }

    protected function buildBaseQuery(): string
    {
        $query = "SELECT {$this->select} FROM {$this->table}";

        if (!empty($this->wheres)) {
            $query .= ' WHERE ' . implode(' ', $this->wheres);
        }

        return "{$query};";
    }
    
    public function toSql(): string
    {
        return $this->buildBaseQuery();
    }
}