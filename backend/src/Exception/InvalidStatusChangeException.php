<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Exception thrown when attempting to change a status in an invalid way.
 * 
 */
class InvalidStatusChangeException extends BadRequestHttpException
{
    public function __construct(string $message = 'Status cannot be changed once it is accepted or rejected.', \Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}