@extends('layouts.dashboard')

@section('content')
<div class="space-y-12 pb-20">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">@lang('hub.whatsapp_integration')</h1>
            <p class="text-slate-400 mt-1 text-lg">@lang('hub.whatsapp_desc')</p>
        </div>
        <div x-data="{ open: false }">
            <button @click="open = true" class="px-6 py-3 accent-gradient text-white rounded-xl font-bold uppercase tracking-widest text-sm shadow-lg shadow-indigo-500/30">
                @lang('hub.connect_new_instance')
            </button>
            
            <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
                <div @click.away="open = false" class="glass max-w-md w-full p-8 rounded-3xl border border-white/10 shadow-2xl">
                    <h2 class="text-2xl font-bold mb-6">@lang('hub.new_whatsapp_instance')</h2>
                    <form action="{{ route('whatsapp.instance.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-2">@lang('hub.instance_name_label')</label>
                            <input type="text" name="instance_name" placeholder="Support_Line_01" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500" required>
                        </div>
                        <button type="submit" class="w-full py-4 accent-gradient text-white rounded-xl font-bold uppercase tracking-widest shadow-lg">
                            @lang('hub.init_show_qr')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 glass border-emerald-500/30 bg-emerald-500/10 text-emerald-400 rounded-xl flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Instances List -->
        <div class="lg:col-span-2 space-y-6">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                @lang('hub.connected_channels')
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($instances as $instance)
                <div class="glass p-6 rounded-2xl border border-white/5 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="font-bold text-lg">{{ $instance->instance_name }}</h3>
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider {{ $instance->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-500' }}">
                                {{ $instance->is_active ? __('hub.active') : __('hub.paused') }}
                            </span>
                        </div>
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-bold uppercase">@lang('hub.status')</span>
                                <span class="px-2 py-0.5 rounded-full capitalize font-bold {{ $instance->status === 'connected' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-800 text-slate-300' }}">
                                    {{ $instance->status }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-bold uppercase">@lang('hub.phone')</span>
                                <span class="font-bold {{ $instance->phone_number ? 'text-emerald-400' : 'text-slate-500' }}">
                                    {{ $instance->phone_number ?? __('hub.not_linked') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <form action="{{ route('whatsapp.instance.toggle', $instance) }}" method="POST" class="flex-1">
                            @csrf
                            <button class="w-full py-2 rounded-lg border border-white/10 text-xs font-bold uppercase hover:bg-slate-800 transition-all">
                                {{ $instance->is_active ? __('hub.pause_bot') : __('hub.resume_bot') }}
                            </button>
                        </form>
                        
                        <div x-data="{ 
                            showQr: false, 
                            qrUrl: '{{ route('whatsapp.instance.qr', $instance) }}',
                            statusUrl: '{{ route('whatsapp.instance.status', $instance) }}',
                            state: 'loading',
                            statusText: '{{ __('hub.initializing') }}',
                            pollTimer: null,
                            qrTimestamp: 0,
                            startPolling() {
                                this.state = 'loading';
                                this.statusText = '{{ __('hub.initializing_session') }}';
                                this.pollStatus();
                            },
                            stopPolling() {
                                if (this.pollTimer) { clearTimeout(this.pollTimer); this.pollTimer = null; }
                            },
                            async pollStatus() {
                                try {
                                    const resp = await fetch(this.statusUrl);
                                    const data = await resp.json();
                                    
                                    if (data && data.status === 'CONNECTED') {
                                        this.state = 'connected';
                                        this.statusText = '{{ __('hub.phone_linked_success') }}';
                                        this.stopPolling();
                                        setTimeout(() => { window.location.reload(); }, 2000);
                                        return;
                                    }
                                    
                                    if (data && data.status === 'QR_READY' && data.hasQr) {
                                        if (data.qrTimestamp && this.qrTimestamp !== data.qrTimestamp) {
                                            this.qrTimestamp = data.qrTimestamp;
                                        } else if (!this.qrTimestamp) {
                                            this.qrTimestamp = Date.now();
                                        }
                                        this.state = 'qr_ready';
                                        this.statusText = '{{ __('hub.scan_instructions') }}';
                                    } else if (data && data.status === 'AUTHENTICATING') {
                                        this.state = 'authenticating';
                                        this.statusText = '{{ __('hub.connecting_check_phone') }}';
                                    } else if (data && data.status === 'GATEWAY_OFFLINE') {
                                        this.state = 'error';
                                        this.statusText = '{{ __('hub.gateway_offline') }}';
                                        return;
                                    } else {
                                        this.state = 'loading';
                                        this.statusText = '{{ __('hub.starting_session') }}';
                                    }
                                    
                                    this.pollTimer = setTimeout(() => this.pollStatus(), 2000);
                                } catch (e) {
                                    this.state = 'error';
                                    this.statusText = '{{ __('hub.connection_error_retry') }}';
                                }
                            }
                        }" x-init="$watch('showQr', v => { if(!v) { stopPolling(); qrTimestamp = 0; } })">
                            <button @click="showQr = true; startPolling()" class="px-4 py-2 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 hover:bg-indigo-500/20 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            </button>

                            <div x-show="showQr" class="fixed inset-0 z-[150] flex items-center justify-center p-6 bg-slate-950/90 backdrop-blur-md">
                                <div @click.away="showQr = false; stopPolling()" class="glass max-w-sm w-full p-8 rounded-3xl border border-white/10 shadow-2xl text-center">
                                     <h3 class="text-xl font-bold mb-4">@lang('hub.scan_qr_code')</h3>
                                     
                                     <div class="bg-white p-4 rounded-2xl mb-6 min-h-[260px] flex items-center justify-center">
                                         <!-- Loading -->
                                         <template x-if="state === 'loading'">
                                             <div class="text-slate-900 flex flex-col items-center">
                                                 <svg class="animate-spin h-8 w-8 mb-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                 <span class="text-xs font-bold">@lang('hub.starting_session')</span>
                                             </div>
                                         </template>
                                         
                                         <!-- Authenticating (after scan) -->
                                         <template x-if="state === 'authenticating'">
                                             <div class="text-slate-900 flex flex-col items-center">
                                                 <svg class="animate-spin h-8 w-8 mb-2 text-indigo-600" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                 <span class="text-xs font-bold text-indigo-700">{{ __('hub.connected') }}...</span>
                                             </div>
                                         </template>
                                         
                                         <!-- QR Ready -->
                                         <template x-if="state === 'qr_ready'">
                                             <img :src="qrUrl + '?t=' + qrTimestamp" alt="QR Code" class="w-full h-auto mx-auto" style="image-rendering: pixelated;">
                                         </template>
                                         
                                         <!-- Connected -->
                                         <template x-if="state === 'connected'">
                                             <div class="text-emerald-600 flex flex-col items-center">
                                                 <svg class="h-16 w-16 mb-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                                 <span class="text-sm font-bold">@lang('hub.linked_successfully')</span>
                                             </div>
                                         </template>
                                         
                                         <!-- Error -->
                                         <template x-if="state === 'error'">
                                             <div class="text-slate-900 flex flex-col items-center">
                                                 <svg class="h-12 w-12 text-rose-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                 <span class="text-xs font-bold text-rose-500" x-text="statusText"></span>
                                                 <button @click="startPolling()" class="mt-4 px-4 py-2 bg-slate-900 text-white rounded-lg text-[10px] uppercase font-bold">@lang('hub.retry')</button>
                                             </div>
                                         </template>
                                     </div>

                                     <p class="text-xs text-slate-400 mb-6 font-medium" x-text="statusText"></p>

                                     <button @click="showQr = false; stopPolling()" class="w-full py-3 bg-slate-800 text-white rounded-xl font-bold uppercase tracking-widest text-xs">
                                         @lang('hub.close')
                                     </button>
                                 </div>
                             </div>
                         </div>

                         <form action="{{ route('whatsapp.instance.delete', $instance) }}" method="POST" onsubmit="return confirm('{{ __('hub.scale_back_confirm') }}');" class="inline">
                             @csrf
                             @method('DELETE')
                             <button type="submit" class="p-2 rounded-lg bg-rose-500/10 text-rose-500 border border-rose-500/20 hover:bg-rose-500/20 transition-all" title="{{ __('hub.remove_connection') }}">
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                             </button>
                         </form>
                     </div>
                 </div>
                 @empty
                 <div class="col-span-full py-12 glass rounded-2xl border-dashed border-2 border-white/5 flex flex-col items-center justify-center text-slate-500">
                     <p class="font-bold uppercase tracking-widest text-xs">@lang('hub.no_instances')</p>
                 </div>
                 @endforelse
             </div>
         </div>

         <!-- Templates Side -->
         <!-- <div class="space-y-6">
             <h2 class="text-xl font-bold flex items-center gap-2">
                 <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                 @lang('hub.auto_responses')
             </h2>
            
            <div class="glass p-6 rounded-3xl border border-white/5 space-y-8">
                @php
                    $types = [
                        'greeting' => 'Greeting (First Message)',
                        'fallback' => 'Low Confidence Fallback',
                        'offline' => 'System Error / Busy',
                        'handoff' => 'Human Handoff Request'
                    ];
                @endphp

                @foreach($types as $type => $label)
                <form action="{{ route('whatsapp.template.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <div class="flex justify-between items-center">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500">{{ $label }}</label>
                        <button type="submit" class="text-[10px] font-bold text-indigo-400 uppercase hover:underline">Update</button>
                    </div>
                    <textarea name="message" rows="3" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500">{{ $templates->where('type', $type)->first()->message ?? '' }}</textarea>
                </form>
                @endforeach
            </div>
        </div> -->
    </div>
</div>
@endsection
