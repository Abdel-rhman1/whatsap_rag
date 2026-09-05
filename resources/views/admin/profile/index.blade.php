@extends('layouts.admin')

@section('title', __('admin.profile.system_profile'))

@section('content')
<div class="max-w-6xl mx-auto space-y-8 {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}" x-data="{ tab: 'info' }">
    <!-- Header Card -->
    <div class="glass p-8 rounded-[2.5rem] border border-white/10 relative overflow-hidden">
        <div class="absolute top-0 {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} p-12 opacity-5 pointer-events-none">
            <svg class="w-48 h-48 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 2.18l7 3.12v4.7c0 4.67-3.13 8.75-7 9.81-3.87-1.06-7-5.14-7-9.81v-4.7l7-3.12zM12 7c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </div>

        <div class="flex flex-col md:flex-row items-center gap-8 relative z-10">
            <!-- Avatar -->
            <div class="relative group">
                <div class="w-32 h-32 rounded-3xl bg-slate-900 flex items-center justify-center border-2 border-white/10 overflow-hidden shadow-2xl group-hover:border-red-500/50 transition-all duration-500">
                    <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=ef4444&color=fff&size=200' }}" 
                         class="w-full h-full object-cover" alt="{{ $user->name }}">
                </div>
                <button class="absolute -bottom-2 {{ app()->getLocale() == 'ar' ? '-left-2' : '-right-2' }} w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center text-white shadow-lg hover:bg-red-500 transition-all active:scale-90 border border-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </button>
            </div>

            <div class="flex-1 text-center md:{{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }} space-y-2">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                    <h2 class="text-3xl font-black italic">{{ $user->name }}</h2>
                    <span class="px-3 py-1 bg-red-500/10 text-red-500 border border-red-500/20 rounded-full text-[10px] font-black uppercase tracking-widest">
                        {{ __('admin.profile.super_admin') }}
                    </span>
                    <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[10px] font-black uppercase tracking-widest">
                        {{ __('admin.profile.secure_session') }}
                    </span>
                </div>
                <p class="text-slate-400 font-medium font-mono text-sm tracking-tighter">{{ $user->email }}</p>
                
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-6 mt-4 pt-4 border-t border-white/5">
                    <div class="flex items-center gap-2 text-xs text-slate-500 font-bold uppercase tracking-tight">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                        {{ __('admin.profile.system_access_global') }}
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 font-bold uppercase tracking-tight">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('admin.profile.last_activity') }}: {{ $user->last_login_at?->diffForHumans() ?? 'Unknown' }}
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
                $activeTabClass = "bg-red-500/10 text-red-500 " . (app()->getLocale() == 'ar' ? 'border-r-4' : 'border-l-4') . " border-red-500";
                $inactiveTabClass = "text-slate-500 hover:bg-white/5";
            @endphp

            <button @click="tab = 'info'" :class="tab === 'info' ? '{{ $activeTabClass }}' : '{{ $inactiveTabClass }}'" class="{{ $tabClass }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                {{ __('admin.profile.management_info') }}
            </button>
            <button @click="tab = 'security'" :class="tab === 'security' ? '{{ $activeTabClass }}' : '{{ $inactiveTabClass }}'" class="{{ $tabClass }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ __('admin.profile.internal_security') }}
            </button>
            <button @click="tab = 'preferences'" :class="tab === 'preferences' ? '{{ $activeTabClass }}' : '{{ $inactiveTabClass }}'" class="{{ $tabClass }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                {{ __('admin.profile.system_preferences') }}
            </button>
            <button @click="tab = 'system_logs'" :class="tab === 'system_logs' ? '{{ $activeTabClass }}' : '{{ $inactiveTabClass }}'" class="{{ $tabClass }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                {{ __('admin.profile.system_activity') }}
            </button>
        </div>

        <!-- Tab Panes -->
        <div class="lg:col-span-3">
            <!-- Management Info Tab -->
            <div x-show="tab === 'info'" x-transition class="glass rounded-3xl p-8 border border-white/5 space-y-8">
                <div class="border-b border-white/5 pb-6">
                    <h3 class="text-xl font-bold italic tracking-tighter uppercase">{{ __('admin.profile.admin_metadata') }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ __('admin.profile.authorized_access_level') }}: <span class="text-red-400 font-bold">{{ __('admin.profile.full_system_root') }}</span></p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('admin.profile.display_name') }}</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 transition-all font-bold italic {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('admin.profile.system_email') }}</label>
                            <input type="text" value="{{ $user->email }}" readonly class="w-full bg-slate-900/50 border border-white/5 text-slate-500 rounded-xl px-4 py-3 text-sm cursor-not-allowed font-mono {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('admin.profile.direct_phone') }}</label>
                            <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 transition-all font-mono {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('admin.profile.permissions_integrity') }}</label>
                            <div class="flex flex-wrap gap-2 pt-1">
                                <span class="px-2 py-0.5 bg-red-500 text-white text-[8px] font-black rounded uppercase">ROOT</span>
                                <span class="px-2 py-0.5 bg-slate-800 text-slate-400 text-[8px] font-black rounded uppercase">TENANT_BYPASS</span>
                                <span class="px-2 py-0.5 bg-slate-800 text-slate-400 text-[8px] font-black rounded uppercase">SEC_LOG_VIEW</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/5 flex gap-3">
                        <button type="submit" class="px-8 py-3 bg-red-600/20 hover:bg-red-600/30 text-red-500 border border-red-500/30 rounded-xl font-bold transition-all">{{ __('admin.profile.sync_profiles') }}</button>
                    </div>
                </form>
            </div>

            <!-- Security Tab -->
            <div x-show="tab === 'security'" x-transition class="glass rounded-3xl p-8 border border-white/5 space-y-8">
                <div class="flex items-center justify-between border-b border-white/5 pb-6">
                    <div>
                        <h3 class="text-xl font-bold italic tracking-tighter uppercase">{{ __('admin.profile.access_key_shields') }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ __('admin.profile.high_risk_notice') }}</p>
                    </div>
                </div>

                <form action="{{ route('profile.security') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('admin.profile.master_password_clearance') }}</label>
                            <input type="password" name="current_password" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('admin.profile.new_system_passcode') }}</label>
                            <input type="password" name="password" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('admin.profile.verify_passcode') }}</label>
                            <input type="password" name="password_confirmation" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 transition-all">
                        </div>
                    </div>
                    <div class="pt-4 flex justify-start">
                        <button type="submit" class="px-8 py-3 bg-red-600 text-white rounded-xl font-bold shadow-lg shadow-red-500/20 hover:scale-105 transition-all">{{ __('admin.profile.update_master_key') }}</button>
                    </div>
                </form>

                <div class="pt-8 mt-8 border-t border-white/5">
                    <h4 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-6 flex items-center gap-2">
                        {{ __('admin.profile.active_master_sessions') }}
                    </h4>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-slate-900/40 rounded-2xl border border-white/5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-red-500/10 flex items-center justify-center text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09m10.198-12.793A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.416 0a8.969 8.969 0 012.168 4.5"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold">Linux Host - Shell Access</p>
                                    <p class="text-[10px] text-slate-500 font-mono italic">{{ request()->ip() }} • {{ __('admin.profile.secure_session') }}</p>
                                </div>
                            </div>
                            <button class="px-4 py-1.5 bg-red-600/10 text-red-500 text-[10px] font-black rounded-lg border border-red-500/30 uppercase">{{ __('admin.profile.terminate') }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preferences Tab -->
            <div x-show="tab === 'preferences'" x-transition class="glass rounded-3xl p-8 border border-white/5 space-y-8">
                <div class="border-b border-white/5 pb-6">
                    <h3 class="text-xl font-bold italic tracking-tighter uppercase">{{ __('admin.profile.machine_preferences') }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ __('admin.profile.global_config_desc') }}</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-8">
                    @csrf
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('admin.profile.primary_language') }}</label>
                            <select name="preferred_language" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 transition-all font-bold appearance-none">
                                <option value="en" {{ $user->preferred_language === 'en' ? 'selected' : '' }}>English (US)</option>
                                <option value="ar" {{ $user->preferred_language === 'ar' ? 'selected' : '' }}>العربية (Arabic)</option>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-1">{{ __('admin.profile.global_reference_utc') }}</label>
                            <select name="timezone" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 transition-all font-bold appearance-none">
                                <option value="UTC" selected>UTC (Coordinated Universal Time)</option>
                                <option value="Africa/Cairo">Cairo Time</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h4 class="text-sm font-black uppercase tracking-widest text-slate-400">{{ __('admin.profile.critical_monitoring') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach(['system_alerts' => 'System Downtime Alerts', 'security_breach' => 'Unauthorized Login Alerts', 'billing_fail' => 'Critical Billing failures'] as $key => $label)
                            <div class="flex items-center justify-between p-4 bg-slate-900/60 rounded-2xl border border-white/5">
                                <span class="text-xs font-bold text-slate-300 uppercase">{{ $label }}</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notification_settings[{{ $key }}]" value="1" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/5 flex justify-end">
                        <button type="submit" class="px-8 py-3 bg-white text-black rounded-xl font-bold hover:bg-red-500 hover:text-white transition-all shadow-xl">{{ __('admin.profile.apply_globally') }}</button>
                    </div>
                </form>
            </div>

            <!-- System Activity Tab -->
            <div x-show="tab === 'system_logs'" x-transition class="glass rounded-3xl overflow-hidden border border-white/5">
                <div class="p-8 border-b border-white/5 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold italic tracking-tighter uppercase">{{ __('admin.profile.audit_trail_master') }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ __('admin.profile.audit_trail_desc') }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-900 text-[10px] font-black uppercase tracking-widest text-slate-500">
                                <th class="px-8 py-4">{{ __('admin.profile.action_event') }}</th>
                                <th class="px-8 py-4">{{ __('admin.profile.target_tenant') }}</th>
                                <th class="px-8 py-4">{{ __('admin.profile.ip_node') }}</th>
                                <th class="px-8 py-4 text-right">{{ __('admin.profile.sequence') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-[11px] font-bold">
                            @forelse($logs as $log)
                            <tr class="hover:bg-red-500/[0.03] transition-colors">
                                <td class="px-8 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-red-500">#</span>
                                        <span class="uppercase tracking-tight">{{ str_replace('_', ' ', $log->action) }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-slate-500">
                                    {{ $log->tenant?->name ?? __('admin.profile.system_wide') }}
                                </td>
                                <td class="px-8 py-4 font-mono text-red-400 opacity-70">{{ $log->ip_address }}</td>
                                <td class="px-8 py-4 text-right text-slate-500">{{ $log->created_at->format('Y/m/d H:i:s') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center text-slate-600 font-mono italic">{{ __('admin.profile.empty_audit_trail') }}</td>
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

