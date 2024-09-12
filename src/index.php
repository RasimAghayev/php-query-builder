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
$selectColumnWhereINAndQuery = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->where('status', '=', 1)
    ->orWhere(function ($query) {
        $query->where('quantity', '>', 0)
            ->where('amount', '>', 0);
    })
    ->whereIn('customer', [13, 135, 168])
    ->toSql();
echo $selectColumnWhereINAndQuery . '<br/>';
$queryBuilder=null;

$queryBuilder = new QueryBuilder();
$selectColumnWhereNotINAndQuery = $queryBuilder
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
echo $selectColumnWhereNotINAndQuery . '<br/>';
$queryBuilder=null;

$queryBuilder = new QueryBuilder();
$selectColumnWhereLikeAndQuery = $queryBuilder
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
echo $selectColumnWhereLikeAndQuery . '<br/>';
$queryBuilder=null;

$queryBuilder = new QueryBuilder();
$selectColumnWhereNotLikeAndQuery = $queryBuilder
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
echo $selectColumnWhereNotLikeAndQuery . '<br/>';
$queryBuilder=null;
