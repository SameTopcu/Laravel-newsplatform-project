<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->text('source_url')->nullable()->after('content');
            $table->string('source_url_hash', 64)->nullable()->unique()->after('source_url');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropUnique(['source_url_hash']);
            $table->dropColumn(['source_url', 'source_url_hash']);
        });
    }
};
