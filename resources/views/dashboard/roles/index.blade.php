@extends('layouts.dashboard')

@section('title', __('hub.roles') ?? 'Roles')

@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500" x-data="{
    deleteModalOpen: false,
    roleToDelete: null,
    roleToDeleteName: '',
    confirmDelete(id, name) {
        this.roleToDelete = id;
        this.roleToDeleteName = name;
        this.deleteModalOpen = true;
    }
}">
    <!-- Clean Simple Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-white">{{ __('hub.roles') ?? 'Roles' }}</h2>
            <p class="text-slate-400 text-sm mt-0.5">{{ __('hub.roles_subtitle') ?? 'Manage workspace roles and permissions.' }}</p>
        </div>

        <a href="{{ route('roles.create') }}" class="px-5 py-2.5 accent-gradient text-white rounded-xl font-bold flex items-center gap-2 hover:opacity-90 transition-all shadow-lg shadow-indigo-500/20 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>{{ __('hub.create_custom_role') ?? 'Create Role' }}</span>
        </a>
    </div>

    <!-- Unified Clean Roles Table -->
    <div class="glass rounded-2xl overflow-hidden border border-white/5">
        <div class="overflow-x-auto">
            <table class="w-full {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                <thead>
                    <tr class="bg-white/5 text-slate-400 text-[10px] uppercase tracking-widest font-bold border-b border-white/5">
                        <th class="px-6 py-3.5">{{ __('hub.role') ?? 'Role' }}</th>
                        <th class="px-6 py-3.5">{{ __('hub.status') ?? 'Type' }}</th>
                        <th class="px-6 py-3.5">{{ __('hub.capabilities') ?? 'Permissions' }}</th>
                        <th class="px-6 py-3.5">{{ __('hub.users') ?? 'Members' }}</th>
                        <th class="px-6 py-3.5 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}">{{ __('hub.actions') ?? 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm">
                    {{-- System Roles --}}
                    @foreach($systemRoles as $role)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $role->color }};"></span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-white">{{ $role->name }}</span>
                                            <span class="text-[11px] font-mono text-slate-400 bg-white/5 px-2 py-0.5 rounded border border-white/5">{{ $role->slug }}</span>
                                        </div>
                                        @if($role->description)
                                            <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $role->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-300 border border-white/10">
                                    {{ __('hub.system') ?? 'System' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-300 font-medium">
                                    @if($role->slug === 'tenant_admin')
                                        <span class="text-indigo-400 font-semibold">{{ __('hub.all_permissions') ?? 'All Permissions' }}</span>
                                    @else
                                        {{ $role->permissions->count() }} {{ __('hub.permissions') ?? 'permissions' }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-300 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $role->users_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}">
                                <span class="text-xs text-slate-500 italic font-medium">{{ __('hub.built_in') ?? 'Built-in' }}</span>
                            </td>
                        </tr>
                    @endforeach

                    {{-- Custom Roles --}}
                    @foreach($customRoles as $role)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $role->color }};"></span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-white group-hover:text-purple-300 transition-colors">{{ $role->name }}</span>
                                            <span class="text-[11px] font-mono text-purple-300 bg-purple-500/10 px-2 py-0.5 rounded border border-purple-500/20">{{ $role->slug }}</span>
                                        </div>
                                        @if($role->description)
                                            <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $role->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-purple-500/15 text-purple-300 border border-purple-500/30">
                                    {{ __('hub.custom') ?? 'Custom' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-300 font-medium">
                                    {{ $role->permissions->count() }} / {{ $totalPermissions }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-300 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $role->users_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 {{ app()->getLocale() == 'ar' ? 'text-left' : 'text-right' }}">
                                <div class="flex items-center {{ app()->getLocale() == 'ar' ? 'justify-start' : 'justify-end' }} gap-2">
                                    <a href="{{ route('roles.edit', $role) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>{{ __('hub.edit') ?? 'Edit' }}</span>
                                    </a>
                                    <button 
                                        type="button" 
                                        @click="confirmDelete({{ $role->id }}, '{{ addslashes($role->name) }}')"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                                        title="{{ __('hub.delete') ?? 'Delete' }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div @click="deleteModalOpen = false" class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <div class="glass w-full max-w-md rounded-2xl p-6 border border-white/10 relative animate-in zoom-in-95 duration-200">
            <h3 class="text-base font-bold text-white mb-2">{{ __('hub.confirm_delete_role') ?? 'Delete Custom Role?' }}</h3>
            <p class="text-xs text-slate-400 mb-6">
                {{ __('hub.delete_role_warning') ?? 'Are you sure you want to delete' }} <strong class="text-white" x-text="roleToDeleteName"></strong>?
            </p>

            <form :action="'{{ url('/dashboard/roles') }}/' + roleToDelete" method="POST" class="flex gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false" class="flex-1 py-2.5 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-700 transition-colors">
                    {{ __('hub.cancel') ?? 'Cancel' }}
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-red-600 hover:bg-red-500 text-white rounded-xl text-xs font-bold transition-colors shadow-lg shadow-red-600/20">
                    {{ __('hub.confirm_delete') ?? 'Delete Role' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
