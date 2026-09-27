<?php

namespace App\Filament\Organizer\Widgets;

use App\Models\Order;
use App\Models\Participant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        $participantQuery = Participant::query();
        $orderQuery = Order::query();

        if ($user && ! $user->isSuperAdmin()) {
            if ($user->organizer_id) {
                $participantQuery->whereHas('category.event', function ($q) use ($user) {
                    $q->where('organizer_id', $user->organizer_id);
                });
                $orderQuery->whereHas('event', function ($q) use ($user) {
                    $q->where('organizer_id', $user->organizer_id);
                });
            } else {
                $participantQuery->whereRaw('1 = 0');
                $orderQuery->whereRaw('1 = 0');
            }
        }

        $totalParticipants = (clone $participantQuery)->count();
        $totalPaidOrders = (clone $orderQuery)->where('status', 'paid')->count();
        $totalRevenue = (clone $orderQuery)->where('status', 'paid')->sum('subtotal');
        $rpcClaimed = (clone $participantQuery)->where('is_rpc_claimed', true)->count();
        $rpcPercentage = $totalParticipants > 0 ? round(($rpcClaimed / $totalParticipants) * 100, 1) : 0;
        $vipCount = (clone $participantQuery)->where('is_vip', true)->count();

        return [
            Stat::make('Total Pelari Terdaftar', number_format($totalParticipants, 0, ',', '.'))
                ->description("{$totalPaidOrders} Transaksi Terkonfirmasi")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Total Pendapatan Tiket', 'Rp '.number_format($totalRevenue, 0, ',', '.'))
                ->description('Dana Masuk Bersih dari Peserta')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pengambilan Race Pack (RPC)', "{$rpcClaimed} / {$totalParticipants} ({$rpcPercentage}%)")
                ->description('Status Pengambilan Paket Lomba')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($rpcPercentage > 50 ? 'success' : 'warning'),

            Stat::make('Peserta VVIP & Undangan', number_format($vipCount, 0, ',', '.'))
                ->description('Nomor BIB Khusus Terlindungi')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }
}
