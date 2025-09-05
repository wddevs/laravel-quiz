<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('order')->default(1);
            $t->string('title');
            $t->string('type', 32); // <-- string, НЕ enum
            $t->boolean('required')->default(false);
            $t->string('help_text')->nullable();
            $t->string('image_path')->nullable(); // optional ілюстрація питання
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
