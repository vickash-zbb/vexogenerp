<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(array $data, int $status = 200): never
    {
        json_response($data, $status);
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function jsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '{}', true);
        return is_array($data) ? $data : [];
    }

    protected function validate(array $rules): array
    {
        $errors = [];
        $data = [];
        foreach ($rules as $field => $rule) {
            $value = trim((string) ($this->input($field) ?? $this->jsonInput()[$field] ?? ''));
            $data[$field] = $value;
            if (str_contains($rule, 'required') && $value === '') {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
            if (str_contains($rule, 'email') && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'Invalid email address.';
            }
        }
        if ($errors) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        return $data;
    }
}
