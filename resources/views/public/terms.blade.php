<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - RAG Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100">
    <!-- Navigation -->
    <nav class="border-b border-neutral-200 dark:border-neutral-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold">RAG Hub</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-4xl font-bold mb-8">Terms of Service</h1>
        
        <div class="prose dark:prose-invert max-w-none">
            <p class="text-neutral-600 dark:text-neutral-400 mb-6">Last updated: February 26, 2026</p>

            <h2 class="text-2xl font-bold mt-8 mb-4">1. Acceptance of Terms</h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                By accessing and using RAG Hub, you accept and agree to be bound by the terms and provision of this agreement.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">2. Use License</h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                Permission is granted to use RAG Hub for personal or commercial purposes. This license shall automatically terminate if you violate any of these restrictions.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">3. Service Availability</h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                We strive to provide uninterrupted service but do not guarantee that the service will be available at all times. We may suspend or discontinue any part of the service at any time.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">4. Limitation of Liability</h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                RAG Hub shall not be liable for any damages arising from the use or inability to use the service.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">5. Contact</h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                For questions about these Terms, please contact us at legal@raghub.com
            </p>
        </div>

        <div class="mt-12">
            <a href="{{ route('home') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                ← Back to home
            </a>
        </div>
    </div>
</body>
</html>
