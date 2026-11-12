@extends('layouts.app')
@section('title', 'Chat')
@section('heading', 'Chat')
@section('content')
<div class="card grid h-[calc(100vh-9rem)] min-h-[28rem] overflow-hidden md:grid-cols-[20rem_1fr]">
    {{-- Contacts --}}
    <aside class="{{ $user ? 'hidden md:block' : '' }} overflow-y-auto border-r border-slate-100">
        <div class="border-b border-slate-100 px-4 py-3 text-sm font-semibold">
            {{ auth()->user()->isReceptionist() ? 'Officers & creators' : (auth()->user()->isOfficer() ? 'Receptionists & creators' : 'Receptionists & officers') }}
        </div>
        @forelse ($contacts as $c)
            <a href="{{ route('chat.index', $c) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 {{ $user?->id === $c->id ? 'bg-brand-50' : '' }}">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">{{ $c->initials }}</span>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold">{{ $c->name }}</div>
                    <div class="truncate text-xs text-slate-500 capitalize">{{ $notes[$c->id] ?? $c->role }}</div>
                </div>
                @if (($unread[$c->id] ?? 0) > 0)<span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-bold text-white">{{ $unread[$c->id] }}</span>@endif
            </a>
        @empty
            <p class="p-6 text-sm text-slate-500">There is nobody to chat with yet.</p>
        @endforelse
    </aside>

    {{-- Conversation --}}
    <section class="{{ $user ? '' : 'hidden md:flex' }} flex min-h-0 flex-col">
        @if (! $user)
            <div class="grid flex-1 place-items-center p-8 text-center text-slate-500">
                <div><div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-brand-50 text-2xl">💬</div>Select someone to start chatting.</div>
            </div>
        @else
            <header class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-3">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route('chat.index') }}" class="rounded-lg p-1 text-slate-500 hover:bg-slate-100 md:hidden">←</a>
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">{{ $user->initials }}</span>
                    <div class="min-w-0">
                        <div class="truncate font-semibold">{{ $user->name }} <span class="badge bg-slate-100 text-slate-600 capitalize">{{ $user->role }}</span></div>
                        <button type="button" data-toggle="contactPanel" class="text-xs font-medium text-brand-600 hover:underline">Contact details</button>
                    </div>
                </div>
                <div class="relative">
                    <button type="button" data-toggle="clearMenu" class="btn-ghost btn-sm">Clear chat ▾</button>
                    <div id="clearMenu" class="absolute right-0 z-10 mt-2 hidden w-52 rounded-xl bg-white p-1 text-sm shadow-lg ring-1 ring-slate-200">
                        @foreach (['me' => 'Clear for me', 'all' => 'Clear for everyone'] as $scope => $label)
                            <form method="POST" action="{{ route('chat.clear', $user) }}" onsubmit="return confirm('{{ $label }}? This cannot be undone.')">@csrf @method('DELETE')
                                <input type="hidden" name="scope" value="{{ $scope }}">
                                <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-slate-50 {{ $scope === 'all' ? 'text-rose-600' : '' }}">{{ $label }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </header>

            <div id="contactPanel" class="hidden space-y-3 border-b border-slate-100 bg-slate-50 px-4 py-3 text-sm">
                <dl class="grid gap-x-6 gap-y-1 sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">Contact</dt><dd class="font-medium">{{ $user->contact }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Alternative contact</dt><dd class="font-medium">{{ $user->alt_contact ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs text-slate-500">Email</dt><dd class="font-medium">{{ $user->email }}</dd></div>
                </dl>
                <form method="POST" action="{{ route('chat.note', $user) }}" class="flex gap-2">@csrf @method('PUT')
                    <input name="description" value="{{ $notes[$user->id] ?? '' }}" maxlength="500" placeholder="Add a description to this contact (only you see it)" class="input">
                    <button class="btn-primary btn-sm">Save</button>
                </form>
            </div>

            <div id="messages" class="flex-1 space-y-2 overflow-y-auto bg-slate-50/50 p-4"></div>

            <form id="sendForm" class="flex items-end gap-2 border-t border-slate-100 p-3">
                <textarea id="body" rows="1" maxlength="2000" required placeholder="Type a message…" class="input max-h-32 resize-none"></textarea>
                <button class="btn-primary">Send</button>
            </form>

            <script>
            (() => {
                const csrf = document.querySelector('meta[name=csrf-token]').content;
                const box = document.getElementById('messages');
                const form = document.getElementById('sendForm');
                const input = document.getElementById('body');
                const urls = { poll: @json(route('chat.poll', $user)), send: @json(route('chat.store', $user)) };
                const seen = new Set();
                let lastId = 0;

                const add = (m) => {
                    if (seen.has(m.id)) return;
                    seen.add(m.id); lastId = Math.max(lastId, m.id);
                    const row = document.createElement('div');
                    row.className = 'flex ' + (m.mine ? 'justify-end' : 'justify-start');
                    const bubble = document.createElement('div');
                    bubble.className = 'max-w-[80%] rounded-2xl px-3.5 py-2 text-sm shadow-sm ' + (m.mine ? 'rounded-br-md bg-brand-600 text-white' : 'rounded-bl-md bg-white ring-1 ring-slate-200');
                    const text = document.createElement('div'); text.className = 'whitespace-pre-wrap break-words'; text.textContent = m.body;
                    const time = document.createElement('div'); time.className = 'mt-1 text-[10px] opacity-70'; time.textContent = m.time;
                    bubble.append(text, time); row.append(bubble); box.append(row);
                };
                const scroll = () => { box.scrollTop = box.scrollHeight; };
                const atBottom = () => box.scrollHeight - box.scrollTop - box.clientHeight < 80;

                @json($messages).forEach(add); scroll();

                const poll = async () => {
                    if (document.hidden) return;
                    try {
                        const r = await fetch(urls.poll + '?after=' + lastId, { headers: { Accept: 'application/json' } });
                        if (!r.ok) return;
                        const { messages } = await r.json();
                        const stick = atBottom();
                        messages.forEach(add);
                        if (messages.length && stick) scroll();
                    } catch (_) {}
                };
                setInterval(poll, 2500);

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const body = input.value.trim(); if (!body) return;
                    input.value = '';
                    const r = await fetch(urls.send, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ body }),
                    });
                    if (r.ok) { add((await r.json()).message); scroll(); } else { input.value = body; alert('Message could not be sent.'); }
                });
                input.addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); } });
            })();
            </script>
        @endif
    </section>
</div>
@endsection
