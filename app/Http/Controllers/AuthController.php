<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))
                ->with('error', 'Kombinasi email dan password tidak sesuai.');
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi pengelola perpustakaan.');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang kembali, '.$user->name.'.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    public function dashboard(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $stats = [
            'total_books' => Book::count(),
            'active_loans' => Loan::where('status', 'dipinjam')->count(),
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
            'total_members' => User::whereHas('role', fn ($q) => $q->where('role_name', 'Anggota'))->count(),
        ];

        $memberStats = [];
        if ($user->isMember()) {
            $memberStats = [
                'my_loans_count' => Loan::where('user_id', $user->id)->where('status', 'dipinjam')->count(),
                'my_reservations_count' => Reservation::where('user_id', $user->id)->where('status', 'pending')->count(),
                'my_unpaid_fines' => Loan::where('user_id', $user->id)
                    ->whereHas('returnRecord', fn ($q) => $q->where('payment_status', 'belum_bayar'))
                    ->with('returnRecord')
                    ->get()
                    ->sum(fn ($l) => $l->returnRecord?->penalty_fee ?? 0),
            ];
        }

        return view('dashboard', compact('user', 'stats', 'memberStats'));
    }

    public function profile(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('profile.index', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('profile')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }

    public function memberCard(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isMember() || empty($user->member_number)) {
            return redirect()->route('dashboard')
                ->with('error', 'Kartu anggota hanya tersedia untuk akun anggota terdaftar.');
        }

        return view('profile.card', compact('user'));
    }
}
