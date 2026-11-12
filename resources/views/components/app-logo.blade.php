@props([
    'sidebar' => false,
])

@php
    $appName = 'LifeOS';
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$appName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-xl bg-amber-400/20 p-1">
            <x-app-logo-icon class="size-6 drop-shadow-sm" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$appName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-xl bg-amber-400/20 p-1">
            <x-app-logo-icon class="size-6 drop-shadow-sm" />
        </x-slot>
    </flux:brand>
@endif
