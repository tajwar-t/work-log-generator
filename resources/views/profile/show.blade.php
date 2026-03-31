@extends('layouts.app')
@section('title', 'Profile')
@section('content')

{{-- Toaster --}}
<div id="toastContainer" class="toaster-container"></div>

<div class="page-header">
    <div>
        <h1 class="page-title">Profile</h1>
        <p class="page-sub">Manage your account and preferences</p>
    </div>
    <a href="{{ route('logs.index') }}" class="btn-ghost">← Back</a>
</div>

<div class="profile-layout">

    {{-- LEFT: Avatar + Stats --}}
    <div class="profile-sidebar">

        {{-- Avatar Card --}}
        <div class="panel-card avatar-card">
            <div class="avatar-wrap" id="avatarWrap">
                <img
                    id="avatarImg"
                    src="{{ $user->avatar_url }}"
                    alt="{{ $user->name }}"
                    class="avatar-img"
                >
                <div class="avatar-overlay" onclick="document.getElementById('avatarInput').click()">
                    <span class="avatar-overlay-icon">📷</span>
                    <span class="avatar-overlay-text">Change</span>
                </div>
                <input type="file" id="avatarInput" accept="image/*" style="display:none" onchange="uploadAvatar(this)">
            </div>

            <div class="avatar-info">
                <h3 class="avatar-name" id="displayName">{{ $user->name }}</h3>
                <p class="avatar-job" id="displayJob">{{ $user->job_title ?? 'No title set' }}</p>
                <p class="avatar-email">{{ $user->email }}</p>
            </div>

            <div class="avatar-actions">
                <button class="btn-avatar-upload" onclick="document.getElementById('avatarInput').click()">
                    Upload Photo
                </button>
                @if($user->avatar)
                <button class="btn-avatar-remove" onclick="removeAvatar()">Remove</button>
                @endif
            </div>

            <p class="avatar-hint">JPG, PNG, GIF or WebP · Max 2MB</p>

            <div class="avatar-upload-progress" id="uploadProgress" style="display:none">
                <div class="upload-bar" id="uploadBar"></div>
            </div>
        </div>

        {{-- Stats Card --}}
        <div class="panel-card stats-sidebar-card">
            <h4 class="sidebar-section-title">Your Stats</h4>
            <div class="sidebar-stats">
                <div class="sidebar-stat">
                    <span class="sidebar-stat-num">{{ $stats['total'] }}</span>
                    <span class="sidebar-stat-label">Total Logs</span>
                </div>
                <div class="sidebar-stat">
                    <span class="sidebar-stat-num">{{ $stats['dayStarts'] }}</span>
                    <span class="sidebar-stat-label">Day Starts</span>
                </div>
                <div class="sidebar-stat">
                    <span class="sidebar-stat-num">{{ $stats['dayEnds'] }}</span>
                    <span class="sidebar-stat-label">Day Ends</span>
                </div>
                <div class="sidebar-stat">
                    <span class="sidebar-stat-num">{{ $stats['thisMonth'] }}</span>
                    <span class="sidebar-stat-label">This Month</span>
                </div>
            </div>
            <div class="member-since">
                Member since {{ $stats['member_since'] }}
            </div>
        </div>

    </div>

    {{-- RIGHT: Edit Forms --}}
    <div class="profile-main">

        {{-- Profile Info --}}
        <div class="panel-card profile-section">
            <div class="section-title-row">
                <h3 class="profile-section-title">Personal Information</h3>
            </div>

            <div class="profile-form" id="profileForm">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" id="pName" class="form-input" value="{{ $user->name }}" placeholder="Your full name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" id="pEmail" class="form-input" value="{{ $user->email }}" placeholder="you@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Job Title</label>
                        <input type="text" id="pJobTitle" class="form-input" value="{{ $user->job_title ?? '' }}" placeholder="e.g. Senior Developer">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Timezone</label>
                        <div class="select-wrap">
                            <select id="pTimezone" class="styled-select">
                                @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" {{ ($user->timezone ?? 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Bio <span class="form-hint">Max 500 characters</span></label>
                    <textarea id="pBio" class="form-input form-textarea" placeholder="A short bio about yourself…" maxlength="500">{{ $user->bio ?? '' }}</textarea>
                    <div class="char-count"><span id="bioCount">{{ strlen($user->bio ?? '') }}</span>/500</div>
                </div>
                <div class="form-actions">
                    <button class="btn-generate" onclick="saveProfile()">Save Changes</button>
                </div>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="panel-card profile-section">
            <div class="section-title-row">
                <h3 class="profile-section-title">Change Password</h3>
            </div>
            <div class="profile-form">
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <div class="password-wrap">
                        <input type="password" id="pCurrentPw" class="form-input" placeholder="Enter current password">
                        <button type="button" class="pw-toggle" onclick="togglePw('pCurrentPw', this)">👁</button>
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div class="password-wrap">
                            <input type="password" id="pNewPw" class="form-input" placeholder="Min 8 characters" oninput="checkPwStrength(this.value)">
                            <button type="button" class="pw-toggle" onclick="togglePw('pNewPw', this)">👁</button>
                        </div>
                        <div class="pw-strength" id="pwStrength" style="display:none">
                            <div class="pw-strength-bar" id="pwStrengthBar"></div>
                            <span class="pw-strength-label" id="pwStrengthLabel"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <div class="password-wrap">
                            <input type="password" id="pConfirmPw" class="form-input" placeholder="Repeat new password">
                            <button type="button" class="pw-toggle" onclick="togglePw('pConfirmPw', this)">👁</button>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-save" onclick="changePassword()">Update Password</button>
                </div>
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="panel-card profile-section danger-zone">
            <h3 class="profile-section-title danger-title">Danger Zone</h3>
            <div class="danger-row">
                <div>
                    <p class="danger-label">Delete all work logs</p>
                    <p class="danger-desc">Permanently remove all your log entries. This cannot be undone.</p>
                </div>
                <button class="btn-danger" onclick="confirmDeleteLogs()">Delete All Logs</button>
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ---- TOASTER (same as create page) ----
function showToast(msg, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toaster-item toaster-${type}`;
    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
    toast.innerHTML = `<span class="toaster-icon">${icon}</span><span class="toaster-msg">${msg}</span><button class="toaster-close" onclick="this.closest('.toaster-item').remove()">×</button>`;
    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('toaster-show'));
    setTimeout(() => {
        toast.classList.remove('toaster-show');
        toast.classList.add('toaster-hide');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

// ---- AVATAR UPLOAD ----
function uploadAvatar(input) {
    if (!input.files[0]) return;
    const file = input.files[0];

    if (file.size > 2 * 1024 * 1024) {
        showToast('File too large. Max 2MB.', 'error'); return;
    }

    // Preview immediately
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatarImg').src = e.target.result;
    };
    reader.readAsDataURL(file);

    // Show progress bar
    const progress = document.getElementById('uploadProgress');
    const bar      = document.getElementById('uploadBar');
    progress.style.display = 'block';
    bar.style.width = '0%';

    const formData = new FormData();
    formData.append('avatar', file);
    formData.append('_token', CSRF);

    const xhr = new XMLHttpRequest();
    xhr.upload.addEventListener('progress', e => {
        if (e.lengthComputable) bar.style.width = (e.loaded / e.total * 100) + '%';
    });
    xhr.addEventListener('load', () => {
        progress.style.display = 'none';
        const data = JSON.parse(xhr.responseText);
        if (data.success) {
            document.getElementById('avatarImg').src = data.avatar_url + '?t=' + Date.now();
            showToast(data.message, 'success');
        } else {
            showToast(data.message ?? 'Upload failed.', 'error');
        }
    });
    xhr.addEventListener('error', () => {
        progress.style.display = 'none';
        showToast('Upload failed. Please try again.', 'error');
    });

    xhr.open('POST', '{{ route("profile.avatar") }}');
    xhr.send(formData);
}

// ---- REMOVE AVATAR ----
function removeAvatar() {
    if (!confirm('Remove your profile picture?')) return;
    fetch('{{ route("profile.avatar.remove") }}', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Reset to initials avatar
            const name = encodeURIComponent(document.getElementById('displayName').textContent.trim());
            document.getElementById('avatarImg').src = `https://ui-avatars.com/api/?name=${name}&background=6c8fff&color=fff&size=128&bold=true&font-size=0.4`;
            showToast(data.message, 'success');
        }
    })
    .catch(() => showToast('Failed to remove avatar.', 'error'));
}

// ---- SAVE PROFILE ----
function saveProfile() {
    const body = new URLSearchParams({
        _token:    CSRF,
        name:      document.getElementById('pName').value,
        email:     document.getElementById('pEmail').value,
        job_title: document.getElementById('pJobTitle').value,
        timezone:  document.getElementById('pTimezone').value,
        bio:       document.getElementById('pBio').value,
    });

    fetch('{{ route("profile.update") }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: body,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update display name + job in sidebar
            document.getElementById('displayName').textContent = document.getElementById('pName').value;
            document.getElementById('displayJob').textContent  = document.getElementById('pJobTitle').value || 'No title set';
            showToast(data.message, 'success');
        } else {
            showToast(data.message ?? 'Update failed.', 'error');
        }
    })
    .catch(() => showToast('Network error. Please try again.', 'error'));
}

// ---- CHANGE PASSWORD ----
function changePassword() {
    const newPw     = document.getElementById('pNewPw').value;
    const confirmPw = document.getElementById('pConfirmPw').value;
    const currentPw = document.getElementById('pCurrentPw').value;

    if (!currentPw) { showToast('Enter your current password.', 'error'); return; }
    if (newPw.length < 8) { showToast('New password must be at least 8 characters.', 'error'); return; }
    if (newPw !== confirmPw) { showToast('Passwords do not match.', 'error'); return; }

    const body = new URLSearchParams({
        _token:               CSRF,
        current_password:     currentPw,
        password:             newPw,
        password_confirmation: confirmPw,
    });

    fetch('{{ route("profile.password") }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: body,
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            document.getElementById('pCurrentPw').value = '';
            document.getElementById('pNewPw').value     = '';
            document.getElementById('pConfirmPw').value = '';
            document.getElementById('pwStrength').style.display = 'none';
        }
    })
    .catch(() => showToast('Network error. Please try again.', 'error'));
}

// ---- PASSWORD VISIBILITY TOGGLE ----
function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    input.type  = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? '👁' : '🙈';
}

// ---- PASSWORD STRENGTH ----
function checkPwStrength(val) {
    const bar   = document.getElementById('pwStrengthBar');
    const label = document.getElementById('pwStrengthLabel');
    const wrap  = document.getElementById('pwStrength');

    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'flex';

    let score = 0;
    if (val.length >= 8)            score++;
    if (val.length >= 12)           score++;
    if (/[A-Z]/.test(val))          score++;
    if (/[0-9]/.test(val))          score++;
    if (/[^A-Za-z0-9]/.test(val))   score++;

    const levels = [
        { pct: '20%', color: '#ff5f5f', text: 'Very weak' },
        { pct: '40%', color: '#f5a623', text: 'Weak' },
        { pct: '60%', color: '#f5a623', text: 'Fair' },
        { pct: '80%', color: '#43c678', text: 'Strong' },
        { pct: '100%', color: '#43c678', text: 'Very strong' },
    ];
    const lvl = levels[score - 1] || levels[0];
    bar.style.width = lvl.pct;
    bar.style.background = lvl.color;
    label.textContent = lvl.text;
    label.style.color = lvl.color;
}

// ---- BIO CHAR COUNT ----
document.getElementById('pBio').addEventListener('input', function() {
    document.getElementById('bioCount').textContent = this.value.length;
});

// ---- DANGER: DELETE ALL LOGS ----
function confirmDeleteLogs() {
    if (!confirm('Are you sure? This will permanently delete ALL your work logs and cannot be undone.')) return;
    if (prompt('Type DELETE to confirm:') !== 'DELETE') {
        showToast('Cancelled.', 'info'); return;
    }
    fetch('/api/delete-all-logs', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => showToast(data.message, data.success ? 'success' : 'error'))
    .catch(() => showToast('Failed. Please try again.', 'error'));
}
</script>

<style>
/* ---- PROFILE LAYOUT ---- */
.profile-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 860px) { .profile-layout { grid-template-columns: 1fr; } }

/* ---- SIDEBAR ---- */
.profile-sidebar { display: flex; flex-direction: column; gap: 1rem; }

.avatar-card { text-align: center; }

.avatar-wrap {
    position: relative;
    width: 110px; height: 110px;
    margin: 0 auto 1.25rem;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    border: 3px solid var(--border-2);
    transition: border-color 0.2s;
}
.avatar-wrap:hover { border-color: var(--accent); }
.avatar-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.avatar-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,0.55);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 0.25rem;
    opacity: 0; transition: opacity 0.2s;
}
.avatar-wrap:hover .avatar-overlay { opacity: 1; }
.avatar-overlay-icon { font-size: 1.4rem; }
.avatar-overlay-text { font-size: 0.72rem; color: #fff; font-weight: 600; letter-spacing: 0.04em; }

.avatar-info { margin-bottom: 1rem; }
.avatar-name { font-family: var(--font-display); font-size: 1.05rem; font-weight: 700; margin-bottom: 0.2rem; }
.avatar-job  { font-size: 0.8rem; color: var(--accent); margin-bottom: 0.25rem; }
.avatar-email { font-size: 0.75rem; color: var(--text-3); }

.avatar-actions { display: flex; gap: 0.5rem; justify-content: center; margin-bottom: 0.5rem; flex-wrap: wrap; }
.btn-avatar-upload {
    background: var(--accent-glow); color: var(--accent);
    border: 1px solid rgba(108,143,255,0.3); border-radius: 8px;
    padding: 0.4rem 0.875rem; font-size: 0.8rem;
    font-family: var(--font-body); cursor: pointer;
    transition: all 0.15s;
}
.btn-avatar-upload:hover { background: var(--accent); color: #fff; }
.btn-avatar-remove {
    background: var(--red-dim); color: var(--red);
    border: 1px solid rgba(255,95,95,0.3); border-radius: 8px;
    padding: 0.4rem 0.875rem; font-size: 0.8rem;
    font-family: var(--font-body); cursor: pointer;
    transition: all 0.15s;
}
.btn-avatar-remove:hover { background: var(--red); color: #fff; }
.avatar-hint { font-size: 0.72rem; color: var(--text-3); margin-top: 0.25rem; }

.avatar-upload-progress {
    margin-top: 0.75rem; background: var(--bg-3);
    border-radius: 4px; height: 4px; overflow: hidden;
}
.upload-bar {
    height: 100%; background: var(--accent);
    border-radius: 4px; width: 0%;
    transition: width 0.2s ease;
}

/* Stats sidebar */
.stats-sidebar-card {}
.sidebar-section-title {
    font-family: var(--font-display); font-size: 0.8rem;
    font-weight: 600; color: var(--text-2);
    text-transform: uppercase; letter-spacing: 0.06em;
    margin-bottom: 1rem;
}
.sidebar-stats {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 0.75rem; margin-bottom: 1rem;
}
.sidebar-stat {
    background: var(--bg-3); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 0.75rem;
    display: flex; flex-direction: column; align-items: center; gap: 0.2rem;
}
.sidebar-stat-num {
    font-family: var(--font-display); font-size: 1.4rem;
    font-weight: 800; color: var(--accent);
}
.sidebar-stat-label { font-size: 0.7rem; color: var(--text-3); text-align: center; }
.member-since {
    font-size: 0.75rem; color: var(--text-3);
    text-align: center; padding-top: 0.75rem;
    border-top: 1px solid var(--border);
}

/* ---- PROFILE MAIN ---- */
.profile-main { display: flex; flex-direction: column; gap: 1.25rem; }
.profile-section {}
.section-title-row {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.5rem;
    padding-bottom: 0.875rem;
    border-bottom: 1px solid var(--border);
}
.profile-section-title {
    font-family: var(--font-display); font-size: 1rem; font-weight: 700;
}
.profile-form { display: flex; flex-direction: column; gap: 1rem; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 600px) { .form-grid-2 { grid-template-columns: 1fr; } }
.form-hint { font-size: 0.75rem; color: var(--text-3); font-weight: 400; margin-left: 0.5rem; }
.form-textarea {
    resize: vertical; min-height: 90px;
    line-height: 1.6; padding-top: 0.65rem;
}
.char-count { font-size: 0.72rem; color: var(--text-3); text-align: right; margin-top: -0.5rem; }
.form-actions { display: flex; justify-content: flex-end; padding-top: 0.5rem; }

/* Password */
.password-wrap { position: relative; }
.pw-toggle {
    position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; font-size: 1rem;
    color: var(--text-3); padding: 0; line-height: 1;
    transition: color 0.15s;
}
.pw-toggle:hover { color: var(--text); }
.pw-strength {
    display: flex; align-items: center; gap: 0.6rem;
    margin-top: 0.4rem;
}
.pw-strength-bar {
    flex: 1; height: 3px; border-radius: 2px;
    background: var(--red); transition: width 0.3s, background 0.3s;
}
.pw-strength-label { font-size: 0.72rem; white-space: nowrap; font-weight: 500; }

/* Danger Zone */
.danger-zone { border-color: rgba(255,95,95,0.25) !important; }
.danger-title { color: var(--red); margin-bottom: 1rem; }
.danger-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1.5rem; flex-wrap: wrap;
}
.danger-label { font-size: 0.9rem; font-weight: 600; color: var(--text); margin-bottom: 0.2rem; }
.danger-desc  { font-size: 0.8rem; color: var(--text-3); }
</style>
@endsection
