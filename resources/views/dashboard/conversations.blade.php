@extends('layouts.dashboard')

@section('title', __('hub.messages'))

@section('content')
<div class="h-[calc(100vh-12rem)] flex overflow-hidden rounded-2xl glass border border-white/5 animate-in fade-in slide-in-from-bottom-4 duration-700" 
     x-data="chatSystem()" 
     x-init="init()">
    
    <!-- Left Sidebar: Conversations List -->
    <div class="w-full md:w-80 lg:w-96 flex flex-col border-inline-end border-white/5 bg-slate-950/20">
        <!-- Search & Header -->
        <div class="p-4 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">@lang('hub.conversations')</h3>
                <div class="flex gap-2">
                    <button @click="filterType = 'all'" :class="filterType === 'all' ? 'bg-indigo-500/20 text-indigo-400' : 'text-slate-500'" class="p-1 px-2 rounded-lg transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                    <button @click="filterType = 'human'" :class="filterType === 'human' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/20' : 'text-slate-500'" class="p-1 px-2 rounded-lg transition-all relative">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <template x-if="conversations.filter(c => c.escalated_to_human).length > 0">
                            <span class="absolute -top-1 -right-1 w-2 h-2 bg-amber-500 rounded-full"></span>
                        </template>
                    </button>
                </div>
            </div>
            <div class="relative group">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-500 group-focus-within:text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" 
                       x-model="search"
                       placeholder="{{ __('Search...') }}" 
                       class="w-full pl-10 pr-4 py-2 bg-slate-900/50 border border-white/10 rounded-xl focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 outline-none transition-all text-sm">
            </div>
        </div>

        <!-- List -->
        <div class="flex-1 overflow-y-auto custom-scrollbar divide-y divide-white/5">
            <!-- Skeleton Loader -->
            <template x-if="loading">
                <div class="p-4 space-y-4">
                    <template x-for="i in 6">
                        <div class="flex gap-3">
                            <div class="w-12 h-12 rounded-full bg-white/5 animate-pulse"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 w-2/3 bg-white/5 animate-pulse rounded"></div>
                                <div class="h-3 w-1/2 bg-white/5 animate-pulse rounded"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!loading && filteredConversations.length === 0">
                <div class="flex flex-col items-center justify-center h-64 text-slate-600 px-6 text-center">
                    <div class="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <p class="text-sm font-medium">@lang('hub.no_conversations')</p>
                </div>
            </template>

            <!-- Conversation Items -->
            <template x-for="conv in filteredConversations" :key="conv.id">
                <div @click="selectConversation(conv)" 
                     :class="{'bg-indigo-500/10 border-indigo-500/20 active-chat-glow': selectedId === conv.id, 'hover:bg-white/[0.03]': selectedId !== conv.id}"
                     class="p-4 cursor-pointer transition-all relative group overflow-hidden border-l-2" 
                     :style="selectedId === conv.id ? 'border-color: #6366f1' : 'border-color: transparent'">
                    
                    <div class="flex gap-3 relative z-10">
                        <!-- Avatar -->
                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center border border-white/10 overflow-hidden shadow-lg group-hover:border-indigo-500/30 transition-colors">
                                <img :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(conv.contact_name || conv.phone_number) + '&background=6366f1&color=fff'" alt="">
                            </div>
                            <template x-if="conv.escalated_to_human">
                                <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-amber-500 border-2 border-[#050810] rounded-full flex items-center justify-center">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-bold text-sm truncate" :class="selectedId === conv.id ? 'text-indigo-300' : 'text-slate-200'" x-text="conv.contact_name || conv.phone_number"></h4>
                                <span class="text-[10px] text-slate-500 whitespace-nowrap font-medium" x-text="formatTime(conv.last_message_at)"></span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs text-slate-400 truncate flex-1" x-text="conv.last_message || '...'" :class="{'text-slate-300 font-medium': selectedId === conv.id}"></p>
                                
                                <!-- SLA Status Dot -->
                                <template x-if="conv.messages.length > 0 && conv.messages[0].metadata?.sla_badge">
                                    <div :class="{
                                        'bg-emerald-500': conv.messages[0].metadata.sla_badge.color === 'green',
                                        'bg-amber-500': conv.messages[0].metadata.sla_badge.color === 'yellow',
                                        'bg-rose-500': conv.messages[0].metadata.sla_badge.color === 'red'
                                    }" class="w-2 h-2 rounded-full shadow-sm"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Right: Chat Window -->
    <div class="flex-1 flex flex-col bg-[#050810]/40 relative">
        <template x-if="!selected">
            <div class="flex-1 flex flex-col items-center justify-center text-slate-500 bg-slate-950/20">
                <div class="w-20 h-20 accent-gradient p-5 rounded-3xl shadow-2xl shadow-indigo-500/20 mb-6 animate-pulse">
                    <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <h4 class="text-xl font-bold text-white mb-2">@lang('hub.chat')</h4>
                <p class="text-sm">Select a conversation to start chatting.</p>
            </div>
        </template>

        <template x-if="selected">
            <div class="flex-1 flex flex-col h-full">
                <!-- Chat Header -->
                <div class="h-20 glass flex items-center justify-between px-6 border-b border-white/5 shrink-0 z-20">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-slate-800 border border-white/10 overflow-hidden shadow-lg">
                            <img :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(selected.contact_name || selected.phone_number) + '&background=6366f1&color=fff'" alt="">
                        </div>
                        <div>
                            <h4 class="font-bold text-base" x-text="selected.contact_name || selected.phone_number"></h4>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest">@lang('hub.online')</span>
                                <span class="text-[10px] text-slate-500 px-2 border-l border-white/10" x-text="selected.platform.toUpperCase()"></span>
                                <template x-if="selected.language">
                                    <span class="text-[10px] bg-white/10 px-1.5 py-0.5 rounded text-white uppercase font-black" x-text="selected.language"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <button class="p-2.5 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5V.01M12 12V.01M12 19V.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Banner if human required -->
                <template x-if="selected.escalated_to_human">
                    <div class="bg-amber-500/10 border-y border-amber-500/20 px-6 py-2 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2 text-amber-400 text-xs font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @lang('hub.human_reply_required')
                        </div>
                    </div>
                </template>

                <!-- Messages area -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-6 flex flex-col scroll-smooth" id="messages-container">
                    <template x-for="(msg, index) in selected.messages" :key="msg.id || index">
                        <div class="flex w-full" :class="msg.role === 'user' ? 'justify-start animation-slide-in-left' : 'justify-end animation-slide-in-right'">
                            <!-- Bubble -->
                            <div class="max-w-[85%] md:max-w-[70%] lg:max-w-[60%] flex flex-col" :class="msg.role === 'user' ? 'items-start' : 'items-end'">
                                <!-- Sender Name (Human Replies) -->
                                <template x-if="msg.role === 'assistant' && msg.source === 'human'">
                                    <span class="text-[9px] text-indigo-400 font-black uppercase tracking-widest mb-1 ml-1">@lang('hub.agent')</span>
                                </template>

                                <div :class="{
                                         'bg-slate-800 text-white rounded-2xl rounded-tl-sm shadow-md': msg.role === 'user',
                                         'accent-gradient text-white rounded-2xl rounded-tr-sm shadow-lg shadow-indigo-500/10': msg.role === 'assistant',
                                         'opacity-70': msg.status === 'sending'
                                     }"
                                     class="px-4 py-3 relative group transition-all hover:scale-[1.01]">
                                    
                                    <!-- Audio Message UI -->
                                    <template x-if="msg.metadata?.audio_url">
                                        <div class="mb-3 flex items-center gap-3 bg-black/30 p-3 rounded-2xl border border-white/5 group/audio">
                                            <button type="button" 
                                                    @click="const a = $el.closest('.group/audio').querySelector('audio'); a.paused ? a.play() : a.pause()"
                                                    class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-all">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            </button>
                                            <div class="flex-1 min-w-[120px]">
                                                <div class="h-1 bg-white/10 rounded-full overflow-hidden">
                                                    <div class="h-full bg-indigo-400 shadow-[0_0_8px_rgba(99,102,241,0.5)] transition-all" style="width: 0%" x-init="const a = $el.closest('.group/audio').querySelector('audio'); a.addEventListener('timeupdate', () => $el.style.width = (a.currentTime/a.duration*100)+'%'); a.addEventListener('ended', () => $el.style.width = '0%')"></div>
                                                </div>
                                            </div>
                                            <audio :src="msg.metadata.audio_url" preload="metadata"
                                                   @play="$el.previousElementSibling.previousElementSibling.querySelector('svg').innerHTML = '<path d=\'M6 19h4V5H6v14zm8-14v14h4V5h-4z\'/>'" 
                                                   @pause="$el.previousElementSibling.previousElementSibling.querySelector('svg').innerHTML = '<path d=\'M8 5v14l11-7z\'/>'"></audio>
                                        </div>
                                    </template>

                                    <!-- File Message UI -->
                                    <template x-if="msg.metadata?.file_url">
                                        <a :href="msg.metadata.file_url" target="_blank" class="mb-3 flex items-center gap-3 bg-white/5 hover:bg-white/10 p-3 rounded-2xl border border-white/10 transition-colors group/file">
                                            <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-bold truncate" x-text="msg.metadata.file_name || 'Attached File'"></div>
                                                <div class="text-[10px] opacity-50 uppercase tracking-tighter" x-text="msg.metadata.file_size || 'View File'"></div>
                                            </div>
                                        </a>
                                    </template>

                                    <!-- Text Content -->
                                    <p class="text-[13.5px] leading-relaxed whitespace-pre-wrap font-medium"
                                       :class="msg.metadata?.audio_url ? 'italic text-slate-300 border-l-2 border-white/10 pl-3' : ''"
                                       x-text="msg.content"></p>
                                    
                                    <!-- SLA/Source Footer -->
                                    <template x-if="msg.role === 'assistant' || msg.metadata?.sla_badge">
                                        <div class="mt-2 flex items-center gap-2">
                                            <template x-if="msg.metadata?.sla_badge">
                                                <span :class="{
                                                    'text-emerald-400 bg-emerald-500/10 border-emerald-500/20': msg.metadata.sla_badge.color === 'green',
                                                    'text-amber-400 bg-amber-500/10 border-amber-500/20': msg.metadata.sla_badge.color === 'yellow',
                                                    'text-rose-400 bg-rose-500/10 border-rose-500/20': msg.metadata.sla_badge.color === 'red'
                                                }" class="text-[8px] font-black uppercase border px-1.5 py-0.5 rounded-lg">
                                                    SLA: <span x-text="msg.metadata.sla_badge.status"></span>
                                                </span>
                                            </template>
                                            <span class="text-[8px] font-black uppercase tracking-widest px-1.5 py-0.5 bg-black/20 rounded-lg border border-white/5 opacity-50" x-text="msg.source"></span>
                                        </div>
                                    </template>
                                </div>
                                <!-- Timestamp bar -->
                                <div class="flex items-center gap-1.5 mt-1 px-1" :class="msg.role === 'user' ? 'justify-start' : 'justify-end'">
                                    <span class="text-[9px] text-slate-500 font-bold tracking-tight uppercase" x-text="formatTime(msg.created_at)"></span>
                                    <template x-if="msg.status === 'sending'">
                                        <svg class="w-3 h-3 text-slate-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Input area -->
                <div class="p-6 shrink-0 z-20">
                    <form @submit.prevent="sendMessage()" class="relative flex items-center gap-3">
                        <div class="flex-1 relative group bg-slate-900 shadow-2xl rounded-2xl border border-white/5 focus-within:border-indigo-500/30 transition-all p-1">
                            <textarea 
                                x-model="replyText"
                                @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                                placeholder="@lang('hub.type_message')" 
                                rows="1"
                                class="w-full bg-transparent border-none focus:ring-0 outline-none text-sm px-4 py-3 resize-none custom-scrollbar min-h-[44px] max-h-32 text-white"
                                :disabled="sending"></textarea>
                            
                            <div class="absolute right-3 bottom-3 flex items-center gap-2">
                                <button type="button" class="p-1.5 text-slate-500 hover:text-indigo-400 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                                <button type="button" class="p-1.5 text-slate-500 hover:text-indigo-400 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.414a6 6 0 108.486 8.486L20.5 13"/></svg>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" 
                                :disabled="!replyText.trim() || sending"
                                :class="!replyText.trim() || sending ? 'opacity-50 grayscale cursor-not-allowed' : 'hover:scale-105 active:scale-95 shadow-lg shadow-indigo-500/20'"
                                class="w-12 h-12 accent-gradient rounded-2xl flex items-center justify-center text-white transition-all">
                            <template x-if="!sending">
                                <svg class="w-6 h-6 transform translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            </template>
                            <template x-if="sending">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </template>
                        </button>
                    </form>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function chatSystem() {
    return {
        conversations: [],
        loading: true,
        selectedId: null,
        selected: null,
        search: '',
        filterType: 'all', // all, human
        replyText: '',
        sending: false,
        pollInterval: null,

        async init() {
            // Handle filter from URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('filter') === 'human') {
                this.filterType = 'human';
            }

            await this.fetchConversations();
            this.loading = false;
            
            // Start polling
            this.pollInterval = setInterval(() => this.fetchConversations(false), 5000);
        },

        async fetchConversations(showLoader = true) {
            try {
                const response = await fetch('{{ route("api.conversations") }}');
                const data = await response.json();
                
                this.conversations = data;

                if (this.selectedId) {
                    const found = data.find(c => c.id === this.selectedId);
                    if (found) {
                        // Sort messages ASC (oldest first)
                        found.messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                        
                        const oldMessagesCount = this.selected ? this.selected.messages.length : 0;
                        this.selected = found;
                        
                        if (found.messages.length > oldMessagesCount) {
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    }
                }
            } catch (error) {
                console.error('Failed to fetch conversations:', error);
            }
        },

        get filteredConversations() {
            let filtered = [...this.conversations];
            
            // Search
            if (this.search) {
                const s = this.search.toLowerCase();
                filtered = filtered.filter(c => 
                    (c.contact_name?.toLowerCase().includes(s)) || 
                    (c.phone_number?.includes(s)) ||
                    (c.last_message?.toLowerCase().includes(s))
                );
            }

            // Type Filter
            if (this.filterType === 'human') {
                filtered = filtered.filter(c => c.escalated_to_human);
            }

            // Sort by last_message_at DESC
            return filtered.sort((a, b) => {
                const dateA = a.last_message_at ? new Date(a.last_message_at) : 0;
                const dateB = b.last_message_at ? new Date(b.last_message_at) : 0;
                return dateB - dateA;
            });
        },

        selectConversation(conv) {
            this.selectedId = conv.id;
            // Clone and sort messages ASC
            const sortedConv = { ...conv };
            sortedConv.messages = [...conv.messages].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            this.selected = sortedConv;
            this.replyText = '';
            this.$nextTick(() => {
                setTimeout(() => this.scrollToBottom('auto'), 50);
            });
        },

        async sendMessage() {
            if (!this.replyText.trim() || this.sending) return;

            const text = this.replyText;
            this.replyText = '';
            this.sending = true;

            // Optimistic UI update
            const optimisticMsg = {
                id: 'tmp-' + Date.now(),
                role: 'assistant',
                source: 'human',
                content: text,
                created_at: new Date().toISOString(),
                status: 'sending'
            };
            
            this.selected.messages.push(optimisticMsg);
            this.$nextTick(() => this.scrollToBottom());

            try {
                const url = '{{ route("api.conversations.reply", ":id") }}'.replace(':id', this.selected.id);
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: text })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Replace optimistic message
                    const idx = this.selected.messages.findIndex(m => m.id === optimisticMsg.id);
                    if (idx !== -1) {
                        this.selected.messages[idx] = { ...data.message, status: 'confirmed' };
                    }
                    this.selected.escalated_to_human = false;
                } else {
                    throw new Error(data.error || 'Failed to send');
                }
            } catch (error) {
                console.error('Send error:', error);
                // Mark as failed instead of removing (optional improvement)
                this.selected.messages = this.selected.messages.filter(m => m.id !== optimisticMsg.id);
                alert(error.message || 'Connection error');
            } finally {
                this.sending = false;
                this.$nextTick(() => this.scrollToBottom());
                setTimeout(() => this.fetchConversations(false), 2000);
            }
        },

        scrollToBottom(behavior = 'smooth') {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTo({
                    top: container.scrollHeight,
                    behavior: behavior
                });
            }
        },

        formatTime(dateString) {
            if (!dateString) return '';
            const d = new Date(dateString);
            const now = new Date();
            const diff = now - d;
            
            if (diff < 60000) return '{{ __("hub.just_now") }}';
            if (d.toDateString() === now.toDateString()) {
                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            if (diff < 7 * 24 * 3600 * 1000) {
                return d.toLocaleDateString([], { weekday: 'short' });
            }
            return d.toLocaleDateString([], { month: 'short', day: 'numeric' });
        }
    }
}
</script>
@endsection
