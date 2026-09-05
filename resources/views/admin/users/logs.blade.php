@extends('layouts.admin')

@section('title', __('hub.user_audit_logs'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ explode(' ', __('hub.system_audit_trail'))[0] }} <span class="text-red-500">{{ explode(' ', __('hub.system_audit_trail'))[1] ?? '' }} {{ explode(' ', __('hub.system_audit_trail'))[2] ?? '' }}</span></h2>
            <p class="text-slate-400 mt-1">{{ __('hub.audit_trail_subtitle') }}</p>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="glass rounded-3xl overflow-hidden border border-white/5">
        <div class="overflow-x-auto">
            <table class="w-full {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                <thead>
                    <tr class="bg-white/5 text-slate-400 text-[10px] uppercase tracking-widest font-bold">
                        <th class="px-6 py-4">{{ __('hub.timestamp') }}</th>
                        <th class="px-6 py-4">{{ __('hub.user') }}</th>
                        <th class="px-6 py-4">{{ __('hub.tenant') }}</th>
                        <th class="px-6 py-4">{{ __('hub.action') ?? 'Action' }}</th>
                        <th class="px-6 py-4">{{ __('hub.details') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}">{{ __('hub.ip_address') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($logs as $log)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-white">{{ $log->user->name ?? __('hub.system') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs text-slate-400">{{ $log->tenant->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-[10px] font-bold uppercase tracking-wider border border-white/5">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 italic max-w-xs truncate">
                                @if($log->metadata)
                                    {{ json_encode($log->metadata) }}
                                @else
                                    {{ $log->entity_type }} #{{ $log->entity_id }}
                                @endif
                            </td>
                            <td class="px-6 py-4 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }} whitespace-nowrap text-[10px] font-mono text-slate-500">
                                {{ $log->ip_address }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-white/5">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
