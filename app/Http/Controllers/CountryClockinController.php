<?php

namespace App\Http\Controllers;

use App\Models\CountryClockinSetting;
use App\Models\CountryClockinOverride;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CountryClockinController extends Controller
{
    public function index()
    {
        $settings = CountryClockinSetting::with([
            'overrides' => fn($q) => $q->where('is_active', true)->orderBy('override_date')
        ])
        ->where('is_active', true)
        ->orderBy('country')
        ->get()
        ->map(fn($s) => [
            'id'                    => $s->id,
            'country'               => $s->country,
            'country_code'          => $s->country_code,
            'default_clockin_time'  => $s->default_clockin_time,
            'default_clockout_time' => $s->default_clockout_time,
            'grace_minutes'         => $s->grace_minutes,
            'timezone'              => $s->timezone,
            'today_clockin_time'    => $s->todayClockinTime(),
            'has_today_override'    => $s->hasTodayOverride(),
            'overrides'             => $s->overrides->map(fn($o) => [
                'id'            => $o->id,
                'override_date' => $o->override_date->format('Y-m-d'),
                'clockin_time'  => $o->clockin_time,
                'clockout_time' => $o->clockout_time,
                'reason'        => $o->reason,
                'type'          => $o->type,
            ]),
        ]);

        return Inertia::render('Attendances/ClockinSettings', ['settings' => $settings]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'country'               => 'required|string|max:100',
            'country_code'          => 'required|string|max:5',
            'default_clockin_time'  => 'required|date_format:H:i',
            'default_clockout_time' => 'required|date_format:H:i',
            'grace_minutes'         => 'required|integer|min:0|max:60',
            'timezone'              => 'required|timezone',
        ]);

        CountryClockinSetting::create($data);

        return back()->with('success', "Clock-in settings for {$data['country']} added.");
    }

    public function update(Request $request, CountryClockinSetting $setting)
    {
        $data = $request->validate([
            'default_clockin_time'  => 'required|date_format:H:i',
            'default_clockout_time' => 'required|date_format:H:i',
            'grace_minutes'         => 'required|integer|min:0|max:60',
            'timezone'              => 'required|timezone',
        ]);

        $setting->update($data);

        return back()->with('success', "Default times for {$setting->country} updated.");
    }

    public function addOverride(Request $request, CountryClockinSetting $setting)
    {
        $data = $request->validate([
            'override_date' => 'required|date',
            'clockin_time'  => 'required|date_format:H:i',
            'clockout_time' => 'nullable|date_format:H:i',
            'reason'        => 'nullable|string|max:255',
            'type'          => 'required|in:temporary,permanent',
        ]);

        // Deactivate any existing override for same date
        $setting->overrides()->where('override_date', $data['override_date'])->update(['is_active' => false]);

        $setting->overrides()->create([...$data, 'is_active' => true, 'created_by' => auth()->id()]);

        return back()->with('success', "Override for {$setting->country} on {$data['override_date']} saved.");
    }

    public function removeOverride(CountryClockinOverride $override)
    {
        $label = "{$override->setting->country} — {$override->override_date->format('d M Y')}";
        $override->update(['is_active' => false]);
        return back()->with('success', "Override removed for {$label}.");
    }
}
