<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\OutgoingSlip;
use App\Models\ActivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@dftm.com'],
            [
                'name' => 'System Administrator',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '09171234567',
                'status' => 'active',
            ]
        );

        $encoder = User::firstOrCreate(
            ['email' => 'encoder@dftm.com'],
            [
                'name' => 'Lead Data Encoder',
                'username' => 'encoder',
                'password' => Hash::make('encoder123'),
                'role' => 'encoder',
                'phone' => '09187654321',
                'status' => 'active',
            ]
        );

        // 2. Create Sample Incoming Batch 1 (Huawei)
        $batch1 = Batch::create([
            'slip_no' => 'IRS-' . date('Ymd') . '-001',
            'batch_no' => 'BATCH 1',
            'company_name' => 'DFTM DIGITAL SOLUTIONS / CONVERGE ICT',
            'date_delivered' => now()->subDays(5)->format('Y-m-d'),
            'item_description' => 'HUAWEI GPON ONT WIFI ROUTER',
            'brand' => 'HUAWEI',
            'model' => 'EG8145V5',
            'total_quantity' => 10,
            'in_stock_quantity' => 8,
            'outgoing_quantity' => 2,
            'status' => 'PARTIAL',
            'notes' => 'Received from Central Warehouse in good box packaging.',
            'encoded_by' => $encoder->id,
        ]);

        $samplesHuawei = [
            ['sn' => '48575443F8A12301', 'mac' => '00:1A:2B:3C:4D:01', 'diag' => 'NO POWER', 'part' => 'SMD CAPACITOR', 'status' => 'Repaired', 'box' => 'BOX-01', 'stock' => 'IN_STOCK'],
            ['sn' => '48575443F8A12302', 'mac' => '00:1A:2B:3C:4D:02', 'diag' => 'CORRODED BOARD', 'part' => 'N/A', 'status' => 'BER', 'box' => 'BOX-01', 'stock' => 'IN_STOCK'],
            ['sn' => '48575443F8A12303', 'mac' => '00:1A:2B:3C:4D:03', 'diag' => 'FIRMWARE CRASH / RED LOS', 'part' => 'REFLASH EEPROM', 'status' => 'Repaired', 'box' => 'BOX-01', 'stock' => 'IN_STOCK'],
            ['sn' => '48575443F8A12304', 'mac' => '00:1A:2B:3C:4D:04', 'diag' => 'OPTICAL RX LOSS HIGH', 'part' => 'BOSA MODULE', 'status' => 'Repaired', 'box' => 'BOX-01', 'stock' => 'IN_STOCK'],
            ['sn' => '48575443F8A12305', 'mac' => '00:1A:2B:3C:4D:05', 'diag' => 'DEFECTIVE LAN 1 & 2', 'part' => 'PULSE TRANSFORMER', 'status' => 'Repaired', 'box' => 'BOX-01', 'stock' => 'IN_STOCK'],
            ['sn' => '48575443F8A12306', 'mac' => '00:1A:2B:3C:4D:06', 'diag' => 'BURNT MAIN IC', 'part' => 'N/A', 'status' => 'BER', 'box' => 'BOX-02', 'stock' => 'IN_STOCK'],
            ['sn' => '48575443F8A12307', 'mac' => '00:1A:2B:3C:4D:07', 'diag' => 'WIFI 5GHZ NOT BROADCASTING', 'part' => 'WIFI POWER AMPLIFIER', 'status' => 'Repaired', 'box' => 'BOX-02', 'stock' => 'IN_STOCK'],
            ['sn' => '48575443F8A12308', 'mac' => '00:1A:2B:3C:4D:08', 'diag' => 'INITIAL INCOMING - FOR DIAGNOSTICS', 'part' => '', 'status' => 'In process', 'box' => 'BOX-02', 'stock' => 'IN_STOCK'],
            ['sn' => '48575443F8A12309', 'mac' => '00:1A:2B:3C:4D:09', 'diag' => 'ADAPTER PORT LOOSE', 'part' => 'DC JACK REPLACEMENT', 'status' => 'Repaired', 'box' => 'BOX-02', 'stock' => 'RELEASED'],
            ['sn' => '48575443F8A12310', 'mac' => '00:1A:2B:3C:4D:10', 'diag' => 'NO 2.4GHZ SIGNAL', 'part' => 'RF FILTER', 'status' => 'Repaired', 'box' => 'BOX-02', 'stock' => 'RELEASED'],
        ];

        // 3. Create Sample Outgoing Slip
        $outgoing1 = OutgoingSlip::create([
            'slip_no' => 'ORS-' . date('Ymd') . '-001',
            'company_name' => 'DFTM DIGITAL SOLUTIONS',
            'customer_name' => 'PLDT / Smart Field Operations',
            'contact_number' => '09201234567',
            'date_delivered' => now()->subDays(5)->format('Y-m-d'),
            'date_released' => now()->subDays(1)->format('Y-m-d'),
            'si_number' => 'SI-99882',
            'dr_number' => 'DR-44510',
            'batch_no' => 'BATCH 1',
            'item_description' => 'HUAWEI GPON ONT WIFI ROUTER',
            'brand' => 'HUAWEI',
            'model' => 'EG8145V5',
            'box_no' => 'BOX-02',
            'total_quantity' => 2,
            'status' => 'Repaired',
            'notes' => 'Tested 100% OK prior to release with standard warranty.',
            'encoded_by' => $admin->id,
        ]);

        foreach ($samplesHuawei as $index => $item) {
            $isReleased = $item['stock'] === 'RELEASED';
            InventoryItem::create([
                'batch_id' => $batch1->id,
                'outgoing_slip_id' => $isReleased ? $outgoing1->id : null,
                'item_no' => $index + 1,
                'brand' => 'HUAWEI',
                'model' => 'EG8145V5',
                'serial_number' => $item['sn'],
                'mac_address' => $item['mac'],
                'box_no' => $item['box'],
                'technical_diagnostic' => $item['diag'],
                'replace_parts' => $item['part'],
                'repair_status' => $item['status'],
                'stock_status' => $item['stock'],
                'company_name' => $batch1->company_name,
                'customer_name' => $isReleased ? $outgoing1->customer_name : null,
                'customer_contact' => $isReleased ? $outgoing1->contact_number : null,
                'si_number' => $isReleased ? $outgoing1->si_number : null,
                'dr_number' => $isReleased ? $outgoing1->dr_number : null,
                'date_delivered' => $batch1->date_delivered,
                'date_outgoing' => $isReleased ? $outgoing1->date_released : null,
                'encoded_by' => $encoder->id,
            ]);
        }

        // 4. Create Sample Incoming Batch 2 (ZTE)
        $batch2 = Batch::create([
            'slip_no' => 'IRS-' . date('Ymd') . '-002',
            'batch_no' => 'BATCH 2',
            'company_name' => 'GLOBE TELECOM / DFTM SOLUTIONS',
            'date_delivered' => now()->subDays(2)->format('Y-m-d'),
            'item_description' => 'ZTE DUAL BAND GIGA ONT MODEM',
            'brand' => 'ZTE',
            'model' => 'ZXHN F670L',
            'total_quantity' => 5,
            'in_stock_quantity' => 5,
            'outgoing_quantity' => 0,
            'status' => 'IN_STOCK',
            'notes' => 'All units for diagnostics & component level repair.',
            'encoded_by' => $encoder->id,
        ]);

        $samplesZte = [
            ['sn' => 'ZTEGC1234001', 'mac' => 'E0:CC:7A:11:22:01', 'diag' => 'NO POWER ON / BLINKING LED', 'part' => 'DC REGULATOR IC', 'status' => 'Repaired', 'box' => 'BOX-Z1'],
            ['sn' => 'ZTEGC1234002', 'mac' => 'E0:CC:7A:11:22:02', 'diag' => 'WATER DAMAGE / CORRODED', 'part' => 'N/A', 'status' => 'BER', 'box' => 'BOX-Z1'],
            ['sn' => 'ZTEGC1234003', 'mac' => 'E0:CC:7A:11:22:03', 'diag' => 'PON LIGHT NOT DETECTED', 'part' => 'OPTICAL TRANSCEIVER', 'status' => 'Repaired', 'box' => 'BOX-Z1'],
            ['sn' => 'ZTEGC1234004', 'mac' => 'E0:CC:7A:11:22:04', 'diag' => 'LAN PORT 3 NO DATA', 'part' => 'MAGNETIC CHOKE', 'status' => 'Repaired', 'box' => 'BOX-Z1'],
            ['sn' => 'ZTEGC1234005', 'mac' => 'E0:CC:7A:11:22:05', 'diag' => 'UNTESTED / ARRIVED', 'part' => '', 'status' => 'In process', 'box' => 'BOX-Z1'],
        ];

        foreach ($samplesZte as $index => $item) {
            InventoryItem::create([
                'batch_id' => $batch2->id,
                'outgoing_slip_id' => null,
                'item_no' => $index + 1,
                'brand' => 'ZTE',
                'model' => 'ZXHN F670L',
                'serial_number' => $item['sn'],
                'mac_address' => $item['mac'],
                'box_no' => $item['box'],
                'technical_diagnostic' => $item['diag'],
                'replace_parts' => $item['part'],
                'repair_status' => $item['status'],
                'stock_status' => 'IN_STOCK',
                'company_name' => $batch2->company_name,
                'date_delivered' => $batch2->date_delivered,
                'encoded_by' => $encoder->id,
            ]);
        }

        ActivityLog::log('SYSTEM_INIT', 'System initialized with default accounts and sample repair inventory records.');
    }
}
