<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('hub.landing_title') ?? 'RAG Hub | AI-Powered WhatsApp Gateway' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        @if(app()->getLocale() == 'ar')
            @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap');
        @endif

        body {
            font-family: 'Plus Jakarta Sans', {{ app()->getLocale() == 'ar' ? "'Cairo'," : "" }} sans-serif;
            background-color: #090d16;
            color: #f1f5f9;
        }

        .glass-nav {
            background: rgba(9, 13, 22, 0.85);
            backdrop-filter: blur(12px);
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .gradient-text {
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col justify-between">

    <!-- Simple Navigation -->
    <nav class="sticky top-0 z-50 glass-nav border-b border-white/5 py-4">
        <div class="max-w-6xl mx-auto px-6 flex items-center justify-between">
            <!-- Brand -->
            <a href="#" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black shadow-md shadow-indigo-600/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-white">RAG<span class="text-indigo-400">HUB</span></span>
            </a>

            <!-- Right Controls -->
            <div class="flex items-center gap-4">
                <!-- Language Switcher -->
                <div class="flex items-center bg-slate-900 rounded-lg p-1 border border-white/10 text-xs">
                    <a href="{{ route('lang.set', 'en') }}" class="px-2.5 py-1 rounded-md font-bold {{ app()->getLocale() == 'en' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white' }}">EN</a>
                    <a href="{{ route('lang.set', 'ar') }}" class="px-2.5 py-1 rounded-md font-bold {{ app()->getLocale() == 'ar' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white' }}">AR</a>
                </div>

                @auth('tenant')
                    <a href="{{ route('tenant.dashboard') }}" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition-all">
                        @lang('hub.dashboard')
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-white px-2 py-1">@lang('hub.login')</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition-all">
                        @lang('hub.get_started')
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Clean Hero Section -->
    <header class="py-20 lg:py-28 relative">
        <div class="max-w-4xl mx-auto px-6 text-center space-y-6">
            
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                WhatsApp Gateway & Document RAG
            </div>

            <h1 class="text-4xl sm:text-6xl font-extrabold text-white tracking-tight leading-tight">
                Automate WhatsApp Customer Support with <span class="gradient-text">RAG Intelligence</span>
            </h1>

            <p class="text-base sm:text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Connect your WhatsApp number, upload your business documents, and let AI answer customer questions 24/7 with zero hallucinations.
            </p>

            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth('tenant')
                    <a href="{{ route('tenant.dashboard') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-base transition-all shadow-lg shadow-indigo-600/20">
                        Go to Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-base transition-all shadow-lg shadow-indigo-600/20">
                        @lang('hub.start_free_trial') &rarr;
                    </a>
                    <a href="{{ route('login') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl glass-panel hover:bg-white/5 text-slate-200 font-semibold text-base transition-all">
                        @lang('hub.login')
                    </a>
                @endauth
            </div>

            <!-- Minimal Preview Mockup -->
            <div class="pt-10">
                <div class="glass-panel rounded-2xl p-6 text-left max-w-2xl mx-auto border border-white/10 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-white/10 pb-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-slate-200">Live WhatsApp Session</span>
                        </div>
                        <span class="text-slate-400 font-mono text-[11px]">Context Mode: Active</span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="bg-slate-900/80 p-3 rounded-xl border border-white/5 max-w-[85%]">
                            <p class="text-slate-400 text-[10px] mb-0.5">User:</p>
                            <p class="text-slate-200">What is the return policy for damaged items?</p>
                        </div>
                        <div class="bg-indigo-950/40 p-3.5 rounded-xl border border-indigo-500/20 max-w-[85%] {{ app()->getLocale() == 'ar' ? 'mr-auto' : 'ml-auto' }} space-y-1">
                            <p class="text-indigo-300 text-[10px] font-semibold">RAG Bot (99.4% Match):</p>
                            <p class="text-slate-100">Damaged items can be returned within 14 days for a full refund. (Source: Returns_Guide.pdf)</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </header>

    <!-- Simplified Features (3 Cards) -->
    <section class="py-16 border-t border-white/5 bg-slate-950/40">
        <div class="max-w-5xl mx-auto px-6">
            
            <div class="text-center max-w-xl mx-auto space-y-2 mb-12">
                <h2 class="text-2xl font-bold text-white tracking-tight">Core Platform Features</h2>
                <p class="text-slate-400 text-sm">Everything required for automated WhatsApp AI customer support.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <div class="glass-panel p-6 rounded-2xl space-y-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-white">WhatsApp Integration</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Link your WhatsApp instance in seconds via QR code. Automated inbound message listener and instant replies.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-panel p-6 rounded-2xl space-y-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Knowledge RAG Engine</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Upload PDFs and documents. High-precision vector chunk matching ensures zero hallucination context replies.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-panel p-6 rounded-2xl space-y-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Human Fallback Handoff</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Automatically transfer low-confidence queries to human support operators directly in your dashboard.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- Simple Steps -->
    <section class="py-16 border-t border-white/5">
        <div class="max-w-4xl mx-auto px-6">
            <div class="grid sm:grid-cols-3 gap-8 text-center">
                <div class="space-y-2">
                    <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Step 1</span>
                    <h4 class="text-base font-bold text-white">Connect WhatsApp</h4>
                    <p class="text-xs text-slate-400">Scan QR code to link your number.</p>
                </div>
                <div class="space-y-2">
                    <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Step 2</span>
                    <h4 class="text-base font-bold text-white">Upload Documents</h4>
                    <p class="text-xs text-slate-400">Train the AI with your company files.</p>
                </div>
                <div class="space-y-2">
                    <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Step 3</span>
                    <h4 class="text-base font-bold text-white">Automate Support</h4>
                    <p class="text-xs text-slate-400">AI responds to customer queries 24/7.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Clean Footer -->
    <footer class="border-t border-white/5 py-8 text-xs text-slate-500">
        <div class="max-w-6xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; 2026 RAG HUB. All rights reserved.</p>
            <div class="flex items-center gap-6">
                <a href="{{ route('privacy') }}" class="hover:text-slate-300 transition-colors">@lang('hub.privacy')</a>
                <a href="{{ route('terms') }}" class="hover:text-slate-300 transition-colors">@lang('hub.terms')</a>
            </div>
        </div>
    </footer>

</body>
</html>
