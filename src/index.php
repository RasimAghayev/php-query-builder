<?php
require 'vendor/autoload.php';

use App\Core\QueryBuilder;

unset($queryBuilder); 

$queryBuilder = new QueryBuilder();
$selectQuery = $queryBuilder
    ->table('product')
    ->toSql();
echo $selectQuery . '<br/>';
unset($queryBuilder); 

$queryBuilder = new QueryBuilder();
$selectColumnQuery = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->toSql();
echo $selectColumnQuery . '<br/>';
unset($queryBuilder); 

$queryBuilder = new QueryBuilder();
$selectColumnWhereQuery = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->where('status', '=', 1)
    ->toSql();
echo $selectColumnWhereQuery . '<br/>';
unset($queryBuilder); 

$queryBuilder = new QueryBuilder();
$selectColumnWhereOrWhereQuery = $queryBuilder
    ->table('product')
    ->select('id', 'name', 'price')
    ->where('status', '=', 1)
    ->orWhere('quantity', '>', 0)
    ->toSql();
echo $selectColumnWhereOrWhereQuery . '<br/>';
unset($queryBuilder); 

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
unset($queryBuilder); 

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
unset($queryBuilder); 

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
unset($queryBuilder); 

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
unset($queryBuilder); 

$queryBuilder = new QueryBuilder();
$selectColumnWhereBetweenAndQuery = $queryBuilder
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
echo $selectColumnWhereBetweenAndQuery . '<br/>';
unset($queryBuilder); 

$queryBuilder = new QueryBuilder();
$selectColumnWhereNotBetweenAndQuery = $queryBuilder
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
echo $selectColumnWhereNotBetweenAndQuery . '<br/>';
unset($queryBuilder); 

$queryBuilder = new QueryBuilder();
$selectColumnOtherQuery = $queryBuilder
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
echo $selectColumnOtherQuery . '<br/>';
unset($queryBuilder); 

$queryBuilder = new QueryBuilder();
$selectColumnAggregationQuery = $queryBuilder
    ->table('product')
    ->sum('quantity * amount', 'total_quantity')
    ->avg('amount', 'avg_amount')
    ->count('id', 'total_products')
    ->toSql();
echo $selectColumnAggregationQuery . '<br/>';
unset($queryBuilder); 
