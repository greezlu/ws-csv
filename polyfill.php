<?php

if (!function_exists('str_contains')) {
    /**
     * Determine if a string contains a given substring.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    function str_contains(string $haystack, string $needle): bool
    {
        return !empty($needle) && mb_strpos($haystack, $needle) !== false;
    }
}
