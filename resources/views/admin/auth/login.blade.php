<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('admin.auth.admin_panel') }} - RAG Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        @if(app()->getLocale() == 'ar')
            @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap');
            body { font-family: 'Cairo', 'Inter', sans-serif !important; }
        @endif

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-slide-up { animation: slideUp 0.5s ease-out; }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-neutral-50 via-indigo-50/30 to-purple-50/30 dark:from-neutral-950 dark:via-indigo-950/20 dark:to-purple-950/20 min-h-screen flex items-center justify-center p-4 relative">
    
    <!-- Language Switcher -->
    <div class="absolute top-6 {{ app()->getLocale() == 'ar' ? 'left-6' : 'right-6' }} flex items-center bg-white/50 dark:bg-neutral-900/50 backdrop-blur-md rounded-full px-1 py-1 border border-neutral-200 dark:border-neutral-800">
        <a href="{{ route('lang.set', 'en') }}" class="px-3 py-1 rounded-full text-xs font-bold uppercase transition-all {{ app()->getLocale() == 'en' ? 'gradient-bg text-white' : 'text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300' }}">EN</a>
        <a href="{{ route('lang.set', 'ar') }}" class="px-3 py-1 rounded-full text-xs font-bold uppercase transition-all {{ app()->getLocale() == 'ar' ? 'gradient-bg text-white' : 'text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300' }}">AR</a>
    </div>

    <div class="w-full max-w-md animate-slide-up">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 mb-4">
                <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">{{ __('admin.auth.admin_panel') }}</span>
            </div>
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">{{ __('admin.auth.welcome_back') }}</h1>
            <p class="text-neutral-600 dark:text-neutral-400">{{ __('admin.auth.login_manage') }}</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-xl border border-neutral-200/50 dark:border-neutral-800/50 rounded-2xl p-8 shadow-2xl">
            @if(session('message'))
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 rounded-lg flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-blue-600 dark:text-blue-400">{{ session('message') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-lg flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-600 dark:text-red-400">{{ session('error') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-lg">
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}">
                @csrf

                <!-- Email -->
                <div class="mb-5 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('admin.auth.email_address') }}
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-3 bg-white dark:bg-neutral-800 border-2 border-neutral-200 dark:border-neutral-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-neutral-900 dark:text-neutral-100 transition-all duration-200 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}"
                           placeholder="admin@raghub.com" dir="ltr">
                </div>

                <!-- Password -->
                <div class="mb-6 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('admin.auth.password') }}
                    </label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 bg-white dark:bg-neutral-800 border-2 border-neutral-200 dark:border-neutral-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-neutral-900 dark:text-neutral-100 transition-all duration-200 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}"
                           placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" dir="ltr">
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-indigo-600 border-neutral-300 rounded focus:ring-indigo-500 transition">
                        <span class="mx-2 text-sm text-neutral-700 dark:text-neutral-300">{{ __('admin.auth.remember_me') }}</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 gradient-bg text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                    {{ __('admin.auth.login_button') }}
                </button>
            </form>
        </div>
    </div>
</body>
</html>
