<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - RAG Hub</title>
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
        <h1 class="text-4xl font-bold mb-8">Privacy Policy</h1>
        
        <div class="prose dark:prose-invert max-w-none">
            <p class="text-neutral-600 dark:text-neutral-400 mb-6">Last updated: February 26, 2026</p>

            <h2 class="text-2xl font-bold mt-8 mb-4">1. Information We Collect</h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                We collect information you provide directly to us, including your name, email address, and any content you upload to our platform.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">2. How We Use Your Information</h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                We use the information we collect to provide, maintain, and improve our services, including processing your documents and providing AI-powered responses.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">3. Data Security</h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, or destruction.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">4. Contact Us</h2>
            <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                If you have any questions about this Privacy Policy, please contact us at privacy@raghub.com
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
