<?php

namespace App\Http\Controllers;

use App\Http\Requests\Return\StoreReturnRequest;
use App\Models\Loan;
use App\Models\ReturnBook;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(Request $request): View
    {
        $query = ReturnBook::with(['loan.user', 'loan.loanDetails.copy.book']);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('loan.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%");
            });
        }

        $returns = $query->latest('return_date')->paginate(15)->withQueryString();

        return view('returns.index', compact('returns'));
    }

    public function create(Loan $loan): View|RedirectResponse
    {
        if ($loan->status === 'dikembalikan') {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Buku pada transaksi ini sudah dikembalikan sebelumnya.');
        }

        $loan->load(['user', 'loanDetails.copy.book']);

        $returnDate = now()->startOfDay();
        $dueDate = Carbon::parse($loan->due_date)->startOfDay();

        $lateDays = 0;
        if ($returnDate->greaterThan($dueDate)) {
            $lateDays = (int) $returnDate->diffInDays($dueDate, true);
        }

        $finePerDay = (float) SystemSetting::get('fine_per_day', 2000);
        $estimatedFine = $lateDays * $finePerDay;

        return view('returns.create', compact('loan', 'lateDays', 'finePerDay', 'estimatedFine'));
    }

    public function store(StoreReturnRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $loan = Loan::with('loanDetails.copy')->findOrFail($validated['loan_id']);

        if ($loan->status === 'dikembalikan') {
            return back()->with('error', 'Transaksi ini sudah selesai dan buku telah dikembalikan.');
        }

        try {
            DB::transaction(function () use ($loan, $validated) {
                $returnDate = now()->startOfDay();
                $dueDate = Carbon::parse($loan->due_date)->startOfDay();

                $lateDays = 0;
                if ($returnDate->greaterThan($dueDate)) {
                    $lateDays = (int) $returnDate->diffInDays($dueDate, true);
                }

                $finePerDay = (float) SystemSetting::get('fine_per_day', 2000);
                $penaltyFee = $lateDays * $finePerDay;

                $paymentStatus = 'tanpa_denda';
                if ($penaltyFee > 0) {
                    $paymentStatus = $validated['payment_status'] ?? 'belum_bayar';
                }

                ReturnBook::create([
                    'loan_id' => $loan->id,
                    'return_date' => $returnDate->toDateString(),
                    'late_days' => $lateDays,
                    'penalty_fee' => $penaltyFee,
                    'payment_status' => $paymentStatus,
                ]);

                $loan->update([
                    'status' => 'dikembalikan',
                ]);

                foreach ($loan->loanDetails as $detail) {
                    if ($detail->copy) {
                        $detail->copy->update([
                            'is_available' => true,
                        ]);
                    }
                }
            });

            return redirect()->route('loans.show', $loan)
                ->with('success', 'Pengembalian buku berhasil diproses dan eksemplar telah tersedia kembali.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses pengembalian: '.$e->getMessage());
        }
    }

    public function payPenalty(Request $request, ReturnBook $returnBook): RedirectResponse
    {
        if ($returnBook->payment_status === 'lunas') {
            return back()->with('error', 'Denda ini sudah lunas tercatat di sistem.');
        }

        $returnBook->update([
            'payment_status' => 'lunas',
        ]);

        return back()->with('success', 'Pembayaran denda sebesar Rp '.number_format($returnBook->penalty_fee, 0, ',', '.').' berhasil dicatat.');
    }
}
