{{-- resources/views/filament/hooks/user_name.blade.php --}}

@if (auth()->check())
    <div class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        {{ auth()->user()->name }}
    </div>
@endif
