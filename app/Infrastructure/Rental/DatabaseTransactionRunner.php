<?php

namespace App\Infrastructure\Rental;

use App\Application\Rental\Contracts\TransactionRunner;
use App\Application\Rental\TransactionConflict;
use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DetectsConcurrencyErrors;
use Illuminate\Database\QueryException;

/**
 * Transaksi database dengan retry terbatas untuk deadlock/lock wait timeout.
 */
final class DatabaseTransactionRunner implements TransactionRunner
{
    use DetectsConcurrencyErrors;

    private const ATTEMPTS = 3;

    public function __construct(private readonly ConnectionInterface $connection) {}

    public function run(Closure $work): mixed
    {
        try {
            return $this->connection->transaction($work, self::ATTEMPTS);
        } catch (QueryException $exception) {
            if ($this->causedByConcurrencyError($exception)) {
                throw TransactionConflict::afterRetries();
            }

            throw $exception;
        }
    }
}
