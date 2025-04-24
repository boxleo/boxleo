<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\Announcement;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\AnnouncementAttachment;
use App\Notifications\AnnouncementPublishedNotification;

class AnnouncementApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcement::with('attachments', 'departments', 'units')->get();
         // Attach the author's firstname (lookup by the author column)
        $announcements->transform(function ($announcement) {
            $announcement->author_name = User::find($announcement->author)?->firstname;
            return $announcement;
        });
        return response()->json($announcements);
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

        $request->validate([
            'subject' => 'required|string',
            'description' => 'required|string',
            'attachments.*' => 'sometimes|file|max:10240', // 10MB max
        ]);

        $status = $request->input('action') === 'publish' ? 'published' : 'draft';
        $publishDate = now();

        $isActive = false;
        if ($status === 'published') {
            $expirationDate = $request->input('expiration_date');
            // Only active if published AND not expired
            $isActive = $expirationDate ? now()->lessThan($expirationDate) : true;
        }

        $isActive = $isActive ? 1 : 0;

        $announcement = Announcement::create([
            'subject' => $request->input('subject'),
            'description' => $request->input('description'),
            'author' => auth()->user()->id,
            'publish_date' => $publishDate,
            'expiration_date' => $request->input('expiration_date'),
            'is_active' => $isActive,
            'attachment' => $request->input('attachment'),
            'priority' => $request->input('priority'),
            'status' => $status,
        ]);
        Log::info('Announcement created', ['announcement_id' => $announcement->id]);
        // Handle file attachments if any
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('attachments', $filename, 'public');

                Log::info('File stored successfully', [
                    'filename' => $filename,
                    'path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);

                $announcement->attachments()->create([
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        Log::info('Announcement created successfully', ['announcement_id' => $announcement->id]);


        // if ($status === 'published') {
        //     $unitId = Auth::user()->unit_id;

        //     // Check if departments were selected
        //     if ($request->has('department_ids') && !empty($request->input('department_ids'))) {
        //         $departmentIds = $request->input('department_ids');

        //         // Get users who belong to selected departments AND have the same unit_id
        //         $users = User::whereHas('department', function($query) use ($departmentIds) {
        //             $query->whereIn('department_id', $departmentIds);
        //         })
        //         ->where('is_enabled', true)
        //         ->where('unit_id', $unitId)
        //         ->get();

        //         Log::info('Sending notifications to users in selected departments and same unit', [
        //             'unit_id' => $unitId,
        //             'department_ids' => $departmentIds,
        //             'user_count' => $users->count()
        //         ]);

        //         Notification::send($users, new AnnouncementPublishedNotification($announcement));
        //     } else {
        //         // No departments specified, so only filter by unit_id
        //         $users = User::where('is_enabled', true)
        //                     ->where('unit_id', $unitId)
        //                     ->get();

        //         Notification::send($users, new AnnouncementPublishedNotification($announcement));
        //     }
        // }

        if ($status === 'published') {
            $authorUnitId = Auth::user()->unit_id;
            
            // Get users based on selected departments and units
            // $query = User::where('is_enabled', true);
            $query = User::where('is_enabled', true)
                         ->where('email', '!=', 'brian.omondi@boxleocourier.com');
            
            // If specific units are selected
            if ($request->has('unit_ids') && !empty($request->input('unit_ids'))) {
                $unitIds = $request->input('unit_ids');
                $query->whereIn('unit_id', $unitIds);
            } else {
                // Default to author's unit only
                $query->where('unit_id', $authorUnitId);
            }
            
            // If specific departments are selected
            if ($request->has('department_ids') && !empty($request->input('department_ids'))) {
                $departmentIds = $request->input('department_ids');
                $query->whereHas('department', function($q) use ($departmentIds) {
                    $q->whereIn('department_id', $departmentIds);
                });
            }
            
            $users = $query->get();
            
            Notification::send($users, new AnnouncementPublishedNotification($announcement));
        }

        if ($request->has('department_ids')) {
            $departmentIds = $request->input('department_ids');
            // Assuming you have a many-to-many relationship set up
            $announcement->departments()->sync($departmentIds);
            Log::info('Departments associated with announcement', [
                'announcement_id' => $announcement->id,
                'department_ids' => $departmentIds
            ]);
        }

        if ($request->has('unit_ids')) {
            $unitIds = $request->input('unit_ids');
            $announcement->units()->sync($unitIds);
        }

        return response()->json([
            'message' => 'Announcement created successfully',
            'data' => $announcement->load('attachments'),
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
        $announcement = Announcement::findOrFail($id);

        $status = $request->input('action') === 'publish' ? 'published' : 'draft';


        $data = $request->except(['status', 'action', 'publish_date', 'author']);

        // Make sure is_active is saved as an integer (0 or 1)
        if (isset($data['is_active'])) {
            $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }

        $announcement->update($data);

        $announcement->status = $status;

        if (!$announcement->publish_date) {
            $announcement->publish_date = now();
        }

        if ($status === 'draft') {
            $announcement->is_active = false;
        } else {
            $announcement->is_active = $announcement->expiration_date ?
                now()->lessThan($announcement->expiration_date) :
                true;
        }

        $announcement->save();

        if ($status === 'published') {
            $unitId = Auth::user()->unit_id;

            // Check if departments were selected
            if ($request->has('department_ids') && !empty($request->input('department_ids'))) {
                $departmentIds = $request->input('department_ids');

                // Get users who belong to selected departments AND have the same unit_id
                $users = User::whereHas('department', function($query) use ($departmentIds) {
                    $query->whereIn('department_id', $departmentIds);
                })
                ->where('is_enabled', true)
                ->where('unit_id', $unitId)
                ->get();

                Log::info('Sending notifications to users in selected departments and same unit', [
                    'unit_id' => $unitId,
                    'department_ids' => $departmentIds,
                    'user_count' => $users->count()
                ]);

                Notification::send($users, new AnnouncementPublishedNotification($announcement));
            } else {
                // No departments specified, so only filter by unit_id
                $users = User::where('is_enabled', true)
                            ->where('unit_id', $unitId)
                            ->get();

                Notification::send($users, new AnnouncementPublishedNotification($announcement));
            }
        }

        // Handle attachments (if any)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('attachments', $filename, 'public');

                $announcement->attachments()->create([
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }
        // Update department associations
    if ($request->has('department_ids')) {
        $departmentIds = $request->input('department_ids');
        $announcement->departments()->sync($departmentIds);
    }

    if ($request->has('unit_ids')) {
        $unitIds = $request->input('unit_ids');
        $announcement->units()->sync($unitIds);
    }

        return response()->json($announcement, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        Log::info('Announcement deleted successfully', ['announcement_id' => $id]);

        return response()->json([
            'message' => 'Announcement deleted successfully',
        ], 200);

    }

    public function downloadAttachment($id)
    {
        $attachment = AnnouncementAttachment::findOrFail($id);
        $path = storage_path('app/public/' . $attachment->file_path);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        return response()->download($path, $attachment->original_filename);
    }

}
