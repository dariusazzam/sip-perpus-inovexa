<?php

namespace App\Http\Controllers;

use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'fine_per_day' => (float) SystemSetting::get('fine_per_day', 2000),
            'max_borrow_days' => (int) SystemSetting::get('max_borrow_days', 7),
            'max_books_borrowed' => (int) SystemSetting::get('max_books_borrowed', 3),
            'reservation_expiry_hours' => (int) SystemSetting::get('reservation_expiry_hours', 24),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Konfigurasi aturan sistem perpustakaan berhasil diperbarui.');
    }
}
