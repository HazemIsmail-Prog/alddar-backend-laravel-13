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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->string('journal_number')->unique();
            $table->enum('journal_type', [
                'general', 'sales', 'purchase', 'cash_receipt', 'cash_payment',
                'bank_deposit', 'bank_withdrawal', 'credit_note', 'debit_note',
                'inventory_adjustment',
            ]);
            $table->date('journal_date');
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->integer('total_debit')->default(0); // in cents
            $table->integer('total_credit')->default(0); // in cents
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->nullableMorphs('reference'); // polymorphic: invoices, purchase_orders, sales_orders, etc.
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            
            $table->index(['journal_number', 'status']);
            $table->index(['journal_type', 'journal_date']);
            $table->index('status');
            // $table->string('currency', 3)->default('USD');
            // $table->integer('exchange_rate')->default(10000); // in basis points
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
