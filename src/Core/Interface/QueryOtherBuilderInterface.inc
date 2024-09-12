<?php

/**
 * Interface QueryOtherBuilderInterface
 *
 * Provides methods for building SQL queries with other clauses.
 *
 * @package App\Core\Interface
 */

namespace App\Core\Interface;

/**
 * Interface QueryOtherBuilderInterface
 *
 * Defines methods for SQL query clauses such as ordering, grouping, limiting, and offsetting.
 */
interface QueryOtherBuilderInterface
{
    /**
     * Specifies the column to order the results by.
     *
     * @param string $column The column to sort by.
     * @param string $direction The sort direction (ASC or DESC). Default is 'ASC'.
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self;

    /**
     * Specifies the columns to group the results by.
     *
     * @param string ...$columns Columns to group by.
     * @return self
     */
    public function groupBy(string ...$columns): self;

    /**
     * Adds a HAVING clause to the query.
     *
     * @param string $column The column to apply the condition on.
     * @param string $operator The operator to use in the condition.
     * @param mixed $value The value to compare against.
     * @return self
     */
    public function having(string $column, string $operator, mixed $value): self;

    /**
     * Limits the number of results returned by the query.
     *
     * @param int $limit The maximum number of results to return.
     * @return self
     */
    public function limit(int $limit): self;

    /**
     * Skips a number of results before starting to return results.
     *
     * @param int $offset The number of results to skip.
     * @return self
     */
    public function offset(int $offset): self;
}
