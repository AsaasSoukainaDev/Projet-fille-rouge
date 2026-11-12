<?php

declare(strict_types=1);

namespace App\Exceptions;

class ValidationException extends AppException
{
    /** @var array<int, string> */
    private array $errors;

    /**
     * @param array<int, string> $errors
     */
    public function __construct(array $errors, int $code = 422)
    {
        $this->errors = $errors;
        parent::__construct('Données invalides.', $code);
    }

    /**
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
