<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ELibraryController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('books.index');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/catalog', [BookController::class, 'index'])->name('books.index');
Route::get('/catalog/{book}', [BookController::class, 'show'])->name('books.show');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile/card', [AuthController::class, 'memberCard'])->name('profile.card');

    Route::get('/elibrary', [ELibraryController::class, 'index'])->name('elibrary.index');
    Route::get('/elibrary/history', [ELibraryController::class, 'history'])->name('elibrary.history');
    Route::get('/elibrary/{ebook}/read', [ELibraryController::class, 'read'])->name('elibrary.read');
    Route::get('/elibrary/{ebook}/download', [ELibraryController::class, 'download'])->name('elibrary.download');

    Route::middleware('role:Anggota')->group(function () {
        Route::get('/my-loans', [LoanController::class, 'index'])->name('member.loans');
        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    });

    Route::middleware('role:Admin Pegawai|Super Admin')->group(function () {
        Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
        Route::post('/books', [BookController::class, 'store'])->name('books.store');
        Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
        Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
        Route::post('/books/{book}/copies', [BookController::class, 'storeCopy'])->name('books.copies.store');
        Route::put('/copies/{copy}', [BookController::class, 'updateCopy'])->name('books.copies.update');
        Route::delete('/copies/{copy}', [BookController::class, 'destroyCopy'])->name('books.copies.destroy');

        Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
        Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
        Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
        Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
        Route::get('/loans/{loan}/slip', [LoanController::class, 'slip'])->name('loans.slip');

        Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
        Route::get('/returns/{loan}/create', [ReturnController::class, 'create'])->name('returns.create');
        Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
        Route::post('/returns/{returnBook}/pay', [ReturnController::class, 'payPenalty'])->name('returns.pay');

        Route::get('/admin/reservations', [ReservationController::class, 'index'])->name('admin.reservations.index');
        Route::post('/admin/reservations/{reservation}/taken', [ReservationController::class, 'markAsTaken'])->name('reservations.taken');
        Route::delete('/admin/reservations/{reservation}', [ReservationController::class, 'cancel'])->name('admin.reservations.cancel');

        Route::post('/elibrary', [ELibraryController::class, 'store'])->name('elibrary.store');
        Route::delete('/elibrary/{ebook}', [ELibraryController::class, 'destroy'])->name('elibrary.destroy');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/loans', [ReportController::class, 'loans'])->name('reports.loans');
        Route::get('/reports/returns', [ReportController::class, 'returns'])->name('reports.returns');
        Route::get('/reports/books', [ReportController::class, 'books'])->name('reports.books');
        Route::get('/reports/members', [ReportController::class, 'members'])->name('reports.members');
    });

    Route::middleware('role:Super Admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
