{{-- vars: $challenges, $assignees (for reassign; empty = none), $compact?, $admin? --}}
@php $u = auth()->user(); $compact = $compact ?? false; $admin = $admin ?? false; @endphp
@if ($challenges->isEmpty())
    <div class="px-6 py-12 text-center text-sm text-slate-500">No challenges to show.</div>
@else
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50/70">
            <tr>
                <th class="th">Ref / Index</th><th class="th">Category</th>
                @unless ($compact)<th class="th">Description</th>@endunless
                <th class="th">{{ $u->isReceptionist() && ! $admin ? 'Submitted to' : 'Registered by' }}@if ($admin) / To @endif</th>
                <th class="th">Status</th>
                @unless ($compact)<th class="th">Date</th><th class="th text-right">Actions</th>@endunless
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        @foreach ($challenges as $c)
            <tr class="hover:bg-slate-50/60">
                <td class="td"><div class="font-semibold text-slate-900">{{ $c->index_number }}</div><div class="text-xs text-slate-500"></div></td>
                <td class="td max-w-[14rem]">{{ $c->category_label }}</td>
                @unless ($compact)<td class="td max-w-xs"><p class="line-clamp-3 whitespace-pre-line">{{ $c->description }}</p></td>@endunless
                <td class="td">
                    @if ($admin)
                        <div class="text-xs text-slate-500">From</div><div>{{ $c->creator?->name ?? '—' }}</div>
                        <div class="mt-1 text-xs text-slate-500">To</div><div>{{ $c->assignee?->name ?? '—' }}</div>
                    @elseif ($u->isReceptionist())
                        {{ $c->assignee?->name ?? '—' }}
                    @else
                        {{ $c->creator?->name ?? '—' }}
                    @endif
                </td>
                <td class="td">@include('partials.status-badge', ['challenge' => $c])</td>
                @unless ($compact)
                <td class="td whitespace-nowrap text-xs text-slate-500">{{ $c->created_at->format('d M Y H:i') }}@if ($c->attended_at)<br>Attended {{ $c->attended_at->format('d M Y H:i') }}@endif</td>
                <td class="td text-right">
                    @if ($c->status === 'in_progress')
                        @if ($assignees->isNotEmpty())
                            <form method="POST" action="{{ route('challenges.reassign', $c) }}" class="mb-2 flex items-center justify-end gap-2">@csrf
                                <select name="assigned_to" class="input !w-40 !py-1.5 text-xs">
                                    @foreach ($assignees as $a)<option value="{{ $a->id }}" @selected($c->assigned_to === $a->id)>{{ $a->name }} ({{ $a->role }})</option>@endforeach
                                </select>
                                <button class="btn-ghost btn-sm">Send</button>
                            </form>
                        @endif
                        @if ($c->assigned_to === $u->id && ! $u->isReceptionist())
                            <form method="POST" action="{{ route('challenges.attend', $c) }}" onsubmit="return confirm('Mark this challenge as attended?')">@csrf
                                <button class="btn-primary btn-sm">Mark attended</button>
                            </form>
                        @endif
                    @endif
                    @if ($admin)
                        <form method="POST" action="{{ route('admin.challenges.destroy', $c) }}" onsubmit="return confirm('Delete this challenge permanently?')" class="mt-2">@csrf @method('DELETE')
                            <button class="btn-danger btn-sm">Delete</button>
                        </form>
                    @endif
                </td>
                @endunless
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
