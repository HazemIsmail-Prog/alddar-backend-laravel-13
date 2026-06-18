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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            // $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description')->nullable();
            $table->integer('quantity');
            $table->integer('unit_price'); // in cents
            $table->integer('discount_amount')->default(0); // in cents
            $table->integer('total_amount'); // in cents
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('product_id');
            
            // $table->integer('shipped_quantity')->default(0);
            // $table->integer('tax_rate')->default(0); // in basis points
            // $table->integer('tax_amount')->default(0); // in cents
            // $table->integer('discount_rate')->default(0); // in basis points
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
