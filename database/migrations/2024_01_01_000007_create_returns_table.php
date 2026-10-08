<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->unique()->constrained('loans')->cascadeOnDelete();
            $table->date('return_date');
            $table->integer('late_days')->default(0);
            $table->decimal('penalty_fee', 10, 2)->default(0);
            $table->enum('payment_status', ['lunas', 'belum_bayar', 'tanpa_denda'])->default('tanpa_denda');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
