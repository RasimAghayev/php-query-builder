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
}
