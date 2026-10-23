<?php

namespace App\Http\Requests\Admin;

use App\Domain\Rental\RentalStatus;
use App\Models\Rental;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filter daftar dan cetak transaksi admin: anggota, status (termasuk turunan "terlambat"),
 * dan rentang tanggal dibuat dalam WIB.
 */
class RentalFilterRequest extends FormRequest
{
    public const STATUS_OVERDUE = 'overdue';

    /**
     * Kembali ke daftar tanpa filter bila parameter tidak valid, agar tidak terjadi redirect berulang.
     *
     * @var string
     */
    protected $redirectRoute = 'admin.rentals.index';

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'member' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_MEMBER)],
            'status' => ['nullable', Rule::in(array_keys(self::statusOptions()))],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'member' => 'anggota',
            'status' => 'status',
            'from' => 'tanggal awal',
            'to' => 'tanggal akhir',
        ];
    }

    /**
     * Pilihan filter status; "active" ikut mencakup pesanan aktif yang terlambat.
     *
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        $options = [];
        foreach (RentalStatus::cases() as $status) {
            $options[$status->value] = $status->label();
            if ($status === RentalStatus::Active) {
                $options[self::STATUS_OVERDUE] = 'Terlambat';
            }
        }

        return $options;
    }

    /**
     * @return array{member: ?int, status: ?string, from: ?string, to: ?string}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'member' => isset($validated['member']) ? (int) $validated['member'] : null,
            'status' => $validated['status'] ?? null,
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
        ];
    }

    /**
     * Parameter query yang terisi, untuk tautan cetak dan paginasi.
     *
     * @return array<string, int|string>
     */
    public function queryParameters(): array
    {
        return array_filter($this->filters(), fn (int|string|null $value): bool => $value !== null && $value !== '');
    }

    /**
     * @return Builder<Rental>
     */
    public function rentalQuery(): Builder
    {
        $filters = $this->filters();
        $timezone = config('gtrent.display_timezone');

        return Rental::query()
            ->with(['user', 'items'])
            ->when($filters['member'], fn (Builder $query, int $member) => $query->where('user_id', $member))
            ->when($filters['status'], function (Builder $query, string $status): void {
                if ($status === self::STATUS_OVERDUE) {
                    $query->overdue();
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($filters['from'], fn (Builder $query, string $from) => $query->where(
                'created_at',
                '>=',
                CarbonImmutable::createFromFormat('Y-m-d', $from, $timezone)->startOfDay()->utc(),
            ))
            ->when($filters['to'], fn (Builder $query, string $to) => $query->where(
                'created_at',
                '<=',
                CarbonImmutable::createFromFormat('Y-m-d', $to, $timezone)->endOfDay()->utc(),
            ))
            ->latest('id');
    }

    public function memberLabel(): string
    {
        $member = $this->filters()['member'];

        return $member === null ? 'Semua anggota' : (string) User::withTrashed()->whereKey($member)->value('name');
    }

    public function statusLabel(): string
    {
        $status = $this->filters()['status'];

        return $status === null ? 'Semua' : self::statusOptions()[$status];
    }

    public function periodLabel(): string
    {
        ['from' => $from, 'to' => $to] = $this->filters();
        $format = fn (string $date): string => CarbonImmutable::createFromFormat('Y-m-d', $date)->locale('id')->translatedFormat('d M Y');

        return match (true) {
            $from !== null && $to !== null => $format($from).' – '.$format($to),
            $from !== null => 'Sejak '.$format($from),
            $to !== null => 'Sampai '.$format($to),
            default => 'Semua tanggal',
        };
    }
}
