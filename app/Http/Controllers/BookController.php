<?php

namespace App\Http\Controllers;

use App\Http\Requests\Book\StoreBookCopyRequest;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::with('category')->withCount([
            'copies',
            'copies as available_copies_count' => function ($q) {
                $q->where('is_available', true)->where('condition_status', 'baik');
            },
        ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhere('publisher', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $books = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::orderBy('category_name')->get();

        return view('books.index', compact('books', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('category_name')->get();

        return view('books.create', compact('categories'));
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $book = DB::transaction(function () use ($validated) {
            $book = Book::create([
                'category_id' => $validated['category_id'],
                'isbn' => $validated['isbn'],
                'title' => $validated['title'],
                'author' => $validated['author'],
                'publisher' => $validated['publisher'],
                'publish_year' => $validated['publish_year'],
            ]);

            $copiesCount = (int) ($validated['initial_copies'] ?? 0);
            $shelfLocation = $validated['shelf_location'] ?? 'Rak Utama';

            for ($i = 1; $i <= $copiesCount; $i++) {
                BookCopy::create([
                    'book_id' => $book->id,
                    'inventory_code' => $book->isbn.'-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'shelf_location' => $shelfLocation,
                    'condition_status' => 'baik',
                    'is_available' => true,
                ]);
            }

            return $book;
        });

        return redirect()->route('books.show', $book)
            ->with('success', 'Buku dan eksemplar berhasil ditambahkan ke katalog.');
    }

    public function show(Book $book): View
    {
        $book->load([
            'category',
            'copies',
            'ebooks',
            'reservations' => fn ($q) => $q->where('status', 'pending')->orderBy('queue_number'),
        ]);

        return view('books.show', compact('book'));
    }

    public function edit(Book $book): View
    {
        $categories = Category::orderBy('category_name')->get();

        return view('books.edit', compact('book', 'categories'));
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $book->update($request->validated());

        return redirect()->route('books.show', $book)
            ->with('success', 'Data katalog buku berhasil diperbarui.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $hasActiveLoans = $book->copies()
            ->whereHas('loanDetails.loan', fn ($q) => $q->where('status', 'dipinjam'))
            ->exists();

        if ($hasActiveLoans) {
            return back()->with('error', 'Buku tidak dapat dihapus karena salah satu eksemplar sedang dalam masa peminjaman aktif.');
        }

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Katalog buku beserta eksemplar berhasil dihapus.');
    }

    public function storeCopy(StoreBookCopyRequest $request, Book $book): RedirectResponse
    {
        $validated = $request->validated();
        $validated['book_id'] = $book->id;
        $validated['is_available'] = true;

        BookCopy::create($validated);

        return redirect()->route('books.show', $book)
            ->with('success', 'Eksemplar baru berhasil ditambahkan.');
    }

    public function updateCopy(Request $request, BookCopy $copy): RedirectResponse
    {
        $validated = $request->validate([
            'shelf_location' => ['required', 'string', 'max:100'],
            'condition_status' => ['required', 'in:baik,rusak,hilang'],
            'is_available' => ['required', 'boolean'],
        ]);

        $copy->update($validated);

        return back()->with('success', 'Data eksemplar inventaris berhasil diperbarui.');
    }

    public function destroyCopy(BookCopy $copy): RedirectResponse
    {
        $isInActiveLoan = $copy->loanDetails()
            ->whereHas('loan', fn ($q) => $q->where('status', 'dipinjam'))
            ->exists();

        if ($isInActiveLoan) {
            return back()->with('error', 'Eksemplar tidak dapat dihapus karena sedang dipinjam.');
        }

        $copy->delete();

        return back()->with('success', 'Eksemplar berhasil dihapus dari inventaris.');
    }
}
