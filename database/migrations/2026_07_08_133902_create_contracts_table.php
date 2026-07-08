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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('party_id')->constrained('parties');
            
            $table->string('contract_number')->unique();
            $table->string('contract_type');

            $table->integer('contract_value');

            $table->date('contract_date');
            $table->date('contract_expiration_date');
            
            $table->date('compressor_warranty_start_date')->nullable();
            $table->date('compressor_warranty_end_date')->nullable();
            
            $table->decimal('parts_status', 10, 2)->nullable();

            $table->string('contract_status');
            $table->string('contract_payment_status');
            $table->longText('contract_details')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
