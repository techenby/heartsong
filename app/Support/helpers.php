<?php

declare(strict_types=1);

use Illuminate\Support\Str;

if (! function_exists('keyFor')) {
    /**
     * Build a consistent key for a given object.
     *
     * Format: {prefix}-{class name}-{class id}-{suffix}
     *
     * - Object name is the base class name in kebab-case (e.g., App\Models\Course => "course", App\Enums\Period => "period").
     * - Prefix and suffix are optional and omitted if null/empty.
     */
    function keyFor($item, ?string $prefix = null, ?string $suffix = null): string
    {
        $segments = [];

        if (! empty($prefix)) {
            $segments[] = trim($prefix, '-');
        }

        // todo figure out if there's duplicate class/class names?
        $itemName = Str::of(class_basename($item))->kebab()->toString();
        $segments[] = $itemName;
        $segments[] = (string) (is_string($item) && enum_exists($item)) ? $item->name : $item->getKey();

        if (! empty($suffix)) {
            $segments[] = trim($suffix, '-');
        }

        return implode('-', $segments);
    }
}
