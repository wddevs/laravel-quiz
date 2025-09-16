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
        Schema::create('quiz_submissions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $t->uuid('uuid')->unique();

            $t->string('status', 32)->default('new'); // new|viewed|...
            $t->boolean('paid')->default(false);
            $t->timestamp('viewed_at')->nullable();

            // контактні поля (для пошуку/відображення)
            $t->string('contact_name')->nullable();
            $t->string('contact_phone')->nullable();
            $t->string('contact_email')->nullable();
            $t->string('contact_text')->nullable();

            // поля для інфо/фільтрів
            $t->string('ip', 45)->nullable();
            $t->string('referrer', 1024)->nullable();
            $t->string('source_url', 1024)->nullable();
            $t->string('country', 64)->nullable();
            $t->string('city', 128)->nullable();
            $t->unsignedSmallInteger('discount_percent')->nullable();

            $t->json('answers'); // сирий масив [{q,t,a},...]
            $t->json('extra')->nullable();
            $t->json('result')->nullable();

            $t->timestamps();

            $t->index(['quiz_id','status']);
            $t->index('created_at');
            $t->index('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_submissions');
    }
};
