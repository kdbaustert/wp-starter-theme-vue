@props([
  'type' => null,
  'message' => null,
])

@php($class = match ($type) {
  'success' => 'alert-success',
  'caution' => 'alert-warning',
  'warning' => 'alert-danger',
  default => 'alert-primary',
})

<div {{ $attributes->merge(['class' => "alert {$class}", 'role' => 'alert']) }}>
  {!! $message ?? $slot !!}
</div>
