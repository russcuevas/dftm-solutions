<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id')->nullable()->index();
            $table->unsignedBigInteger('outgoing_slip_id')->nullable()->index();
            $table->integer('item_no')->nullable(); // 1, 2, 3...
            $table->string('brand')->nullable()->index();
            $table->string('model')->nullable()->index();
            $table->string('serial_number')->nullable()->index();
            $table->string('mac_address')->nullable()->index();
            $table->string('box_no')->nullable();
            $table->text('technical_diagnostic')->nullable(); // e.g. NO POWER, CORRODED BOARD
            $table->text('replace_parts')->nullable(); // e.g. SMD CAPACITOR, N/A
            $table->string('repair_status')->default('PENDING')->nullable(); // PENDING, REPAIRED, BER, GOOD
            $table->string('stock_status')->default('IN_STOCK')->nullable(); // IN_STOCK, OUTGOING, RELEASED
            $table->string('company_name')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_contact')->nullable();
            $table->string('si_number')->nullable();
            $table->string('dr_number')->nullable();
            $table->date('date_delivered')->nullable();
            $table->date('date_outgoing')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('encoded_by')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('outgoing_slip_id')->references('id')->on('outgoing_slips')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
