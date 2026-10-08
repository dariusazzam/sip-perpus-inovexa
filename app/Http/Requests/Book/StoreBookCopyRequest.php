<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->isLibrarian();
    }

    public function rules(): array
    {
        return [
            'inventory_code' => ['required', 'string', 'max:100', 'unique:book_copies,inventory_code'],
            'shelf_location' => ['required', 'string', 'max:100'],
            'condition_status' => ['required', 'in:baik,rusak,hilang'],
        ];
    }
}
