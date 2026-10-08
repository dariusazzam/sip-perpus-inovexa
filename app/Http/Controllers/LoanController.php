<?php

namespace App\Http\Controllers;

use App\Http\Requests\Loan\StoreLoanRequest;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\Reservation;
use App\Models\SystemSetting;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $query = Loan::with(['user', 'admin', 'loanDetails.copy.book', 'returnRecord']);

        if ($currentUser->isMember()) {
            $query->where('user_id', $currentUser->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($sub) => $sub->where('name', 'like', "%{$search}%")->orWhere('member_number', 'like', "%{$search}%"))
                    ->orWhereHas('loanDetails.copy.book', fn ($sub) => $sub->where('title', 'like', "%{$search}%"));
            });
        }

        $loans = $query->latest('borrow_date')->paginate(15)->withQueryString();

        return view('loans.index', compact('loans'));
    }

    public function create(): View
    {
        $members = User::whereHas('role', fn ($q) => $q->where('role_name', 'Anggota'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $availableCopies = BookCopy::with('book')
            ->where('is_available', true)
            ->where('condition_status', 'baik')
            ->get();

        $maxBooksBorrowed = (int) SystemSetting::get('max_books_borrowed', 3);
        $maxBorrowDays = (int) SystemSetting::get('max_borrow_days', 7);

        return view('loans.create', compact('members', 'availableCopies', 'maxBooksBorrowed', 'maxBorrowDays'));
    }

    public function store(StoreLoanRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $borrower = User::findOrFail($validated['user_id']);

        if (! $borrower->isActive()) {
            return back()->withInput()->with('error', 'Peminjaman gagal: Status anggota sedang dinonaktifkan/disuspend.');
        }

        $maxBooksAllowed = (int) SystemSetting::get('max_books_borrowed', 3);
        $currentActiveBorrowedCount = LoanDetail::whereHas('loan', function ($q) use ($borrower) {
            $q->where('user_id', $borrower->id)->where('status', 'dipinjam');
        })->count();

        $newBooksCount = count($validated['copy_ids']);

        if (($currentActiveBorrowedCount + $newBooksCount) > $maxBooksAllowed) {
            return back()->withInput()->with(
                'error',
                "Peminjaman gagal: Anggota telah meminjam {$currentActiveBorrowedCount} eksemplar. Batas maksimal peminjaman adalah {$maxBooksAllowed} eksemplar."
            );
        }

        try {
            $loan = DB::transaction(function () use ($borrower, $validated) {
                /** @var Collection<int, BookCopy> $copies */
                $copies = BookCopy::whereIn('id', $validated['copy_ids'])
                    ->lockForUpdate()
                    ->get();

                if ($copies->count() !== count($validated['copy_ids'])) {
                    throw new Exception('Satu atau lebih eksemplar buku tidak valid.');
                }

                foreach ($copies as $copy) {
                    if (! $copy->is_available || $copy->condition_status !== 'baik') {
                        throw new Exception("Eksemplar dengan kode {$copy->inventory_code} tidak tersedia untuk dipinjam.");
                    }
                }

                $maxBorrowDays = (int) SystemSetting::get('max_borrow_days', 7);
                $borrowDate = now()->toDateString();
                $dueDate = now()->addDays($maxBorrowDays)->toDateString();

                $loan = Loan::create([
                    'user_id' => $borrower->id,
                    'admin_id' => Auth::id(),
                    'borrow_date' => $borrowDate,
                    'due_date' => $dueDate,
                    'status' => 'dipinjam',
                ]);

                foreach ($copies as $copy) {
                    LoanDetail::create([
                        'loan_id' => $loan->id,
                        'copy_id' => $copy->id,
                    ]);

                    $copy->update([
                        'is_available' => false,
                    ]);

                    Reservation::where('user_id', $borrower->id)
                        ->where('book_id', $copy->book_id)
                        ->where('status', 'pending')
                        ->update(['status' => 'diambil']);
                }

                return $loan;
            });

            return redirect()->route('loans.show', $loan)
                ->with('success', 'Transaksi peminjaman berhasil diproses dan disimpan ke sistem.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Peminjaman gagal: '.$e->getMessage());
        }
    }

    public function show(Loan $loan): View
    {
        $loan->load(['user.role', 'admin', 'loanDetails.copy.book.category', 'returnRecord']);

        return view('loans.show', compact('loan'));
    }

    public function slip(Loan $loan): View
    {
        $loan->load(['user', 'admin', 'loanDetails.copy.book']);

        return view('loans.slip', compact('loan'));
    }
}
