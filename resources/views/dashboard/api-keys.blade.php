@extends('layouts.dashboard')

@section('title', __('hub.api_keys_title'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ __('hub.manage') }} <span class="accent-text-gradient">{{ __('hub.api_keys_title') }}</span></h2>
            <p class="text-slate-400 mt-1">{{ __('hub.api_keys_subtitle') }}</p>
        </div>
        <button @click="$dispatch('open-modal', 'generate-key')" class="px-5 py-2.5 accent-gradient rounded-xl transition-all duration-300 shadow-lg shadow-indigo-500/20 font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            {{ __('hub.generate_new_key') }}
        </button>
    </div>

    <!-- Info Box -->
    <div class="glass p-6 rounded-2xl border-l-4 border-indigo-500 bg-indigo-500/5">
        <div class="flex gap-4">
            <div class="p-3 rounded-full bg-indigo-500/20 text-indigo-400 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="font-bold">{{ __('hub.security_notice_title') }}</p>
                <p class="text-sm text-slate-400">{{ __('hub.security_notice_text') }}</p>
            </div>
        </div>
    </div>

    <!-- keys list -->
    <div class="glass rounded-2xl overflow-hidden overflow-x-auto">
        <table class="w-full text-left min-w-[600px]">
            <thead>
                <tr class="bg-white/5 text-slate-400 text-xs uppercase tracking-widest font-bold">
                    <th class="px-6 py-4">{{ __('hub.table_name') }}</th>
                    <th class="px-6 py-4">{{ __('hub.table_key') }}</th>
                    <th class="px-6 py-4">{{ __('hub.table_status') }}</th>
                    <th class="px-6 py-4">{{ __('hub.table_created') }}</th>
                    <th class="px-6 py-4 text-right">{{ __('hub.table_actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($apiKeys as $key)
                    <tr class="hover:bg-white/[0.02] transition-colors text-right-dir">
                        <td class="px-6 py-4">
                            <span class="font-semibold">{{ $key->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 group" x-data="{ copied: false, key: '{{ $key->key }}' }">
                                <code class="bg-slate-900 border border-white/10 px-3 py-1 rounded text-xs text-indigo-300 font-mono">
                                    {{ substr($key->key, 0, 8) }}**********************
                                </code>
                                <button @click="navigator.clipboard.writeText(key); copied = true; setTimeout(() => copied = false, 2000)" class="text-slate-500 hover:text-white transition-colors">
                                    <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <svg x-show="copied" class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($key->status == 'active')
                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/20">{{ __('hub.status_active') }}</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-400 text-[10px] font-bold uppercase tracking-wider border border-rose-500/20">{{ __('hub.status_revoked') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-sm">
                            {{ $key->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($key->status == 'active')
                                <form action="{{ route('tenant.api-keys.revoke', $key->id) }}" method="POST" onsubmit="return confirm('{{ __('hub.revoke_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-400 hover:text-rose-300 transition-colors text-xs font-bold uppercase tracking-widest">{{ __('hub.revoke_button') }}</button>
                                </form>
                            @else
                                <span class="text-slate-600 text-xs font-bold uppercase tracking-widest">{{ __('hub.unavailable') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 bg-slate-900 rounded-full flex items-center justify-center text-slate-600">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                </div>
                                <p class="text-slate-500 font-medium">{{ __('hub.no_api_keys_found') }}</p>
                                <button @click="$dispatch('open-modal', 'generate-key')" class="text-indigo-400 hover:text-indigo-300 font-bold transition-colors">{{ __('hub.generate_first_key') }} &rarr;</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal (Alpine.js) -->
    <div x-data="{ show: false }" 
         x-show="show" 
         @open-modal.window="if($event.detail == 'generate-key') show = true"
         class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm"
         x-cloak>
        <div class="glass w-full max-w-md p-8 rounded-3xl shadow-2xl animate-in zoom-in-95 duration-200" @click.away="show = false">
            <h3 class="text-2xl font-bold mb-4">{{ __('hub.new_api_key_modal_title') }}</h3>
            <p class="text-slate-400 text-sm mb-6">{{ __('hub.new_api_key_modal_subtitle') }}</p>
            
            <form action="{{ route('tenant.api-keys.generate') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 px-1">{{ __('hub.key_name_label') }}</label>
                        <input type="text" name="name" placeholder="{{ __('hub.key_name_placeholder') }}" required 
                               class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    </div>
                    <button type="submit" class="w-full py-3 accent-gradient rounded-xl font-bold text-white shadow-xl shadow-indigo-500/20 mt-4">{{ __('hub.generate_new_key') }}</button>
                    <button type="button" @click="show = false" class="w-full py-3 text-slate-500 hover:text-white transition-colors text-sm font-medium">{{ __('hub.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
