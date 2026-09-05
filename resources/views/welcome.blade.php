<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RagHub - Multi-Tenant RAG</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');
        body { font-family: 'Outfit', sans-serif; background: #0f172a; color: #f8fafc; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .accent-gradient { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full glass p-8 rounded-3xl border border-white/10 shadow-2xl">
        <div class="text-center mb-10">
            <div class="w-16 h-16 accent-gradient rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20 mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h1 class="text-4xl font-bold tracking-tight uppercase">Rag<span class="text-indigo-400">Hub</span></h1>
            <p class="text-slate-400 mt-2">Production-Ready Multi-Tenant RAG</p>
        </div>

        <div class="space-y-4">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest text-center mb-6">Select Demo Tenant to Continue</p>
            
            <a href="{{ route('demo-login', 1) }}" class="group block p-4 bg-slate-800/50 hover:bg-slate-800 rounded-2xl border border-white/5 hover:border-indigo-500/50 transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold">1</div>
                        <div>
                            <h3 class="font-bold">Tenant One</h3>
                            <p class="text-xs text-slate-500">Industry: Tech/SaaS</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-slate-600 group-hover:text-indigo-400 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <a href="{{ route('demo-login', 2) }}" class="group block p-4 bg-slate-800/50 hover:bg-slate-800 rounded-2xl border border-white/5 hover:border-orange-500/50 transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center text-orange-400 font-bold">2</div>
                        <div>
                            <h3 class="font-bold">Tenant Two</h3>
                            <p class="text-xs text-slate-500">Industry: E-commerce</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-slate-600 group-hover:text-orange-400 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
        </div>

        <div class="mt-10 pt-8 border-t border-white/5 text-center">
            <p class="text-xs text-slate-600 font-medium">Laravel 12 + Qdrant + Ollama</p>
        </div>
    </div>
</body>
</html>
