<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $t) {
            if (!Schema::hasColumn('quizzes', 'domain_allowlist')) {
                $t->json('domain_allowlist')->nullable()->after('settings');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $t) {
            if (Schema::hasColumn('quizzes', 'domain_allowlist')) {
                $t->dropColumn('domain_allowlist');
            }
        });
    }
};
