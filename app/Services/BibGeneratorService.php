<?php

namespace App\Services;

use App\Models\EventCategory;
use App\Models\Participant;
use Exception;
use Illuminate\Support\Facades\DB;

class BibGeneratorService
{
    /**
     * Generate nomor BIB otomatis untuk peserta saat pembayaran lunas
     */
    public function autoAssignBib(Participant $participant): string
    {
        // Jika sudah memiliki nomor BIB kustom VVIP, jangan ubah
        if ($participant->is_custom_bib && !empty($participant->bib_number)) {
            return $participant->bib_number;
        }

        $category = $participant->category;
        if (!$category) {
            throw new Exception('Kategori lomba tidak ditemukan');
        }

        return DB::transaction(function () use ($participant, $category) {
            $prefix = $category->bib_prefix ?: '';
            $startNum = (int) ($category->bib_start_number ?: 1001);
            $reservedPool = (array) ($category->reserved_bib_numbers ?: []);

            // Ambil semua nomor BIB yang sudah terpakai di kategori ini
            $existingBibs = Participant::where('event_category_id', $category->id)
                ->whereNotNull('bib_number')
                ->where('id', '!=', $participant->id)
                ->pluck('bib_number')
                ->toArray();

            $candidateNum = $startNum;
            while (true) {
                $candidateBib = $prefix . $candidateNum;

                // Cek apakah nomor ini masuk ke daftar reservasi VVIP atau sudah terpakai
                if (!in_array($candidateNum, $reservedPool) && 
                    !in_array((string)$candidateNum, $reservedPool) && 
                    !in_array($candidateBib, $existingBibs)) {
                    break;
                }

                $candidateNum++;
            }

            $finalBib = $prefix . $candidateNum;
            $participant->update(['bib_number' => $finalBib]);

            return $finalBib;
        });
    }

    /**
     * Tetapkan nomor BIB kustom untuk tamu VVIP / Pejabat / Sponsor
     */
    public function assignCustomVipBib(
        Participant $participant,
        string $customBibNumber,
        string $reason = 'Tamu VVIP'
    ): bool {
        // Cek duplikasi di kategori yang sama
        $exists = Participant::where('event_category_id', $participant->event_category_id)
            ->where('bib_number', $customBibNumber)
            ->where('id', '!=', $participant->id)
            ->exists();

        if ($exists) {
            throw new Exception("Nomor BIB {$customBibNumber} sudah digunakan oleh pelari lain pada kategori ini.");
        }

        return $participant->update([
            'bib_number' => $customBibNumber,
            'is_vip' => true,
            'is_custom_bib' => true,
            'custom_bib_reason' => $reason,
        ]);
    }

    /**
     * Remap / Urutkan ulang nomor BIB massal sebelum cetak fisik
     * (Peserta dengan is_custom_bib = true akan terlindungi dan tidak tertimpa)
     */
    public function bulkRemapBibs(
        EventCategory $category,
        string $sortBy = 'estimated_finish_time',
        string $direction = 'asc'
    ): int {
        return DB::transaction(function () use ($category, $sortBy, $direction) {
            $prefix = $category->bib_prefix ?: '';
            $startNum = (int) ($category->bib_start_number ?: 1001);
            $reservedPool = (array) ($category->reserved_bib_numbers ?: []);

            // Ambil nomor BIB milik peserta VVIP yang terkunci
            $lockedBibs = Participant::where('event_category_id', $category->id)
                ->where('is_custom_bib', true)
                ->whereNotNull('bib_number')
                ->pluck('bib_number')
                ->toArray();

            // Ambil seluruh peserta non-custom yang terdaftar dan lunas
            $participants = Participant::where('event_category_id', $category->id)
                ->where('is_custom_bib', false)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'paid');
                })
                ->orderBy($sortBy, $direction)
                ->get();

            $currentNum = $startNum;
            $count = 0;

            foreach ($participants as $runner) {
                while (true) {
                    $candidateBib = $prefix . $currentNum;

                    // Lewati nomor jika masuk ke pool reservasi atau sudah dipakai oleh VVIP
                    if (!in_array($currentNum, $reservedPool) &&
                        !in_array((string)$currentNum, $reservedPool) &&
                        !in_array($candidateBib, $lockedBibs)) {
                        break;
                    }

                    $currentNum++;
                }

                $runner->update(['bib_number' => $prefix . $currentNum]);
                $currentNum++;
                $count++;
            }

            return $count;
        });
    }
}
