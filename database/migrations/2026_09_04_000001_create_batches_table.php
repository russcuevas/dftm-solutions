<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('slip_no')->nullable()->index();
            $table->string('batch_no')->nullable()->index();
            $table->string('company_name')->nullable();
            $table->date('date_delivered')->nullable();
            $table->text('item_description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->integer('total_quantity')->default(0)->nullable();
            $table->integer('in_stock_quantity')->default(0)->nullable();
            $table->integer('outgoing_quantity')->default(0)->nullable();
            $table->string('status')->default('IN_STOCK')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('encoded_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
