@extends('layouts.dashboard')

@section('title', __('hub.messages') . ' - WhatsApp Web')

@section('content')
<div class="h-[calc(100vh-10rem)] min-h-[580px] flex overflow-hidden rounded-2xl border border-[#222e35] shadow-2xl bg-[#0b141a] animate-in fade-in duration-500 font-sans" 
     x-data="whatsappChat()" 
     x-init="init()">
    
    <!-- LEFT PANEL: Conversations Sidebar -->
    <div class="w-full md:w-80 lg:w-[380px] flex flex-col bg-[#111b21] border-r border-[#222e35] rtl:border-r-0 rtl:border-l shrink-0">
        <!-- Sidebar Header -->
        <div class="h-16 px-4 bg-[#202c33] flex items-center justify-between border-b border-[#222e35]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-[#00a884]/20 border border-[#00a884]/40 flex items-center justify-center text-[#00a884] font-bold">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#e9edef]">WhatsApp Chats</h3>
                    <span class="text-[10px] text-[#00a884] flex items-center gap-1 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00a884] animate-pulse"></span>
                        Live Gateway
                    </span>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="flex items-center gap-1 text-[#aebac1]">
                <button @click="fetchConversations(false)" title="Refresh" class="p-2 hover:bg-[#374248] rounded-full transition-colors">
                    <svg class="w-5 h-5" :class="isRefreshing ? 'animate-spin text-[#00a884]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>

        <!-- Search Bar & Filters -->
        <div class="p-2.5 bg-[#111b21] border-b border-[#222e35] space-y-2">
            <div class="relative bg-[#202c33] rounded-lg flex items-center px-3 py-1.5">
                <svg class="w-4 h-4 text-[#8696a0] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" 
                       x-model="search"
                       placeholder="Search or start new chat" 
                       class="w-full bg-transparent border-none focus:outline-none focus:ring-0 text-sm text-[#e9edef] placeholder-[#8696a0] px-2 py-0.5">
                <button x-show="search" @click="search = ''" class="text-[#8696a0] hover:text-white text-xs">✕</button>
            </div>

            <!-- Filter Pills -->
            <div class="flex gap-1.5 pt-0.5">
                <button @click="filterType = 'all'" 
                        :class="filterType === 'all' ? 'bg-[#00a884]/20 text-[#00a884] border-[#00a884]/40 font-semibold' : 'bg-[#202c33] text-[#8696a0] border-transparent hover:text-[#d1d7db]'"
                        class="px-3 py-1 rounded-full text-xs border transition-all">
                    All (<span x-text="conversations.length"></span>)
                </button>
                <button @click="filterType = 'human'" 
                        :class="filterType === 'human' ? 'bg-amber-500/20 text-amber-400 border-amber-500/40 font-semibold' : 'bg-[#202c33] text-[#8696a0] border-transparent hover:text-[#d1d7db]'"
                        class="px-3 py-1 rounded-full text-xs border transition-all flex items-center gap-1.5">
                    <span>Human Needed</span>
                    <template x-if="conversations.filter(c => c.escalated_to_human).length > 0">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    </template>
                </button>
            </div>
        </div>

        <!-- Conversations List -->
        <div class="flex-1 overflow-y-auto custom-scrollbar divide-y divide-[#222e35]/50">
            <!-- Loading Skeleton -->
            <template x-if="loading">
                <div class="p-3 space-y-3">
                    <template x-for="i in 5" :key="i">
                        <div class="flex gap-3 items-center">
                            <div class="w-12 h-12 rounded-full bg-[#202c33] animate-pulse shrink-0"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 w-2/3 bg-[#202c33] rounded animate-pulse"></div>
                                <div class="h-3 w-4/5 bg-[#202c33] rounded animate-pulse"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!loading && filteredConversations.length === 0">
                <div class="flex flex-col items-center justify-center h-64 text-[#8696a0] px-6 text-center">
                    <div class="w-14 h-14 bg-[#202c33] rounded-full flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 opacity-40 text-[#00a884]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-[#d1d7db]">No conversations yet</p>
                    <p class="text-xs text-[#8696a0] mt-1">Send a message to your WhatsApp connected number to test live chat!</p>
                </div>
            </template>

            <!-- Conversation Item -->
            <template x-for="conv in filteredConversations" :key="conv.id">
                <div @click="selectConversation(conv)" 
                     :class="{
                         'bg-[#2a3942] border-l-4 border-l-[#00a884] rtl:border-l-0 rtl:border-r-4 rtl:border-r-[#00a884]': selectedId === conv.id,
                         'hover:bg-[#202c33]': selectedId !== conv.id
                     }"
                     class="px-3.5 py-3 cursor-pointer transition-colors flex items-center gap-3 relative group">
                    
                    <!-- Avatar with initials / color -->
                    <div class="relative shrink-0">
                        <div class="w-12 h-12 rounded-full overflow-hidden flex items-center justify-center font-bold text-white shadow-md"
                             :style="'background: ' + getAvatarColor(conv.contact_name || conv.phone_number)">
                            <span class="text-base font-semibold uppercase" x-text="getInitials(conv.contact_name || conv.phone_number)"></span>
                        </div>
                        <template x-if="conv.escalated_to_human">
                            <span class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-amber-500 border-2 border-[#111b21] rounded-full flex items-center justify-center" title="Human attention needed">
                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            </span>
                        </template>
                    </div>

                    <!-- Meta & Snippet -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="font-semibold text-sm text-[#e9edef] truncate" x-text="conv.contact_name || conv.phone_number"></h4>
                            <span class="text-[11px] text-[#8696a0] whitespace-nowrap font-normal" x-text="formatWhatsAppTime(conv.last_message_at)"></span>
                        </div>
                        
                        <div class="flex items-center justify-between gap-1">
                            <div class="flex items-center gap-1 min-w-0 text-xs text-[#8696a0] truncate">
                                <!-- Double checkmark if outgoing message -->
                                <template x-if="conv.last_message_role === 'assistant'">
                                    <svg class="w-4 h-4 shrink-0 text-[#53bdeb]" viewBox="0 0 16 11" fill="currentColor">
                                        <path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"/>
                                    </svg>
                                </template>
                                <span class="truncate" x-text="conv.last_message || 'No messages yet'"></span>
                            </div>

                            <!-- Human handoff label or tag -->
                            <template x-if="conv.escalated_to_human">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 shrink-0">
                                    Human
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- RIGHT PANEL: WhatsApp Chat Area -->
    <div class="flex-1 flex flex-col bg-[#0b141a] relative overflow-hidden">
        
        <!-- EMPTY STATE (No chat selected) -->
        <template x-if="!selected">
            <div class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-[#222e35]/30">
                <div class="w-24 h-24 rounded-full bg-[#202c33] border border-[#222e35] flex items-center justify-center mb-6 shadow-xl text-[#00a884]">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zM12.05 20.15c-1.47 0-2.91-.39-4.18-1.12l-.3-.18-3.11.82.83-3.03-.2-.31a7.92 7.92 0 01-1.22-4.22c0-4.38 3.56-7.94 7.94-7.94 2.12 0 4.12.83 5.62 2.33a7.9 7.9 0 012.32 5.61c0 4.38-3.56 7.94-7.94 7.94z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-[#e9edef] mb-2">WhatsApp Web Live Chat</h3>
                <p class="text-sm text-[#8696a0] max-w-md leading-relaxed">
                    Select a conversation from the left to view customer chats, AI RAG automated responses, and reply live with human takeover.
                </p>
                <div class="mt-8 flex items-center gap-2 text-xs text-[#8696a0] bg-[#111b21] px-4 py-2 rounded-full border border-[#222e35]">
                    <svg class="w-4 h-4 text-[#00a884]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Connected to WhatsApp Multi-Device Gateway</span>
                </div>
            </div>
        </template>

        <!-- ACTIVE CHAT VIEW -->
        <template x-if="selected">
            <div class="flex-1 flex flex-col h-full overflow-hidden">
                
                <!-- Chat Top Header -->
                <div class="h-16 px-4 bg-[#202c33] flex items-center justify-between border-b border-[#222e35] shrink-0 z-20">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Contact Avatar -->
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white shadow shrink-0"
                             :style="'background: ' + getAvatarColor(selected.contact_name || selected.phone_number)">
                            <span class="text-sm uppercase" x-text="getInitials(selected.contact_name || selected.phone_number)"></span>
                        </div>

                        <!-- Name & Online Status -->
                        <div class="min-w-0">
                            <h4 class="font-bold text-sm text-[#e9edef] truncate" x-text="selected.contact_name || selected.phone_number"></h4>
                            <div class="flex items-center gap-2 text-xs text-[#8696a0]">
                                <span class="flex items-center gap-1 text-[#00a884]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#00a884]"></span>
                                    <span>online</span>
                                </span>
                                <span class="text-[#222e35]">|</span>
                                <span class="text-[11px]" x-text="selected.phone_number"></span>
                                <template x-if="selected.language">
                                    <span class="text-[9px] bg-[#111b21] px-1.5 py-0.5 rounded text-[#aebac1] uppercase font-bold border border-[#222e35]" x-text="selected.language"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Actions & Human Toggle -->
                    <div class="flex items-center gap-2">
                        <!-- Human Handoff Toggle Button -->
                        <button @click="toggleEscalation()" 
                                :class="selected.escalated_to_human ? 'bg-amber-500/20 text-amber-300 border-amber-500/40 hover:bg-amber-500/30' : 'bg-[#111b21] text-[#aebac1] border-[#222e35] hover:text-[#e9edef]'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold border flex items-center gap-1.5 transition-all">
                            <template x-if="selected.escalated_to_human">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                                    <span>Human Handoff Active</span>
                                </span>
                            </template>
                            <template x-if="!selected.escalated_to_human">
                                <span class="flex items-center gap-1.5">
                                    <span class="text-xs">🤖</span>
                                    <span>AI Autopilot</span>
                                </span>
                            </template>
                        </button>
                    </div>
                </div>

                <!-- Messages Wallpaper & Scroll Area -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6 space-y-3 flex flex-col relative"
                     id="messages-container"
                     style="background-color: #0b141a; background-image: radial-gradient(#182229 1.5px, transparent 1.5px); background-size: 20px 20px;">
                    
                    <!-- Date Centered Pill -->
                    <div class="flex justify-center my-2 sticky top-2 z-10">
                        <span class="bg-[#182229]/90 backdrop-blur-sm border border-[#222e35] text-[#8696a0] text-[11px] font-semibold px-3 py-1 rounded-md shadow-sm">
                            TODAY
                        </span>
                    </div>

                    <!-- Messages Loop -->
                    <template x-for="(msg, index) in selected.messages" :key="msg.id || index">
                        <div class="flex w-full" :class="msg.role === 'user' ? 'justify-start' : 'justify-end'">
                            
                            <!-- WhatsApp Bubble Container -->
                            <div class="max-w-[85%] md:max-w-[70%] lg:max-w-[62%] relative flex flex-col group">
                                
                                <!-- The Bubble -->
                                <div :class="{
                                         'bg-[#202c33] text-[#e9edef] rounded-2xl rounded-tl-none border border-[#2a3942]/40 shadow-sm': msg.role === 'user',
                                         'bg-[#005c4b] text-[#e9edef] rounded-2xl rounded-tr-none border border-[#02735e]/40 shadow-md': msg.role === 'assistant',
                                         'opacity-75': msg.status === 'sending'
                                     }"
                                     class="px-3.5 py-2.5 relative transition-all">
                                    
                                    <!-- Sender Label (For Incoming User Message or Agent) -->
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <template x-if="msg.role === 'user'">
                                            <span class="text-[11px] font-bold text-[#00a884]" x-text="selected.contact_name || 'Customer'"></span>
                                        </template>
                                        <template x-if="msg.role === 'assistant'">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#25d366]/90 flex items-center gap-1">
                                                <span x-text="msg.source === 'human' ? '👤 Support Agent' : '🤖 AI Assistant'"></span>
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Audio Message UI (WhatsApp Voice Note) -->
                                    <template x-if="msg.metadata?.audio_url">
                                        <div class="my-2 flex items-center gap-3 bg-black/25 p-2.5 rounded-xl border border-white/5">
                                            <button type="button" 
                                                    @click="toggleAudio($el)"
                                                    class="w-9 h-9 rounded-full bg-[#00a884] text-white flex items-center justify-center shrink-0 shadow hover:bg-[#02906f] transition-all">
                                                <svg class="w-5 h-5 play-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            </button>
                                            <div class="flex-1 min-w-[120px]">
                                                <div class="h-1.5 bg-white/20 rounded-full overflow-hidden">
                                                    <div class="h-full bg-[#00a884] transition-all" style="width: 0%"></div>
                                                </div>
                                                <div class="flex justify-between text-[10px] text-[#8696a0] mt-1 font-mono">
                                                    <span>Voice Note</span>
                                                    <span class="time-display">0:00</span>
                                                </div>
                                            </div>
                                            <audio :src="msg.metadata.audio_url" preload="metadata"
                                                   @timeupdate="updateAudioProgress($el)"
                                                   @ended="resetAudio($el)"></audio>
                                        </div>
                                    </template>

                                    <!-- Text Content -->
                                    <div class="text-[14px] leading-relaxed whitespace-pre-wrap select-text font-normal pr-14" 
                                         x-text="msg.content"></div>
                                    
                                    <!-- Bubble Footer: Timestamp & Checkmarks -->
                                    <div class="float-right -mt-3 ml-2 flex items-center gap-1 select-none">
                                        <span class="text-[10px] font-medium" 
                                              :class="msg.role === 'assistant' ? 'text-[#7ca69e]' : 'text-[#8696a0]'"
                                              x-text="formatBubbleTime(msg.created_at)"></span>
                                        
                                        <!-- Checkmarks for assistant messages -->
                                        <template x-if="msg.role === 'assistant'">
                                            <span>
                                                <template x-if="msg.status === 'sending'">
                                                    <!-- Single Gray Clock / Sent -->
                                                    <svg class="w-3.5 h-3.5 text-[#7ca69e] animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="3"></circle></svg>
                                                </template>
                                                <template x-if="msg.status !== 'sending'">
                                                    <!-- Double Blue Checkmarks -->
                                                    <svg class="w-4 h-4 text-[#53bdeb]" viewBox="0 0 16 11" fill="currentColor">
                                                        <path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"/>
                                                    </svg>
                                                </template>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Quick Replies Bar -->
                <div class="px-4 py-2 bg-[#111b21] border-t border-[#222e35] flex items-center gap-2 overflow-x-auto custom-scrollbar shrink-0 text-xs">
                    <span class="text-[#8696a0] font-bold text-[11px] shrink-0">Quick:</span>
                    <button type="button" @click="insertQuickText('أهلاً وسهلاً بك! كيف يمكنني مساعدتك اليوم؟')" class="px-2.5 py-1 bg-[#202c33] hover:bg-[#2a3942] text-[#d1d7db] rounded-full border border-[#222e35] shrink-0 transition-colors">
                        Greeting 👋
                    </button>
                    <button type="button" @click="insertQuickText('تم استلام استفسارك وجارٍ مراجعته فوراً.')" class="px-2.5 py-1 bg-[#202c33] hover:bg-[#2a3942] text-[#d1d7db] rounded-full border border-[#222e35] shrink-0 transition-colors">
                        Acknowledged ✅
                    </button>
                    <button type="button" @click="insertQuickText('شكراً لتواصلك معنا، نتمنى لك يوماً سعيداً!')" class="px-2.5 py-1 bg-[#202c33] hover:bg-[#2a3942] text-[#d1d7db] rounded-full border border-[#222e35] shrink-0 transition-colors">
                        Farewell 🙏
                    </button>
                </div>

                <!-- Bottom Message Input Bar (WhatsApp Web Style) -->
                <div class="p-3 bg-[#202c33] flex items-center gap-2 shrink-0 border-t border-[#222e35] z-20">
                    <!-- Emoji Picker Icon -->
                    <button type="button" 
                            @click="showEmoji = !showEmoji"
                            class="p-2 text-[#8696a0] hover:text-[#e9edef] transition-colors rounded-full relative"
                            title="Insert Emoji">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        
                        <!-- Mini Emoji Popup -->
                        <div x-show="showEmoji" @click.away="showEmoji = false" x-cloak 
                             class="absolute bottom-12 left-0 bg-[#222e35] border border-[#2a3942] rounded-xl p-2 shadow-2xl flex gap-1 z-30">
                            <template x-for="em in ['👍', '❤️', '😊', '🙏', '✅', '👋', '🎉', '🎧']" :key="em">
                                <button type="button" @click.stop="replyText += em; showEmoji = false" class="p-1.5 hover:bg-[#2a3942] rounded text-lg leading-none" x-text="em"></button>
                            </template>
                        </div>
                    </button>

                    <!-- Text Input -->
                    <div class="flex-1 bg-[#2a3942] rounded-lg px-4 py-2 flex items-center border border-transparent focus-within:border-[#00a884]/40 transition-colors">
                        <textarea 
                            x-model="replyText"
                            @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                            placeholder="Type a message" 
                            rows="1"
                            class="w-full bg-transparent border-none focus:outline-none focus:ring-0 text-sm text-[#e9edef] placeholder-[#8696a0] resize-none max-h-32 custom-scrollbar"
                            :disabled="sending"></textarea>
                    </div>

                    <!-- WhatsApp Green Send Button -->
                    <button @click="sendMessage()"
                            :disabled="!replyText.trim() || sending"
                            :class="!replyText.trim() || sending ? 'opacity-40 cursor-not-allowed' : 'bg-[#00a884] hover:bg-[#02906f] text-white shadow-lg active:scale-95'"
                            class="w-11 h-11 rounded-full flex items-center justify-center transition-all shrink-0">
                        <template x-if="!sending">
                            <svg class="w-5 h-5 rtl:rotate-180 transform translate-x-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </template>
                        <template x-if="sending">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </template>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function whatsappChat() {
    return {
        conversations: [],
        loading: true,
        selectedId: null,
        selected: null,
        search: '',
        filterType: 'all',
        replyText: '',
        sending: false,
        isRefreshing: false,
        showEmoji: false,
        pollTimer: null,

        async init() {
            await this.fetchConversations();
            this.loading = false;

            // Auto-select first conversation if available
            if (!this.selectedId && this.conversations.length > 0) {
                this.selectConversation(this.conversations[0]);
            }

            // Live polling every 2.5 seconds
            this.pollTimer = setInterval(() => this.fetchConversations(false), 2500);
        },

        async fetchConversations(showLoader = true) {
            if (showLoader) this.isRefreshing = true;
            try {
                const response = await fetch('{{ route("api.conversations") }}');
                const data = await response.json();
                
                if (Array.isArray(data)) {
                    this.conversations = data;

                    // If a chat is currently open, update its message list seamlessly
                    if (this.selectedId) {
                        const current = data.find(c => c.id === this.selectedId);
                        if (current) {
                            const prevCount = this.selected ? this.selected.messages.length : 0;
                            this.selected = current;

                            if (current.messages.length > prevCount) {
                                this.$nextTick(() => this.scrollToBottom('smooth'));
                            }
                        }
                    }
                }
            } catch (err) {
                console.error('Failed to fetch WhatsApp conversations:', err);
            } finally {
                if (showLoader) this.isRefreshing = false;
            }
        },

        get filteredConversations() {
            let list = [...this.conversations];

            if (this.search) {
                const q = this.search.toLowerCase();
                list = list.filter(c => 
                    (c.contact_name && c.contact_name.toLowerCase().includes(q)) ||
                    (c.phone_number && c.phone_number.includes(q)) ||
                    (c.last_message && c.last_message.toLowerCase().includes(q))
                );
            }

            if (this.filterType === 'human') {
                list = list.filter(c => c.escalated_to_human);
            }

            return list;
        },

        selectConversation(conv) {
            this.selectedId = conv.id;
            this.selected = conv;
            this.replyText = '';
            this.$nextTick(() => {
                setTimeout(() => this.scrollToBottom('auto'), 50);
            });
        },

        insertQuickText(text) {
            this.replyText = text;
        },

        async sendMessage() {
            if (!this.replyText.trim() || this.sending || !this.selected) return;

            const text = this.replyText.trim();
            this.replyText = '';
            this.sending = true;

            // Optimistic message append
            const tempId = 'temp-' + Date.now();
            const optimistic = {
                id: tempId,
                role: 'assistant',
                source: 'human',
                content: text,
                created_at: new Date().toISOString(),
                status: 'sending'
            };

            this.selected.messages.push(optimistic);
            this.$nextTick(() => this.scrollToBottom('smooth'));

            try {
                const url = '{{ route("api.conversations.reply", ":id") }}'.replace(':id', this.selected.id);
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: text })
                });

                const json = await res.json();

                if (json.success) {
                    const idx = this.selected.messages.findIndex(m => m.id === tempId);
                    if (idx !== -1) {
                        this.selected.messages[idx] = { ...json.message, status: 'confirmed' };
                    }
                    this.selected.escalated_to_human = false;
                } else {
                    throw new Error(json.error || 'Failed to send message');
                }
            } catch (err) {
                console.error('Send error:', err);
                alert(err.message || 'Network error sending message.');
                this.selected.messages = this.selected.messages.filter(m => m.id !== tempId);
            } finally {
                this.sending = false;
                this.$nextTick(() => this.scrollToBottom('smooth'));
            }
        },

        async toggleEscalation() {
            if (!this.selected) return;

            try {
                const url = '{{ route("api.conversations.toggle-escalation", ":id") }}'.replace(':id', this.selected.id);
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await res.json();
                if (data.success) {
                    this.selected.escalated_to_human = data.escalated_to_human;
                    const found = this.conversations.find(c => c.id === this.selected.id);
                    if (found) found.escalated_to_human = data.escalated_to_human;
                }
            } catch (e) {
                console.error('Toggle escalation failed:', e);
            }
        },

        scrollToBottom(behavior = 'smooth') {
            const el = document.getElementById('messages-container');
            if (el) {
                el.scrollTo({ top: el.scrollHeight, behavior });
            }
        },

        formatWhatsAppTime(iso) {
            if (!iso) return '';
            const date = new Date(iso);
            const now = new Date();
            const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));

            if (diffDays === 0) {
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } else if (diffDays === 1) {
                return 'Yesterday';
            } else if (diffDays < 7) {
                return date.toLocaleDateString([], { weekday: 'short' });
            }
            return date.toLocaleDateString([], { month: 'numeric', day: 'numeric', year: '2-digit' });
        },

        formatBubbleTime(iso) {
            if (!iso) return '';
            const date = new Date(iso);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        getInitials(name) {
            if (!name) return 'W';
            const parts = name.trim().split(/\s+/);
            if (parts.length >= 2) {
                return (parts[0][0] + parts[1][0]).toUpperCase();
            }
            return name.substring(0, 2).toUpperCase();
        },

        getAvatarColor(str) {
            const colors = ['#00a884', '#0070e0', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6'];
            let hash = 0;
            for (let i = 0; i < (str || '').length; i++) {
                hash = str.charCodeAt(i) + ((hash << 5) - hash);
            }
            return colors[Math.abs(hash) % colors.length];
        },

        toggleAudio(btn) {
            const card = btn.closest('.flex');
            const audio = card.querySelector('audio');
            const playIcon = btn.querySelector('.play-icon');

            if (audio.paused) {
                audio.play();
                playIcon.innerHTML = '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>';
            } else {
                audio.pause();
                playIcon.innerHTML = '<path d="M8 5v14l11-7z"/>';
            }
        },

        updateAudioProgress(audio) {
            const card = audio.closest('.flex');
            const bar = card.querySelector('.bg-\\[\\#00a884\\]');
            const timeSpan = card.querySelector('.time-display');

            if (bar && audio.duration) {
                bar.style.width = (audio.currentTime / audio.duration * 100) + '%';
            }
            if (timeSpan) {
                const mins = Math.floor(audio.currentTime / 60);
                const secs = Math.floor(audio.currentTime % 60).toString().padStart(2, '0');
                timeSpan.textContent = `${mins}:${secs}`;
            }
        },

        resetAudio(audio) {
            const card = audio.closest('.flex');
            const playIcon = card.querySelector('.play-icon');
            const bar = card.querySelector('.bg-\\[\\#00a884\\]');
            const timeSpan = card.querySelector('.time-display');

            if (playIcon) playIcon.innerHTML = '<path d="M8 5v14l11-7z"/>';
            if (bar) bar.style.width = '0%';
            if (timeSpan) timeSpan.textContent = '0:00';
        }
    }
}
</script>
@endsection
