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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->string('invoice_number')->unique();
            $table->nullableMorphs('reference'); // polymorphic: purchase_orders, sales_orders, stock_transfers, adjustments
            $table->string('invoice_type'); // sales, purchase, credit_note, debit_note
            $table->foreignId('party_id')->constrained('parties');
            $table->date('invoice_date');
            $table->string('status'); // draft, sent, approved, partially_paid, paid, overdue, cancelled
            $table->integer('subtotal')->default(0); // in cents
            $table->integer('discount_amount')->default(0); // in cents
            $table->integer('total_amount')->default(0); // in cents
            $table->integer('amount_paid')->default(0); // in cents
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // $table->date('due_date');
            // $table->date('paid_date')->nullable();
            // $table->integer('tax_amount')->default(0); // in cents
            // $table->integer('shipping_cost')->default(0); // in cents
            // $table->string('currency', 3)->default('USD');
            // $table->integer('exchange_rate')->default(10000); // in basis points
            // $table->text('terms_and_conditions')->nullable();
            // $table->json('attachments')->nullable();
            // $table->timestamp('approved_at')->nullable();

            $table->index(['invoice_number', 'status']);
            $table->index(['party_id', 'invoice_type']);
            // $table->index(['invoice_date', 'due_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
