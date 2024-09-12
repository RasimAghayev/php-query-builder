<?php

/**
 * Interface QueryWhereBuilderInterface
 *
 * Provides methods for constructing "WHERE" clauses in queries.
 *
 * @package App\Core\Interface
 */

namespace App\Core\Interface;

/**
 * Interface QueryWhereBuilderInterface
 *
 * Defines methods for building "WHERE" conditions in SQL queries.
 */
interface QueryWhereBuilderInterface
{
    /**
     * Adds a "WHERE" condition to the query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator (e.g., '=', '<', '>', 'LIKE').
     * @param mixed $value The value to compare the column against.
     * @return self
     */
    public function where(string $column, string $operator, mixed $value): self;

    /**
     * Adds an "OR WHERE" condition to the query.
     *
     * @param string|callable $callback The condition or a callback function to build a sub-query.
     * @param string|null $operator The comparison operator (e.g., '=', '<', '>', 'LIKE').
     * @param mixed|null $value The value to compare the column against.
     * @return self
     */
    public function orWhere(string|callable $callback, string $operator = null, mixed $value = null): self;

    /**
     * Adds a "WHERE IN" condition to the query.
     *
     * @param string $column The column name.
     * @param string[] $values An array of values to compare the column against.
     * @return self
     */
    public function whereIn(string $column, array $values): self;

    /**
     * Adds a "WHERE NOT IN" condition to the query.
     *
     * @param string $column The column name.
     * @param string[] $values An array of values to compare the column against.
     * @return self
     */
    public function whereNotIn(string $column, array $values): self;

    /**
     * Adds a "WHERE LIKE" condition to the query.
     *
     * @param string $column The column name.
     * @param string $pattern The pattern to match against.
     * @return self
     */
    public function whereLike(string $column, string $pattern): self;

    /**
     * Adds a "WHERE NOT LIKE" condition to the query.
     *
     * @param string $column The column name.
     * @param string $pattern The pattern to not match against.
     * @return self
     */
    public function whereNotLike(string $column, string $pattern): self;

    /**
     * Adds a "WHERE BETWEEN" condition to the query.
     *
     * @param string $column The column name.
     * @param mixed $start The start value for the range.
     * @param mixed $end The end value for the range.
     * @return self
     */
    public function whereBetween(string $column, mixed $start, mixed $end): self;

    /**
     * Adds a "WHERE NOT BETWEEN" condition to the query.
     *
     * @param string $column The column name.
     * @param mixed $start The start value for the range.
     * @param mixed $end The end value for the range.
     * @return self
     */
    public function whereNotBetween(string $column, mixed $start, mixed $end): self;
}
