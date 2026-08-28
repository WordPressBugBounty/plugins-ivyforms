<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Common\Exceptions;

/**
 * Validation failure scoped to one or more form field keys (e.g. file-upload_0).
 */
class FieldValidationException extends ValidationException
{
    /**
     * @param string $message Human-readable validation message.
     * @param array<string, array{code: string}> $submissionFieldErrors Map of field key => error metadata.
     */
    public function __construct(string $message, array $submissionFieldErrors)
    {
        parent::__construct($message, 0, null, $submissionFieldErrors);
    }

    /**
     * @return array<string, array{code: string}>
     */
    public function getSubmissionFieldErrors(): array
    {
        /** @var array<string, array{code: string}> */
        return $this->getFieldErrors();
    }
}
