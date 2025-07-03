<x-filament-widgets::widget>
    <x-filament::card>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
            Pengguna Online ({{ count($users) }})
        </h2>

        <div class="mt-4 space-y-4">
            @forelse ($users as $user)
                <div class="flex items-center space-x-3">
                    <!-- Indikator Online -->
                    <span class="flex h-3 w-3">
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-green-500"></span>
                    </span>

                    {{-- <!-- Avatar Pengguna -->
                    <img class="h-8 w-8 rounded-full" src="{{ $user->getFilamentAvatarUrl() }}" alt="{{ $user->name }}"> --}}

                    <!-- Nama dan Waktu Aktivitas -->
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $user->name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Aktif: {{ \Carbon\Carbon::parse($user->last_activity_at)->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 dark:text-gray-400">
                    Tidak ada pengguna yang sedang online.
                </p>
            @endforelse
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
