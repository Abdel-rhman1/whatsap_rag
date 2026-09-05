<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RagHub Admin - Central Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <style>body { font-family: 'Cairo', 'Outfit', sans-serif !important; }</style>
    @endif
    <style>
        :root {
            --bg-primary: #080a0f;
            --bg-secondary: #0f121a;
            --sidebar-width: 280px;
            --accent-primary: #ef4444; /* Admin Crimson */
            --accent-secondary: #f97316;
        }
        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--bg-primary); 
            color: #f8fafc;
        }
        .glass { background: rgba(15, 18, 26, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .sidebar-glass { background: rgba(10, 12, 18, 0.9); backdrop-filter: blur(20px); border-inline-end: 1px solid rgba(255, 255, 255, 0.05); }
        .accent-gradient { background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%); }
        .nav-link-active { background: rgba(239, 68, 68, 0.1); color: white; border-inline-end: 3px solid var(--accent-primary); }
    </style>
</head>
<body x-data="{ sidebarOpen: true }">
    <!-- Sidebar -->
    <aside 
        class="fixed top-0 {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} h-screen z-50 transition-all duration-300 sidebar-glass flex flex-col"
        :style="sidebarOpen ? 'width: var(--sidebar-width)' : 'width: 80px'"
    >
        <div class="h-20 flex items-center px-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 accent-gradient rounded-xl flex items-center justify-center shadow-lg shadow-red-500/20 flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <span x-show="sidebarOpen" x-transition class="text-xl font-bold tracking-tight uppercase tracking-tighter">{{ __('admin.layout.admin_node') }}</span>
            </div>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
            <x-nav-item href="{{ route('admin.dashboard') }}" label="{{ __('admin.dashboard.global_system_overview') }}" icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" :active="request()->routeIs('admin.dashboard')" expanded="sidebarOpen" />
            
            <x-nav-item href="{{ route('admin.tenants') }}" label="{{ __('admin.tenants.tenant_management') }}" icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" :active="request()->routeIs('admin.tenants.*')" expanded="sidebarOpen" />
            
            <x-nav-item href="{{ route('admin.plans') }}" label="{{ __('admin.plans.plan_management') }}" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" :active="request()->routeIs('admin.plans.*')" expanded="sidebarOpen" />
            
            
            
            <x-nav-item href="{{ route('admin.campaigns') }}" label="{{ __('admin.campaigns.global_campaigns') }}" icon="M11 5.882V19.247A7.447 7.447 0 0011 15.75V5.882m0 0a8.997 8.997 0 013.524 3.352M11 5.882A8.997 8.997 0 007.476 9.234M11 19.247a8.997 8.997 0 01-3.524-3.352M11 19.247a8.997 8.997 0 003.524-3.352m0 0A8.997 8.997 0 0118 12.75A8.997 8.997 0 0114.524 9.234m-7.048 0c1.131-1.307 2.73-2.134 4.524-2.134m0 0c-1.103 0-2.003.897-2.003 2s.9 2 2.003 2m0 0c1.103 0 2.003-.897 2.003-2s-.9-2-2.003-2" :active="request()->routeIs('admin.campaigns.*')" expanded="sidebarOpen" />

            <x-nav-item href="{{ route('admin.orders.index') }}" label="{{ __('orders.title') }}" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" :active="request()->routeIs('admin.orders.*')" expanded="sidebarOpen" />

            <x-nav-item href="{{ route('admin.users.index') }}" label="{{ __('hub.user_management') ?? 'User Management' }}" icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" :active="request()->routeIs('admin.users.*')" expanded="sidebarOpen" />

            <x-nav-item href="{{ route('admin.logs') }}" label="{{ __('hub.audit_logs') }}" icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" :active="request()->routeIs('admin.logs.*')" expanded="sidebarOpen" />

            <div class="pt-6 my-2 border-t border-white/5 mx-2"></div>

            <x-nav-item href="{{ route('admin.settings') }}" label="{{ __('hub.system_settings') }}" icon="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" :active="request()->routeIs('admin.settings.*')" expanded="sidebarOpen" />
        </nav>

        <div class="p-4 border-t border-white/5 glass">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-500/10 flex items-center justify-center border border-red-500/20">
                    <span class="text-sm font-bold text-red-500">AD</span>
                </div>
                <div x-show="sidebarOpen" x-transition class="flex-1 min-w-0">
                    <a href="{{ route('admin.profile.index') }}" class="hover:underline">
                        <p class="text-sm font-semibold truncate">{{ auth()->guard('admin')->user()->name }}</p>
                    </a>
                    <p class="text-[10px] text-red-400 font-bold uppercase tracking-wider">{{ __('admin.layout.super_admin') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}" x-show="sidebarOpen" x-transition>
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="transition-all duration-300 min-h-screen flex flex-col" :style="sidebarOpen ? 'margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: var(--sidebar-width)' : 'margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 80px'">
        <header class="h-20 glass sticky top-0 z-40 px-8 flex justify-between items-center border-b border-white/5">
            <h1 class="text-xl font-bold">@yield('title')</h1>
            <div class="flex items-center gap-6">
                <!-- Language Switcher -->
                <div class="flex items-center bg-slate-900/50 rounded-full px-1 py-1 border border-white/10">
                    <a href="{{ route('lang.set', 'en') }}" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase transition-all {{ app()->getLocale() == 'en' ? 'accent-gradient text-white font-black' : 'text-slate-500 hover:text-slate-300' }}">EN</a>
                    <a href="{{ route('lang.set', 'ar') }}" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase transition-all {{ app()->getLocale() == 'ar' ? 'accent-gradient text-white font-black' : 'text-slate-500 hover:text-slate-300' }}">AR</a>
                </div>
                
                 <span class="px-3 py-1 bg-red-500/10 text-red-500 rounded-full text-[10px] font-bold border border-red-500/20">{{ __('admin.layout.system_live') }}</span>
            </div>
        </header>

        <div class="flex-1 p-8 pb-12">
             @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @php
                        $msg = session('success');
                        $adminMap = [
                            'Campaign paused by admin.' => 'campaigns.flash.paused',
                            'Campaign resumed by admin.' => 'campaigns.flash.resumed',
                            'Campaign cancelled by admin.' => 'campaigns.flash.cancelled',
                            'Retrying failed messages...' => 'campaigns.flash.retrying',
                        ];
                    @endphp
                    {{ Lang::has($adminMap[$msg] ?? '') ? __($adminMap[$msg]) : $msg }}
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>
