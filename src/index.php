<?php
require 'vendor/autoload.php';

use App\Core\QueryBuilder;

$queryBuilder=null;

$queryBuilder = new QueryBuilder();
$selectQuery = $queryBuilder
    ->table('product')
    ->toSql();
echo $selectQuery . '<br/>';
$queryBuilder=null;

$queryBuilder = new QueryBuilder();
$selectColumnQuery = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->toSql();
echo $selectColumnQuery . '<br/>';
$queryBuilder=null;

$queryBuilder = new QueryBuilder();
$selectColumnWhereQuery = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->where('status', '=', 1)
    ->toSql();
echo $selectColumnWhereQuery . '<br/>';
$queryBuilder=null;

$queryBuilder = new QueryBuilder();
$selectColumnWhereOrWhereQuery = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->where('status', '=', 1)
    ->orWhere('quantity', '>', 0)
    ->toSql();
echo $selectColumnWhereOrWhereQuery . '<br/>';
$queryBuilder=null;

$queryBuilder = new QueryBuilder();
$selectColumnWhereOrWhereAndQuery = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->where('status', '=', 1)
    ->orWhere(function ($query) {
        $query->where('quantity', '>', 0)
            ->where('amount', '>', 0);
    })
    ->toSql();
echo $selectColumnWhereOrWhereAndQuery . '<br/>';

$queryBuilder = new QueryBuilder();
$selectColumnWhereOrWhereAndQuery = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->where('status', '=', 1)
    ->orWhere(function ($query) {
        $query->where('quantity', '>', 0)
            ->where('amount', '>', 0);
    })
    ->toSql();
echo $selectColumnWhereOrWhereAndQuery . '<br/>';
