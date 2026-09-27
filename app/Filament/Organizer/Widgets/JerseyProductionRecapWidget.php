<?php

namespace App\Filament\Organizer\Widgets;

use App\Models\Event;
use App\Models\Participant;
use Filament\Widgets\Widget;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JerseyProductionRecapWidget extends Widget
{
    protected static string $view = 'filament.organizer.widgets.jersey-production-recap-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public ?int $selectedEventId = null;

    public string $viewMode = 'summary'; // 'summary' or 'by_category'

    public function mount(): void
    {
        $firstEvent = $this->getEvents()->first();
        if ($firstEvent) {
            $this->selectedEventId = $firstEvent->id;
        }
    }

    public function getEvents()
    {
        $user = auth()->user();
        $query = Event::query();

        if ($user && ! $user->isSuperAdmin() && $user->organizer_id) {
            $query->where('organizer_id', $user->organizer_id);
        }

        return $query->orderBy('event_start_date', 'desc')->get();
    }

    public function getSelectedEventProperty(): ?Event
    {
        if (! $this->selectedEventId) {
            return null;
        }

        return Event::with(['categories', 'jerseySizes'])->find($this->selectedEventId);
    }

    public function getRecapDataProperty(): array
    {
        $event = $this->selectedEvent;

        if (! $event) {
            return [
                'sizes' => [],
                'categories' => [],
                'matrix' => [],
                'grand_total_paid' => 0,
                'grand_total_pending' => 0,
                'grand_total' => 0,
            ];
        }

        // Ambil semua ukuran jersey event
        $jerseySizes = $event->jerseySizes()->get();

        // Query grouped data
        $counts = Participant::query()
            ->join('orders', 'participants.order_id', '=', 'orders.id')
            ->join('event_categories', 'participants.event_category_id', '=', 'event_categories.id')
            ->where('event_categories.event_id', $event->id)
            ->whereIn('orders.status', ['paid', 'pending'])
            ->selectRaw('participants.jersey_size_id, participants.event_category_id, orders.status, count(*) as count')
            ->groupBy('participants.jersey_size_id', 'participants.event_category_id', 'orders.status')
            ->get();

        $paidBySize = [];
        $pendingBySize = [];
        $matrix = []; // [category_id][size_id] => paid_count

        foreach ($counts as $row) {
            $sId = $row->jersey_size_id;
            $cId = $row->event_category_id;
            $status = $row->status;
            $count = (int) $row->count;

            if ($status === 'paid') {
                $paidBySize[$sId] = ($paidBySize[$sId] ?? 0) + $count;
                $matrix[$cId][$sId] = ($matrix[$cId][$sId] ?? 0) + $count;
            } else {
                $pendingBySize[$sId] = ($pendingBySize[$sId] ?? 0) + $count;
            }
        }

        $grandTotalPaid = array_sum($paidBySize);
        $grandTotalPending = array_sum($pendingBySize);
        $grandTotal = $grandTotalPaid + $grandTotalPending;

        $sizesData = [];
        foreach ($jerseySizes as $j) {
            $paid = $paidBySize[$j->id] ?? 0;
            $pending = $pendingBySize[$j->id] ?? 0;
            $total = $paid + $pending;
            $percentage = $grandTotalPaid > 0 ? round(($paid / $grandTotalPaid) * 100, 1) : 0;

            $sizesData[] = [
                'id' => $j->id,
                'size_name' => $j->size_name,
                'gender_type' => $j->gender_type,
                'chest_width_cm' => $j->chest_width_cm,
                'body_length_cm' => $j->body_length_cm,
                'is_unlimited' => $j->isUnlimited(),
                'stock' => $j->stock,
                'available_stock' => $j->available_stock,
                'paid_qty' => $paid,
                'pending_qty' => $pending,
                'total_qty' => $total,
                'percentage' => $percentage,
            ];
        }

        return [
            'sizes' => $sizesData,
            'categories' => $event->categories()->get(),
            'matrix' => $matrix,
            'grand_total_paid' => $grandTotalPaid,
            'grand_total_pending' => $grandTotalPending,
            'grand_total' => $grandTotal,
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $event = $this->selectedEvent;
        $recap = $this->recapData;

        $eventTitle = $event ? $event->title : 'Event';
        $filename = 'rekap-produksi-jersey-'.date('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () use ($eventTitle, $recap) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['REKAP KEBUTUHAN PRODUKSI JERSEY - JELATIX']);
            fputcsv($handle, ['Event:', $eventTitle]);
            fputcsv($handle, ['Tanggal Rekap:', now()->format('d F Y, H:i').' WIB']);
            fputcsv($handle, ['Catatan:', 'Data siap produksi diambil dari peserta yang telah berstatus Lunas (Paid)']);
            fputcsv($handle, []);

            // Summary Table
            fputcsv($handle, [
                'UKURAN',
                'TIPE GENDER',
                'LEBAR DADA (CM)',
                'PANJANG (CM)',
                'SIAP PRODUKSI (LUNAS)',
                'PENDING (ESTIMASI TAMBAHAN)',
                'TOTAL TERDAFTAR',
                'PERSENTASE (%)',
                'STATUS KUOTA STOK',
            ]);

            foreach ($recap['sizes'] as $s) {
                fputcsv($handle, [
                    $s['size_name'],
                    strtoupper($s['gender_type']),
                    $s['chest_width_cm'] ?: '-',
                    $s['body_length_cm'] ?: '-',
                    $s['paid_qty'],
                    $s['pending_qty'],
                    $s['total_qty'],
                    $s['percentage'].'%',
                    $s['is_unlimited'] ? 'Unlimited' : "Batas: {$s['stock']} pcs (Sisa {$s['available_stock']})",
                ]);
            }

            fputcsv($handle, [
                'TOTAL KESELURUHAN',
                '',
                '',
                '',
                $recap['grand_total_paid'],
                $recap['grand_total_pending'],
                $recap['grand_total'],
                '100%',
                '',
            ]);

            // Jika ada breakdown per kategori
            if (count($recap['categories']) > 1) {
                fputcsv($handle, []);
                fputcsv($handle, ['BREAKDOWN PER KATEGORI LOMBA (PESERTA LUNAS)']);

                $catHeader = ['KATEGORI LOMBA'];
                foreach ($recap['sizes'] as $s) {
                    $catHeader[] = $s['size_name'];
                }
                $catHeader[] = 'TOTAL KATEGORI';
                fputcsv($handle, $catHeader);

                foreach ($recap['categories'] as $cat) {
                    $row = [$cat->name];
                    $catTotal = 0;
                    foreach ($recap['sizes'] as $s) {
                        $qty = $recap['matrix'][$cat->id][$s['id']] ?? 0;
                        $catTotal += $qty;
                        $row[] = $qty;
                    }
                    $row[] = $catTotal;
                    fputcsv($handle, $row);
                }
            }

            fclose($handle);
        }, $filename, $headers);
    }
}
