@extends('layouts.dashboard')

@section('title', __('hub.logs_monitoring'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ explode(' ', __('hub.system_logs'))[0] }} <span class="accent-text-gradient">{{ explode(' ', __('hub.system_logs'))[1] ?? '' }}</span></h2>
            <p class="text-slate-400 mt-1">{{ __('hub.logs_subtitle') }}</p>
        </div>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-slate-900 border border-white/10 rounded-lg text-sm font-medium hover:bg-slate-800 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                {{ __('hub.filters') }}
            </button>
            <a href="{{ route('tenant.logs.export') }}" class="px-4 py-2 bg-slate-900 border border-white/10 rounded-lg text-sm font-medium hover:bg-slate-800 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                {{ __('hub.export_csv') }}
            </a>
        </div>
    </div>

    <div class="glass rounded-3xl overflow-hidden">
        <div class="max-h-[600px] overflow-y-auto custom-scrollbar">
            <table class="w-full {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                <thead>
                    <tr class="bg-white/5 text-slate-400 text-[10px] uppercase tracking-widest font-bold sticky top-0 backdrop-blur-xl">
                        <th class="px-6 py-4">{{ __('hub.timestamp') }}</th>
                        <th class="px-6 py-4">{{ __('hub.event_direction') }}</th>
                        <th class="px-6 py-4">{{ __('hub.content_preview') }}</th>
                        <th class="px-6 py-4">{{ __('hub.status') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}">{{ __('hub.details') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($logs as $log)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium">{{ $log->created_at->format('M d, H:i:s') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($log->is_from_me)
                                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </div>
                                        <span class="text-xs font-bold uppercase tracking-wider">{{ __('hub.outbound') }}</span>
                                    @else
                                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center {{ app()->getLocale() == 'ar' ? '' : 'rotate-180' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </div>
                                        <span class="text-xs font-bold uppercase tracking-wider">{{ __('hub.inbound') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-slate-400 text-sm truncate max-w-xs italic">"{{ Str::limit($log->content, 60) }}"</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ __('hub.success') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}">
                                <button class="text-indigo-400 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center text-slate-600">{{ __('hub.no_logs_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-white/5">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
