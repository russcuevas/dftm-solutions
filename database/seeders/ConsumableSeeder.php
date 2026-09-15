<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsumableCategory;
use App\Models\ConsumableItem;
use App\Models\ConsumableMonthlyStock;
use App\Models\ConsumableDailyLog;

class ConsumableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = 2026;
        $currentMonth = 9; // September

        $data = [
            'BOXES' => [
                ['name' => "Big box Single wall 10'S", 'unit' => 'BUNDLE', 'cost' => 0.00, 'beginning' => 31, 'logs' => [
                    ['day' => 1, 'out' => 2],
                    ['day' => 3, 'out' => 2],
                    ['day' => 4, 'out' => 2],
                ]],
                ['name' => "SURF 2 SAWA Small box 25'S", 'unit' => 'BUNDLE', 'cost' => 0.00, 'beginning' => 118],
                ['name' => "BIDA FIBER Small box  25'S", 'unit' => 'BUNDLE', 'cost' => 0.00, 'beginning' => 1],
                ['name' => "Plain Small box 25'S", 'unit' => 'BUNDLE', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "Pads", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 4800],
            ],
            'RAGS' => [
                ['name' => "White Rags 100's", 'unit' => 'BUNDLE', 'cost' => 0.00, 'beginning' => 41, 'logs' => [
                    ['day' => 1, 'out' => 1],
                    ['day' => 3, 'out' => 1],
                ]],
                ['name' => "Colored Rags 100's", 'unit' => 'BUNDLE', 'cost' => 0.00, 'beginning' => 0],
            ],
            'PAPER' => [
                ['name' => "SHORT BOND PAPER", 'unit' => 'REAM', 'cost' => 0.00, 'beginning' => 3],
                ['name' => "LONG BOND PAPER", 'unit' => 'REAM', 'cost' => 0.00, 'beginning' => 6],
                ['name' => "A4 BOND PAPER", 'unit' => 'REAM', 'cost' => 0.00, 'beginning' => 3],
                ['name' => "News Print Long", 'unit' => 'BUNDLE', 'cost' => 0.00, 'beginning' => 4],
                ['name' => "News Print Short", 'unit' => 'BUNDLE', 'cost' => 0.00, 'beginning' => 3],
                ['name' => "VECO MATTE STICKER", 'unit' => 'PCK', 'cost' => 45.00, 'beginning' => 11, 'logs' => [
                    ['day' => 3, 'out' => 1],
                ]],
                ['name' => "GLOSSY A4 STICKER", 'unit' => 'PCK', 'cost' => 165.00, 'beginning' => 9],
                ['name' => "ITECH A4 VINYL STICKER", 'unit' => 'PCK', 'cost' => 224.00, 'beginning' => 50],
                ['name' => "VECO CLEAR STICKER", 'unit' => 'PCK', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "GREASE PAPER 1000'S", 'unit' => 'BUNDLE', 'cost' => 820.00, 'beginning' => 3, 'logs' => [
                    ['day' => 4, 'out' => 1],
                ]],
            ],
            'PROD MATERIALS' => [
                ['name' => "COTTON BUDS 10's", 'unit' => 'PCK', 'cost' => 0.00, 'beginning' => 0, 'logs' => [
                    ['day' => 4, 'out' => 1],
                ]],
                ['name' => "WASH BRUSH", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 6],
                ['name' => "SPRAY BOTTLE", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 1],
                ['name' => "TOOTH BRUSH", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 6],
                ['name' => "CUTTER REFFIL", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 3],
                ['name' => "MASKING TAPE", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 10, 'logs' => [
                    ['day' => 4, 'out' => 1],
                ]],
                ['name' => "PACKING TAPE", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 26],
                ['name' => "STATIONARY TAPE SMALL", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 28],
                ['name' => "STATIONARY TAPE BIG", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 71],
                ['name' => "DOUBLE SIDED TAPE", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 5],
                ['name' => "ELECTRICAL TAPE", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 5],
                ['name' => "PAINT BRUSH", 'unit' => 'PCS', 'cost' => 33.00, 'beginning' => 2],
                ['name' => "STEEL BRUSH WOODEN", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 5],
                ['name' => "STEEL BRUSH BIG", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 1],
                ['name' => "SPATULA", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 1],
                ['name' => "GLOVES", 'unit' => 'BOX', 'cost' => 175.00, 'beginning' => 2, 'logs' => [
                    ['day' => 1, 'out' => 1],
                ]],
            ],
            'TECH MATERIALS' => [
                ['name' => "SOLDERING PASTE", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 3],
                ['name' => "SOLDERING LED", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 3],
                ['name' => "WD 40", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 5],
                ['name' => "CONTACT CLEANER", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 2],
                ['name' => "SOLDERING IRON 60W", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 3],
                ['name' => "SOLDERING IRON30W", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 1],
                ['name' => "SOLDERING TIP  60", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 9],
                ['name' => "SOLDERING TIP 30", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 2],
                ['name' => "RJ45", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "SOLDERING PUMP", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 2],
                ['name' => "DESOLDERING WIRE", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 3],
                ['name' => "GREENFIELDSCREW DRIVER 125MM", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 1],
                ['name' => "GREENFIELDSCREW DRIVER 15MM", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 1],
                ['name' => "LONG NOSE PLIERS 6\"", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 1],
                ['name' => "CUTTER PLIERS", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 1],
                ['name' => "COMBINATION PLIERS6'/ MINI", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 2],
                ['name' => "TWISTIE", 'unit' => 'PCK', 'cost' => 0.00, 'beginning' => 3],
                ['name' => "T8", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 2],
                ['name' => "9V BATTERY", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "AAA BATTERY", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "AA BATTERY", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "ANALOG TESTER", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "SOLDERING WIRE", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "STEAL WOOL", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 7],
                ['name' => "SOLDERING STAND", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "NEEDLE FILE", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "MAGNIFYING GLASS", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 1],
                ['name' => "CONNECTOR", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "LOTUS PLIERS SET", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "T15", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
            ],
            'MISCELLANEOUS' => [
                ['name' => "FACEMASK 100'S", 'unit' => 'PCK', 'cost' => 0.00, 'beginning' => 1],
                ['name' => "kn95 mask", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "Trash Bag 100's (Small)", 'unit' => 'PCK', 'cost' => 0.00, 'beginning' => 3],
                ['name' => "Trash Bag 100's (Large)", 'unit' => 'PCK', 'cost' => 0.00, 'beginning' => 2],
                ['name' => "Trash Bag 100's (XLarge)", 'unit' => 'PCK', 'cost' => 0.00, 'beginning' => 3],
                ['name' => "Trash Bag 50'S XXL", 'unit' => 'ROLL', 'cost' => 350.00, 'beginning' => 0],
                ['name' => "MOUSE", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "DONGLE", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "bosny CLEAR PAINT", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "GLUE STICK", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 16],
            ],
            'CHEMICAL' => [
                ['name' => "Lacquer Thinner", 'unit' => 'PCS', 'cost' => 420.00, 'beginning' => 2],
                ['name' => "Paint Thinner", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "ParaLux Rubbing Compound", 'unit' => 'PCS', 'cost' => 300.00, 'beginning' => 0, 'logs' => [
                    ['day' => 1, 'out' => 1],
                ]],
                ['name' => "Wipe Out", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 9],
            ],
            'OFFICE SUPPLIES' => [
                ['name' => "Asticky note", 'unit' => 'PAD', 'cost' => 0.00, 'beginning' => 7],
                ['name' => "INDEX CARD 1/4", 'unit' => 'PAD', 'cost' => 0.00, 'beginning' => 2],
                ['name' => "Ballpen Black", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 50],
                ['name' => "Ballpen Blue", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 48],
                ['name' => "Ballpen Red", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 45],
                ['name' => "Binder Clip 2\"", 'unit' => 'BOX', 'cost' => 0.00, 'beginning' => 5],
                ['name' => "Binder Clip 1 5/8", 'unit' => 'BOX', 'cost' => 0.00, 'beginning' => 6],
                ['name' => "Binder Clip 1 1/4", 'unit' => 'BOX', 'cost' => 0.00, 'beginning' => 5],
                ['name' => "Binder Clip 1\"", 'unit' => 'BOX', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "Binder Clip 3/4", 'unit' => 'BOX', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "RULER", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 4],
                ['name' => "SLIDER", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 10],
                ['name' => "Brown Envelope Long", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 44],
                ['name' => "Brown Envelope Short", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 61],
                ['name' => "HIGH LIGHTER", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 9],
                ['name' => "Correction Tape", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 4],
                ['name' => "Elmers Glue 130g", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 0],
                ['name' => "Fastener (Plastic)", 'unit' => 'BOX', 'cost' => 0.00, 'beginning' => 4],
                ['name' => "Folder Long Kraft", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 73],
                ['name' => "Folder Long White", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 80],
                ['name' => "Folder Short Kraft", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 83],
                ['name' => "Folder Short White", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 71],
                ['name' => "Gel Pen Black", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 6],
                ['name' => "Gel Pen Blue", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 8],
                ['name' => "Gel Pen Red", 'unit' => 'PCS', 'cost' => 0.00, 'beginning' => 7],
                ['name' => "Fastener metal", 'unit' => 'BOX', 'cost' => 0.00, 'beginning' => 2],
            ]
        ];

        $catSort = 1;
        foreach ($data as $catName => $items) {
            $category = ConsumableCategory::firstOrCreate(
                ['name' => $catName],
                ['sort_order' => $catSort++]
            );

            $itemSort = 1;
            foreach ($items as $itemData) {
                $item = ConsumableItem::firstOrCreate(
                    [
                        'category_id' => $category->id,
                        'name' => $itemData['name'],
                    ],
                    [
                        'unit' => $itemData['unit'],
                        'cost' => $itemData['cost'] ?? 0.00,
                        'sort_order' => $itemSort++,
                        'is_active' => true,
                    ]
                );

                // Set beginning stock for Sept 2026
                ConsumableMonthlyStock::updateOrCreate(
                    [
                        'consumable_item_id' => $item->id,
                        'year' => $currentYear,
                        'month' => $currentMonth,
                    ],
                    [
                        'beginning_stock' => $itemData['beginning'] ?? 0,
                    ]
                );

                // Seed daily logs if present
                if (!empty($itemData['logs'])) {
                    foreach ($itemData['logs'] as $log) {
                        $logDate = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $log['day']);
                        ConsumableDailyLog::firstOrCreate(
                            [
                                'consumable_item_id' => $item->id,
                                'log_date' => $logDate,
                            ],
                            [
                                'in_qty' => $log['in'] ?? 0,
                                'out_qty' => $log['out'] ?? 0,
                                'remarks' => 'Excel Initial Log',
                            ]
                        );
                    }
                }
            }
        }
    }
}
