<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->isLibrarian();
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'isbn' => ['required', 'string', 'max:50', 'unique:books,isbn'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'publish_year' => ['required', 'integer', 'min:1500', 'max:'.(date('Y') + 1)],
            'shelf_location' => ['nullable', 'string', 'max:100'],
            'initial_copies' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
