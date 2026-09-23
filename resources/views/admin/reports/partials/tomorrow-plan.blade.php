@php
    $planOptions = \App\Support\TomorrowPlan::options($code);
@endphp

<div>
    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">{{ $label ?? 'Rencana Besok' }}:</span>
    @if (! empty($data['tomorrow_activities']))
        <div class="space-y-3">
            @foreach ((array) $data['tomorrow_activities'] as $activity)
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="font-bold text-xs text-indigo-700 uppercase tracking-wide block mb-1">{{ $planOptions[$activity] ?? ucwords(str_replace('_', ' ', $activity)) }}</span>
                    <p class="text-slate-700 whitespace-pre-line">{{ $data['tomorrow_activity_details'][$activity] ?? '-' }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 whitespace-pre-line">{{ ($data['rencana_besok'] ?? '') ?: '-' }}</p>
    @endif
</div>
