@extends('layouts.dashboard')

@section('title', __('hub.my_profile'))

@section('content')
<div class="max-w-6xl mx-auto space-y-8 {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}" x-data="{ tab: 'info' }">
    <!-- Header Card -->
    <div class="glass p-8 rounded-[2.5rem] border border-white/10 relative overflow-hidden">
        <div class="absolute top-0 {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} p-12 opacity-5 pointer-events-none">
            <svg class="w-48 h-48 text-indigo-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </div>

        <div class="flex flex-col md:flex-row items-center gap-8 relative z-10">
            <!-- Avatar -->
            <div class="relative group">
                <div class="w-32 h-32 rounded-3xl bg-slate-800 flex items-center justify-center border-2 border-white/10 overflow-hidden shadow-2xl group-hover:border-indigo-500/50 transition-all duration-500">
                    <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff&size=200' }}" 
                         class="w-full h-full object-cover" alt="{{ $user->name }}">
                </div>
                <button class="absolute -bottom-2 {{ app()->getLocale() == 'ar' ? '-left-2' : '-right-2' }} w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg hover:bg-indigo-500 transition-all active:scale-90 border border-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </button>
            </div>

            <div class="flex-1 text-center md:{{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }} space-y-2">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                    <h2 class="text-3xl font-black">{{ $user->name }}</h2>
                    <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-full text-[10px] font-black uppercase tracking-widest">
                        {{ $user->role ?? 'Tenant Admin' }}
                    </span>
                    <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[10px] font-black uppercase tracking-widest">
                        {{ __('hub.active') }}
                    </span>
                </div>
                <p class="text-slate-400 font-medium">{{ $user->email }}</p>
                
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-6 mt-4 pt-4 border-t border-white/5">
                    <div class="flex items-center gap-2 text-xs text-slate-500 font-bold">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ __('hub.joined') }} {{ $user->created_at->format('M d, Y') }}
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 font-bold">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('hub.last_login') }} {{ $user->last_login_at?->diffForHumans() ?? __('hub.never') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Content -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1 space-y-2">
            @php
                $tabClass = "w-full px-6 py-4 flex items-center gap-4 font-bold transition-all " . (app()->getLocale() == 'ar' ? 'rounded-l-2xl' : 'rounded-r-2xl');
                $activeTabClass = "bg-indigo-500/10 text-indigo-400 " . (app()->getLocale() == 'ar' ? 'border-r-4' : 'border-l-4') . " border-indigo-500";
                $inactiveTabClass = "text-slate-500 hover:bg-white/5";
            @endphp

            <button @click="tab = 'info'" :class="tab === 'info' ? '{{ $activeTabClass }}' : '{{ $inactiveTabClass }}'" class="{{ $tabClass }}">
                <svg class="w-5 h-5 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                {{ __('hub.profile_info') }}
            </button>
            <button @click="tab = 'security'" :class="tab === 'security' ? '{{ $activeTabClass }}' : '{{ $inactiveTabClass }}'" class="{{ $tabClass }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                {{ __('hub.security') }}
            </button>
            <button @click="tab = 'preferences'" :class="tab === 'preferences' ? '{{ $activeTabClass }}' : '{{ $inactiveTabClass }}'" class="{{ $tabClass }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924-1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                {{ __('hub.preferences') }}
            </button>
            <button @click="tab = 'logs'" :class="tab === 'logs' ? '{{ $activeTabClass }}' : '{{ $inactiveTabClass }}'" class="{{ $tabClass }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ __('hub.activity_log') }}
            </button>
        </div>

        <!-- Tab Panes -->
        <div class="lg:col-span-3">
            <!-- Profile Info Tab -->
            <div x-show="tab === 'info'" x-transition class="glass rounded-3xl p-8 border border-white/5 space-y-8">
                <div class="flex items-center justify-between border-b border-white/5 pb-6">
                    <div>
                        <h3 class="text-xl font-bold">{{ __('hub.profile_info') }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ __('hub.personal_details_instruction') }}</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('hub.full_name') }}</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 transition-all font-medium {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('hub.email_address') }}</label>
                            <input type="text" value="{{ $user->email }}" readonly class="w-full bg-slate-900/50 border border-white/5 text-slate-500 rounded-xl px-4 py-3 text-sm cursor-not-allowed font-medium {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                            <p class="text-[10px] text-slate-600 italic">{{ __('hub.email_no_change') }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('hub.phone_number') }}</label>
                            <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 transition-all font-medium {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('hub.role') }}</label>
                            <div class="w-full bg-slate-900/50 border border-white/5 py-3 px-4 rounded-xl flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></span>
                                <span class="text-sm font-bold text-slate-400 capitalize">{{ str_replace('_', ' ', $user->role ?? 'Tenant Admin') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/5 flex gap-3">
                        <button type="submit" class="px-8 py-3 accent-gradient text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:scale-105 active:scale-95 transition-all">{{ __('hub.save_changes') }}</button>
                        <button type="reset" class="px-8 py-3 bg-white/5 text-slate-400 rounded-xl font-bold hover:bg-white/10 transition-all">{{ __('hub.cancel') }}</button>
                    </div>
                </form>
            </div>

            <!-- Security Tab -->
            <div x-show="tab === 'security'" x-transition class="glass rounded-3xl p-8 border border-white/5 space-y-8">
                <div class="flex items-center justify-between border-b border-white/5 pb-6">
                    <div>
                        <h3 class="text-xl font-bold">{{ __('hub.security_settings') }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ __('hub.manage_security_desc') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-500 uppercase">{{ __('hub.last_changed') }}</p>
                        <p class="text-xs font-black text-slate-300">{{ $user->password_changed_at?->format('F d, Y') ?? __('hub.never') }}</p>
                    </div>
                </div>

                <!-- Password Change -->
                <form action="{{ route('profile.security') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('hub.current_password') }}</label>
                            <input type="password" name="current_password" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('hub.new_password') }}</label>
                            <input type="password" name="password" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('hub.confirm_new_password') }}</label>
                            <input type="password" name="password_confirmation" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 transition-all">
                        </div>
                    </div>
                    <div class="pt-4 flex justify-start">
                        <button type="submit" class="px-8 py-3 bg-white/10 text-white rounded-xl font-bold hover:bg-white/20 transition-all">{{ __('hub.update_password') }}</button>
                    </div>
                </form>

                <!-- Active Sessions Placeholder -->
                <div class="pt-8 mt-8 border-t border-white/5">
                    <h4 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-6 flex items-center gap-2">
                        {{ __('hub.active_sessions') }}
                        <span class="px-1.5 py-0.5 bg-indigo-500/10 text-indigo-400 text-[8px] rounded">Live</span>
                    </h4>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-slate-900/40 rounded-2xl border border-white/5 group hover:border-indigo-500/30 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-indigo-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold">macOS - Chrome Browser</p>
                                    <p class="text-[10px] text-slate-500">{{ request()->ip() }} • {{ __('hub.current_session') }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-black uppercase text-emerald-400">{{ __('hub.online') }}</span>
                        </div>
                        
                        <!-- coming soon 2fa -->
                        <div class="p-6 bg-indigo-500/5 border border-dashed border-indigo-500/20 rounded-2xl flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold">{{ __('hub.two_factor_auth') }}</p>
                                    <p class="text-[10px] text-slate-500 italic">{{ __('hub.two_factor_desc') }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 bg-white/5 text-[8px] font-bold text-slate-500 rounded uppercase">{{ __('hub.coming_soon') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preferences Tab -->
            <div x-show="tab === 'preferences'" x-transition class="glass rounded-3xl p-8 border border-white/5 space-y-8">
                <div class="flex items-center justify-between border-b border-white/5 pb-6">
                    <div>
                        <h3 class="text-xl font-bold">{{ __('hub.preferences') }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ __('hub.localization_notifications_desc') }}</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-8">
                    @csrf
                    <!-- Hidden field to preserve name -->
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('hub.preferred_language') }}</label>
                            <select name="preferred_language" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 transition-all appearance-none font-bold">
                                <option value="en" {{ $user->preferred_language === 'en' ? 'selected' : '' }}>English (US)</option>
                                <option value="ar" {{ $user->preferred_language === 'ar' ? 'selected' : '' }}>العربية (Arabic)</option>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('hub.timezone') }}</label>
                            <select name="timezone" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 transition-all appearance-none font-bold">
                                <option value="UTC" {{ $user->timezone === 'UTC' ? 'selected' : '' }}>UTC / Greenwich Mean Time</option>
                                <option value="Africa/Cairo" {{ $user->timezone === 'Africa/Cairo' ? 'selected' : '' }}>Cairo (GMT+2)</option>
                                <option value="Asia/Riyadh" {{ $user->timezone === 'Asia/Riyadh' ? 'selected' : '' }}>Riyadh (GMT+3)</option>
                                <option value="Europe/London" {{ $user->timezone === 'Europe/London' ? 'selected' : '' }}>London (GMT+0)</option>
                                <option value="America/New_York" {{ $user->timezone === 'America/New_York' ? 'selected' : '' }}>New York (GMT-5)</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h4 class="text-sm font-black uppercase tracking-widest text-slate-500 border-b border-white/5 pb-2">{{ __('hub.notifications') }}</h4>
                        
                        <div class="space-y-4">
                            @foreach(['email_alerts' => 'Email Notifications', 'browser_alerts' => 'Browser Push Alerts', 'whatsapp_alerts' => 'WhatsApp System Alerts'] as $key => $label)
                            <div class="flex items-center justify-between p-4 bg-slate-900/30 rounded-2xl border border-white/5">
                                <span class="text-sm font-medium text-slate-300">{{ $label }}</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notification_settings[{{ $key }}]" value="1" class="sr-only peer" {{ ($user->notification_settings[$key] ?? false) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/5 flex justify-end">
                        <button type="submit" class="px-8 py-3 accent-gradient text-white rounded-xl font-bold hover:scale-105 transition-all">{{ __('hub.save_changes') }}</button>
                    </div>
                </form>
            </div>

            <!-- Activity Log Tab -->
            <div x-show="tab === 'logs'" x-transition class="glass rounded-3xl overflow-hidden border border-white/5">
                <div class="p-8 border-b border-white/5">
                    <h3 class="text-xl font-bold">{{ __('hub.activity_log') }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ __('hub.review_logs_desc') }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-900/50 text-[10px] font-black uppercase tracking-widest text-slate-500">
                                <th class="px-8 py-4">{{ __('hub.action') }}</th>
                                <th class="px-8 py-4">{{ __('hub.context') }}</th>
                                <th class="px-8 py-4">{{ __('hub.ip_address') }}</th>
                                <th class="px-8 py-4 text-right">{{ __('hub.timestamp') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm">
                            @forelse($logs as $log)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-8 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full {{ str_contains($log->action, 'fail') || str_contains($log->action, 'alert') ? 'bg-rose-500' : 'bg-indigo-500' }}"></div>
                                        <span class="font-bold uppercase tracking-tight">{{ str_replace('_', ' ', $log->action) }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-slate-500 font-medium">
                                    {{ $log->entity_type ? class_basename($log->entity_type) : 'System' }}
                                </td>
                                <td class="px-8 py-4 font-mono text-xs text-slate-400">{{ $log->ip_address }}</td>
                                <td class="px-8 py-4 text-right text-slate-500 font-bold whitespace-nowrap">{{ $log->created_at->format('M d, H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center text-slate-500 italic">{{ __('hub.no_activity_logs') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-8 py-4 bg-slate-900/30">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
