<?php

/**
 * Interface QueryStaticBuilderInterface
 *
 * Provides methods for constructing static queries.
 *
 * @package App\Core\Interface
 */

namespace App\Core\Interface;

/**
 * Interface QueryStaticBuilderInterface
 *
 * Defines methods for building static SQL queries.
 */
interface QueryStaticBuilderInterface
{
    /**
     * Sets the table for the query.
     *
     * @param string $table The name of the table.
     * @return self
     */
    public function table(string $table): self;

    /**
     * Specifies the columns to select.
     *
     * @param string ...$columns The columns to select.
     * @return self
     */
    public function select(string ...$columns): self;

    /**
     * Builds and returns the SQL query string.
     *
     * @return string The SQL query string.
     */
    public function toSql(): string;
}
