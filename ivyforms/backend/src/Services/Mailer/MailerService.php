<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Mailer;

use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Entity\Notification\Notification;

class MailerService
{
    /**
     * Send an email using WordPress wp_mail function
     *
     * @param Notification $notification The notification object with email data
     * @param mixed[] $formData The submitted form data for template variables
     *
     * @return bool Whether the email was sent successfully
     */
    public function sendEmail(Notification $notification, array $formData): bool
    {
        // Extract notification data
        $contact      = $notification->getNotificationContact();
        $toAddresses  = $this->validatedAddresses($contact->getReceiver());
        $ccAddresses  = $this->validatedAddresses($contact->getCc());
        $bccAddresses = $this->validatedAddresses($contact->getBcc());
        $to      = implode(', ', $toAddresses);
        $cc      = implode(', ', $ccAddresses);
        $bcc     = implode(', ', $bccAddresses);
        $from    = $contact->getSender();
        $replyTo = $contact->getReplyTo();

        if ($to === '') {
            return false;
        }

        if ($this->hasUnsafeHeaderValues($from, $replyTo, $cc, $bcc)) {
            return false;
        }

        $subject = $notification->getSubject();
        $message = $this->replacePlaceholders($notification->getMessage(), $formData);

        $headers        = $this->buildHeaders($from, $replyTo);
        $visibleHeaders = $headers;
        if ($cc !== '') {
            $visibleHeaders[] = 'Cc: ' . $cc;
        }

        // Send email using WordPress function
        $sent = wp_mail($to, $subject, $message, $visibleHeaders);

        if ($sent && $bccAddresses !== []) {
            $sent = $this->sendBccCopies($bccAddresses, $subject, $message, $headers);
        }

        return $sent;
    }

    /**
     * Build the headers shared by every copy of the notification.
     *
     * @return string[]
     */
    private function buildHeaders(string $from, string $replyTo): array
    {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        if (!empty($from)) {
            $headers[] = 'From: ' . $from;
        }

        // If replyTo is set and not identical to From, add Reply-To header
        if (!empty($replyTo) && $replyTo !== $from) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        return $headers;
    }

    /**
     * PHPMailer keeps the Bcc header in the message on the mail()/sendmail/qmail
     * transports, so a shared send would expose Bcc addresses to the To and Cc
     * recipients. Each Bcc recipient therefore gets its own copy.
     *
     * @param string[] $bccAddresses
     * @param string[] $headers
     */
    private function sendBccCopies(array $bccAddresses, string $subject, string $message, array $headers): bool
    {
        $sent = true;

        foreach ($bccAddresses as $bccAddress) {
            $sent = wp_mail($bccAddress, $subject, $message, $headers) && $sent;
        }

        return $sent;
    }

    /**
     * Replace template variables in content with actual form data
     * Supports format like {{field_name}}
     *
     * @param string $content Content with template variables
     * @param mixed[] $formData Form submission data
     *
     * @return string Content with replaced variables
     */
    private function replacePlaceholders(string $content, array $formData): string
    {
        // Replace field variables like {{field_name}}
        preg_match_all('/{{(.+?)}}/s', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $key => $fieldName) {
                $fieldValue = $formData[$fieldName] ?? '';
                if (is_array($fieldValue)) {
                    $fieldValue = implode(', ', $fieldValue);
                }

                $content = str_replace($matches[0][$key], $fieldValue, $content);
            }
        }

        // Replace special variables
        $content = str_replace('{{all_fields}}', $this->getAllFieldsHtml($formData), $content);

        return $content;
    }

    /**
     * Generate HTML table with all form fields for email template
     *
     * @param mixed[] $formData Form submission data
     *
     * @return string HTML representation of all fields
     */
    private function getAllFieldsHtml(array $formData): string
    {
        // Generate HTML table for all fields
        $html = '<h2>Form Submission Details</h2>';
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';

        foreach ($formData as $fieldName => $fieldValue) {
            // Skip system fields
            if (in_array($fieldName, ['formId', 'nonce'])) {
                continue;
            }

            // Format array values
            if (is_array($fieldValue)) {
                $fieldValue = implode(', ', $fieldValue);
            }

            // Format label by converting snake_case or camelCase to Title Case
            $label = ucwords(str_replace(['_', '-'], ' ', $fieldName));

            $html .= '<tr>';
            $html .= '<th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">';
            $html .=  esc_html($label) . '</th>';
            $html .=  '</th>';
            $html .= '<td style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">';
            $html .=  esc_html($fieldValue) . '</td>';
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    /**
     * @param string ...$values
     */
    private function hasUnsafeHeaderValues(string ...$values): bool
    {
        foreach ($values as $value) {
            if (Sanitizer::containsEmailHeaderInjection($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Keep only validated email addresses for To:/Cc:/Bcc: recipients.
     *
     * @param string $addresses Comma-separated addresses
     * @return string[]
     */
    private function validatedAddresses(string $addresses): array
    {
        $addresses = trim($addresses);
        if ($addresses === '') {
            return [];
        }

        $validated = [];

        foreach (array_map('trim', explode(',', $addresses)) as $address) {
            if ($address === '' || Sanitizer::containsEmailHeaderInjection($address)) {
                continue;
            }

            $emailCandidate = $address;
            if (preg_match('/<([^>]+)>/', $address, $match)) {
                $emailCandidate = trim($match[1]);
            }

            if (Sanitizer::isValidEmail($emailCandidate)) {
                $validated[] = $address;
            }
        }

        return $validated;
    }
}
