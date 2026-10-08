<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->isLibrarian();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'copy_ids' => ['required', 'array', 'min:1'],
            'copy_ids.*' => ['required', 'distinct', 'exists:book_copies,id'],
        ];
    }
}
