<?php

namespace App\Core;

class QueryBuilder
{
    private string $table = '';
    private string $select = '*';

    public function table(string $table)
    {
        $this->table = $table;
        return $this;
    }

    protected function buildBaseQuery(): string
    {
        $query = "SELECT {$this->select} FROM {$this->table}";
        return "{$query};";
    }
    
    public function toSql(): string
    {
        return $this->buildBaseQuery();
    }
}