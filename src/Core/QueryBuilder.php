<?php

namespace App\Core;

class QueryBuilder
{
    private string $table = '';
    private string $select = '*';
    private array $wheres = [];

    // Private Methods

    private function escapeValue(mixed $value): string
    {
        if (is_string($value)) {
            return "'$value'";
        }
        return (string)$value;
    }

    private function andOr(string $whereQuery,string $operator='OR') : string 
    {
        return (empty($this->wheres))?"":"{$operator} ".$whereQuery;
    }

    private function arrayToSqlList(array $values): string
    {
        return implode(', ', array_map(fn($value) => $this->escapeValue($value), $values));
    }
    
    // Public Methods

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
    
    public function orWhere(string|callable $callback,
                            string          $operator = null,
                            mixed           $value = null): self
    {
        $wheresQuery='';
        if (is_callable($callback)) {
            $subQuery = new static();
            $callback($subQuery);
            $whereQuery='(' . implode(' AND ', $subQuery->wheres) . ')';
        } else {
            $whereQuery = "$callback $operator " . $this->escapeValue($value);
        }
        $this->wheres[] =$this->andOr($whereQuery);
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $this->wheres[] = $this->andOr("{$column} IN (" . $this->arrayToSqlList($values) . ")", 'AND');
        return $this;
    }

    public function whereNotIn(string $column, array $values): self
    {
        $this->wheres[] = $this->andOr("{$column} NOT IN (" . $this->arrayToSqlList($values) . ")", 'AND');
        return $this;
    }

    public function whereLike(string $column, string $pattern): self
    {
        $this->wheres[] = $this->andOr("$column LIKE '$pattern'", 'AND');
        return $this;
    }

    public function whereNotLike(string $column, string $pattern): self
    {
        $this->wheres[] = $this->andOr("$column NOT LIKE '$pattern'", 'AND');
        return $this;
    }

    public function whereBetween(string $column, mixed $start, mixed $end): self
    {
        $this->wheres[] = $this->andOr("$column BETWEEN " . $this->escapeValue($start) . " AND " . $this->escapeValue($end), 'AND');
        return $this;
    }
    public function whereNotBetween(string $column, mixed $start, mixed $end): self
    {
        $this->wheres[] = $this->andOr("$column NOT BETWEEN " . $this->escapeValue($start) . " AND " . $this->escapeValue($end), 'AND');
        return $this;
    }


    // Base Query

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