<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'isbn',
        'title',
        'author',
        'publisher',
        'publish_year',
    ];

    protected function casts(): array
    {
        return [
            'publish_year' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class, 'book_id');
    }

    public function availableCopies(): HasMany
    {
        return $this->hasMany(BookCopy::class, 'book_id')
            ->where('is_available', true)
            ->where('condition_status', 'baik');
    }

    public function ebooks(): HasMany
    {
        return $this->hasMany(Ebook::class, 'book_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'book_id');
    }
}
