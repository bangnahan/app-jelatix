<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrewScannerController extends Controller
{
    public function index()
    {
        return view('crew.scanner');
    }

    /**
     * Verifikasi token QR Code dari kamera kru
     */
    public function verifyQr(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $search = trim($request->input('token'));

        $participant = Participant::with(['category.event', 'jerseySize', 'order', 'rpcClaimedBy'])
            ->where('qr_token', $search)
            ->orWhere('bib_number', $search)
            ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'E-Ticket tidak ditemukan atau tidak valid!',
            ], 404);
        }

        // Cek apakah order sudah lunas
        if ($participant->order && $participant->order->status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => "Order {$participant->order->order_code} berstatus " . strtoupper($participant->order->status) . " (Belum Lunas).",
                'participant' => $participant,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'participant' => [
                'id' => $participant->id,
                'full_name' => $participant->full_name,
                'bib_name' => $participant->bib_name ?: $participant->full_name,
                'bib_number' => $participant->bib_number ?: 'BELUM DI-ASSIGN',
                'category_name' => $participant->category?->name,
                'distance_km' => $participant->category?->distance_km,
                'jersey_size' => $participant->jerseySize ? $participant->jerseySize->size_name . ' (' . ucfirst($participant->jerseySize->gender_type) . ')' : 'Tidak Ada Data',
                'blood_type' => $participant->blood_type,
                'id_number' => $participant->id_number,
                'is_vip' => $participant->is_vip,
                'is_rpc_claimed' => $participant->is_rpc_claimed,
                'rpc_claimed_at' => $participant->rpc_claimed_at?->format('d M Y, H:i') . ' WIB',
                'rpc_claimed_by' => $participant->rpcClaimedBy?->name,
                'is_proxy_claimed' => $participant->is_proxy_claimed,
                'proxy_collector_name' => $participant->proxy_collector_name,
            ],
        ]);
    }

    /**
     * Konfirmasi penyerahan race pack kepada pelari
     */
    public function claimRpc(Request $request): JsonResponse
    {
        $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'is_proxy' => 'nullable|boolean',
            'proxy_name' => 'nullable|string',
            'proxy_nik' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $participant = Participant::where('id', $request->input('participant_id'))
                ->lockForUpdate()
                ->first();

            if ($participant->is_rpc_claimed) {
                return response()->json([
                    'success' => false,
                    'message' => "Race Pack SUDAH PERNAH DIAMBIL sebelumnya pada {$participant->rpc_claimed_at?->format('d M Y, H:i')} WIB!",
                ], 422);
            }

            $isProxy = (bool) $request->input('is_proxy', false);

            $participant->update([
                'is_rpc_claimed' => true,
                'rpc_claimed_at' => now(),
                'rpc_claimed_by_user_id' => auth()->id() ?: null,
                'is_proxy_claimed' => $isProxy,
                'proxy_collector_name' => $isProxy ? $request->input('proxy_name') : null,
                'proxy_collector_id_number' => $isProxy ? $request->input('proxy_nik') : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Race Pack berhasil diserahkan kepada {$participant->full_name}.",
                'claimed_at' => now()->format('d M Y, H:i') . ' WIB',
            ]);
        });
    }
}
