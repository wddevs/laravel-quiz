<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // опційно — до чого “причеплений” файл
            $table->nullableMorphs('assetable');

            // де і що лежить
            $table->string('disk', 32)->default('public');
            $table->string('path');                // ЗБЕРІГАЄМО ВІДНОСНИЙ ШЛЯХ (без APP_URL)
            $table->string('original_name');
            $table->string('mime_type', 128)->nullable();
            $table->string('extension', 16)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('collection', 64)->nullable(); // наприклад: 'quiz', 'avatar'
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('assets');
    }
};
