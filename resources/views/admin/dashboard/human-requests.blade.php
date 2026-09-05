@extends('admin.layouts.app')

@section('title', 'Human Requests')
@section('page-title', 'Human Handoff Requests')

@section('content')
<div class="space-y-6 animate-fade-in relative z-10">
    <!-- Header & Filters -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Support Escalations</h2>
            <p class="text-sm text-slate-500">Track and manage fallback requests across tenant chatbots.</p>
        </div>
        
        <form method="GET" action="{{ route('admin.human-requests') }}" class="w-full md:w-auto flex flex-col sm:flex-row gap-3">
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
            <select name="status" class="w-full sm:w-48 px-4 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-slate-700 shadow-sm cursor-pointer appearance-none transition-all pr-10">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
            </select>

            <!-- Buttons -->
            <button type="submit" class="px-5 py-2 accent-gradient text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 transition-all text-sm shrink-0">
                Apply Filters
            </button>
            @if(request()->hasAny(['tenant_id', 'status']))
                <a href="{{ route('admin.human-requests') }}" class="px-5 py-2 bg-slate-100 text-slate-600 font-semibold rounded-xl hover:bg-slate-200 transition-all text-sm flex items-center justify-center shrink-0">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Requests List -->
    <div class="space-y-4">
        @forelse($requests as $request)
            <div class="bg-white rounded-2xl p-6 border border-slate-100 card-neo hover:translate-x-1 transition-all duration-300">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-4">
                            <!-- Status Badge -->
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border shrink-0
                                {{ $request->status === 'pending' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $request->status === 'pending' ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                                {{ ucfirst($request->status) }}
                            </span>
                            
                            <div class="h-4 w-px bg-slate-200"></div>
                            
                            <!-- Tenant Context -->
                            <p class="text-xs font-semibold text-slate-500 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $request->tenant?->name ?? 'Unknown Tenant' }}
                            </p>
                        </div>
                        
                        <div class="ml-1 pl-4 border-l-2 border-indigo-100">
                            <!-- User Message -->
                            <p class="text-sm font-medium text-slate-800 mb-2 leading-relaxed">
                                "{{ $request->user_message ?? $request->last_message ?? 'User requested human assistance without providing a specific message.' }}"
                            </p>
                            
                            <div class="flex flex-wrap items-center gap-3 mt-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 bg-slate-50 px-2.5 py-1 rounded-md border border-slate-100">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $request->name ?? 'Anonymous User' }}
                                </span>
                                
                                @if($request->language)
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 bg-slate-50 px-2.5 py-1 rounded-md border border-slate-100">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                                    {{ strtoupper($request->language) }}
                                </span>
                                @endif

                                @if($request->reason)
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-md border border-rose-100">
                                    <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Reason: {{ $request->reason }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col lg:items-end gap-2 shrink-0 border-t lg:border-t-0 border-slate-100 pt-4 lg:pt-0 mt-2 lg:mt-0">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 lg:text-right">Activity Timeline</div>
                        <div class="flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Requested {{ $request->created_at->diffForHumans() }}
                        </div>
                        
                        @if($request->resolved_at)
                        <div class="flex items-center gap-1.5 text-xs font-medium text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Resolved {{ $request->resolved_at->diffForHumans() }}
                        </div>
                        @else
                        <!-- Action placeholder: you could add a resolve button here if logic existed -->
                        @endif
                        
                        <a href="{{ route('admin.conversations', ['search' => $request->whatsapp_id]) }}" class="mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors group">
                            View Conversation
                            <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-16 border border-slate-100 text-center card-neo">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">No Human Requests</h3>
                <p class="text-sm text-slate-500 max-w-sm mx-auto">There are no pending or resolved human intervention requests matching your filters.</p>
                @if(request()->hasAny(['tenant_id', 'status']))
                    <a href="{{ route('admin.human-requests') }}" class="mt-4 inline-block text-indigo-600 font-semibold text-sm hover:underline">Clear Filters</a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($requests->hasPages())
        <div class="mt-8 flex justify-center">
            <div class="bg-white border border-slate-100 rounded-xl p-2 shadow-sm inline-block">
                {{ $requests->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
