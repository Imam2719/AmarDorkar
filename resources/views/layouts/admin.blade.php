<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="{{ session('admin_theme','light')==='dark' ? 'adm-dark' : '' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', __('admin.dashboard')) — amardorkar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════
   AMARDORKAR ADMIN — LIGHT + DARK THEME
   ═══════════════════════════════════════════ */
:root {
  --primary:#2563eb;
  --primary-h:#1d4ed8;
  --primary-s:#eff6ff;
  --primary-t:rgba(37,99,235,.1);
  --success:#16a34a; --success-s:#f0fdf4;
  --warning:#d97706; --warning-s:#fffbeb;
  --danger:#dc2626;  --danger-s:#fef2f2;
  --purple:#7c3aed;  --purple-s:#f5f3ff;

  --sidebar-w:248px;
  --topbar-h:60px;

  /* Light mode defaults */
  --bg:#f8fafc;
  --white:#ffffff;
  --surface:#ffffff;
  --border:#e2e8f0;
  --border-2:#f1f5f9;
  --text:#0f172a;
  --text-2:#334155;
  --muted:#64748b;
  --muted-2:#94a3b8;
  --hover:#f8fafc;

  --r-sm:8px;
  --r:12px;
  --r-lg:16px;
  --r-xl:20px;

  --sh-xs:0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
  --sh-sm:0 2px 8px rgba(0,0,0,.07),0 1px 3px rgba(0,0,0,.04);
  --sh:0 4px 16px rgba(0,0,0,.08),0 2px 6px rgba(0,0,0,.04);
  --sh-lg:0 8px 32px rgba(0,0,0,.1),0 4px 12px rgba(0,0,0,.05);
}

/* Dark mode overrides */
.adm-dark {
  --bg:#0f172a;
  --white:#1e293b;
  --surface:#1e293b;
  --border:#334155;
  --border-2:#1e293b;
  --text:#f1f5f9;
  --text-2:#cbd5e1;
  --muted:#94a3b8;
  --muted-2:#64748b;
  --hover:#334155;
  --primary-s:rgba(37,99,235,.15);
  --sh-xs:0 1px 3px rgba(0,0,0,.3),0 1px 2px rgba(0,0,0,.2);
  --sh-sm:0 2px 8px rgba(0,0,0,.35),0 1px 3px rgba(0,0,0,.2);
  --sh:0 4px 16px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.25);
}

*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{
  background:var(--bg);color:var(--text);
  font-family:'DM Sans','Hind Siliguri',sans-serif;
  font-size:14px;line-height:1.5;
  display:flex;min-height:100vh;overflow-x:hidden;
}
[lang="bn"],
.bn { font-family:'Hind Siliguri',sans-serif !important; }

/* ─── SCROLLBAR ───────────────────────── */
::-webkit-scrollbar{width:5px;height:5px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px;}
::-webkit-scrollbar-thumb:hover{background:#94a3b8;}

/* ─── SIDEBAR ─────────────────────────── */
.adm-sidebar {
  width:var(--sidebar-w);
  height:100vh;
  background:var(--white);
  border-right:1px solid var(--border);
  position:fixed;top:0;left:0;z-index:500;
  display:flex;flex-direction:column;
  box-shadow:var(--sh-sm);
  transition:transform .28s cubic-bezier(.4,0,.2,1);
  overflow:hidden;
}

/* Brand */
.adm-brand {
  height:auto;
  min-height:var(--topbar-h);
  padding:10px 16px;
  display:flex;align-items:center;gap:10px;
  border-bottom:1px solid var(--border);
  text-decoration:none;flex-shrink:0;
  transition:background .15s;
}
.adm-brand:hover{background:var(--hover);}
.adm-brand-logo {
  height:38px;width:auto;max-width:140px;
  object-fit:contain;flex-shrink:0;
}
.adm-brand-sub{font-size:10px;color:var(--muted);font-weight:500;margin-top:2px;}

/* Nav */
.adm-nav {
  flex:1;overflow-y:auto;overflow-x:hidden;
  padding:12px 10px 8px;
}
/* Nav group collapsible */
.adm-nav-group {
  font-size:10px;font-weight:700;letter-spacing:.9px;text-transform:uppercase;
  color:var(--muted-2);padding:14px 8px 5px;
  display:flex;align-items:center;justify-content:space-between;
  cursor:pointer;user-select:none;
  border-radius:var(--r-sm);
  transition:background .15s;
}
.adm-nav-group:first-child{padding-top:4px;}
.adm-nav-group:hover{background:var(--hover);}
.adm-nav-group-arrow{font-size:9px;transition:transform .2s;color:var(--muted-2);}
.adm-nav-group.collapsed .adm-nav-group-arrow{transform:rotate(-90deg);}
.adm-nav-section{overflow:hidden;transition:max-height .3s ease;}

.adm-nav-item {
  display:flex;align-items:center;gap:9px;
  padding:9px 10px;border-radius:var(--r-sm);
  color:var(--muted);text-decoration:none;
  font-size:13px;font-weight:500;
  margin-bottom:1px;
  transition:all .15s;white-space:nowrap;position:relative;
}
.adm-nav-item:hover{background:var(--hover);color:var(--text);}
.adm-nav-item.active{
  background:var(--primary-s);color:var(--primary);font-weight:600;
}
.adm-nav-item.active .adm-nav-ic{background:var(--primary);color:#fff;}
.adm-nav-ic {
  width:28px;height:28px;border-radius:7px;
  background:var(--bg);
  display:flex;align-items:center;justify-content:center;
  font-size:12px;flex-shrink:0;transition:all .15s;
}
.adm-nav-item:hover .adm-nav-ic{background:var(--border);}

/* Sidebar footer */
.adm-sidebar-foot {
  padding:10px;border-top:1px solid var(--border);flex-shrink:0;
}
.adm-user-row {
  display:flex;align-items:center;gap:10px;
  padding:10px;border-radius:var(--r);
  background:var(--bg);margin-bottom:8px;
}
.adm-user-av {
  width:32px;height:32px;border-radius:50%;
  background:linear-gradient(135deg,var(--primary),var(--purple));
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:12px;font-weight:700;flex-shrink:0;
}
.adm-user-name{font-size:12px;font-weight:700;color:var(--text);}
.adm-user-role{font-size:10px;color:var(--muted);margin-top:1px;}
.adm-logout {
  width:100%;display:flex;align-items:center;justify-content:center;gap:7px;
  padding:9px;border-radius:var(--r-sm);border:1.5px solid #fecaca;
  background:#fff5f5;color:#dc2626;
  font-size:12px;font-weight:600;cursor:pointer;
  transition:all .15s;
}
.adm-logout:hover{background:#fee2e2;border-color:#f87171;}

/* ─── OVERLAY (mobile) ────────────────── */
.adm-overlay{
  display:none;position:fixed;inset:0;
  background:rgba(15,23,42,.4);z-index:499;
  backdrop-filter:blur(3px);
}
.adm-overlay.on{display:block;}

/* ─── MAIN ────────────────────────────── */
.adm-main{
  margin-left:var(--sidebar-w);
  flex:1;display:flex;flex-direction:column;
  min-height:100vh;max-width:100%;
}

/* ─── TOPBAR ──────────────────────────── */
.adm-topbar {
  height:var(--topbar-h);
  background:var(--white);
  border-bottom:1px solid var(--border);
  padding:0 20px;
  display:flex;align-items:center;justify-content:space-between;
  position:sticky;top:0;z-index:200;
  box-shadow:var(--sh-xs);
}
.adm-topbar-l{display:flex;align-items:center;gap:12px;}
.adm-menu-btn{
  display:none;width:34px;height:34px;border-radius:var(--r-sm);
  background:none;border:1.5px solid var(--border);
  cursor:pointer;align-items:center;justify-content:center;
  color:var(--muted);font-size:14px;transition:all .15s;
}
.adm-menu-btn:hover{background:var(--hover);color:var(--text);}
.adm-page-title{font-size:16px;font-weight:700;color:var(--text);}

.adm-topbar-r{display:flex;align-items:center;gap:6px;}

/* Language toggle */
.adm-lang-wrap{
  display:flex;align-items:center;
  background:var(--bg);border:1.5px solid var(--border);
  border-radius:20px;padding:3px;gap:2px;
}
.adm-lang-btn{
  padding:4px 12px;border-radius:16px;font-size:11px;font-weight:700;
  text-decoration:none;color:var(--muted);transition:all .15s;border:none;background:none;cursor:pointer;
}
.adm-lang-btn.on{background:var(--primary);color:#fff;box-shadow:0 1px 4px rgba(37,99,235,.3);}
.adm-lang-btn:hover:not(.on){color:var(--text);background:var(--border);}

.adm-topbar-btn{
  width:34px;height:34px;border-radius:var(--r-sm);
  border:1.5px solid var(--border);background:var(--white);
  display:flex;align-items:center;justify-content:center;
  color:var(--muted);font-size:13px;cursor:pointer;
  text-decoration:none;transition:all .15s;
}
.adm-topbar-btn:hover{background:var(--primary-s);border-color:var(--primary);color:var(--primary);}

/* ─── CONTENT ─────────────────────────── */
.adm-content{flex:1;padding:20px;}

/* ─── ALERTS ──────────────────────────── */
.adm-alert{
  display:flex;align-items:flex-start;gap:10px;
  padding:12px 16px;border-radius:var(--r);
  font-size:13px;font-weight:500;margin-bottom:14px;
  animation:fadeSlide .3s ease both;
}
.adm-alert .adm-alert-icon{font-size:15px;margin-top:1px;flex-shrink:0;}
.adm-alert-close{margin-left:auto;background:none;border:none;cursor:pointer;opacity:.5;font-size:14px;}
.adm-alert-close:hover{opacity:1;}
.adm-alert.ok {background:rgba(21,128,61,.12);border:1px solid rgba(21,128,61,.3);color:#4ade80;}
.adm-alert.err{background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);color:#f87171;}
.adm-alert.wrn{background:rgba(217,119,6,.1);border:1px solid rgba(217,119,6,.3);color:#fbbf24;}
.adm-alert.inf{background:rgba(29,78,216,.1);border:1px solid rgba(29,78,216,.3);color:#60a5fa;}

/* ─── CARD ────────────────────────────── */
.adm-card{
  background:var(--white);border:1px solid var(--border);
  border-radius:var(--r-lg);box-shadow:var(--sh-xs);
  overflow:hidden;
}
.adm-card-head{
  padding:14px 18px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;gap:12px;
  background:var(--white);
}
.adm-card-title{font-size:14px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px;}
.adm-card-body{padding:18px;}

/* ─── STAT CARDS ──────────────────────── */
.adm-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;}
.adm-stat{
  background:var(--white);border:1px solid var(--border);
  border-radius:var(--r-lg);padding:18px;
  box-shadow:var(--sh-xs);
  transition:box-shadow .2s,transform .2s;
  position:relative;overflow:hidden;
}
.adm-stat::before{
  content:'';position:absolute;inset:0 0 auto 0;height:3px;
  border-radius:var(--r-lg) var(--r-lg) 0 0;
}
.adm-stat.blue::before{background:var(--primary);}
.adm-stat.green::before{background:var(--success);}
.adm-stat.amber::before{background:var(--warning);}
.adm-stat.red::before{background:var(--danger);}
.adm-stat.purple::before{background:var(--purple);}
.adm-stat:hover{box-shadow:var(--sh);transform:translateY(-2px);}
.adm-stat-icon{
  width:42px;height:42px;border-radius:var(--r-sm);
  display:flex;align-items:center;justify-content:center;font-size:17px;color:#fff;
  flex-shrink:0;
}
.adm-stat-icon.blue{background:var(--primary);}
.adm-stat-icon.green{background:var(--success);}
.adm-stat-icon.amber{background:var(--warning);}
.adm-stat-icon.red{background:var(--danger);}
.adm-stat-icon.purple{background:var(--purple);}
.adm-stat-val{font-size:22px;font-weight:800;color:var(--text);letter-spacing:-.5px;margin-top:10px;}
.adm-stat-lbl{font-size:12px;color:var(--muted);font-weight:500;margin-top:2px;}

/* ─── TABLE ───────────────────────────── */
.adm-table{width:100%;border-collapse:collapse;}
.adm-table thead th{
  font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;
  color:var(--muted);background:var(--bg);
  border-bottom:2px solid var(--border);
  padding:11px 14px;white-space:nowrap;
}
.adm-table tbody td{
  font-size:13px;vertical-align:middle;
  border-bottom:1px solid var(--border-2);
  padding:12px 14px;
}
.adm-table tbody tr:hover td{background:var(--hover);}
.adm-table tbody tr:last-child td{border-bottom:none;}

/* ─── BADGES / STATUS ─────────────────── */
.adm-badge{
  display:inline-flex;align-items:center;gap:5px;
  padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;
  white-space:nowrap;
}
.adm-badge::before{content:'';width:5px;height:5px;border-radius:50%;flex-shrink:0;}
.s-pending   {background:#fef9c3;color:#854d0e;} .s-pending::before   {background:#d97706;}
.s-confirmed {background:#d1fae5;color:#065f46;} .s-confirmed::before {background:#16a34a;}
.s-processing{background:#dbeafe;color:#1e40af;} .s-processing::before{background:#2563eb;}
.s-shipped   {background:#ede9fe;color:#4c1d95;} .s-shipped::before   {background:#7c3aed;}
.s-delivered {background:#d1fae5;color:#065f46;} .s-delivered::before {background:#16a34a;}
.s-cancelled {background:#fee2e2;color:#991b1b;} .s-cancelled::before {background:#dc2626;}
.s-paid      {background:#d1fae5;color:#065f46;} .s-paid::before      {background:#16a34a;}
.s-failed    {background:#fee2e2;color:#991b1b;} .s-failed::before    {background:#dc2626;}
.s-refunded  {background:#fef9c3;color:#854d0e;} .s-refunded::before  {background:#d97706;}

/* ─── BUTTONS ─────────────────────────── */
.adm-btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:8px 16px;border-radius:var(--r-sm);
  font-size:13px;font-weight:600;cursor:pointer;
  border:none;text-decoration:none;white-space:nowrap;
  transition:all .15s;
}
.adm-btn-primary{background:var(--primary);color:#fff;box-shadow:0 1px 6px rgba(37,99,235,.3);}
.adm-btn-primary:hover{background:var(--primary-h);color:#fff;box-shadow:0 3px 10px rgba(37,99,235,.4);transform:translateY(-1px);}
.adm-btn-success{background:var(--success);color:#fff;}
.adm-btn-success:hover{background:#15803d;color:#fff;transform:translateY(-1px);}
.adm-btn-danger{background:var(--danger);color:#fff;}
.adm-btn-danger:hover{background:#b91c1c;color:#fff;}
.adm-btn-ghost{background:var(--white);border:1.5px solid var(--border);color:var(--muted);}
.adm-btn-ghost:hover{background:var(--hover);color:var(--text);}
.adm-btn-sm{padding:6px 12px;font-size:12px;}
.adm-btn-xs{padding:4px 9px;font-size:11px;border-radius:6px;}
.adm-btn-icon{width:32px;height:32px;padding:0;justify-content:center;}

/* ─── FORMS ───────────────────────────── */
.adm-input,.adm-select,.form-control,.form-select{
  border:1.5px solid var(--border);border-radius:var(--r-sm);
  padding:8px 12px;font-size:13px;color:var(--text);
  background:var(--white);transition:all .15s;width:100%;
}
.adm-input:focus,.adm-select:focus,.form-control:focus,.form-select:focus{
  border-color:var(--primary);
  box-shadow:0 0 0 3px rgba(37,99,235,.1);outline:none;
}
.adm-label,.form-label{
  font-size:12px;font-weight:700;color:var(--text-2);margin-bottom:5px;display:block;
}

/* ─── PAGINATION ──────────────────────── */
.pagination .page-link{
  border-radius:var(--r-sm) !important;margin:0 2px;
  border:1.5px solid var(--border);color:var(--muted);
  font-size:12px;font-weight:600;padding:6px 11px;transition:all .15s;
}
.pagination .page-link:hover{background:var(--primary-s);border-color:var(--primary);color:var(--primary);}
.pagination .page-item.active .page-link{background:var(--primary);border-color:var(--primary);color:#fff;box-shadow:0 1px 6px rgba(37,99,235,.3);}

/* ─── MODAL ───────────────────────────── */
.modal-content{border:none;border-radius:var(--r-xl);box-shadow:var(--sh-lg);}
.modal-header{border-bottom:1px solid var(--border);padding:18px 22px;background:var(--white);border-radius:var(--r-xl) var(--r-xl) 0 0;}
.modal-body{padding:22px;background:var(--white);}
.modal-footer{border-top:1px solid var(--border);padding:14px 22px;gap:8px;background:var(--white);border-radius:0 0 var(--r-xl) var(--r-xl);}
.modal-title{font-weight:700;font-size:15px;color:var(--text);}

/* ─── SEARCH BOX ──────────────────────── */
.adm-search-wrap{position:relative;}
.adm-search-wrap .adm-si{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted-2);font-size:12px;pointer-events:none;}
.adm-search-wrap input{padding-left:32px !important;}

/* ─── ANIMATIONS ──────────────────────── */
@keyframes fadeSlide{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
.fade-up{animation:fadeUp .32s ease both;}
.fade-up-1{animation:fadeUp .32s .05s ease both;}
.fade-up-2{animation:fadeUp .32s .1s ease both;}
.fade-up-3{animation:fadeUp .32s .15s ease both;}

/* ─── RESPONSIVE ──────────────────────── */
@media(max-width:991px){
  .adm-sidebar{transform:translateX(-100%);}
  .adm-sidebar.open{transform:none;}
  .adm-main{margin-left:0;}
  .adm-menu-btn{display:flex !important;}
  .adm-content{padding:14px;}
  .adm-stats{grid-template-columns:repeat(2,1fr) !important;gap:12px;}
}
@media(max-width:575px){
  /* Keep 2 cols even on small phones — never go to 1 col */
  .adm-stats{grid-template-columns:repeat(2,1fr) !important;gap:10px;}
  .adm-stat{padding:14px 12px;}
  .adm-stat-val{font-size:18px;}
  .adm-topbar{padding:0 12px;}
  .adm-page-title{font-size:13px;}
  /* Make table scrollable */
  .table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch;}
}

/* ─── STAT GRID (used by orders/sales/customers pages) ─── */
.stats-grid{
  display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;
}
.stat-card{
  background:var(--white);border:1px solid var(--border);
  border-radius:var(--r-lg);padding:18px;
  box-shadow:var(--sh-xs);
  transition:box-shadow .2s,transform .2s;
  position:relative;overflow:hidden;
}
.stat-card:hover{box-shadow:var(--sh);transform:translateY(-2px);}
.stat-header{display:flex;justify-content:space-between;align-items:flex-start;}
.stat-number{font-size:22px;font-weight:800;color:var(--text);letter-spacing:-.5px;}
.stat-label{font-size:12px;color:var(--muted);font-weight:500;margin-top:2px;}
.stat-icon{
  width:42px;height:42px;border-radius:var(--r-sm);
  display:flex;align-items:center;justify-content:center;font-size:17px;color:#fff;
  flex-shrink:0;
}

/* card-header / card-title for pages using Bootstrap card classes */
.card-header{
  padding:14px 18px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;gap:12px;
  background:var(--white);
}
.card-title{font-size:14px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px;margin:0;}

/* ─── PREMIUM UI ENHANCEMENTS ─────────────── */
/* Gradient top border on stat cards */
.stat-card::before{
  content:'';position:absolute;inset:0 0 auto 0;height:3px;border-radius:var(--r-lg) var(--r-lg) 0 0;
}
.stat-card.blue::before,.stat-card:nth-child(4n+1)::before{background:var(--primary);}
.stat-card.green::before,.stat-card:nth-child(4n+2)::before{background:var(--success);}
.stat-card.amber::before,.stat-card:nth-child(4n+3)::before{background:var(--warning);}
.stat-card.red::before,.stat-card:nth-child(4n+4)::before{background:var(--danger);}

/* Better card hover */
.adm-card:hover{box-shadow:var(--sh);transition:box-shadow .2s;}

/* Better table on mobile */
@media(max-width:767px){
  .adm-table td,.adm-table th{padding:9px 10px;font-size:12px;}
  .table>:not(caption)>*>*{padding:.55rem .7rem;font-size:12px;}
  /* Mobile: hide less-critical columns */
  .adm-content .card-body{padding:0 !important;}
}

/* Quick Actions button on mobile */
@media(max-width:575px){
  .adm-content .row.g-3 .col-md-3.col-6 a{
    font-size:11px;padding:10px 8px;
  }
}

@keyframes animate-fadeInUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
.animate-fadeInUp{animation:animate-fadeInUp .32s ease both;}

@media(max-width:991px){
  .stats-grid{grid-template-columns:repeat(2,1fr) !important;gap:12px;}
}
@media(max-width:575px){
  /* Keep 2 cols on small phones */
  .stats-grid{grid-template-columns:repeat(2,1fr) !important;gap:10px;}
  .stat-card{padding:14px 12px;}
  .stat-number{font-size:18px;}
}

/* ─── STATUS BADGES (used by orders page) ─── */
.status-badge{
  display:inline-flex;align-items:center;gap:4px;
  padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;
}
.status-pending   {background:#fef9c3;color:#854d0e;}
.status-confirmed {background:#d1fae5;color:#065f46;}
.status-processing{background:#dbeafe;color:#1e40af;}
.status-shipped   {background:#ede9fe;color:#4c1d95;}
.status-delivered {background:#d1fae5;color:#065f46;}
.status-cancelled {background:#fee2e2;color:#991b1b;}
.payment-pending  {background:#fef9c3;color:#854d0e;}
.payment-paid     {background:#d1fae5;color:#065f46;}
.payment-failed   {background:#fee2e2;color:#991b1b;}
.payment-refunded {background:#fffbeb;color:#d97706;}

/* Override Bootstrap defaults cleanly */
.btn{border-radius:var(--r-sm);}
.table>:not(caption)>*>*{padding:.7rem .875rem;}
.card{border-radius:var(--r-lg);border-color:var(--border);}
.badge{font-weight:600;}
.form-control,.form-select{border-color:var(--border);font-size:13px;}
.form-control:focus,.form-select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,.1);}
.btn-primary{background:var(--primary);border-color:var(--primary);}
.btn-primary:hover{background:var(--primary-h);border-color:var(--primary-h);}
.btn-success{background:var(--success);border-color:var(--success);}
.btn-danger{background:var(--danger);border-color:var(--danger);}
.text-primary{color:var(--primary) !important;}
.text-success{color:var(--success) !important;}
.text-warning{color:var(--warning) !important;}
.text-danger{color:var(--danger) !important;}
.bg-primary{background:var(--primary) !important;}
.bg-success{background:var(--success) !important;}
.bg-warning{background:var(--warning) !important;}
.bg-danger{background:var(--danger) !important;}
.alert{border-radius:var(--r);}
.alert-success{background:rgba(21,128,61,.12);border-color:rgba(21,128,61,.3);color:#4ade80;}
.alert-danger{background:rgba(220,38,38,.1);border-color:rgba(220,38,38,.3);color:#f87171;}
.alert-warning{background:rgba(217,119,6,.1);border-color:rgba(217,119,6,.3);color:#fbbf24;}
.alert-info{background:rgba(29,78,216,.1);border-color:rgba(29,78,216,.3);color:#60a5fa;}

/* ═══════════════════════════════════════════════════════════════
   DARK MODE — Bootstrap component overrides
   All white/light Bootstrap defaults overridden here so every
   page (orders, products, categories, discounts, flash-sales,
   sales, customers, dashboard) goes fully dark.
═══════════════════════════════════════════════════════════════ */

/* ── Card ── */
.adm-dark .card {
  background:var(--surface) !important;
  border-color:var(--border) !important;
  color:var(--text) !important;
}
.adm-dark .card-header {
  background:var(--white) !important;
  border-color:var(--border) !important;
  color:var(--text) !important;
}
.adm-dark .card-body  { background:var(--surface) !important; color:var(--text) !important; }
.adm-dark .card-footer{ background:var(--white) !important;   border-color:var(--border) !important; }

/* ── Table ── */
.adm-dark .table {
  --bs-table-bg: var(--surface);
  --bs-table-color: var(--text);
  --bs-table-border-color: var(--border);
  --bs-table-hover-bg: var(--hover);
  --bs-table-striped-bg: var(--bg);
  color:var(--text) !important;
  border-color:var(--border) !important;
}
.adm-dark .table > :not(caption) > * > * {
  background-color: var(--surface) !important;
  color: var(--text) !important;
  border-color: var(--border) !important;
}
.adm-dark .table thead th,
.adm-dark .table thead tr,
.adm-dark .table > thead {
  background:var(--bg) !important;
  color:var(--muted) !important;
  border-color:var(--border) !important;
}
.adm-dark .table-hover > tbody > tr:hover > * {
  background-color:var(--hover) !important;
  color:var(--text) !important;
}
.adm-dark .table-borderless > :not(caption) > * > * { border-color:transparent !important; }
.adm-dark th { color:var(--muted) !important; }
.adm-dark td { color:var(--text) !important; }

/* ── Modal ── */
.adm-dark .modal-content  { background:var(--surface) !important; border-color:var(--border) !important; color:var(--text) !important; }
.adm-dark .modal-header   { background:var(--white) !important;   border-color:var(--border) !important; }
.adm-dark .modal-body     { background:var(--surface) !important; color:var(--text) !important; }
.adm-dark .modal-footer   { background:var(--white) !important;   border-color:var(--border) !important; }
.adm-dark .modal-title    { color:var(--text) !important; }
.adm-dark .btn-close      { filter:invert(1) grayscale(1); }

/* ── Flash Sale modal danger header — keep red bg, just fix close btn ── */
.adm-dark .modal-header.bg-danger { background:var(--danger) !important; }
.adm-dark .modal-header.bg-danger .btn-close { filter:none; }

/* ── Forms ── */
.adm-dark .input-group-text {
  background:var(--bg) !important;
  border-color:var(--border) !important;
  color:var(--text) !important;
}
.adm-dark .form-check-input {
  background-color:var(--bg) !important;
  border-color:var(--border) !important;
}
.adm-dark .form-check-input:checked { background-color:var(--primary) !important; border-color:var(--primary) !important; }
.adm-dark .form-check-label { color:var(--text) !important; }
.adm-dark .form-text  { color:var(--muted) !important; }
.adm-dark .form-label { color:var(--text-2) !important; }
.adm-dark fieldset, .adm-dark legend { color:var(--text) !important; }
.adm-dark .form-control-color { background:var(--surface) !important; border-color:var(--border) !important; }
.adm-dark .invalid-feedback { color:#f87171 !important; }
.adm-dark .is-invalid { border-color:#f87171 !important; }

/* ── Buttons (outline variants) ── */
.adm-dark .btn-outline-primary   { color:#60a5fa !important; border-color:#60a5fa !important; }
.adm-dark .btn-outline-primary:hover { background:#60a5fa !important; color:#0f172a !important; }
.adm-dark .btn-outline-secondary { color:var(--muted) !important; border-color:var(--border) !important; }
.adm-dark .btn-outline-secondary:hover { background:var(--hover) !important; color:var(--text) !important; }
.adm-dark .btn-outline-success   { color:#4ade80 !important; border-color:#4ade80 !important; }
.adm-dark .btn-outline-success:hover { background:#4ade80 !important; color:#0f172a !important; }
.adm-dark .btn-outline-danger    { color:#f87171 !important; border-color:#f87171 !important; }
.adm-dark .btn-outline-danger:hover  { background:#f87171 !important; color:#0f172a !important; }
.adm-dark .btn-outline-warning   { color:#fbbf24 !important; border-color:#fbbf24 !important; }
.adm-dark .btn-outline-warning:hover { background:#fbbf24 !important; color:#0f172a !important; }
.adm-dark .btn-outline-info      { color:#38bdf8 !important; border-color:#38bdf8 !important; }
.adm-dark .btn-outline-info:hover    { background:#38bdf8 !important; color:#0f172a !important; }
.adm-dark .btn-secondary { background:var(--hover) !important; border-color:var(--border) !important; color:var(--text) !important; }

/* ── Badges ── */
.adm-dark .badge.bg-secondary { background:#334155 !important; color:#cbd5e1 !important; }
.adm-dark .badge.bg-primary   { background:rgba(37,99,235,.4) !important; color:#93c5fd !important; }
.adm-dark .badge.bg-success   { background:rgba(22,163,74,.4) !important; color:#86efac !important; }
.adm-dark .badge.bg-danger    { background:rgba(220,38,38,.4) !important; color:#fca5a5 !important; }
.adm-dark .badge.bg-warning   { background:rgba(217,119,6,.4) !important; color:#fcd34d !important; }
.adm-dark .badge.bg-info      { background:rgba(14,165,233,.4) !important; color:#7dd3fc !important; }
.adm-dark .badge.bg-warning.text-dark { color:#fcd34d !important; }

/* ── Borders & helpers ── */
.adm-dark .border-top   { border-color:var(--border) !important; }
.adm-dark .border-bottom{ border-color:var(--border) !important; }
.adm-dark .border       { border-color:var(--border) !important; }
.adm-dark .text-muted   { color:var(--muted) !important; }
.adm-dark .text-dark    { color:var(--text) !important; }
.adm-dark small         { color:var(--muted) !important; }
.adm-dark code          { background:var(--bg) !important; color:#f472b6 !important; border-radius:4px; padding:1px 5px; }
.adm-dark hr            { border-color:var(--border) !important; }
.adm-dark .p-3.border-top { border-color:var(--border) !important; }

/* ── Stats cards ── */
.adm-dark .stat-card    { background:var(--surface) !important; border-color:var(--border) !important; }
.adm-dark .stat-number  { color:var(--text) !important; }
.adm-dark .stat-label   { color:var(--muted) !important; }

/* ── Alerts (override Bootstrap default white bg) ── */
.adm-dark .alert         { border-width:1px; }
.adm-dark .alert-success { background:rgba(21,128,61,.15) !important; border-color:rgba(21,128,61,.4) !important; color:#4ade80 !important; }
.adm-dark .alert-danger  { background:rgba(220,38,38,.15) !important; border-color:rgba(220,38,38,.4) !important; color:#f87171 !important; }
.adm-dark .alert-warning { background:rgba(217,119,6,.15) !important; border-color:rgba(217,119,6,.4) !important; color:#fbbf24 !important; }
.adm-dark .alert-info    { background:rgba(14,165,233,.15) !important; border-color:rgba(14,165,233,.4) !important; color:#38bdf8 !important; }

/* ── Category icon boxes ── */
.adm-dark [style*="border-radius:10px"] { }  /* dynamic bg from PHP — intentional */

/* ── Scrollbar for dark ── */
.adm-dark ::-webkit-scrollbar-track { background:var(--bg); }


</style>
@stack('styles')
</head>
<body>

{{-- ═══ SIDEBAR ═══ --}}
<aside class="adm-sidebar" id="admSidebar">

  {{-- Brand --}}
  <a href="{{ route('home') }}" class="adm-brand" title="{{ app()->getLocale()==='bn' ? 'সাইট দেখুন' : 'View Store' }}">
    @php $adminLogo = app()->getLocale()==='bn' ? 'logo_bng.png' : 'logo_eng.png'; @endphp
    <img src="{{ asset($adminLogo) }}" alt="amardorkar" class="adm-brand-logo"
      onerror="this.src='{{ asset('logo.png') }}';this.onerror=null;">
    <div class="adm-brand-sub">Admin Panel</div>
  </a>

  {{-- Navigation --}}
  <nav class="adm-nav" id="admNav">

    <div class="adm-nav-group" onclick="toggleNavSection('secMain',this)">
      <span>{{ app()->getLocale()==='bn' ? 'প্রধান' : 'Main' }}</span>
      <i class="fas fa-chevron-down adm-nav-group-arrow"></i>
    </div>
    <div class="adm-nav-section" id="secMain">

    <a href="{{ route('admin.dashboard') }}"
       class="adm-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <span class="adm-nav-ic"><i class="fas fa-home"></i></span>
      <span>{{ app()->getLocale()==='bn' ? 'ড্যাশবোর্ড' : 'Dashboard' }}</span>
    </a>

    <a href="{{ route('admin.orders.index') }}"
       class="adm-nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
      <span class="adm-nav-ic"><i class="fas fa-shopping-bag"></i></span>
      <span>{{ app()->getLocale()==='bn' ? 'অর্ডার' : 'Orders' }}</span>
    </a>

    <a href="{{ route('admin.products.index') }}"
       class="adm-nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
      <span class="adm-nav-ic"><i class="fas fa-box-open"></i></span>
      <span>{{ app()->getLocale()==='bn' ? 'পণ্য' : 'Products' }}</span>
    </a>

    <a href="{{ route('admin.categories.index') }}"
       class="adm-nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
      <span class="adm-nav-ic"><i class="fas fa-layer-group"></i></span>
      <span>{{ app()->getLocale()==='bn' ? 'ক্যাটাগরি' : 'Categories' }}</span>
    </a>

    </div>

    <div class="adm-nav-group" onclick="toggleNavSection('secMarketing',this)">
      <span>{{ app()->getLocale()==='bn' ? 'মার্কেটিং' : 'Marketing' }}</span>
      <i class="fas fa-chevron-down adm-nav-group-arrow"></i>
    </div>
    <div class="adm-nav-section" id="secMarketing">

    <a href="{{ route('admin.discounts.index') }}"
       class="adm-nav-item {{ request()->routeIs('admin.discounts.*') ? 'active' : '' }}">
      <span class="adm-nav-ic"><i class="fas fa-percent"></i></span>
      <span>{{ app()->getLocale()==='bn' ? 'ছাড় / অফার' : 'Discounts' }}</span>
    </a>

    <a href="{{ route('admin.flash-sales.index') }}"
       class="adm-nav-item {{ request()->routeIs('admin.flash-sales.*') ? 'active' : '' }}">
      <span class="adm-nav-ic"><i class="fas fa-bolt"></i></span>
      <span>{{ app()->getLocale()==='bn' ? 'ফ্ল্যাশ সেল' : 'Flash Sale' }}</span>
    </a>

    <a href="{{ route('admin.banners.index') }}"
       class="adm-nav-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
      <span class="adm-nav-ic"><i class="fas fa-images"></i></span>
      <span>{{ app()->getLocale()==='bn' ? 'ব্যানার' : 'Banners' }}</span>
    </a>

    </div>

    <div class="adm-nav-group" onclick="toggleNavSection('secReports',this)">
      <span>{{ app()->getLocale()==='bn' ? 'রিপোর্ট' : 'Reports' }}</span>
      <i class="fas fa-chevron-down adm-nav-group-arrow"></i>
    </div>
    <div class="adm-nav-section" id="secReports">

    <a href="{{ route('admin.sales.index') }}"
       class="adm-nav-item {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
      <span class="adm-nav-ic"><i class="fas fa-chart-line"></i></span>
      <span>{{ app()->getLocale()==='bn' ? 'বিক্রয় ইতিহাস' : 'Sales History' }}</span>
    </a>

    <a href="{{ route('admin.customers.index') }}"
       class="adm-nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
      <span class="adm-nav-ic"><i class="fas fa-users"></i></span>
      <span>{{ app()->getLocale()==='bn' ? 'গ্রাহক' : 'Customers' }}</span>
    </a>

    <a href="{{ route('admin.reviews.index') }}"
       class="adm-nav-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
      <span class="adm-nav-ic"><i class="fas fa-star"></i></span>
      <span>{{ app()->getLocale()==='bn' ? 'রিভিউ' : 'Reviews' }}</span>
    </a>

    </div>

    @if(auth('admin')->user()?->isSuperAdmin())
    <div class="adm-nav-group" onclick="toggleNavSection('secSystem',this)">
      <span>{{ app()->getLocale()==='bn' ? 'সিস্টেম' : 'System' }}</span>
      <i class="fas fa-chevron-down adm-nav-group-arrow"></i>
    </div>
    <div class="adm-nav-section" id="secSystem">
    <a href="{{ route('admin.admins.index') }}"
       class="adm-nav-item {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
      <span class="adm-nav-ic"><i class="fas fa-user-shield"></i></span>
      <span>{{ app()->getLocale()==='bn' ? 'অ্যাডমিন' : 'Admin Manage' }}</span>
    </a>
    </div>
    @endif

  </nav>

  {{-- Footer --}}
  <div class="adm-sidebar-foot">
    <div class="adm-user-row">
      <div class="adm-user-av">{{ strtoupper(substr(auth('admin')->user()?->name ?? 'A', 0, 1)) }}</div>
      <div style="flex:1;min-width:0;">
        <div class="adm-user-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
          {{ auth('admin')->user()?->name ?? 'Admin' }}
        </div>
        <div class="adm-user-role">
          {{ auth('admin')->user()?->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
        </div>
      </div>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">
      @csrf
      <button class="adm-logout" type="submit">
        <i class="fas fa-sign-out-alt"></i>
        {{ app()->getLocale()==='bn' ? 'লগআউট' : 'Logout' }}
      </button>
    </form>
  </div>
</aside>

{{-- Overlay --}}
<div class="adm-overlay" id="admOverlay" onclick="admCloseSidebar()"></div>

{{-- ═══ MAIN ═══ --}}
<div class="adm-main">

  {{-- Topbar --}}
  <div class="adm-topbar">
    <div class="adm-topbar-l">
      <button class="adm-menu-btn" onclick="admToggleSidebar()" aria-label="Menu">
        <i class="fas fa-bars"></i>
      </button>
      <span class="adm-page-title">@yield('page-title', 'Dashboard')</span>
    </div>
    <div class="adm-topbar-r">

      {{-- BUG 2 FIX: POST forms prevent CDN/proxy caching language state-change requests --}}
      {{-- Language Toggle --}}
      <div class="adm-lang-wrap">
        <form method="POST" action="{{ route('language.switch') }}" style="display:inline;">
            @csrf
            <input type="hidden" name="lang" value="bn">
            <button type="submit" class="adm-lang-btn {{ app()->getLocale()==='bn' ? 'on' : '' }}">বাং</button>
        </form>
        <form method="POST" action="{{ route('language.switch') }}" style="display:inline;">
            @csrf
            <input type="hidden" name="lang" value="en">
            <button type="submit" class="adm-lang-btn {{ app()->getLocale()==='en' ? 'on' : '' }}">EN</button>
        </form>
      </div>

      {{-- Theme Toggle --}}
      <button onclick="admToggleTheme()" class="adm-topbar-btn" id="admThemeBtn"
        title="{{ session('admin_theme','light')==='dark' ? 'Light Mode' : 'Dark Mode' }}">
        <i class="fas {{ session('admin_theme','light')==='dark' ? 'fa-sun' : 'fa-moon' }}"></i>
      </button>

      {{-- Store link --}}
      <a href="{{ route('home') }}" target="_blank" class="adm-topbar-btn" title="{{ app()->getLocale()==='bn' ? 'সাইট দেখুন' : 'View Store' }}">
        <i class="fas fa-store"></i>
      </a>

    </div>
  </div>

  {{-- Content --}}
  <div class="adm-content">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="adm-alert ok fade-up">
      <i class="fas fa-check-circle adm-alert-icon"></i>
      <span>{{ session('success') }}</span>
      <button class="adm-alert-close" onclick="this.closest('.adm-alert').remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif
    @if(session('error'))
    <div class="adm-alert err fade-up">
      <i class="fas fa-exclamation-circle adm-alert-icon"></i>
      <span>{{ session('error') }}</span>
      <button class="adm-alert-close" onclick="this.closest('.adm-alert').remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="adm-alert wrn fade-up">
      <i class="fas fa-exclamation-triangle adm-alert-icon"></i>
      <span>{{ session('warning') }}</span>
      <button class="adm-alert-close" onclick="this.closest('.adm-alert').remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif
    @if($errors->any())
    <div class="adm-alert err fade-up">
      <i class="fas fa-exclamation-circle adm-alert-icon"></i>
      <div>@foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach</div>
      <button class="adm-alert-close" onclick="this.closest('.adm-alert').remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif

    @yield('content')

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── THEME TOGGLE ── */
function admToggleTheme(){
  const html = document.documentElement;
  const isDark = html.classList.toggle('adm-dark');
  const btn = document.getElementById('admThemeBtn');
  if(btn){
    btn.querySelector('i').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
    btn.title = isDark ? 'Light Mode' : 'Dark Mode';
  }
  fetch('/admin/theme', {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
    body: JSON.stringify({theme: isDark ? 'dark' : 'light'})
  });
}

function admToggleSidebar(){
  document.getElementById('admSidebar').classList.toggle('open');
  document.getElementById('admOverlay').classList.toggle('on');
  document.body.style.overflow = document.getElementById('admSidebar').classList.contains('open') ? 'hidden' : '';
}
function admCloseSidebar(){
  document.getElementById('admSidebar').classList.remove('open');
  document.getElementById('admOverlay').classList.remove('on');
  document.body.style.overflow = '';
}
window.addEventListener('resize',()=>{ if(window.innerWidth>991) admCloseSidebar(); });

/* ── COLLAPSIBLE NAV SECTIONS ── */
function toggleNavSection(id, groupEl){
  const sec = document.getElementById(id);
  if(!sec) return;
  const isOpen = sec.style.maxHeight && sec.style.maxHeight !== '0px';
  if(isOpen){
    sec.style.maxHeight = '0px';
    groupEl.classList.add('collapsed');
  } else {
    sec.style.maxHeight = sec.scrollHeight + 'px';
    groupEl.classList.remove('collapsed');
  }
}

/* Auto-expand sections on load — open all by default, collapse non-active */
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.adm-nav-section').forEach(sec => {
    const hasActive = sec.querySelector('.adm-nav-item.active');
    if(hasActive || sec.id === 'secMain'){
      sec.style.maxHeight = sec.scrollHeight + 'px';
    } else {
      sec.style.maxHeight = sec.scrollHeight + 'px'; // open all by default
    }
  });
});
</script>
@stack('modals')
@stack('scripts')
</body>
</html>