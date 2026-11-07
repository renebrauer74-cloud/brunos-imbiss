<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Einstellungen der Website (Kontakt, Öffnungszeiten, Texte) – genau eine Zeile.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_title', 120);
            $table->string('tagline', 160)->nullable();
            $table->string('slogan', 160)->nullable();
            $table->string('notice', 240)->nullable();
            $table->string('street', 120)->nullable();
            $table->string('zip_city', 120)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 120)->nullable();
            $table->json('opening_hours')->nullable();
            $table->string('hours_note', 300)->nullable();
            $table->text('about')->nullable();
            $table->text('imprint')->nullable();
            $table->text('privacy')->nullable();
            $table->timestamps();
        });

        DB::table('site_settings')->insert([
            'site_title' => "Bruno's Imbiss",
            'tagline' => 'Currywurst, Pommes & Schnitzel in Bochum',
            'slogan' => '… immer frisch, immer lecker!',
            'zip_city' => 'Bochum',
            'about' => "Currywurst, Pommes, Schnitzel und Frikadellen – frisch zubereitet und mit ordentlich Ruhrpott-Herz.\nBei Bruno in Bochum gibt's ehrlichen Imbiss-Genuss ohne Schnickschnack. Komm vorbei, wir freuen uns auf dich!",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
