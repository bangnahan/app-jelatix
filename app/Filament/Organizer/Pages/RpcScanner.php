<?php

namespace App\Filament\Organizer\Pages;

use App\Models\Event;
use App\Models\Participant;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class RpcScanner extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Scanner RPC (Race Pack)';

    protected static ?string $title = 'Scanner Pengambilan Race Pack (RPC)';

    protected static ?string $navigationGroup = 'Operasional Venue';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.organizer.pages.rpc-scanner';

    public ?int $selectedEventId = null;

    public string $manualToken = '';

    public ?array $participantData = null;

    public ?string $errorMessage = null;

    public ?string $warningMessage = null;

    public bool $isProxy = false;

    public string $proxyName = '';

    public string $proxyNik = '';

    public array $recentClaims = [];

    public function mount(): void
    {
        $firstEvent = $this->getEvents()->first();
        if ($firstEvent) {
            $this->selectedEventId = $firstEvent->id;
        }

        $this->loadRecentClaims();
    }

    public function getEvents()
    {
        $user = auth()->user();
        $query = Event::query();

        if ($user && ! $user->isSuperAdmin()) {
            if ($user->organizer_id) {
                $query->where('organizer_id', $user->organizer_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query->orderBy('event_start_date', 'desc')->get();
    }

    public function updatedSelectedEventId(): void
    {
        $this->resetParticipant();
        $this->loadRecentClaims();
    }

    public function getStatsProperty(): array
    {
        $user = auth()->user();
        $query = Participant::query();

        if ($user && ! $user->isSuperAdmin() && $user->organizer_id) {
            $query->whereHas('category.event', function ($q) use ($user) {
                $q->where('organizer_id', $user->organizer_id);
            });
        }

        if ($this->selectedEventId) {
            $query->whereHas('category', function ($q) {
                $q->where('event_id', $this->selectedEventId);
            });
        }

        // Only count participants whose orders are paid
        $query->whereHas('order', function ($q) {
            $q->where('status', 'paid');
        });

        $total = (clone $query)->count();
        $claimed = (clone $query)->where('is_rpc_claimed', true)->count();
        $unclaimed = $total - $claimed;
        $percentage = $total > 0 ? (int) round(($claimed / $total) * 100) : 0;

        return [
            'total' => $total,
            'claimed' => $claimed,
            'unclaimed' => $unclaimed,
            'percentage' => $percentage,
        ];
    }

    public function verifyToken(string $token): void
    {
        $token = trim($token);
        if (empty($token)) {
            return;
        }

        $this->manualToken = $token;
        $this->errorMessage = null;
        $this->warningMessage = null;

        $user = auth()->user();
        $query = Participant::with(['category.event', 'jerseySize', 'order', 'rpcClaimedBy'])
            ->where(function ($q) use ($token) {
                $q->where('qr_token', $token)
                    ->orWhere('bib_number', $token)
                    ->orWhere('id_number', $token);
            });

        if ($user && ! $user->isSuperAdmin()) {
            if ($user->organizer_id) {
                $query->whereHas('category.event', function ($q) use ($user) {
                    $q->where('organizer_id', $user->organizer_id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($this->selectedEventId) {
            $query->whereHas('category', function ($q) {
                $q->where('event_id', $this->selectedEventId);
            });
        }

        $participant = $query->first();

        if (! $participant) {
            $this->participantData = null;
            $this->errorMessage = 'E-Ticket atau Nomor BIB tidak ditemukan!';
            $this->dispatch('scanner-sound', sound: 'error');
            Notification::make()
                ->title('Tidak Ditemukan')
                ->body('Data peserta dengan kode/BIB tersebut tidak ditemukan pada event Anda.')
                ->danger()
                ->send();

            return;
        }

        // Check payment status
        if ($participant->order && $participant->order->status !== 'paid') {
            $this->warningMessage = "Perhatian: Order {$participant->order->order_code} berstatus ".strtoupper($participant->order->status).' (Belum Lunas).';
        }

        $this->isProxy = (bool) $participant->is_proxy_claimed;
        $this->proxyName = $participant->proxy_collector_name ?: '';
        $this->proxyNik = $participant->proxy_collector_id_number ?: '';

        $this->participantData = [
            'id' => $participant->id,
            'full_name' => $participant->full_name,
            'bib_name' => $participant->bib_name ?: $participant->full_name,
            'bib_number' => $participant->bib_number ?: 'BELUM DI-ASSIGN',
            'event_title' => $participant->category?->event?->title,
            'category_name' => $participant->category?->name,
            'distance_km' => $participant->category?->distance_km,
            'jersey_size' => $participant->jerseySize ? $participant->jerseySize->size_name.' ('.ucfirst($participant->jerseySize->gender_type).')' : 'Tidak Ada Data',
            'jersey_size_short' => $participant->jerseySize?->size_name ?: '-',
            'jersey_gender' => $participant->jerseySize ? ucfirst($participant->jerseySize->gender_type) : '',
            'blood_type' => $participant->blood_type ?: '-',
            'id_number' => $participant->id_number,
            'gender' => $participant->gender === 'male' ? 'Laki-laki' : 'Perempuan',
            'emergency_contact' => $participant->emergency_contact_name.' ('.$participant->emergency_contact_phone.')',
            'medical_conditions' => $participant->medical_conditions ?: 'Tidak Ada',
            'is_vip' => $participant->is_vip,
            'is_rpc_claimed' => $participant->is_rpc_claimed,
            'rpc_claimed_at' => $participant->rpc_claimed_at?->format('d M Y, H:i').' WIB',
            'rpc_claimed_by' => $participant->rpcClaimedBy?->name,
            'is_proxy_claimed' => $participant->is_proxy_claimed,
            'proxy_collector_name' => $participant->proxy_collector_name,
        ];

        if ($participant->is_rpc_claimed) {
            $this->dispatch('scanner-sound', sound: 'warning');
            Notification::make()
                ->title('PERINGATAN: SUDAH DIAMBIL')
                ->body("Race Pack telah diserahkan pada {$participant->rpc_claimed_at?->format('d M Y, H:i')} WIB".($participant->rpcClaimedBy ? " oleh {$participant->rpcClaimedBy->name}" : ''))
                ->warning()
                ->send();
        } else {
            $this->dispatch('scanner-sound', sound: 'success');
        }
    }

    public function claimRacePack(): void
    {
        if (! $this->participantData) {
            return;
        }

        $participantId = $this->participantData['id'];

        if ($this->isProxy) {
            if (empty(trim($this->proxyName)) || empty(trim($this->proxyNik))) {
                Notification::make()
                    ->title('Data Kuasa Tidak Lengkap')
                    ->body('Harap isi Nama dan NIK KTP Penerima Kuasa!')
                    ->danger()
                    ->send();

                return;
            }
        }

        $user = auth()->user();

        try {
            DB::transaction(function () use ($participantId, $user) {
                $query = Participant::where('id', $participantId)->lockForUpdate();

                if ($user && ! $user->isSuperAdmin() && $user->organizer_id) {
                    $query->whereHas('category.event', function ($q) use ($user) {
                        $q->where('organizer_id', $user->organizer_id);
                    });
                }

                $participant = $query->first();

                if (! $participant) {
                    throw new \Exception('Peserta tidak ditemukan atau Anda tidak berwenang.');
                }

                if ($participant->is_rpc_claimed) {
                    throw new \Exception("Race pack SUDAH PERNAH DIAMBIL sebelumnya pada {$participant->rpc_claimed_at?->format('d M Y, H:i')} WIB!");
                }

                $participant->update([
                    'is_rpc_claimed' => true,
                    'rpc_claimed_at' => now(),
                    'rpc_claimed_by_user_id' => $user?->id,
                    'is_proxy_claimed' => $this->isProxy,
                    'proxy_collector_name' => $this->isProxy ? trim($this->proxyName) : null,
                    'proxy_collector_id_number' => $this->isProxy ? trim($this->proxyNik) : null,
                ]);

                $this->participantData['is_rpc_claimed'] = true;
                $this->participantData['rpc_claimed_at'] = now()->format('d M Y, H:i').' WIB';
                $this->participantData['rpc_claimed_by'] = $user?->name;
                $this->participantData['is_proxy_claimed'] = $this->isProxy;
                $this->participantData['proxy_collector_name'] = $this->proxyName;
            });

            $this->loadRecentClaims();
            $this->dispatch('scanner-sound', sound: 'success');

            Notification::make()
                ->title('Race Pack Berhasil Diserahkan!')
                ->body("BIB: {$this->participantData['bib_number']} - {$this->participantData['full_name']} ({$this->participantData['jersey_size']})")
                ->success()
                ->send();

        } catch (\Exception $e) {
            $this->dispatch('scanner-sound', sound: 'error');
            Notification::make()
                ->title('Gagal Klaim Race Pack')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetParticipant(): void
    {
        $this->participantData = null;
        $this->manualToken = '';
        $this->errorMessage = null;
        $this->warningMessage = null;
        $this->isProxy = false;
        $this->proxyName = '';
        $this->proxyNik = '';
    }

    public function loadRecentClaims(): void
    {
        $user = auth()->user();
        $query = Participant::with(['category.event', 'jerseySize', 'rpcClaimedBy'])
            ->where('is_rpc_claimed', true);

        if ($user && ! $user->isSuperAdmin()) {
            if ($user->organizer_id) {
                $query->whereHas('category.event', function ($q) use ($user) {
                    $q->where('organizer_id', $user->organizer_id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($this->selectedEventId) {
            $query->whereHas('category', function ($q) {
                $q->where('event_id', $this->selectedEventId);
            });
        }

        $this->recentClaims = $query->orderBy('rpc_claimed_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'bib_number' => $p->bib_number ?: 'BELUM-BIB',
                    'full_name' => $p->full_name,
                    'jersey' => $p->jerseySize?->size_name ?: '-',
                    'claimed_at' => $p->rpc_claimed_at?->format('H:i:s').' WIB',
                    'claimed_by' => $p->rpcClaimedBy?->name ?: 'Kru',
                    'is_proxy' => $p->is_proxy_claimed,
                    'proxy_name' => $p->proxy_collector_name,
                ];
            })
            ->toArray();
    }
}
