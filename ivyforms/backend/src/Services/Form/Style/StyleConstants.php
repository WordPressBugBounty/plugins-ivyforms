<?php

namespace IvyForms\Services\Form\Style;

/**
 * Shared base class for style constants
 */
abstract class StyleConstants
{
    /**
     * Allowed values for style properties
     */
    public const ALLOWED_ALIGNMENTS = ['left', 'center', 'right'];
    public const ALLOWED_BORDER_STYLES = ['solid', 'dashed', 'dotted'];
    public const ALLOWED_FONT_STYLES = ['normal', 'italic'];
    public const ALLOWED_FONT_WEIGHTS = ['100', '200', '300', '400', '500', '600', '700', '800', '900'];
}
