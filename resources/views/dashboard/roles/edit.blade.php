@extends('layouts.dashboard')

@section('title', __('hub.edit_role') ?? 'Edit Role')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700" x-data="{
    roleName: '{{ addslashes($role->name) }}',
    selectedColor: '{{ $role->color ?? '#6366f1' }}',
    searchQuery: '',
    selectedPermissions: {{ json_encode($assignedPermissionNames) }},
    togglePermission(name) {
        if (this.selectedPermissions.includes(name)) {
            this.selectedPermissions = this.selectedPermissions.filter(p => p !== name);
        } else {
            this.selectedPermissions.push(name);
        }
    },
    toggleModule(modulePerms) {
        const allSelected = modulePerms.every(p => this.selectedPermissions.includes(p));
        if (allSelected) {
            this.selectedPermissions = this.selectedPermissions.filter(p => !modulePerms.includes(p));
        } else {
            modulePerms.forEach(p => {
                if (!this.selectedPermissions.includes(p)) {
                    this.selectedPermissions.push(p);
                }
            });
        }
    },
    selectAll() {
        @php
            $allNames = [];
            foreach($permissions as $mod => $items) {
                foreach($items as $item) {
                    $allNames[] = $item->name;
                }
            }
        @endphp
        this.selectedPermissions = {{ json_encode($allNames) }};
    },
    deselectAll() {
        this.selectedPermissions = [];
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('roles.index') }}" class="p-2.5 rounded-xl glass hover:bg-white/10 text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-2xl font-bold tracking-tight text-white">{{ __('hub.edit_role') ?? 'Edit Role' }}: {{ $role->name }}</h2>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-purple-500/20 text-purple-300 border border-purple-500/30">{{ $role->slug }}</span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('hub.edit_role_subtitle') ?? 'Update role capabilities and permissions matrix.' }}</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Role Metadata Card -->
        <div class="glass rounded-3xl p-6 md:p-8 border border-white/10 space-y-6">
            <h3 class="text-base font-bold text-white flex items-center gap-2 border-b border-white/5 pb-4">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                {{ __('hub.role_details') ?? 'Role Information' }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">
                        {{ __('hub.role_name') ?? 'Role Name' }} <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="roleName" 
                        required 
                        class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition-colors"
                    >
                </div>

                <!-- Slug (Readonly) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">
                        {{ __('hub.role_slug') ?? 'Role Identifier' }}
                    </label>
                    <input 
                        type="text" 
                        value="{{ $role->slug }}" 
                        disabled 
                        class="w-full bg-slate-900/60 border border-white/5 rounded-xl px-4 py-3 text-sm text-slate-500 font-mono cursor-not-allowed"
                    >
                </div>

                <!-- Badge Color -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">
                        {{ __('hub.badge_color') ?? 'Badge Color' }}
                    </label>
                    <div class="flex items-center gap-3">
                        <input 
                            type="color" 
                            name="color" 
                            x-model="selectedColor" 
                            class="w-12 h-11 bg-transparent border-0 rounded-xl cursor-pointer p-0"
                        >
                        <div class="flex-1 flex gap-2">
                            @foreach(['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4'] as $preset)
                                <button 
                                    type="button" 
                                    @click="selectedColor = '{{ $preset }}'"
                                    class="w-7 h-7 rounded-lg transition-transform hover:scale-110 shadow" 
                                    style="background-color: {{ $preset }};"
                                ></button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">
                        {{ __('hub.description') ?? 'Role Description' }}
                    </label>
                    <textarea 
                        name="description" 
                        rows="2" 
                        class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm text-slate-300 focus:outline-none focus:border-indigo-500 transition-colors"
                    >{{ old('description', $role->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Permission Matrix Card -->
        <div class="glass rounded-3xl p-6 md:p-8 border border-white/10 space-y-6">
            <!-- Header with Search & Bulk Select -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-white/5 pb-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ __('hub.permission_matrix') ?? 'Permission Matrix' }}
                        </h3>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            <span x-text="selectedPermissions.length"></span> {{ __('hub.selected') ?? 'selected' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">{{ __('hub.permission_matrix_desc') ?? 'Check the specific capabilities this role will be granted.' }}</p>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <!-- Search input -->
                    <div class="relative flex-1 md:w-60">
                        <input 
                            type="text" 
                            x-model="searchQuery" 
                            placeholder="{{ __('hub.filter_permissions') ?? 'Search permissions...' }}" 
                            class="w-full bg-slate-900/80 border border-white/10 rounded-xl pl-9 pr-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                        >
                        <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <button type="button" @click="selectAll()" class="px-3 py-2 text-xs font-semibold rounded-xl bg-indigo-500/10 text-indigo-300 hover:bg-indigo-500/20 border border-indigo-500/20 transition-colors whitespace-nowrap">
                        {{ __('hub.select_all') ?? 'Select All' }}
                    </button>
                    <button type="button" @click="deselectAll()" class="px-3 py-2 text-xs font-semibold rounded-xl bg-white/5 text-slate-400 hover:text-white border border-white/10 transition-colors whitespace-nowrap">
                        {{ __('hub.clear') ?? 'Clear' }}
                    </button>
                </div>
            </div>

            <!-- Modules and Permissions Grid -->
            <div class="space-y-6">
                @foreach($permissions as $module => $perms)
                    @php
                        $modPermNames = $perms->pluck('name')->toArray();
                    @endphp
                    <div class="rounded-2xl p-5 bg-white/[0.02] border border-white/5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-200">
                                    {{ ucfirst($module) }} {{ __('hub.module') ?? 'Module' }}
                                </h4>
                                <span class="text-xs text-slate-500">({{ $perms->count() }} {{ __('hub.permissions') ?? 'permissions' }})</span>
                            </div>
                            <button 
                                type="button" 
                                @click="toggleModule({{ json_encode($modPermNames) }})"
                                class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition-colors"
                            >
                                {{ __('hub.toggle_category') ?? 'Toggle Category' }}
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($perms as $perm)
                                <div 
                                    x-show="!searchQuery || '{{ strtolower($perm->name . ' ' . $perm->label . ' ' . $perm->description) }}'.includes(searchQuery.toLowerCase())"
                                    @click="togglePermission('{{ $perm->name }}')"
                                    :class="selectedPermissions.includes('{{ $perm->name }}') ? 'border-indigo-500/50 bg-indigo-500/[0.08]' : 'border-white/5 bg-slate-900/30 hover:border-white/10'"
                                    class="cursor-pointer rounded-xl p-3.5 border transition-all duration-150 flex items-start gap-3 select-none"
                                >
                                    <div class="pt-0.5">
                                        <div 
                                            class="w-4 h-4 rounded border flex items-center justify-center transition-colors"
                                            :class="selectedPermissions.includes('{{ $perm->name }}') ? 'bg-indigo-600 border-indigo-500 text-white' : 'border-slate-600 bg-slate-800'"
                                        >
                                            <svg x-show="selectedPermissions.includes('{{ $perm->name }}')" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1 mb-0.5">
                                            <span class="text-xs font-bold text-white truncate">{{ $perm->label }}</span>
                                            @if($perm->risk_level === 'danger')
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-red-500/20 text-red-300 border border-red-500/30">Danger</span>
                                            @elseif($perm->risk_level === 'high')
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30">High</span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] font-mono text-indigo-300/80 block">{{ $perm->name }}</span>
                                        <p class="text-[11px] text-slate-400 mt-1 line-clamp-2 leading-relaxed">{{ $perm->description }}</p>
                                    </div>

                                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" :checked="selectedPermissions.includes('{{ $perm->name }}')" class="hidden">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit Footer -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('roles.index') }}" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold transition-colors">
                {{ __('hub.cancel') ?? 'Cancel' }}
            </a>
            <button type="submit" class="px-8 py-3 rounded-xl accent-gradient text-white text-sm font-bold shadow-lg shadow-indigo-500/20 hover:opacity-90 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ __('hub.update_role') ?? 'Save Role Changes' }}</span>
            </button>
        </div>
    </form>
</div>
@endsection
