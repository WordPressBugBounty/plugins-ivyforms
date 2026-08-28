<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Common\Exceptions;

use Exception;

/**
 * ValidationException class
 */
class ValidationException extends Exception
{
    /** @var array<string, string|array{code: string}> */
    private array $fieldErrors;

    /**
     * ValidationException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     * @param array<string, string|array{code: string}> $fieldErrors
     */
    public function __construct(
        $message = 'validation_failed',
        $code = 0,
        Exception $previous = null,
        array $fieldErrors = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->fieldErrors = $fieldErrors;
    }

    /**
     * @return array<string, string|array{code: string}>
     */
    public function getFieldErrors(): array
    {
        return $this->fieldErrors;
    }
}
