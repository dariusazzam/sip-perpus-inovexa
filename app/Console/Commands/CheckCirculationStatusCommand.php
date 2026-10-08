<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\Reservation;
use Illuminate\Console\Command;

class CheckCirculationStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-circulation-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatisasi pembaruan status peminjaman lewat jatuh tempo (terlambat) dan antrean reservasi kadaluarsa';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->toDateString();

        // 1. Perbarui status pinjaman yang melewati jatuh tempo
        $overdueLoansCount = Loan::where('status', 'dipinjam')
            ->whereDate('due_date', '<', $today)
            ->update(['status' => 'terlambat']);

        // 2. Perbarui reservasi antrean yang melewati masa berlaku
        $expiredReservationsCount = Reservation::where('status', 'pending')
            ->where('expiration_date', '<', now())
            ->update(['status' => 'kadaluarsa']);

        $this->info('Pemeriksaan sirkulasi selesai.');
        $this->line("- Pinjaman ditandai terlambat: {$overdueLoansCount}");
        $this->line("- Reservasi ditandai kadaluarsa: {$expiredReservationsCount}");

        return Command::SUCCESS;
    }
}
