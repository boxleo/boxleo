<?php

namespace App\Observers;

use App\Models\Complaint;
use App\Models\ComplaintLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ComplaintObserver
{
    /**
     * Handle the Complaint "created" event.
     */
 
    public function created(Complaint $complaint): void
    {
        ComplaintLog::create([
           'complaint_id' => $complaint->id,
           'user_id' => Auth::id(),
           'action' => 'created',
           'old_data' => null, // No old data for newly created complaints
           'new_data' => json_encode($complaint->toArray()),
           'changed_data' => json_encode($complaint->toArray()), // Everything is new
        ]);

        Log::info('Complaint created', [
           'complaint_id' => $complaint->id,
           'action' => 'Created',
           'new_data' => $complaint->toArray()
        ]);
    }
    /**
     * Handle the Complaint "updated" event.
     */
   
     public function updated(Complaint $complaint)
     {
         $original = $complaint->getOriginal();  // Old values
         $changes = $complaint->getChanges();    // Only changed values
 
         ComplaintLog::create([
             'complaint_id' => $complaint->id,
             'user_id' => auth()->id(),
             'action' => 'Updated voice',
             'old_data' => json_encode($original),
             'new_data' => json_encode($complaint->toArray()),
             'changed_data' => json_encode($changes),
         ]);
 
         Log::info('Complaint updated', [
             'complaint_id' => $complaint->id,
             'action' => 'Updated',
             'old_data' => $original,
             'new_data' => $complaint->toArray(),
             'changed_data' => $changes
         ]);
     }
 
   
    /**
     * Handle the Complaint "deleted" event.
     */
    public function deleted(Complaint $complaint)
    {
        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => auth()->id(),
            'action' => 'Deleted Complaint',
            'old_data' => json_encode($complaint->getOriginal()),
            'new_data' => null,
            'changed_data' => null,
        ]);

        Log::info('Complaint deleted', [
            'complaint_id' => $complaint->id,
            'action' => 'Deleted',
            'old_data' => $complaint->getOriginal()
        ]);
    }
    /**
     * Handle the Complaint "restored" event.
     */
    public function restored(Complaint $complaint): void
    {
        //
    }

    /**
     * Handle the Complaint "force deleted" event.
     */
    public function forceDeleted(Complaint $complaint): void
    {
        //
    }
}
