<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Common\Exceptions;

use Exception;

/**
 * Class QueryExecutionException
 *
 * @package IvyForms\Common\Exceptions
 */
class QueryExecutionException extends Exception
{
    /**
     * QueryExecutionException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = 'Query Execution Error', $code = 0, Exception $previous = null)
    {
        if ($previous) {
            $message .= ' ' . $previous->getMessage();
        }

        parent::__construct($message, $code, $previous);
    }
}
