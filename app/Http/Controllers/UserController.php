<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('role');

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::orderBy('role_name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->isSuperAdmin()) {
            $roles = Role::orderBy('role_name')->get();
        } else {
            $roles = Role::where('role_name', 'Anggota')->get();
        }

        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $role = Role::findOrFail($validated['role_id']);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->isSuperAdmin() && strtolower($role->role_name) !== 'anggota') {
            abort(403, 'Anda hanya memiliki akses untuk mendaftarkan akun Anggota.');
        }

        if (strtolower($role->role_name) === 'anggota' && empty($validated['member_number'])) {
            $lastId = User::max('id') ?? 0;
            $validated['member_number'] = 'MBR-'.date('Y').'-'.str_pad((string) ($lastId + 1), 4, '0', STR_PAD_LEFT);
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna baru berhasil didaftarkan.');
    }

    public function edit(User $user): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->isSuperAdmin() && ! $user->isMember()) {
            abort(403);
        }

        if ($currentUser->isSuperAdmin()) {
            $roles = Role::orderBy('role_name')->get();
        } else {
            $roles = Role::where('role_name', 'Anggota')->get();
        }

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $validated = $request->validated();
        $role = Role::findOrFail($validated['role_id']);

        if (! $currentUser->isSuperAdmin() && strtolower($role->role_name) !== 'anggota') {
            abort(403);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'Data akun pengguna berhasil diperbarui.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        }

        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        $message = $newStatus === 'active'
            ? 'Akun pengguna berhasil diaktifkan kembali.'
            : 'Akun pengguna berhasil dinonaktifkan (disuspend).';

        return back()->with('success', $message);
    }
}
