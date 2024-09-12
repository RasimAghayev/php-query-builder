<?php

/**
 * Class QueryBuilder
 *
 * Builds SQL queries with various clauses.
 *
 * @package App\Core
 */

namespace App\Core;

use JetBrains\PhpStorm\Pure;
use App\Core\Interface\{QueryStaticBuilderInterface,
    QueryWhereBuilderInterface,
    QueryOtherBuilderInterface,
    AggregationInterface};

/**
 * QueryBuilder class is responsible for constructing SQL queries
 * with support for various clauses like WHERE, GROUP BY, ORDER BY, etc.
 */
class QueryBuilder implements
    QueryStaticBuilderInterface,
    QueryWhereBuilderInterface,
    QueryOtherBuilderInterface,
    AggregationInterface
{
    /**
     * @var string
     */
    private string $table = '';

    /**
     * @var string
     */
    private string $select = '*';

    /**
     * @var array<array<string, mixed>>
     */
    private array $wheres = [];

    /**
     * @var string
     */
    private string $orderBy = '';

    /**
     * @var string
     */
    private string $groupBy = '';

    /**
     * @var string
     */
    private string $having = '';

    /**
     * @var string
     */
    private string $limit = '';

    /**
     * @var string
     */
    private string $offset = '';

    // Private Methods

    /**
     * Escapes a value for SQL query.
     * 
     * @param mixed $value
     * @return string
     */
    private function escapeValue(mixed $value): string
    {
        if (is_string($value)) {
            return "'$value'";
        }
        return (string)$value;
    }

    /**
     * Constructs the WHERE clause with an optional logical operator.
     * 
     * @param string $whereQuery
     * @param string $operator
     * @return string
     */
    private function andOr(string $whereQuery, string $operator = 'OR'): string
    {
        return (empty($this->wheres)) ? "" : "$operator $whereQuery";
    }

    /**
     * Converts an array of values to a SQL list format.
     * 
     * @param mixed[] $values
     * @return string
     */
    private function arrayToSqlList(array $values): string
    {
        return implode(', ', array_map(fn($value) => $this->escapeValue($value), $values));
    }

    // Public Methods

    /**
     * Sets the table for the query.
     * 
     * @param string $table
     * @return QueryStaticBuilderInterface
     */
    public function table(string $table): QueryStaticBuilderInterface
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Specifies the columns to select.
     * 
     * @param string ...$columns
     * @return QueryStaticBuilderInterface
     */
    public function select(string ...$columns): QueryStaticBuilderInterface
    {
        $this->select = implode(', ', $columns);
        return $this;
    }

    /**
     * Adds a WHERE clause to the query.
     * 
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return QueryWhereBuilderInterface
     */
    public function where(string $column, string $operator, mixed $value): QueryWhereBuilderInterface
    {
        $this->wheres[] = "$column $operator " . $this->escapeValue($value);
        return $this;
    }

    /**
     * Adds an OR WHERE clause to the query.
     * 
     * @param string|callable $callback
     * @param string|null $operator
     * @param mixed|null $value
     * @return QueryWhereBuilderInterface
     */
    public function orWhere(string|callable $callback, string $operator = null, mixed $value = null): QueryWhereBuilderInterface
    {
        $wheresQuery = '';
        if (is_callable($callback)) {
            $subQuery = new self(); 
            $callback($subQuery);
            $filteredWheres = array_filter($subQuery->wheres, 'is_string');
            $whereQuery = '(' . implode(' AND ', $filteredWheres) . ')';
        } else {
            $whereQuery = "$callback $operator " . $this->escapeValue($value);
        }
        $this->wheres[] = $this->andOr($whereQuery);
        return $this;
    }

    /**
     * Adds a WHERE IN clause to the query.
     * 
     * @param string $column
     * @param mixed[] $values
     * @return QueryWhereBuilderInterface
     */
    public function whereIn(string $column, array $values): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("$column IN (" . $this->arrayToSqlList($values) . ")", 'AND');
        return $this;
    }

    /**
     * Adds a WHERE NOT IN clause to the query.
     * 
     * @param string $column
     * @param mixed[] $values
     * @return QueryWhereBuilderInterface
     */
    public function whereNotIn(string $column, array $values): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("$column NOT IN (" . $this->arrayToSqlList($values) . ")", 'AND');
        return $this;
    }

    /**
     * Adds a WHERE LIKE clause to the query.
     * 
     * @param string $column
     * @param string $pattern
     * @return QueryWhereBuilderInterface
     */
    public function whereLike(string $column, string $pattern): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("$column LIKE '$pattern'", 'AND');
        return $this;
    }

    /**
     * Adds a WHERE NOT LIKE clause to the query.
     * 
     * @param string $column
     * @param string $pattern
     * @return QueryWhereBuilderInterface
     */
    public function whereNotLike(string $column, string $pattern): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("$column NOT LIKE '$pattern'", 'AND');
        return $this;
    }

    /**
     * Adds a WHERE BETWEEN clause to the query.
     * 
     * @param string $column
     * @param mixed $start
     * @param mixed $end
     * @return QueryWhereBuilderInterface
     */
    public function whereBetween(string $column, mixed $start, mixed $end): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("$column BETWEEN " . $this->escapeValue($start) . " AND " . $this->escapeValue($end), 'AND');
        return $this;
    }

    /**
     * Adds a WHERE NOT BETWEEN clause to the query.
     * 
     * @param string $column
     * @param mixed $start
     * @param mixed $end
     * @return QueryWhereBuilderInterface
     */
    public function whereNotBetween(string $column, mixed $start, mixed $end): QueryWhereBuilderInterface
    {
        $this->wheres[] = $this->andOr("$column NOT BETWEEN " . $this->escapeValue($start) . " AND " . $this->escapeValue($end), 'AND');
        return $this;
    }

    // Aggregation Methods with Alias

    /**
     * Adds a SUM aggregation to the SELECT clause.
     * 
     * @param string $expression
     * @param string $alias
     * @return AggregationInterface
     */
    public function sum(string $expression, string $alias = 'result'): AggregationInterface
    {
        $this->select .= ", SUM($expression)" . ($alias ? " AS $alias" : "");
        return $this;
    }

    /**
     * Adds an AVG aggregation to the SELECT clause.
     * 
     * @param string $expression
     * @param string $alias
     * @return AggregationInterface
     */
    public function avg(string $expression, string $alias = 'result'): AggregationInterface
    {
        $this->select .= ", AVG($expression)" . ($alias ? " AS $alias" : "");
        return $this;
    }

    /**
     * Adds a COUNT aggregation to the SELECT clause.
     * 
     * @param string $expression
     * @param string $alias
     * @return AggregationInterface
     */
    public function count(string $expression, string $alias = 'result'): AggregationInterface
    {
        $this->select .= ", COUNT($expression)" . ($alias ? " AS $alias" : "");
        return $this;
    }

    /**
     * Constructs an aggregation query.
     * 
     * @param string $function
     * @param string $expression
     * @param string $alias
     * @return string
     */
    protected function aggregate(string $function, string $expression, string $alias): string
    {
        $query = "SELECT $function($expression) AS $alias FROM {$this->table}";

        if (!empty($this->wheres)) {
            $whereClauses = array_map(function ($where) {
                return $where['column'] . ' ' . $where['operator'] . ' ' . (is_string($where['value']) ? "'" . $where['value'] . "'" : $where['value']);
            }, $this->wheres);
            $query .= ' WHERE ' . implode(' AND ', $whereClauses);
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

    /**
     * Adds an ORDER BY clause to the query.
     * 
     * @param string $column
     * @param string $direction
     * @return QueryOtherBuilderInterface
     */
    public function orderBy(string $column, string $direction = 'ASC'): QueryOtherBuilderInterface
    {
        $this->orderBy = "ORDER BY $column $direction";
        return $this;
    }

    /**
     * Adds a GROUP BY clause to the query.
     * 
     * @param string ...$columns
     * @return QueryOtherBuilderInterface
     */
    public function groupBy(string ...$columns): QueryOtherBuilderInterface
    {
        $this->groupBy = 'GROUP BY ' . implode(', ', $columns);
        return $this;
    }

    /**
     * Adds a HAVING clause to the query.
     * 
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return QueryOtherBuilderInterface
     */
    public function having(string $column, string $operator, mixed $value): QueryOtherBuilderInterface
    {
        $this->having = "HAVING $column $operator " . $this->escapeValue($value);
        return $this;
    }

    /**
     * Sets the LIMIT for the query.
     * 
     * @param int $limit
     * @return QueryOtherBuilderInterface
     */
    public function limit(int $limit): QueryOtherBuilderInterface
    {
        $this->limit = "LIMIT $limit";
        return $this;
    }

    /**
     * Sets the OFFSET for the query.
     * 
     * @param int $offset
     * @return QueryOtherBuilderInterface
     */
    public function offset(int $offset): QueryOtherBuilderInterface
    {
        $this->offset = "OFFSET $offset";
        return $this;
    }

    // Base Query

    /**
     * Builds the base SQL query string.
     * 
     * @return string
     */
    protected function buildBaseQuery(): string
    {
        $query = "SELECT {$this->select} FROM {$this->table}";

        if (!empty($this->wheres)) {
            $whereClauses = array_map(function ($where) {
                return $where['column'] . ' ' . $where['operator'] . ' ' . (is_string($where['value']) ? "'" . $where['value'] . "'" : $where['value']);
            }, $this->wheres);
            $query .= ' WHERE ' . implode(' AND ', $whereClauses);
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

    /**
     * Returns the SQL query string for the current state of the QueryBuilder.
     * 
     * @return string
     */
    public function toSql(): string
    {
        return $this->buildBaseQuery();
    }
}
