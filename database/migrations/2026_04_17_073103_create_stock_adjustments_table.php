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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->string('adjustment_number')->unique();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->enum('adjustment_type', ['positive', 'negative', 'damage', 'loss', 'found', 'expired']);
            $table->enum('status', ['draft', 'approved', 'completed', 'cancelled'])->default('draft');
            $table->date('adjustment_date');
            $table->text('reason');
            $table->json('documents')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['adjustment_number', 'status']);
            $table->index('adjustment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
