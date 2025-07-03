<div class="fi-theme-toggle">
    <button x-data="{
        theme: null,
    
        init: function() {
            this.theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
    
            $watch('theme', () => {
                localStorage.setItem('theme', this.theme)
    
                if (this.theme === 'dark') {
                    document.documentElement.classList.add('dark')
                } else {
                    document.documentElement.classList.remove('dark')
                }
            })
        },
    }" x-on:click="theme = theme === 'light' ? 'dark' : 'light'" type="button"
        class="flex rounded-full bg-gray-100 p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-500 dark:hover:text-gray-400"
        aria-label="Toggle theme">
        {{-- IKON BULAN (selalu tampil) --}}
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
            {{-- Kelas warna tema hanya aktif saat theme adalah 'dark' --}} :class="{ 'text-primary-500 dark:text-primary-400': theme === 'dark' }">
            <path d="M17.293 13.293A8 8 0 0 1 6.707 2.707a8.001 8.001 0 1 0 10.586 10.586Z" />
        </svg>
    </button>
</div>
