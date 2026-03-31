<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkLog — @yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/png" sizes="32x32" href="https://moccasin-buffalo-320073.hostingersite.com/public/work_log_generator_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@300;400;500&family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
/* ============================================
   WorkLog — Sleek Editorial Dark Theme
   Font: Syne (display) + DM Sans (body) + DM Mono (code)
   ============================================ */

:root {
    --bg: #0d0f14;
    --bg-2: #13161d;
    --bg-3: #1a1d26;
    --bg-4: #20242f;
    --border: #252934;
    --border-2: #2e3345;
    --text: #e8eaf0;
    --text-2: #9ca3b4;
    --text-3: #5a6278;
    --accent: #6c8fff;
    --accent-2: #4a6ef0;
    --accent-glow: rgba(108,143,255,0.15);
    --green: #43c678;
    --green-dim: rgba(67,198,120,0.12);
    --amber: #f5a623;
    --amber-dim: rgba(245,166,35,0.12);
    --red: #ff5f5f;
    --red-dim: rgba(255,95,95,0.12);
    --purple: #b47eff;
    --purple-dim: rgba(180,126,255,0.12);
    --radius: 10px;
    --radius-lg: 16px;
    --shadow: 0 4px 24px rgba(0,0,0,0.35);
    --shadow-lg: 0 8px 40px rgba(0,0,0,0.5);
    --font-display: 'Syne', sans-serif;
    --font-body: 'DM Sans', sans-serif;
    --font-mono: 'DM Mono', monospace;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html { font-size: 15px; scroll-behavior: smooth; }

body {
    background: var(--bg);
    color: var(--text);
    font-family: var(--font-body);
    line-height: 1.6;
    min-height: 100vh;
}

/* ---- NAVBAR ---- */
.navbar {
    position: sticky; top: 0; z-index: 100;
    background: rgba(13,15,20,0.92);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
}
.nav-inner {
    max-width: 1200px; margin: 0 auto;
    padding: 0 1.5rem;
    display: flex; align-items: center; justify-content: space-between;
    height: 58px;
}
.nav-brand {
    display: flex; align-items: center; gap: 0.5rem;
    font-family: var(--font-display); font-size: 1.15rem; font-weight: 700;
    color: var(--text); text-decoration: none; letter-spacing: -0.02em;
}
.brand-icon { font-size: 1.3rem; color: var(--accent); }
.nav-right { display: flex; align-items: center; gap: 1rem; }
.nav-user {
    font-size: 0.8rem; color: var(--text-3);
    padding: 0.25rem 0.6rem;
    background: var(--bg-3);
    border-radius: 20px;
    border: 1px solid var(--border);
}
.nav-link {
    font-size: 0.875rem; color: var(--text-2);
    text-decoration: none; padding: 0.25rem 0.5rem;
    border-radius: 6px; transition: all 0.15s;
}
.nav-link:hover, .nav-link.active { color: var(--text); background: var(--bg-3); }
.btn-logout {
    background: none; border: 1px solid var(--border-2);
    color: var(--text-3); font-size: 0.8rem; font-family: var(--font-body);
    padding: 0.3rem 0.75rem; border-radius: 6px; cursor: pointer;
    transition: all 0.15s;
}
.btn-logout:hover { color: var(--red); border-color: var(--red); }

/* ---- MAIN ---- */
.main-content {
    max-width: 1200px; margin: 0 auto;
    padding: 2rem 1.5rem 4rem;
}

/* ---- ALERTS ---- */
.alert {
    padding: 0.75rem 1rem; border-radius: var(--radius);
    margin-bottom: 1.25rem; font-size: 0.875rem;
    border: 1px solid;
}
.alert-success { background: var(--green-dim); border-color: var(--green); color: var(--green); }
.alert-error { background: var(--red-dim); border-color: var(--red); color: var(--red); }

/* ---- PAGE HEADER ---- */
.page-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;
}
.page-title {
    font-family: var(--font-display); font-size: 1.75rem;
    font-weight: 800; letter-spacing: -0.03em; color: var(--text);
}
.page-sub { color: var(--text-3); font-size: 0.875rem; margin-top: 0.2rem; }

/* ---- BUTTONS ---- */
.btn-primary {
    background: var(--accent); color: #fff;
    border: none; border-radius: var(--radius);
    padding: 0.6rem 1.25rem; font-size: 0.875rem;
    font-family: var(--font-body); font-weight: 500;
    cursor: pointer; text-decoration: none; display: inline-flex;
    align-items: center; transition: all 0.15s;
}
.btn-primary:hover { background: var(--accent-2); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(108,143,255,0.35); }
.btn-secondary {
    background: var(--bg-3); color: var(--text-2);
    border: 1px solid var(--border-2); border-radius: var(--radius);
    padding: 0.6rem 1.25rem; font-size: 0.875rem;
    font-family: var(--font-body); font-weight: 500;
    cursor: pointer; text-decoration: none; display: inline-flex;
    align-items: center; transition: all 0.15s;
}
.btn-secondary:hover { color: var(--text); border-color: var(--border-2); background: var(--bg-4); }
.btn-ghost {
    background: none; color: var(--text-3);
    border: none; border-radius: var(--radius);
    padding: 0.6rem 1rem; font-size: 0.875rem;
    text-decoration: none; display: inline-flex; align-items: center;
    cursor: pointer; transition: color 0.15s;
}
.btn-ghost:hover { color: var(--text); }
.btn-danger {
    background: var(--red-dim); color: var(--red);
    border: 1px solid var(--red); border-radius: var(--radius);
    padding: 0.6rem 1.25rem; font-size: 0.875rem;
    font-family: var(--font-body); cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center;
    transition: all 0.15s;
}
.btn-danger:hover { background: var(--red); color: #fff; }
.btn-full { width: 100%; justify-content: center; }

/* ---- AUTH ---- */
.auth-body { background: var(--bg); }
.auth-bg {
    position: fixed; inset: 0; z-index: 0;
    background: radial-gradient(ellipse at 20% 50%, rgba(108,143,255,0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(180,126,255,0.05) 0%, transparent 50%);
}
.auth-grid {
    position: absolute; inset: 0;
    background-image: linear-gradient(var(--border) 1px, transparent 1px),
                      linear-gradient(90deg, var(--border) 1px, transparent 1px);
    background-size: 40px 40px;
    opacity: 0.3;
}
.auth-container {
    position: relative; z-index: 1;
    min-height: 100vh; display: flex;
    flex-direction: column; align-items: center; justify-content: center;
    padding: 2rem;
}
.auth-brand { text-align: center; margin-bottom: 2rem; }
.brand-icon-lg { font-size: 2.5rem; color: var(--accent); display: block; margin-bottom: 0.5rem; }
.brand-name {
    font-family: var(--font-display); font-size: 2rem; font-weight: 800;
    letter-spacing: -0.04em; color: var(--text); margin: 0;
}
.brand-tagline { color: var(--text-3); font-size: 0.875rem; margin-top: 0.25rem; }
.auth-card {
    background: var(--bg-2); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 2.5rem;
    width: 100%; max-width: 400px;
    box-shadow: var(--shadow-lg);
}
.auth-title {
    font-family: var(--font-display); font-size: 1.4rem;
    font-weight: 700; margin-bottom: 0.25rem;
}
.auth-sub { color: var(--text-3); font-size: 0.875rem; margin-bottom: 1.75rem; }
.auth-form { display: flex; flex-direction: column; gap: 1rem; }
.auth-switch {
    text-align: center; margin-top: 1.5rem;
    font-size: 0.85rem; color: var(--text-3);
}
.auth-switch a { color: var(--accent); text-decoration: none; }
.auth-switch a:hover { text-decoration: underline; }

/* ---- FORM ELEMENTS ---- */
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-label { font-size: 0.8rem; font-weight: 500; color: var(--text-2); letter-spacing: 0.02em; }
.form-input {
    background: var(--bg-3); border: 1px solid var(--border-2);
    color: var(--text); border-radius: var(--radius);
    padding: 0.65rem 0.875rem; font-size: 0.9rem;
    font-family: var(--font-body); width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
    outline: none;
}
.form-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); }
.form-input::placeholder { color: var(--text-3); }
.form-check { margin: -0.25rem 0; }
.check-label {
    display: flex; align-items: center; gap: 0.5rem;
    font-size: 0.85rem; color: var(--text-2); cursor: pointer;
}

/* ---- GENERATOR LAYOUT ---- */
.generator-layout {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;
}
@media (max-width: 900px) { .generator-layout { grid-template-columns: 1fr; } }

.panel-card {
    background: var(--bg-2); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 1.75rem;
    box-shadow: var(--shadow);
}
.output-card { display: flex; flex-direction: column; min-height: 480px; }
.output-title {
    font-family: var(--font-display); font-size: 0.9rem;
    font-weight: 600; color: var(--text-2); margin-bottom: 1rem;
    text-transform: uppercase; letter-spacing: 0.06em;
}

/* ---- CONTROLS ---- */
.control-row {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
    margin-bottom: 1.25rem;
}
.control-group { display: flex; flex-direction: column; gap: 0.4rem; }
.ctrl-label { font-size: 0.78rem; font-weight: 500; color: var(--text-2); letter-spacing: 0.03em; }
.select-wrap { position: relative; }
.select-wrap::after {
    content: '▾'; position: absolute; right: 0.75rem; top: 50%;
    transform: translateY(-50%); color: var(--text-3); pointer-events: none;
    font-size: 0.9rem;
}
.styled-select {
    background: var(--bg-3); border: 1px solid var(--border-2);
    color: var(--text); border-radius: var(--radius);
    padding: 0.65rem 2rem 0.65rem 0.875rem;
    font-size: 0.875rem; font-family: var(--font-body);
    width: 100%; appearance: none; cursor: pointer;
    outline: none; transition: border-color 0.15s;
}
.styled-select:focus { border-color: var(--accent); }
.styled-input {
    background: var(--bg-3); border: 1px solid var(--border-2);
    color: var(--text); border-radius: var(--radius);
    padding: 0.65rem 0.875rem; font-size: 0.875rem;
    font-family: var(--font-body); width: 100%;
    outline: none; transition: border-color 0.15s;
}
.styled-input:focus { border-color: var(--accent); }
.styled-input.sm, .styled-select.sm { padding: 0.45rem 0.75rem; font-size: 0.82rem; }

/* ---- SMART SUGGESTIONS ---- */
.smart-section {
    display: flex; align-items: center; gap: 0.75rem;
    margin-bottom: 1.5rem; flex-wrap: wrap;
}
.smart-label { font-size: 0.75rem; color: var(--text-3); font-weight: 500; white-space: nowrap; }
.smart-chips { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.chip {
    background: var(--bg-3); border: 1px solid var(--border-2);
    color: var(--text-2); font-size: 0.78rem;
    padding: 0.3rem 0.75rem; border-radius: 20px;
    cursor: pointer; font-family: var(--font-body);
    transition: all 0.15s;
}
.chip:hover { background: var(--bg-4); color: var(--text); border-color: var(--accent); }

/* ---- WORK SECTIONS ---- */
.work-section { margin-bottom: 1.5rem; }
.section-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 0.75rem;
}
.section-label {
    font-family: var(--font-display); font-size: 0.95rem;
    font-weight: 600; color: var(--text);
}
.btn-add {
    background: var(--accent-glow); color: var(--accent);
    border: 1px solid rgba(108,143,255,0.3);
    border-radius: 8px; padding: 0.3rem 0.75rem;
    font-size: 0.78rem; font-family: var(--font-body);
    cursor: pointer; transition: all 0.15s; white-space: nowrap;
}
.btn-add:hover { background: var(--accent); color: #fff; }
.btn-add-green {
    background: var(--green-dim); color: var(--green);
    border-color: rgba(67,198,120,0.3);
}
.btn-add-green:hover { background: var(--green); color: #fff; }

.items-list { display: flex; flex-direction: column; gap: 0.5rem; }
.item-row { display: flex; align-items: center; gap: 0.5rem; }
.item-input {
    flex: 1; background: var(--bg-3); border: 1px solid var(--border-2);
    color: var(--text); border-radius: var(--radius);
    padding: 0.6rem 0.875rem; font-size: 0.875rem;
    font-family: var(--font-body); outline: none;
    transition: border-color 0.15s, background 0.15s;
}
.item-input:focus { border-color: var(--accent); background: var(--bg-4); }
.item-input::placeholder { color: var(--text-3); }
.btn-remove {
    background: none; border: none; color: var(--text-3);
    font-size: 1rem; cursor: pointer; padding: 0.4rem;
    border-radius: 6px; transition: all 0.15s; flex-shrink: 0;
}
.btn-remove:hover { color: var(--red); background: var(--red-dim); }

/* ---- ACTION BUTTONS ---- */
.action-row { display: flex; gap: 0.75rem; margin-top: 1.75rem; }
.btn-generate {
    flex: 1; background: var(--accent); color: #fff;
    border: none; border-radius: var(--radius);
    padding: 0.75rem 1rem; font-size: 0.9rem;
    font-family: var(--font-display); font-weight: 600;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    gap: 0.4rem; transition: all 0.2s;
}
.btn-generate:hover {
    background: var(--accent-2); transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(108,143,255,0.4);
}
.btn-save {
    flex: 1; background: var(--purple-dim); color: var(--purple);
    border: 1px solid rgba(180,126,255,0.3); border-radius: var(--radius);
    padding: 0.75rem 1rem; font-size: 0.9rem;
    font-family: var(--font-display); font-weight: 600;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    gap: 0.4rem; transition: all 0.2s;
}
.btn-save:hover { background: var(--purple); color: #fff; }

/* ---- OUTPUT AREA ---- */
.output-area {
    flex: 1; background: var(--bg-3); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 1.25rem;
    font-family: var(--font-mono); font-size: 0.82rem;
    line-height: 1.7; overflow-y: auto; min-height: 300px;
    margin-bottom: 1rem;
}
.output-area.empty {
    display: flex; align-items: center; justify-content: center;
}
.output-empty { text-align: center; color: var(--text-3); }
.empty-icon { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.3; }
.output-empty p { font-size: 0.875rem; margin-bottom: 0.25rem; }
.output-empty small { font-size: 0.78rem; }
.out-bold {
    font-weight: 500; color: var(--text);
    font-family: var(--font-mono);
}
.out-line { color: var(--text-2); }

.output-actions { display: flex; justify-content: flex-end; }
.btn-copy {
    background: var(--green-dim); color: var(--green);
    border: 1px solid rgba(67,198,120,0.3);
    border-radius: var(--radius); padding: 0.6rem 1.25rem;
    font-size: 0.875rem; font-family: var(--font-body); font-weight: 500;
    cursor: pointer; transition: all 0.15s;
}
.btn-copy:hover:not(:disabled) { background: var(--green); color: #fff; }
.btn-copy:disabled { opacity: 0.4; cursor: default; }
.btn-copy.copied { background: var(--green); color: #fff; }

/* ---- STATS BAR ---- */
.stats-bar {
    display: flex; gap: 0.875rem; margin-bottom: 1.5rem;
    overflow-x: auto; padding-bottom: 0.25rem;
}
.stat-card {
    background: var(--bg-2); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 1rem 1.25rem;
    min-width: 110px; display: flex; flex-direction: column;
    align-items: center; gap: 0.2rem; flex-shrink: 0;
}
.stat-num {
    font-family: var(--font-display); font-size: 1.5rem;
    font-weight: 800; color: var(--accent);
}
.stat-label { font-size: 0.72rem; color: var(--text-3); white-space: nowrap; }

/* ---- FILTER BAR ---- */
.filter-bar {
    background: var(--bg-2); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 0.875rem 1rem;
    margin-bottom: 1.5rem;
}
.filter-form { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }
.btn-filter {
    background: var(--accent); color: #fff; border: none;
    border-radius: var(--radius); padding: 0.45rem 1rem;
    font-size: 0.82rem; font-family: var(--font-body); cursor: pointer;
    transition: background 0.15s;
}
.btn-filter:hover { background: var(--accent-2); }
.btn-clear {
    background: none; color: var(--text-3); font-size: 0.8rem;
    text-decoration: none; padding: 0.4rem 0.6rem;
    border-radius: 6px; transition: color 0.15s;
}
.btn-clear:hover { color: var(--red); }

/* ---- LOGS GRID ---- */
.logs-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
}
.log-card {
    background: var(--bg-2); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 1.25rem;
    transition: all 0.2s; cursor: default;
}
.log-card:hover { border-color: var(--border-2); transform: translateY(-2px); box-shadow: var(--shadow); }
.log-card.day_start { border-top: 2px solid var(--accent); }
.log-card.day_end { border-top: 2px solid var(--amber); }

.log-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; gap: 0.5rem; flex-wrap: wrap; }
.log-meta { display: flex; flex-direction: column; gap: 0.35rem; }
.log-type-badge {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.2rem 0.65rem; border-radius: 20px;
    font-size: 0.75rem; font-weight: 500;
}
.log-type-badge.day_start { background: var(--accent-glow); color: var(--accent); }
.log-type-badge.day_end { background: var(--amber-dim); color: var(--amber); }
.log-type-badge.lg { font-size: 0.85rem; padding: 0.3rem 0.875rem; }
.log-date { font-size: 0.8rem; color: var(--text-2); }
.log-actions { display: flex; gap: 0.4rem; flex-shrink: 0; }
.action-btn {
    font-size: 0.75rem; padding: 0.25rem 0.6rem;
    border-radius: 6px; text-decoration: none; cursor: pointer;
    font-family: var(--font-body); border: 1px solid; transition: all 0.15s;
    background: none;
}
.action-btn.view { color: var(--text-3); border-color: var(--border-2); }
.action-btn.view:hover { color: var(--text); border-color: var(--text-3); }
.action-btn.edit { color: var(--accent); border-color: rgba(108,143,255,0.3); }
.action-btn.edit:hover { background: var(--accent-glow); }
.action-btn.delete { color: var(--red); border-color: rgba(255,95,95,0.3); }
.action-btn.delete:hover { background: var(--red-dim); }

.log-preview { margin-bottom: 0.875rem; }
.preview-section {}
.preview-label { font-size: 0.72rem; color: var(--text-3); display: block; margin-bottom: 0.3rem; }
.preview-list { padding-left: 1rem; }
.preview-list li { font-size: 0.82rem; color: var(--text-2); margin-bottom: 0.1rem; }
.preview-more { color: var(--text-3) !important; font-style: italic; }

.log-card-footer {
    display: flex; justify-content: space-between;
    border-top: 1px solid var(--border); padding-top: 0.75rem;
    font-size: 0.75rem; color: var(--text-3);
}

/* ---- EMPTY STATE ---- */
.empty-state {
    text-align: center; padding: 5rem 2rem;
    color: var(--text-3);
}
.empty-big-icon { font-size: 4rem; display: block; opacity: 0.15; margin-bottom: 1rem; }
.empty-state h3 { font-family: var(--font-display); font-size: 1.2rem; color: var(--text-2); margin-bottom: 0.5rem; }
.empty-state p { margin-bottom: 1.5rem; }

/* ---- PAGINATION ---- */
.pagination-wrap { margin-top: 2rem; display: flex; justify-content: center; }
.pagination { display: flex; gap: 0.4rem; align-items: center; }
.page-btn {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 36px; height: 36px; padding: 0 0.6rem;
    background: var(--bg-2); border: 1px solid var(--border);
    color: var(--text-2); text-decoration: none; border-radius: 8px;
    font-size: 0.83rem; transition: all 0.15s; cursor: pointer;
}
.page-btn:hover:not(.disabled):not(.active) { background: var(--bg-3); border-color: var(--border-2); }
.page-btn.active { background: var(--accent); border-color: var(--accent); color: #fff; }
.page-btn.disabled { opacity: 0.35; cursor: default; }

/* ---- VIEW / SHOW PAGE ---- */
.view-layout { display: grid; grid-template-columns: 1fr 280px; gap: 1.5rem; }
@media (max-width: 900px) { .view-layout { grid-template-columns: 1fr; } }

.template-display {}
.template-rendered {
    background: var(--bg-2); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 2rem;
    font-family: var(--font-mono); font-size: 0.85rem;
    line-height: 1.9; margin-bottom: 1rem;
}
.tpl-sep { color: var(--text-3); }
.tpl-title { font-weight: 500; color: var(--text); font-size: 0.9rem; }
.tpl-header { font-weight: 500; color: var(--text); }
.tpl-item { color: var(--text-2); padding-left: 0.25rem; }
.view-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }

.info-card {
    background: var(--bg-2); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 1.25rem;
    margin-bottom: 1rem;
}
.info-card h4 {
    font-family: var(--font-display); font-size: 0.8rem;
    font-weight: 600; color: var(--text-2);
    text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 1rem;
}
.info-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.5rem 0; border-bottom: 1px solid var(--border);
}
.info-row:last-child { border-bottom: none; }
.info-key { font-size: 0.78rem; color: var(--text-3); }
.info-val { font-size: 0.82rem; color: var(--text); font-weight: 500; }
.related-desc { font-size: 0.78rem; color: var(--text-3); margin-bottom: 0.75rem; }
.related-link {
    display: flex; align-items: center; justify-content: space-between;
    font-size: 0.875rem; color: var(--accent); text-decoration: none;
    padding: 0.5rem 0.875rem; background: var(--accent-glow);
    border-radius: var(--radius); border: 1px solid rgba(108,143,255,0.2);
    transition: all 0.15s;
}
.related-link:hover { background: var(--accent); color: #fff; }

/* ---- SCROLLBAR ---- */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: var(--bg); }
::-webkit-scrollbar-thumb { background: var(--border-2); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: var(--text-3); }

/* ---- RESPONSIVE ---- */
@media (max-width: 640px) {
    .control-row { grid-template-columns: 1fr; }
    .action-row { flex-direction: column; }
    .nav-user { display: none; }
    .stats-bar { gap: 0.5rem; }
}

    </style>
</head>
<body class="auth-body">
    <div class="auth-bg">
        <div class="auth-grid"></div>
    </div>
    <div class="auth-container">
        <div class="auth-brand">
            <span class="brand-icon-lg">&#9635;</span>
            <h1 class="brand-name">WorkLog</h1>
            <p class="brand-tagline">Your daily work chronicle</p>
        </div>
        @yield('content')
    </div>
</body>
</html>
