@extends('layouts.admin')

@section('title', __('admin.logs.title'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="glass rounded-3xl overflow-hidden">
        <div class="max-h-[700px] overflow-y-auto custom-scrollbar">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5 text-slate-400 text-[10px] uppercase tracking-widest font-bold sticky top-0 backdrop-blur-xl text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <th class="px-6 py-4">{{ __('admin.logs.table_admin') }}</th>
                        <th class="px-6 py-4">{{ __('admin.logs.table_action') }}</th>
                        <th class="px-6 py-4">{{ __('admin.logs.table_info') }}</th>
                        <th class="px-6 py-4">{{ __('admin.logs.table_ip') }}</th>
                        <th class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}">{{ __('admin.logs.table_time') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($logs as $log)
                        <tr class="hover:bg-white/[0.02] transition-colors text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3 {{ app()->getLocale() == 'ar' ? 'flex-row-reverse' : '' }}">
                                    <div class="w-8 h-8 rounded bg-red-500/10 flex items-center justify-center text-red-500 font-bold text-xs flex-shrink-0">
                                         {{ substr($log->adminUser->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="text-sm font-medium">{{ $log->adminUser->name ?? __('admin.logs.system_user') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 bg-slate-800 text-slate-300 rounded text-[10px] border border-white/5 font-bold uppercase tracking-widest">{{ $log->action }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-400">{{ $log->description }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-mono text-slate-500" dir="ltr">{{ $log->ip_address }}</span>
                            </td>
                            <td class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-slate-500 text-sm">
                                {{ $log->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center text-slate-600 italic">{{ __('admin.logs.no_logs') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
             <div class="p-4 bg-white/5">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
