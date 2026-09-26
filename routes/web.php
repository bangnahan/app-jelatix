<?php

use App\Http\Controllers\CrewScannerController;
use App\Http\Controllers\PublicTicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('public.ticket.index');
});

// Fitur Mandiri Pelari: Cek Tiket & Unduh E-Ticket
Route::get('/cek-tiket', [PublicTicketController::class, 'index'])->name('public.ticket.index');
Route::post('/cek-tiket', [PublicTicketController::class, 'search'])->name('public.ticket.search');
Route::get('/ticket/{token}/download', [PublicTicketController::class, 'downloadPdf'])->name('public.ticket.download');

// Portal Scanner Kru Venue Race Pack Collection (RPC)
Route::get('/crew', [CrewScannerController::class, 'index'])->name('crew.scanner.index');
Route::post('/crew/verify', [CrewScannerController::class, 'verifyQr'])->name('crew.scanner.verify');
Route::post('/crew/claim', [CrewScannerController::class, 'claimRpc'])->name('crew.scanner.claim');
