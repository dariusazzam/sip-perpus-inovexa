<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Models\Book;
use App\Models\Reservation;
use App\Models\ReturnBook;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $query = Reservation::with(['user', 'book.category']);

        if ($currentUser->isMember()) {
            $query->where('user_id', $currentUser->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->input('book_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $reservations = $query->latest()->paginate(15)->withQueryString();

        return view('reservations.index', compact('reservations'));
    }

    public function store(StoreReservationRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $book = Book::findOrFail($request->validated('book_id'));

        $availableCopiesCount = $book->availableCopies()->count();
        if ($availableCopiesCount > 0) {
            return back()->with(
                'error',
                'Reservasi ditolak: Masih terdapat '.$availableCopiesCount.' eksemplar buku yang tersedia di perpustakaan. Silakan langsung melakukan peminjaman.'
            );
        }

        $hasUnpaidFines = ReturnBook::whereHas('loan', fn ($q) => $q->where('user_id', $user->id))
            ->where('payment_status', 'belum_bayar')
            ->exists();

        if ($hasUnpaidFines) {
            return back()->with(
                'error',
                'Reservasi ditolak: Anda memiliki catatan denda tertunggak yang belum diselesaikan. Harap melunasi denda terlebih dahulu.'
            );
        }

        $reservation = DB::transaction(function () use ($user, $book) {
            // Lock book row to serialize queue number generation for this book
            Book::where('id', $book->id)->lockForUpdate()->first();

            $existingPending = Reservation::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if ($existingPending) {
                throw new \RuntimeException(
                    'Anda sudah memiliki antrean reservasi aktif untuk buku ini dengan nomor antrean #'.$existingPending->queue_number.'.'
                );
            }

            $currentMaxQueue = Reservation::where('book_id', $book->id)
                ->where('status', 'pending')
                ->max('queue_number') ?? 0;

            $nextQueueNumber = $currentMaxQueue + 1;
            $expiryHours = (int) SystemSetting::get('reservation_expiry_hours', 24);
            $expirationDate = now()->addHours($expiryHours);

            return Reservation::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'queue_number' => $nextQueueNumber,
                'expiration_date' => $expirationDate,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('reservations.index')
            ->with('success', 'Reservasi berhasil didaftarkan. Anda berada pada nomor antrean #'.$reservation->queue_number.'.');
    }

    public function cancel(Reservation $reservation): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isMember() && $reservation->user_id !== $user->id) {
            abort(403);
        }

        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Hanya reservasi yang berstatus pending yang dapat dibatalkan.');
        }

        $reservation->delete();

        return back()->with('success', 'Antrean reservasi berhasil dibatalkan.');
    }

    public function markAsTaken(Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Status reservasi sudah tidak aktif.');
        }

        $reservation->update([
            'status' => 'diambil',
        ]);

        return back()->with('success', 'Status reservasi buku berhasil diubah menjadi diambil.');
    }
}
