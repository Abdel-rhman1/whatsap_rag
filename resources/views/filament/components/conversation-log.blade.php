<div class="space-y-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-xl max-h-[600px] overflow-y-auto flex flex-col" style="direction: ltr;">
    @foreach($getState() ?? [] as $message)
        <div class="flex {{ $message->role === 'user' ? 'justify-start' : 'justify-end' }}">
            <div class="max-w-[75%] rounded-2xl px-4 py-2 shadow-sm
                {{ $message->role === 'user' 
                    ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-tl-none border border-gray-100 dark:border-gray-700' 
                    : ($message->source === 'human' 
                        ? 'bg-emerald-500 text-white rounded-tr-none' 
                        : 'bg-blue-600 text-white rounded-tr-none') }}">
                <div class="text-[10px] opacity-70 mb-1 flex items-center gap-2">
                    <span class="font-bold">
                        {{ match($message->role) {
                            'user' => 'USER',
                            'assistant' => ($message->source === 'human' ? 'HUMAN AGENT' : 'AI ASSISTANT'),
                            default => strtoupper($message->role)
                        } }}
                    </span>
                    <span>•</span>
                    <span>{{ $message->created_at->format('H:i') }}</span>
                    @if($message->role !== 'user')
                        <span class="ml-auto text-[8px] uppercase tracking-tighter text-white/60">
                            {{ $message->source }}
                        </span>
                    @endif
                </div>
                <div class="text-sm whitespace-pre-wrap {{ $message->role === 'user' ? 'text-left' : 'text-right' }}" 
                     style="{{ preg_match('/\p{Arabic}/u', $message->content) ? 'direction: rtl; text-align: right;' : 'direction: ltr; text-align: left;' }}">
                    {{ $message->content }}
                </div>
            </div>
        </div>
    @endforeach
</div>
