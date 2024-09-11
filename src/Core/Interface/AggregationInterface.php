<?php

namespace App\Core\Interface;
interface AggregationInterface
{
    public function sum(string $expression): string;
    public function avg(string $expression): string;
    public function count(string $expression): string;
}