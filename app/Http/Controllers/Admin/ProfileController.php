<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();
        
        // System Activity (performed by this admin)
        $logs = AdminActivityLog::where('admin_user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('admin.profile.index', compact('user', 'logs'));
    }

    public function update(Request $request)
    {
        $user = auth('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'avatar_url' => 'nullable|url',
            'preferred_language' => 'required|in:en,ar',
            'timezone' => 'required|string',
            'notification_settings' => 'nullable|array',
        ]);

        $user->update($validated);

        AdminActivityLog::log('profile_updated', null, get_class($user), $user->id, ['fields' => array_keys($validated)]);

        return back()->with('success', __('hub.profile_updated_successfully'));
    }

    public function updateSecurity(Request $request)
    {
        $user = auth('admin')->user();

        $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        AdminActivityLog::log('password_changed', null, get_class($user), $user->id);

        return back()->with('success', __('hub.password_updated_successfully'));
    }
}
