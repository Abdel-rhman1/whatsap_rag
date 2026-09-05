@extends('admin.layouts.app')

@section('title', 'API Keys')
@section('page-title', 'Platform API Keys')

@section('content')
<div class="space-y-6 animate-fade-in relative z-10" x-data="{ copyToClipboard(text, id) { navigator.clipboard.writeText(text); $dispatch('notify', {title: 'Copied!', message: 'API Key copied to clipboard', type: 'success'}); } }">
    <!-- Header & Filters -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">API Keys Management</h2>
            <p class="text-sm text-slate-500">Monitor and manage access tokens across all tenants.</p>
        </div>
        
        <form method="GET" action="{{ route('admin.api-keys') }}" class="w-full md:w-auto flex flex-col sm:flex-row gap-3">
            <!-- Tenant Filter -->
            <select name="tenant_id" class="w-full sm:w-48 px-4 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-slate-700 shadow-sm cursor-pointer appearance-none transition-all pr-10">
                <option value="">All Tenants</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                        {{ $tenant->name }}
                    </option>
                @endforeach
            </select>

            <!-- Status Filter -->
            <select name="status" class="px-4 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-slate-700 shadow-sm cursor-pointer appearance-none transition-all pr-10">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
            </select>

            <!-- Buttons -->
            <button type="submit" class="px-5 py-2 accent-gradient text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all text-sm shrink-0">
                Filter Keys
            </button>
            @if(request()->hasAny(['tenant_id', 'status']))
                <a href="{{ route('admin.api-keys') }}" class="px-5 py-2 bg-slate-100 text-slate-600 font-semibold rounded-xl hover:bg-slate-200 transition-all text-sm flex items-center justify-center shrink-0">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- API Keys Table Card -->
    <div class="bg-white rounded-2xl border border-slate-100 card-neo overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tenant</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Key Details</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-48">API Key</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Usage & Dates</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($apiKeys as $key)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <!-- Tenant -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg flex items-center justify-center text-indigo-700 font-bold border border-indigo-200 shadow-sm flex-shrink-0">
                                        {{ substr($key->tenant->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate">{{ $key->tenant->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-slate-500 font-medium truncate">{{ $key->tenant->email ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Key Name -->
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded-lg border border-slate-200">{{ $key->name ?? 'Default Key' }}</span>
                            </td>
                            <!-- Masked Key -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <code class="text-xs bg-slate-800 text-indigo-100 px-3 py-1.5 rounded-lg border border-slate-700 font-mono tracking-wider w-40 text-center truncate">
                                        {{ $key->masked_key ?? 'sk-live-••••••••••••' }}
                                    </code>
                                    <button @click="copyToClipboard('{{ $key->key ?? $key->masked_key }}', '{{ $key->id }}')" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors tooltip" title="Copy to clipboard">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </div>
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border
                                    {{ $key->status === 'active' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $key->status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    {{ ucfirst($key->status) }}
                                </span>
                            </td>
                            <!-- Dates -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-medium text-slate-600 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Used: {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">
                                        Created: {{ $key->created_at->format('M d, Y') }}
                                    </span>
                                </div>
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2" x-data="{ confirming: false }">
                                    <button x-show="!confirming" @click="confirming = true" class="flex items-center gap-1 px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg text-xs font-bold border border-amber-200 transition-colors tooltip" title="Regenerate Key">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Renew
                                    </button>
                                    
                                    <div x-show="confirming" class="flex gap-2" style="display: none;">
                                        <button @click="confirming = false" class="px-3 py-1.5 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-lg text-xs font-bold border border-slate-200 transition-colors">Cancel</button>
                                        <button @click="$dispatch('notify', {title: 'Key Regenerated', message: 'API key successfully regenerated.', type: 'success'}); confirming = false;" class="px-3 py-1.5 bg-rose-500 text-white hover:bg-rose-600 rounded-lg text-xs font-bold transition-colors">Confirm</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center border border-slate-100 mb-2">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">No API keys found</h3>
                                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Either no tenants have generated keys or your filters didn't match any records.</p>
                                    @if(request()->hasAny(['tenant_id', 'status']))
                                    <a href="{{ route('admin.api-keys') }}" class="mt-2 text-indigo-600 font-medium text-sm hover:underline">Clear all filters</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($apiKeys->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $apiKeys->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
