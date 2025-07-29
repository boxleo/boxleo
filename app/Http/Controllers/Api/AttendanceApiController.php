<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Carbon;


class AttendanceApiController extends Controller
{
    public function index(Request $request)
    {
        $unit_id = $request->unit_id ?? auth()->user()->unit_id;

        $query = Attendance::with('user.unit')
            ->whereHas('user', function ($query) use ($unit_id) {
                $query->where('unit_id', $unit_id)
                    ->whereNull('deleted_at');
            })
            ->orderBy('created_at', 'desc');

        $this->applyFilters($request, $query);

        $attendances = $query->take(2000)->get();

        $totalEmployees = User::whereNull('deleted_at')
            ->where('super_admin', 0)
            ->where('unit_id', $unit_id)
            ->count();

        $attendanceStats = $this->calculateAttendanceStatistics($attendances, $totalEmployees);

        return response()->json([
            'attendances' => $attendances,
            'average_presence_percentage' => $attendanceStats['average_presence_percentage'],
            'average_clock_in_time' => $attendanceStats['average_clock_in_time'],
            'average_clock_out_time' => $attendanceStats['average_clock_out_time'],
            'average_working_hours' => $attendanceStats['average_working_hours'],
        ]);
    }

    public function attendanceAnalytics()
    {
        $startDate = Carbon::now()->startOfWeek()->subWeek();
        $endDate = $startDate->copy()->addDays(6);
        $attendances = Attendance::whereBetween('attendance_date', [$startDate, $endDate])->get();
        $leaves = Leave::where('status', 'Approved')
            ->where(fn($query) => $query->whereBetween('from', [$startDate, $endDate])
                ->orWhereBetween('to', [$startDate, $endDate]))
            ->get();

        $attendanceData = [];
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $dayData = $this->getDayAttendanceData($date, $attendances, $leaves);
            $attendanceData[$date->format('l')] = [$dayData];
        }

        return response()->json($attendanceData);
    }

    public function userAttendance(Request $request)
    {
        $attendances = Attendance::where('user_id', $request->user_id)
            ->orderBy('attendance_date', 'desc')
            ->get();

        $attendances->transform(function ($attendance) {
            $attendanceDate = new DateTime($attendance->attendance_date);
            $attendance->attendance_date = $attendanceDate->format('D d M Y');
            return $attendance;
        });

        return response()->json(['attendances' => $attendances]);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Store method called', ['request' => $request->all()]);

            $this->validateAttendanceRequest($request);

            // Find existing attendance for this user and date
            $existingAttendance = Attendance::where('user_id', $request->user_id)
                ->where('attendance_date', $request->attendance_date)
                ->first();

            // If clocking in and no record exists, allow creation
            // If clocking out and record exists with clock_in_time but no clock_out_time, allow clock out
            // If clocking in and record exists with clock_in_time, prevent duplicate clock in
            // If clocking out and record exists with clock_out_time, update  clock out

            // if (
            //     ($request->attendance_type === 'clock_in' && $existingAttendance && $existingAttendance->clock_in_time) ||
            //     ($request->attendance_type === 'clock_out' && (!$existingAttendance || $existingAttendance->clock_out_time))
            // ) {
            //     Log::warning('Attendance already marked', [
            //         'user_id' => $request->user_id,
            //         'attendance_date' => $request->attendance_date,
            //         'attendance_type' => $request->attendance_type,
            //     ]);
            //     return response()->json(['message' => 'Attendance is already marked!'], 400);
            // }



            if ($request->attendance_type === 'clock_in' && $existingAttendance && $existingAttendance->clock_in_time) {
                Log::warning('Clock-in already marked', [
                    'user_id' => $request->user_id,
                    'attendance_date' => $request->attendance_date,
                ]);
                return response()->json(['message' => 'Clock-in already marked!'], 400);
            }

            if ($request->attendance_type === 'clock_out' && !$existingAttendance) {
                Log::warning('Cannot clock out without clocking in', [
                    'user_id' => $request->user_id,
                    'attendance_date' => $request->attendance_date,
                ]);
                return response()->json(['message' => 'No clock-in record found!'], 400);
            }


            if ($existingAttendance) {
                Log::warning('Attendance already marked', [
                    'user_id' => $request->user_id,
                    'attendance_date' => $request->attendance_date,
                ]);
                return response()->json(['message' => 'Attendance is already marked!'], 400);
            }

            $user = User::find($request->user_id);
            if (!$user) {
                Log::error('User not found', ['user_id' => $request->user_id]);
                return response()->json(['error' => 'User not found'], 404);
            }

            $office = $user->office;
            if (!$office) {
                Log::error('Office not found for user', ['user_id' => $request->user_id]);
                return response()->json(['error' => 'Office not found'], 404);
            }

            Log::info('Validating location', [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'office' => $office,
            ]);

            $distanceFromPremise = $this->validateLocation($request->latitude, $request->longitude, $office);

            Log::info('Distance from premise calculated', ['distance' => $distanceFromPremise]);

            $notes = $distanceFromPremise ? "Distance from the premise: " . $distanceFromPremise : null;

            Log::info('Processing attendance', [
                'user_id' => $request->user_id,
                'attendance_date' => $request->attendance_date,
                'notes' => $notes,
            ]);

            $this->processAttendance($request, $notes);

            Log::info('Attendance record created successfully', [
                'user_id' => $request->user_id,
                'attendance_date' => $request->attendance_date,
            ]);

            return response()->json(['message' => 'Record created!'], 200);
        } catch (\Exception $e) {
            Log::error('Error in store method', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Attendance $attendance)
    {
        try {
            $this->validateAttendanceRequest($request);
            $attendance->update($request->all());
            return response()->json(['message' => 'Attendance record updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error in update method: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:In Time,Late',
            'ids' => 'required|array',
            'ids.*' => 'exists:attendances,id',
        ]);

        Attendance::whereIn('id', $request->ids)->update(['status' => $request->status]);

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return response()->json(['message' => 'Attendance deleted successfully']);
    }

    private function applyFilters(Request $request, $query)
    {
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('attendance_date')) {
            $query->whereDate('attendance_date', $request->attendance_date);
        }

        if ($request->has('unit_id')) {
            $query->whereHas('user', fn($userQuery) => $userQuery->where('unit_id', $request->unit_id));
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
    }

    private function calculateAttendanceStatistics($attendances, $totalEmployees)
    {
        $today = date('Y-m-d');
        $todayAttendances = $attendances->filter(fn($attendance) => $attendance->attendance_date == $today);
        $uniqueDailyAttendancesToday = $todayAttendances->groupBy('user_id');

        $totalPresentToday = $uniqueDailyAttendancesToday->filter(fn($group) => $group->first()->is_present)->count();
        $totalMarkedAttendanceToday = $uniqueDailyAttendancesToday->count();

        $averagePresencePercentage = $totalEmployees > 0 ? ($totalMarkedAttendanceToday / $totalEmployees) * 100 : 0;
        $averagePresencePercentage = number_format($averagePresencePercentage, 2);

        $uniqueDailyAttendances = $attendances->groupBy(fn($attendance) => $attendance->attendance_date . '_' . $attendance->user_id);

        $totalClockInTime = $uniqueDailyAttendances->sum(fn($group) => strtotime($group->first()->clock_in_time));
        $countClockOutTime = $uniqueDailyAttendances->filter(fn($group) => $group->first()->clock_out_time)->count();
        $totalClockOutTime = $uniqueDailyAttendances->sum(fn($group) => strtotime($group->first()->clock_out_time) ?: 0);
        $totalWorkingHours = $uniqueDailyAttendances->sum(fn($group) => $group->first()->hours_worked);

        $totalMarkedAttendance = $uniqueDailyAttendances->count();

        $averageClockInTime = $totalMarkedAttendance > 0 ? date('H:i:s', $totalClockInTime / $totalMarkedAttendance) : '00:00:00';
        $averageClockOutTime = $countClockOutTime > 0 ? date('H:i:s', $totalClockOutTime / $countClockOutTime) : '00:00:00';

        $averageClockInSeconds = strtotime($averageClockInTime);
        $averageClockOutSeconds = strtotime($averageClockOutTime);

        $averageWorkingSeconds = $averageClockOutSeconds - $averageClockInSeconds;
        $averageWorkingHoursCalculated = $averageWorkingSeconds > 0 ? $averageWorkingSeconds / 3600 : 9.0;

        $averageWorkingHours = number_format($averageWorkingHoursCalculated, 2);

        return [
            'average_presence_percentage' => $averagePresencePercentage,
            'average_clock_in_time' => $averageClockInTime,
            'average_clock_out_time' => $averageClockOutTime,
            'average_working_hours' => $averageWorkingHours,
        ];
    }

    private function getDayAttendanceData($date, $attendances, $leaves)
    {
        $dayData = [
            'in_time' => 0,
            'late' => 0,
            'on_leave' => 0,
        ];

        foreach ($attendances as $attendance) {
            if (Carbon::parse($attendance->attendance_date)->format('Y-m-d') === $date->format('Y-m-d')) {
                $dayData[$attendance->status === 'In Time' ? 'in_time' : 'late']++;
            }
        }

        foreach ($leaves as $leave) {
            if ($date->between($leave->from, $leave->to)) {
                $dayData['on_leave']++;
            }
        }

        return $dayData;
    }

    private function validateAttendanceRequest(Request $request)
    {
        $request->validate([
            'attendance_type' => 'required|in:clock_in,clock_out',
            'user_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'time' => 'required|date_format:H:i:s',
        ]);
    }

    // private function validateLocation($userLatitude, $userLongitude)
    // {
    //     $premiseLatitude = -1.3473844528198242;
    //     $premiseLongitude = 36.901023864746094;

    //     $distanceInKilometers = $this->haversineDistance($premiseLatitude, $premiseLongitude, $userLatitude, $userLongitude);

    //     $formattedDistance = number_format($distanceInKilometers, 2) . ' m';

    //     Log::info('Distance from premise:', ['distance' => $formattedDistance]);

    //     return $formattedDistance;
    // }



    private function validateLocation($userLatitude, $userLongitude, $office)
    {
        $distanceInKilometers = $this->haversineDistance(
            $office->latitude,
            $office->longitude,
            $userLatitude,
            $userLongitude
        );

        $formattedDistance = number_format($distanceInKilometers, 2) . ' km';

        Log::info('Distance from premise:', ['distance' => $formattedDistance]);

        return $formattedDistance;
    }


    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371; // Radius of Earth in kilometers

        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Haversine formula
        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($lat1) * cos($lat2) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Distance in kilometers
        $distance = $earth_radius * $c;

        return $distance;
    }

    private function processAttendance(Request $request, $notes = null)
    {
        Log::info('Processing attendance', [
            'user_id' => $request->user_id,
            'attendance_date' => $request->attendance_date,
            'attendance_type' => $request->attendance_type,
            'time' => $request->time,
            'notes' => $notes,
        ]);

        $existingAttendance = Attendance::firstOrNew([
            'user_id' => $request->user_id,
            'attendance_date' => $request->attendance_date,
        ]);

        Log::info('Existing attendance record', ['attendance' => $existingAttendance]);

        $user = Auth::user();
        Log::info('Authenticated user', ['user' => $user]);

        $unit = $user->unit;
        if (!$unit) {
            Log::error('User unit not found', ['user_id' => $request->user_id]);
            throw new \Exception('User unit not found.');
        }

        Log::info('User unit retrieved', ['unit' => $unit]);

        if ($request->attendance_type === 'clock_in' && !$existingAttendance->clock_in_time) {
            $existingAttendance->clock_in_time = $request->time;
            // $existingAttendance->status = $this->isLate($request->time, $unit) ? 'Late' : 'In Time';

            $existingAttendance->status = $this->isLate($request->time, $unit, $request->attendance_date) ? 'Late' : 'In Time';




            Log::info('Clock-in time set', [
                'clock_in_time' => $request->time,
                'status' => $existingAttendance->status,
            ]);
        } elseif ($request->attendance_type === 'clock_out' && !$existingAttendance->clock_out_time) {
            $existingAttendance->clock_out_time = $request->time;
            Log::info('Clock-out time set', ['clock_out_time' => $request->time]);
        }

        if ($notes) {
            $existingAttendance->notes = $notes;
            Log::info('Notes added to attendance', ['notes' => $notes]);
        }

        $existingAttendance->is_present = true;
        $existingAttendance->save();

        Log::info('Attendance record saved successfully', ['attendance' => $existingAttendance]);
    }


    // private function isLate($clockInTime, $unit)
    // {
    //     // Validate input
    //     if (!$unit) {
    //         Log::error('Invalid unit provided to isLate function');
    //         return false;
    //     }

    //     // Convert clock-in time to unit's timezone
    //     $userTime = Carbon::parse($clockInTime)->setTimezone($unit->timezone);

    //     // Set default thresholds
    //     $defaultLateThreshold = $unit->late_threshold ?? '08:00';
    //     $weekendThreshold = $unit->weekend_threshold ?? '08:30';

    //     // Get day of week (0 = Sunday, 6 = Saturday)
    //     $userDayOfWeek = $userTime->dayOfWeek;

    //     // Parse weekend day configuration - handle both integer and string inputs
    //     $weekendDays = [];
    //     if (isset($unit->weekend_day)) {
    //         if (is_array($unit->weekend_day)) {
    //             $weekendDays = $unit->weekend_day;
    //         } else {
    //             $weekendDays = [$unit->weekend_day];
    //         }
    //     } else {
    //         // Default to Saturday only
    //         $weekendDays = [Carbon::SATURDAY];
    //     }

    //     // Check if it's a weekend
    //     $isWeekend = in_array($userDayOfWeek, $weekendDays);

    //     // Check if it's a holiday
    //     $isHoliday = Holiday::whereDate('date', $userTime->toDateString())->exists();

    //     // Decide which threshold to use
    //     $lateThreshold = ($isWeekend || $isHoliday) ? $weekendThreshold : $defaultLateThreshold;

    //     // Create a Carbon instance for the threshold time on the same day
    //     $thresholdTime = Carbon::parse(
    //         $userTime->toDateString() . ' ' . $lateThreshold,
    //         $unit->timezone
    //     )->setTimezone('UTC');

    //     Log::info('Evaluating lateness', [
    //         'clock_in_time_utc' => $clockInTime,
    //         'user_time' => $userTime->toDateTimeString(),
    //         'is_weekend' => $isWeekend,
    //         'is_holiday' => $isHoliday,
    //         'threshold_local' => $lateThreshold,
    //         'threshold_utc' => $thresholdTime->toDateTimeString(),
    //     ]);

    //     // Determine if the user is late
    //     return Carbon::parse($clockInTime)->greaterThan($thresholdTime);
    // }



    private function isLate($clockInTime, $unit, $attendanceDate = null)
    {
        // Validate input
        if (!$unit) {
            Log::error('Invalid unit provided to isLate function');
            return false;
        }

        // Ensure we have a full date and time for processing.
        // If clockInTime is just a time (HH:MM:SS) and attendanceDate is provided, combine them.
        // Otherwise, assume clockInTime is a full datetime string.
        $dateTimeToParse = $clockInTime;
        if ($attendanceDate && preg_match('/^\d{2}:\d{2}:\d{2}$/', $clockInTime)) {
            $dateTimeToParse = $attendanceDate . ' ' . $clockInTime;
        }

        Log::info('Time parsing', [
            'original_clockInTime' => $clockInTime,
            'attendanceDate_provided' => $attendanceDate,
            'final_dateTimeToParse' => $dateTimeToParse
        ]);

        try {
            // Parse the clock-in time and set it to the unit's timezone.
            // This 'userTime' represents the actual clock-in moment in the user's local time.
            // $userTime = Carbon::parse($dateTimeToParse)->setTimezone($unit->timezone);

            $userTime = Carbon::parse($dateTimeToParse); // Do NOT setTimezone again

        } catch (\Exception $e) {
            Log::error('Failed to parse clockInTime or attendanceDate combination: ' . $e->getMessage(), [
                'clockInTime' => $clockInTime,
                'attendanceDate' => $attendanceDate
            ]);
            return false; // Or handle error appropriately
        }

        // Set default thresholds
        // It's good practice to ensure these are valid time strings.
        $defaultLateThreshold = $unit->late_threshold ?? '08:00';
        $weekendThreshold = $unit->weekend_threshold ?? '08:30';
        $sundayThreshold = $unit->sunday_threshold ?? '11:00'; // Specific Sunday threshold

        // Get day of week (0 = Sunday, 6 = Saturday)
        $userDayOfWeek = $userTime->dayOfWeek;

        // Parse weekend day configuration - handle both integer and array inputs
        $weekendDays = [];
        if (isset($unit->weekend_day)) {
            $weekendDays = is_array($unit->weekend_day)
                ? $unit->weekend_day
                : [$unit->weekend_day];
        } else {
            // Default to Saturday only if not configured
            $weekendDays = [Carbon::SATURDAY];
        }

        // Check if it's a holiday for the specific date of the clock-in

        // check if there is a holiday in the unit_id = user,unit
        // $isHoliday = Holiday::whereDate('date', $userTime->toDateString())->exists();
        $isHoliday = Holiday::whereDate('date', $userTime->toDateString())->exists();


        // Determine which threshold to use based on the day of the week and holidays
        $lateThreshold = '';
        if ($userDayOfWeek === Carbon::SUNDAY) {
            $lateThreshold = $sundayThreshold;
        } elseif (in_array($userDayOfWeek, $weekendDays) || $isHoliday) {
            $lateThreshold = $weekendThreshold;
        } else {
            $lateThreshold = $defaultLateThreshold;
        }

        // Create the threshold time on the *same day* as the user's clock-in,
        // and ensure it's in the *same timezone* as userTime for accurate comparison.
        // $thresholdTime = Carbon::parse(
        //     $userTime->toDateString() . ' ' . $lateThreshold,
        //     $unit->timezone
        // );


        $thresholdTime = $userTime->copy()->setTimeFromTimeString($lateThreshold);

        Log::info('Threshold time calculation', [
            'late_threshold' => $lateThreshold,
            'threshold_time_full_datetime' => $thresholdTime->toDateTimeString(),
        ]);


        Log::info('Evaluating lateness', [
            'clock_in_datetime' => $userTime->toDateTimeString(), // Clock-in in user's timezone
            'user_day_of_week' => $userDayOfWeek,
            'is_sunday' => $userDayOfWeek === Carbon::SUNDAY,
            'is_weekend_day_configured' => in_array($userDayOfWeek, $weekendDays),
            'is_holiday' => $isHoliday,
            'threshold_used_time_string' => $lateThreshold, // The time string used (e.g., '08:00')
            'threshold_time_full_datetime' => $thresholdTime->toDateTimeString(), // Threshold in user's timezone
            'unit_timezone' => $unit->timezone
        ]);

        // Compare the user's actual clock-in time with the calculated threshold time.
        // Both are now Carbon instances in the same timezone, allowing for a direct and accurate comparison.
        return $userTime->greaterThan($thresholdTime);
    }


    public function fetchServerTime(): JsonResponse
    {
        // Get the current server time
        $serverTime = now()->toDateTimeString(); // Uses Laravel's `now()` helper

        // Return the response as JSON
        return response()->json([
            'time' => $serverTime,
        ]);
    }

    public function Usertimezone(Request $request): JsonResponse
    {
        $user = Auth::user();
        // Find the user
        if (!$user || !$user->unit) {
            return response()->json(['error' => 'User or unit not found'], 404);
        }

        // Get the user's timezone
        $timezone = $user->unit->timezone;

        // Return the response as JSON
        return response()->json([
            'timezone' => $timezone,
            'current_time' => now()->setTimezone($timezone)->toDateTimeString(),
        ]);
    }



    // public function syncZkteco(Request $request)
    // {
    //     Log::info('syncZkteco called', ['request_data' => $request->all()]);

    //     $records = collect($request->input('records', []));
    //     Log::info('Records received', ['count' => $records->count()]);

    //     $users = User::whereIn('zk_user_id', $records->pluck('user_id'))->get()->keyBy('zk_user_id');
    //     Log::info('Users fetched for zk_user_ids', ['user_ids' => $records->pluck('user_id')->unique()->values()->all()]);

    //     $grouped = $records->groupBy(function ($item) {
    //         return $item['user_id'] . '_' . Carbon::parse($item['time'])->toDateString();
    //     });

    //     foreach ($grouped as $groupKey => $userRecords) {
    //         Log::info('Processing group', ['groupKey' => $groupKey, 'records' => $userRecords->toArray()]);

    //         $sorted = $userRecords->sortBy('time')->values();
    //         $zkUserId = explode('_', $groupKey)[0];
    //         $date = Carbon::parse($sorted->first()['time'])->toDateString();

    //         $user = $users->get($zkUserId);
    //         if (!$user) {
    //             Log::warning("User not found for zk_id: $zkUserId");
    //             continue;
    //         }

    //         $clockIn = Carbon::parse($sorted->first()['time'])->format('H:i:s');
    //         $clockOut = Carbon::parse($sorted->last()['time'])->format('H:i:s');

    //         Log::info('Attendance data prepared', [
    //             'user_id' => $user->id,
    //             'date' => $date,
    //             'clock_in' => $clockIn,
    //             'clock_out' => $clockOut,
    //         ]);

    //         // Use helper method
    //         $this->processAttendanceFromZkteco(
    //             $user,
    //             $date,
    //             $clockIn,
    //             $clockOut,
    //             $user->unit,
    //             'Auto-synced from ZKTeco'
    //         );

    //         Log::info("Synced attendance for user", [
    //             'user_id' => $user->id,
    //             'user_name' => $user->name ?? ($user->firstname ?? '') . ' ' . ($user->lastname ?? ''),
    //             'date' => $date
    //         ]);
    //     }

    //     Log::info('ZKTeco sync completed');
    //     return response()->json(['message' => 'ZKTeco records synced']);
    // }




    public function syncZkteco(Request $request)
    {
        Log::info('syncZkteco called', ['request_data' => $request->all()]);

        $records = collect($request->input('records', []));
        Log::info('Records received', ['count' => $records->count()]);

        if ($records->isEmpty()) {
            return response()->json(['message' => 'No records to sync'], 400);
        }

        $users = User::whereIn('zk_user_id', $records->pluck('user_id'))->get()->keyBy('zk_user_id');
        Log::info('Users fetched for zk_user_ids', ['user_ids' => $records->pluck('user_id')->unique()->values()->all()]);

        foreach ($records as $record) {
            $zkUserId = $record['user_id'];
            $user = $users->get($zkUserId);

            if (!$user) {
                Log::warning("User not found for zk_user_id: $zkUserId");
                continue;
            }

            $date = $record['date'];
            $clockIn = $record['clock_in'];
            $clockOut = $record['clock_out'];

            Log::info('Attendance data prepared', [
                'user_id' => $user->id,
                'date' => $date,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
            ]);

            // Use the helper method
            $this->processAttendanceFromZkteco(
                $user,
                $date,
                $clockIn,
                $clockOut,
                $user->unit,
                'Auto-synced from ZKTeco'
            );

            Log::info("Synced attendance for user", [
                'user_id' => $user->id,
                'user_name' => $user->name ?? ($user->firstname ?? '') . ' ' . ($user->lastname ?? ''),
                'date' => $date
            ]);
        }

        Log::info('ZKTeco sync completed');
        return response()->json(['message' => 'ZKTeco records synced']);
    }





    // private function processAttendanceFromZkteco($user, $date, $clockIn, $clockOut, $unit, $notes = null)
    // {
    //     Log::info('syncZktecoProcessed called', ['request_data' => $request->all()]);

    //     $records = collect($request->input('records', []));
    //     Log::info('Processed records received', ['count' => $records->count()]);

    //     $users = User::whereIn('zk_user_id', $records->pluck('user_id'))->get()->keyBy('zk_user_id');

    //     foreach ($records as $record) {
    //         $user = $users->get($record['user_id']);

    //         if (!$user) {
    //             Log::warning("User not found for zk_user_id: {$record['user_id']}");
    //             continue;
    //         }

    //         $attendance = Attendance::updateOrCreate(
    //             [
    //                 'user_id' => $user->id,
    //                 'attendance_date' => $record['date'],
    //             ],
    //             [
    //                 'clock_in_time' => $record['clock_in'],
    //                 'clock_out_time' => $record['clock_out'],
    //                 'final_clock_time' => $record['final_clock'] ?? $record['clock_out'],
    //                 'raw_punches' => json_encode($record['raw_punches']),
    //                 'in_out_pairs' => json_encode($record['in_out_pairs']),
    //                 'is_present' => true,
    //                 'hours_worked' => Carbon::parse($record['clock_out'])->diffInHours(Carbon::parse($record['clock_in'])),
    //                 'status' => $this->isLateFromZkteco($record['clock_in'], $user->unit) ? 'Late' : 'In Time',
    //                 'notes' => 'Processed ZKTeco sync',
    //                 'ip_address' => request()->ip(),
    //             ]
    //         );

    //         Log::info('Processed ZKTeco attendance saved', [
    //             'attendance_id' => $attendance->id,
    //             'user_id' => $user->id,
    //             'date' => $record['date'],
    //         ]);
    //     }

    //     return response()->json(['message' => 'Processed ZKTeco records synced']);
    // }




    private function processAttendanceFromZkteco($user, $date, $clockIn, $clockOut, $unit, $notes = null)
    {
        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'attendance_date' => $date,
            ],
            [
                'clock_in_time' => $clockIn,
                'clock_out_time' => $clockOut,
                'final_clock_time' => $clockOut,
                'raw_punches' => null, // Optional: fill if available
                'in_out_pairs' => null, // Optional: fill if available
                'is_present' => true,
                'hours_worked' => Carbon::parse($clockOut)->diffInHours(Carbon::parse($clockIn)),
                'status' => $this->isLateFromZkteco($clockIn, $unit) ? 'Late' : 'In Time',
                'notes' => $notes ?? 'Processed ZKTeco sync',
                'ip_address' => request()->ip(),
            ]
        );

        Log::info('Processed ZKTeco attendance saved', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'date' => $date,
        ]);
    }



    private function isLateFromZkteco($clockInTime, $unit)
    {
        Log::info('isLateFromZkteco called', [
            'clockInTime' => $clockInTime,
            'unit_id' => $unit ? $unit->id : null,
            'unit_timezone' => $unit ? $unit->timezone : null,
            'unit_late_threshold' => $unit->late_threshold ?? null,
            'unit_weekend_threshold' => $unit->weekend_threshold ?? null,
            'unit_sunday_threshold' => $unit->sunday_threshold ?? null,
            'unit_weekend_day' => $unit->weekend_day ?? null,
        ]);

        if (!$unit) {
            Log::warning('Missing unit for lateness check');
            return false;
        }

        try {
            $userTime = Carbon::parse($clockInTime)->setTimezone($unit->timezone);
        } catch (\Exception $e) {
            Log::error('Failed to parse clockInTime in isLateFromZkteco', [
                'clockInTime' => $clockInTime,
                'error' => $e->getMessage(),
            ]);
            return false;
        }

        $defaultLateThreshold = $unit->late_threshold ?? '08:00';
        $weekendThreshold = $unit->weekend_threshold ?? '08:30';
        $sundayThreshold = $unit->sunday_threshold ?? '11:00';

        // Always override with 11:00 for Sunday threshold as in original code
        $sundayThreshold = '11:00';

        $userDayOfWeek = $userTime->dayOfWeek;

        $weekendDays = is_array($unit->weekend_day)
            ? $unit->weekend_day
            : [$unit->weekend_day ?? Carbon::SATURDAY];

        $isWeekend = in_array($userDayOfWeek, $weekendDays);
        $isHoliday = Holiday::whereDate('date', $userTime->toDateString())->exists();

        Log::info('isLateFromZkteco day checks', [
            'user_day_of_week' => $userDayOfWeek,
            'is_weekend' => $isWeekend,
            'is_holiday' => $isHoliday,
            'weekend_days' => $weekendDays,
        ]);

        // Check for Sunday specifically
        if ($userDayOfWeek === Carbon::SUNDAY) {
            $lateThreshold = $sundayThreshold;
        } elseif ($isWeekend || $isHoliday) {
            $lateThreshold = $weekendThreshold;
        } else {
            $lateThreshold = $defaultLateThreshold;
        }

        $thresholdTime = Carbon::parse(
            $userTime->toDateString() . ' ' . $lateThreshold,
            $unit->timezone
        )->setTimezone('UTC');

        Log::info('Evaluating lateness for ZKTeco', [
            'user_time' => $userTime->toDateTimeString(),
            'threshold' => $thresholdTime->toDateTimeString(),
            'lateThreshold' => $lateThreshold,
            'comparison' => Carbon::parse($clockInTime)->toDateTimeString() . ' > ' . $thresholdTime->toDateTimeString(),
        ]);

        $isLate = Carbon::parse($clockInTime)->greaterThan($thresholdTime);

        Log::info('isLateFromZkteco result', [
            'isLate' => $isLate,
        ]);

        return $isLate;
    }




    // private function processAttendanceFromZkteco($user, $date, $clockIn, $clockOut, $unit, $notes = null)
    // {
    //     Log::info('Processing ZKTeco attendance', [
    //         'user_id' => $user->id,
    //         'date' => $date,
    //         'clock_in' => $clockIn,
    //         'clock_out' => $clockOut,
    //     ]);

    //     $existingAttendance = Attendance::firstOrNew([
    //         'user_id' => $user->id,
    //         'attendance_date' => $date,
    //     ]);

    //     if (!$existingAttendance->clock_in_time) {
    //         $existingAttendance->clock_in_time = $clockIn;
    //         $existingAttendance->status = $this->isLateFromZkteco($clockIn, $unit) ? 'Late' : 'In Time';
    //         Log::info('Clock-in set for ZKTeco', [
    //             'status' => $existingAttendance->status
    //         ]);
    //     }

    //     if (!$existingAttendance->clock_out_time) {
    //         $existingAttendance->clock_out_time = $clockOut;
    //         Log::info('Clock-out set for ZKTeco');
    //     }

    //     $existingAttendance->hours_worked = Carbon::parse($clockOut)->diffInHours(Carbon::parse($clockIn));
    //     $existingAttendance->is_present = true;
    //     $existingAttendance->notes = $notes ?? 'Auto-synced from ZKTeco';
    //     $existingAttendance->status = $existingAttendance->status ?? 'Present';
    //     $existingAttendance->ip_address = request()->ip();
    //     $existingAttendance->save();

    //     Log::info('ZKTeco attendance saved', ['attendance_id' => $existingAttendance->id]);
    // }
}
