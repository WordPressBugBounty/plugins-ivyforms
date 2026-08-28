<?php

namespace IvyForms\Services\Confirmation;

use IvyForms\Entity\Confirmation\Confirmation;

/**
 * Resolves which confirmation applies after submission (conditional logic + ordering).
 *
 * Resolution is two-phase:
 * 1. Walk enabled confirmations by position and return the first whose smart logic matches.
 *    Rows with smart logic disabled are skipped here (matches() returns false).
 * 2. If none match, resolveFallback() picks default → first non-conditional → first enabled.
 */
class ConfirmationResolver
{
    /**
     * @param array<Confirmation> $confirmations
     * @param array<string, mixed> $submissionData
     * @param array<int, mixed> $formFields
     */
    public function resolve(
        array $confirmations,
        array $submissionData,
        array $formFields,
        int $formId
    ): ?Confirmation {
        $enabled = array_values(array_filter(
            $confirmations,
            static fn (Confirmation $confirmation): bool => $confirmation->isEnabled()
        ));

        if ($enabled === []) {
            return null;
        }

        usort(
            $enabled,
            static function (Confirmation $left, Confirmation $right): int {
                $position = $left->getPosition() <=> $right->getPosition();
                if ($position !== 0) {
                    return $position;
                }

                return $left->getId() <=> $right->getId();
            }
        );

        foreach ($enabled as $confirmation) {
            if ($this->matches($confirmation, $submissionData, $formFields, $formId)) {
                return $confirmation;
            }
        }

        return $this->resolveFallback($enabled);
    }

    /**
     * @param array<Confirmation> $enabled
     */
    private function resolveFallback(array $enabled): ?Confirmation
    {
        foreach ($enabled as $confirmation) {
            if ($confirmation->isDefault()) {
                return $confirmation;
            }
        }

        foreach ($enabled as $confirmation) {
            $smartLogic = $confirmation->getSmartLogic();
            if (empty($smartLogic['enabled'])) {
                return $confirmation;
            }
        }

        return $enabled[0] ?? null;
    }

    /**
     * @param array<string, mixed> $submissionData
     * @param array<int, mixed> $formFields
     */
    private function matches(
        Confirmation $confirmation,
        array $submissionData,
        array $formFields,
        int $formId
    ): bool {
        $smartLogic = $confirmation->getSmartLogic();
        if (empty($smartLogic['enabled'])) {
            // Non-conditional rows are only considered in resolveFallback() so
            // conditional confirmations are evaluated first by position.
            return false;
        }

        return (bool) apply_filters(
            'ivyforms/confirmation/should_use',
            true,
            $smartLogic,
            $submissionData,
            $formFields,
            $confirmation,
            $formId
        );
    }
}
