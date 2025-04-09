<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Log;

class AnnouncementApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Creating new announcement', ['data' => $request->all()]);

        $announcement = Announcement::create([
            'subject' => $request->input('subject'),
            'description' => $request->input('description'),
            'author' => auth()->user()->id,
            'publish_date' => $request->input('publish_date'),
            'expiration_date' => $request->input('expiration_date'),
            'is_active' => $request->input('is_active'),
            'attachment' => $request->input('attachment'),
            'priority' => $request->input('priority'),
            'status' => $request->input('status'),
        ]);

        Log::info('Announcement created successfully', ['announcement_id' => $announcement->id]);

        return response()->json([
            'message' => 'Announcement created successfully',
            'data' => $announcement,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
