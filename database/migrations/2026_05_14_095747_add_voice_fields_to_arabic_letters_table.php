<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arabic_letters', function (Blueprint $table) {
            $table->string('transliteration')->nullable()->after('sound'); // phonetic in Latin script
            $table->string('audio_url')->nullable()->after('transliteration'); // manual override URL
        });
    }

    public function down(): void
    {
        Schema::table('arabic_letters', function (Blueprint $table) {
            $table->dropColumn(['transliteration', 'audio_url']);
        });
    }
};
