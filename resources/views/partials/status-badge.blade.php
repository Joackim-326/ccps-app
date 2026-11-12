@php $label = $challenge->statusLabelFor(auth()->user()); @endphp
<span class="badge {{ $challenge->status === 'attended' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $label }}</span>
