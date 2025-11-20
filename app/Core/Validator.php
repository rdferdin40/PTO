<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Input Validator
 *
 * Simple validation library for form inputs
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validate fields
     */
    public function validate(array $rules): bool
    {
        foreach ($rules as $field => $ruleSet) {
            $rulesArray = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;

            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply single validation rule
     */
    private function applyRule(string $field, string $rule): void
    {
        $value = $this->data[$field] ?? null;

        // Parse rule with parameters (e.g., "min:3")
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $param = $parts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = __('validation_required', ['field' => $field]);
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = __('validation_email', ['field' => $field]);
                }
                break;

            case 'min':
                if ($value && strlen($value) < (int)$param) {
                    $this->errors[$field][] = __('validation_min', ['field' => $field, 'min' => $param]);
                }
                break;

            case 'max':
                if ($value && strlen($value) > (int)$param) {
                    $this->errors[$field][] = __('validation_max', ['field' => $field, 'max' => $param]);
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->errors[$field][] = __('validation_numeric', ['field' => $field]);
                }
                break;

            case 'date':
                if ($value && !strtotime($value)) {
                    $this->errors[$field][] = __('validation_date', ['field' => $field]);
                }
                break;
        }
    }

    /**
     * Get validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }
}
