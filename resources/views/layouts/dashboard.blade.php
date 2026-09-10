<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @lang('hub.dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <style>body { font-family: 'Cairo', 'Outfit', sans-serif !important; }</style>
    @endif
    <style>
        :root {
            --bg-primary: #050810;
            --bg-secondary: #0c1220;
            --sidebar-width: 280px;
            --accent-primary: #6366f1;
            --accent-secondary: #a855f7;
        }
        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--bg-primary); 
            color: #f8fafc;
            overflow-x: hidden;
        }
        .glass { 
            background: rgba(12, 18, 32, 0.7); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.05); 
        }
        .sidebar-glass {
            background: rgba(8, 12, 24, 0.8);
            backdrop-filter: blur(20px);
            border-inline-end: 1px solid rgba(255, 255, 255, 0.05);
        }
        .accent-gradient { 
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%); 
        }
        .accent-text-gradient {
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -8px rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.3);
        }
        .nav-link-active {
            background: rgba(99, 102, 241, 0.1);
            color: white;
            border-inline-end: 3px solid var(--accent-primary);
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    </style>
</head>
<body x-data="{ sidebarOpen: true }">
    <!-- Sidebar -->
    <aside 
        class="fixed top-0 {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} h-screen z-50 transition-all duration-300 sidebar-glass flex flex-col"
        :style="sidebarOpen ? 'width: var(--sidebar-width)' : 'width: 80px'"
    >
        <!-- Logo -->
        <div class="h-20 flex items-center px-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 accent-gradient rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span x-show="sidebarOpen" x-transition class="text-xl font-bold tracking-tight uppercase">Rag<span class="text-indigo-400">Hub</span></span>
            </div>
        </div>

        <!-- Navigation -->
        <!-- Navigation -->
        <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto custom-scrollbar">
            <x-nav-item href="{{ route('tenant.dashboard') }}" label="{{ __('hub.overview') }}" icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" :active="request()->routeIs('tenant.dashboard')" expanded="sidebarOpen" />
            
            <x-nav-item href="{{ route('whatsapp.index') }}" label="WhatsApp Sessions" icon="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" :active="request()->routeIs('whatsapp.*')" expanded="sidebarOpen" />

            <x-nav-item href="{{ route('conversations.index') }}" label="{{ __('hub.messages') }}" icon="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" :active="request()->routeIs('conversations.*')" expanded="sidebarOpen" />

            <x-nav-item href="{{ route('widget.index') }}" label="{{ __('hub.chat_widget') }}" icon="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" :active="request()->routeIs('widget.*')" expanded="sidebarOpen" />

            <x-nav-item href="{{ route('knowledge.index') }}" label="{{ __('hub.knowledge_base') }}" icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" :active="request()->routeIs('knowledge.*')" expanded="sidebarOpen" />


            @if(auth('tenant')->user()->hasPermission('users.view'))
                <x-nav-item href="{{ route('tenant.users.index') }}" label="{{ __('hub.users') ?? 'Users' }}" icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" :active="request()->routeIs('tenant.users.*')" expanded="sidebarOpen" />
                <x-nav-item href="{{ route('roles.index') }}" label="{{ __('hub.roles') ?? 'Roles' }}" icon="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" :active="request()->routeIs('roles.*')" expanded="sidebarOpen" />
            @endif
        </nav>

        <!-- Bottom User -->
        <div class="p-4 border-t border-white/5 glass">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center border border-white/10 overflow-hidden">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth('tenant')->user()->name) }}&background=6366f1&color=fff" alt="">
                </div>
                <div x-show="sidebarOpen" x-transition class="flex-1 min-w-0">
                    <a href="{{ route('profile.index') }}" class="hover:underline">
                        <p class="text-sm font-semibold truncate">{{ auth('tenant')->user()->name }}</p>
                    </a>
                </div>
                <form method="POST" action="{{ route('logout') }}" x-show="sidebarOpen" x-transition>
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition-colors">
                        <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main 
        class="transition-all duration-300 min-h-screen flex flex-col"
        :style="sidebarOpen ? 'margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: var(--sidebar-width)' : 'margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 80px'"
    >
        <!-- Topbar -->
        <header class="h-20 glass sticky top-0 z-40 px-8 flex justify-between items-center border-b border-white/5">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-xl font-bold">@yield('title', __('hub.dashboard'))</h1>
            </div>

            <div class="flex items-center gap-6">
                <!-- Language Switcher -->
                <div class="flex items-center bg-slate-900/50 rounded-full px-1 py-1 border border-white/10">
                    <a href="{{ route('lang.set', 'en') }}" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase transition-all {{ app()->getLocale() == 'en' ? 'accent-gradient text-white font-black' : 'text-slate-500 hover:text-slate-300' }}">EN</a>
                    <a href="{{ route('lang.set', 'ar') }}" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase transition-all {{ app()->getLocale() == 'ar' ? 'accent-gradient text-white font-black' : 'text-slate-500 hover:text-slate-300' }}">AR</a>
                </div>

                <!-- Notifications -->
                <button class="relative p-2 text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-indigo-500 rounded-full border-2 border-[#050810]"></span>
                </button>

                <!-- Search -->
                <div class="hidden md:flex items-center gap-2 px-4 py-2 bg-slate-900/50 rounded-full border border-white/10 text-slate-400 group">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" placeholder="{{ __('Search...') }}" class="bg-transparent border-none focus:outline-none text-sm w-48">
                    <span class="text-[10px] bg-slate-800 px-1.5 py-0.5 rounded border border-white/10 uppercase font-mono">⌘K</span>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="flex-1 p-8 pb-12 overflow-x-hidden">
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @php
                        $msg = session('success');
                        $map = [
                            'Campaign created and processing started.' => 'campaigns.flash.created',
                            'Campaign paused.' => 'campaigns.flash.paused',
                            'Campaign resumed.' => 'campaigns.flash.resumed',
                            'Campaign cancelled.' => 'campaigns.flash.cancelled',
                            'Campaign deleted.' => 'campaigns.flash.deleted',
                            'Retrying failed messages...' => 'campaigns.flash.retrying',
                            'Campaign created successfully.' => 'campaigns.flash.created',
                        ];
                    @endphp
                    {{ Lang::has($map[$msg] ?? '') ? __($map[$msg]) : $msg }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl flex items-center justify-between gap-3 animate-in fade-in slide-in-from-top-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    @if(session('upgrade_required'))
                        <a href="{{ url('/dashboard/billing') }}" class="px-4 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-300 text-sm font-medium rounded-lg transition-colors border border-red-500/30 whitespace-nowrap">
                            {{ __('hub.upgrade_to_continue') ?? 'Upgrade Plan' }}
                        </a>
                    @endif
                </div>
            @endif

            @yield('content')
        </div>
        
        <!-- Footer -->
        <footer class="p-8 border-t border-white/5 text-slate-500 text-sm flex justify-between items-center bg-slate-950/20">
            <p>&copy; {{ date('Y') }} RagHub Multi-Tenant. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-indigo-400 transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-indigo-400 transition-colors">Terms of Service</a>
                <a href="#" class="hover:text-indigo-400 transition-colors">Support</a>
            </div>
        </footer>
    </main>

    <!-- Human Escalation Alert System -->
    <div x-data="escalationAlerts()" x-init="startPolling()" class="fixed bottom-6 {{ app()->getLocale() == 'ar' ? 'left-6' : 'right-6' }} z-[9999] space-y-3 max-w-md w-full pointer-events-none">
        <!-- Alert Toasts -->
        <template x-for="alert in visibleAlerts" :key="alert.id">
            <div 
                x-show="alert.show"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="pointer-events-auto bg-slate-900/95 backdrop-blur-2xl border border-red-500/40 rounded-2xl p-5 shadow-2xl shadow-red-500/20 group text-right"
                dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"
            >
                <!-- Alert Header -->
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 relative mt-0.5">
                        <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center border border-red-500/30">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-ping"></span>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-bold text-red-400">🚨 {{ __('hub.human_fallback') ?? 'تنبيه: تدخل بشري مطلوب' }}</p>
                            <span class="text-[10px] bg-red-500/20 text-red-300 px-2 py-0.5 rounded-full font-mono uppercase border border-red-500/30" x-text="alert.reason || 'RAG Low Score'"></span>
                        </div>
                        <p class="text-xs font-semibold text-slate-200 mt-1 truncate" x-text="alert.name"></p>
                        <p class="text-xs text-slate-400 mt-0.5 line-clamp-2 italic bg-slate-950/40 p-2 rounded-lg border border-white/5" x-text="'« ' + alert.last_message + ' »'"></p>
                    </div>
                </div>

                <!-- Preview of Fallback Message to send -->
                <div class="mt-3 p-2.5 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-200/90 leading-relaxed">
                    <span class="font-bold text-amber-300 block mb-0.5">💬 الرسالة المقترحة للإرسال:</span>
                    <span x-text="alert.language === 'ar' || !alert.language ? 'سؤالك محتاج تدخل بشري 👩‍💼، هنوصل لحضرتك قريبًا.' : 'Your inquiry requires human support 👩‍💼, an agent will assist you shortly.'"></span>
                </div>

                <!-- Action Options -->
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <!-- Option 1: Ignore Warning (Saved to DB) -->
                    <button 
                        @click="ignoreAlert(alert.id)"
                        :disabled="alert.loading"
                        class="px-3 py-2 bg-slate-800 hover:bg-slate-700 active:scale-[0.98] border border-slate-600/50 rounded-xl text-xs font-bold text-slate-300 transition-all flex items-center justify-center gap-1.5 shadow-sm disabled:opacity-50"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <span>{{ __('تجاهل التنبيه') ?? 'Ignore Warning' }}</span>
                    </button>

                    <!-- Option 2: Confirm & Send Message -->
                    <button 
                        @click="confirmAlert(alert.id)"
                        :disabled="alert.loading"
                        class="px-3 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-[0.98] rounded-xl text-xs font-bold text-white transition-all flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-600/20 disabled:opacity-50"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>{{ __('تأكيد وإرسال') ?? 'Confirm & Send' }}</span>
                    </button>
                </div>

                <!-- Secondary Link -->
                <div class="mt-2.5 text-center">
                    <a href="{{ route('conversations.index') }}" class="text-[11px] text-indigo-400 hover:text-indigo-300 underline font-medium transition-colors">
                        {{ __('فتح المحادثة والرد يدوياً') ?? 'Open Conversation & Reply Manually' }} &rarr;
                    </a>
                </div>
            </div>
        </template>
    </div>

    <!-- Topbar Bell Badge (reactive) -->
    <div x-data="bellBadge()" x-init="start()" id="bell-badge-root">
        <!-- This syncs the bell icon badge count -->
    </div>

    <script>
        // CSRF Token helper
        const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Web Audio API — generates alert tone without external files
        function playAlertSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();

                // Triple-beep pattern
                [0, 0.25, 0.5].forEach(delay => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, ctx.currentTime + delay);
                    osc.frequency.setValueAtTime(1100, ctx.currentTime + delay + 0.08);

                    gain.gain.setValueAtTime(0, ctx.currentTime + delay);
                    gain.gain.linearRampToValueAtTime(0.3, ctx.currentTime + delay + 0.02);
                    gain.gain.linearRampToValueAtTime(0, ctx.currentTime + delay + 0.15);

                    osc.start(ctx.currentTime + delay);
                    osc.stop(ctx.currentTime + delay + 0.2);
                });
            } catch (e) {
                console.warn('Audio alert unavailable:', e);
            }
        }

        function escalationAlerts() {
            return {
                visibleAlerts: [],
                knownIds: new Set(),
                lastCheck: new Date().toISOString(),
                pollInterval: null,

                startPolling() {
                    this.poll();
                    this.pollInterval = setInterval(() => this.poll(), 8000);
                },

                async poll() {
                    try {
                        const res = await fetch(`/api/pending-alerts?since=${encodeURIComponent(this.lastCheck)}`);
                        if (!res.ok) return;
                        const data = await res.json();

                        if (data.requests && data.requests.length > 0) {
                            let hasNew = false;
                            data.requests.forEach(req => {
                                if (!this.knownIds.has(req.id)) {
                                    this.knownIds.add(req.id);
                                    hasNew = true;
                                    this.visibleAlerts.unshift({ ...req, show: true, loading: false });
                                }
                            });

                            if (hasNew) {
                                playAlertSound();
                                this.updateBellBadge(data.count);
                            }
                        }

                        this.lastCheck = data.server_time || new Date().toISOString();

                        if (this.visibleAlerts.length > 5) {
                            this.visibleAlerts = this.visibleAlerts.slice(0, 5);
                        }
                    } catch (e) {}
                },

                async ignoreAlert(id) {
                    const alert = this.visibleAlerts.find(a => a.id === id);
                    if (alert) alert.loading = true;

                    try {
                        const res = await fetch(`/api/pending-alerts/${id}/ignore`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            }
                        });

                        if (res.ok) {
                            this.dismissAlert(id);
                            this.fetchTotalCount();
                        }
                    } catch (e) {
                        console.error('Failed to ignore alert:', e);
                    } finally {
                        if (alert) alert.loading = false;
                    }
                },

                async confirmAlert(id) {
                    const alert = this.visibleAlerts.find(a => a.id === id);
                    if (alert) alert.loading = true;

                    try {
                        const res = await fetch(`/api/pending-alerts/${id}/confirm`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            }
                        });

                        if (res.ok) {
                            this.dismissAlert(id);
                            this.fetchTotalCount();
                        }
                    } catch (e) {
                        console.error('Failed to confirm alert:', e);
                    } finally {
                        if (alert) alert.loading = false;
                    }
                },

                dismissAlert(id) {
                    const alert = this.visibleAlerts.find(a => a.id === id);
                    if (alert) alert.show = false;
                    setTimeout(() => {
                        this.visibleAlerts = this.visibleAlerts.filter(a => a.id !== id);
                    }, 350);
                },

                async fetchTotalCount() {
                    try {
                        const res = await fetch('/api/pending-alerts');
                        if (!res.ok) return;
                        const data = await res.json();
                        this.updateBellBadge(data.count);
                    } catch (e) {}
                },

                updateBellBadge(count) {
                    const badge = document.getElementById('escalation-badge');
                    if (badge) {
                        badge.textContent = count;
                        badge.style.display = count > 0 ? 'flex' : 'none';
                    }
                }
            };
        }

        function bellBadge() {
            return {
                count: 0,
                start() {
                    this.fetchCount();
                    setInterval(() => this.fetchCount(), 15000);
                },
                async fetchCount() {
                    try {
                        const res = await fetch('/api/pending-alerts');
                        if (!res.ok) return;
                        const data = await res.json();
                        this.count = data.count;
                        const badge = document.getElementById('escalation-badge');
                        if (badge) {
                            badge.textContent = this.count;
                            badge.style.display = this.count > 0 ? 'flex' : 'none';
                        }
                    } catch (e) {}
                }
            };
        }
    </script>
</body>
</html>
