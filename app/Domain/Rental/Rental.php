<?php

namespace App\Domain\Rental;

use DateTimeImmutable;

/**
 * Entity pesanan sewa. Semua perubahan status melewati method di sini agar transisi,
 * urutan waktu, tenggat, dan denda selalu mengikuti aturan PRD.
 */
final class Rental
{
    private ?PaymentMethod $paymentMethod = null;

    private ?int $paymentConfirmedBy = null;

    private ?DateTimeImmutable $paymentConfirmedAt = null;

    private ?DateTimeImmutable $dispatchedAt = null;

    private ?int $dispatchedBy = null;

    private ?DateTimeImmutable $receivedAt = null;

    private ?DateTimeImmutable $receiptRecordedAt = null;

    private ?int $receiptRecordedBy = null;

    private ?DateTimeImmutable $dueAt = null;

    private ?DateTimeImmutable $returnedAt = null;

    private ?DateTimeImmutable $returnRecordedAt = null;

    private ?int $returnedBy = null;

    private ?string $returnNotes = null;

    private ?DateTimeImmutable $cancelledAt = null;

    private ?int $cancelledBy = null;

    private ?string $cancelReason = null;

    /**
     * @param  list<RentalLine>  $lines
     */
    private function __construct(
        public readonly ?int $id,
        public readonly int $memberId,
        private RentalStatus $status,
        public readonly array $lines,
        public readonly int $shippingFee,
    ) {}

    /**
     * Pesanan baru hasil checkout yang valid, berstatus menunggu pembayaran.
     *
     * @param  list<RentalLine>  $lines
     */
    public static function open(int $memberId, array $lines, int $shippingFee): self
    {
        return new self(null, $memberId, RentalStatus::PendingPayment, $lines, $shippingFee);
    }

    /**
     * Membangun ulang entity dari penyimpanan.
     *
     * @param  list<RentalLine>  $lines
     * @param  array<string, mixed>  $state
     */
    public static function restore(int $id, int $memberId, RentalStatus $status, array $lines, int $shippingFee, array $state): self
    {
        $rental = new self($id, $memberId, $status, $lines, $shippingFee);

        foreach ($state as $property => $value) {
            if (property_exists($rental, $property)) {
                $rental->{$property} = $value;
            }
        }

        return $rental;
    }

    public function subtotal(): int
    {
        return array_sum(array_map(fn (RentalLine $line): int => $line->packagePrice, $this->lines));
    }

    public function initialTotal(): int
    {
        return $this->subtotal() + $this->shippingFee;
    }

    public function fineTotal(): int
    {
        return array_sum(array_map(fn (RentalLine $line): int => $line->fineAmount(), $this->lines));
    }

    public function confirmPayment(PaymentMethod $method, int $adminId, DateTimeImmutable $now): void
    {
        $this->guardTransition(RentalStatus::Paid, 'mengonfirmasi pembayaran');

        $this->paymentMethod = $method;
        $this->paymentConfirmedBy = $adminId;
        $this->paymentConfirmedAt = $now;
        $this->status = RentalStatus::Paid;
    }

    public function cancel(string $reason, int $adminId, DateTimeImmutable $now): void
    {
        $this->guardTransition(RentalStatus::Cancelled, 'membatalkan pesanan');

        $reason = trim($reason);
        $length = mb_strlen($reason);
        if ($length < 3 || $length > 255) {
            throw new RentalRuleViolation('Alasan pembatalan wajib diisi 3-255 karakter.');
        }

        $this->cancelReason = $reason;
        $this->cancelledBy = $adminId;
        $this->cancelledAt = $now;
        $this->status = RentalStatus::Cancelled;
    }

    public function dispatch(DateTimeImmutable $dispatchedAt, int $adminId, DateTimeImmutable $now): void
    {
        $this->guardTransition(RentalStatus::InDelivery, 'mencatat pengiriman');
        $this->guardEventTime($dispatchedAt, $this->paymentConfirmedAt, $now, 'Waktu keberangkatan', 'waktu konfirmasi pembayaran');

        $this->dispatchedAt = $dispatchedAt;
        $this->dispatchedBy = $adminId;
        $this->status = RentalStatus::InDelivery;
    }

    public function recordReceipt(DateTimeImmutable $receivedAt, int $adminId, DateTimeImmutable $now): void
    {
        $this->guardTransition(RentalStatus::Active, 'mencatat penerimaan penyewa');
        $this->guardEventTime($receivedAt, $this->dispatchedAt, $now, 'Waktu diterima penyewa', 'waktu keberangkatan');

        $this->receivedAt = $receivedAt;
        $this->receiptRecordedAt = $now;
        $this->receiptRecordedBy = $adminId;
        $this->dueAt = RentalPeriod::dueAt($receivedAt);
        $this->status = RentalStatus::Active;
    }

    public function recordReturn(DateTimeImmutable $returnedAt, int $adminId, DateTimeImmutable $now, ?string $notes): void
    {
        $this->guardTransition(RentalStatus::Returned, 'mencatat pengembalian');
        $this->guardEventTime($returnedAt, $this->receivedAt, $now, 'Waktu diterima GTR', 'waktu diterima penyewa');

        $lateDays = RentalPeriod::lateDays($this->dueAt, $returnedAt);
        foreach ($this->lines as $line) {
            $line->applyLateDays($lateDays);
        }

        $notes = $notes === null ? null : trim($notes);
        $this->returnNotes = $notes === '' ? null : $notes;
        $this->returnedAt = $returnedAt;
        $this->returnRecordedAt = $now;
        $this->returnedBy = $adminId;
        $this->status = RentalStatus::Returned;
    }

    public function status(): RentalStatus
    {
        return $this->status;
    }

    public function isOverdue(DateTimeImmutable $now): bool
    {
        return RentalPeriod::isOverdue($this->status, $this->dueAt, $now);
    }

    /**
     * Nilai yang perlu disimpan kembali oleh repository.
     *
     * @return array<string, mixed>
     */
    public function state(): array
    {
        return [
            'paymentMethod' => $this->paymentMethod,
            'paymentConfirmedBy' => $this->paymentConfirmedBy,
            'paymentConfirmedAt' => $this->paymentConfirmedAt,
            'dispatchedAt' => $this->dispatchedAt,
            'dispatchedBy' => $this->dispatchedBy,
            'receivedAt' => $this->receivedAt,
            'receiptRecordedAt' => $this->receiptRecordedAt,
            'receiptRecordedBy' => $this->receiptRecordedBy,
            'dueAt' => $this->dueAt,
            'returnedAt' => $this->returnedAt,
            'returnRecordedAt' => $this->returnRecordedAt,
            'returnedBy' => $this->returnedBy,
            'returnNotes' => $this->returnNotes,
            'cancelledAt' => $this->cancelledAt,
            'cancelledBy' => $this->cancelledBy,
            'cancelReason' => $this->cancelReason,
        ];
    }

    private function guardTransition(RentalStatus $next, string $action): void
    {
        if (! $this->status->canTransitionTo($next)) {
            throw RentalRuleViolation::invalidTransition($this->status, $action);
        }
    }

    private function guardEventTime(
        DateTimeImmutable $at,
        ?DateTimeImmutable $notBefore,
        DateTimeImmutable $now,
        string $label,
        string $previousLabel,
    ): void {
        if ($at > $now) {
            throw new RentalRuleViolation("{$label} tidak boleh di masa depan.");
        }

        if ($notBefore !== null && $at < $notBefore) {
            throw new RentalRuleViolation("{$label} tidak boleh sebelum {$previousLabel}.");
        }
    }
}
