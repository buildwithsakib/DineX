<?php
// CSRF token helpers

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';   // needed for e()

function validate_required(array $data, array $fields): array
{
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    return $errors;
}

function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_min_length(string $value, int $min): bool
{
    return strlen(trim($value)) >= $min;
}

function validate_max_length(string $value, int $max): bool
{
    return strlen(trim($value)) <= $max;
}

function validate_numeric($value): bool
{
    return is_numeric($value);
}

function validate_positive_decimal($value): bool
{
    return is_numeric($value) && (float)$value >= 0;
}

function validate_phone(string $phone): bool
{
    return preg_match('/^[0-9+\-\s()]{7,20}$/', $phone);
}

function validation_errors_to_message(array $errors): string
{
    return implode(' ', $errors);
}