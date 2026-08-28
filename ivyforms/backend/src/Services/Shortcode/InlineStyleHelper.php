<?php

/**
 * Helper for IvyForms frontend inline styles
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Shortcode;

class InlineStyleHelper
{
    /**
     * Returns the inline CSS for IvyForms frontend
     * @return string
     */
    public static function getFrontendInlineCss(): string
    {
        return '.ivyforms-skeleton {
                width: 100%;
                max-width: 720px;
                margin: 0 auto;
            }

            .ivyforms-skeleton-title {
                height: 28px;
                width: 40%;
                background: #dbdee3;
                margin-bottom: 12px;
            }

            .ivyforms-skeleton-field {
                margin-bottom: 16px;
            }

            .ivyforms-skeleton-label {
                height: 22px;
                width: 20%;
                background: #dbdee3;
                font-size: 14px;
                border-radius: 4px;
                margin-bottom: 4px;
            }

            .ivyforms-skeleton-input {
                line-height: 22px;
                font-size: 16px;
                background: #dbdee3;
                border-radius: 8px;
                width: 100%;
            }

            .ivyforms-skeleton-email,
            .ivyforms-skeleton-textarea,
            .ivyforms-skeleton-text,
            .ivyforms-skeleton-number,
            .ivyforms-skeleton-phone {
                height: 40px;
            }

            .ivyforms-skeleton-textarea {
                height: 60px;
            }

            .ivyforms-skeleton-button {
                height: 40px;
                width: 100px;
                background: #c0c5cb;
                border-radius: 6px;
                margin-top: 12px;
            }

            .ivyforms-skeleton-animate {
                animation: pulse 1.5s infinite ease-in-out;
            }

            @keyframes pulse {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.4;
                }
            }

            .ivyforms-preview {
                width: 100%;
                max-width: 720px;
                margin: 0 auto;
                background: #fff;
                padding: 40px;
            }

            .ivyforms-preview h1 {
                font-size: 50px !important;
                font-weight: normal !important;
                margin-top: 0 !important;
                margin-left: 0 !important;
                margin-bottom: 20px !important;
                padding: 0 !important;
            }';
    }
}
