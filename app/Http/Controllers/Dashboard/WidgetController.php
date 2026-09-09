<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TenantApiKey;
use App\Models\WidgetSetting;
use App\Services\TenantService;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    public function index()
    {
        $tenant = auth('tenant')->user();
        $settings = WidgetSetting::forTenant($tenant);

        // Fetch API keys
        $apiKeys = $tenant->apiKeys()->where('status', 'active')->latest()->get();

        // If no active API key exists, generate a default one for widget use
        if ($apiKeys->isEmpty()) {
            $defaultKey = $this->tenantService->generateApiKey($tenant, 'Default Widget Key');
            $apiKeys = collect([$defaultKey]);
        }

        $activeKey = $apiKeys->first()->key ?? $tenant->widget_key;
        $cdnUrl = url('/widget.js');

        $embedSnippet = "<script src=\"{$cdnUrl}\" data-api-key=\"{$activeKey}\"></script>";

        return view('dashboard.widget', compact('tenant', 'settings', 'apiKeys', 'activeKey', 'embedSnippet', 'cdnUrl'));
    }

    public function update(Request $request)
    {
        $tenant = auth('tenant')->user();
        $settings = WidgetSetting::forTenant($tenant);

        $request->validate([
            'primary_color'       => 'required|string|max:30',
            'bot_name'            => 'required|string|max:60',
            'bubble_title'        => 'nullable|string|max:100',
            'greeting_message'    => 'required|string|max:500',
            'placeholder_text'    => 'nullable|string|max:100',
            'theme'               => 'required|in:dark,light',
            'position'            => 'required|in:bottom-right,bottom-left',
            'suggested_questions' => 'nullable|array',
            'suggested_questions.*' => 'nullable|string|max:100',
            'allowed_domains'     => 'nullable|string',
        ]);

        // Clean suggested questions (filter out empty strings)
        $suggested = array_values(array_filter($request->input('suggested_questions', []), fn($q) => filled(trim($q))));

        // Parse allowed domains comma/newline list into array
        $domainsInput = $request->input('allowed_domains', '');
        $domains = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $domainsInput))));
        if (empty($domains)) {
            $domains = ['*'];
        }

        $settings->update([
            'primary_color'       => $request->input('primary_color'),
            'bot_name'            => $request->input('bot_name'),
            'bubble_title'        => $request->input('bubble_title') ?: 'Chat with us',
            'greeting_message'    => $request->input('greeting_message'),
            'placeholder_text'    => $request->input('placeholder_text') ?: 'Type a message...',
            'theme'               => $request->input('theme'),
            'position'            => $request->input('position'),
            'suggested_questions' => $suggested,
            'allowed_domains'     => $domains,
            'is_enabled'          => $request->boolean('is_enabled'),
            'sound_enabled'       => $request->boolean('sound_enabled'),
        ]);

        return back()->with('success', 'Chat Widget settings updated successfully!');
    }
}
