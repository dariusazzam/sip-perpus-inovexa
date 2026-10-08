<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->isLibrarian();
    }

    public function rules(): array
    {
        $bookId = $this->route('book')?->id ?? $this->route('book');

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'isbn' => ['required', 'string', 'max:50', Rule::unique('books', 'isbn')->ignore($bookId)],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'publish_year' => ['required', 'integer', 'min:1500', 'max:'.(date('Y') + 1)],
        ];
    }
}
