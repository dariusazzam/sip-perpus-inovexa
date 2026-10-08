<?php

namespace App\Http\Controllers;

use App\Http\Requests\ELibrary\StoreELibraryRequest;
use App\Models\Book;
use App\Models\Ebook;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ELibraryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ebook::with('book.category');

        if ($request->filled('doc_type')) {
            $query->where('doc_type', $request->input('doc_type'));
        }

        if ($request->filled('access_level')) {
            $query->where('access_level', $request->input('access_level'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('book', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        $ebooks = $query->latest()->paginate(12)->withQueryString();
        $books = Book::orderBy('title')->get();

        return view('elibrary.index', compact('ebooks', 'books'));
    }

    public function store(StoreELibraryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $file = $request->file('file');
        $filePath = $file->store('ebooks', 'local');

        Ebook::create([
            'book_id' => $validated['book_id'],
            'doc_type' => $validated['doc_type'],
            'file_path' => $filePath,
            'access_level' => $validated['access_level'],
        ]);

        return redirect()->route('elibrary.index')
            ->with('success', 'Dokumen digital E-Library berhasil diunggah secara aman.');
    }

    public function read(Ebook $ebook): StreamedResponse|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($ebook->access_level === 'member_only') {
            if (! $user) {
                return redirect()->route('login')
                    ->with('error', 'Dokumen ini hanya dapat diakses oleh anggota perpustakaan yang terdaftar.');
            }

            if (! $user->isActive()) {
                abort(403, 'Akun Anda sedang dinonaktifkan.');
            }
        }

        $disk = $this->getStorageDisk($ebook->file_path);

        if (! $disk->exists($ebook->file_path)) {
            abort(404, 'File dokumen tidak ditemukan pada penyimpanan server.');
        }

        if ($user) {
            $history = session()->get('reading_history', []);
            $history[$ebook->id] = [
                'ebook_id' => $ebook->id,
                'title' => $ebook->book->title,
                'doc_type' => $ebook->doc_type,
                'read_at' => now()->toDateTimeString(),
            ];
            session()->put('reading_history', $history);
        }

        return $disk->response(
            $ebook->file_path,
            $ebook->book->title.'.'.pathinfo($ebook->file_path, PATHINFO_EXTENSION),
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }

    public function download(Ebook $ebook): BinaryFileResponse|StreamedResponse|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($ebook->access_level === 'member_only') {
            if (! $user) {
                return redirect()->route('login')
                    ->with('error', 'Silakan login terlebih dahulu untuk mengunduh dokumen ini.');
            }

            if (! $user->isActive()) {
                abort(403, 'Akun Anda sedang dinonaktifkan.');
            }
        }

        $disk = $this->getStorageDisk($ebook->file_path);

        if (! $disk->exists($ebook->file_path)) {
            abort(404, 'File dokumen tidak ditemukan pada server.');
        }

        return $disk->download(
            $ebook->file_path,
            $ebook->book->title.'-'.$ebook->doc_type.'.pdf'
        );
    }

    public function history(): View
    {
        $history = session()->get('reading_history', []);

        return view('elibrary.history', compact('history'));
    }

    public function destroy(Ebook $ebook): RedirectResponse
    {
        if (Storage::disk('local')->exists($ebook->file_path)) {
            Storage::disk('local')->delete($ebook->file_path);
        } elseif (Storage::disk('public')->exists($ebook->file_path)) {
            Storage::disk('public')->delete($ebook->file_path);
        }

        $ebook->delete();

        return back()->with('success', 'Dokumen digital E-Library berhasil dihapus.');
    }

    /**
     * Resolves the storage disk for the file, preferring private local storage.
     */
    protected function getStorageDisk(string $filePath): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        return Storage::disk('local')->exists($filePath)
            ? Storage::disk('local')
            : Storage::disk('public');
    }
}
