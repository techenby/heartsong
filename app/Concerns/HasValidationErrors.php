<?php

namespace App\Concerns;

use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait HasValidationErrors
{
    /** @var array<string, array<int, string>> Validation messages keyed by field, e.g. "meetings.0.day". */
    public array $errors = [];

    public function error(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Validate and return the validated data, or record the errors and return null.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     * @param  array<string, string>  $messages
     * @param  array<string, string>  $attributes
     * @return array<string, mixed>|null
     */
    protected function validateOrRecord(array $data, array $rules, array $messages = [], array $attributes = []): ?array
    {
        return $this->recordingErrors(fn () => Validator::make($data, $rules, $messages, $attributes)->validate());
    }

    /**
     * Run an action that may throw a ValidationException, recording its errors instead.
     *
     * @template T
     *
     * @param  Closure(): T  $action
     * @return T|null
     */
    protected function recordingErrors(Closure $action): mixed
    {
        try {
            $result = $action();
        } catch (ValidationException $exception) {
            $this->errors = $exception->errors();

            return null;
        }

        $this->errors = [];

        return $result;
    }
}
