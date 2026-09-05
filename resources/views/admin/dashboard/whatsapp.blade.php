@extends('layouts.admin')

@section('title', __('admin.whatsapp.system_health'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-stat-card 
            title="{{ __('admin.whatsapp.total_sessions') }}" 
            value="{{ $stats['total'] }}" 
            icon="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
            color="indigo" 
        />
        <x-stat-card 
            title="{{ __('admin.whatsapp.connected') }}" 
            value="{{ $stats['connected'] }}" 
            icon="M5 13l4 4L19 7"
            color="emerald" 
        />
        <x-stat-card 
            title="{{ __('admin.whatsapp.disconnected_errors') }}" 
            value="{{ $stats['errors'] }}" 
            icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            color="rose" 
        />
    </div>

    <div class="glass rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-white/5 text-slate-400 text-[10px] uppercase tracking-widest font-bold text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <th class="px-6 py-4">{{ __('admin.whatsapp.table_tenant') }}</th>
                    <th class="px-6 py-4">{{ __('admin.whatsapp.table_instance') }}</th>
                    <th class="px-6 py-4">{{ __('admin.whatsapp.table_phone') }}</th>
                    <th class="px-6 py-4">{{ __('admin.whatsapp.table_status') }}</th>
                    <th class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}">{{ __('admin.whatsapp.table_actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($instances as $instance)
                <tr class="hover:bg-white/[0.02] transition-colors text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-white">{{ $instance->tenant->name ?? __('admin.whatsapp.unknown_tenant') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <code class="text-xs text-indigo-400 font-mono">{{ $instance->instance_name }}</code>
                    </td>
                    <td class="px-6 py-4">
                         <span class="text-xs text-slate-400">{{ $instance->phone_number ?: '—' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($instance->status === 'connected')
                            <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                                {{ __('admin.whatsapp.status_connected') }}
                            </span>
                        @else
                            <span class="flex items-center gap-1.5 text-xs text-rose-400 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                {{ strtoupper($instance->status) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}">
                        <form action="{{ route('admin.whatsapp.restart', $instance->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-red-500 hover:text-white transition-colors uppercase tracking-widest">{{ __('admin.whatsapp.restart') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-slate-500 text-sm">{{ __('admin.whatsapp.no_instances') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $instances->links() }}
    </div>

    <div class="glass p-8 rounded-3xl {{ app()->getLocale() == 'ar' ? 'border-r-4' : 'border-l-4' }} border-amber-500 bg-amber-500/5">
        <h4 class="text-lg font-bold mb-2">{{ __('admin.whatsapp.gateway_nodes') }}</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-6">
            @foreach(['US-East', 'EU-West', 'AS-North', 'ME-South'] as $region)
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-bold text-slate-500 uppercase">
                        <span>{{ $region }}</span>
                        <span>88%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full accent-gradient" style="width: 88%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
