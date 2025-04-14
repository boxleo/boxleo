<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\Announcement;
use Illuminate\Support\Facades\Log;
use App\Models\User;
// use App\Mail\AnnouncementNotification;
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
        // $announcements = Announcement::with('author', 'attachments')->get();
        $announcements = Announcement::with('attachments')->get();
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

        if ($status === 'published') {
            // $this->notifyAllUsers($announcement);
            Notification::send(User::all(), new AnnouncementPublishedNotification($announcement));
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

        // $announcement->update($request->except(['status', 'action', 'publish_date', 'author']));

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
            // $announcement = Announcement::with('author')->find($announcement->id);
            // $announcement -> load('authorUser');
            // $this->notifyAllUsers($announcement);

            $unitId = Auth::user()->unit_id;

            $users = User::where('is_enabled', true)
                        ->where('unit_id', $unitId)
                        ->get();

            Notification::send($users, new AnnouncementPublishedNotification($announcement));
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

    // public function sendEmails(string $id)
    // {
    //     $announcement = Announcement::findOrFail($id);

    //     // Don't send emails for drafts
    //     if ($announcement->status !== 'published') {
    //         return response()->json(['message' => 'Cannot send emails for draft announcements'], 400);
    //     }

    //     // Get all active users
    //     $users = User::where('is_enabled', true)->get();

    //     // Dispatch emails to queue for better performance
    //     foreach ($users as $user) {
    //         Mail::to($user->email)->queue(new AnnouncementNotification($announcement));
    //     }

    //     return response()->json(['message' => 'Emails queued for sending']);
    // }

    public function downloadAttachment($id)
    {
        $attachment = AnnouncementAttachment::findOrFail($id);
        $path = storage_path('app/public/' . $attachment->file_path);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        return response()->download($path, $attachment->original_filename);
    }

    // protected function notifyAllUsers(Announcement $announcement)
    // {
    //     if (! $announcement->relationLoaded('authorUser')) {
    //         $announcement->load('authorUser');
    //     }

    //     // $authorId = $announcement->author()->first();

    //     // Find the author user
    //     // $author = User::find($authorId);
    //     // $author = $announcement->author()->first();
    //     $author = $announcement->author;

    //     if (! $author) {
    //         Log::warning('Could not send notifications - author not found', [
    //             'announcement_id' => $announcement->id,
    //             'author_column'   => $announcement->getAttribute('author'),
    //         ]);
    //         return;
    //     }

    //     // Now you can safely read unit_id
    //     // $unitId = $author->unit_id;
    //     $unitId = Auth::user()->unit_id;

    //     $users = User::where('is_enabled', true)
    //                  ->where('unit_id', $unitId)
    //                  ->get();

    //     foreach ($users as $user) {
    //         $user->notify(new AnnouncementPublishedNotification($announcement));
    //     }

    //     Log::info('Published notification sent', ['announcement_id' => $announcement->id]);



        // if ($author) {
        //     $unitId = $announcement->author->unit_id;
        //     $users = User::where('is_enabled', true)
        //                 ->where('unit_id', $author->unit_id)
        //                 ->get();
        //     foreach ($users as $user) {
        //         $user->notify(new AnnouncementPublishedNotification($announcement));
        //     }
        //     Log::info('Published notification sent', ['announcement_id' => $announcement->id]);
        // } else {
        //     Log::warning('Could not send notifications - author not found', ['announcement_id' => $announcement->id]);
        // }


        // if (!$announcement->relationLoaded('author')) {
        //     $announcement->load('author');
        // }

        // $users = User::where('is_enabled', true)->where('unit_id', $announcement->author->unit_id)
        // ->get();
        // foreach ($users as $user) {
        //     $user->notify(new AnnouncementPublishedNotification($announcement));
        // }
        // Log::info('Published notification sent', ['announcement_id' => $announcement->id]);
    // }

    public function sendNotifications(Announcement $announcement)
    {
        $this->notifyAllUsers($announcement);
        return response()->json(['message' => 'Notifications sent successfully']);
    }
}
