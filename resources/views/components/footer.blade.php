<footer class="bg-body-tertiary border-top py-4 mt-auto">
    <div class="container text-center">
        {{-- Menambahkan elemen dekoratif tipis di atas teks --}}
        <div class="mb-2 mx-auto" style="width: 40px; height: 3px; background-color: #40BF89; border-radius: 10px; opacity: 0.5;"></div>

        <p class="mb-0 text-body-secondary small fw-medium">
            &copy; {{ date('Y') }} Balai Kekarantinaan Kesehatan (BKK).
        </p>
        <p class="mb-0 text-body-tertiary" style="font-size: 0.75rem;">
            Aplikasi Absensi WFA - Developed by Ardyan.
        </p>
    </div>
</footer>

<style>
    /* Menambah transisi halus saat pindah mode */
    footer {
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    /* Memastikan garis border tidak terlalu kontras di mode gelap */
    [data-bs-theme="dark"] footer {
        border-top-color: rgba(255, 255, 255, 0.1) !important;
    }
</style>
