<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Common\Exceptions;

use Exception;

/**
 * Class ForbiddenException
 *
 * Handled by Controller to return a JSON 403 REST response.
 *
 * @package IvyForms\Common\Exceptions
 */
class ForbiddenException extends Exception
{
    /**
     * ForbiddenException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = '', $code = 403, Exception $previous = null)
    {
        if ($message === '' || $message === 'forbidden') {
            $message = esc_html__('You do not have permission to access this page.', 'ivyforms');
        }

        parent::__construct($message, $code, $previous);
    }
}
