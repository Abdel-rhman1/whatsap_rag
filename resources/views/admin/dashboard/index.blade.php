@extends('layouts.admin')

@section('title', __('admin.dashboard.global_system_overview'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Admin Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <x-stat-card 
            title="{{ __('admin.dashboard.total_tenants') }}" 
            value="{{ $stats['total_tenants'] }}" 
            icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
            color="indigo" 
        />
        <x-stat-card 
            title="{{ __('admin.dashboard.total_messages') }}" 
            value="{{ number_format($stats['total_messages']) }}" 
            icon="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
            color="emerald" 
        />
        <x-stat-card 
            title="{{ __('admin.dashboard.whatsapp_health') }}" 
            value="{{ $stats['whatsapp_health'] }}%" 
            icon="M13 10V3L4 14h7v7l9-11h-7z"
            color="{{ $stats['whatsapp_health'] > 80 ? 'emerald' : 'amber' }}" 
        />
        <x-stat-card 
            title="{{ __('admin.dashboard.total_campaigns') }}" 
            value="{{ $stats['total_campaigns'] }}" 
            icon="M11 5.882V19.247A7.447 7.447 0 0011 15.75V5.882m0 0a8.997 8.997 0 013.524 3.352"
            color="purple" 
        />
        <x-stat-card 
            title="{{ __('admin.dashboard.messages_sent_today') }}" 
            value="{{ number_format($stats['campaign_messages_today']) }}" 
            icon="M13 10V3L4 14h7v7l9-11h-7z"
            color="emerald" 
        />
        <x-stat-card 
            title="{{ __('admin.dashboard.active_campaigns') }}" 
            value="{{ $stats['active_campaigns'] }}" 
            icon="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
            color="indigo" 
        />
        <x-stat-card 
            title="SLA Compliance" 
            value="{{ $stats['sla_health'] }}%" 
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            color="{{ $stats['sla_health'] > 90 ? 'emerald' : ($stats['sla_health'] > 75 ? 'amber' : 'rose') }}" 
        />
    </div>

    <div class="grid grid-cols-12 gap-8">
        <!-- Top Tenants -->
        <div class="col-span-12 lg:col-span-8 glass rounded-3xl p-8">
            <h4 class="text-xl font-bold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-9 9-4-4-6 6"/></svg>
                {{ __('admin.dashboard.top_performing_tenants') }}
            </h4>
            <div class="space-y-4">
                @forelse($stats['top_tenants'] as $tenant)
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.05] transition-all cursor-pointer">
                        <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center font-bold text-indigo-400">
                             {{ substr($tenant->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-bold">{{ $tenant->name }}</p>
                            <p class="text-xs text-slate-500">{{ $tenant->email }}</p>
                        </div>
                        <div class="text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}">
                            <p class="text-sm font-bold">{{ $tenant->conversations_count }}</p>
                            <p class="text-[10px] text-slate-600 uppercase font-bold tracking-widest">{{ __('admin.dashboard.conversations') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm text-center py-4">{{ __('admin.dashboard.no_data') }}</p>
                @endforelse
            </div>
        </div>

        <!-- System Alerts -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
            <div class="glass rounded-3xl p-8 bg-red-500/5 border-red-500/10">
                <h4 class="text-sm font-bold text-red-500 uppercase tracking-widest mb-6">{{ __('admin.dashboard.critical_alerts') }}</h4>
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="w-2 h-2 rounded-full bg-red-500 mt-1.5 animate-pulse flex-shrink-0"></div>
                        <div>
                            <p class="text-sm font-bold">{{ __('admin.dashboard.node_near_capacity') }}</p>
                            <p class="text-xs text-slate-500">{{ __('admin.dashboard.resource_usage') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass rounded-3xl p-8">
                <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-6">{{ __('admin.dashboard.global_activity') }}</h4>
                <div class="space-y-6">
                    <div class="relative {{ app()->getLocale() == 'ar' ? 'pr-6 border-r-2 border-l-0' : 'pl-6 border-l-2' }} border-indigo-500/20">
                        <div class="absolute {{ app()->getLocale() == 'ar' ? '-right-[9px]' : '-left-[9px]' }} top-0 w-4 h-4 rounded-full bg-[#080a0f] border-2 border-indigo-500"></div>
                        <p class="text-xs text-slate-500 font-bold mb-1 uppercase tracking-widest">{{ __('admin.dashboard.two_mins_ago') }}</p>
                        <p class="text-sm font-medium">{{ __('admin.dashboard.new_tenant_registration') }}: <span class="text-indigo-400">Acme Corp</span></p>
                    </div>
                    <div class="relative {{ app()->getLocale() == 'ar' ? 'pr-6 border-r-2 border-l-0' : 'pl-6 border-l-2' }} border-slate-700">
                        <div class="absolute {{ app()->getLocale() == 'ar' ? '-right-[9px]' : '-left-[9px]' }} top-0 w-4 h-4 rounded-full bg-[#080a0f] border-2 border-slate-700"></div>
                        <p class="text-xs text-slate-500 font-bold mb-1 uppercase tracking-widest">{{ __('admin.dashboard.fifteen_mins_ago') }}</p>
                        <p class="text-sm font-medium">{{ __('admin.dashboard.plan_upgrade') }}: <span class="text-emerald-400">Globex Inc</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Messages per Tenant -->
        <div class="glass rounded-3xl p-8 border border-white/5">
            <h4 class="text-xl font-bold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                {{ __('admin.dashboard.messages_per_tenant') }}
            </h4>
            <div class="space-y-5">
                @forelse($stats['messages_per_tenant'] as $tenant)
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-300 font-medium">{{ $tenant->name }}</span>
                            <span class="text-indigo-400 font-bold">{{ number_format($tenant->messages_count) }}</span>
                        </div>
                        <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                            @php
                                $maxCount = $stats['messages_per_tenant']->max('messages_count');
                                $p = $maxCount > 0 ? ($tenant->messages_count / $maxCount) * 100 : 0;
                            @endphp
                            <div class="bg-indigo-500 h-full rounded-full" style="width: {{ $p }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm text-center py-4">{{ __('admin.dashboard.no_data') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Campaigns per Tenant -->
        <div class="glass rounded-3xl p-8 border border-white/5">
            <h4 class="text-xl font-bold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                {{ __('admin.dashboard.top_campaign_hubs') }}
            </h4>
            <div class="space-y-5">
                @forelse($stats['top_tenants_by_campaigns'] as $tenant)
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-300 font-medium">{{ $tenant->name }}</span>
                            <span class="text-purple-400 font-bold">{{ $tenant->campaigns_count }}</span>
                        </div>
                        <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                            @php
                                $maxC = $stats['top_tenants_by_campaigns']->max('campaigns_count');
                                $pC = $maxC > 0 ? ($tenant->campaigns_count / $maxC) * 100 : 0;
                            @endphp
                            <div class="bg-purple-500 h-full rounded-full" style="width: {{ $pC }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm text-center py-4">{{ __('admin.dashboard.no_data') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
