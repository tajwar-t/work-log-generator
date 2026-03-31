@extends('layouts.app')
@section('title', 'History')
@section('content')
<style>
    input[type="month"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
}
.saved-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.3rem 0.75rem; border-radius: 20px;
    font-size: 0.75rem; font-weight: 500;
    margin-bottom: 0.875rem;
    border: 1px solid;
}
.saved-badge-green {
    background: rgba(67,198,120,0.1);
    border-color: rgba(67,198,120,0.4);
    color: #43c678;
}
.saved-badge-dim {
    background: rgba(108,143,255,0.08);
    border-color: rgba(108,143,255,0.25);
    color: #9ca3b4;
}
/* ─── GENERATOR TOGGLE ─── */
.generator-toggle-bar {
    display: flex; align-items: center; justify-content: space-between;
    background: var(--bg-2); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 0.9rem 1.25rem;
    margin-bottom: 1.5rem; cursor: pointer; user-select: none;
    transition: border-color 0.2s, background 0.2s;
}
.generator-toggle-bar:hover { border-color: var(--accent); background: var(--bg-3); }
.generator-toggle-title { font-size: 1rem; font-weight: 600; color: var(--text); display: flex; align-items: center; gap: 0.6rem; }
.generator-toggle-sub { font-size: 0.78rem; color: var(--text-3); margin-top: 0.1rem; }
.generator-toggle-icon { font-size: 1rem; color: var(--text-3); transition: transform 0.3s; line-height: 1; }
.generator-toggle-icon.open { transform: rotate(180deg); }
.generator-collapse { overflow: hidden; max-height: 0; opacity: 0; transition: max-height 0.4s cubic-bezier(0.4,0,0.2,1), opacity 0.3s; margin-bottom: 0; }
.generator-collapse.open { max-height: 3000px; opacity: 1; margin-bottom: 2rem; }
</style>

{{-- Chat Panel --}}
<div id="chatPanel" class="chat-panel">
    <div class="chat-header">
        <div class="chat-header-user">
            <img id="chatAvatar" src="" alt="" class="chat-hdr-avatar">
            <div>
                <span id="chatName" class="chat-hdr-name"></span>
                <span id="chatJob"  class="chat-hdr-job"></span>
            </div>
        </div>
        <button class="chat-close-btn" onclick="closeChat()" title="Close">&#x2715;</button>
    </div>
    <div id="chatMessages" class="chat-messages"></div>
    <div class="chat-input-row">
        <textarea id="chatInput" class="chat-textarea" placeholder="Write a message&#x2026;" rows="1"
            onkeydown="handleChatKey(event)" oninput="autoResize(this)"></textarea>
        <button class="chat-send-btn" onclick="sendMessage()" id="chatSendBtn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
    </div>
</div>
<div id="chatOverlay" class="chat-overlay" onclick="closeChat()"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- LOG GENERATOR (collapsible, embedded at top of index)     --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div id="toastContainer" class="toaster-container"></div>

<div class="generator-toggle-bar" onclick="toggleGenerator()">
    <div>
        <h1 class="generator-toggle-title"><span>⚙</span> Work Log Generator</h1>
        <div class="generator-toggle-sub">Create your daily work log entry</div>
    </div>
    <span class="generator-toggle-icon open" id="generatorToggleIcon">▼</span>
</div>

<div class="generator-collapse open" id="generatorCollapse">
    <div class="generator-layout">

        <!-- LEFT: Input Panel -->
        <div class="input-panel">
            <div class="panel-card">

                <div class="control-row">
                    <div class="control-group">
                        <label class="ctrl-label">Template Type</label>
                        <div class="select-wrap">
                            <select id="logType" class="styled-select">
                                <option value="day_start">Day Start</option>
                                <option value="day_end">Day End</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="ctrl-label">Date</label>
                        <input type="date" id="logDate" class="styled-input" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div id="savedBadge" class="saved-badge" style="display:none"></div>
                <div class="smart-section">
                    <span class="smart-label">⚡ Smart Fill</span>
                    <div class="smart-chips">
                        <button type="button" class="chip" onclick="loadDayChip(1)">Yesterday's Work</button>
                        <button type="button" class="chip" onclick="loadDayChip(2)">2 Days Ago</button>
                        <button type="button" class="chip" onclick="loadDayChip(3)">3 Days Ago</button>
                    </div>
                </div>

                <div class="work-section">
                    <div class="section-header">
                        <span class="section-label" id="sectionALabel">Last Day Work</span>
                        <button type="button" class="btn-add" onclick="addItem('sectionA')">+ Add Item</button>
                    </div>
                    <div id="sectionA" class="items-list">
                        <div class="item-row">
                            <input type="text" class="item-input" placeholder="Enter work item">
                            <button type="button" class="btn-remove" onclick="removeItem(this)">🗑</button>
                        </div>
                    </div>
                </div>

                <div class="work-section">
                    <div class="section-header">
                        <span class="section-label" id="sectionBLabel">Today's Work</span>
                        <button type="button" class="btn-add btn-add-green" onclick="addItem('sectionB')">+ Add Item</button>
                    </div>
                    <div id="sectionB" class="items-list">
                        <div class="item-row">
                            <input type="text" class="item-input" placeholder="Enter work item">
                            <button type="button" class="btn-remove" onclick="removeItem(this)">🗑</button>
                        </div>
                    </div>
                </div>

                <div class="action-row">
                    <button type="button" class="btn-generate" onclick="generateTemplate()">
                        <span>⚙</span> Generate Template
                    </button>
                    <button type="button" class="btn-save" id="saveBtn" onclick="saveLog()">
                        <span>💾</span> Save Work Log
                    </button>
                </div>

            </div>
        </div>

        <!-- RIGHT: Output Panel -->
        <div class="output-panel">
            <div class="panel-card output-card">
                <h3 class="output-title">Generated Template</h3>
                <div id="outputArea" class="output-area empty">
                    <div class="output-empty">
                        <span class="empty-icon">◫</span>
                        <p>Your generated template will appear here</p>
                        <small>Fill in your work items and click "Generate Template"</small>
                    </div>
                </div>
                <div class="output-actions">
                    <button type="button" class="btn-copy" onclick="copyTemplate()" id="copyBtn" disabled>Copy Template</button>
                </div>
            </div>
        </div>

    </div>
</div>
{{-- ══════════════════════════════════════════════════════════ --}}

<div class="page-header" id="w-log-history">
    <div>
        <h2 class="page-title">Work Log History</h2>
        <p class="page-sub">All your daily entries in one place</p>
    </div>
    <a href="{{ route('logs.create') }}" class="btn-primary">+ New Log</a>
</div>

<div class="stats-bar">
    <div class="stat-card"><span class="stat-num">{{ $stats['total'] }}</span><span class="stat-label">Total Logs</span></div>
    <div class="stat-card"><span class="stat-num">{{ $stats['this_week'] }}</span><span class="stat-label">This Week</span></div>
    <div class="stat-card"><span class="stat-num">{{ $stats['day_starts'] }}</span><span class="stat-label">Day Starts</span></div>
    <div class="stat-card"><span class="stat-num">{{ $stats['day_ends'] }}</span><span class="stat-label">Day Ends</span></div>
    <div class="stat-card"><span class="stat-num">{{ $stats['streak'] }}</span><span class="stat-label">Day Streak &#x1F525;</span></div>
</div>

<div class="filter-bar">
    <form method="GET" action="{{ route('logs.index') }}" class="filter-form">
        <select name="type" class="styled-select sm" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="day_start" {{ request('type') == 'day_start' ? 'selected' : '' }}>Day Start</option>
            <option value="day_end"   {{ request('type') == 'day_end'   ? 'selected' : '' }}>Day End</option>
        </select>
        <input type="month" name="month" class="styled-input sm" value="{{ request('month') }}" onchange="this.form.submit()">
        <input type="text"  name="search" class="styled-input sm" placeholder="Search entries&#x2026;" value="{{ request('search') }}">
        <button type="submit" class="btn-filter">Search</button>
        @if(request()->hasAny(['type','month','search']))
            <a href="{{ route('logs.index') }}" class="btn-clear">&#x2715; Clear</a>
        @endif
    </form>
</div>

@if($logs->isEmpty())
    <div class="empty-state">
        <span class="empty-big-icon">&#x25FB;</span>
        <h3>No logs yet</h3>
        <p>Start by creating your first work log entry</p>
        <a href="{{ route('logs.create') }}" class="btn-primary">Create First Log &#x2192;</a>
    </div>
@else
    <div class="logs-grid">
        @foreach($logs as $log)
        <div class="log-card {{ $log->log_type }}">
            <div class="log-card-header">
                <div class="log-meta">
                    <span class="log-type-badge {{ $log->log_type }}">
                        {{ $log->log_type === 'day_start' ? '☀ Day Start' : '🌙 Day End' }}
                    </span>
                     <span class="log-date">{{ \Carbon\Carbon::parse($log->log_date)->format('D, d M Y') }}</span>
                </div>
                <div class="log-actions">
                    <a href="{{ route('logs.show', $log->id) }}" class="action-btn view">View</a>
                    <a href="{{ route('logs.edit', $log->id) }}" class="action-btn edit">Edit</a>
                    <form method="POST" action="{{ route('logs.destroy', $log->id) }}" style="display:inline" onsubmit="return confirm('Delete this log?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn delete">Delete</button>
                    </form>
                </div>
            </div>
            <div class="log-preview">
                @php $sectionA = json_decode($log->section_a_items, true) ?? []; @endphp
                @if(count($sectionA))
                <div class="preview-section">
                    <span class="preview-label">{{ $log->log_type === 'day_start' ? 'Last Day' : 'Today' }}</span>
                    <ul class="preview-list">
                        @foreach(array_slice($sectionA, 0, 3) as $item)<li>{{ $item }}</li>@endforeach
                        @if(count($sectionA) > 3)<li class="preview-more">+{{ count($sectionA) - 3 }} more</li>@endif
                    </ul>
                </div>
                @endif
            </div>
            <div class="log-card-footer">
                <span class="log-items-count">{{ count(json_decode($log->section_a_items,true)??[]) + count(json_decode($log->section_b_items,true)??[]) }} items total</span>
                <span class="log-ago">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</span>
            </div>
        </div>
        @endforeach
    </div>
    <div class="pagination-wrap">{{ $logs->withQueryString()->links('vendor.pagination.custom') }}</div>
@endif

<!-- TEAM SECTION -->
<div class="team-section" id="team-section">
    <div class="team-section-header">
        <div>
            <h2 class="team-title">Team</h2>
            <p class="team-sub">All users on WorkLog &#x2014; click to start a conversation</p>
        </div>
        <input type="text" id="userSearch" class="styled-input sm" placeholder="Search users&#x2026;" oninput="filterUsers(this.value)" style="max-width:200px">
    </div>
    <div id="usersGrid" class="users-grid">
        <div class="users-loading">Loading team&#x2026;</div>
    </div>
</div>


<!-- GROUP CHAT SECTION -->
<div class="group-chat-section" id="group-chat-section">
    <div class="group-chat-header-row">
        <div>
            <h2 class="team-title">&#x1F4AC; Group Chat</h2>
            <p class="team-sub">Everyone on WorkLog &#x2014; messages visible to all members</p>
        </div>
        <div class="group-online-wrap" id="groupOnlineWrap"></div>
    </div>

    <div class="group-chat-layout">
        <!-- Messages feed -->
        <div class="group-messages-box" id="groupMessages">
            <div class="chat-loading" id="groupLoading">
                <span class="chat-loading-dot"></span>
                <span class="chat-loading-dot"></span>
                <span class="chat-loading-dot"></span>
            </div>
        </div>

        <!-- Input row -->
        <div class="group-input-row">
            <img src="{{ Auth::user()->avatar_url }}" alt="me" class="group-my-avatar">
            <textarea
                id="groupInput"
                class="chat-textarea group-textarea"
                placeholder="Send a message to the group&#x2026;"
                rows="1"
                onkeydown="handleGroupKey(event)"
                oninput="autoResizeGroup(this)"
            ></textarea>
            <button class="chat-send-btn" onclick="sendGroupMessage()" id="groupSendBtn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF  = document.querySelector('meta[name="csrf-token"]').content;
const MY_ID = {{ Auth::id() }};

// ══════════════════════════════════════════════════════════════
// LOG GENERATOR
// ══════════════════════════════════════════════════════════════

// ---- TOGGLE ----
function toggleGenerator() {
    const collapse = document.getElementById('generatorCollapse');
    const icon     = document.getElementById('generatorToggleIcon');
    const isOpen   = collapse.classList.toggle('open');
    icon.classList.toggle('open', isOpen);
}

// ---- LABELS ----
const labels = {
    day_start: {
        a: 'Last Day Work',  b: "Today's Work",
        aHeader: '::: Last day I worked with :::', bHeader: ':::: Today I will work with ::::', title: 'Day Start'
    },
    day_end: {
        a: "Today's Work", b: "Tomorrow's Work",
        aHeader: '::: Today I worked with :::', bHeader: ':::: Tomorrow I will work with ::::', title: 'Day End'
    }
};

function getType() { return document.getElementById('logType').value; }
function getDate() { return document.getElementById('logDate').value; }

function updateLabels() {
    const t = getType();
    document.getElementById('sectionALabel').textContent = labels[t].a;
    document.getElementById('sectionBLabel').textContent = labels[t].b;
}
document.getElementById('logType').addEventListener('change', () => { updateLabels(); runSmartFill(); });
document.getElementById('logDate').addEventListener('change', runSmartFill);
updateLabels();

// ---- ROW HELPERS ----
function makeRow(value = '') {
    const div = document.createElement('div');
    div.className = 'item-row';
    div.draggable = true;
    div.innerHTML = `
        <span class="drag-handle" title="Drag to reorder">&#8942;&#8942;</span>
        <span class="item-num">1</span>
        <input type="text" class="item-input" placeholder="Enter work item" value="${value.replace(/"/g, '&quot;')}">
        <button type="button" class="btn-remove" onclick="removeItem(this)">🗑</button>`;
    attachDragEvents(div);
    return div;
}

function addItem(sectionId, value = '') {
    const section = document.getElementById(sectionId);
    const div = makeRow(value);
    section.appendChild(div);
    reNumber(section);
    div.querySelector('input').focus();
}

function removeItem(btn) {
    const row = btn.closest('.item-row');
    const section = row.closest('.items-list');
    if (section.querySelectorAll('.item-row').length > 1) {
        row.style.opacity = '0';
        row.style.transform = 'translateX(20px)';
        row.style.transition = 'opacity 0.2s, transform 0.2s';
        setTimeout(() => { row.remove(); reNumber(section); }, 200);
    } else {
        row.querySelector('input').value = '';
    }
}

function getItems(sectionId) {
    return [...document.querySelectorAll(`#${sectionId} .item-input`)]
        .map(i => i.value.trim()).filter(Boolean);
}

function setItems(sectionId, items) {
    const section = document.getElementById(sectionId);
    section.innerHTML = '';
    if (!items || items.length === 0) items = [''];
    items.forEach(item => section.appendChild(makeRow(item)));
    reNumber(section);
}

// ---- SMART FILL: auto on page load / type+date change ----
function runSmartFill() {
    const type = getType();
    const date = getDate();
    if (!date) return;
 
    fetch(`/api/smart-fill?type=${type}&date=${date}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        setItems('sectionA', data.section_a && data.section_a.length ? data.section_a : []);
        setItems('sectionB', data.section_b && data.section_b.length ? data.section_b : []);
 
        // Show badge: where did this data come from?
        const badge = document.getElementById('savedBadge');
        if (!badge) return;
        if (data.from_saved) {
            badge.textContent = '✓ Loaded your saved log';
            badge.className = 'saved-badge saved-badge-green';
            badge.style.display = 'inline-flex';
        } else if ((data.section_a && data.section_a.length) || (data.section_b && data.section_b.length)) {
            badge.textContent = '⟳ Auto-filled from previous log';
            badge.className = 'saved-badge saved-badge-dim';
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }
    })
    .catch(() => {});
}

// ---- SMART FILL CHIPS: Yesterday / 2 days ago / 3 days ago ----
function loadDayChip(daysAgo) {
    const type = getType();
    const date = getDate();
    const chip = document.querySelectorAll('.chip')[daysAgo - 1];

    chip.classList.add('chip-loading');
    chip.disabled = true;

    fetch(`/api/fetch-day?days_ago=${daysAgo}&type=${type}&date=${date}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        chip.classList.remove('chip-loading');
        chip.disabled = false;
        if (!data.found) {
            showToast(`No ${type === 'day_start' ? 'Day Start' : 'Day End'} log found for ${data.date}`, 'error');
            return;
        }
        setItems('sectionA', data.section_a && data.section_a.length ? data.section_a : []);
        setItems('sectionB', data.section_b && data.section_b.length ? data.section_b : []);
        showToast('Loaded entries from ' + data.date, 'success');
    })
    .catch(() => {
        chip.classList.remove('chip-loading');
        chip.disabled = false;
        showToast('Could not fetch entries', 'error');
    });
}

// ---- GENERATE TEMPLATE ----
function generateTemplate() {
    const type    = getType();
    const dateVal = getDate();
    const date    = dateVal ? new Date(dateVal).toLocaleDateString('en-GB', {day:'2-digit',month:'2-digit',year:'numeric'}) : 'N/A';
    const itemsA  = getItems('sectionA');
    const itemsB  = getItems('sectionB');
    const lbl     = labels[type];
    const SEP     = '— -- — -- — -- — -- — -- —';

    const lines = [
        SEP,
        `**${lbl.title} ${date}**`,
        SEP,
        `**${lbl.aHeader}**`,
        ...itemsA.map((item, i) => `${i+1}. ${item}`),
        SEP,
        `**${lbl.bHeader}**`,
        ...itemsB.map((item, i) => `${i+1}. ${item}`),
        SEP,
    ];

    const text = lines.join('\n');
    renderOutput(text);
    document.getElementById('copyBtn').disabled = false;
    return text;
}

function renderOutput(text) {
    const area = document.getElementById('outputArea');
    area.classList.remove('empty');
    area.innerHTML = text.split('\n').map(line =>
        line.startsWith('**') && line.endsWith('**')
            ? `<div class="out-bold">${line.slice(2,-2)}</div>`
            : `<div class="out-line">${line}</div>`
    ).join('');
}

// ---- SAVE (AJAX — stays on same page) ----
function saveLog() {
    const text   = generateTemplate();
    const type   = getType();
    const date   = getDate();
    const itemsA = getItems('sectionA');
    const itemsB = getItems('sectionB');

    const saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span>⏳</span> Saving…';

    const body = new URLSearchParams({
        _token:           CSRF,
        log_type:         type,
        log_date:         date,
        section_a_items:  JSON.stringify(itemsA),
        section_b_items:  JSON.stringify(itemsB),
        generated_text:   text,
    });

    fetch('{{ route('logs.store') }}', {
        method:  'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    body,
    })
    .then(r => r.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<span>💾</span> Save Work Log';
        if (data.success) {
            showToast(data.message ?? 'Saved successfully!', 'success');
            //setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Save failed. Please try again.', 'error');
        }
    })
    .catch(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<span>💾</span> Save Work Log';
        showToast('Network error. Please try again.', 'error');
    });
}

// ---- COPY ----
const BOLD_MAP = {'A':'𝗔','B':'𝗕','C':'𝗖','D':'𝗗','E':'𝗘','F':'𝗙','G':'𝗚','H':'𝗛','I':'𝗜','J':'𝗝','K':'𝗞','L':'𝗟','M':'𝗠','N':'𝗡','O':'𝗢','P':'𝗣','Q':'𝗤','R':'𝗥','S':'𝗦','T':'𝗧','U':'𝗨','V':'𝗩','W':'𝗪','X':'𝗫','Y':'𝗬','Z':'𝗭','a':'𝗮','b':'𝗯','c':'𝗰','d':'𝗱','e':'𝗲','f':'𝗳','g':'𝗴','h':'𝗵','i':'𝗶','j':'𝗷','k':'𝗸','l':'𝗹','m':'𝗺','n':'𝗻','o':'𝗼','p':'𝗽','q':'𝗾','r':'𝗿','s':'𝘀','t':'𝘁','u':'𝘂','v':'𝘃','w':'𝘄','x':'𝘅','y':'𝘆','z':'𝘇','0':'𝟎','1':'𝟏','2':'𝟐','3':'𝟑','4':'𝟒','5':'𝟓','6':'𝟔','7':'𝟕','8':'𝟖','9':'𝟗'};
function toBoldUnicode(str) { return [...str].map(ch => BOLD_MAP[ch] ?? ch).join(''); }
function copyTemplate() {
    const stored = document.getElementById('outputArea').innerText;
    const lines  = [...document.querySelectorAll('.out-bold, .out-line')].map(el => {
        return el.classList.contains('out-bold') ? toBoldUnicode(el.textContent) : el.textContent;
    }).join('\n');
    if (!lines.trim()) return;
    navigator.clipboard.writeText(lines).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.textContent = '✓ Copied!';
        btn.classList.add('copied');
        setTimeout(() => { btn.textContent = 'Copy Template'; btn.classList.remove('copied'); }, 2000);
    });
}

// ---- TOASTER ----
function showToast(msg, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toaster-item toaster-${type}`;
    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
    toast.innerHTML = `
        <span class="toaster-icon">${icon}</span>
        <span class="toaster-msg">${msg}</span>
        <button class="toaster-close" onclick="this.closest('.toaster-item').remove()">×</button>
    `;
    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('toaster-show'));
    setTimeout(() => {
        toast.classList.remove('toaster-show');
        toast.classList.add('toaster-hide');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

// ---- DRAG & DROP ----
let dragSrc = null;

function reNumber(section) {
    section.querySelectorAll('.item-num').forEach((el, i) => el.textContent = i + 1);
}

function attachDragEvents(row) {
    row.addEventListener('dragstart', e => {
        dragSrc = row; row.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', '');
    });
    row.addEventListener('dragend', () => {
        row.classList.remove('dragging');
        document.querySelectorAll('.item-row').forEach(r => r.classList.remove('drag-over'));
        dragSrc = null;
    });
    row.addEventListener('dragover', e => {
        e.preventDefault(); e.dataTransfer.dropEffect = 'move';
        if (dragSrc && dragSrc !== row) {
            document.querySelectorAll('.item-row').forEach(r => r.classList.remove('drag-over'));
            row.classList.add('drag-over');
        }
    });
    row.addEventListener('dragleave', () => row.classList.remove('drag-over'));
    row.addEventListener('drop', e => {
        e.preventDefault(); row.classList.remove('drag-over');
        if (!dragSrc || dragSrc === row) return;
        const section = row.closest('.items-list');
        const rows    = [...section.querySelectorAll('.item-row')];
        const srcIdx  = rows.indexOf(dragSrc);
        const tgtIdx  = rows.indexOf(row);
        if (srcIdx < tgtIdx) section.insertBefore(dragSrc, row.nextSibling);
        else                  section.insertBefore(dragSrc, row);
        reNumber(section);
        dragSrc.classList.add('drop-flash');
        setTimeout(() => dragSrc && dragSrc.classList.remove('drop-flash'), 350);
    });
    row.querySelector('.drag-handle').addEventListener('touchstart', e => {
        dragSrc = row; row.classList.add('dragging'); e.preventDefault();
    }, { passive: false });
    row.querySelector('.drag-handle').addEventListener('touchmove', e => {
        if (!dragSrc) return; e.preventDefault();
        const t = e.touches[0];
        const el = document.elementFromPoint(t.clientX, t.clientY);
        const target = el ? el.closest('.item-row') : null;
        document.querySelectorAll('.item-row').forEach(r => r.classList.remove('drag-over'));
        if (target && target !== dragSrc) target.classList.add('drag-over');
    }, { passive: false });
    row.querySelector('.drag-handle').addEventListener('touchend', e => {
        if (!dragSrc) return;
        dragSrc.classList.remove('dragging');
        const t = e.changedTouches[0];
        const el = document.elementFromPoint(t.clientX, t.clientY);
        const target = el ? el.closest('.item-row') : null;
        document.querySelectorAll('.item-row').forEach(r => r.classList.remove('drag-over'));
        if (target && target !== dragSrc) {
            const section = target.closest('.items-list');
            const rows    = [...section.querySelectorAll('.item-row')];
            if (rows.indexOf(dragSrc) < rows.indexOf(target)) section.insertBefore(dragSrc, target.nextSibling);
            else section.insertBefore(dragSrc, target);
            reNumber(section);
            dragSrc.classList.add('drop-flash');
            setTimeout(() => dragSrc && dragSrc.classList.remove('drop-flash'), 350);
        }
        dragSrc = null;
    });
}

// ---- INIT GENERATOR ----
document.querySelectorAll('.items-list').forEach(section => {
    section.querySelectorAll('.item-row').forEach(row => {
        row.draggable = true;
        if (!row.querySelector('.drag-handle')) {
            const h = document.createElement('span');
            h.className = 'drag-handle'; h.title = 'Drag to reorder'; h.innerHTML = '&#8942;&#8942;';
            const n = document.createElement('span'); n.className = 'item-num';
            row.insertBefore(n, row.firstChild);
            row.insertBefore(h, row.firstChild);
        }
        attachDragEvents(row);
    });
    reNumber(section);
});
runSmartFill();

// ══════════════════════════════════════════════════════════════
// END GENERATOR
// ══════════════════════════════════════════════════════════════

// ─── USER LIST ───────────────────────────────────────────────
let allUsers = [];

function loadUsers() {
    fetch('/api/chat/users', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(users => { allUsers = users; renderUsers(users); });
}

function renderUsers(users) {
    const grid = document.getElementById('usersGrid');
    if (!users.length) {
        grid.innerHTML = '<div class="users-empty">No other users yet.</div>';
        return;
    }
    grid.innerHTML = users.map(u => `
        <div class="user-card" onclick="openChat(${u.id})" data-name="${u.name.toLowerCase()}" data-email="${u.email.toLowerCase()}">
            <div class="user-card-avatar-wrap">
                <img src="${u.avatar_url}" alt="${escHtml(u.name)}" class="user-card-avatar">
                ${u.unread > 0 ? `<span class="user-unread-badge">${u.unread > 9 ? '9+' : u.unread}</span>` : ''}
            </div>
            <div class="user-card-info">
                <span class="user-card-name">${escHtml(u.name)}</span>
                <span class="user-card-job">${escHtml(u.job_title || u.email)}</span>
                ${u.last_message
                    ? `<span class="user-card-last">${u.last_message.is_mine ? '<span class="last-mine">You: </span>' : ''}${escHtml(truncate(u.last_message.body, 38))}<span class="last-time">${u.last_message.created_at}</span></span>`
                    : '<span class="user-card-last user-card-new">Start a conversation</span>'}
            </div>
            <div class="user-card-arrow">&#x203A;</div>
        </div>`).join('');
}

function filterUsers(q) {
    q = q.toLowerCase();
    renderUsers(q ? allUsers.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)) : allUsers);
}

// ─── CHAT PANEL ──────────────────────────────────────────────
let activeChatUserId = null;
let pollInterval     = null;
let lastMessageId    = 0;

function openChat(userId) {
    activeChatUserId = userId;
    lastMessageId    = 0;

    document.getElementById('chatPanel').classList.add('open');
    document.getElementById('chatOverlay').classList.add('open');
    document.getElementById('chatMessages').innerHTML = `
        <div class="chat-loading">
            <span class="chat-loading-dot"></span>
            <span class="chat-loading-dot"></span>
            <span class="chat-loading-dot"></span>
        </div>`;

    fetch(`/api/chat/conversation/${userId}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            document.getElementById('chatAvatar').src        = data.user.avatar_url;
            document.getElementById('chatName').textContent  = data.user.name;
            document.getElementById('chatJob').textContent   = data.user.job_title || '';
            renderMessages(data.messages);
            if (data.messages.length) lastMessageId = data.messages[data.messages.length - 1].id;
            scrollToBottom();
            document.getElementById('chatInput').focus();
            clearInterval(pollInterval);
            pollInterval = setInterval(pollMessages, 3000);
            loadUsers();
        });
}

function closeChat() {
    activeChatUserId = null;
    clearInterval(pollInterval);
    document.getElementById('chatPanel').classList.remove('open');
    document.getElementById('chatOverlay').classList.remove('open');
    loadUsers();
}

function renderMessages(messages) {
    const c = document.getElementById('chatMessages');
    c.innerHTML = '';
    if (!messages.length) {
        c.innerHTML = '<div class="chat-empty-hint">No messages yet. Say hello!</div>';
        return;
    }
    let lastDate = null;
    messages.forEach(m => {
        if (m.date !== lastDate) {
            const sep = document.createElement('div');
            sep.className = 'chat-date-sep';
            sep.textContent = m.date;
            c.appendChild(sep);
            lastDate = m.date;
        }
        c.appendChild(makeBubble(m));
    });
}

function appendMessage(m) {
    const c = document.getElementById('chatMessages');
    const empty = c.querySelector('.chat-empty-hint');
    if (empty) empty.remove();
    c.appendChild(makeBubble(m));
    scrollToBottom();
}

function makeBubble(m) {
    const wrap = document.createElement('div');
    wrap.className = `chat-bubble-wrap ${m.is_mine ? 'mine' : 'theirs'}`;
    wrap.dataset.id = m.id;
    wrap.innerHTML = `
        <div class="chat-bubble">${escHtml(m.body).replace(/\n/g,'<br>')}</div>
        <span class="chat-time">${m.created_at}${m.is_mine ? (m.read_at ? ' &#x2713;&#x2713;' : ' &#x2713;') : ''}</span>`;
    return wrap;
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const body  = input.value.trim();
    if (!body || !activeChatUserId) return;
    input.value = '';
    autoResize(input);
    const btn = document.getElementById('chatSendBtn');
    btn.disabled = true;

    // Optimistic bubble
    appendMessage({ id: 'opt_' + Date.now(), body, is_mine: true, read_at: null,
        created_at: new Date().toTimeString().slice(0,5),
        date: new Date().toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) });

    fetch('/api/chat/send', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF },
        body: new URLSearchParams({ receiver_id: activeChatUserId, body })
    }).then(r => r.json()).then(data => {
        btn.disabled = false;
        if (data.id) {
            const opt = document.querySelector('.chat-bubble-wrap.mine:last-child');
            if (opt) opt.replaceWith(makeBubble(data));
            lastMessageId = Math.max(lastMessageId, data.id);
        }
    }).catch(() => { btn.disabled = false; });
}

function pollMessages() {
    if (!activeChatUserId) return;
    fetch(`/api/chat/poll/${activeChatUserId}?last_id=${lastMessageId}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            data.messages.forEach(m => { appendMessage(m); lastMessageId = Math.max(lastMessageId, m.id); });
            updateNavUnread(data.total_unread);
        });
}

function scrollToBottom() {
    const c = document.getElementById('chatMessages');
    c.scrollTop = c.scrollHeight;
}

function handleChatKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// ─── NAV UNREAD BADGE ────────────────────────────────────────
function updateNavUnread(count) {
    let badge = document.getElementById('navUnreadBadge');
    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.id = 'navUnreadBadge';
            badge.className = 'nav-unread-badge';
            const btn = document.querySelector('.nav-avatar-btn');
            if (btn) btn.appendChild(badge);
        }
        badge.textContent = count > 99 ? '99+' : count;
    } else if (badge) { badge.remove(); }
}

function pollNavUnread() {
    fetch('/api/chat/unread', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json()).then(d => updateNavUnread(d.count));
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function truncate(s, n) { return s.length > n ? s.slice(0,n) + '\u2026' : s; }

loadUsers();
pollNavUnread();

// ─── GROUP CHAT ──────────────────────────────────────────────
let groupLastId   = 0;
let groupPollInt  = null;

function loadGroupMessages() {
    fetch('/api/group/messages', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(msgs => {
            renderGroupMessages(msgs);
            if (msgs.length) groupLastId = msgs[msgs.length - 1].id;
            scrollGroupToBottom();
            // Start polling
            clearInterval(groupPollInt);
            groupPollInt = setInterval(pollGroupMessages, 4000);
        });
}

function renderGroupMessages(msgs) {
    const box = document.getElementById('groupMessages');
    box.innerHTML = '';
    if (!msgs.length) {
        box.innerHTML = '<div class="chat-empty-hint">No messages yet. Say something to the team!</div>';
        return;
    }
    let lastDate = null;
    msgs.forEach(m => {
        if (m.date !== lastDate) {
            const sep = document.createElement('div');
            sep.className = 'chat-date-sep';
            sep.textContent = m.date;
            box.appendChild(sep);
            lastDate = m.date;
        }
        box.appendChild(makeGroupBubble(m));
    });
}

function makeGroupBubble(m) {
    const wrap = document.createElement('div');
    wrap.className = 'group-msg-wrap' + (m.is_mine ? ' group-mine' : ' group-theirs');
    wrap.dataset.id = m.id;

    if (m.is_mine) {
        wrap.innerHTML = `
            <div class="group-msg-body-wrap">
                <div class="group-bubble mine-bubble">${escHtml(m.body).replace(/\n/g,'<br>')}</div>
                <span class="chat-time group-time">${m.created_at}</span>
            </div>
            <img src="${m.avatar_url}" class="group-avatar-sm" title="${escHtml(m.user_name)}">
        `;
    } else {
        wrap.innerHTML = `
            <img src="${m.avatar_url}" class="group-avatar-sm" title="${escHtml(m.user_name)}">
            <div class="group-msg-body-wrap">
                <span class="group-sender-name">${escHtml(m.user_name)}</span>
                <div class="group-bubble theirs-bubble">${escHtml(m.body).replace(/\n/g,'<br>')}</div>
                <span class="chat-time group-time">${m.created_at}</span>
            </div>
        `;
    }
    return wrap;
}

function appendGroupMessage(m) {
    const box = document.getElementById('groupMessages');
    const empty = box.querySelector('.chat-empty-hint');
    if (empty) empty.remove();
    // Date sep if needed
    const lastSep = box.querySelector('.chat-date-sep:last-of-type');
    if (!lastSep || lastSep.textContent !== m.date) {
        const sep = document.createElement('div');
        sep.className = 'chat-date-sep';
        sep.textContent = m.date;
        box.appendChild(sep);
    }
    box.appendChild(makeGroupBubble(m));
    scrollGroupToBottom();
}

function sendGroupMessage() {
    const input = document.getElementById('groupInput');
    const body  = input.value.trim();
    if (!body) return;
    input.value = '';
    autoResizeGroup(input);

    const btn = document.getElementById('groupSendBtn');
    btn.disabled = true;

    // Optimistic
    const opt = {
        id: 'gopt_' + Date.now(), body, is_mine: true,
        user_name: 'You', avatar_url: document.querySelector('.group-my-avatar').src,
        created_at: new Date().toTimeString().slice(0,5),
        date: new Date().toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'})
    };
    appendGroupMessage(opt);

    fetch('/api/group/send', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF },
        body: new URLSearchParams({ body })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.id) {
            const opt = document.querySelector('.group-mine:last-child');
            if (opt) opt.replaceWith(makeGroupBubble(data));
            groupLastId = Math.max(groupLastId, data.id);
        }
    })
    .catch(() => { btn.disabled = false; });
}

function pollGroupMessages() {
    fetch(`/api/group/poll?last_id=${groupLastId}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(msgs => {
            msgs.forEach(m => {
                appendGroupMessage(m);
                groupLastId = Math.max(groupLastId, m.id);
            });
        });
}

function scrollGroupToBottom() {
    const box = document.getElementById('groupMessages');
    box.scrollTop = box.scrollHeight;
}

function handleGroupKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendGroupMessage(); }
}

function autoResizeGroup(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

loadGroupMessages();

setInterval(pollNavUnread, 15000);
</script>

<style>
/* TEAM SECTION */
.team-section { margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border); }
.team-section-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
.team-title { font-family:var(--font-display); font-size:1.4rem; font-weight:800; letter-spacing:-0.03em; color:var(--text); }
.team-sub { font-size:0.85rem; color:var(--text-3); margin-top:0.2rem; }

.users-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:0.875rem; }
.users-loading, .users-empty { grid-column:1/-1; text-align:center; color:var(--text-3); font-size:0.875rem; padding:2rem; }

.user-card {
    display:flex; align-items:center; gap:0.875rem;
    background:var(--bg-2); border:1px solid var(--border);
    border-radius:var(--radius-lg); padding:1rem; cursor:pointer;
    transition:all 0.18s; position:relative; overflow:hidden;
}
.user-card::before { content:''; position:absolute; inset:0; background:linear-gradient(135deg, var(--accent-glow), transparent); opacity:0; transition:opacity 0.2s; }
.user-card:hover { border-color:var(--accent); transform:translateY(-2px); box-shadow:var(--shadow); }
.user-card:hover::before { opacity:1; }
.user-card-avatar-wrap { position:relative; flex-shrink:0; }
.user-card-avatar { width:48px; height:48px; border-radius:50%; object-fit:cover; border:2px solid var(--border-2); transition:border-color 0.2s; }
.user-card:hover .user-card-avatar { border-color:var(--accent); }
.user-unread-badge { position:absolute; top:-4px; right:-4px; background:var(--red); color:#fff; font-size:0.62rem; font-weight:700; min-width:18px; height:18px; border-radius:9px; display:flex; align-items:center; justify-content:center; padding:0 4px; border:2px solid var(--bg-2); font-family:var(--font-mono); }
.user-card-info { flex:1; min-width:0; display:flex; flex-direction:column; gap:0.15rem; }
.user-card-name { font-size:0.9rem; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.user-card-job  { font-size:0.75rem; color:var(--accent); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.user-card-last { font-size:0.72rem; color:var(--text-3); display:flex; gap:0.3rem; align-items:baseline; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; }
.user-card-new  { color:var(--accent) !important; font-style:italic; }
.last-mine { color:var(--text-2); font-weight:500; }
.last-time  { margin-left:auto; flex-shrink:0; color:var(--text-3); font-size:0.68rem; }
.user-card-arrow { color:var(--text-3); font-size:1.3rem; flex-shrink:0; transition:color 0.2s, transform 0.2s; }
.user-card:hover .user-card-arrow { color:var(--accent); transform:translateX(3px); }

/* CHAT PANEL */
.chat-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:299; backdrop-filter:blur(2px); opacity:0; transition:opacity 0.25s; }
.chat-overlay.open { display:block; opacity:1; }
.chat-panel { position:fixed; right:0; top:0; bottom:0; width:380px; max-width:100vw; background:var(--bg-2); border-left:1px solid var(--border-2); display:flex; flex-direction:column; z-index:300; transform:translateX(100%); transition:transform 0.32s cubic-bezier(0.4,0,0.2,1); box-shadow:-8px 0 40px rgba(0,0,0,0.5); }
.chat-panel.open { transform:translateX(0); }
.chat-header { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid var(--border); background:var(--bg-3); flex-shrink:0; }
.chat-header-user { display:flex; align-items:center; gap:0.75rem; }
.chat-hdr-avatar { width:38px; height:38px; border-radius:50%; object-fit:cover; border:2px solid var(--border-2); }
.chat-hdr-name { display:block; font-size:0.9rem; font-weight:600; color:var(--text); }
.chat-hdr-job  { display:block; font-size:0.72rem; color:var(--accent); }
.chat-close-btn { background:var(--bg-4); border:1px solid var(--border-2); color:var(--text-3); font-size:0.9rem; width:32px; height:32px; border-radius:50%; cursor:pointer; transition:all 0.15s; display:flex; align-items:center; justify-content:center; }
.chat-close-btn:hover { background:var(--red-dim); color:var(--red); border-color:var(--red); }
.chat-messages { flex:1; overflow-y:auto; padding:1rem; display:flex; flex-direction:column; gap:0.35rem; scroll-behavior:smooth; }
.chat-empty-hint { text-align:center; color:var(--text-3); font-size:0.8rem; margin:auto; padding:2rem; }
.chat-loading { display:flex; align-items:center; justify-content:center; gap:6px; padding:2rem; margin:auto; }
.chat-loading-dot { width:8px; height:8px; border-radius:50%; background:var(--accent); opacity:0.4; animation:dotPulse 1.2s ease-in-out infinite; }
.chat-loading-dot:nth-child(2){animation-delay:0.2s} .chat-loading-dot:nth-child(3){animation-delay:0.4s}
@keyframes dotPulse{0%,80%,100%{opacity:0.2;transform:scale(0.8)}40%{opacity:1;transform:scale(1)}}
.chat-date-sep { text-align:center; font-size:0.68rem; color:var(--text-3); margin:0.75rem 0 0.4rem; position:relative; }
.chat-date-sep::before,.chat-date-sep::after { content:''; position:absolute; top:50%; width:calc(50% - 2.5rem); height:1px; background:var(--border); }
.chat-date-sep::before{left:0} .chat-date-sep::after{right:0}
.chat-bubble-wrap { display:flex; flex-direction:column; max-width:78%; animation:bubbleIn 0.18s ease; }
@keyframes bubbleIn{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:none}}
.chat-bubble-wrap.mine   { align-self:flex-end;  align-items:flex-end; }
.chat-bubble-wrap.theirs { align-self:flex-start; align-items:flex-start; }
.chat-bubble { padding:0.55rem 0.875rem; border-radius:14px; font-size:0.875rem; line-height:1.5; word-break:break-word; }
.mine   .chat-bubble { background:var(--accent); color:#fff; border-bottom-right-radius:3px; }
.theirs .chat-bubble { background:var(--bg-4); color:var(--text); border:1px solid var(--border); border-bottom-left-radius:3px; }
.chat-time { font-size:0.63rem; color:var(--text-3); margin-top:0.15rem; padding:0 0.2rem; }
.chat-input-row { display:flex; align-items:flex-end; gap:0.5rem; padding:0.875rem 1rem; border-top:1px solid var(--border); background:var(--bg-3); flex-shrink:0; }
.chat-textarea { flex:1; background:var(--bg-4); border:1px solid var(--border-2); color:var(--text); border-radius:12px; padding:0.6rem 0.875rem; font-size:0.875rem; font-family:var(--font-body); outline:none; resize:none; max-height:120px; line-height:1.5; transition:border-color 0.15s; scrollbar-width:none; }
.chat-textarea:focus { border-color:var(--accent); }
.chat-textarea::placeholder { color:var(--text-3); }
.chat-send-btn { width:38px; height:38px; flex-shrink:0; background:var(--accent); border:none; border-radius:50%; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.15s; }
.chat-send-btn:hover { background:var(--accent-2); transform:scale(1.07); }
.chat-send-btn:disabled { opacity:0.5; cursor:default; transform:none; }

/* Nav unread badge */
.nav-unread-badge { position:absolute; top:-4px; right:-4px; background:var(--red); color:#fff; font-size:0.6rem; font-weight:700; min-width:16px; height:16px; border-radius:8px; display:flex; align-items:center; justify-content:center; padding:0 3px; border:2px solid var(--bg); font-family:var(--font-mono); pointer-events:none; }
.nav-avatar-btn { position:relative; }
/* ─── GROUP CHAT SECTION ─── */
.group-chat-section {
    margin-top: 2.5rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border);
}
.group-chat-header-row {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;
}
.group-online-wrap {
    display: flex; gap: -6px;
}

.group-chat-layout {
    background: var(--bg-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.group-messages-box {
    height: 440px;
    overflow-y: auto;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    scroll-behavior: smooth;
}

/* Group message rows */
.group-msg-wrap {
    display: flex;
    align-items: flex-end;
    gap: 0.6rem;
    animation: bubbleIn 0.18s ease;
    max-width: 72%;
}
.group-mine   { align-self: flex-end;  flex-direction: row-reverse; }
.group-theirs { align-self: flex-start; }

.group-avatar-sm {
    width: 30px; height: 30px;
    border-radius: 50%; object-fit: cover;
    border: 1.5px solid var(--border-2);
    flex-shrink: 0;
}

.group-msg-body-wrap {
    display: flex; flex-direction: column; gap: 0.18rem;
}
.group-mine .group-msg-body-wrap   { align-items: flex-end; }
.group-theirs .group-msg-body-wrap { align-items: flex-start; }

.group-sender-name {
    font-size: 0.7rem; font-weight: 600;
    color: var(--accent); padding: 0 0.25rem;
    letter-spacing: 0.01em;
}

.group-bubble {
    padding: 0.55rem 0.875rem;
    border-radius: 14px;
    font-size: 0.875rem; line-height: 1.5;
    word-break: break-word; max-width: 100%;
}
.mine-bubble   { background: var(--accent); color: #fff; border-bottom-right-radius: 3px; }
.theirs-bubble { background: var(--bg-4); color: var(--text); border: 1px solid var(--border); border-bottom-left-radius: 3px; }

.group-time { font-size: 0.62rem; color: var(--text-3); padding: 0 0.2rem; }

/* Group input row */
.group-input-row {
    display: flex; align-items: flex-end; gap: 0.625rem;
    padding: 0.875rem 1rem;
    border-top: 1px solid var(--border);
    background: var(--bg-3);
}
.group-my-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    object-fit: cover; border: 1.5px solid var(--border-2);
    flex-shrink: 0;
}
.group-textarea { flex: 1; }

</style>
@endsection