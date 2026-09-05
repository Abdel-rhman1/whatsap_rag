@extends('admin.layouts.app')

@section('title', 'Knowledge Bases')
@section('page-title', 'Knowledge Bases Management')

@section('content')
<div class="space-y-6 animate-fade-in relative z-10">
    <!-- Header & Filters -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Tenant Knowledge Bases</h2>
            <p class="text-sm text-slate-500">Monitor and manage documents, URLs, and text sources across all tenants.</p>
        </div>
        
        <form method="GET" action="{{ route('admin.knowledge-bases') }}" class="w-full md:w-auto flex flex-col sm:flex-row gap-3">
            <!-- Tenant Filter -->
            <select name="tenant_id" class="w-full sm:w-64 px-4 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-slate-700 shadow-sm cursor-pointer appearance-none transition-all pr-10">
                <option value="">All Tenants</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                        {{ $tenant->name }}
                    </option>
                @endforeach
            </select>

            <!-- Buttons -->
            <button type="submit" class="px-5 py-2 accent-gradient text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all text-sm shrink-0">
                Filter Sources
            </button>
            @if(request('tenant_id'))
                <a href="{{ route('admin.knowledge-bases') }}" class="px-5 py-2 bg-slate-100 text-slate-600 font-semibold rounded-xl hover:bg-slate-200 transition-all text-sm flex items-center justify-center shrink-0">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Knowledge Sources Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($knowledgeSources as $source)
            <div class="bg-white rounded-2xl p-6 border border-slate-100 card-neo hover:-translate-y-1 transition-all duration-300 group flex flex-col">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-indigo-50 group-hover:bg-indigo-100 rounded-xl flex items-center justify-center transition-colors">
                        @if(($source->type ?? 'document') === 'url' || ($source->type ?? 'document') === 'website')
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        @elseif(($source->type ?? 'document') === 'text')
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        @else
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @endif
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg border bg-slate-50 border-slate-200 text-slate-600">
                        {{ $source->type ?? 'document' }}
                    </span>
                </div>
                
                <h3 class="text-lg font-bold text-slate-800 mb-1 line-clamp-2" title="{{ $source->title ?? 'Untitled' }}">{{ $source->title ?? 'Untitled' }}</h3>
                <p class="text-sm font-medium text-slate-500 mb-6 flex items-center gap-1.5 flex-1">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="truncate">{{ $source->tenant->name ?? 'Unknown Tenant' }}</span>
                </p>
                
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $source->created_at->format('M d, Y') }}
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        {{ $source->chunks_count ?? 0 }} chunks
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 xl:col-span-4">
                <div class="bg-white rounded-2xl p-12 border border-slate-100 text-center card-neo">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1">No Knowledge Bases Found</h3>
                    <p class="text-sm text-slate-500 max-w-sm mx-auto">There are no knowledge sources matching your current filters, or no tenants have uploaded any knowledge yet.</p>
                    @if(request('tenant_id'))
                        <a href="{{ route('admin.knowledge-bases') }}" class="mt-4 inline-block text-indigo-600 font-semibold text-sm hover:underline">Clear Filters</a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($knowledgeSources->hasPages())
        <div class="mt-8 flex justify-center">
            <div class="bg-white border border-slate-100 rounded-xl p-2 shadow-sm inline-block">
                {{ $knowledgeSources->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
