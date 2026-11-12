<?php

declare(strict_types=1);

namespace App\Exceptions;

class NotFoundException extends AppException
{
    public function __construct(string $resource, int $id)
    {
        parent::__construct(sprintf('%s #%d introuvable.', $resource, $id), 404);
    }
}
