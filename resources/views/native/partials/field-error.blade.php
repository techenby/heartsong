{{-- Expects $field; reads the screen's $errors (from HasValidationErrors). --}}
@isset($errors[$field][0])
    <native:text class="text-sm text-theme-destructive">{{ $errors[$field][0] }}</native:text>
@endisset
