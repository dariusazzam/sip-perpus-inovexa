<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'fine_per_day' => ['required', 'numeric', 'min:0'],
            'max_borrow_days' => ['required', 'integer', 'min:1', 'max:60'],
            'max_books_borrowed' => ['required', 'integer', 'min:1', 'max:20'],
            'reservation_expiry_hours' => ['required', 'integer', 'min:1', 'max:168'],
        ];
    }
}
