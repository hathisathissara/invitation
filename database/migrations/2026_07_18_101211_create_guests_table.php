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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->string('name', 150)->nullable();
            $table->string('whatsapp_number', 20)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('side', 50)->nullable();
            $table->boolean('is_opened')->default(0);
            $table->dateTime('opened_at')->nullable();
            $table->enum('rsvp_status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('guest_note')->nullable();
            $table->integer('seats_reserved')->default(1);
            $table->boolean('is_sent')->default(0);
            $table->dateTime('sent_at')->nullable();
            $table->string('invite_token', 20)->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
