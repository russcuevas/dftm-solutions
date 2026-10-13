<?php

namespace App\Traits;

use App\Models\ComparingDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ComparingDraftsTrait
{
    /**
     * Get all comparing drafts from the database.
     */
    public function getDrafts(Request $request)
    {
        $drafts = ComparingDraft::with('user:id,name,role')
            ->latest('updated_at')
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'title' => $d->title,
                    'company_name' => $d->company_name,
                    'brand' => $d->brand,
                    'model' => $d->model,
                    'batch_no' => $d->batch_no,
                    'date_delivered' => $d->date_delivered,
                    'status' => $d->status,
                    'sound_enabled' => (bool)$d->sound_enabled,
                    'verified_count' => (int)$d->verified_count,
                    'total_rows' => (int)$d->total_rows,
                    'created_by' => $d->user ? $d->user->name : 'Staff',
                    'is_owner' => Auth::id() === $d->user_id,
                    'updated_at' => $d->updated_at ? $d->updated_at->toISOString() : null,
                    'updated_at_formatted' => $d->updated_at ? $d->updated_at->timezone('Asia/Manila')->format('M d, Y h:i A') : '',
                ];
            });

        return response()->json([
            'success' => true,
            'drafts' => $drafts,
            'total' => $drafts->count(),
        ]);
    }

    /**
     * Get a specific draft with full row data from the database.
     */
    public function getDraft($id)
    {
        $draft = ComparingDraft::with('user:id,name')->find($id);

        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Draft not found in database.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'draft' => [
                'id' => $draft->id,
                'title' => $draft->title,
                'company_name' => $draft->company_name,
                'brand' => $draft->brand,
                'model' => $draft->model,
                'batch_no' => $draft->batch_no,
                'date_delivered' => $draft->date_delivered,
                'status' => $draft->status,
                'sound_enabled' => (bool)$draft->sound_enabled,
                'rows' => $draft->rows_data ?: [],
                'verified_count' => (int)$draft->verified_count,
                'total_rows' => (int)$draft->total_rows,
                'created_by' => $draft->user ? $draft->user->name : 'Staff',
                'updated_at_formatted' => $draft->updated_at ? $draft->updated_at->timezone('Asia/Manila')->format('M d, Y h:i A') : '',
            ]
        ]);
    }

    /**
     * Save or update draft in the database.
     */
    public function saveDraft(Request $request)
    {
        $id = $request->input('id');
        $asNew = filter_var($request->input('as_new', false), FILTER_VALIDATE_BOOLEAN);

        $company = trim($request->input('company_name', ''));
        $brand = trim($request->input('brand', ''));
        $model = trim($request->input('model', ''));
        $batchNo = trim($request->input('batch_no', ''));
        $dateDelivered = $request->input('date_delivered');
        $status = $request->input('status', 'GOOD');
        $soundEnabled = filter_var($request->input('sound_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $rows = $request->input('rows', []);
        if (!is_array($rows)) {
            $rows = [];
        }

        $verifiedCount = 0;
        $totalRows = 0;
        foreach ($rows as $r) {
            if (!empty($r['is_matched']) && !empty($r['id'])) {
                $verifiedCount++;
            }
            if (!empty($r['serial_number']) || !empty($r['mac_address']) || !empty($r['id'])) {
                $totalRows++;
            }
        }

        $draft = null;
        if ($id && !$asNew) {
            $draft = ComparingDraft::find($id);
        }

        $title = trim($request->input('title', ''));
        if (empty($title)) {
            if ($draft && !empty($draft->title)) {
                $title = $draft->title;
            } else {
                $title = ($company ? $company . ' - ' : '') . ($batchNo ?: ('Draft ' . now()->timezone('Asia/Manila')->format('h:i A')));
            }
        }

        if (!$draft) {
            $draft = new ComparingDraft();
            $draft->user_id = Auth::id();
        }

        $draft->title = $title;
        $draft->company_name = $company;
        $draft->brand = $brand;
        $draft->model = $model;
        $draft->batch_no = $batchNo;
        $draft->date_delivered = $dateDelivered;
        $draft->status = $status;
        $draft->sound_enabled = $soundEnabled;
        $draft->rows_data = $rows;
        $draft->verified_count = $verifiedCount;
        $draft->total_rows = $totalRows;
        $draft->save();

        return response()->json([
            'success' => true,
            'draft_id' => $draft->id,
            'title' => $draft->title,
            'verified_count' => $draft->verified_count,
            'total_rows' => $draft->total_rows,
            'updated_at_formatted' => $draft->updated_at->timezone('Asia/Manila')->format('M d, Y h:i A'),
            'message' => 'Draft successfully saved to database.',
        ]);
    }

    /**
     * Rename a draft in the database.
     */
    public function renameDraft(Request $request, $id)
    {
        $draft = ComparingDraft::find($id);
        if (!$draft) {
            return response()->json(['success' => false, 'message' => 'Draft not found.'], 404);
        }

        $title = trim($request->input('title', ''));
        if (empty($title)) {
            return response()->json(['success' => false, 'message' => 'Title cannot be empty.'], 422);
        }

        $draft->title = $title;
        $draft->save();

        return response()->json([
            'success' => true,
            'title' => $draft->title,
            'message' => 'Draft renamed successfully.',
        ]);
    }

    /**
     * Delete a draft from the database.
     */
    public function destroyDraft($id)
    {
        $draft = ComparingDraft::find($id);
        if ($draft) {
            $draft->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Draft deleted from database.',
        ]);
    }
}
