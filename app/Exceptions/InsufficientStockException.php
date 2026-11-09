<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected array $shortages;

    public function __construct(string $message = 'Stok bahan baku tidak mencukupi.', array $shortages = [], int $code = 0, ?\Throwable $previous = null)
    {
        $this->shortages = $shortages;
        parent::__construct($message, $code, $previous);
    }

    public function getShortages(): array
    {
        return $this->shortages;
    }
}
