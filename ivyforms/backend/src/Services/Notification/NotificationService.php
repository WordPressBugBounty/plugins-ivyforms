<?php

namespace IvyForms\Services\Notification;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Sanitizer\HtmlSanitizer;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Entity\Notification\Notification;
use IvyForms\Factory\Notification\NotificationFactory;
use IvyForms\Repository\Notification\NotificationRepositoryInterface;
use IvyForms\Services\Placeholder\PlaceholderService;
use IvyForms\Services\Media\ImageService;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\Services\Mailer\MailerService;

class NotificationService
{
    private NotificationRepositoryInterface $notificationRepository;
    private ImageService $imageService;

    // Constructor injection for the NotificationRepository via PHP-DI
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        ImageService $imageService
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->imageService = $imageService;
    }

    /**
     * Get all notifications from the repository.
     *
     * @return array<mixed>|null
     */
    public function getAllNotifications(): ?array
    {
        return $this->notificationRepository->getAll();
    }

    /**
     * Create a new notification.
     *
     * @param Notification $notificationData
     *
     * @return int Notification ID
     */
    public function createNotification(Notification $notificationData): int
    {
        // Process images in message content before saving
        $message = $notificationData->getMessage();
        if (!empty($message)) {
            $processedMessage = $this->imageService->processImagesInContent($message, 'notification');
            $notificationData->setMessage($processedMessage);
        }

        // Create notification via repository and return new notification ID
        return $this->notificationRepository->add($notificationData);
    }

    /**
     * Get a specific notification by its ID.
     *
     * @param int $notificationId
     *
     * @return object NotificationEntity
     *
     * @throws NotFoundException If the notification is not found
     */
    public function getNotificationById(int $notificationId): object
    {
        $notification = $this->notificationRepository->getById($notificationId);

        if (!$notification) {
            throw new NotFoundException(
                BackendStrings::getExceptionStrings()['notification_not_found']
            );
        }

        return $notification;
    }

    /**
     * Update an existing notification.
     *
     * @param int $notificationId
     * @param Notification $notificationData
     *
     * @return bool
     */
    public function updateNotification(int $notificationId, Notification $notificationData): bool
    {
        // Process images in message content before saving
        $message = $notificationData->getMessage();
        if (!empty($message)) {
            $processedMessage = $this->imageService->processImagesInContent($message, 'notification');
            $notificationData->setMessage($processedMessage);
        }

        // Validate notification data before updating

        return $this->notificationRepository->update($notificationId, $notificationData);
    }

    /**
     * Delete a notification by its ID.
     *
     * @param int $notificationId
     *
     * @return int
     */
    public function deleteNotification(int $notificationId): int
    {
        return $this->notificationRepository->delete($notificationId);
    }

    /**
     * Delete multiple notifications by their IDs.
     *
     * @param array<int> $notificationIds
     *
     * @return int Number of deleted notifications
     */
    public function deleteNotifications(array $notificationIds): int
    {
        return $this->notificationRepository->deleteMany($notificationIds);
    }

    /**
     * Get notifications by form ID.
     *
     * @param int $formId
     *
     * @return array<Notification>
     *
     * @throws NotFoundException If no notifications are found
     */
    public function getNotificationsByFormId(int $formId): array
    {
        $notification = $this->notificationRepository->getAllById($formId);

        if (!$notification) {
            throw new NotFoundException(
                BackendStrings::getExceptionStrings()['notification_not_found']
            );
        }

        return $notification;
    }

    /**
     * Get active notifications by form ID.
     *
     * @param int $formId
     *
     * @return array<Notification>
     *
     */
    public function getActiveNotificationsByFormId(int $formId): array
    {
        $notification = $this->notificationRepository->getAllActiveById($formId);

        // TODO Implement handling no notifications found, but do not return an error to the user
        // For now, log the error if WP_DEBUG is enabled
        if (!$notification && defined('WP_DEBUG') && WP_DEBUG) {
            error_log(BackendStrings::getExceptionStrings()['active_notif_not_found']);
        }

        return $notification;
    }

    /**
     * Delete notifications by multiple form IDs.
     *
     * @param array<int> $formIds
     *
     * @return int Number of deleted notifications
     * @throws InvalidArgumentException If no form IDs are provided
     */
    public function deleteNotificationsByFormIds(array $formIds): int
    {
        if (empty($formIds)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['no_form_ids_provided']
            );
        }

        return $this->notificationRepository->deleteManyByForeignKeyValues($formIds);
    }

    /**
     * Duplicate notifications from one form to another.
     *
     * @param int $originalFormId
     * @param int $newFormId
     *
     * @return void
     * @throws NotFoundException
     * @throws InvalidArgumentException
     */
    public function duplicateNotifications(int $originalFormId, int $newFormId): void
    {
        $notifications = $this->getNotificationsByFormId($originalFormId);
        foreach ($notifications as $notification) {
            $notificationData = $notification->toArray();
            unset($notificationData['id']);
            $notificationData['formId'] = $newFormId;

            $this->createNotification(NotificationFactory::create($notificationData));
        }
    }

    /**
     * Search notifications with pagination and sorting
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function searchNotifications(array $params): array
    {
        return $this->notificationRepository->search($params);
    }

    /**
     * Create a default notification for a form.
     *
     * @param int $formId
     * @param string $formName
     * @param string $adminEmail
     *
     * @return void
     * @throws InvalidArgumentException|QueryExecutionException
     */
    public function createDefaultNotificationForForm(int $formId, string $formName, string $adminEmail): void
    {
        $defaultNotification = [
            'formId'      => $formId,
            'name'        => BackendStrings::getAllFormsStrings()['admin_notification_email'],
            'sender'      => $adminEmail,
            'receiver'    => $adminEmail,
            'enabled'     => 1,
            'subject'     => $formName,
            'message'     => '{{all_data}}',
            'smartLogic'  => [
                'enabled' => false,
                'match' => 'any',
                'rules' => [],
            ],
        ];
        $notification = NotificationFactory::create($defaultNotification);
        $notificationId = $this->createNotification($notification);
        if (!$notificationId) {
            throw new QueryExecutionException(
                BackendStrings::getSettingsFormBuilderStrings()['failed_to_create_default_notification']
            );
        }
        $notification->setId($notificationId);
    }

    /**
     * Process notifications for the form.
     *
     * @param int $formId
     * @param array<mixed> $submissionData
     * @param array<mixed> $formFields
     * @param array<string, mixed> $fieldData
     * @param array<string, mixed> $generalData
     * @param MailerService $mailerService
     * @param array<string, string> $fieldLabels
     * @return bool
     */
    public function processNotifications(
        int $formId,
        array $submissionData,
        array $formFields,
        array $fieldData,
        array $generalData,
        MailerService $mailerService,
        array $fieldLabels = []
    ): bool {
        $activeNotifications = $this->getActiveNotificationsByFormId($formId);
        $logicFormFields = $formFields;
        $exceptionStrings = BackendStrings::getExceptionStrings();
        foreach ($activeNotifications as $notification) {
            if (empty($notification->getNotificationContact()->getReceiver())) {
                continue;
            }

            $shouldSend = apply_filters(
                'ivyforms/notification/should_send',
                true,
                $notification->getSmartLogic(),
                $submissionData,
                $logicFormFields,
                $notification,
                $formId
            );

            if (!$shouldSend) {
                continue;
            }

            // Resolve sender and receiver
            $sender = $this->resolveAddress(
                $notification->getNotificationContact()->getSender(),
                $fieldData,
                $generalData,
                $fieldLabels
            );

            $receiver = $this->resolveAddress(
                $notification->getNotificationContact()->getReceiver(),
                $fieldData,
                $generalData,
                $fieldLabels
            );

            $cc = $this->resolveAddressList(
                $notification->getNotificationContact()->getCc(),
                $fieldData,
                $generalData,
                $fieldLabels
            );

            $bcc = $this->resolveAddressList(
                $notification->getNotificationContact()->getBcc(),
                $fieldData,
                $generalData,
                $fieldLabels
            );

            // Build subject and sanitized message
            $subject = PlaceholderService::replacePlaceholders(
                $notification->getSubject(),
                $fieldData,
                $generalData,
                $fieldLabels,
                $notification
            );

            $safeMessage = $this->buildSafeMessage(
                $notification->getMessage(),
                $fieldData,
                $generalData,
                $fieldLabels,
                $notification
            );

            // Resolve replyTo and validate; fallback to sender if empty
            $replyTo = $this->resolveAddress(
                $notification->getNotificationContact()->getReplyTo(),
                $fieldData,
                $generalData,
                $fieldLabels
            );
            if (empty($replyTo) && !empty($sender)) {
                $replyTo = $sender;
            }

            if (empty($sender) || empty($receiver)) {
                error_log(
                    $exceptionStrings['error_log_invalid_email'] . $sender . ' ' . $receiver
                );
                return false;
            }

            // Apply resolved values back to the notification
            $notification->getNotificationContact()->setSender($sender);
            $notification->getNotificationContact()->setReceiver($receiver);
            $notification->getNotificationContact()->setReplyTo($replyTo);
            $notification->getNotificationContact()->setCc($cc);
            $notification->getNotificationContact()->setBcc($bcc);
            $notification->setSubject($subject);
            $notification->setMessage($safeMessage);

            // Send notification
            $sent = $mailerService->sendEmail($notification, $submissionData);
            if (!$sent) {
                error_log($exceptionStrings['error_log_failed_send'] . ' ' . $formId);
            }
        }

        return true;
    }

    /**
     * Resolve an email address template that may contain placeholders
     *
     * Returns the original replaced string when valid, or empty string when invalid.
     *
     * @param string $template
     * @param array<string,mixed> $fieldData
     * @param array<string,mixed> $generalData
     * @param array<string,string> $fieldLabels
     * @return string
     */
    private function resolveAddress(string $template, array $fieldData, array $generalData, array $fieldLabels): string
    {
        if (Sanitizer::containsEmailHeaderInjection($template)) {
            return '';
        }

        $rawReplaced = PlaceholderService::replacePlaceholders(
            $template,
            $fieldData,
            $generalData,
            $fieldLabels
        );

        if (Sanitizer::containsEmailHeaderInjection($rawReplaced)) {
            return '';
        }

        $emailCandidate = $rawReplaced;
        if (preg_match('/<([^>]+)>/', $rawReplaced, $match)) {
            $emailCandidate = trim($match[1]);
        }

        return Sanitizer::isValidEmail($emailCandidate) ? $rawReplaced : '';
    }

    /**
     * Resolve a comma-separated list of email address templates.
     *
     * @param string $template
     * @param array<string,mixed> $fieldData
     * @param array<string,mixed> $generalData
     * @param array<string,string> $fieldLabels
     * @return string
     */
    private function resolveAddressList(
        string $template,
        array $fieldData,
        array $generalData,
        array $fieldLabels
    ): string {
        $template = trim($template);
        if ($template === '') {
            return '';
        }

        $parts = array_map('trim', explode(',', $template));
        $resolved = [];

        foreach ($parts as $part) {
            if ($part === '' || Sanitizer::containsEmailHeaderInjection($part)) {
                continue;
            }

            $address = $this->resolveAddress($part, $fieldData, $generalData, $fieldLabels);
            if ($address !== '') {
                $resolved[] = $address;
            }
        }

        return implode(', ', $resolved);
    }

    /**
     * Build a sanitized HTML message from a template with placeholders.
     *
     * @param string $template
     * @param array<string,mixed> $fieldData
     * @param array<string,mixed> $generalData
     * @param array<string,string> $fieldLabels
     * @param Notification $notification
     * @return string
     */
    private function buildSafeMessage(
        string $template,
        array $fieldData,
        array $generalData,
        array $fieldLabels,
        Notification $notification
    ): string {
        $message = PlaceholderService::replacePlaceholders(
            $template,
            $fieldData,
            $generalData,
            $fieldLabels,
            $notification
        );

        return HtmlSanitizer::sanitizeHtmlContent($message);
    }
}
