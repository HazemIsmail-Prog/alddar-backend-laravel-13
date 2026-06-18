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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('technician_id')->nullable()->constrained('users');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->foreignId('phone_id')->nullable()->constrained('phones');
            $table->enum('order_type', ['sales', 'purchase']);
            $table->string('order_number')->unique();
            $table->foreignId('party_id')->constrained('parties');
            $table->date('order_date');
            $table->foreignId('status_id')->constrained('order_statuses');
            $table->enum('payment_status', ['pending', 'partially_paid', 'paid', 'overdue'])->default('pending');
            $table->integer('subtotal')->default(0); // in cents
            $table->integer('discount_amount')->default(0); // in cents
            $table->integer('total_amount')->default(0); // in cents
            $table->integer('amount_paid')->default(0); // in cents
            $table->text('notes')->nullable();
            $table->boolean('is_confirmed_to_dispatch')->default(false);
            $table->boolean('is_inprogress')->default(false);
            // cancelled_at
            $table->dateTime('cancelled_at')->nullable();
            // completed_at
            $table->dateTime('completed_at')->nullable();
            // sort_number
            $table->double('sort_number')->default(0);
            $table->timestamps();
            
            // $table->softDeletes();
            // $table->string('reference_number')->nullable();
            // $table->date('required_date');
            // $table->date('shipped_date')->nullable();
            // $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            // $table->integer('tax_amount')->default(0); // in cents
            // $table->integer('shipping_cost')->default(0); // in cents
            // $table->string('currency', 3)->default('USD');
            // $table->integer('exchange_rate')->default(10000); // in basis points
            // $table->text('shipping_address')->nullable();
            // $table->text('billing_address')->nullable();
            // $table->text('terms_and_conditions')->nullable();
            // $table->json('attachments')->nullable();

            // $table->index(['order_number', 'status']);
            // $table->index(['client_id', 'status']);
            $table->index(['payment_status', 'order_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
