<?php

use App\Http\Controllers\CrewScannerController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\PublicTicketController;
use App\Models\Event;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $events = Event::where('status', 'published')
        ->with(['categories', 'organizer'])
        ->orderBy('event_start_date', 'asc')
        ->get();

    return view('public.home', compact('events'));
})->name('home');

// Pendaftaran Lomba Lari & Checkout Tripay
Route::get('/events/{slug}/register', [PublicRegistrationController::class, 'show'])->name('public.register.show');
Route::post('/events/{slug}/checkout', [PublicRegistrationController::class, 'checkout'])->name('public.register.checkout');
Route::get('/orders/{order_code}', [PublicRegistrationController::class, 'showInvoice'])->name('public.order.show');
Route::get('/orders/{order_code}/status', [PublicRegistrationController::class, 'checkStatus'])->name('public.order.status');
Route::post('/orders/{order_code}/simulate', [PublicRegistrationController::class, 'simulatePay'])->name('public.order.simulate');

// Fitur Mandiri Pelari: Cek Tiket & Unduh E-Ticket
Route::get('/cek-tiket', [PublicTicketController::class, 'index'])->name('public.ticket.index');
Route::post('/cek-tiket', [PublicTicketController::class, 'search'])->name('public.ticket.search');
Route::get('/ticket/{token}/download', [PublicTicketController::class, 'downloadPdf'])->name('public.ticket.download');

// Portal Scanner Kru Venue Race Pack Collection (RPC)
Route::get('/crew', [CrewScannerController::class, 'index'])->name('crew.scanner.index');
Route::post('/crew/verify', [CrewScannerController::class, 'verifyQr'])->name('crew.scanner.verify');
Route::post('/crew/claim', [CrewScannerController::class, 'claimRpc'])->name('crew.scanner.claim');
