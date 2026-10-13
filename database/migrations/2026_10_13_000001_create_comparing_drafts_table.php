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
        Schema::create('comparing_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('company_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('batch_no')->nullable();
            $table->string('date_delivered')->nullable();
            $table->string('status')->default('GOOD');
            $table->boolean('sound_enabled')->default(true);
            $table->longText('rows_data')->nullable();
            $table->unsignedInteger('verified_count')->default(0);
            $table->unsignedInteger('total_rows')->default(0);
            $table->timestamps();

            $table->index(['company_name']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparing_drafts');
    }
};
