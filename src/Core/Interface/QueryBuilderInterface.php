<?php

namespace App\Core\Interface;

interface QueryBuilderInterface
{
    public function table(string $table): self;
    public function select(string ...$columns): self;
    public function where(string $column, string $operator, mixed $value): self;
    public function orWhere(callable $callback): self;
    public function whereIn(string $column, array $values): self;
    public function whereNotIn(string $column, array $values): self;
    public function whereLike(string $column, string $pattern): self;
    public function whereNotLike(string $column, string $pattern): self;
    public function whereBetween(string $column, mixed $start, mixed $end): self;
    public function whereNotBetween(string $column, mixed $start, mixed $end): self;
    public function orderBy(string $column, string $direction = 'ASC'): self;
    public function toSql(): string;
}