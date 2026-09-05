@props(['href', 'icon', 'label', 'active' => false, 'expanded' => 'true'])

<a href="{{ $href }}" 
   class="w-full h-12 flex items-center gap-4 px-3 rounded-xl transition-all duration-200 group {{ $active ? 'accent-gradient text-white shadow-lg shadow-indigo-500/20' : 'hover:bg-white/5 text-slate-400 hover:text-white' }}">
    <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center">
        <svg fill="none" class="w-5 h-5" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
        </svg>
    </div>
    <span x-show="{{ $expanded }}" x-transition class="font-medium text-sm truncate">{{ $label }}</span>
    
    @if($active)
        <div x-show="{{ $expanded }}" class="ms-auto w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
    @endif
</a>
