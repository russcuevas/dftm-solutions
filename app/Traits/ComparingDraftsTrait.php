<?php

namespace App\Traits;

use App\Models\ComparingDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ComparingDraftsTrait
{
    /**
     * Get the single active comparing draft from the database.
     * Enforces single draft policy so users are never confused.
     */
    public function getDrafts(Request $request)
    {
        $draft = ComparingDraft::with('user:id,name,role')->latest('updated_at')->first();

        $drafts = collect();
        if ($draft) {
            $drafts->push([
                'id' => $draft->id,
                'title' => $draft->title ?: ($draft->company_name ? $draft->company_name . ' - ' . ($draft->batch_no ?: 'Draft') : 'Comparing Draft'),
                'company_name' => $draft->company_name,
                'brand' => $draft->brand,
                'model' => $draft->model,
                'batch_no' => $draft->batch_no,
                'date_delivered' => $draft->date_delivered,
                'status' => $draft->status,
                'sound_enabled' => (bool)$draft->sound_enabled,
                'verified_count' => (int)$draft->verified_count,
                'total_rows' => (int)$draft->total_rows,
                'created_by' => $draft->user ? $draft->user->name : 'Staff',
                'is_owner' => Auth::id() === $draft->user_id,
                'updated_at' => $draft->updated_at ? $draft->updated_at->toISOString() : null,
                'updated_at_formatted' => $draft->updated_at ? $draft->updated_at->timezone('Asia/Manila')->format('M d, Y h:i A') : '',
            ]);
        }

        return response()->json([
            'success' => true,
            'drafts' => $drafts,
            'total' => $drafts->count(),
            'single_draft' => $drafts->first() ?: null,
        ]);
    }

    /**
     * Get the single draft with full row data from the database.
     */
    public function getDraft($id)
    {
        $draft = ($id === 'active' || empty($id))
            ? ComparingDraft::with('user:id,name')->latest('updated_at')->first()
            : ComparingDraft::with('user:id,name')->find($id);

        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Walang naka-save na draft sa database.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'draft' => [
                'id' => $draft->id,
                'title' => $draft->title ?: ($draft->company_name ? $draft->company_name . ' - ' . ($draft->batch_no ?: 'Draft') : 'Comparing Draft'),
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
     * Save or update the single draft in the database.
     * Enforces single draft policy so only one draft ever exists.
     */
    public function saveDraft(Request $request)
    {
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

        // Strictly enforce ONLY 1 draft record in the entire database
        $draft = ComparingDraft::latest('updated_at')->first();
        if (!$draft) {
            $draft = new ComparingDraft();
            $draft->user_id = Auth::id();
        } else {
            $draft->user_id = Auth::id() ?: $draft->user_id;
        }

        $title = trim($request->input('title', ''));
        if (empty($title)) {
            $title = ($company ? $company . ' - ' : '') . ($batchNo ?: 'Draft Sheet');
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

        // Remove any other legacy drafts
        ComparingDraft::where('id', '!=', $draft->id)->delete();

        return response()->json([
            'success' => true,
            'draft_id' => $draft->id,
            'title' => $draft->title,
            'verified_count' => $draft->verified_count,
            'total_rows' => $draft->total_rows,
            'updated_at_formatted' => $draft->updated_at->timezone('Asia/Manila')->format('M d, Y h:i A'),
            'message' => 'Nai-save ang draft sa database.',
        ]);
    }

    /**
     * Rename the single draft in the database.
     */
    public function renameDraft(Request $request, $id)
    {
        $draft = ComparingDraft::latest('updated_at')->first();
        if (!$draft) {
            return response()->json(['success' => false, 'message' => 'Walang draft na nahanap.'], 404);
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
     * Delete / clear the single draft from the database.
     */
    public function destroyDraft($id)
    {
        ComparingDraft::truncate();

        return response()->json([
            'success' => true,
            'message' => 'Nai-clear na ang saved draft.',
        ]);
    }
}
