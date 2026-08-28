<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\ValueObjects\Number\Integer;

/**
 * Class IDs
 *
 * @package IvyForms\ValueObjects\Number\Integer
 */
final class IDs
{
    /**
     * @var int
     */
    private int $id;

    /**
     * Id constructor.
     *
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Return the password from the value object
     *
     * @return int
     */
    public function getValue(): int
    {
        return $this->id;
    }
}
