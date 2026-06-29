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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained();
            $table->foreignId('product_id')->nullable()->constrained();
            $table->foreignId('warehouse_id')->nullable()->constrained();
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->string('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->integer('unit_price'); // in cents
            $table->integer('discount_amount')->default(0); // in cents
            $table->integer('total_price'); // in cents
            $table->timestamps();

            // $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            // $table->integer('tax_rate')->default(0); // in basis points
            // $table->integer('tax_amount')->default(0); // in cents
            // $table->integer('discount_rate')->default(0); // in basis points
            // $table->json('metadata')->nullable();

            $table->index('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
