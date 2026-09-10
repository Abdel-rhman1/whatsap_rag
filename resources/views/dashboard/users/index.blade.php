@extends('layouts.dashboard')

@section('title', __('hub.users'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700" x-data="{ showInviteModal: false }">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ __('hub.team_members') }}</h2>
            <p class="text-slate-400 mt-1">{{ __('hub.manage_team_subtitle') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('roles.index') }}" class="px-5 py-3 glass hover:bg-white/10 text-slate-300 hover:text-white rounded-xl font-semibold flex items-center gap-2 transition-all border border-white/10 text-sm">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>{{ __('hub.manage_roles') ?? 'Manage Roles & Permissions' }}</span>
            </a>
            <button @click="showInviteModal = true" class="px-6 py-3 accent-gradient text-white rounded-xl font-bold flex items-center gap-2 hover:opacity-90 transition-all shadow-lg shadow-indigo-500/20 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                {{ __('hub.invite_member') }}
            </button>
        </div>
    </div>

    <!-- Users Table -->
    <div class="glass rounded-3xl overflow-hidden border border-white/5">
        <div class="overflow-x-auto">
            <table class="w-full {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                <thead>
                    <tr class="bg-white/5 text-slate-400 text-[10px] uppercase tracking-widest font-bold">
                        <th class="px-6 py-4">{{ __('hub.user') }}</th>
                        <th class="px-6 py-4">{{ __('hub.role') }}</th>
                        <th class="px-6 py-4">{{ __('hub.status') }}</th>
                        <th class="px-6 py-4">{{ __('hub.last_active') }}</th>
                        <th class="px-6 py-4 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}">{{ __('hub.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    {{-- Primary Account Owner --}}
                    @if(isset($tenant))
                        <tr class="bg-indigo-500/[0.04] hover:bg-indigo-500/[0.08] transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center overflow-hidden">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($tenant->name) }}&background=6366f1&color=fff" alt="">
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-bold text-white">{{ $tenant->name }}</p>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                                {{ __('hub.owner') ?? 'Owner' }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500">{{ $tenant->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/5 text-slate-300 border border-white/10">
                                    {{ __('hub.tenant_admin') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 text-[10px] font-black uppercase tracking-wider">
                                    <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                    {{ __('hub.active') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-400">{{ $tenant->last_login_at ? $tenant->last_login_at->diffForHumans() : __('hub.never') }}</span>
                            </td>
                            <td class="px-6 py-4 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}">
                                <span class="text-xs text-slate-500 font-medium italic">{{ __('hub.primary_account') ?? 'Primary Account' }}</span>
                            </td>
                        </tr>
                    @endif

                    @forelse($users as $user)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center overflow-hidden">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff" alt="">
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('tenant.users.role', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <select onchange="this.form.submit()" name="role" class="bg-slate-900/50 border border-white/10 rounded-lg text-xs font-medium px-2 py-1 text-slate-300 focus:outline-none focus:border-indigo-500 transition-colors">
                                        @if(isset($roles) && count($roles) > 0)
                                            @foreach($roles as $r)
                                                <option value="{{ $r->slug }}" {{ $user->role === $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                                            @endforeach
                                        @else
                                            <option value="tenant_admin" {{ $user->role === 'tenant_admin' ? 'selected' : '' }}>{{ __('hub.tenant_admin') }}</option>
                                            <option value="agent" {{ $user->role === 'agent' ? 'selected' : '' }}>{{ __('hub.agent') }}</option>
                                            <option value="analyst" {{ $user->role === 'analyst' ? 'selected' : '' }}>{{ __('hub.analyst') }}</option>
                                            <option value="billing" {{ $user->role === 'billing' ? 'selected' : '' }}>{{ __('hub.billing') }}</option>
                                        @endif
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 text-[10px] font-black uppercase tracking-wider">
                                        <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                        {{ __('hub.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-500/10 text-slate-400 text-[10px] font-black uppercase tracking-wider">
                                        <span class="w-1 h-1 rounded-full bg-slate-500"></span>
                                        {{ __('hub.disabled') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-400">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : __('hub.never') }}</span>
                            </td>
                            <td class="px-6 py-4 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}">
                                <div class="flex {{ app()->getLocale() == 'ar' ? 'justify-start' : 'justify-end' }} gap-2">
                                    <form action="{{ route('tenant.users.toggle', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 {{ $user->is_active ? 'text-rose-400 hover:bg-rose-500/10' : 'text-emerald-400 hover:bg-emerald-500/10' }} rounded-lg transition-colors" title="{{ $user->is_active ? __('hub.deactivate') : __('hub.activate') }}">
                                            @if($user->is_active)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </button>
                                    </form>
                                    <button class="p-2 text-slate-400 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @if(!isset($tenant))
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    {{ __('hub.no_team_members') }}
                                </td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Invite Modal -->
    <div x-show="showInviteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div @click="showInviteModal = false" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="glass w-full max-w-md rounded-3xl p-8 border border-white/10 relative animate-in zoom-in-95 duration-200">
            <h3 class="text-xl font-bold mb-2">{{ __('hub.invite_team_member') }}</h3>
            <p class="text-slate-400 text-sm mb-6">{{ __('hub.invite_subtitle') }}</p>

            <form action="{{ route('tenant.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('hub.full_name') }}</label>
                    <input type="text" name="name" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('hub.email_address') }}</label>
                    <input type="email" name="email" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('hub.workspace_role') }}</label>
                    <select name="role" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition-colors">
                        @if(isset($roles) && count($roles) > 0)
                            @foreach($roles as $r)
                                <option value="{{ $r->slug }}" {{ (isset($settings) && $settings->default_role === $r->slug) || (!isset($settings) && $r->slug === 'agent') ? 'selected' : '' }}>
                                    {{ $r->name }} ({{ $r->description ?? $r->slug }})
                                </option>
                            @endforeach
                        @else
                            <option value="agent" selected>{{ __('hub.agent_desc') }}</option>
                            <option value="analyst">{{ __('hub.analyst_desc') }}</option>
                            <option value="billing">{{ __('hub.billing_desc') }}</option>
                            <option value="tenant_admin">{{ __('hub.admin_desc') }}</option>
                        @endif
                    </select>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showInviteModal = false" class="flex-1 py-3 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-700 transition-colors">{{ __('hub.cancel') }}</button>
                    <button type="submit" class="flex-1 py-3 accent-gradient text-white rounded-xl font-bold hover:opacity-90 transition-all">{{ __('hub.send_invite') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
