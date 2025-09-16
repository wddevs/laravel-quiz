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
        Schema::create('quiz_event_uniques', function (Blueprint $t) {
            $t->id();
            $t->date('date');
            $t->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $t->string('project_domain', 255)->nullable();
            $t->string('type', 16);              // impression|open|lead
            $t->string('visitor_id', 64);        // vid (NOT NULL)
            $t->string('page_hash', 64)->default(''); // not null '' щоб unique працював
            $t->timestamps();

            // один і той самий відвідувач не може «додати» більше 1 події в день
            $t->unique(['date','quiz_id','project_domain','type','visitor_id','page_hash'], 'uq_event_unique');
            $t->index(['quiz_id','date','type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_event_uniques');
    }
};
