<?php
namespace App\Exceptions;

class UnauthorizedException extends HttpException
{
    public function __construct(string $message = 'Acesso não autorizado')
    {
        parent::__construct($message, 401);
    }
}
