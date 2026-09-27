<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventCustomField;
use App\Models\JerseySize;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organizer;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Superadmin User
        $superadmin = User::firstOrCreate([
            'email' => 'admin@jelatix.id',
        ], [
            'name' => 'Superadmin Jelatix',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        // 2. Sample Organizer
        $organizer = Organizer::firstOrCreate([
            'slug' => 'nusantara-runners-eo',
        ], [
            'name' => 'Nusantara Athletics & Running Organizer',
            'email' => 'contact@nusantararunners.com',
            'phone' => '081298765432',
            'description' => 'Penyelenggara Lomba Lari Resmi Berstandar Nasional.',
            'bank_name' => 'BCA',
            'bank_account_number' => '8830123456',
            'bank_account_holder' => 'PT Nusantara Olahraga Jaya',
            'commission_rate' => 5.00,
            'is_verified' => true,
            'is_active' => true,
        ]);

        // 3. Organizer Owner & Crew
        $eoOwner = User::firstOrCreate([
            'email' => 'eo@jelatix.id',
        ], [
            'organizer_id' => $organizer->id,
            'name' => 'Budi Santoso (EO Director)',
            'password' => Hash::make('password'),
            'role' => 'organizer_owner',
            'phone' => '081211112222',
            'is_active' => true,
        ]);

        $scannerCrew = User::firstOrCreate([
            'email' => 'crew@jelatix.id',
        ], [
            'organizer_id' => $organizer->id,
            'name' => 'Kru Scanner Venue',
            'password' => Hash::make('password'),
            'role' => 'scanner_crew',
            'phone' => '081233334444',
            'is_active' => true,
        ]);

        // 4. Sample Event
        $event = Event::firstOrCreate([
            'slug' => 'jelatix-city-run-2026',
        ], [
            'organizer_id' => $organizer->id,
            'title' => 'Jelatix City Fun Run & 10K Championship 2026',
            'short_description' => 'Lomba lari perkotaan terbesar dengan rute ikonik dan festival kuliner sehat.',
            'full_description' => 'Ikuti keseruan Jelatix City Fun Run 2026 yang melewati landmark utama kota dengan medali finisher eksklusif.',
            'race_location_name' => 'Monumen Nasional (Monas), Jakarta Pusat',
            'race_location_address' => 'Jl. Silang Monas Barat Daya, Gambir, Jakarta Pusat',
            'race_location_map_url' => 'https://maps.google.com',
            'event_start_date' => now()->addDays(45)->setHour(5)->setMinute(30),
            'registration_open_date' => now()->subDays(5),
            'registration_close_date' => now()->addDays(30),
            'rpc_start_date' => now()->addDays(42),
            'rpc_end_date' => now()->addDays(44),
            'rpc_location' => 'Mall FX Sudirman Lt. 3, Jakarta',
            'waiver_content' => 'Dengan ini saya menyatakan sehat jasmani dan rohani serta menyetujui seluruh syarat & ketentuan lomba.',
            'auto_assign_bib' => true,
            'status' => 'published',
        ]);

        // 5. Categories (5K, 10K)
        $cat5k = EventCategory::firstOrCreate([
            'event_id' => $event->id,
            'name' => '5K Fun Run',
        ], [
            'distance_km' => 5.00,
            'normal_price' => 200000,
            'early_bird_price' => 150000,
            'early_bird_end_date' => now()->addDays(10),
            'quota' => 500,
            'slots_taken' => 2,
            'bib_prefix' => '5K',
            'bib_start_number' => 1001,
            'reserved_bib_numbers' => [1001, 1002, 1003, 8888], // Reservasi VVIP
            'cut_off_time_minutes' => 60,
            'min_age' => 10,
        ]);

        $cat10k = EventCategory::firstOrCreate([
            'event_id' => $event->id,
            'name' => '10K National Open',
        ], [
            'distance_km' => 10.00,
            'normal_price' => 300000,
            'early_bird_price' => 250000,
            'early_bird_end_date' => now()->addDays(10),
            'quota' => 300,
            'slots_taken' => 1,
            'bib_prefix' => '10K',
            'bib_start_number' => 10001,
            'reserved_bib_numbers' => [10001, 10002, 99999],
            'cut_off_time_minutes' => 120,
            'min_age' => 15,
        ]);

        // 6. Jersey Sizes (XS sampai 5XL)
        $sizes = [
            ['size_name' => 'XS', 'gender_type' => 'unisex', 'chest_width_cm' => 46, 'body_length_cm' => 66, 'stock' => 100],
            ['size_name' => 'S', 'gender_type' => 'unisex', 'chest_width_cm' => 48, 'body_length_cm' => 68, 'stock' => 100],
            ['size_name' => 'M', 'gender_type' => 'unisex', 'chest_width_cm' => 50, 'body_length_cm' => 70, 'stock' => 200],
            ['size_name' => 'L', 'gender_type' => 'unisex', 'chest_width_cm' => 52, 'body_length_cm' => 72, 'stock' => 200],
            ['size_name' => 'XL', 'gender_type' => 'unisex', 'chest_width_cm' => 54, 'body_length_cm' => 74, 'stock' => 150],
            ['size_name' => 'XXL', 'gender_type' => 'unisex', 'chest_width_cm' => 56, 'body_length_cm' => 76, 'stock' => 100],
            ['size_name' => '3XL', 'gender_type' => 'unisex', 'chest_width_cm' => 58, 'body_length_cm' => 78, 'stock' => 50],
            ['size_name' => '4XL', 'gender_type' => 'unisex', 'chest_width_cm' => 60, 'body_length_cm' => 80, 'stock' => 50],
            ['size_name' => '5XL', 'gender_type' => 'unisex', 'chest_width_cm' => 62, 'body_length_cm' => 82, 'stock' => 50],
        ];

        $sizeModels = [];
        foreach ($sizes as $s) {
            $sizeModels[$s['size_name']] = JerseySize::firstOrCreate([
                'event_id' => $event->id,
                'size_name' => $s['size_name'],
                'gender_type' => $s['gender_type'],
            ], $s);
        }

        // 7. Custom Fields
        EventCustomField::firstOrCreate([
            'event_id' => $event->id,
            'field_key' => 'shuttle_bus_point',
        ], [
            'label' => 'Titik Antar-Jemput Shuttle Bus',
            'field_type' => 'select',
            'options' => ['Stasiun MRT Bundaran HI', 'Stasiun KRL Tebet', 'Tidak Menggunakan Shuttle'],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        EventCustomField::firstOrCreate([
            'event_id' => $event->id,
            'field_key' => 'running_club',
        ], [
            'label' => 'Nama Komunitas Lari / Klub',
            'field_type' => 'text',
            'placeholder' => 'Misal: Indorunners, RIOT, dll (opsional)',
            'is_required' => false,
            'sort_order' => 2,
        ]);

        // 8. Sample Order 1 (Regular Runner - Lunas)
        $order1 = Order::firstOrCreate([
            'order_code' => 'JLTX-2026-0001',
        ], [
            'event_id' => $event->id,
            'customer_name' => 'Andi Pratama',
            'customer_email' => 'andi.pratama@gmail.com',
            'customer_phone' => '081234567001',
            'subtotal' => 150000,
            'platform_fee' => 5000,
            'grand_total' => 155000,
            'status' => 'paid',
            'tripay_reference' => 'TP-982348234',
            'tripay_payment_method' => 'QRIS',
            'paid_at' => now()->subHours(2),
        ]);

        OrderItem::firstOrCreate([
            'order_id' => $order1->id,
            'event_category_id' => $cat5k->id,
        ], [
            'quantity' => 1,
            'unit_price' => 150000,
            'total_price' => 150000,
        ]);

        Participant::firstOrCreate([
            'order_id' => $order1->id,
            'full_name' => 'Andi Pratama',
        ], [
            'event_category_id' => $cat5k->id,
            'jersey_size_id' => $sizeModels['L']->id,
            'bib_number' => '5K1004', // Auto-assigned melewati 1001-1003
            'bib_name' => 'ANDI P',
            'id_type' => 'KTP',
            'id_number' => '3171012345670001',
            'gender' => 'male',
            'birth_date' => '1995-05-14',
            'blood_type' => 'O+',
            'phone' => '081234567001',
            'email' => 'andi.pratama@gmail.com',
            'emergency_contact_name' => 'Dewi Lestari',
            'emergency_contact_phone' => '081234567999',
            'emergency_contact_relation' => 'Istri',
            'medical_conditions' => 'Tidak ada',
            'wave_group' => 'Wave A',
            'custom_fields_data' => [
                'shuttle_bus_point' => 'Stasiun MRT Bundaran HI',
                'running_club' => 'Indorunners Jakarta',
            ],
            'waiver_accepted' => true,
            'waiver_accepted_at' => now()->subHours(2),
            'waiver_ip_address' => '127.0.0.1',
            'qr_token' => 'qr_demo_token_andi_pratama_01',
            'is_rpc_claimed' => false,
        ]);

        // 9. Sample VVIP Runner (Nomor Cantik Khusus: 5K0001)
        Participant::firstOrCreate([
            'full_name' => 'Bambang Pamungkas',
            'event_category_id' => $cat5k->id,
        ], [
            'jersey_size_id' => $sizeModels['M']->id,
            'bib_number' => '5K0001',
            'is_vip' => true,
            'is_custom_bib' => true,
            'custom_bib_reason' => 'Tamu Kehormatan / Atlet Nasional',
            'bib_name' => 'BEPE 20',
            'id_type' => 'KTP',
            'id_number' => '3171029988770002',
            'gender' => 'male',
            'birth_date' => '1980-06-10',
            'blood_type' => 'A+',
            'phone' => '081199998888',
            'email' => 'bepe@legend.id',
            'emergency_contact_name' => 'Tribuana Tungga Dewi',
            'emergency_contact_phone' => '081199998889',
            'emergency_contact_relation' => 'Istri',
            'medical_conditions' => 'Kondisi fisik prima',
            'wave_group' => 'Wave VIP',
            'custom_fields_data' => [
                'shuttle_bus_point' => 'Tidak Menggunakan Shuttle',
                'running_club' => 'VIP Ambassador',
            ],
            'waiver_accepted' => true,
            'waiver_accepted_at' => now(),
            'waiver_ip_address' => '127.0.0.1',
            'qr_token' => 'qr_demo_token_bepe_vvip_99',
            'is_rpc_claimed' => false,
        ]);
    }
}
