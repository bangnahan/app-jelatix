<?php

use App\Models\Event;
use App\Models\JerseySize;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $standardSizes = [
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

        $events = Event::all();
        foreach ($events as $event) {
            foreach ($standardSizes as $sizeData) {
                JerseySize::firstOrCreate(
                    [
                        'event_id' => $event->id,
                        'size_name' => $sizeData['size_name'],
                        'gender_type' => $sizeData['gender_type'],
                    ],
                    [
                        'chest_width_cm' => $sizeData['chest_width_cm'],
                        'body_length_cm' => $sizeData['body_length_cm'],
                        'stock' => $sizeData['stock'],
                        'allocated_stock' => 0,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Keep jersey sizes to preserve foreign keys
    }
};
