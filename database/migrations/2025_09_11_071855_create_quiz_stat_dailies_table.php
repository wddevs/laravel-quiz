<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('quiz_stats_daily', function (Blueprint $t) {
            $t->id();
            $t->date('date');
            $t->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $t->string('project_domain', 255)->nullable();
            $t->unsignedBigInteger('impressions')->default(0);
            $t->unsignedBigInteger('opens')->default(0);
            $t->unsignedBigInteger('leads')->default(0);
            $t->timestamps();

            $t->unique(['date','quiz_id','project_domain'],'uq_stats_day_quiz_domain');
            $t->index(['quiz_id','date']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('quiz_stats_daily');
    }
};
