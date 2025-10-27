<?php
// helpers.php

/**
 * Basic sanitize: trim, remove control-chars
 */
function sanitize_text($str) {
    $s = trim($str);
    // remove non-printable characters
    $s = preg_replace('/[\x00-\x1F\x7F]/u', '', $s);
    return $s;
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Simple integer filter
 */
function int_or_null($val) {
    if ($val === null || $val === '') return null;
    return filter_var($val, FILTER_VALIDATE_INT) ?: null;
}
