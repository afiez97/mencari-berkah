<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arabic_letters', function (Blueprint $table) {
            $table->id();
            $table->integer('order_num');
            $table->string('arabic');
            $table->string('name');
            $table->string('sound');
            $table->string('group_name');
            $table->text('mnemonic');
            $table->text('visual_description');
            $table->string('form_isolated');
            $table->string('form_initial');
            $table->string('form_medial');
            $table->string('form_final');
            $table->string('example_word');
            $table->string('example_translation');
            $table->integer('difficulty')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arabic_letters');
    }
};
