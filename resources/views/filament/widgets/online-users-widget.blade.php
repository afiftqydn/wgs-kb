@php
    use Illuminate\Support\Str;
@endphp

<x-filament-widgets::widget>
    <x-filament::card>
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold tracking-tight text-gray-950 dark:text-white">
                Pengguna Online ({{ $this->onlineUsers->count() }})
            </h2>
        </div>

        <div class="mt-4 space-y-4">
            @if ($this->onlineUsers->count() > 0)
                @foreach ($this->onlineUsers as $user)
                    <div class="flex items-center space-x-4">
                        {{-- Avatar dengan Inisial --}}
                        <div class="relative flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white bg-primary-500 rounded-full">
                                {{ Str::of($user->name)->substr(0, 2)->upper() }}
                            </div>
                            {{-- Titik Status Online --}}
                            <span class="absolute bottom-0 right-0 block w-3 h-3 bg-green-500 border-2 border-white rounded-full dark:border-gray-800"></span>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                {{ $user->name }}
                            </p>
                            <p class="text-xs text-gray-500 truncate dark:text-gray-400">
                                {{-- Menampilkan peran pertama pengguna --}}
                                {{ $user->roles->first()?->name ?? 'User' }}
                            </p>
                        </div>
                        
                        <div class="inline-flex items-center text-xs font-semibold text-gray-500 dark:text-gray-400">
                           Aktif {{ $user->last_activity_at->diffForHumans(null, true) }} lalu
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <p>Tidak ada pengguna yang sedang online.</p>
                </div>
            @endif
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
