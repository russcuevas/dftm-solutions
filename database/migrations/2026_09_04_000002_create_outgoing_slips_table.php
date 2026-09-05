<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outgoing_slips', function (Blueprint $table) {
            $table->id();
            $table->string('slip_no')->nullable()->index();
            $table->string('company_name')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->date('date_delivered')->nullable();
            $table->date('date_released')->nullable();
            $table->string('si_number')->nullable()->index();
            $table->string('dr_number')->nullable()->index();
            $table->string('batch_no')->nullable();
            $table->text('item_description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('box_no')->nullable();
            $table->integer('total_quantity')->default(0)->nullable();
            $table->string('status')->default('RELEASED')->nullable(); // REPAIRED, BER, GOOD, RELEASED
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('encoded_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outgoing_slips');
    }
};
