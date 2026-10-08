<?php

namespace App\Http\Requests\Return;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->isLibrarian();
    }

    public function rules(): array
    {
        return [
            'loan_id' => ['required', 'exists:loans,id'],
            'payment_status' => ['nullable', 'in:lunas,belum_bayar,tanpa_denda'],
        ];
    }
}
