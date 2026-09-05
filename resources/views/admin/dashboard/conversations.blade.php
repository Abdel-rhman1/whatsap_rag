@extends('admin.layouts.app')

@section('title', 'Conversations')
@section('page-title', 'Bot Conversations')

@section('content')
<div class="space-y-6 animate-fade-in relative z-10">
    <!-- Header & Filters -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Chat History & Logs</h2>
            <p class="text-sm text-slate-500">Monitor automated conversations across all tenant integrations.</p>
        </div>
        
        <form method="GET" action="{{ route('admin.conversations') }}" class="w-full md:w-auto flex flex-col sm:flex-row gap-3">
            <!-- Search Filter -->
            <div class="relative w-full sm:w-64">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-slate-800 shadow-sm transition-all placeholder-slate-400">
            </div>

            <!-- Tenant Filter -->
            <select name="tenant_id" class="w-full sm:w-48 px-4 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-slate-700 shadow-sm cursor-pointer appearance-none transition-all pr-10">
                <option value="">All Tenants</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                        {{ $tenant->name }}
                    </option>
                @endforeach
            </select>

            <!-- Buttons -->
            <button type="submit" class="px-5 py-2 accent-gradient text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all text-sm shrink-0">
                Filter
            </button>
            @if(request()->hasAny(['tenant_id', 'search']))
                <a href="{{ route('admin.conversations') }}" class="px-5 py-2 bg-slate-100 text-slate-600 font-semibold rounded-xl hover:bg-slate-200 transition-all text-sm flex items-center justify-center shrink-0">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Conversations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($conversations as $conversation)
            <div class="bg-white rounded-2xl p-6 border border-slate-100 card-neo hover:-translate-y-1 transition-all duration-300 group flex flex-col">
                <div class="flex items-start justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-xl flex items-center justify-center text-indigo-700 font-bold border border-indigo-200 shadow-sm group-hover:scale-105 transition-transform shrink-0">
                            {{ substr($conversation->tenant?->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800 truncate" title="{{ $conversation->tenant?->name ?? 'Unknown Tenant' }}">{{ $conversation->tenant?->name ?? 'Unknown Tenant' }}</p>
                            <p class="text-xs font-mono text-slate-500 truncate" title="{{ $conversation->external_id ?? 'No ID' }}">ID: {{ $conversation->external_id ?? 'No ID' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex-1 flex flex-col justify-center py-4 border-y border-slate-50 mb-5">
                    <div class="flex items-center gap-4 px-2">
                        <div class="flex-1 text-center">
                            <span class="block text-2xl font-black text-indigo-600">{{ $conversation->messages_count ?? 0 }}</span>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Messages</span>
                        </div>
                        <div class="w-px h-10 bg-slate-200"></div>
                        <div class="flex-1 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 mb-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </span>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Active</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between mt-auto">
                    <div class="text-xs font-medium text-slate-500 flex flex-col gap-1">
                        <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Started: {{ $conversation->created_at->diffForHumans() }}</span>
                        <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Last Activity: {{ $conversation->updated_at->diffForHumans() }}</span>
                    </div>
                    
                    <button class="w-8 h-8 rounded-full bg-slate-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-colors border border-slate-200" title="View Details">
                        <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 xl:col-span-3">
                <div class="bg-white rounded-2xl p-16 border border-slate-100 text-center card-neo">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1">No Conversations Found</h3>
                    <p class="text-sm text-slate-500 max-w-sm mx-auto">There are no matching conversations based on your current filters.</p>
                    @if(request()->hasAny(['tenant_id', 'search']))
                        <a href="{{ route('admin.conversations') }}" class="mt-4 inline-block text-indigo-600 font-semibold text-sm hover:underline">Clear Filters</a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($conversations->hasPages())
        <div class="mt-8 flex justify-center">
            <div class="bg-white border border-slate-100 rounded-xl p-2 shadow-sm inline-block">
                {{ $conversations->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
