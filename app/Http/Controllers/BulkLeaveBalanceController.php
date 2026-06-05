<?php

namespace App\Http\Controllers;

use App\Models\BulkLeaveImport;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BulkLeaveBalanceController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Get all countries from employees table (uses existing country field you confirmed)
        $countries = DB::table('employees')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        $recentImports = BulkLeaveImport::with('creator:id,name')
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        return Inertia::render('Leaves/BulkLeaveBalance', [
            'leaveTypes'    => $leaveTypes,
            'countries'     => $countries,
            'recentImports' => $recentImports,
        ]);
    }

    /**
     * Preview count before applying.
     */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'scope'   => 'required|in:all,country',
            'country' => 'required_if:scope,country|nullable|string',
        ]);

        $query = DB::table('employees')->where('status', 'active');
        if ($data['scope'] === 'country') {
            $query->where('country', $data['country']);
        }

        return response()->json(['count' => $query->count()]);
    }

    /**
     * Apply bulk leave balance to all or per-country employees.
     */
    public function bulkAssign(Request $request)
    {
        $data = $request->validate([
            'label'         => 'nullable|string|max:255',
            'leave_type_id' => 'required|exists:leave_types,id',
            'days'          => 'required|numeric|min:0',
            'action'        => 'required|in:add,set',
            'scope'         => 'required|in:all,country',
            'country'       => 'required_if:scope,country|nullable|string',
            'year'          => 'required|integer|min:2020|max:2099',
        ]);

        $import = BulkLeaveImport::create([
            'label'         => $data['label'] ?? 'Bulk assign — ' . now()->format('d M Y H:i'),
            'country'       => $data['scope'] === 'country' ? $data['country'] : null,
            'leave_type_id' => $data['leave_type_id'],
            'days'          => $data['days'],
            'action'        => $data['action'],
            'status'        => 'processing',
            'created_by'    => auth()->id(),
        ]);

        try {
            $query = DB::table('employees')->where('status', 'active')->select('id');
            if ($data['scope'] === 'country') {
                $query->where('country', $data['country']);
            }

            $employees    = $query->get();
            $total        = $employees->count();
            $successCount = 0;
            $failedCount  = 0;
            $errors       = [];

            $import->update(['total_records' => $total]);

            DB::transaction(function () use ($employees, $data, &$successCount, &$failedCount, &$errors) {
                foreach ($employees as $employee) {
                    try {
                        // ── Find or create the leave balance row ──────────────────────────────
                        // NOTE: adjust the column names below to match your actual leave_balances table
                        $existing = DB::table('leave_balances')
                            ->where('employee_id', $employee->id)
                            ->where('leave_type_id', $data['leave_type_id'])
                            ->where('year', $data['year'])
                            ->first();

                        if ($existing) {
                            $newBalance = $data['action'] === 'add'
                                ? $existing->balance + $data['days']
                                : $data['days'];

                            DB::table('leave_balances')
                                ->where('id', $existing->id)
                                ->update(['balance' => $newBalance, 'updated_at' => now()]);
                        } else {
                            DB::table('leave_balances')->insert([
                                'employee_id'   => $employee->id,
                                'leave_type_id' => $data['leave_type_id'],
                                'year'          => $data['year'],
                                'balance'       => $data['days'],
                                'created_at'    => now(),
                                'updated_at'    => now(),
                            ]);
                        }

                        $successCount++;
                    } catch (\Exception $e) {
                        $failedCount++;
                        $errors[] = ['employee_id' => $employee->id, 'error' => $e->getMessage()];
                    }
                }
            });

            $import->update([
                'success_count' => $successCount,
                'failed_count'  => $failedCount,
                'errors'        => $errors ?: null,
                'status'        => 'completed',
            ]);

            $msg = "✓ Applied to {$successCount} employee(s).";
            if ($failedCount > 0) $msg .= " {$failedCount} failed — check import log.";

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            $import->update(['status' => 'failed', 'errors' => [['error' => $e->getMessage()]]]);
            return back()->withErrors(['bulk' => 'Bulk assignment failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Download a pre-filled CSV template the admin can use as reference.
     */
    public function downloadTemplate()
    {
        $leaveTypes = LeaveType::where('is_active', true)->get(['id', 'name']);

        $header = ['employee_id', 'leave_type_id', 'leave_type_name', 'days', 'year', 'action(add/set)'];
        $rows   = [];

        foreach ($leaveTypes as $lt) {
            $rows[] = ['101', $lt->id, $lt->name, '1.5', date('Y'), 'add'];
        }

        $csv = implode(',', $header) . "\n";
        foreach ($rows as $row) {
            $csv .= implode(',', $row) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bulk_leave_template.csv"',
        ]);
    }
}
