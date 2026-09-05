@extends('layouts.admin')

@section('title', __('admin.tenants.tenant_management'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ __('admin.tenants.system_tenants') }}</h2>
            <p class="text-slate-400 mt-1">{{ __('admin.tenants.manage_monitor') }}</p>
        </div>
        <a href="{{ route('admin.tenants.create') }}" class="px-5 py-2.5 accent-gradient rounded-xl transition-all duration-300 shadow-lg shadow-red-500/20 font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            {{ __('admin.tenants.create_new_tenant') }}
        </a>
    </div>

    <!-- Search & Filters -->
    <form action="{{ route('admin.tenants') }}" method="GET" class="glass p-4 rounded-2xl flex flex-col md:flex-row gap-4 items-center">
        <div class="flex-1 w-full relative group">
            <svg class="absolute {{ app()->getLocale() == 'ar' ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.tenants.search_placeholder') }}" class="w-full bg-black/20 border border-white/5 rounded-xl {{ app()->getLocale() == 'ar' ? 'pr-11 pl-4' : 'pl-11 pr-4' }} py-2.5 focus:outline-none focus:border-red-500/50 transition-all text-sm">
        </div>
        <select name="status" onchange="this.form.submit()" class="bg-black/20 border border-white/5 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-red-500/50">
            <option {{ request('status') == '' ? 'selected' : '' }}>{{ __('admin.tenants.all_statuses') }}</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('admin.tenants.active_only') }}</option>
            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('admin.tenants.suspended') }}</option>
        </select>
        <button type="submit" class="hidden"></button>
    </form>

    <div class="glass rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-white/5 text-slate-400 text-[10px] uppercase tracking-widest font-bold text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <th class="px-6 py-4">{{ __('admin.tenants.table_tenant') }}</th>
                    <th class="px-6 py-4">{{ __('admin.tenants.table_current_plan') }}</th>
                    <th class="px-6 py-4">{{ __('admin.tenants.table_activity') }}</th>
                    <th class="px-6 py-4">{{ __('admin.tenants.table_status') }}</th>
                    <th class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}">{{ __('admin.tenants.table_actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($tenants as $tenant)
                    <tr class="hover:bg-white/[0.02] transition-colors group text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center font-bold text-slate-400 group-hover:text-red-400 transition-colors">
                                    {{ substr($tenant->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-sm">{{ $tenant->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $tenant->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded bg-indigo-500/10 text-indigo-400 text-[10px] font-bold uppercase tracking-wider border border-indigo-500/20">
                                {{ $tenant->plan->name ?? __('admin.tenants.no_plan') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium">{{ $tenant->conversations_count }} {{ __('admin.dashboard.conversations') }}</span>
                                <div class="w-24 h-1 bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-red-500" style="width: 45%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($tenant->status == 'active')
                                <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                                    {{ __('admin.tenants.active') }}
                                </span>
                            @else
                                <span class="flex items-center gap-1.5 text-xs text-rose-500 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    {{ __('admin.tenants.suspended_status') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}">
                            <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-white transition-colors">{{ __('admin.tenants.view_details') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500 text-sm">
                            {{ __('admin.tenants.no_tenants_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $tenants->links() }}
    </div>
</div>
@endsection
