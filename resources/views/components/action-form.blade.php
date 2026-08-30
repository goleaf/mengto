@props([
    'action',
    'payload' => [],
])

<form
    method="POST"
    action="{{ $action }}"
    class="contents"
    data-action-form
    data-action-pending="false"
>
    @csrf

    @forelse ($payload as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @empty
    @endforelse

    {{ $slot }}
</form>
