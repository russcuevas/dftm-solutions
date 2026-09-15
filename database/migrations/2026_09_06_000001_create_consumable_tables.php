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
        // Consumable Categories table
        Schema::create('consumable_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Consumable Items table
        Schema::create('consumable_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('consumable_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('unit')->default('PCS');
            $table->decimal('cost', 12, 2)->default(0.00);
            $table->integer('min_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Consumable Monthly Stocks (for tracking beginning stocks per month/year)
        Schema::create('consumable_monthly_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumable_item_id')->constrained('consumable_items')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month'); // 1 to 12
            $table->integer('beginning_stock')->default(0);
            $table->timestamps();

            $table->unique(['consumable_item_id', 'year', 'month'], 'item_year_month_unique');
        });

        // Consumable Daily Logs (daily stock in / stock out transactions)
        Schema::create('consumable_daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumable_item_id')->constrained('consumable_items')->onDelete('cascade');
            $table->date('log_date');
            $table->integer('in_qty')->default(0);
            $table->integer('out_qty')->default(0);
            $table->string('reference_no')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['consumable_item_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumable_daily_logs');
        Schema::dropIfExists('consumable_monthly_stocks');
        Schema::dropIfExists('consumable_items');
        Schema::dropIfExists('consumable_categories');
    }
};
