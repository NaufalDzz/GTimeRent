<?php

namespace App\Application\Rental\Contracts;

use App\Application\Rental\TransactionConflict;
use Closure;

interface TransactionRunner
{
    /**
     * Menjalankan pekerjaan dalam satu transaksi; rollback bila gagal.
     *
     * @template T
     *
     * @param  Closure(): T  $work
     * @return T
     *
     * @throws TransactionConflict bila konflik penguncian tetap terjadi setelah retry terbatas
     */
    public function run(Closure $work): mixed;
}
