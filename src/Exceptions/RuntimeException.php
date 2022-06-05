<?php

namespace Illuminated\Console\Exceptions;

use Exception;
use Symfony\Component\Console\Exception\RuntimeException as SymfonyRuntimeException;

class RuntimeException extends SymfonyRuntimeException
{
    /**
     * The context.
     */
    private array $context;

    /**
     * Create a new instance of the exception.
     */
    public function __construct(string $message = '', array $context = [], int $code = 0, Exception $previous = null)
    {
        $this->context = $context;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the context.
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
