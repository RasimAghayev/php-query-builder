<?php

use PHPUnit\Framework\TestCase;
use App\Core\QueryBuilder;

class QueryBuilderTest extends TestCase
{
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

}
