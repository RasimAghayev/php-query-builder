<?php

namespace App\Core\Interface;

interface QueryOtherBuilderInterface
{
    public function orderBy(string $column, string $direction = 'ASC'): self;
    public function groupBy(string ...$columns): self;
    public function having(string $column, string $operator, mixed $value): self;
    public function limit(int $limit): self;
    public function offset(int $offset): self;
}