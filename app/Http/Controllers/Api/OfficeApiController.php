<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\Request;

class OfficeApiController extends Controller
{
    public function index()
    {

        $offices = Office::with('users', 'unit')->get();

        return response()->json(['offices' => $offices]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|integer',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        Office::create($request->all());

        return response()->json(['Suceess' => 'Office Created Successifully']);
    }

    public function update(Request $request, Office $office)
    {
       

        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $office->update($validatedData);
        return response()->json(['message' => 'Office updated successfully', 'office' => $office], 200);
    }


    public function destroy(Office $office)
    {
        $office->delete();

        return response()->json(['message' => 'Office deleted successfully']);
    }
}
