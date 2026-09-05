@extends('layouts.admin')

@section('title', 'Tenant View: ' . $tenant->name)

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Breadcrumbs & Quick Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.tenants') }}" class="hover:text-red-400">Tenants</a>
            <span>&raquo;</span>
            <span class="text-white font-medium">{{ $tenant->name }}</span>
        </div>
        <div class="flex gap-3">
             <form action="{{ route('admin.tenants.status', $tenant->id) }}" method="POST">
                @csrf
                @method('PATCH')
                @if($tenant->status == 'active')
                    <input type="hidden" name="status" value="suspended">
                    <button class="px-5 py-2.5 bg-rose-500/10 border border-rose-500/20 text-rose-500 hover:bg-rose-500 hover:text-white rounded-xl transition-all duration-300 font-semibold flex items-center gap-2">
                         Suspend Account
                    </button>
                @else
                    <input type="hidden" name="status" value="active">
                    <button class="px-5 py-2.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 hover:bg-emerald-500 hover:text-white rounded-xl transition-all duration-300 font-semibold flex items-center gap-2">
                         Activate Account
                    </button>
                @endif
            </form>
            <button class="px-5 py-2.5 bg-white text-black rounded-xl font-bold">Edit Details</button>
        </div>
    </div>

    <!-- Tenant Grid -->
    <div class="grid grid-cols-12 gap-8">
        <!-- Main Stats -->
        <div class="col-span-12 lg:col-span-8 space-y-8">
            <div class="glass p-8 rounded-3xl grid grid-cols-3 gap-8">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Knowledge Sources</p>
                    <p class="text-3xl font-bold">{{ $tenant->knowledge_sources_count }}</p>
                </div>
                 <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Total Conversations</p>
                    <p class="text-3xl font-bold">{{ $tenant->conversations_count }}</p>
                </div>
                 <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Last Login</p>
                    <p class="text-lg font-bold">{{ $tenant->last_login_at?->diffForHumans() ?? 'Never' }}</p>
                </div>
            </div>

            <!-- API Keys & Webhooks -->
            <div class="grid grid-cols-2 gap-6">
                <div class="glass p-6 rounded-2xl">
                    <h5 class="font-bold mb-4">Active API Keys</h5>
                    <div class="space-y-3">
                        @foreach($tenant->apiKeys as $key)
                            <div class="flex justify-between items-center text-sm p-3 bg-white/[0.02] border border-white/5 rounded-xl">
                                <span>{{ $key->name }}</span>
                                <span class="text-[10px] font-bold text-emerald-400">{{ $key->status }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="glass p-6 rounded-2xl">
                    <h5 class="font-bold mb-4">Webhooks</h5>
                    <div class="space-y-3">
                        @foreach($tenant->webhooks as $webhook)
                            <div class="flex flex-col gap-1 p-3 bg-white/[0.02] border border-white/5 rounded-xl overflow-hidden">
                                <span class="text-xs truncate font-mono text-slate-400">{{ $webhook->url }}</span>
                                <span class="text-[10px] font-bold text-indigo-400 uppercase">{{ $webhook->status }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- WhatsApp Sessions -->
            <div class="glass p-8 rounded-3xl">
                <h5 class="font-bold mb-6">WhatsApp Instances</h5>
                <div class="space-y-4">
                    @foreach($tenant->whatsappInstances as $instance)
                         <div class="flex items-center gap-4 p-4 bg-slate-900/40 rounded-2xl border border-white/5">
                            <div class="w-10 h-10 rounded-full bg-emerald-500/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold">{{ $instance->name ?? 'Default Instance' }}</p>
                                <p class="text-[10px] text-slate-500 font-mono">{{ $instance->instance_id }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 text-[10px] font-bold uppercase tracking-wider">{{ $instance->status }}</span>
                         </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar Config -->
        <div class="col-span-12 lg:col-span-4 space-y-8">
            <div class="glass p-8 rounded-3xl border-l-4 border-amber-500">
                <h4 class="text-lg font-bold mb-2">Internal Note</h4>
                <textarea class="w-full bg-transparent border-none focus:outline-none text-slate-400 text-sm italic" rows="4" placeholder="Add a private note about this tenant..."></textarea>
                <button class="mt-4 text-xs font-bold text-amber-500 uppercase tracking-widest hover:text-white transition-colors">Save Note</button>
            </div>
        </div>
    </div>
</div>
@endsection
