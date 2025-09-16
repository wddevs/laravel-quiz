<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $t) {
            // нове поле після uuid (працює в MySQL; у SQLite "after" ігнорується)
            $t->string('visitor_id', 64)->nullable()->after('uuid');
            $t->index('visitor_id');

            // приклад: додати soft deletes (опціонально)
            // $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $t) {
            // спочатку прибираємо індекс, потім колонку
            $t->dropIndex(['visitor_id']); // або $t->dropIndex('quiz_submissions_visitor_id_index');
            $t->dropColumn('visitor_id');

            // якщо додавали soft deletes:
            // $t->dropSoftDeletes();
        });
    }
};
