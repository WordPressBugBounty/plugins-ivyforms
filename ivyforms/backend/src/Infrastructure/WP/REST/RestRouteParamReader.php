<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Infrastructure\WP\REST;

use WP_REST_Request;

/**
 * Reads positive integer route/body params from a WP REST request.
 */
final class RestRouteParamReader
{
    /**
     * @param WP_REST_Request<array<string, mixed>> $request
     */
    public static function getPositiveIntParam(WP_REST_Request $request, string $key = 'id'): ?int
    {
        $raw = $request->get_param($key);
        if ($raw === null || $raw === '' || !is_scalar($raw)) {
            return null;
        }
        $id = (int) $raw;

        return $id > 0 ? $id : null;
    }
}
