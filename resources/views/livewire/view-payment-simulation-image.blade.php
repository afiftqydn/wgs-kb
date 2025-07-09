<div class="p-6 flex flex-col items-center justify-center space-y-6">
    <h2 class="text-lg font-bold text-gray-700 dark:text-gray-300">
        Simulasi Pembayaran
    </h2>

    <img src="{{ $imageUrl }}" alt="Simulasi Pembayaran" class="max-w-full max-h-[60vh] rounded shadow">

    <div class="flex space-x-3">
        <a href="{{ $imageUrl }}" target="_blank"
            class="inline-block px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition">
            Perbesar
        </a>

        <a href="{{ $imageUrl }}" download
            class="inline-block px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
            Download
        </a>
    </div>

    <p class="text-xs text-gray-500">Klik "Perbesar" untuk melihat gambar lebih jelas di tab baru.</p>
</div>
