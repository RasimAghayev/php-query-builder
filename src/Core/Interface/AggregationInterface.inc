<?php

/**
 * Interface AggregationInterface
 *
 * Provides methods for adding aggregation functions to queries.
 *
 * @package    App\Core\Interface
 * @subpackage Aggregation
 * @copyright  2024 Your Company
 * @author    Rasim Aghayevv <rasimaqayev@gmail.com>
 */

namespace App\Core\Interface;

/**
 * Interface AggregationInterface
 *
 * Defines methods for applying aggregation functions such as SUM, AVG, and COUNT in SQL queries.
 */
interface AggregationInterface
{
    /**
     * Adds a SUM aggregation to the query.
     *
     * @param string $expression The column or expression to sum.
     * @param string $alias Optional alias for the result.
     * @return self
     */
    public function sum(string $expression, string $alias = 'result'): self;

    /**
     * Adds an AVG aggregation to the query.
     *
     * @param string $expression The column or expression to average.
     * @param string $alias Optional alias for the result.
     * @return self
     */
    public function avg(string $expression, string $alias = 'result'): self;

    /**
     * Adds a COUNT aggregation to the query.
     *
     * @param string $expression The column or expression to count.
     * @param string $alias Optional alias for the result.
     * @return self
     */
    public function count(string $expression, string $alias = 'result'): self;
}

// end interface
