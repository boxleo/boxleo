<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Complaint;
use Illuminate\Http\Request;
use App\Mail\ComplaintNotification;
use App\Http\Controllers\Controller;
use App\Models\ComplaintLog;
use App\Models\Department;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class ComplaintApiController extends Controller
{


    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        $status = $request->query('status');
        $category = $request->query('category');
        $priority = $request->query('priority');

        $user = auth()->user();
        $hr = $user->is_hr;
        $hod = $user->is_hod;
        $super_admin = $user->super_admin;

        $query = Complaint::with(['user', 'addressedTo', 'followers','departments']);

        // Allow creators to see their complaints
        if (!($hr || $hod || $super_admin)) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('addressedTo', fn($q) => $q->where('users.id', $user->id))
                    ->orWhereHas('followers', fn($q) => $q->where('users.id', $user->id));
            });
        }

        // Apply filters
        if ($userId) $query->where('user_id', $userId);
        if ($status) $query->where('status', $status);
        if ($category) $query->where('category', $category);
        if ($priority) $query->where('priority', $priority);

        // Fetch and transform data
        $complaints = $query->get()->map(fn($complaint) => [
            'id' => $complaint->id,
            'subject' => $complaint->subject,
            'description' => $complaint->description,
            'category' => $complaint->category,
            'priority' => $complaint->priority,
            'user_id' => $complaint->user_id,
            'status' => $complaint->status,
            'entry_channel' => $complaint->entry_channel,
            'due_date' => $complaint->due_date,
            'date_opened' => $complaint->date_opened,
            'closed_date' => $complaint->closed_date,
            'attachments' => $complaint->attachments,
            'links' => $complaint->links,
            'comments' => $complaint->comments,
            'resolution' => $complaint->resolution,
            'created_at' => $complaint->created_at,
            'updated_at' => $complaint->updated_at,
            'deleted_at' => $complaint->deleted_at,
            'created_by' => optional($complaint->user)->firstname . ' ' . optional($complaint->user)->lastname,
            'addressed_to' => $complaint->addressedTo->map(fn($user) => $user->firstname . ' ' . $user->lastname)->toArray(),
            'followers' => $complaint->followers->map(fn($user) => $user->firstname . ' ' . $user->lastname)->toArray(),

            // given a complaint has department_ids, include department names
            'departments' => $complaint->departments->map(fn($department) => $department->name)->toArray(),
        ]);

        return response()->json(['complaints' => $complaints], Response::HTTP_OK);
    }


    public function store(Request $request)
    {
        Log::info('Complaint request received.', ['request' => $request->all()]);

        try {
            // Validate the request including attachments
            $data = $this->validateComplaint($request);
            $data['status'] = 'Open';
            $data['priority'] = 'Medium';

            // Handle attachments - support both single and multiple files
            $attachments = [];

            if ($request->hasFile('attachments')) {
                $files = is_array($request->file('attachments')) ? $request->file('attachments') : [$request->file('attachments')];

                foreach ($files as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('complaints', $filename, 'public');

                    Log::info('Stored file path:', ['path' => $path]);

                    $storedAttachment = [
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType()
                    ];

                    $attachments[] = $storedAttachment;

                    Log::info('Attachment details:', [
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize()
                    ]);
                }
            }

            // Ensure attachments are stored as JSON
            $data['attachments'] = json_encode($attachments);

            // Handle links
            if ($request->has('links')) {
                $data['links'] = json_encode($request->input('links'));
            }

            // Create the complaint
            $complaint = Complaint::create($data);
            Log::info('Complaint created successfully.', ['complaint_id' => $complaint->id]);

         
            if ($request->filled('department_id')) {
                // Ensure department_id is always treated as an array
                $departmentIds = is_array($request->department_id) ? $request->department_id : [$request->department_id];
            
                $complaint->departments()->sync($departmentIds);
            
                Log::info('Department IDs synced.', [
                    'complaint_id' => $complaint->id,
                    'department_ids' => $departmentIds
                ]);
            
                foreach ($departmentIds as $departmentId) {
                    $department = Department::find($departmentId);
                    if ($department && $department->manager_id) {
                        $manager = User::find($department->manager_id);
                        if ($manager) {

                            $this->sendComplaintNotifications($request, $complaint);                          
                              Log::info('Notification sent to department manager.', [
                                'complaint_id' => $complaint->id,
                                'department_id' => $departmentId,
                                'manager_id' => $manager->id
                            ]);
                        }
                    }
                }
            }
            
            
            // Attach Assigned Users (Role: addressed_to)
            if ($request->filled('addressed_to')) {
                $complaint->users()->syncWithoutDetaching(
                    collect($request->addressed_to)->mapWithKeys(fn($userId) => [$userId => ['role' => 'addressed_to']])
                );
                Log::info('Assigned users added.', ['complaint_id' => $complaint->id, 'users' => $request->addressed_to]);
            }

            // Attach Followers (Role: follower)
            if ($request->filled('followers')) {
                $complaint->users()->syncWithoutDetaching(
                    collect($request->followers)->mapWithKeys(fn($userId) => [$userId => ['role' => 'follower']])
                );
                Log::info('Follower users added.', ['complaint_id' => $complaint->id, 'users' => $request->followers]);
            }

            // Send email notifications

            Log::info('Sending complaint notifications.', ['complaint_id' => $complaint->id]);
            $this->sendComplaintNotifications($request, $complaint);
            Log::info('Complaint notifications sent successfully.', ['complaint_id' => $complaint->id]);

            // Log the action
            // $this->LogAction($complaint->id, 'Created', $complaint);

            return response()->json(['complaint' => $complaint], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Error storing complaint:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'An error occurred while submitting the complaint: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // proper validation
    protected function validateComplaint(Request $request)
    {
        return $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'addressed_to' => 'nullable|array',
            'addressed_to.*' => 'exists:users,id',
            'followers' => 'nullable|array',
            'followers.*' => 'exists:users,id',
            'is_anonymous' => 'boolean',
            // 'attachments' => 'nullable|array',
            // 'attachments.*' => 'file|mimes:pdf,doc,docx,png|max:10240',
            'attachments' => 'nullable|file|mimes:pdf,doc,docx.pdf,png|max:10240',

            'links' => 'nullable|array',
            'links.*' => 'string|url',
            'user_id' => 'required|exists:users,id',
            'department_ids' => 'nullable|array',
            'office_id' => 'nullable|exists:offices,id',
             'unit_id' => 'nullable|exists:units,id',

        ]);
    }

    private function sendComplaintNotifications(Request $request, Complaint $complaint)
    {
        if ($request->filled('addressed_to')) {
            $assignedToUsers = User::whereIn('id', $request->addressed_to)->get();
            foreach ($assignedToUsers as $user) {
                Mail::to($user->email)->send(new ComplaintNotification($complaint, $user));
            }
            Log::info('Emails sent to assigned users.', ['users' => $request->addressed_to]);
        }

        if ($request->filled('followers')) {
            $followerUsers = User::whereIn('id', $request->followers)->get();
            foreach ($followerUsers as $user) {
                Mail::to($user->email)->send(new ComplaintNotification($complaint, $user));
            }
            Log::info('Emails sent to followers.', ['users' => $request->followers]);
        }

        if ($request->filled('department_id')) {
            Log::info('Sending emails to department managers.', [
                'has_department_id' => $request->filled('department_id'),
                'department_ids' => $request->department_id
            ]);
        
            // Get departments along with their managers
            $departments = Department::with('managers','users')->whereIn('id', $request->department_id)->get();
        
            foreach ($departments as $department) {
                if($department->name == 'Management'){
                    // send email to all member of department management
                    foreach ($department->users as $user) {
                        Mail::to($user->email)->send(new ComplaintNotification($complaint, $user));
                        Log::info('Email sent to department manager of management.', [
                            'department_id' => $department->id,
                            'user_id' => $user->id
                        ]);
                    }


                }else 
                foreach ($department->managers as $manager) { // Loop through managers
                    Mail::to($manager->email)->send(new ComplaintNotification($complaint, $manager));
                    Log::info('Email sent to department manager.', [
                        'department_id' => $department->id,
                        'manager_id' => $manager->id,
                        'manager_email' => $manager->email
                    ]);
                }
            }
        
            Log::info('Emails sent to department managers.', ['department_ids' => $request->department_id]);
        }
        
        
    }



    public function update(Request $request, $id)
    {
        Log::info('Complaint update request received.', ['request' => $request->all()]);

        $complaint = $this->findComplaint($id);

        if (!$complaint) {
            return response()->json(['message' => 'Complaint not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->validate([
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'addressed_to' => 'nullable|array',
            'addressed_to.*' => 'exists:users,id',
            'followers' => 'nullable|array',
            'followers.*' => 'exists:users,id',
            'is_anonymous' => 'nullable|boolean', // Make is_anonymous nullable
            'attachments' => 'nullable|array', // Change to array to allow multiple files
            'attachments.*' => 'file|mimes:pdf,doc,docx,png|max:10240', // Validate each file
            'links' => 'nullable|array',
            'links.*' => 'string|url',
            'comments' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id'
        ]);

        // Handle attachments - support both single and multiple files
        $attachments = [];
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');

            // Handle both array and single file upload
            if (is_array($files)) {
                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $path = $file->store('complaints', 'public');
                        $attachments[] = [
                            'original_name' => $file->getClientOriginalName(),
                            'file_path' => $path,
                            'mime_type' => $file->getMimeType(),
                            'size' => $file->getSize()
                        ];
                        Log::info('Document uploaded', ['document' => $file->getClientOriginalName(), 'path' => $path]);
                    } else {
                        Log::warning('Invalid file upload attempt', ['file' => $file->getClientOriginalName()]);
                    }
                }
            } else {
                // Single file upload
                if ($files->isValid()) {
                    $path = $files->store('complaints', 'public');
                    $attachments[] = [
                        'original_name' => $files->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $files->getMimeType(),
                        'size' => $files->getSize()
                    ];
                    Log::info('Document uploaded', ['document' => $files->getClientOriginalName(), 'path' => $path]);
                } else {
                    Log::warning('Invalid file upload attempt', ['file' => $files->getClientOriginalName()]);
                }
            }
        }

        // Store attachments in JSON format if available
        if (!empty($attachments)) {
            $data['attachments'] = json_encode($attachments);
        }

        // Handle links
        if ($request->has('links')) {
            $data['links'] = json_encode($request->input('links'));
        }

        $complaint->update($data);

        Log::info('Complaint status updated.', ['complaint_id' => $complaint]);
        // $this->LogAction($complaint->id, ' Updated', $complaint);



        // Attach Assigned Users (Role: addressed_to)
        if ($request->filled('addressed_to')) {
            $complaint->users()->syncWithoutDetaching(
                collect($request->addressed_to)->mapWithKeys(fn($userId) => [$userId => ['role' => 'addressed_to']])
            );
            Log::info('Assigned users updated.', ['complaint_id' => $complaint->id, 'users' => $request->addressed_to]);
        }

        // Attach Followers (Role: follower)
        if ($request->filled('followers')) {
            $complaint->users()->syncWithoutDetaching(
                collect($request->followers)->mapWithKeys(fn($userId) => [$userId => ['role' => 'follower']])
            );
            Log::info('Follower users updated.', ['complaint_id' => $complaint->id, 'users' => $request->followers]);
        }

        return response()->json(['complaint' => $complaint], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $complaint = $this->findComplaint($id);

        if (!$complaint) {
            return response()->json(['message' => 'Complaint not found'], Response::HTTP_NOT_FOUND);
        }

        $complaint->delete();

        return response()->json(['message' => 'Complaint deleted'], Response::HTTP_OK);
    }


    private function findComplaint($id)
    {
        return Complaint::find($id);
    }

    public function voiceLogs($id)
    {
        $complaintLog = ComplaintLog::with('user')->where('complaint_id',$id)->get();

        if (!$complaintLog) {
            return response()->json(['message' => 'Complaint log not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['logs' => $complaintLog], Response::HTTP_OK);
    }
}


    // private function LogAction($complaintId, $action, $details)
    // {
    //     $complaint = $this->findComplaint($complaintId);
    //     $previousData = $complaint ? $complaint->toArray() : [];

    //     // ComplaintLog model to store the actions
    //     ComplaintLog::create([
    //         'complaint_id' => $complaintId,
    //         'user_id' => auth()->id(),
    //         'action' => $action,
    //         'old_data' => json_encode($previousData),
    //         'new_data' => json_encode($complaint->toArray()),
    //         // 'performed_at' => Carbon::now(),
    //     ]);

    //     Log::info('Action stored.', [
    //         'complaint_id' => $complaintId,
    //         'action' => $action,
    //         'old_data' => $previousData,
    //         'new_data' => $complaint->toArray()
    //     ]);
    // }

  

