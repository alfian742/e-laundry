<?php

use Carbon\Carbon;

// Helper untuk memformat tanggal menggunakan Carbon lokal Indonesia
if (!function_exists('carbon_format_date')) {

    /**
     * Format yang tersedia:
     * - 'default'     : Menampilkan tanggal penuh (contoh: 25 April 2024)
     * - 'datetime'    : Menampilkan tanggal dan waktu (contoh: 25 April 2024 - 14:30)
     * - 'month_year'  : Menampilkan bulan dan tahun saja (contoh: April 2024)
     * - 'day'         : Menampilkan hari saja (contoh: Senin)
     * - 'time'        : Menampilkan jam dan menit saja (contoh: 14:30)
     * - 'human'       : Menampilkan waktu relatif (contoh: 2 hari yang lalu)
     *
     * @param string|\DateTime|null $date   Tanggal yang ingin diformat.
     * @param string $format                Format yang diinginkan.
     * @return string|null                  Tanggal yang sudah diformat, atau null jika tidak valid.
     */
    function carbon_format_date($date = null, $format = 'default')
    {
        if (!$date) return null;

        try {
            $carbonDate = Carbon::parse($date)->locale('id');

            switch ($format) {
                case 'datetime':
                    return $carbonDate->isoFormat('D MMMM YYYY - HH:mm');
                case 'month_year':
                    return $carbonDate->isoFormat('MMMM YYYY');
                case 'day':
                    return $carbonDate->isoFormat('dddd');
                case 'time':
                    return $carbonDate->isoFormat('HH:mm');
                case 'human':
                    return $carbonDate->diffForHumans();
                case 'default':
                default:
                    return $carbonDate->isoFormat('D MMMM YYYY');
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}


// Helper untuk mengambil URL gambar (dengan path yang fleksibel)
if (!function_exists('get_image_url')) {

    /**
     * Mendapatkan URL gambar dari lokasi yang terbatas dan spesifik.
     *
     * @param string|null $image
     * @param string $defaultImage
     * @return string
     */
    function get_image_url($image = null, $defaultImage = 'example-image.jpg')
    {
        if (!$image) {
            return asset("img/static/{$defaultImage}");
        }

        $paths = [
            "img/uploads/payment_methods/{$image}",
            "img/uploads/proofs/{$image}",
            "img/uploads/services/{$image}",
            "img/uploads/site/{$image}",
        ];

        foreach ($paths as $path) {
            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }

        return asset("img/static/{$defaultImage}");
    }
}

// Format mata uang rupiah
if (!function_exists('formatRupiah')) {
    function formatRupiah($amount)
    {
        $amount = $amount ?? 0;
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

// Format mata uang rupiah tanpa (Rp)
if (!function_exists('formatRupiahPlain')) {
    function formatRupiahPlain($amount)
    {
        $amount = $amount ?? 0;
        return number_format($amount, 0, ',', '');
    }
}

// Fromat nomor hp
if (!function_exists('formatPhoneNumber')) {
    function formatPhoneNumber($phone_number)
    {
        $phone_number = $phone_number ?? 'N/A';
        return preg_replace('/^08/', '+628', $phone_number);
    }
}
