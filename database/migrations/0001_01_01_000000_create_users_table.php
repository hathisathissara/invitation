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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role',['admin','couple'])->default('couple');
            $table->enum('status',['pending','active'])->default('pending');
            $table->string('payment_slip')->nullable();
            $table->dateTime('deletion_notice_sent_at')->nullable();
            $table->dateTime('refund_requested_at')->nullable();
            $table->enum('refund_status', ['none', 'pending', 'approved', 'details_submitted', 'rejected', 'completed'])->default('none');
            $table->text('refund_bank_details')->nullable();
            $table->text('refund_reason')->nullable();
            $table->enum('package', ['basic', 'standard', 'premium'])->default('basic');
            $table->boolean('has_guest_gallery')->default(0);
            $table->string('upgrade_slip')->nullable();
            $table->string('pending_upgrade_plan', 100)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
