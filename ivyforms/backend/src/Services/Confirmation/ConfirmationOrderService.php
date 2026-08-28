<?php

namespace IvyForms\Services\Confirmation;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Repository\Confirmation\ConfirmationRepositoryInterface;
use IvyForms\Services\Translations\BackendStrings;

class ConfirmationOrderService
{
    private ConfirmationRepositoryInterface $confirmationRepository;

    public function __construct(ConfirmationRepositoryInterface $confirmationRepository)
    {
        $this->confirmationRepository = $confirmationRepository;
    }

    /**
     * @param int $formId
     * @param array<int, int> $orderedIds
     * @return void
     */
    public function applyOrder(int $formId, array $orderedIds): void
    {
        $this->confirmationRepository->updatePositions($formId, $orderedIds);

        if (!empty($orderedIds)) {
            $this->confirmationRepository->syncDefaultFlag($formId, (int) $orderedIds[0]);
        }
    }

    /**
     * @param int $formId
     * @param array<int, int> $orderedIds
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateReorderIds(int $formId, array $orderedIds): void
    {
        if (empty($orderedIds)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        foreach ($orderedIds as $id) {
            if ((int) $id <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_request_data']
                );
            }
        }

        if (count($orderedIds) !== count(array_unique($orderedIds))) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $existing = $this->confirmationRepository->getAllById($formId);
        $existingIds = array_map(static fn ($confirmation) => $confirmation->getId(), $existing);
        sort($existingIds);

        $normalizedIds = array_map('intval', $orderedIds);
        sort($normalizedIds);

        if ($normalizedIds !== $existingIds) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }
    }
}
