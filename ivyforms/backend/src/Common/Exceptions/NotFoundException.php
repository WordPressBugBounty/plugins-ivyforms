<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Common\Exceptions;

use Exception;

/**
 * Class NotFoundException
 *
 * @package IvyForms\Common\Exceptions
 */
class NotFoundException extends Exception
{
    /**
     * NotFoundException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = 'not_found', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
