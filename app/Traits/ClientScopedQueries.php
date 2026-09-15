<?php

namespace App\Traits;

use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\OutgoingSlip;
use App\Models\Transmittal;

trait ClientScopedQueries
{
    /**
     * Auto self-heal any batches/slips/items that have empty or default 'DFTM DIGITAL SOLUTIONS'
     * company_name but actually belong to a client transmittal.
     */
    protected function autoHealClientOwnership(string $companyName): void
    {
        $clean = strtolower(trim($companyName));

        // 1. Heal InventoryItems missing company_name whose transmittal has client company
        InventoryItem::where(function ($q) {
            $q->whereNull('company_name')
              ->orWhere('company_name', '')
              ->orWhere('company_name', 'DFTM DIGITAL SOLUTIONS');
        })->whereHas('transmittal', function ($tq) use ($clean) {
            $tq->whereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
               ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%']);
        })->with('transmittal')->chunk(100, function ($items) use ($companyName) {
            foreach ($items as $item) {
                $targetCompany = $item->transmittal?->company_name ?: $companyName;
                $item->update(['company_name' => $targetCompany]);
            }
        });

        // 2. Heal Batches missing company_name whose items belong to client
        Batch::where(function ($q) {
            $q->whereNull('company_name')
              ->orWhere('company_name', '')
              ->orWhere('company_name', 'DFTM DIGITAL SOLUTIONS');
        })->whereHas('items', function ($iq) use ($clean) {
            $iq->whereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
               ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%'])
               ->orWhereHas('transmittal', function ($tq) use ($clean) {
                   $tq->whereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
                      ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%']);
               });
        })->chunk(50, function ($batches) use ($companyName) {
            foreach ($batches as $b) {
                $item = $b->items()->whereNotNull('company_name')->first();
                $targetCompany = $item?->company_name ?: $companyName;
                $b->update(['company_name' => $targetCompany]);
            }
        });

        // 3. Heal OutgoingSlips missing company_name
        OutgoingSlip::where(function ($q) {
            $q->whereNull('company_name')
              ->orWhere('company_name', '')
              ->orWhere('company_name', 'DFTM DIGITAL SOLUTIONS');
        })->whereHas('items', function ($iq) use ($clean) {
            $iq->whereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
               ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%'])
               ->orWhereHas('transmittal', function ($tq) use ($clean) {
                   $tq->whereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
                      ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%']);
               });
        })->chunk(50, function ($slips) use ($companyName) {
            foreach ($slips as $s) {
                $item = $s->items()->whereNotNull('company_name')->first();
                $targetCompany = $item?->company_name ?: $companyName;
                $s->update(['company_name' => $targetCompany]);
            }
        });
    }

    /**
     * Query Transmittals belonging to this client (case-insensitive & fuzzy match)
     */
    protected function getClientTransmittalsQuery(string $companyName)
    {
        $clean = strtolower(trim($companyName));

        return Transmittal::where(function ($q) use ($companyName, $clean) {
            $q->where('company_name', $companyName)
              ->orWhereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
              ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%'])
              ->orWhereRaw('? LIKE CONCAT("%", LOWER(TRIM(company_name)), "%")', [$clean]);
        });
    }

    /**
     * Query Batches belonging to this client:
     * matches by batch company_name OR by items/transmittal belonging to client
     */
    protected function getClientBatchesQuery(string $companyName)
    {
        $clean = strtolower(trim($companyName));

        return Batch::where(function ($q) use ($companyName, $clean) {
            $q->where('company_name', $companyName)
              ->orWhereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
              ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%'])
              ->orWhereRaw('? LIKE CONCAT("%", LOWER(TRIM(company_name)), "%")', [$clean])
              ->orWhereHas('items', function ($iq) use ($clean) {
                  $iq->whereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
                     ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%'])
                     ->orWhereHas('transmittal', function ($tq) use ($clean) {
                         $tq->whereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
                            ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%']);
                     });
              });
        });
    }

    /**
     * Query InventoryItems belonging to this client
     */
    protected function getClientItemsQuery(string $companyName)
    {
        $clean = strtolower(trim($companyName));

        return InventoryItem::where(function ($q) use ($companyName, $clean) {
            $q->where('company_name', $companyName)
              ->orWhereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
              ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%'])
              ->orWhereHas('transmittal', function ($tq) use ($clean) {
                  $tq->whereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
                     ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%']);
              })
              ->orWhereHas('batch', function ($bq) use ($clean) {
                  $bq->whereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
                     ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%']);
              });
        });
    }

    /**
     * Query OutgoingSlips belonging to this client
     */
    protected function getClientOutgoingSlipsQuery(string $companyName, $batches = null)
    {
        $clean = strtolower(trim($companyName));

        return OutgoingSlip::where(function ($q) use ($companyName, $clean, $batches) {
            $q->where('company_name', $companyName)
              ->orWhereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
              ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%'])
              ->orWhereRaw('? LIKE CONCAT("%", LOWER(TRIM(company_name)), "%")', [$clean])
              ->orWhereHas('items', function ($iq) use ($clean) {
                  $iq->whereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
                     ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%'])
                     ->orWhereHas('transmittal', function ($tq) use ($clean) {
                         $tq->whereRaw('LOWER(TRIM(company_name)) = ?', [$clean])
                            ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . $clean . '%']);
                     });
              });

            if ($batches && $batches->isNotEmpty()) {
                $batchNos = $batches->pluck('batch_no')->filter()->unique()->toArray();
                if (!empty($batchNos)) {
                    $q->orWhereIn('batch_no', $batchNos);
                }
            }
        });
    }
}
