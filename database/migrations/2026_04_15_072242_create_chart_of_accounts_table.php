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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->string('account_code', 50)->unique();
            $table->string('account_name');
            $table->enum('account_type', [
                'asset', 'liability', 'equity', 'income', 'expense',
                'cost_of_goods_sold', 'bank', 'cash', 'accounts_receivable',
                'accounts_payable', 'inventory',
            ]);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->integer('level')->default(0);
            $table->boolean('is_leaf')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system_account')->default(false);
            $table->integer('opening_balance')->default(0); // in cents
            $table->date('opening_balance_date')->nullable();
            $table->integer('current_balance')->default(0); // in cents
            $table->text('description')->nullable();
            // $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['account_type', 'is_active']);
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
