<?php

namespace App\Core\Interface;

interface QueryStaticBuilderInterface
{
    public function table(string $table): self;
    public function select(string ...$columns): self;
    public function toSql(): string;
}