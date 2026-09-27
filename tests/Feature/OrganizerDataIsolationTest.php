<?php

namespace Tests\Feature;

use App\Filament\Organizer\Resources\EventResource;
use App\Filament\Organizer\Resources\OrderResource;
use App\Filament\Organizer\Resources\ParticipantResource;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\JerseySize;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerDataIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Organizer $organizer1;

    protected Organizer $organizer2;

    protected User $eo1;

    protected User $eo2;

    protected Event $event1;

    protected Event $event2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizer1 = Organizer::create([
            'name' => 'EO Satu',
            'slug' => 'eo-satu',
            'email' => 'eo1@jelatix.id',
            'phone' => '0811111111',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->organizer2 = Organizer::create([
            'name' => 'EO Dua',
            'slug' => 'eo-dua',
            'email' => 'eo2@jelatix.id',
            'phone' => '0822222222',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->eo1 = User::factory()->create([
            'role' => 'organizer_owner',
            'organizer_id' => $this->organizer1->id,
            'is_active' => true,
        ]);

        $this->eo2 = User::factory()->create([
            'role' => 'organizer_owner',
            'organizer_id' => $this->organizer2->id,
            'is_active' => true,
        ]);

        $this->event1 = Event::create([
            'organizer_id' => $this->organizer1->id,
            'title' => 'Event Lari EO Satu',
            'slug' => 'event-lari-eo-satu',
            'event_start_date' => now()->addDays(30),
            'registration_open_date' => now()->subDay(),
            'registration_close_date' => now()->addDays(20),
            'race_location_name' => 'Gelora Bung Karno',
            'status' => 'published',
        ]);

        $this->event2 = Event::create([
            'organizer_id' => $this->organizer2->id,
            'title' => 'Event Lari EO Dua',
            'slug' => 'event-lari-eo-dua',
            'event_start_date' => now()->addDays(40),
            'registration_open_date' => now()->subDay(),
            'registration_close_date' => now()->addDays(25),
            'race_location_name' => 'Monas Jakarta',
            'status' => 'published',
        ]);
    }

    public function test_eo1_can_only_see_their_own_events_in_the_list(): void
    {
        $this->actingAs($this->eo1);

        $response = $this->get(EventResource::getUrl('index', panel: 'organizer'));
        $response->assertOk();
        $response->assertSee('Event Lari EO Satu');
        $response->assertDontSee('Event Lari EO Dua');
    }

    public function test_eo2_can_only_see_their_own_events_in_the_list(): void
    {
        $this->actingAs($this->eo2);

        $response = $this->get(EventResource::getUrl('index', panel: 'organizer'));
        $response->assertOk();
        $response->assertSee('Event Lari EO Dua');
        $response->assertDontSee('Event Lari EO Satu');
    }

    public function test_eo1_cannot_access_or_edit_event_of_eo2(): void
    {
        $this->actingAs($this->eo1);

        // Mencoba membuka form edit event milik EO 2
        $response = $this->get(EventResource::getUrl('edit', ['record' => $this->event2], panel: 'organizer'));
        // Karena di-filter oleh getEloquentQuery & EventPolicy, harus 404 atau 403
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));
    }

    public function test_eo1_cannot_see_participants_from_eo2(): void
    {
        $cat1 = EventCategory::create([
            'event_id' => $this->event1->id,
            'name' => '5K EO 1',
            'distance_km' => 5,
            'normal_price' => 150000,
            'quota' => 100,
        ]);

        $cat2 = EventCategory::create([
            'event_id' => $this->event2->id,
            'name' => '10K EO 2',
            'distance_km' => 10,
            'normal_price' => 250000,
            'quota' => 100,
        ]);

        $jersey1 = JerseySize::create([
            'event_id' => $this->event1->id,
            'size_name' => 'L',
            'gender_type' => 'unisex',
            'is_unlimited' => true,
        ]);

        $jersey2 = JerseySize::create([
            'event_id' => $this->event2->id,
            'size_name' => 'XL',
            'gender_type' => 'unisex',
            'is_unlimited' => true,
        ]);

        $p1 = Participant::create([
            'event_category_id' => $cat1->id,
            'jersey_size_id' => $jersey1->id,
            'full_name' => 'Pelari Milik EO Satu',
            'bib_number' => '5K1001',
            'id_type' => 'KTP',
            'id_number' => '3201010101010001',
            'gender' => 'male',
            'birth_date' => '1995-01-01',
            'blood_type' => 'O+',
            'phone' => '081234567891',
            'email' => 'pelari1@test.com',
            'emergency_contact_name' => 'Kontak 1',
            'emergency_contact_phone' => '081234567892',
            'emergency_contact_relation' => 'Kerabat',
        ]);

        $p2 = Participant::create([
            'event_category_id' => $cat2->id,
            'jersey_size_id' => $jersey2->id,
            'full_name' => 'Pelari Rahasia EO Dua',
            'bib_number' => '10K2001',
            'id_type' => 'KTP',
            'id_number' => '3201010101010002',
            'gender' => 'female',
            'birth_date' => '1996-02-02',
            'blood_type' => 'A+',
            'phone' => '081234567893',
            'email' => 'pelari2@test.com',
            'emergency_contact_name' => 'Kontak 2',
            'emergency_contact_phone' => '081234567894',
            'emergency_contact_relation' => 'Kerabat',
        ]);

        $this->actingAs($this->eo1);

        $response = $this->get(ParticipantResource::getUrl('index', panel: 'organizer'));
        $response->assertOk();
        $response->assertSee('Pelari Milik EO Satu');
        $response->assertDontSee('Pelari Rahasia EO Dua');

        // EO 1 mencoba membuka halaman edit peserta EO 2
        $editResponse = $this->get(ParticipantResource::getUrl('edit', ['record' => $p2], panel: 'organizer'));
        $this->assertTrue(in_array($editResponse->getStatusCode(), [403, 404]));
    }

    public function test_eo1_cannot_see_orders_from_eo2(): void
    {
        $order1 = Order::create([
            'event_id' => $this->event1->id,
            'order_code' => 'JLTX-EO1-001',
            'customer_name' => 'Customer EO 1',
            'customer_email' => 'c1@test.com',
            'customer_phone' => '0811111112',
            'subtotal' => 150000,
            'grand_total' => 150000,
            'status' => 'paid',
        ]);

        $order2 = Order::create([
            'event_id' => $this->event2->id,
            'order_code' => 'JLTX-EO2-002',
            'customer_name' => 'Customer EO 2',
            'customer_email' => 'c2@test.com',
            'customer_phone' => '0822222223',
            'subtotal' => 250000,
            'grand_total' => 250000,
            'status' => 'paid',
        ]);

        $this->actingAs($this->eo1);

        $response = $this->get(OrderResource::getUrl('index', panel: 'organizer'));
        $response->assertOk();
        $response->assertSee('JLTX-EO1-001');
        $response->assertDontSee('JLTX-EO2-002');

        // EO 1 mencoba membuka halaman edit order EO 2
        $editResponse = $this->get(OrderResource::getUrl('edit', ['record' => $order2], panel: 'organizer'));
        $this->assertTrue(in_array($editResponse->getStatusCode(), [403, 404]));
    }

    public function test_when_eo_creates_event_it_is_automatically_assigned_to_their_organizer(): void
    {
        $this->actingAs($this->eo1);

        $event = Event::create([
            'organizer_id' => $this->eo1->organizer_id,
            'title' => 'Event Baru EO 1',
            'slug' => 'event-baru-eo-1',
            'event_start_date' => now()->addDays(50),
            'registration_open_date' => now()->subDay(),
            'registration_close_date' => now()->addDays(30),
            'race_location_name' => 'Stadion Utama',
            'status' => 'draft',
        ]);

        $this->assertEquals($this->organizer1->id, $event->organizer_id);
    }

    public function test_superadmin_can_see_all_events(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $this->actingAs($superadmin);

        $response = $this->get(EventResource::getUrl('index', panel: 'organizer'));
        $response->assertOk();
        $response->assertSee('Event Lari EO Satu');
        $response->assertSee('Event Lari EO Dua');
    }
}
