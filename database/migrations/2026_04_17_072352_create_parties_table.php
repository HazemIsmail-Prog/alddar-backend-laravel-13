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
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->string('name');
            $table->boolean('is_vendor')->default(false);
            $table->boolean('is_client')->default(false);
            $table->enum('status', ['active', 'inactive', 'blocked', 'pending'])->default('active');
            $table->integer('balance')->default(0); // in cents
            $table->timestamps();
            $table->softDeletes();

            // $table->string('legal_name')->nullable();
            // $table->string('email')->nullable();
            // $table->string('phone')->nullable();
            // $table->string('mobile')->nullable();
            // $table->string('fax')->nullable();
            // $table->string('website')->nullable();
            // $table->string('tax_number')->nullable();
            // $table->string('registration_number')->nullable();
            // $table->enum('tax_type', ['registered', 'unregistered', 'consumer', 'special'])->default('registered');
            // $table->enum('payment_terms', ['immediate', 'net_15', 'net_30', 'net_45', 'net_60'])->default('net_30');
            // $table->integer('credit_limit')->nullable(); // in cents
            // $table->integer('credit_days')->default(30);
            // $table->string('currency', 3)->default('USD');
            // $table->json('bank_details')->nullable();
            // $table->text('notes')->nullable();

            $table->index(['is_vendor', 'is_client', 'status']);
            $table->index('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
