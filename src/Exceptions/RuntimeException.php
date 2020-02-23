<?php

namespace Illuminated\Console\Exceptions;

use Exception;
use Symfony\Component\Console\Exception\RuntimeException as SymfonyRuntimeException;

class RuntimeException extends SymfonyRuntimeException
{
    /**
     * The context.
     *
     * @var array
     */
    private $context;

    /**
     * Create a new instance of the exception.
     *
     * @param string $message
     * @param array $context
     * @param int $code
     * @param \Exception|null $previous
     * @return void
     */
    public function __construct(string $message = '', array $context = [], int $code = 0, Exception $previous = null)
    {
        $this->context = $context;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the context.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
