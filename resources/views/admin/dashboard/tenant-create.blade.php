@extends('layouts.admin')

@section('title', 'Provision New Tenant')

@section('content')
<div class="max-w-2xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="glass p-8 rounded-3xl">
        <h3 class="text-2xl font-bold mb-6">Tenant Configuration</h3>
        
        <form action="{{ route('admin.tenants.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 px-1">Display Name</label>
                    <input type="text" name="name" placeholder="Acme Corporation" required 
                           class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 px-1">Primary Email</label>
                    <input type="email" name="email" placeholder="admin@acme.com" required 
                           class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all">
                    <p class="mt-2 text-[10px] text-slate-500 italic">This will be the tenant's login ID.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 px-1">Initial Password</label>
                    <input type="password" name="password" value="password123" required 
                           class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all">
                </div>

                <div class="pt-6 border-t border-white/5 flex gap-4">
                    <button type="submit" class="flex-1 py-4 accent-gradient rounded-xl font-bold text-white shadow-xl shadow-red-500/20 transition-all active:scale-95">
                        Provision Tenant Node
                    </button>
                    <a href="{{ route('admin.tenants') }}" class="px-8 py-4 bg-slate-900 text-slate-400 font-bold rounded-xl border border-white/5 hover:bg-slate-800 transition-colors">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="glass p-6 rounded-2xl border-l-4 border-indigo-500 bg-indigo-500/5">
        <p class="text-sm font-medium">Automatic Provisioning</p>
        <p class="text-xs text-slate-400 mt-1">Provisioning a tenant will automatically generate their isolation credentials and initialize a dedicated vector collection for their RAG data.</p>
    </div>
</div>
@endsection
