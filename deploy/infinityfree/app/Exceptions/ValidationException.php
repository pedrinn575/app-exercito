<?php
namespace App\Exceptions;

class ValidationException extends HttpException
{
    public function __construct(string $message = 'Dados inválidos')
    {
        parent::__construct($message, 422);
    }
}
