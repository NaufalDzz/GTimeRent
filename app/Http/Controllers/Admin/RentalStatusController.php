<?php

namespace App\Http\Controllers\Admin;

use App\Application\Rental\CancelRental;
use App\Application\Rental\ConfirmPayment;
use App\Application\Rental\Data\Actor;
use App\Application\Rental\DispatchRental;
use App\Application\Rental\RecordReceipt;
use App\Application\Rental\RecordReturn;
use App\Application\Rental\TransactionConflict;
use App\Domain\Rental\RentalRuleViolation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancelRentalRequest;
use App\Http\Requests\Admin\ConfirmPaymentRequest;
use App\Http\Requests\Admin\DispatchRentalRequest;
use App\Http\Requests\Admin\RecordReceiptRequest;
use App\Http\Requests\Admin\RecordReturnRequest;
use App\Models\Rental;
use App\Support\Format;
use Closure;
use Illuminate\Http\RedirectResponse;

/**
 * Aksi status pesanan oleh admin. Aturan transisi dan waktu dijaga use case/domain.
 */
class RentalStatusController extends Controller
{
    public function confirm(ConfirmPaymentRequest $request, Rental $rental, ConfirmPayment $confirmPayment): RedirectResponse
    {
        $method = $request->paymentMethod();

        return $this->run($rental, fn (Actor $actor) => $confirmPayment->handle($actor, $rental->id, $method),
            fn (): string => "Pembayaran {$rental->number} dikonfirmasi lunas ({$method->label()}).");
    }

    public function cancel(CancelRentalRequest $request, Rental $rental, CancelRental $cancelRental): RedirectResponse
    {
        return $this->run($rental, fn (Actor $actor) => $cancelRental->handle($actor, $rental->id, $request->validated('reason')),
            fn (): string => "Pesanan {$rental->number} dibatalkan dan unit dilepas.");
    }

    public function dispatch(DispatchRentalRequest $request, Rental $rental, DispatchRental $dispatchRental): RedirectResponse
    {
        $dispatchedAt = Format::fromWibInput($request->validated('dispatched_at'))->toDateTimeImmutable();

        return $this->run($rental, fn (Actor $actor) => $dispatchRental->handle($actor, $rental->id, $dispatchedAt),
            fn (): string => "Keberangkatan kurir untuk {$rental->number} dicatat. Status: Dikirim.");
    }

    public function receive(RecordReceiptRequest $request, Rental $rental, RecordReceipt $recordReceipt): RedirectResponse
    {
        $receivedAt = Format::fromWibInput($request->validated('received_at'))->toDateTimeImmutable();

        return $this->run($rental, fn (Actor $actor) => $recordReceipt->handle($actor, $rental->id, $receivedAt),
            fn (): string => 'Penerimaan penyewa dicatat. Jatuh tempo '.Format::wib($rental->refresh()->due_at).'.');
    }

    public function return(RecordReturnRequest $request, Rental $rental, RecordReturn $recordReturn): RedirectResponse
    {
        $returnedAt = Format::fromWibInput($request->validated('returned_at'))->toDateTimeImmutable();

        return $this->run($rental, fn (Actor $actor) => $recordReturn->handle($actor, $rental->id, $returnedAt, $request->validated('notes')),
            fn (): string => 'Pengembalian dicatat. Denda final '.Format::rupiah($rental->load('items')->fineTotal()).'.');
    }

    /**
     * @param  Closure(Actor): mixed  $action
     * @param  Closure(): string  $successMessage
     */
    private function run(Rental $rental, Closure $action, Closure $successMessage): RedirectResponse
    {
        try {
            $action(new Actor((int) auth()->id(), true));
        } catch (RentalRuleViolation|TransactionConflict $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.rentals.show', $rental)->with('status', $successMessage());
    }
}
