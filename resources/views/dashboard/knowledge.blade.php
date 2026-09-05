@extends('layouts.dashboard')

@section('title', __('hub.kb_title'))

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">{{ __('hub.kb_title') }}</h1>
            <p class="text-slate-400 mt-1 text-lg">{{ __('hub.kb_subtitle') }}</p>
        </div>
        <div x-data="{ uploading: false }">
            <button @click="document.getElementById('file-upload').click(); uploading = true;" :disabled="uploading" class="px-6 py-3 accent-gradient text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:scale-105 transition-all text-sm uppercase tracking-wider disabled:opacity-50">
                <span x-show="!uploading">{{ __('hub.upload_new_document') }}</span>
                <span x-show="uploading" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    {{ __('hub.uploading') }}
                </span>
            </button>
            <form id="upload-form" action="{{ route('knowledge.store') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="file" id="file-upload" name="file" onchange="this.form.submit();">
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 glass border-emerald-500/30 bg-emerald-500/10 text-emerald-400 rounded-xl flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($sources as $source)
        <div class="glass p-6 rounded-2xl flex flex-col gap-4 border border-white/5 hover:border-white/10 transition-all group relative">
            <div class="flex justify-between items-start">
                <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center border border-white/5">
                    @if($source->type == 'pdf')
                        <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v-4m0 0l-2 2m2-2l2 2"/></svg>
                    @elseif($source->type == 'docx')
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    @else
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @endif
                </div>
                <div class="flex gap-2">
                    <span class="px-2 py-1 rounded text-[10px] uppercase font-bold tracking-widest bg-slate-800/50 border border-white/5">
                        {{ $source->type }}
                    </span>
                </div>
            </div>
            
            <div class="min-h-[60px]">
                <h3 class="font-bold text-lg truncate mb-1" title="{{ $source->name }}">{{ $source->name }}</h3>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full 
                        @if($source->status == 'indexed') bg-emerald-500 
                        @elseif($source->status == 'failed') bg-rose-500 
                        @else bg-amber-500 animate-pulse @endif">
                    </span>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider transition-all">
                        @if($source->status == 'indexed') 
                            {{ __('hub.kb_status_indexed') }}
                        @elseif($source->status == 'failed') 
                            {{ __('hub.kb_status_failed') }}
                        @else 
                            {{ __('hub.kb_status_pending') }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="flex justify-between items-center mt-2 pt-4 border-t border-white/5">
                <span class="text-[10px] text-slate-500 font-bold uppercase">{{ $source->created_at->diffForHumans() }}</span>
                <form action="{{ route('knowledge.destroy', $source) }}" method="POST" onsubmit="return confirm('{{ __('hub.delete_confirm') }}')">
                    @csrf
                    @method('DELETE')
                    <button class="text-rose-500/30 hover:text-rose-500 transition-all focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-20 flex flex-col items-center justify-center glass rounded-3xl border-dashed border-2 border-white/10">
            <svg class="w-16 h-16 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            <p class="text-slate-500 font-bold uppercase tracking-widest text-sm">{{ __('hub.no_documents_found') }}</p>
        </div>
        @endforelse
    </div>

    @if($sources->hasPages())
    <div class="mt-8">
        {{ $sources->links() }}
    </div>
    @endif
</div>
@endsection
