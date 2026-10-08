<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LibraryCirculationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    private Book $book;

    private BookCopy $copy1;

    private BookCopy $copy2;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['role_name' => 'Admin Pegawai']);
        $memberRole = Role::create(['role_name' => 'Anggota']);

        SystemSetting::create(['key' => 'fine_per_day', 'value' => '2000']);
        SystemSetting::create(['key' => 'max_borrow_days', 'value' => '7']);
        SystemSetting::create(['key' => 'max_books_borrowed', 'value' => '3']);
        SystemSetting::create(['key' => 'reservation_expiry_hours', 'value' => '24']);

        $this->admin = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $this->member = User::create([
            'role_id' => $memberRole->id,
            'member_number' => 'MBR-001',
            'name' => 'Member Test',
            'email' => 'member@test.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $category = Category::create(['category_name' => 'Umum']);

        $this->book = Book::create([
            'category_id' => $category->id,
            'isbn' => '111-222-333',
            'title' => 'Buku Panduan',
            'author' => 'Penulis',
            'publisher' => 'Penerbit',
            'publish_year' => 2023,
        ]);

        $this->copy1 = BookCopy::create([
            'book_id' => $this->book->id,
            'inventory_code' => 'INV-001',
            'shelf_location' => 'Rak A',
            'condition_status' => 'baik',
            'is_available' => true,
        ]);

        $this->copy2 = BookCopy::create([
            'book_id' => $this->book->id,
            'inventory_code' => 'INV-002',
            'shelf_location' => 'Rak A',
            'condition_status' => 'baik',
            'is_available' => true,
        ]);
    }

    public function test_loan_circulation_flow(): void
    {
        $response = $this->actingAs($this->admin)->post(route('loans.store'), [
            'user_id' => $this->member->id,
            'copy_ids' => [$this->copy1->id],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('loans', [
            'user_id' => $this->member->id,
            'status' => 'dipinjam',
        ]);

        $this->copy1->refresh();
        $this->assertFalse($this->copy1->is_available);

        $loan = Loan::where('user_id', $this->member->id)->first();

        $returnResponse = $this->actingAs($this->admin)->post(route('returns.store'), [
            'loan_id' => $loan->id,
            'payment_status' => 'tanpa_denda',
        ]);

        $returnResponse->assertSessionHas('success');
        $this->assertDatabaseHas('returns', [
            'loan_id' => $loan->id,
        ]);

        $this->copy1->refresh();
        $this->assertTrue($this->copy1->is_available);
    }

    public function test_cannot_borrow_exceeding_max_books_setting(): void
    {
        SystemSetting::set('max_books_borrowed', 1);

        $response = $this->actingAs($this->admin)->post(route('loans.store'), [
            'user_id' => $this->member->id,
            'copy_ids' => [$this->copy1->id, $this->copy2->id],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('loans', [
            'user_id' => $this->member->id,
        ]);
    }

    public function test_reservation_requires_zero_available_copies(): void
    {
        $response = $this->actingAs($this->member)->post(route('reservations.store'), [
            'book_id' => $this->book->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('reservations', [
            'book_id' => $this->book->id,
        ]);

        $this->copy1->update(['is_available' => false]);
        $this->copy2->update(['is_available' => false]);

        $successResponse = $this->actingAs($this->member)->post(route('reservations.store'), [
            'book_id' => $this->book->id,
        ]);

        $successResponse->assertSessionHas('success');
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->member->id,
            'book_id' => $this->book->id,
            'queue_number' => 1,
            'status' => 'pending',
        ]);
    }
}
