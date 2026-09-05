<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - RAG Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.3); }
        .glass-dark { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(16px); border-right: 1px solid rgba(255, 255, 255, 0.05); }
        
        .accent-gradient { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
        .accent-gradient-text { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        /* Smooth Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .sidebar-link { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-link:hover { transform: translateX(6px); background-color: rgba(255,255,255,0.05); }
        .sidebar-link.active { background: linear-gradient(90deg, rgba(79, 70, 229, 0.15) 0%, transparent 100%); border-left: 3px solid #8b5cf6; color: #fff; transform: translateX(4px); }
        .sidebar-link.active svg { color: #a78bfa; }

        .fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; }
        
        /* Modern Cards */
        .card-neo { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05), inset 0 1px 0 rgba(255,255,255,0.8); }
    </style>
</head>
<body class="text-slate-800 antialiased" x-data="{ sidebarOpen: false, profileDropdown: false }">

    <div class="flex h-screen overflow-hidden bg-slate-50">
        
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 lg:w-72 glass-dark text-slate-300 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:flex-shrink-0 flex flex-col">
            <!-- Logo -->
            <div class="h-20 flex items-center px-8 border-b border-white/5">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 accent-gradient rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-white uppercase">Rag<span class="text-indigo-400">Hub</span></span>
                </div>
                <!-- Close Mobile -->
                <button @click="sidebarOpen = false" class="ml-auto text-slate-400 hover:text-white lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation Categories -->
            <div class="flex-1 overflow-y-auto py-6 px-4 space-y-8">
                <div>
                    <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Overview</p>
                    <nav class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-300 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.tenants') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-300 {{ request()->routeIs('admin.tenants*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Tenants Management
                        </a>
                        <a href="{{ route('admin.api-keys') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-300 {{ request()->routeIs('admin.api-keys') ? 'active' : '' }}">
                            <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            Platform API Keys
                        </a>
                    </nav>
                </div>

                <div>
                    <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Operations & Data</p>
                    <nav class="space-y-1">
                        <a href="{{ route('admin.knowledge-bases') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-300 {{ request()->routeIs('admin.knowledge-bases') ? 'active' : '' }}">
                            <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            Knowledge Bases
                        </a>
                        <a href="{{ route('admin.human-requests') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-300 {{ request()->routeIs('admin.human-requests') ? 'active' : '' }}">
                            <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                            Human Requests
                        </a>
                        <a href="{{ route('admin.conversations') }}" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-300 {{ request()->routeIs('admin.conversations') ? 'active' : '' }}">
                            <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            Conversations
                        </a>
                    </nav>
                </div>
            </div>
            
            <div class="p-6 border-t border-white/5 text-center">
                <p class="text-xs text-slate-500">RAG Hub &copy; {{ date('Y') }}</p>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Navbar -->
            <header class="glass h-20 px-6 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-500 hover:text-indigo-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    </button>
                    <!-- Search or breadcrumb can go here -->
                    <h2 class="text-xl font-bold text-slate-800 hidden sm:block">@yield('page-title', 'Dashboard')</h2>
                </div>

                <div class="flex items-center gap-4 sm:gap-6">
                    <!-- Notifications -->
                    <button class="relative p-2 text-slate-400 hover:text-indigo-600 transition-colors rounded-full hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
                    </button>

                    <!-- Profile Dropdown -->
                    <div class="relative" @click.away="profileDropdown = false">
                        <button @click="profileDropdown = !profileDropdown" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-slate-800">{{ auth('admin')->user()->name ?? 'Admin' }}</p>
                                <p class="text-xs text-slate-500 font-medium">Super Admin</p>
                            </div>
                            <div class="w-10 h-10 rounded-full accent-gradient flex items-center justify-center text-white font-bold shadow-md shadow-indigo-500/20 ring-2 ring-white">
                                {{ substr(auth('admin')->user()->name, 0, 1) }}
                            </div>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="profileDropdown" x-transition.enter="transition ease-out duration-100" x-transition.enter-start="transform opacity-0 scale-95" x-transition.enter-end="transform opacity-100 scale-100" x-transition.leave="transition ease-in duration-75" x-transition.leave-start="transform opacity-100 scale-100" x-transition.leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-xl shadow-slate-200/50 border border-slate-100 py-1 overflow-hidden" style="display: none;">
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-3 text-sm text-rose-600 font-medium hover:bg-rose-50 flex items-center gap-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-8">
                <div class="max-w-7xl mx-auto space-y-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Toast Notifications (Alpine Component) -->
    <div x-data="{ toasts: [] }" 
         @notify.window="toasts.push({id: Date.now(), ...$event.detail}); setTimeout(() => { toasts = toasts.filter(t => t.id !== $event.detail.id) }, 4000)"
         class="fixed bottom-0 right-0 p-6 space-y-4 z-50 flex flex-col items-end pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true" x-transition.duration.300ms class="pointer-events-auto flex items-center gap-3 px-6 py-4 bg-white border border-slate-100 rounded-xl shadow-lg shadow-slate-200/50 min-w-[300px]">
                <div class="flex-shrink-0" :class="toast.type === 'success' ? 'text-emerald-500' : 'text-rose-500'">
                    <svg x-show="toast.type === 'success'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg x-show="toast.type === 'error'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800" x-text="toast.title"></p>
                    <p class="text-xs text-slate-500 font-medium" x-text="toast.message"></p>
                </div>
            </div>
        </template>
    </div>

    @stack('scripts')
</body>
</html>
