<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create transmittals table for Incoming
        if (!Schema::hasTable('transmittals')) {
            Schema::create('transmittals', function (Blueprint $table) {
                $table->id();
                $table->string('transmittal_no')->unique()->index();
                $table->string('company_name')->nullable();
                $table->date('date_received')->nullable();
                $table->string('brand')->nullable();
                $table->string('model')->nullable();
                $table->string('status')->nullable();
                $table->integer('total_quantity')->default(0);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('encoded_by')->nullable();
                $table->timestamps();
            });
        }

        // 2. Add transmittal_id to inventory_items
        if (!Schema::hasColumn('inventory_items', 'transmittal_id')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->unsignedBigInteger('transmittal_id')->nullable()->after('id')->index();
                $table->foreign('transmittal_id')->references('id')->on('transmittals')->onDelete('cascade');
            });
        }

        // 3. Migrate existing batches into transmittals if any
        if (Schema::hasTable('batches')) {
            $existingBatches = DB::table('batches')->get();
            foreach ($existingBatches as $b) {
                $transmittalNo = $b->slip_no ?: ('TR-' . date('Ymd') . '-' . str_pad($b->id, 3, '0', STR_PAD_LEFT));
                // Replace IRS- prefix if present with TR-
                if (str_starts_with($transmittalNo, 'IRS-')) {
                    $transmittalNo = 'TR-' . substr($transmittalNo, 4);
                }

                $transmittalId = DB::table('transmittals')->insertGetId([
                    'transmittal_no' => $transmittalNo,
                    'company_name' => $b->company_name,
                    'date_received' => $b->date_delivered,
                    'brand' => $b->brand,
                    'model' => $b->model,
                    'status' => $b->status,
                    'total_quantity' => $b->total_quantity ?? 0,
                    'notes' => $b->notes,
                    'encoded_by' => $b->encoded_by,
                    'created_at' => $b->created_at ?? now(),
                    'updated_at' => $b->updated_at ?? now(),
                ]);

                // Point items of this batch to the transmittal as well
                DB::table('inventory_items')
                    ->where('batch_id', $b->id)
                    ->whereNull('transmittal_id')
                    ->update(['transmittal_id' => $transmittalId]);
            }
        }

        // 4. In batches table: make sure batch_no is well supported and slip_no can be nullable
        if (Schema::hasTable('batches')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->string('slip_no')->nullable()->change();
                $table->string('batch_no')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inventory_items', 'transmittal_id')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->dropForeign(['transmittal_id']);
                $table->dropColumn('transmittal_id');
            });
        }

        Schema::dropIfExists('transmittals');
    }
};
