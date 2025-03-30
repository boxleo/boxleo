<?php

namespace App\Http\Controllers\Api;

use App\Models\Holiday;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class HolidayApiController extends Controller
{

    public function index()
    {
        $holidays = Holiday::whereYear('date', now()->year)->get();

        return response()->json(['holidays' => $holidays]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            // 'unit_id' => 'required|exists:units,id',
        ]);



        $holiday = Holiday::create([
            'name' => $validatedData['name'],
            'date' => $validatedData['date'],
            // 'unit_id' => $validatedData['unit_id'],
            'is_recurring' => 1
        ]);

        return response()->json(['holiday' => $holiday], 201);
    }

    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        try {
            $holiday = Holiday::findOrFail($id);
            $holiday->delete();

            Log::info("Holiday with ID {$id} deleted successfully.");
            return response()->json(['message' => 'Holiday deleted successfully.']);
        } catch (\Exception $e) {
            Log::error("Failed to delete holiday with ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Failed to delete holiday.'], 500);
        }
    }
}
