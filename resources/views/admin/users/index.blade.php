@extends('layouts.admin')

@section('title', __('hub.global_user_management'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ explode(' ', __('hub.system_users'))[0] }} <span class="text-red-500">{{ explode(' ', __('hub.system_users'))[1] ?? '' }}</span></h2>
            <p class="text-slate-400 mt-1">{{ __('hub.manage_global_users_subtitle') }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass p-6 rounded-3xl border border-white/5">
        <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">{{ __('hub.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('hub.search_placeholder') }}" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">{{ __('hub.tenant') }}</label>
                <select name="tenant_id" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                    <option value="">{{ __('hub.all_tenants') }}</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-700 transition-colors">{{ __('hub.filter_results') }}</button>
            </div>
        </form>
    </div>

    <!-- Global Users Table -->
    <div class="glass rounded-3xl overflow-hidden border border-white/5">
        <div class="overflow-x-auto">
            <table class="w-full {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                <thead>
                    <tr class="bg-white/5 text-slate-400 text-[10px] uppercase tracking-widest font-bold">
                        <th class="px-6 py-4">{{ __('hub.user_details') }}</th>
                        <th class="px-6 py-4">{{ __('hub.tenant') }}</th>
                        <th class="px-6 py-4">{{ __('hub.role') }}</th>
                        <th class="px-6 py-4">{{ __('hub.status') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}">{{ __('hub.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($users as $user)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center overflow-hidden">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=ef4444&color=fff" alt="">
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-slate-300 bg-slate-800 px-2 py-1 rounded border border-white/5">{{ $user->tenant->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $user->role }}
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 text-[10px] font-black uppercase tracking-wider">
                                        <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                        {{ __('hub.active') ?? 'Active' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-red-500/10 text-red-400 text-[10px] font-black uppercase tracking-wider">
                                        <span class="w-1 h-1 rounded-full bg-red-500"></span>
                                        {{ __('hub.suspended') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}">
                                <div class="flex {{ app()->getLocale() == 'ar' ? 'justify-start' : 'justify-end' }} gap-2">
                                    <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-slate-800 text-xs font-bold rounded-lg hover:bg-slate-700 transition-colors">
                                            {{ $user->is_active ? __('hub.suspend_action') : __('hub.activate_action') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('hub.user_delete_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-500 hover:text-red-500 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-white/5">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
