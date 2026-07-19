<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Couple Gallery එකට
        Schema::table('galleries', function (Blueprint $table) {
            $table->string('public_id')->nullable()->after('image_path');
        });

        // 2. Guest Gallery එකට
        Schema::table('guest_galleries', function (Blueprint $table) {
            $table->string('public_id')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
        Schema::table('guest_galleries', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
    }
};
