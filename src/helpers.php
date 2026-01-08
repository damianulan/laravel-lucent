<?php

use Mews\Purifier\Facades\Purifier;

if ( ! function_exists('class_uses_trait')) {
    /**
     * Checks if trait is used by a target class.
     * It recurses through the whole class inheritance tree.
     *
     * @param  mixed  $trait_class
     * @param  mixed  $target_class
     */
    function class_uses_trait($trait_class, $target_class): bool
    {
        return in_array($trait_class, class_uses_recursive($target_class));
    }
}

if ( ! function_exists('clean_html')) {

    /**
     * Uses mews/purifier custom setup to clean HTML input off of possible XSS vulnerabilities
     * Best suited for cleaning before placing in rich text editors
     */
    function clean_html(?string $input): string
    {
        if ($input) {
            return Purifier::clean($input, 'lucent_config');
        }

        return '';
    }
}
