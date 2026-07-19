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
        Schema::create('weddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('bride_name', 100)->nullable();
            $table->string('groom_name', 100)->nullable();
            $table->date('wedding_date')->nullable();
            $table->string('venue')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('love_story')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('template_name', 100)->default('default');
            $table->string('invite_language', 5)->default('en')->comment('en, si, ta');
            $table->string('music_track', 50)->nullable()->comment('preset key');
            $table->string('slug')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weddings');
    }
};
