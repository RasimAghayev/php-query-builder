<?php
require 'vendor/autoload.php';

use App\Core\QueryBuilder;


$queryBuilder = new QueryBuilder();

$sql = $queryBuilder
    ->table('product')
    ->toSql();

echo $sql . '<br/>';