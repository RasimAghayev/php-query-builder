<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Core\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    /**
     * @return void
     */
    public function testSelectQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->toSql();

        $this->assertEquals(
            "SELECT * FROM product;",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->select('id', 'name', 'price')
            ->toSql();

        $this->assertEquals(
            "SELECT id, name, price FROM product;",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnWhereQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->select('id', 'name', 'price')
            ->where('status', '=', 1)
            ->toSql();

        $this->assertEquals(
            "SELECT id, name, price FROM product WHERE status = 1;",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnWhereOrWhereQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->select('id', 'name', 'price')
            ->where('status', '=', 1)
            ->orWhere('quantity', '>', 0)
            ->toSql();

        $this->assertEquals(
            "SELECT id, name, price FROM product WHERE status = 1 OR quantity > 0;",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnWhereOrWhereAndQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->select('id', 'name', 'price')
            ->where('status', '=', 1)
            ->orWhere(function ($query) {
                $query->where('quantity', '>', 0)
                    ->where('amount', '>', 0);
            })
            ->toSql();

        $this->assertEquals(
            "SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0);",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnWhereINAndQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->select('id', 'name', 'price')
            ->where('status', '=', 1)
            ->orWhere(function ($query) {
                $query->where('quantity', '>', 0)
                    ->where('amount', '>', 0);
            })
            ->whereIn('customer', [13, 135, 168])
            ->toSql();

        $this->assertEquals(
            "SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168);",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnWhereNotINAndQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->select('id', 'name', 'price')
            ->where('status', '=', 1)
            ->orWhere(function ($query) {
                $query->where('quantity', '>', 0)
                    ->where('amount', '>', 0);
            })
            ->whereIn('customer', [13, 135, 168])
            ->whereNotIn('payer', [13, 135, 168])
            ->toSql();

        $this->assertEquals(
            "SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168);",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnWhereLikeAndQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->select('id', 'name', 'price')
            ->where('status', '=', 1)
            ->orWhere(function ($query) {
                $query->where('quantity', '>', 0)
                    ->where('amount', '>', 0);
            })
            ->whereIn('customer', [13, 135, 168])
            ->whereNotIn('payer', [13, 135, 168])
            ->whereLike('name', '%Ras%')
            ->toSql();

        $this->assertEquals(
            "SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168) AND name LIKE '%Ras%';",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnWhereNotLikeAndQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->select('id', 'name', 'price')
            ->where('status', '=', 1)
            ->orWhere(function ($query) {
                $query->where('quantity', '>', 0)
                    ->where('amount', '>', 0);
            })
            ->whereIn('customer', [13, 135, 168])
            ->whereNotIn('payer', [13, 135, 168])
            ->whereLike('name', '%Ras%')
            ->whereNotLike('name', '%es%')
            ->toSql();

        $this->assertEquals(
            "SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168) AND name LIKE '%Ras%' AND name NOT LIKE '%es%';",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnWhereBetweenAndQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->select('id', 'name', 'price')
            ->where('status', '=', 1)
            ->orWhere(function ($query) {
                $query->where('quantity', '>', 0)
                    ->where('amount', '>', 0);
            })
            ->whereIn('customer', [13, 135, 168])
            ->whereNotIn('payer', [13, 135, 168])
            ->whereLike('name', '%Ras%')
            ->whereNotLike('name', '%es%')
            ->whereBetween('date', '2023-01-01', '2023-12-31')
            ->toSql();

        $this->assertEquals(
            "SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168) AND name LIKE '%Ras%' AND name NOT LIKE '%es%' AND date BETWEEN '2023-01-01' AND '2023-12-31';",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnWhereNotBetweenAndQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->select('id', 'name', 'price')
            ->where('status', '=', 1)
            ->orWhere(function ($query) {
                $query->where('quantity', '>', 0)
                    ->where('amount', '>', 0);
            })
            ->whereIn('customer', [13, 135, 168])
            ->whereNotIn('payer', [13, 135, 168])
            ->whereLike('name', '%Ras%')
            ->whereNotLike('name', '%es%')
            ->whereBetween('date', '2023-01-01', '2023-12-31')
            ->whereNotBetween('created_at', '2023-01-01', '2023-12-31')
            ->toSql();

        $this->assertEquals(
            "SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168) AND name LIKE '%Ras%' AND name NOT LIKE '%es%' AND date BETWEEN '2023-01-01' AND '2023-12-31' AND created_at NOT BETWEEN '2023-01-01' AND '2023-12-31';",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnOtherQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->select('id', 'name', 'price')
            ->where('status', '=', 1)
            ->orWhere(function ($query) {
                $query->where('quantity', '>', 0)
                    ->where('amount', '>', 0);
            })
            ->whereIn('customer', [13, 135, 168])
            ->whereNotIn('payer', [13, 135, 168])
            ->whereLike('name', '%Ras%')
            ->whereNotLike('name', '%es%')
            ->whereBetween('date', '2023-01-01', '2023-12-31')
            ->whereNotBetween('created_at', '2023-01-01', '2023-12-31')
            ->groupBy('category, status')
            ->having('amount', '>', 100)
            ->orderBy('amount', 'DESC')
            ->limit(10)
            ->offset(5)
            ->toSql();

        $this->assertEquals(
            "SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168) AND name LIKE '%Ras%' AND name NOT LIKE '%es%' AND date BETWEEN '2023-01-01' AND '2023-12-31' AND created_at NOT BETWEEN '2023-01-01' AND '2023-12-31' GROUP BY category, status HAVING amount > 100 ORDER BY amount DESC LIMIT 10 OFFSET 5;",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testOrderByDefaultDirectionQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->orderBy('price')
            ->toSql();

        $this->assertEquals(
            "SELECT * FROM product ORDER BY price ASC;",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testGroupByMultipleColumnsQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->groupBy('category', 'status')
            ->toSql();

        $this->assertEquals(
            "SELECT * FROM product GROUP BY category, status;",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testLimitQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->limit(10)
            ->toSql();

        $this->assertEquals(
            "SELECT * FROM product LIMIT 10;",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testOffsetQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->offset(5)
            ->toSql();

        $this->assertEquals(
            "SELECT * FROM product OFFSET 5;",
            $sql
        );
    }

    /**
     * Documents escapeValue()'s known limitation (see docs/TECHNICAL_DEBT.md):
     * it wraps strings in quotes but does not escape an embedded quote
     * character, so this produces syntactically broken SQL. Not a fix —
     * pins the current, intentional-for-now behavior.
     *
     * @return void
     */
    public function testEscapeValueDoesNotEscapeEmbeddedSingleQuote(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->where('name', '=', "O'Brien")
            ->toSql();

        $this->assertEquals(
            "SELECT * FROM product WHERE name = 'O'Brien';",
            $sql
        );
    }

    /**
     * @return void
     */
    public function testSelectColumnAggregationQuery(): void
    {
        $queryBuilder = new QueryBuilder();

        $sql = $queryBuilder
            ->table('product')
            ->sum('quantity * amount', 'total_quantity')
            ->avg('amount', 'avg_amount')
            ->count('id', 'total_products')
            ->toSql();

        $this->assertEquals(
            "SELECT *, SUM(quantity * amount) AS total_quantity, AVG(amount) AS avg_amount, COUNT(id) AS total_products FROM product;",
            $sql
        );
    }
}
