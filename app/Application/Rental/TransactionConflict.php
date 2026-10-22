<?php

namespace App\Application\Rental;

use RuntimeException;

/**
 * Transaksi gagal karena konflik penguncian setelah retry terbatas. Pesannya aman ditampilkan.
 */
class TransactionConflict extends RuntimeException
{
    public static function afterRetries(): self
    {
        return new self('Sistem sedang memproses permintaan lain untuk data yang sama. Tidak ada perubahan yang disimpan; silakan coba lagi.');
    }
}
