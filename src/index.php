<?php
require 'vendor/autoload.php';

use App\Core\QueryBuilder;


$queryBuilder = new QueryBuilder();

$sql = $queryBuilder
    ->table('product')
    ->toSql();

echo $sql . '<br/>';


$sql = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->toSql();

echo $sql . '<br/>';


$sql = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->toSql();

echo $sql . '<br/>';

$sql = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->where('status', '=', 1)
    ->toSql();

echo $sql . '<br/>';
