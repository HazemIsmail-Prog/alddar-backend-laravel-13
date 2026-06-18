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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->string('payment_number')->unique();
            $table->enum('payment_type', ['receipt', 'payment']);
            $table->foreignId('party_id')->constrained('parties');
            $table->foreignId('invoice_id')->nullable()->constrained();
            $table->foreignId('bank_account_id')->nullable()->constrained('chart_of_accounts');
            $table->date('payment_date');
            $table->integer('amount'); // in cents
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'credit_card', 'debit_card', 'online']);
            $table->string('reference_number')->nullable();
            $table->string('check_number')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->timestamps();
            
            // $table->json('attachments')->nullable();
            // $table->timestamp('approved_at')->nullable();

            $table->index(['payment_number', 'status']);
            $table->index(['party_id', 'payment_type']);
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
