@extends('layouts.dashboard')

@section('title', __('hub.webhooks_title'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ __('hub.webhooks_realtime') }} <span class="accent-text-gradient">{{ __('hub.webhooks_title') }}</span></h2>
            <p class="text-slate-400 mt-1">{{ __('hub.webhooks_subtitle') }}</p>
        </div>
        <button @click="$dispatch('open-modal', 'add-webhook')" class="px-5 py-2.5 accent-gradient rounded-xl transition-all duration-300 shadow-lg shadow-indigo-500/20 font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            {{ __('hub.add_endpoint') }}
        </button>
    </div>

    <!-- Webhooks List -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($webhooks as $webhook)
            <div class="glass p-6 rounded-2xl relative group">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 rounded-xl bg-indigo-500/10 border border-white/5 text-indigo-400 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                    </div>
                    <form action="{{ route('tenant.webhooks.destroy', $webhook->id) }}" method="POST" onsubmit="return confirm('{{ __('hub.delete_webhook_confirm') }}')">
                        @csrf
                        @method('DELETE')
                        <button class="p-2 text-slate-600 hover:text-rose-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>

                <div class="mb-6">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">{{ __('hub.target_url') }}</p>
                    <p class="font-mono text-sm inline-block px-3 py-1 bg-slate-900 border border-white/5 rounded truncate max-w-full text-indigo-300">{{ $webhook->url }}</p>
                </div>

                <div class="mb-6">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">{{ __('hub.enabled_events') }}</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($webhook->events as $event)
                            <span class="px-2 py-0.5 bg-slate-800 text-slate-300 rounded text-[10px] border border-white/5 font-medium">{{ str_replace('.', ' ', $event) }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-between items-end pt-4 border-t border-white/5 mt-4">
                    <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">
                        {{ __('hub.last_success') }}: <span class="text-emerald-400">{{ $webhook->last_triggered_at?->diffForHumans() ?? __('hub.never') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('hub.healthy') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center glass rounded-2xl border-2 border-dashed border-white/5">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-20 h-20 bg-slate-900 rounded-full flex items-center justify-center text-slate-600 mb-2">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                    </div>
                    <h3 class="text-xl font-bold">{{ __('hub.no_endpoints_found') }}</h3>
                    <p class="text-slate-500 max-w-xs px-6">{{ __('hub.ready_to_receive') }}</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Modal -->
    <div x-data="{ show: false }" 
         x-show="show" 
         @open-modal.window="if($event.detail == 'add-webhook') show = true"
         class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm"
         x-cloak>
        <div class="glass w-full max-w-lg p-8 rounded-3xl shadow-2xl animate-in zoom-in-95 duration-200" @click.away="show = false">
            <h3 class="text-2xl font-bold mb-4">{{ __('hub.add_webhook_modal') }}</h3>
            
            <form action="{{ route('tenant.webhooks.store') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 px-1">{{ __('hub.endpoint_url') }}</label>
                        <input type="url" name="url" placeholder="https://api.yourdomain.com/webhooks" required 
                               class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 px-1">{{ __('hub.subscribe_to_events') }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach(['message.sent', 'message.received', 'whatsapp.connected', 'whatsapp_session.error'] as $event)
                                <label class="flex items-center gap-3 p-3 bg-slate-900 rounded-xl border border-white/5 hover:border-white/10 transition-all cursor-pointer">
                                    <input type="checkbox" name="events[]" value="{{ $event }}" checked class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-indigo-500 focus:ring-indigo-500">
                                    <span class="text-sm font-medium">{{ str_replace('.', ' ', $event) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/5 space-y-3">
                        <button type="submit" class="w-full py-3 accent-gradient rounded-xl font-bold text-white shadow-xl shadow-indigo-500/20">{{ __('hub.create_webhook') }}</button>
                        <button type="button" @click="show = false" class="w-full py-3 text-slate-500 hover:text-white transition-colors text-sm font-medium">{{ __('hub.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
