@props(['title', 'value', 'icon', 'trend' => null, 'trendUp' => true, 'color' => 'indigo'])

@php
    $colors = [
        'indigo' => 'from-indigo-500/20 to-indigo-500/5 text-indigo-400',
        'emerald' => 'from-emerald-500/20 to-emerald-500/5 text-emerald-400',
        'amber' => 'from-amber-500/20 to-amber-500/5 text-amber-400',
        'rose' => 'from-rose-500/20 to-rose-500/5 text-rose-400',
    ];
    $currentColor = $colors[$color] ?? $colors['indigo'];
@endphp

<div class="glass p-6 rounded-2xl card-hover transition-all duration-300 relative overflow-hidden group">
    <!-- Background Decor -->
    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-gradient-to-br {{ $currentColor }} opacity-0 group-hover:opacity-10 transition-opacity duration-500 rounded-full blur-2xl"></div>

    <div class="flex justify-between items-start relative z-10">
        <div>
            <p class="text-slate-500 text-sm font-medium mb-1">{{ $title }}</p>
            <h3 class="text-3xl font-bold tracking-tight">{{ $value }}</h3>
            
            @if($trend !== null)
                <div class="mt-2 flex items-center gap-1.5 text-xs font-semibold {{ $trendUp ? 'text-emerald-400' : 'text-rose-400' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($trendUp)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-9 9-4-4-6 6"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-9-9-4 4-6-6"/>
                        @endif
                    </svg>
                    {{ $trend }}% <span class="text-slate-500 font-normal">vs last week</span>
                </div>
            @endif
        </div>
        
        <div class="p-3 rounded-xl bg-gradient-to-br {{ $currentColor }} border border-white/5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        </div>
    </div>
</div>
