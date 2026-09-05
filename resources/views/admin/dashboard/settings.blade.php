@extends('layouts.admin')

@section('title', __('admin.settings.global_system_settings'))

@section('content')
<div class="max-w-4xl space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- General Settings -->
    <div class="glass p-8 rounded-3xl">
        <h3 class="text-xl font-bold mb-6 flex items-center gap-3">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ __('admin.settings.platform_identity') }}
        </h3>
        
        <form class="space-y-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 px-1">{{ __('admin.settings.platform_name') }}</label>
                    <input type="text" value="RagHub SaaS" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-red-500 focus:outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 px-1">{{ __('admin.settings.support_email') }}</label>
                    <input type="email" value="support@raghub.com" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-red-500 focus:outline-none transition-all">
                </div>
            </div>

            <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                <div>
                    <p class="font-bold">{{ __('admin.settings.maintenance_mode') }}</p>
                    <p class="text-xs text-slate-500">{{ __('admin.settings.maintenance_mode_desc') }}</p>
                </div>
                <button type="button" class="w-12 h-6 bg-slate-700 rounded-full relative transition-colors">
                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full"></div>
                </button>
            </div>

            <div class="pt-4">
                <button type="button" class="px-8 py-3 bg-white text-black font-bold rounded-xl shadow-lg hover:bg-slate-100 transition-colors">
                    {{ __('admin.settings.save_changes') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Security Settings -->
    <div class="glass p-8 rounded-3xl">
        <h3 class="text-xl font-bold mb-6 flex items-center gap-3">
             <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            {{ __('admin.settings.security_auth') }}
        </h3>
        
        <div class="space-y-6">
            <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                <div>
                    <p class="font-bold">{{ __('admin.settings.force_2fa') }}</p>
                    <p class="text-xs text-slate-500">{{ __('admin.settings.force_2fa_desc') }}</p>
                </div>
                <button type="button" class="w-12 h-6 bg-red-500 rounded-full relative transition-colors">
                    <div class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full"></div>
                </button>
            </div>

            <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                <div>
                    <p class="font-bold">{{ __('admin.settings.new_registrations') }}</p>
                    <p class="text-xs text-slate-500">{{ __('admin.settings.new_registrations_desc') }}</p>
                </div>
                <button type="button" class="w-12 h-6 bg-red-500 rounded-full relative transition-colors">
                    <div class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full"></div>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
