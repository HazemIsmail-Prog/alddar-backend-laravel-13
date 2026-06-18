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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained();
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->enum('entry_type', ['debit', 'credit']);
            $table->integer('amount'); // in cents
            $table->text('description')->nullable();
            $table->nullableMorphs('reference'); // polymorphic: invoices, payments, etc.
            $table->timestamps();
            
            $table->index('journal_id');
            $table->index('account_id');
            $table->index('entry_type');
            // $table->string('reference_number')->nullable();
            // $table->json('metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
