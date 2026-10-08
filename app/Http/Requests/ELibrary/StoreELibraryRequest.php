<?php

namespace App\Http\Requests\ELibrary;

use Illuminate\Foundation\Http\FormRequest;

class StoreELibraryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->isLibrarian();
    }

    public function rules(): array
    {
        return [
            'book_id' => ['required', 'exists:books,id'],
            'doc_type' => ['required', 'in:ebook,jurnal,skripsi,modul'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'access_level' => ['required', 'in:public,member_only'],
        ];
    }
}
