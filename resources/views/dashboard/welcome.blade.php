<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RAG Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-neutral-50 dark:bg-neutral-950">
    <!-- Top Navigation -->
    <nav class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-neutral-900 dark:text-neutral-100">RAG Hub</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ $tenant->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Welcome Message -->
        @if(session('success'))
            <div class="mb-8 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-600 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        <div class="mb-12">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">
                Welcome, {{ $tenant->name }}! 👋
            </h1>
            <p class="text-neutral-600 dark:text-neutral-400">
                Get started by completing the setup checklist below
            </p>
        </div>

        <!-- Setup Checklist -->
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-8 mb-8">
            <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 mb-6">Setup Checklist</h2>
            <div class="space-y-4">
                <!-- Step 1 -->
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        @if($setupChecklist['knowledge_uploaded'])
                            <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        @else
                            <div class="w-6 h-6 bg-neutral-200 dark:bg-neutral-700 rounded-full"></div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">Upload Knowledge Base</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Add documents, PDFs, and audio files to train your AI</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        @if($setupChecklist['api_key_generated'])
                            <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        @else
                            <div class="w-6 h-6 bg-neutral-200 dark:bg-neutral-700 rounded-full"></div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">API Key Generated</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Your widget key: <code class="bg-neutral-100 dark:bg-neutral-800 px-2 py-1 rounded text-xs">{{ $tenant->widget_key }}</code></p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        @if($setupChecklist['whatsapp_connected'])
                            <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        @else
                            <div class="w-6 h-6 bg-neutral-200 dark:bg-neutral-700 rounded-full"></div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">Connect WhatsApp</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Link your WhatsApp number to start automating</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">Knowledge Sources</h3>
                </div>
                <p class="text-3xl font-bold text-neutral-900 dark:text-neutral-100">{{ $tenant->knowledgeSources()->count() }}</p>
            </div>

            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">Conversations</h3>
                </div>
                <p class="text-3xl font-bold text-neutral-900 dark:text-neutral-100">{{ $tenant->conversations()->count() }}</p>
            </div>

            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">WhatsApp Instances</h3>
                </div>
                <p class="text-3xl font-bold text-neutral-900 dark:text-neutral-100">{{ $tenant->whatsappInstances()->count() }}</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-8">
            <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 mb-6">Quick Actions</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <a href="{{ route('knowledge.index') }}" class="p-4 border border-neutral-200 dark:border-neutral-700 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-500 transition">
                    <h3 class="font-semibold text-neutral-900 dark:text-neutral-100 mb-1">Manage Knowledge Base</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Upload and manage your documents</p>
                </a>
                <a href="{{ route('whatsapp.index') }}" class="p-4 border border-neutral-200 dark:border-neutral-700 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-500 transition">
                    <h3 class="font-semibold text-neutral-900 dark:text-neutral-100 mb-1">WhatsApp Settings</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Connect and configure WhatsApp</p>
                </a>
                <a href="{{ route('knowledge.index') }}" class="p-4 border border-neutral-200 dark:border-neutral-700 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-500 transition">
                    <h3 class="font-semibold text-neutral-900 dark:text-neutral-100 mb-1">View Conversations</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Monitor AI conversations</p>
                </a>
                <a href="{{ route('settings.index') }}" class="p-4 border border-neutral-200 dark:border-neutral-700 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-500 transition">
                    <h3 class="font-semibold text-neutral-900 dark:text-neutral-100 mb-1">Widget Settings</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Customize your chat widget</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
