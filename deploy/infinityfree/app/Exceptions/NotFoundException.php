<?php
namespace App\Exceptions;

class NotFoundException extends HttpException
{
    public function __construct(string $message = 'Recurso não encontrado')
    {
        parent::__construct($message, 404);
    }
}
