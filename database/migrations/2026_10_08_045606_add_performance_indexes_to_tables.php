<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->index(['title', 'author'], 'books_title_author_index');
        });

        Schema::table('book_copies', function (Blueprint $table) {
            $table->index(['is_available', 'condition_status'], 'book_copies_availability_condition_index');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->index(['status', 'due_date'], 'loans_status_due_date_index');
            $table->index(['user_id', 'status'], 'loans_user_status_index');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->index(['book_id', 'status', 'queue_number'], 'reservations_book_status_queue_index');
        });

        Schema::table('ebooks', function (Blueprint $table) {
            $table->index(['doc_type', 'access_level'], 'ebooks_doc_type_access_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropIndex('ebooks_doc_type_access_index');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('reservations_book_status_queue_index');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex('loans_user_status_index');
            $table->dropIndex('loans_status_due_date_index');
        });

        Schema::table('book_copies', function (Blueprint $table) {
            $table->dropIndex('book_copies_availability_condition_index');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex('books_title_author_index');
        });
    }
};
