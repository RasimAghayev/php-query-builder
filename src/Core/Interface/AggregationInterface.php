<?php

namespace App\Core\Interface;
interface AggregationInterface
{
    public function sum(string $expression, string $alias = 'result'): self;
    public function avg(string $expression, string $alias = 'result'): self;
    public function count(string $expression, string $alias = 'result'): self;
}