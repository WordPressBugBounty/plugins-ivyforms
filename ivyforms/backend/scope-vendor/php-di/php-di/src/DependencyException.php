<?php

declare(strict_types=1);

namespace IvyForms\Vendor\DI;

use IvyForms\Vendor\Psr\Container\ContainerExceptionInterface;

/**
 * Exception for the Container.
 */
class DependencyException extends \Exception implements ContainerExceptionInterface
{
}
