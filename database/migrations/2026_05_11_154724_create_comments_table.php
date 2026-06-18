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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->foreignId('created_by')->constrained('users');
            $table->string('type', 32);
            $table->longText('body')->nullable();
            $table->string('media_disk')->nullable();
            $table->string('media_path')->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['commentable_type', 'commentable_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
