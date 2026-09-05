<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth('tenant')->user();
        
        // Fetch activity logs for this user/tenant
        $logs = ActivityLog::where('tenant_id', $user->id)
            ->where(function($q) use ($user) {
                // If it's the main tenant account, show all tenant logs? 
                // Prompts says "Users can ONLY edit their own profile" and "Activity Log (read-only)"
                // We'll scope to the current authenticated entity.
            })
            ->latest()
            ->paginate(10);

        return view('dashboard.profile.index', compact('user', 'logs'));
    }

    public function update(Request $request)
    {
        $user = auth('tenant')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'avatar_url' => 'nullable|url',
            'preferred_language' => 'required|in:en,ar',
            'timezone' => 'required|string',
            'notification_settings' => 'nullable|array',
        ]);

        $user->update($validated);

        ActivityLog::log('profile_updated', get_class($user), $user->id, ['fields' => array_keys($validated)]);

        return back()->with('success', __('hub.profile_updated_successfully'));
    }

    public function updateSecurity(Request $request)
    {
        $user = auth('tenant')->user();

        $request->validate([
            'current_password' => ['required', 'current_password:tenant'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        ActivityLog::log('password_changed', get_class($user), $user->id);

        return back()->with('success', __('hub.password_updated_successfully'));
    }
}
