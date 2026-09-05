@extends('layouts.dashboard')

@section('title', __('hub.system_settings'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6 {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
    <div class="glass p-8 rounded-3xl border border-white/10 shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} p-8 opacity-10">
            <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
        </div>
        
        <h2 class="text-2xl font-black mb-2 accent-text-gradient">{{ __('hub.system_settings') }}</h2>
        <p class="text-slate-400 mb-8">{{ __('hub.knowledge_config_subtitle') ?? 'Configure vector RAG thresholds, namespaces, and AI fallback behavior.' }}</p>

        <form action="{{ route('dashboard.knowledge-config.update') }}" method="POST" class="space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Similarity Threshold -->
                <div class="space-y-3">
                    <label class="block text-sm font-bold text-slate-300">{{ __('hub.similarity_threshold') }}</label>
                    <div class="flex items-center gap-4">
                        <input type="range" name="similarity_threshold" min="0" max="1" step="0.05" value="{{ $config->similarity_threshold }}" 
                            class="flex-1 accent-indigo-500" 
                            oninput="this.nextElementSibling.innerText = this.value">
                        <span class="w-12 text-center font-mono font-black text-indigo-400">{{ $config->similarity_threshold }}</span>
                    </div>
                    <p class="text-[10px] text-slate-500 italic">{{ __('hub.threshold_desc') }}</p>
                </div>

                <!-- Custom Namespace -->
                <div class="space-y-3">
                    <label class="block text-sm font-bold text-slate-300">{{ __('hub.vector_namespace') }}</label>
                    <input type="text" name="vector_namespace" value="{{ $config->vector_namespace }}" placeholder="tenant_{{ auth('tenant')->id() }}_kb" 
                        class="w-full bg-slate-900/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500 transition-all font-mono text-sm {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Allowed File Types -->
                <div class="space-y-3">
                    <label class="block text-sm font-bold text-slate-300 mb-2">{{ __('hub.allowed_file_types') }}</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach(['pdf', 'docx', 'audio', 'image'] as $type)
                        <label class="relative flex items-center gap-2 cursor-pointer group">
                             <input type="checkbox" name="allowed_file_types[]" value="{{ $type }}" class="peer hidden" 
                                {{ in_array($type, $config->allowed_file_types ?? []) ? 'checked' : '' }}>
                             <span class="px-4 py-2 bg-slate-900 border border-white/10 rounded-full text-xs font-bold transition-all peer-checked:bg-indigo-500 peer-checked:border-indigo-400 peer-checked:text-white group-hover:border-white/30">{{ strtoupper($type) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Toggles -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between p-4 bg-slate-900/30 rounded-2xl border border-white/5">
                        <div class="{{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                            <p class="text-sm font-bold">{{ __('hub.context_only_mode') }}</p>
                            <p class="text-[10px] text-slate-500">{{ __('hub.context_only_desc') }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="context_only_mode" value="1" class="sr-only peer" {{ $config->context_only_mode ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-slate-900/30 rounded-2xl border border-white/5">
                        <div class="{{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                            <p class="text-sm font-bold">{{ __('hub.human_fallback') }}</p>
                            <p class="text-[10px] text-slate-500">{{ __('hub.human_fallback_desc') }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="hallucination_prevention" value="1" class="sr-only peer" {{ $config->hallucination_prevention ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-white/5 flex justify-end">
                <button type="submit" class="px-8 py-3 accent-gradient text-white rounded-xl font-bold hover:shadow-lg hover:shadow-indigo-500/20 transition-all active:scale-95">{{ __('hub.save_changes') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
