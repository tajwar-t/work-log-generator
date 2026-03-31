@extends('layouts.app')
@section('title', 'View Log')
@section('content')

<div class="page-header">
    <div>
        <span class="log-type-badge {{ $log->log_type }} lg">
            {{ $log->log_type === 'day_start' ? '☀ Day Start' : '🌙 Day End' }}
        </span>
        <h1 class="page-title">{{ \Carbon\Carbon::parse($log->log_date)->format('l, d F Y') }}</h1>
    </div>
    <div style="display:flex;gap:0.75rem">
        <a href="{{ route('logs.edit', $log->id) }}" class="btn-secondary">Edit</a>
        <a href="{{ route('logs.index') }}" class="btn-ghost">← Back</a>
    </div>
</div>

<div class="view-layout">
    <div class="template-display">
        <div class="template-rendered">
            @php
                $sectionA = json_decode($log->section_a_items, true) ?? [];
                $sectionB = json_decode($log->section_b_items, true) ?? [];
                $date = \Carbon\Carbon::parse($log->log_date)->format('d/m/Y');
                $typeLabel = $log->log_type === 'day_start' ? 'Day Start' : 'Day End';
                $headerA = $log->log_type === 'day_start' ? '::: Last day I worked with :::' : '::: Today I worked with :::';
                $headerB = $log->log_type === 'day_start' ? ':::: Today I will work with ::::' : ':::: Tomorrow I will work with ::::';
                $sep = '— -- — -- — -- — -- — -- —';
            @endphp

            <div class="tpl-sep">{{ $sep }}</div>
            <div class="tpl-title">{{ $typeLabel }} {{ $date }}</div>
            <div class="tpl-sep">{{ $sep }}</div>
            <div class="tpl-header">{{ $headerA }}</div>
            @foreach($sectionA as $i => $item)
                <div class="tpl-item">{{ $i+1 }}. {{ $item }}</div>
            @endforeach
            <div class="tpl-sep">{{ $sep }}</div>
            <div class="tpl-header">{{ $headerB }}</div>
            @foreach($sectionB as $i => $item)
                <div class="tpl-item">{{ $i+1 }}. {{ $item }}</div>
            @endforeach
            <div class="tpl-sep">{{ $sep }}</div>
        </div>

        <div class="view-actions">
            <button class="btn-copy" onclick="copyFullTemplate()">Copy Template</button>
            <a href="{{ route('logs.edit', $log->id) }}" class="btn-secondary">Edit Log</a>
            <form method="POST" action="{{ route('logs.destroy', $log->id) }}" style="display:inline" onsubmit="return confirm('Delete this log?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">Delete</button>
            </form>
        </div>
    </div>

    <!-- Sidebar info -->
    <div class="view-sidebar">
        <div class="info-card">
            <h4>Log Details</h4>
            <div class="info-row">
                <span class="info-key">Type</span>
                <span class="info-val">{{ ucfirst(str_replace('_',' ',$log->log_type)) }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Date</span>
                <span class="info-val">{{ \Carbon\Carbon::parse($log->log_date)->format('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Section A items</span>
                <span class="info-val">{{ count($sectionA) }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Section B items</span>
                <span class="info-val">{{ count($sectionB) }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Created</span>
                <span class="info-val">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}</span>
            </div>
        </div>

        @if($relatedLog)
        <div class="info-card related-card">
            <h4>Related Log</h4>
            <p class="related-desc">
                {{ $log->log_type === 'day_start' ? 'Paired Day End' : 'Paired Day Start' }}
            </p>
            <a href="{{ route('logs.show', $relatedLog->id) }}" class="related-link">
                {{ \Carbon\Carbon::parse($relatedLog->log_date)->format('d M Y') }}
                <span>{{ $relatedLog->log_type === 'day_start' ? '☀' : '🌙' }}</span> →
            </a>
        </div>
        @endif
    </div>
</div>

<script>
const BOLD_MAP_S = {'A':'𝗔','B':'𝗕','C':'𝗖','D':'𝗗','E':'𝗘','F':'𝗙','G':'𝗚','H':'𝗛','I':'𝗜','J':'𝗝','K':'𝗞','L':'𝗟','M':'𝗠','N':'𝗡','O':'𝗢','P':'𝗣','Q':'𝗤','R':'𝗥','S':'𝗦','T':'𝗧','U':'𝗨','V':'𝗩','W':'𝗪','X':'𝗫','Y':'𝗬','Z':'𝗭','a':'𝗮','b':'𝗯','c':'𝗰','d':'𝗱','e':'𝗲','f':'𝗳','g':'𝗴','h':'𝗵','i':'𝗶','j':'𝗷','k':'𝗸','l':'𝗹','m':'𝗺','n':'𝗻','o':'𝗼','p':'𝗽','q':'𝗾','r':'𝗿','s':'𝘀','t':'𝘁','u':'𝘂','v':'𝘃','w':'𝘄','x':'𝘅','y':'𝘆','z':'𝘇','0':'𝟎','1':'𝟏','2':'𝟐','3':'𝟑','4':'𝟒','5':'𝟓','6':'𝟔','7':'𝟕','8':'𝟖','9':'𝟗'};
function toBoldS(str) { return [...str].map(ch => BOLD_MAP_S[ch] ?? ch).join(''); }
function copyFullTemplate() {
    const parts = [...document.querySelectorAll('.tpl-sep, .tpl-title, .tpl-header, .tpl-item')].map(el => {
        const cls = el.className;
        const text = el.textContent;
        return (cls === 'tpl-title' || cls === 'tpl-header') ? toBoldS(text) : text;
    }).join('\n');
    navigator.clipboard.writeText(parts).then(() => {
        const btn = event.target;
        btn.textContent = '✓ Copied!';
        setTimeout(() => btn.textContent = 'Copy Template', 2000);
    });
}
</script>
@endsection
