<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RAG Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .animate-slide-up { animation: slideUp 0.5s ease-out; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        .animate-shake { animation: shake 0.5s ease-out; }
        .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .input-focus:focus {
            transform: translateY(-2px);
            transition: all 0.2s ease;
        }
        
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
        }
        
        .loader {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #6366f1;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-neutral-50 via-indigo-50/30 to-purple-50/30 dark:from-neutral-950 dark:via-indigo-950/20 dark:to-purple-950/20 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md animate-slide-up">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 mb-4 hover:scale-105 transition-transform duration-300">
                <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">RAG Hub</span>
            </div>
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">Create your account ✨</h1>
            <p class="text-neutral-600 dark:text-neutral-400">Start automating your customer support today</p>
        </div>

        <!-- Registration Form -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-xl border border-neutral-200/50 dark:border-neutral-800/50 rounded-2xl p-8 shadow-2xl">
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-lg animate-shake">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <ul class="text-sm text-red-600 dark:text-red-400 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>❌ {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" id="registerForm" onsubmit="handleSubmit(event)">
                @csrf

                <!-- Name -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Full Name
                    </label>
                    <div class="relative">
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                               class="input-focus w-full px-4 py-3 bg-white dark:bg-neutral-800 border-2 border-neutral-200 dark:border-neutral-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-neutral-900 dark:text-neutral-100 transition-all duration-200"
                               placeholder="John Doe"
                               oninput="validateName(this)">
                        <div id="name-error" class="hidden mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Name must be at least 2 characters</span>
                        </div>
                        <div id="name-success" class="hidden mt-2 text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Looks good!</span>
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                        Email Address
                    </label>
                    <div class="relative">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="input-focus w-full px-4 py-3 bg-white dark:bg-neutral-800 border-2 border-neutral-200 dark:border-neutral-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-neutral-900 dark:text-neutral-100 transition-all duration-200"
                               placeholder="you@example.com"
                               oninput="validateEmail(this)">
                        <div id="email-error" class="hidden mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Please enter a valid email address</span>
                        </div>
                        <div id="email-success" class="hidden mt-2 text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Valid email!</span>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Password
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="input-focus w-full px-4 py-3 pr-12 bg-white dark:bg-neutral-800 border-2 border-neutral-200 dark:border-neutral-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-neutral-900 dark:text-neutral-100 transition-all duration-200"
                               placeholder="Create a strong password"
                               oninput="checkPasswordStrength(this)">
                        <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition">
                            <svg id="eye-icon-password" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <!-- Password Strength Meter -->
                    <div class="mt-2">
                        <div class="flex gap-1 mb-1">
                            <div id="strength-1" class="strength-bar flex-1 bg-neutral-200 dark:bg-neutral-700"></div>
                            <div id="strength-2" class="strength-bar flex-1 bg-neutral-200 dark:bg-neutral-700"></div>
                            <div id="strength-3" class="strength-bar flex-1 bg-neutral-200 dark:bg-neutral-700"></div>
                            <div id="strength-4" class="strength-bar flex-1 bg-neutral-200 dark:bg-neutral-700"></div>
                        </div>
                        <p id="strength-text" class="text-xs text-neutral-500 dark:text-neutral-400">Minimum 8 characters</p>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Confirm Password
                    </label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="input-focus w-full px-4 py-3 pr-12 bg-white dark:bg-neutral-800 border-2 border-neutral-200 dark:border-neutral-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-neutral-900 dark:text-neutral-100 transition-all duration-200"
                               placeholder="Confirm your password"
                               oninput="checkPasswordMatch(this)">
                        <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition">
                            <svg id="eye-icon-confirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <div id="match-error" class="hidden mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Passwords don't match</span>
                    </div>
                    <div id="match-success" class="hidden mt-2 text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Passwords match!</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn" class="btn-hover w-full py-3.5 gradient-bg text-white font-semibold rounded-xl transition-all duration-200 shadow-lg flex items-center justify-center gap-2 group">
                    <span id="btnText">Create Account</span>
                    <svg id="btnIcon" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    <div id="btnLoader" class="loader hidden"></div>
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-800">
                <p class="text-center text-sm text-neutral-600 dark:text-neutral-400">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-semibold transition inline-flex items-center gap-1 group">
                        Login
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 transition inline-flex items-center gap-2 group">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to home
            </a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const eyeIcon = document.getElementById('eye-icon-' + (fieldId === 'password' ? 'password' : 'confirmation'));
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        }

        // Name validation
        function validateName(input) {
            const nameError = document.getElementById('name-error');
            const nameSuccess = document.getElementById('name-success');
            
            if (input.value.length < 2) {
                input.classList.add('border-red-500');
                input.classList.remove('border-green-500', 'border-neutral-200');
                nameError.classList.remove('hidden');
                nameSuccess.classList.add('hidden');
            } else {
                input.classList.remove('border-red-500');
                input.classList.add('border-green-500');
                nameError.classList.add('hidden');
                nameSuccess.classList.remove('hidden');
            }
        }

        // Email validation
        function validateEmail(input) {
            const emailError = document.getElementById('email-error');
            const emailSuccess = document.getElementById('email-success');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (input.value && !emailRegex.test(input.value)) {
                input.classList.add('border-red-500');
                input.classList.remove('border-green-500', 'border-neutral-200');
                emailError.classList.remove('hidden');
                emailSuccess.classList.add('hidden');
            } else if (input.value) {
                input.classList.remove('border-red-500');
                input.classList.add('border-green-500');
                emailError.classList.add('hidden');
                emailSuccess.classList.remove('hidden');
            } else {
                input.classList.remove('border-red-500', 'border-green-500');
                input.classList.add('border-neutral-200');
                emailError.classList.add('hidden');
                emailSuccess.classList.add('hidden');
            }
        }

        // Password strength checker
        function checkPasswordStrength(input) {
            const password = input.value;
            const strengthText = document.getElementById('strength-text');
            const bars = [
                document.getElementById('strength-1'),
                document.getElementById('strength-2'),
                document.getElementById('strength-3'),
                document.getElementById('strength-4')
            ];
            
            // Reset bars
            bars.forEach(bar => {
                bar.className = 'strength-bar flex-1 bg-neutral-200 dark:bg-neutral-700';
            });
            
            if (password.length === 0) {
                strengthText.textContent = 'Minimum 8 characters';
                strengthText.className = 'text-xs text-neutral-500 dark:text-neutral-400';
                return;
            }
            
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Complexity checks
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            // Cap at 4
            strength = Math.min(strength, 4);
            
            // Update bars and text
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
            const texts = ['Weak 😟', 'Fair 😐', 'Good 🙂', 'Strong 💪'];
            const textColors = ['text-red-600 dark:text-red-400', 'text-orange-600 dark:text-orange-400', 'text-yellow-600 dark:text-yellow-400', 'text-green-600 dark:text-green-400'];
            
            for (let i = 0; i < strength; i++) {
                bars[i].className = `strength-bar flex-1 ${colors[strength - 1]}`;
            }
            
            if (strength > 0) {
                strengthText.textContent = texts[strength - 1];
                strengthText.className = `text-xs font-medium ${textColors[strength - 1]}`;
            }
            
            // Check password match when typing
            const confirmInput = document.getElementById('password_confirmation');
            if (confirmInput.value) {
                checkPasswordMatch(confirmInput);
            }
        }

        // Password match checker
        function checkPasswordMatch(input) {
            const password = document.getElementById('password').value;
            const matchError = document.getElementById('match-error');
            const matchSuccess = document.getElementById('match-success');
            
            if (input.value && input.value !== password) {
                input.classList.add('border-red-500');
                input.classList.remove('border-green-500', 'border-neutral-200');
                matchError.classList.remove('hidden');
                matchSuccess.classList.add('hidden');
            } else if (input.value && input.value === password) {
                input.classList.remove('border-red-500');
                input.classList.add('border-green-500');
                matchError.classList.add('hidden');
                matchSuccess.classList.remove('hidden');
            } else {
                input.classList.remove('border-red-500', 'border-green-500');
                input.classList.add('border-neutral-200');
                matchError.classList.add('hidden');
                matchSuccess.classList.add('hidden');
            }
        }

        // Form submission with loader
        function handleSubmit(event) {
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');
            const btnLoader = document.getElementById('btnLoader');
            
            // Show loader
            btnText.textContent = 'Creating account...';
            btnIcon.classList.add('hidden');
            btnLoader.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        }

        // Auto-focus name on load
        window.addEventListener('load', function() {
            document.getElementById('name').focus();
        });
    </script>
</body>
</html>
