<?php

declare(strict_types=1);

namespace App\Exceptions;

class DataAccessException extends AppException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 500);
    }
}
