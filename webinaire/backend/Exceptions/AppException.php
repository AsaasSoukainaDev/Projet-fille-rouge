<?php

declare(strict_types=1);

namespace App\Exceptions;

class AppException extends \Exception
{
    public function __construct(string $message = '', int $code = 500)
    {
        parent::__construct($message, $code);
    }
}
