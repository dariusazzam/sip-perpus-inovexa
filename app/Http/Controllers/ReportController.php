<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\ReturnBook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_books' => Book::count(),
            'total_copies' => BookCopy::count(),
            'available_copies' => BookCopy::where('is_available', true)->where('condition_status', 'baik')->count(),
            'damaged_copies' => BookCopy::where('condition_status', 'rusak')->count(),
            'lost_copies' => BookCopy::where('condition_status', 'hilang')->count(),
            'total_loans' => Loan::count(),
            'active_loans' => Loan::where('status', 'dipinjam')->count(),
            'completed_loans' => Loan::where('status', 'dikembalikan')->count(),
            'total_fines_collected' => ReturnBook::where('payment_status', 'lunas')->sum('penalty_fee'),
            'total_fines_unpaid' => ReturnBook::where('payment_status', 'belum_bayar')->sum('penalty_fee'),
            'total_members' => User::whereHas('role', fn ($q) => $q->where('role_name', 'Anggota'))->count(),
        ];

        return view('reports.index', compact('stats'));
    }

    public function loans(Request $request): View
    {
        $query = Loan::with(['user', 'admin', 'loanDetails.copy.book']);

        if ($request->filled('start_date')) {
            $query->whereDate('borrow_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('borrow_date', '<=', $request->input('end_date'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $isPrint = $request->boolean('print');
        $loans = $isPrint ? $query->latest('borrow_date')->get() : $query->latest('borrow_date')->paginate(20)->withQueryString();

        return view('reports.loans', compact('loans', 'isPrint'));
    }

    public function returns(Request $request): View
    {
        $query = ReturnBook::with(['loan.user', 'loan.loanDetails.copy.book']);

        if ($request->filled('start_date')) {
            $query->whereDate('return_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('return_date', '<=', $request->input('end_date'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        $isPrint = $request->boolean('print');
        $returns = $isPrint ? $query->latest('return_date')->get() : $query->latest('return_date')->paginate(20)->withQueryString();

        $totalPenalties = $query->sum('penalty_fee');

        return view('reports.returns', compact('returns', 'totalPenalties', 'isPrint'));
    }

    public function books(Request $request): View
    {
        $query = BookCopy::with('book.category');

        if ($request->filled('condition_status')) {
            $query->where('condition_status', $request->input('condition_status'));
        }

        if ($request->filled('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        $isPrint = $request->boolean('print');
        $copies = $isPrint ? $query->orderBy('inventory_code')->get() : $query->orderBy('inventory_code')->paginate(25)->withQueryString();

        return view('reports.books', compact('copies', 'isPrint'));
    }

    public function members(Request $request): View
    {
        $query = User::with('role')->withCount([
            'loans as active_loans_count' => fn ($q) => $q->where('status', 'dipinjam'),
            'loans as total_loans_count',
        ]);

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        } else {
            $query->whereHas('role', fn ($q) => $q->where('role_name', 'Anggota'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $isPrint = $request->boolean('print');
        $members = $isPrint ? $query->orderBy('name')->get() : $query->orderBy('name')->paginate(20)->withQueryString();

        return view('reports.members', compact('members', 'isPrint'));
    }
}
