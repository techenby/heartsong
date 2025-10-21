<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

if (! function_exists('keyFor')) {
    /**
     * Build a consistent key for a given Eloquent model.
     *
     * Format: {prefix}-{model name}-{model id}-{suffix}
     *
     * - Model name is the base class name in kebab-case (e.g., App\Models\Course => "course").
     * - Prefix and suffix are optional and omitted if null/empty.
     */
    function keyFor(Model $model, ?string $prefix = null, ?string $suffix = null): string
    {
        $segments = [];

        if (! empty($prefix)) {
            $segments[] = trim($prefix, '-');
        }

        $modelName = Str::of(class_basename($model))->kebab()->toString();
        $segments[] = $modelName;
        $segments[] = (string) $model->getKey();

        if (! empty($suffix)) {
            $segments[] = trim($suffix, '-');
        }

        return implode('-', $segments);
    }
}
