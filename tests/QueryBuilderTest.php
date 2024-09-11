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
}
