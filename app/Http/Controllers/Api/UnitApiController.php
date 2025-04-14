<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UnitApiController extends Controller
{
    public function index()
    {
        $branches = Unit::with('users', 'offices')->get();

        return response()->json(['branches' => $branches]);
    }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'timezone' => 'required|string|max:255',
        'work_start_time' => 'required|date_format:H:i', 
        'late_threshold' => 'required|date_format:H:i', 
        'weekend_day' => 'required|string|max:255',
        'weekend_clock_in_time' => 'required|date_format:H:i', 
        'weekend_clock_out_time' => 'required|date_format:H:i', 
        'weekday_threshold' => 'required|date_format:H:i', 
        'weekend_threshold' => 'required|date_format:H:i', 
        'clock_in_time' => 'required|date_format:H:i', 
        'clock_out_time' => 'required|date_format:H:i', 
        'address' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
    ]);

    // Create the new unit with the validated data
    Unit::create($validatedData);

    return response()->json(['success' => 'Unit Created Successfully']);
}

public function update(Request $request, Unit $unit)
{
    Log::info('Update method called for Unit', ['unit_id' => $unit->id]);
    // Log::info('Request data', ['request_data' => $request->all()]);

    $validatedData = $request->validate([
        'name' => 'nullable|string|max:255',
        'timezone' => 'nullable|string|max:255',
        'work_start_time' => 'nullable|date_format:H:i',
        'late_threshold' => 'nullable|date_format:H:i|date_format:H:i',
        'weekend_day' => 'nullable|string|max:255',
        'weekend_clock_in_time' => 'nullable|date_format:H:i|date_format:H:i',
        'weekend_clock_out_time' => 'nullable|date_format:H:i|date_format:H:i',
        'weekday_threshold' => 'nullable|date_format:H:i|date_format:H:i',
        'weekend_threshold' => 'nullable|date_format:H:i|date_format:H:i',
        'clock_in_time' => 'nullable|date_format:H:i|date_format:H:i',
        'clock_out_time' => 'nullable|date_format:H:i|date_format:H:i',
        'address' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
    ]);
    

    Log::info('Validated data for update', ['validated_data' => $validatedData]);

    // Update the unit with the validated data
    $unit->update($validatedData);

    Log::info('Unit updated successfully', ['unit' => $unit]);

    return response()->json(['message' => 'Unit updated successfully', 'unit' => $unit], 200);
}

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return response()->json(['message' => 'Unit deleted successfully']);
    }



    public function timeZones()
{
    $data = cache()->remember('apyhub_timezones', 86400, function () {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'apy-token' => 'APY0yxRqF92nNnYOylpThCLADlzIwqPFBflB58nd9TItPQf5KhXIreFkFHCWXh9peygMTRsq2ZU',
        ])->get('https://api.apyhub.com/data/dictionary/timezone');

        return $response->successful() ? $response->json()['data'] : [];
    });

    return response()->json($data);
}

public function countries()
{
    $data = cache()->remember('apyhub_countries', 86400, function () {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'apy-token' => 'APY0yxRqF92nNnYOylpThCLADlzIwqPFBflB58nd9TItPQf5KhXIreFkFHCWXh9peygMTRsq2ZU',
        ])->get('https://api.apyhub.com/data/dictionary/country');

        return $response->successful() ? $response->json()['data'] : [];
    });

    return response()->json($data);

}

}
