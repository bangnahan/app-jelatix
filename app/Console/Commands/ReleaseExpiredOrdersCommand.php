<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredOrdersCommand extends Command
{
    protected $signature = 'jelatix:release-expired-orders';
    protected $description = 'Batalkan order tertunda yang melebihi batas waktu (hold kuota) dan pulihkan slot tiket serta ukuran jersey';

    public function handle(): int
    {
        $expiredOrders = Order::where('status', 'pending')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', now())
            ->with(['participants.category', 'participants.jerseySize'])
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('Tidak ada order kadaluarsa.');
            return Command::SUCCESS;
        }

        $releasedCount = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, &$releasedCount) {
                $order = Order::where('id', $order->id)->lockForUpdate()->first();

                if ($order->status !== 'pending') {
                    return;
                }

                $order->update(['status' => 'expired']);

                // Kembalikan kuota kategori dan stok ukuran jersey ke publik
                foreach ($order->participants as $participant) {
                    if ($participant->category) {
                        $participant->category->decrement('slots_taken', 1);
                    }
                    if ($participant->jerseySize) {
                        $participant->jerseySize->decrement('allocated_stock', 1);
                    }
                }

                $releasedCount++;
            });
        }

        $this->info("Berhasil merilis {$releasedCount} order kadaluarsa.");
        Log::info("Jelatix Cron: Released {$releasedCount} expired orders.");

        return Command::SUCCESS;
    }
}
