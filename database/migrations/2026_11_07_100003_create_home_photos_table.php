<?php

use App\Enums\HomePhotoSlotEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Die drei Fotos unter der Speisekarte – je Platz eine feste Zeile.
     */
    public function up(): void
    {
        Schema::create('home_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('slot')->unique();
            $table->string('caption', 80)->nullable();
            $table->timestamps();
        });

        DB::table('home_photos')->insert(array_map(fn (HomePhotoSlotEnum $slot): array => [
            'slot' => $slot->value,
            'caption' => $slot->defaultCaption(),
            'created_at' => now(),
            'updated_at' => now(),
        ], HomePhotoSlotEnum::cases()));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_photos');
    }
};
