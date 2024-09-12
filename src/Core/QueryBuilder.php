<?php

namespace App\Core;

use App\Core\Interface\{QueryStaticBuilderInterface,
                        QueryWhereBuilderInterface,
                        QueryOtherBuilderInterface,
                        AggregationInterface};

class QueryBuilder implements   QueryStaticBuilderInterface,
                                QueryWhereBuilderInterface,
                                QueryOtherBuilderInterface,
                                AggregationInterface
{
    private string $table = '';
    private string $select = '*';
    private array $wheres = [];
    private string $orderBy = '';
    private string $groupBy = '';
    private string $having = '';
    private string $limit = '';
    private string $offset = '';

    // Private Methods

    private function escapeValue(mixed $value): string
    {
        if (is_string($value)) {
            return "'$value'";
        }
        return (string)$value;
    }

    private function andOr(string $whereQuery,
                           string $operator='OR') : string 
    {
        return (empty($this->wheres)) ? "" : "{$operator} ".$whereQuery;
    }

    private function arrayToSqlList(array $values): string
    {
        return implode(', ', array_map(fn($value) => $this->escapeValue($value), $values));
    }
    
    // Public Methods

    public function table(string $table): QueryStaticBuilderInterface
    {
        $this->table = $table;
        return $this;
    }

    public function select(string ...$columns): QueryStaticBuilderInterface
    {
        $this->select = implode(', ', $columns);
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): QueryWhereBuilderInterface
    {
        $this->wheres[] = "$column $operator " . $this->escapeValue($value);
        return $this;
    }
    
    public function orWhere(string|callable $callback,
                            string          $operator = null,
                            mixed           $value = null): QueryWhereBuilderInterface
    {
        $wheresQuery='';
        if (is_callable($callback)) {
            $subQuery = new static();
            $callback($subQuery);
            $whereQuery='(' . implode(' AND ', $subQuery->wheres) . ')';
        } else {
            $whereQuery = "$callback $operator " . $this->escapeValue($value);
        }
        $this->wheres[] = $this->andOr($whereQuery);
        return $this;
    }

    public function whereIn(string $column, array $values): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("{$column} IN (" . $this->arrayToSqlList($values) . ")", 'AND');
        return $this;
    }

    public function whereNotIn(string $column, array $values): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("{$column} NOT IN (" . $this->arrayToSqlList($values) . ")", 'AND');
        return $this;
    }

    public function whereLike(string $column, string $pattern): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("$column LIKE '$pattern'", 'AND');
        return $this;
    }

    public function whereNotLike(string $column, string $pattern): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("$column NOT LIKE '$pattern'", 'AND');
        return $this;
    }

    public function whereBetween(string $column, mixed $start, mixed $end): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("$column BETWEEN " . $this->escapeValue($start) . " AND " . $this->escapeValue($end), 'AND');
        return $this;
    }

    public function whereNotBetween(string $column, mixed $start, mixed $end): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("$column NOT BETWEEN " . $this->escapeValue($start) . " AND " . $this->escapeValue($end), 'AND');
        return $this;
    }

    // Aggregation Methods with Alias

    public function sum(string $expression, string $alias = 'result'): AggregationInterface
    {
        $this->select .= ", SUM($expression)" . ($alias ? " AS $alias" : "");
        return $this;
    }

    public function avg(string $expression, string $alias = 'result'): AggregationInterface
    {
        $this->select .= ", AVG($expression)" . ($alias ? " AS $alias" : "");
        return $this;
    }

    public function count(string $expression, string $alias = 'result'): AggregationInterface
    {
        $this->select .= ", COUNT($expression)" . ($alias ? " AS $alias" : "");
        return $this;
    }

    protected function aggregate(string $function, string $expression, string $alias): string
    {
        $query = "SELECT $function($expression) AS $alias FROM {$this->table}";

        if (!empty($this->wheres)) {
            $query .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        if (!empty($this->groupBy)) {
            $query .= " {$this->groupBy}";
        }

        if (!empty($this->having)) {
            $query .= " {$this->having}";
        }

        return $query . ";";
    }

    // Other query methods

    public function orderBy(string $column, string $direction = 'ASC'): QueryOtherBuilderInterface
    {
        $this->orderBy = "ORDER BY $column $direction";
        return $this;
    }
    
    public function groupBy(string ...$columns): QueryOtherBuilderInterface
    {
        $this->groupBy = 'GROUP BY ' . implode(', ', $columns);
        return $this;
    }

    public function having(string $column, string $operator, mixed $value): QueryOtherBuilderInterface
    {
        $this->having = "HAVING $column $operator " . $this->escapeValue($value);
        return $this;
    }

    public function limit(int $limit): QueryOtherBuilderInterface
    {
        $this->limit = "LIMIT $limit";
        return $this;
    }

    public function offset(int $offset): QueryOtherBuilderInterface
    {
        $this->offset = "OFFSET $offset";
        return $this;
    }

    // Base Query

    protected function buildBaseQuery(): string
    {
        $query = "SELECT {$this->select} FROM {$this->table}";

        if (!empty($this->wheres)) {
            $query .= ' WHERE ' . implode(' ', $this->wheres);
        }
        if (!empty($this->groupBy)) {
            $query .= " {$this->groupBy}";
        }

        if (!empty($this->having)) {
            $query .= " {$this->having}";
        }

        if (!empty($this->orderBy)) {
            $query .= " {$this->orderBy}";
        }

        if (!empty($this->limit)) {
            $query .= " {$this->limit}";
        }

        if (!empty($this->offset)) {
            $query .= " {$this->offset}";
        }
        return "{$query};";
    }
    
    public function toSql(): string
    {
        return $this->buildBaseQuery();
    }
}
