@extends('layouts.dashboard')

@section('title', __('hub.website_chat_widget'))

@section('content')
<div class="space-y-8 pb-16" x-data="widgetCustomizer({
    primaryColor: '{{ old('primary_color', $settings->primary_color ?? '#6366f1') }}',
    botName: '{{ old('bot_name', $settings->bot_name ?? 'AI Assistant') }}',
    bubbleTitle: '{{ old('bubble_title', $settings->bubble_title ?? __('hub.chat_with_us')) }}',
    greeting: '{{ old('greeting_message', $settings->greeting_message ?? __('hub.default_greeting')) }}',
    placeholder: '{{ old('placeholder_text', $settings->placeholder_text ?? __('hub.type_a_message')) }}',
    theme: '{{ old('theme', $settings->theme ?? 'dark') }}',
    position: '{{ old('position', $settings->position ?? 'bottom-right') }}',
    questions: {{ Js::from(old('suggested_questions', $settings->suggested_questions ?? [__('hub.default_question_1'), __('hub.default_question_2')])) }},
    apiKey: '{{ $activeKey }}',
    cdnUrl: '{{ $cdnUrl }}',
    copiedText: '{{ __('hub.copied') }}',
    copyText: '{{ __('hub.copy_snippet') }}',
    mockReply: '{{ __('hub.mock_bot_reply') }}',
    questionPlaceholder: '{{ __('hub.question_placeholder') }}'
})">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">{{ __('hub.website_chat_widget') }}</h1>
            <p class="text-slate-400 mt-1 text-base">{{ __('hub.widget_page_desc') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/test-widget.html" target="_blank" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider flex items-center gap-2 border border-white/10 transition-all shadow-lg">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>{{ __('hub.live_test_demo') }}</span>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 glass border-emerald-500/30 bg-emerald-500/10 text-emerald-400 rounded-xl flex items-center gap-3 shadow-lg">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Quick Embed Code Banner -->
    <div class="glass p-6 rounded-2xl border border-white/10 shadow-xl space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    <span>{{ __('hub.html_embed_code') }}</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('hub.embed_snippet_desc') }}</p>
            </div>
            
            <div class="flex items-center gap-3 shrink-0">
                <!-- API Key Selection -->
                <div class="flex items-center gap-2">
                    <label class="text-xs text-slate-400 font-semibold uppercase">{{ __('hub.api_key_label') }}</label>
                    <select x-model="apiKey" class="bg-slate-900 text-xs text-white border border-white/10 rounded-lg px-3 py-2 outline-none focus:border-indigo-500 font-mono">
                        @foreach($apiKeys as $keyItem)
                            <option value="{{ $keyItem->key }}">{{ $keyItem->name }} ({{ $keyItem->key_prefix }}...)</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" @click="copySnippet()" class="px-4 py-2 accent-gradient text-white rounded-lg text-xs font-bold uppercase tracking-wider shadow hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    <span x-text="copied ? copiedText : copyText"></span>
                </button>
            </div>
        </div>

        <div class="relative bg-slate-950/80 rounded-xl p-4 border border-white/5 font-mono text-xs text-emerald-400 overflow-x-auto select-all" dir="ltr">
            <span x-text="snippet"></span>
        </div>
    </div>

    <!-- Main Grid: Controls + Live Preview -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Controls Column -->
        <div class="lg:col-span-7 space-y-6">
            <form action="{{ route('widget.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- General & Behavior -->
                <div class="glass p-6 rounded-2xl border border-white/5 space-y-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span>{{ __('hub.general_and_behavior') }}</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center justify-between p-4 bg-slate-900/60 rounded-xl border border-white/5">
                            <div>
                                <label class="text-xs font-bold uppercase text-white block">{{ __('hub.widget_status') }}</label>
                                <span class="text-[11px] text-slate-400">{{ __('hub.show_widget_external') }}</span>
                            </div>
                            <input type="checkbox" name="is_enabled" value="1" {{ $settings->is_enabled ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500 rounded cursor-pointer">
                        </div>

                        <div class="flex items-center justify-between p-4 bg-slate-900/60 rounded-xl border border-white/5">
                            <div>
                                <label class="text-xs font-bold uppercase text-white block">{{ __('hub.sound_chime') }}</label>
                                <span class="text-[11px] text-slate-400">{{ __('hub.sound_chime_desc') }}</span>
                            </div>
                            <input type="checkbox" name="sound_enabled" value="1" {{ $settings->sound_enabled ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500 rounded cursor-pointer">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">{{ __('hub.bot_name_label') }}</label>
                            <input type="text" name="bot_name" x-model="botName" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">{{ __('hub.bubble_title_label') }}</label>
                            <input type="text" name="bubble_title" x-model="bubbleTitle" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Appearance & Theme -->
                <div class="glass p-6 rounded-2xl border border-white/5 space-y-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        <span>{{ __('hub.branding_and_colors') }}</span>
                    </h3>

                    <!-- Color Picker + Palettes -->
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">{{ __('hub.primary_brand_color') }}</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="primary_color" x-model="primaryColor" class="w-12 h-10 bg-transparent rounded-lg cursor-pointer border border-white/10 p-1">
                            <input type="text" x-model="primaryColor" dir="ltr" class="w-32 bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs font-mono text-white uppercase focus:outline-none focus:border-indigo-500">
                            
                            <!-- Quick Swatches -->
                            <div class="flex items-center gap-1.5 {{ app()->getLocale() == 'ar' ? 'mr-2' : 'ml-2' }}">
                                <template x-for="c in ['#6366f1', '#00a884', '#0284c7', '#8b5cf6', '#ec4899', '#f59e0b', '#0f172a']" :key="c">
                                    <button type="button" @click="primaryColor = c" 
                                            :style="'background:' + c"
                                            :class="primaryColor === c ? 'ring-2 ring-white scale-110' : 'opacity-80 hover:opacity-100'"
                                            class="w-6 h-6 rounded-full transition-all shadow-sm"></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Theme & Position -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-400 mb-2">{{ __('hub.color_mode') }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label :class="theme === 'dark' ? 'bg-indigo-500/20 border-indigo-500 text-white font-bold' : 'bg-slate-900/60 border-white/10 text-slate-400'" class="flex items-center justify-center gap-2 p-3 rounded-xl border cursor-pointer transition-all text-xs">
                                    <input type="radio" name="theme" value="dark" x-model="theme" class="hidden">
                                    <span>{{ __('hub.dark_mode') }}</span>
                                </label>
                                <label :class="theme === 'light' ? 'bg-indigo-500/20 border-indigo-500 text-white font-bold' : 'bg-slate-900/60 border-white/10 text-slate-400'" class="flex items-center justify-center gap-2 p-3 rounded-xl border cursor-pointer transition-all text-xs">
                                    <input type="radio" name="theme" value="light" x-model="theme" class="hidden">
                                    <span>{{ __('hub.light_mode') }}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-400 mb-2">{{ __('hub.screen_position') }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label :class="position === 'bottom-right' ? 'bg-indigo-500/20 border-indigo-500 text-white font-bold' : 'bg-slate-900/60 border-white/10 text-slate-400'" class="flex items-center justify-center gap-2 p-3 rounded-xl border cursor-pointer transition-all text-xs">
                                    <input type="radio" name="position" value="bottom-right" x-model="position" class="hidden">
                                    <span>{{ __('hub.bottom_right') }}</span>
                                </label>
                                <label :class="position === 'bottom-left' ? 'bg-indigo-500/20 border-indigo-500 text-white font-bold' : 'bg-slate-900/60 border-white/10 text-slate-400'" class="flex items-center justify-center gap-2 p-3 rounded-xl border cursor-pointer transition-all text-xs">
                                    <input type="radio" name="position" value="bottom-left" x-model="position" class="hidden">
                                    <span>{{ __('hub.bottom_left') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messaging & Content -->
                <div class="glass p-6 rounded-2xl border border-white/5 space-y-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>{{ __('hub.greeting_and_questions') }}</span>
                    </h3>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">{{ __('hub.welcome_greeting') }}</label>
                        <textarea name="greeting_message" x-model="greeting" rows="2" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500" required></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">{{ __('hub.input_placeholder') }}</label>
                        <input type="text" name="placeholder_text" x-model="placeholder" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <!-- Suggested Starter Chips -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase text-slate-400">{{ __('hub.starter_questions') }}</label>
                            <button type="button" @click="addQuestion()" class="text-xs text-indigo-400 hover:text-indigo-300 font-bold uppercase tracking-wider">{{ __('hub.add_chip') }}</button>
                        </div>
                        
                        <div class="space-y-2">
                            <template x-for="(q, idx) in questions" :key="idx">
                                <div class="flex items-center gap-2">
                                    <input type="text" :name="'suggested_questions[' + idx + ']'" x-model="questions[idx]" :placeholder="questionPlaceholder" class="flex-1 bg-slate-900 border border-white/10 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                                    <button type="button" @click="removeQuestion(idx)" class="p-2 text-rose-400 hover:bg-rose-500/10 rounded-lg">✕</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Security & Allowed Domains -->
                <div class="glass p-6 rounded-2xl border border-white/5 space-y-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>{{ __('hub.security_allowed_domains') }}</span>
                    </h3>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">{{ __('hub.authorized_domains') }}</label>
                        <input type="text" name="allowed_domains" value="{{ implode(', ', $settings->allowed_domains ?? ['*']) }}" placeholder="* or example.com, myapp.com" dir="ltr" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 font-mono">
                        <span class="text-[11px] text-slate-500 mt-1 block">{{ __('hub.domains_wildcard_hint') }}</span>
                    </div>
                </div>

                <button type="submit" class="w-full py-4 accent-gradient text-white rounded-xl font-bold uppercase tracking-widest text-sm shadow-xl shadow-indigo-500/25 hover:scale-[1.01] active:scale-95 transition-all">
                    {{ __('hub.save_widget_config') }}
                </button>
            </form>
        </div>

        <!-- Live Interactive Preview Column -->
        <div class="lg:col-span-5 space-y-4">
            <div class="sticky top-24 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>{{ __('hub.interactive_live_preview') }}</span>
                    </h3>
                    <span class="text-xs text-slate-400">{{ __('hub.updates_live_as_type') }}</span>
                </div>

                <!-- Mock Browser Window -->
                <div class="w-full h-[640px] rounded-2xl overflow-hidden border border-white/10 shadow-2xl flex flex-col"
                     :class="theme === 'dark' ? 'bg-[#0a0f1d]' : 'bg-slate-100'">
                    
                    <!-- Browser Top Bar -->
                    <div class="h-10 px-4 flex items-center gap-2 border-b" dir="ltr"
                         :class="theme === 'dark' ? 'bg-slate-900 border-white/10 text-slate-400' : 'bg-slate-200 border-slate-300 text-slate-600'">
                        <div class="flex gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-rose-500/80"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500/80"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500/80"></span>
                        </div>
                        <div class="flex-1 text-center font-mono text-[11px] truncate opacity-70">
                            https://your-website.com
                        </div>
                    </div>

                    <!-- Mock Website Body -->
                    <div class="flex-1 p-6 relative overflow-hidden flex flex-col justify-between">
                        <!-- Mock Website Content -->
                        <div class="space-y-4 opacity-30 select-none pointer-events-none">
                            <div class="h-6 w-1/3 rounded bg-slate-500"></div>
                            <div class="space-y-2">
                                <div class="h-3 w-full rounded bg-slate-500/60"></div>
                                <div class="h-3 w-4/5 rounded bg-slate-500/60"></div>
                                <div class="h-3 w-2/3 rounded bg-slate-500/60"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <div class="h-20 rounded-xl bg-slate-500/30"></div>
                                <div class="h-20 rounded-xl bg-slate-500/30"></div>
                            </div>
                        </div>

                        <!-- THE PREVIEW WIDGET (Styled with Alpine reactive variables) -->
                        <div class="absolute inset-0 pointer-events-none p-4 flex flex-col justify-end"
                             :class="position === 'bottom-left' ? 'items-start' : 'items-end'">
                            
                            <!-- Widget Chat Box -->
                            <div x-show="previewOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="w-full max-w-[340px] h-[480px] rounded-2xl shadow-2xl flex flex-col overflow-hidden mb-3 border pointer-events-auto"
                                 :class="theme === 'dark' ? 'bg-[#0f172a] text-white border-white/10' : 'bg-white text-slate-900 border-slate-200'">
                                 
                                <!-- Header -->
                                <div class="p-4 text-white flex items-center justify-between shadow" :style="'background:' + primaryColor">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold">🤖</div>
                                        <div>
                                            <h4 class="text-xs font-bold" x-text="botName"></h4>
                                            <span class="text-[10px] opacity-80 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span> {{ __('hub.active_now') }}
                                            </span>
                                        </div>
                                    </div>
                                    <button type="button" @click="previewOpen = false" class="text-white/80 hover:text-white text-sm">✕</button>
                                </div>

                                <!-- Message List -->
                                <div class="flex-1 p-3.5 overflow-y-auto space-y-3 text-xs" :class="theme === 'dark' ? 'bg-[#080d1a]' : 'bg-slate-50'">
                                    <!-- Bot Greeting -->
                                    <div class="flex flex-col items-start max-w-[85%]">
                                        <div class="p-2.5 rounded-2xl rounded-tl-none shadow-sm" :class="theme === 'dark' ? 'bg-[#1e293b] text-white border border-white/5' : 'bg-white text-slate-800 border border-slate-200'" x-text="greeting"></div>
                                        <span class="text-[9px] text-slate-400 mt-1 ml-1">{{ __('hub.just_now') }}</span>
                                    </div>

                                    <!-- Suggested Chips -->
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        <template x-for="q in questions" :key="q">
                                            <button type="button" @click="mockAsk(q)" class="px-2.5 py-1 rounded-full text-[11px] border transition-colors text-left"
                                                    :style="'color:' + primaryColor + '; border-color:' + primaryColor + '40; background:' + primaryColor + '10'"
                                                    x-text="q"></button>
                                        </template>
                                    </div>

                                    <!-- Mock Sent Messages -->
                                    <template x-for="(m, i) in mockMessages" :key="i">
                                        <div class="flex flex-col" :class="m.role === 'user' ? 'items-end' : 'items-start'">
                                            <div class="p-2.5 rounded-2xl max-w-[85%]" 
                                                 :class="m.role === 'user' ? 'text-white rounded-tr-none' : (theme === 'dark' ? 'bg-[#1e293b] text-white rounded-tl-none border border-white/5' : 'bg-white text-slate-800 rounded-tl-none border border-slate-200')"
                                                 :style="m.role === 'user' ? 'background:' + primaryColor : ''"
                                                 x-text="m.text"></div>
                                            <span class="text-[9px] text-slate-400 mt-1 px-1">{{ __('hub.just_now') }}</span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Input Bar -->
                                <div class="p-2.5 flex items-center gap-2 border-t" :class="theme === 'dark' ? 'bg-[#1e293b] border-white/10' : 'bg-white border-slate-200'">
                                    <input type="text" x-model="mockInput" @keydown.enter="sendMock()" :placeholder="placeholder" class="flex-1 bg-transparent text-xs px-3 py-1.5 rounded-full border focus:outline-none" :class="theme === 'dark' ? 'border-white/10 text-white' : 'border-slate-300 text-slate-900'">
                                    <button type="button" @click="sendMock()" class="w-7 h-7 rounded-full text-white flex items-center justify-center shrink-0" :style="'background:' + primaryColor">
                                        <svg class="w-3.5 h-3.5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Launcher Button -->
                            <button type="button" @click="previewOpen = !previewOpen" 
                                    class="w-14 h-14 rounded-full shadow-2xl flex items-center justify-center text-white pointer-events-auto hover:scale-105 active:scale-95 transition-transform"
                                    :style="'background:' + primaryColor">
                                <template x-if="!previewOpen">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                                </template>
                                <template x-if="previewOpen">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function widgetCustomizer(initial) {
    return {
        primaryColor: initial.primaryColor,
        botName: initial.botName,
        bubbleTitle: initial.bubbleTitle,
        greeting: initial.greeting,
        placeholder: initial.placeholder,
        theme: initial.theme,
        position: initial.position,
        questions: initial.questions || [],
        apiKey: initial.apiKey,
        cdnUrl: initial.cdnUrl,
        copiedText: initial.copiedText,
        copyText: initial.copyText,
        mockReply: initial.mockReply,
        questionPlaceholder: initial.questionPlaceholder,
        copied: false,
        previewOpen: true,
        mockInput: '',
        mockMessages: [],

        get snippet() {
            return `<script src="${this.cdnUrl}" data-api-key="${this.apiKey}"><\/script>`;
        },

        copySnippet() {
            navigator.clipboard.writeText(this.snippet);
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        },

        addQuestion() {
            this.questions.push('');
        },

        removeQuestion(index) {
            this.questions.splice(index, 1);
        },

        mockAsk(text) {
            this.mockMessages.push({ role: 'user', text });
            setTimeout(() => {
                this.mockMessages.push({
                    role: 'bot',
                    text: this.mockReply
                });
            }, 500);
        },

        sendMock() {
            if (!this.mockInput.trim()) return;
            const text = this.mockInput.trim();
            this.mockInput = '';
            this.mockAsk(text);
        }
    };
}
</script>
@endsection
