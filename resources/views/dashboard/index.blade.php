@extends('layouts.dashboard')

@section('title', __('hub.overview'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">@lang('hub.welcome_back'), <span class="accent-text-gradient">{{ auth('tenant')->user()->name }}</span>!</h2>
            <p class="text-slate-400 mt-1">@lang('hub.welcome_message')</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('whatsapp.index') }}" class="px-5 py-2.5 glass border border-white/10 hover:border-indigo-500/50 hover:bg-indigo-500/5 rounded-xl transition-all duration-300 flex items-center gap-2 font-medium">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                @lang('hub.connect_whatsapp')
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card 
            title="{{ __('hub.messages_today') }}" 
            value="{{ $stats['usage_today'] }}" 
            icon="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
            color="indigo" 
        />
        <x-stat-card 
            title="{{ __('hub.active_sessions') }}" 
            value="{{ $stats['active_whatsapp'] }}" 
            icon="M13 10V3L4 14h7v7l9-11h-7z"
            color="emerald" 
        />
        <x-stat-card 
            title="{{ __('hub.pending_requests') }}" 
            value="{{ $stats['pending_requests'] }}" 
            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
            color="amber" 
        />
        <x-stat-card 
            title="{{ __('hub.api_keys') }}" 
            value="{{ $stats['api_keys_active'] }}" 
            icon="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
            color="rose" 
        />
    </div>

    <div class="grid grid-cols-12 gap-8">
        <!-- Usage Chart -->
        <div class="col-span-12 lg:col-span-8 space-y-6">
            <div class="glass p-8 rounded-2xl">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h4 class="text-xl font-bold">@lang('hub.usage_analytics')</h4>
                        <p class="text-slate-500 text-sm">@lang('hub.message_volume_7d')</p>
                    </div>
                </div>
                
                <div class="h-64 flex items-end justify-between gap-2">
                    @foreach($analytics as $data)
                        <div class="flex-1 flex flex-col items-center gap-2 group">
                            <div class="relative w-full bg-indigo-500/10 rounded-t-lg transition-all duration-500 overflow-hidden" style="height: {{ ($data->count / (max($analytics->pluck('count')->toArray()) ?: 1)) * 100 }}%">
                                <div class="absolute inset-0 accent-gradient opacity-40 group-hover:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-[10px] text-slate-500 uppercase font-medium">{{ Carbon\Carbon::parse($data->date)->format('D') }}</span>
                        </div>
                    @endforeach
                    @if(count($analytics) == 0)
                        <div class="w-full flex flex-col items-center justify-center text-slate-600 h-full border-2 border-dashed border-white/5 rounded-2xl">
                            <p>{{ __('hub.no_data_available') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Setup Checklist -->
            <div class="glass p-8 rounded-2xl">
                <h4 class="text-xl font-bold mb-6">@lang('hub.setup_checklist')</h4>
                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-4 rounded-xl {{ $setupChecklist['knowledge_uploaded'] ? 'bg-emerald-500/5' : 'bg-slate-900/40' }} border border-white/5">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center {{ $setupChecklist['knowledge_uploaded'] ? 'bg-emerald-500 text-white' : 'border-2 border-slate-700 text-slate-500' }}">
                            @if($setupChecklist['knowledge_uploaded'])
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span class="text-[10px] font-bold">1</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold {{ $setupChecklist['knowledge_uploaded'] ? 'text-white' : 'text-slate-400' }}">@lang('hub.knowledge_base')</p>
                            <p class="text-xs text-slate-500">@lang('hub.upload_kb_desc')</p>
                        </div>
                        @if(!$setupChecklist['knowledge_uploaded'])
                            <a href="{{ route('knowledge.index') }}" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 uppercase tracking-wider">@lang('hub.get_started') &rarr;</a>
                        @endif
                    </div>

                 
                    <div class="flex items-center gap-4 p-4 rounded-xl {{ $setupChecklist['whatsapp_connected'] ? 'bg-emerald-500/5' : 'bg-slate-900/40' }} border border-white/5">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center {{ $setupChecklist['whatsapp_connected'] ? 'bg-emerald-500 text-white' : 'border-2 border-slate-700 text-slate-500' }}">
                            @if($setupChecklist['whatsapp_connected'])
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span class="text-[10px] font-bold">3</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold {{ $setupChecklist['whatsapp_connected'] ? 'text-white' : 'text-slate-400' }}">@lang('hub.connect_whatsapp')</p>
                            <p class="text-xs text-slate-500">@lang('hub.link_whatsapp_desc')</p>
                        </div>
                        @if(!$setupChecklist['whatsapp_connected'])
                            <a href="{{ route('whatsapp.index') }}" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 uppercase tracking-wider">@lang('hub.get_started') &rarr;</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-span-12 lg:col-span-4 space-y-8">
            <!-- Active Integrations -->
            <div class="glass p-8 rounded-2xl">
                <h4 class="text-lg font-bold mb-6">@lang('hub.integrations')</h4>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center p-2">
                             <svg class="w-6 h-6 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold">WhatsApp Gateway</p>
                            @if(($stats['active_whatsapp'] ?? 0) > 0)
                                <p class="text-[10px] text-emerald-500 font-bold uppercase tracking-wider">{{ __('hub.connected') }} ({{ $stats['active_whatsapp'] }})</p>
                            @else
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">{{ __('hub.not_linked') }}</p>
                            @endif
                        </div>
                        @if(($stats['active_whatsapp'] ?? 0) > 0)
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        @else
                            <a href="{{ route('whatsapp.index') }}" class="text-xs font-bold text-indigo-400 hover:underline">Link &rarr;</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
