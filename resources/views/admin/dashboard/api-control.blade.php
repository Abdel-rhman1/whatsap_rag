@extends('layouts.admin')

@section('title', 'API Control Center')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
     <div class="glass p-8 rounded-3xl border-l-4 border-red-500">
        <h3 class="text-2xl font-bold mb-2">Emergency System Shutdown</h3>
        <p class="text-slate-400 mb-6">Instantly disable all API activity across all tenants. Use only in case of critical security breach or platform-wide maintenance.</p>
        <button class="px-8 py-4 bg-red-600 hover:bg-red-700 text-white font-extrabold rounded-2xl shadow-xl shadow-red-500/30 transition-all active:scale-95 uppercase tracking-tighter">
            Execute Emergency Shutdown
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="glass p-8 rounded-3xl">
            <h4 class="text-xl font-bold mb-6">Global Rate Limiting</h4>
            <div class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Requests per Minute (Global)</label>
                    <input type="number" value="10000" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-red-500 transition-all font-mono">
                </div>
                 <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Burst Allowance</label>
                    <input type="number" value="200" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-red-500 transition-all font-mono">
                </div>
                <button class="w-full py-3 bg-white text-black font-bold rounded-xl shadow-lg">Update Global Limits</button>
            </div>
        </div>

        <div class="glass p-8 rounded-3xl">
            <h4 class="text-xl font-bold mb-6">Service Health Overrides</h4>
            <div class="space-y-4">
                @foreach(['RAG Inference', 'Vector Search', 'WhatsApp Gateway', 'Webhook Dispatcher'] as $service)
                    <div class="flex justify-between items-center p-4 bg-white/5 rounded-2xl border border-white/5">
                        <span class="font-bold">{{ $service }}</span>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 bg-emerald-500/10 text-emerald-500 rounded-lg text-xs font-bold border border-emerald-500/20">OPERATIONAL</span>
                            <button class="text-slate-500 hover:text-white"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg></button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
