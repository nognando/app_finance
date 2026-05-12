<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NogFinance – Controle Financeiro</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
:root {
  --bg:         #0a0e1a;
  --bg2:        #0f1525;
  --bg3:        #141b2d;
  --card:       #161e30;
  --card2:      #1c2640;
  --border:     #1e2d47;
  --border2:    #263552;
  --text:       #e2e8f0;
  --text2:      #94a3b8;
  --text3:      #64748b;
  --green:      #10b981;
  --green2:     #059669;
  --red:        #ef4444;
  --red2:       #dc2626;
  --yellow:     #f59e0b;
  --blue:       #3b82f6;
  --purple:     #8b5cf6;
  --accent:     #6ee7b7;
  --radius:     12px;
  --radius-lg:  18px;
  --shadow:     0 4px 24px rgba(0,0,0,.4);
  --transition: .2s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
html { scroll-behavior:smooth; }
body {
  font-family:'Sora',sans-serif;
  background:var(--bg);
  color:var(--text);
  min-height:100vh;
  overflow-x:hidden;
}
/* ── SCROLLBAR ── */
::-webkit-scrollbar { width:6px; height:6px; }
::-webkit-scrollbar-track { background:var(--bg2); }
::-webkit-scrollbar-thumb { background:var(--border2); border-radius:99px; }

/* ── LAYOUT ── */
.app-wrap { display:flex; min-height:100vh; }

/* ── SIDEBAR ── */
.sidebar {
  width:240px; min-width:240px;
  background:var(--bg2);
  border-right:1px solid var(--border);
  display:flex; flex-direction:column;
  position:fixed; top:0; left:0; height:100vh;
  z-index:100;
  transition:transform var(--transition);
}
.sidebar-logo {
  padding:24px 20px 20px;
  border-bottom:1px solid var(--border);
  display:flex; align-items:center; gap:10px;
}
.logo-icon {
  width:36px; height:36px; border-radius:10px;
  background:linear-gradient(135deg,var(--green),var(--blue));
  display:flex; align-items:center; justify-content:center;
  font-size:18px;
}
.logo-text { font-weight:800; font-size:1.1rem; letter-spacing:-.03em; }
.logo-text span { color:var(--green); }
.sidebar-nav { flex:1; padding:16px 12px; overflow-y:auto; }
.nav-label { font-size:.65rem; font-weight:700; letter-spacing:.1em; color:var(--text3); text-transform:uppercase; padding:8px 8px 4px; }
.nav-item {
  display:flex; align-items:center; gap:10px;
  padding:10px 12px; border-radius:var(--radius);
  cursor:pointer; color:var(--text2); font-size:.875rem; font-weight:500;
  transition:background var(--transition), color var(--transition);
  margin-bottom:2px;
}
.nav-item:hover { background:var(--card2); color:var(--text); }
.nav-item.active { background:linear-gradient(90deg,rgba(16,185,129,.15),rgba(59,130,246,.08)); color:var(--green); border:1px solid rgba(16,185,129,.2); }
.nav-item svg { flex-shrink:0; }

/* ── MAIN ── */
.main {
  flex:1; margin-left:240px;
  display:flex; flex-direction:column;
  min-height:100vh;
}
.topbar {
  background:var(--bg2); border-bottom:1px solid var(--border);
  padding:0 24px; height:60px;
  display:flex; align-items:center; justify-content:space-between;
  position:sticky; top:0; z-index:50;
}
.topbar-left { display:flex; align-items:center; gap:12px; }
.page-title { font-size:1rem; font-weight:700; }
.month-nav {
  display:flex; align-items:center; gap:8px;
  background:var(--card); border:1px solid var(--border);
  border-radius:99px; padding:4px 6px;
}
.month-nav button {
  background:none; border:none; cursor:pointer;
  color:var(--text2); width:28px; height:28px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  transition:background var(--transition);
}
.month-nav button:hover { background:var(--card2); color:var(--text); }
.month-label { font-size:.85rem; font-weight:600; min-width:120px; text-align:center; }
.topbar-right { display:flex; align-items:center; gap:8px; }
.btn-menu { display:none; }

/* ── CONTENT ── */
.content { padding:24px; flex:1; }

/* ── VIEWS ── */
.view { display:none; }
.view.active { display:block; animation:fadeUp .3s ease; }
@keyframes fadeUp {
  from { opacity:0; transform:translateY(8px); }
  to   { opacity:1; transform:translateY(0); }
}

/* ── CARDS DE RESUMO ── */
.summary-grid {
  display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr));
  gap:16px; margin-bottom:24px;
}
.summary-card {
  background:var(--card); border:1px solid var(--border);
  border-radius:var(--radius-lg); padding:20px;
  position:relative; overflow:hidden;
  transition:border-color var(--transition);
}
.summary-card:hover { border-color:var(--border2); }
.summary-card::before {
  content:''; position:absolute; top:0; left:0; right:0; height:2px;
}
.summary-card.green::before { background:var(--green); }
.summary-card.red::before { background:var(--red); }
.summary-card.yellow::before { background:var(--yellow); }
.summary-card.blue::before { background:var(--blue); }
.sc-label { font-size:.75rem; font-weight:600; color:var(--text3); text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px; }
.sc-value { font-size:1.4rem; font-weight:800; font-family:'JetBrains Mono',monospace; letter-spacing:-.02em; }
.sc-value.green { color:var(--green); }
.sc-value.red { color:var(--red); }
.sc-value.yellow { color:var(--yellow); }
.sc-value.blue { color:var(--blue); }
.sc-sub { font-size:.75rem; color:var(--text3); margin-top:4px; }

/* ── GRID 2 COL ── */
.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px; }
@media(max-width:900px) { .grid-2 { grid-template-columns:1fr; } }

/* ── PAINEIS ── */
.panel {
  background:var(--card); border:1px solid var(--border);
  border-radius:var(--radius-lg); overflow:hidden;
}
.panel-header {
  padding:16px 20px; border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between;
}
.panel-title { font-size:.875rem; font-weight:700; }
.panel-body { padding:16px 20px; }

/* ── TABELAS ── */
.table-wrap { overflow-x:auto; }
table { width:100%; border-collapse:collapse; font-size:.8rem; }
th {
  text-align:left; padding:10px 14px;
  font-size:.7rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
  color:var(--text3); border-bottom:1px solid var(--border);
  white-space:nowrap;
}
td {
  padding:10px 14px; border-bottom:1px solid var(--border);
  vertical-align:middle;
}
tr:last-child td { border-bottom:none; }
tr:hover td { background:rgba(255,255,255,.02); }

/* ── BADGES ── */
.badge {
  display:inline-flex; align-items:center; gap:4px;
  padding:3px 8px; border-radius:99px; font-size:.7rem; font-weight:600;
}
.badge-green  { background:rgba(16,185,129,.15); color:var(--green); }
.badge-red    { background:rgba(239,68,68,.15); color:var(--red); }
.badge-yellow { background:rgba(245,158,11,.15); color:var(--yellow); }
.badge-blue   { background:rgba(59,130,246,.15); color:var(--blue); }
.badge-gray   { background:rgba(100,116,139,.15); color:var(--text2); }

/* ── BOTÕES ── */
.btn {
  display:inline-flex; align-items:center; gap:6px;
  padding:9px 16px; border-radius:var(--radius); font-size:.8rem; font-weight:600;
  cursor:pointer; border:none; transition:all var(--transition); white-space:nowrap;
}
.btn-primary { background:var(--green); color:#000; }
.btn-primary:hover { background:var(--green2); }
.btn-secondary { background:var(--card2); color:var(--text); border:1px solid var(--border); }
.btn-secondary:hover { border-color:var(--border2); }
.btn-danger { background:rgba(239,68,68,.15); color:var(--red); border:1px solid rgba(239,68,68,.2); }
.btn-danger:hover { background:rgba(239,68,68,.25); }
.btn-icon { padding:7px; border-radius:8px; }
.btn-sm { padding:5px 10px; font-size:.75rem; }

/* ── FILTROS ── */
.filters {
  display:flex; flex-wrap:wrap; gap:8px;
  margin-bottom:16px; align-items:center;
}
.filter-group { display:flex; gap:6px; }
select, input[type=text], input[type=date], input[type=number], textarea {
  background:var(--card); border:1px solid var(--border);
  color:var(--text); border-radius:var(--radius); padding:8px 12px;
  font-size:.8rem; font-family:'Sora',sans-serif;
  transition:border-color var(--transition);
  outline:none;
}
select:focus, input:focus, textarea:focus { border-color:var(--green); }
select option { background:var(--bg2); }
.search-input { min-width:200px; }

/* ── MODAL ── */
.modal-overlay {
  position:fixed; inset:0; z-index:1000;
  background:rgba(0,0,0,.7); backdrop-filter:blur(4px);
  display:none; align-items:center; justify-content:center; padding:16px;
}
.modal-overlay.open { display:flex; animation:fadeIn .15s ease; }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.modal {
  background:var(--bg2); border:1px solid var(--border);
  border-radius:var(--radius-lg); width:100%; max-width:520px;
  max-height:90vh; overflow-y:auto;
  animation:slideUp .2s cubic-bezier(.4,0,.2,1);
}
@keyframes slideUp {
  from { transform:translateY(20px); opacity:0; }
  to   { transform:translateY(0); opacity:1; }
}
.modal-header {
  padding:20px 24px 16px; border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between;
}
.modal-title { font-size:1rem; font-weight:700; }
.modal-body { padding:20px 24px; }
.modal-footer {
  padding:16px 24px; border-top:1px solid var(--border);
  display:flex; gap:8px; justify-content:flex-end;
}
.close-btn {
  background:none; border:none; cursor:pointer;
  color:var(--text2); padding:4px; border-radius:6px;
  transition:color var(--transition);
}
.close-btn:hover { color:var(--text); }

/* ── FORM ── */
.form-group { margin-bottom:16px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.form-label { display:block; font-size:.75rem; font-weight:600; color:var(--text2); margin-bottom:6px; }
.form-control { width:100%; }
.form-hint { font-size:.7rem; color:var(--text3); margin-top:4px; }
.tipo-toggle {
  display:flex; border:1px solid var(--border); border-radius:var(--radius); overflow:hidden;
}
.tipo-btn {
  flex:1; padding:10px; border:none; cursor:pointer; font-size:.8rem; font-weight:600;
  background:var(--card); color:var(--text2); transition:all var(--transition); font-family:'Sora',sans-serif;
}
.tipo-btn.active-receita { background:rgba(16,185,129,.2); color:var(--green); }
.tipo-btn.active-despesa { background:rgba(239,68,68,.2); color:var(--red); }

/* ── DOTS / INDICADORES ── */
.dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

/* ── CATEGORY DOT ── */
.cat-badge {
  display:inline-flex; align-items:center; gap:6px;
  font-size:.8rem; white-space:nowrap;
}

/* ── VENCIMENTO URGENTE ── */
.venc-atrasado { color:var(--red); font-weight:600; }
.venc-hoje     { color:var(--yellow); font-weight:600; }
.venc-proximo  { color:var(--blue); }

/* ── EMPTY STATE ── */
.empty {
  text-align:center; padding:48px 24px;
  color:var(--text3);
}
.empty svg { opacity:.3; margin-bottom:12px; }
.empty p { font-size:.875rem; }

/* ── TOAST ── */
.toast-container {
  position:fixed; bottom:24px; right:24px; z-index:9999;
  display:flex; flex-direction:column; gap:8px;
}
.toast {
  background:var(--card2); border:1px solid var(--border);
  border-radius:var(--radius); padding:12px 16px;
  font-size:.8rem; font-weight:500; max-width:280px;
  display:flex; align-items:center; gap:8px;
  animation:slideInRight .2s ease;
  box-shadow:var(--shadow);
}
@keyframes slideInRight {
  from { transform:translateX(20px); opacity:0; }
  to   { transform:translateX(0); opacity:1; }
}
.toast.success { border-color:rgba(16,185,129,.4); }
.toast.error   { border-color:rgba(239,68,68,.4); }
.toast.success::before { content:'✓'; color:var(--green); font-weight:800; }
.toast.error::before   { content:'✕'; color:var(--red); font-weight:800; }

/* ── PROGRESS BAR ── */
.progress { height:4px; background:var(--card2); border-radius:99px; overflow:hidden; margin-top:8px; }
.progress-bar { height:100%; border-radius:99px; transition:width 1s ease; }

/* ── CHIP TIPO ── */
.chip-receita { color:var(--green); font-weight:600; font-size:.8rem; }
.chip-despesa { color:var(--red); font-weight:600; font-size:.8rem; }

/* ── AÇÕES NA TABELA ── */
.actions { display:flex; gap:4px; }

/* ── CHART WRAPPER ── */
.chart-wrap { position:relative; height:200px; }

/* ── MOBILE OVERLAY ── */
.sidebar-overlay {
  display:none; position:fixed; inset:0; background:rgba(0,0,0,.6);
  z-index:99;
}

/* ── RESPONSIVE ── */
@media (max-width:768px) {
  .sidebar {
    transform:translateX(-100%);
  }
  .sidebar.open {
    transform:translateX(0);
    box-shadow:4px 0 32px rgba(0,0,0,.5);
  }
  .sidebar-overlay.open { display:block; }
  .main { margin-left:0; }
  .btn-menu { display:flex !important; }
  .content { padding:16px; }
  .summary-grid { grid-template-columns:1fr 1fr; }
  .form-row { grid-template-columns:1fr; }
  .topbar { padding:0 16px; }
  th:nth-child(n+5), td:nth-child(n+5) { display:none; }
}
@media (max-width:480px) {
  .summary-grid { grid-template-columns:1fr; }
  .month-label { min-width:90px; font-size:.75rem; }
}

/* ── DIVIDER ── */
.divider { border:none; border-top:1px solid var(--border); margin:16px 0; }

/* ── TOTAIS TRANSAÇÕES ── */
.totais-pill {
  display:flex; align-items:center; gap:10px;
  padding:10px 18px; border-radius:var(--radius);
  border:1px solid var(--border);
  background:var(--card);
}
.totais-receita { border-color:rgba(16,185,129,.3); background:rgba(16,185,129,.07); }
.totais-despesa { border-color:rgba(239,68,68,.3);  background:rgba(239,68,68,.07); }
.totais-saldo   { border-color:rgba(59,130,246,.3); background:rgba(59,130,246,.07); }
.totais-saldo.negativo { border-color:rgba(239,68,68,.3); background:rgba(239,68,68,.07); }
.totais-label { font-size:.72rem; font-weight:600; color:var(--text3); white-space:nowrap; }
.totais-valor { font-size:.95rem; font-weight:800; font-family:'JetBrains Mono',monospace; white-space:nowrap; }
.totais-receita .totais-valor { color:var(--green); }
.totais-despesa .totais-valor { color:var(--red); }
.totais-saldo   .totais-valor { color:var(--blue); }
.totais-saldo.negativo .totais-valor { color:var(--red); }

/* ── ANNUAL TABLE ── */
.annual-row td:first-child { font-weight:600; }
.annual-positive { color:var(--green); font-family:'JetBrains Mono',monospace; font-size:.8rem; }
.annual-negative { color:var(--red); font-family:'JetBrains Mono',monospace; font-size:.8rem; }
.annual-neutral  { color:var(--text2); font-family:'JetBrains Mono',monospace; font-size:.8rem; }

/* ── VALOR MONO ── */
.valor-mono { font-family:'JetBrains Mono',monospace; font-size:.85rem; }
</style>
</head>
<body>
<div class="app-wrap">

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">💰</div>
    <span class="logo-text">Nog<span>Finance</span></span>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">Principal</div>
    <div class="nav-item active" onclick="showView('dashboard')">
      <?= icon('layout-dashboard') ?> Dashboard
    </div>
    <div class="nav-item" onclick="showView('transacoes')">
      <?= icon('list') ?> Transações
    </div>
    <div class="nav-item" onclick="showView('pendentes')">
      <?= icon('clock') ?> Pendências
    </div>
    <div class="nav-label" style="margin-top:8px">Análise</div>
    <div class="nav-item" onclick="showView('relatorio')">
      <?= icon('bar-chart-2') ?> Relatório Anual
    </div>
    <div class="nav-label" style="margin-top:8px">Configurações</div>
    <div class="nav-item" onclick="showView('categorias')">
      <?= icon('tag') ?> Categorias
    </div>
  </nav>
  <div style="padding:16px;border-top:1px solid var(--border);font-size:.7rem;color:var(--text3);text-align:center;">
    <?= APP_NAME ?> v<?= APP_VERSION ?>
  </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- MAIN -->
<div class="main">
  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-left">
      <button class="btn btn-icon btn-secondary btn-menu" onclick="toggleSidebar()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <span class="page-title" id="pageTitle">Dashboard</span>
    </div>
    <div class="topbar-right">
      <div class="month-nav">
        <button onclick="prevMonth()" title="Mês anterior">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <span class="month-label" id="monthLabel">–</span>
        <button onclick="nextMonth()" title="Próximo mês">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </div>
      <button class="btn btn-primary" onclick="openModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nova
      </button>
    </div>
  </header>

  <!-- CONTENT -->
  <div class="content">

    <!-- ════════════════ DASHBOARD ════════════════ -->
    <div id="view-dashboard" class="view active">
      <div class="summary-grid" id="summaryCards">
        <div class="summary-card green"><div class="sc-label">Receitas</div><div class="sc-value green" id="totalReceitas">R$ 0,00</div><div class="sc-sub" id="subReceitas">mês atual</div></div>
        <div class="summary-card red"><div class="sc-label">Despesas</div><div class="sc-value red" id="totalDespesas">R$ 0,00</div><div class="sc-sub" id="subDespesas">mês atual</div></div>
        <div class="summary-card yellow"><div class="sc-label">Pendente</div><div class="sc-value yellow" id="totalPendente">R$ 0,00</div><div class="sc-sub">a pagar</div></div>
        <div class="summary-card blue"><div class="sc-label">Saldo</div><div class="sc-value blue" id="totalSaldo">R$ 0,00</div><div class="sc-sub">receitas – despesas</div></div>
      </div>

      <div class="grid-2">
        <div class="panel">
          <div class="panel-header">
            <span class="panel-title">Despesas por Categoria</span>
          </div>
          <div class="panel-body">
            <div class="chart-wrap"><canvas id="chartCategorias"></canvas></div>
            <div id="catLegend" style="margin-top:12px;display:flex;flex-direction:column;gap:6px;"></div>
          </div>
        </div>
        <div class="panel">
          <div class="panel-header">
            <span class="panel-title">⚠️ Vencendo em 7 dias</span>
          </div>
          <div class="panel-body" id="vencendoList" style="display:flex;flex-direction:column;gap:8px;"></div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-header">
          <span class="panel-title">⏳ Pendentes do Mês</span>
          <button class="btn btn-secondary btn-sm" onclick="showView('pendentes')">Ver todas</button>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr>
              <th>Descrição</th><th>Categoria</th><th>Vencimento</th><th>Tipo</th><th>Valor</th><th>Ação</th>
            </tr></thead>
            <tbody id="dashTransacoes"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════════════════ TRANSAÇÕES ════════════════ -->
    <div id="view-transacoes" class="view">
      <div class="filters">
        <div class="filter-group">
          <select id="filtroTipo" onchange="loadTransacoes()">
            <option value="">Todos os tipos</option>
            <option value="receita">Receitas</option>
            <option value="despesa">Despesas</option>
          </select>
          <select id="filtroStatus" onchange="loadTransacoes()">
            <option value="">Todos os status</option>
            <option value="pendente">Pendente</option>
            <option value="pago">Pago</option>
          </select>
          <select id="filtroCategoria" onchange="loadTransacoes()">
            <option value="">Todas as categorias</option>
          </select>
        </div>
        <input type="text" class="search-input" id="filtroBusca" placeholder="🔍 Buscar descrição…" oninput="debounceLoad()">
      </div>
      <div class="panel">
        <div class="panel-header">
          <span class="panel-title" id="transacoesTitle">Transações</span>
          <button class="btn btn-primary btn-sm" onclick="openModal()">+ Nova Transação</button>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr>
              <th>Descrição</th><th>Categoria</th><th>Vencimento</th><th>Tipo</th><th>Valor</th><th>Status</th><th>Pgto</th><th>Ações</th>
            </tr></thead>
            <tbody id="tabelaTransacoes"></tbody>
          </table>
        </div>
      </div>

      <!-- TOTAIS DOS FILTROS -->
      <div id="totaisTransacoes" style="display:none;margin-top:12px">
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:flex-end">
          <div style="font-size:.72rem;color:var(--text3);margin-right:auto" id="totaisQtd"></div>
          <div class="totais-pill totais-receita">
            <span class="totais-label">↑ Receitas</span>
            <span class="totais-valor" id="totaisReceita">R$ 0,00</span>
          </div>
          <div class="totais-pill totais-despesa">
            <span class="totais-label">↓ Despesas</span>
            <span class="totais-valor" id="totaisDespesa">R$ 0,00</span>
          </div>
          <div class="totais-pill totais-saldo" id="totaisSaldoPill">
            <span class="totais-label">⚖ Saldo</span>
            <span class="totais-valor" id="totaisSaldo">R$ 0,00</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ════════════════ PENDENTES ════════════════ -->
    <div id="view-pendentes" class="view">
      <div class="filters">
        <select id="filtroPendenteMes" onchange="loadPendentes()">
          <option value="">Todos os meses</option>
          <option value="1">Janeiro</option><option value="2">Fevereiro</option>
          <option value="3">Março</option><option value="4">Abril</option>
          <option value="5">Maio</option><option value="6">Junho</option>
          <option value="7">Julho</option><option value="8">Agosto</option>
          <option value="9">Setembro</option><option value="10">Outubro</option>
          <option value="11">Novembro</option><option value="12">Dezembro</option>
        </select>
      </div>
      <div class="panel">
        <div class="panel-header">
          <span class="panel-title">💳 Pagamentos Pendentes</span>
          <span class="badge badge-yellow" id="totalPendenteBadge">0 pendências</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr>
              <th>Descrição</th><th>Categoria</th><th>Vencimento</th><th>Situação</th><th>Valor</th><th>Ações</th>
            </tr></thead>
            <tbody id="tabelaPendentes"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════════════════ RELATÓRIO ════════════════ -->
    <div id="view-relatorio" class="view">
      <div class="filters">
        <select id="anoRelatorio" onchange="loadRelatorio()"></select>
      </div>
      <div class="panel" style="margin-bottom:16px">
        <div class="panel-header"><span class="panel-title">Fluxo Anual</span></div>
        <div class="panel-body"><div style="height:280px"><canvas id="chartAnual"></canvas></div></div>
      </div>
      <div class="panel">
        <div class="panel-header"><span class="panel-title">Resumo por Mês</span></div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Mês</th><th>Receitas</th><th>Despesas</th><th>Saldo</th></tr></thead>
            <tbody id="tabelaAnual"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════════════════ CATEGORIAS ════════════════ -->
    <div id="view-categorias" class="view">
      <div style="display:flex;justify-content:flex-end;margin-bottom:12px">
        <button class="btn btn-primary" onclick="openCatModal()">+ Nova Categoria</button>
      </div>
      <div class="panel">
        <div class="table-wrap">
          <table>
            <thead><tr><th>Cor</th><th>Nome</th><th>Tipo</th><th>Ações</th></tr></thead>
            <tbody id="tabelaCategorias"></tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /app-wrap -->

<!-- ══════════ MODAL TRANSAÇÃO ══════════ -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modalTitle">Nova Transação</span>
      <button class="close-btn" onclick="closeModal()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="editId">
      <div class="form-group">
        <label class="form-label">Tipo *</label>
        <div class="tipo-toggle">
          <button type="button" class="tipo-btn active-receita" id="btnReceita" onclick="setTipo('receita')">📈 Receita</button>
          <button type="button" class="tipo-btn" id="btnDespesa" onclick="setTipo('despesa')">📉 Despesa</button>
        </div>
        <input type="hidden" id="tipoInput" value="receita">
      </div>
      <div class="form-group">
        <label class="form-label">Descrição *</label>
        <input type="text" class="form-control" id="descricao" placeholder="Ex: Conta de luz, Salário…" maxlength="200">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Valor *</label>
          <input type="text" class="form-control" id="valor" placeholder="0,00" oninput="maskMoney(this)">
        </div>
        <div class="form-group">
          <label class="form-label">Data de Vencimento *</label>
          <input type="date" class="form-control" id="dataVenc">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Categoria</label>
          <select class="form-control" id="categoriaId"></select>
        </div>
        <div class="form-group" id="parcelasGroup">
          <label class="form-label">Parcelas</label>
          <input type="number" class="form-control" id="parcelas" value="1" min="1" max="120">
          <div class="form-hint">Parcelas mensais a partir da data de vencimento</div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Observação</label>
        <textarea class="form-control" id="observacao" rows="2" placeholder="Notas adicionais…" style="resize:vertical"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary" onclick="salvarTransacao()">💾 Salvar</button>
    </div>
  </div>
</div>

<!-- ══════════ MODAL PAGAR ══════════ -->
<div class="modal-overlay" id="modalPagar">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Confirmar Pagamento</span>
      <button class="close-btn" onclick="closeModal('modalPagar')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <p style="font-size:.875rem;color:var(--text2);margin-bottom:16px" id="pagarDesc">–</p>
      <div class="form-group">
        <label class="form-label">Data do Pagamento</label>
        <input type="date" class="form-control" id="dataPagamento">
      </div>
      <input type="hidden" id="pagarId">
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modalPagar')">Cancelar</button>
      <button class="btn btn-primary" onclick="confirmarPagamento()">✅ Confirmar Pagamento</button>
    </div>
  </div>
</div>

<!-- ══════════ MODAL CATEGORIA ══════════ -->
<div class="modal-overlay" id="modalCat">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="catModalTitle">Nova Categoria</span>
      <button class="close-btn" onclick="closeModal('modalCat')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="catId">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nome *</label>
          <input type="text" class="form-control" id="catNome" placeholder="Nome da categoria">
        </div>
        <div class="form-group">
          <label class="form-label">Tipo</label>
          <select class="form-control" id="catTipo">
            <option value="ambos">Receita e Despesa</option>
            <option value="receita">Apenas Receita</option>
            <option value="despesa">Apenas Despesa</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Cor</label>
        <input type="color" class="form-control" id="catCor" value="#6366f1" style="height:40px;cursor:pointer;">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modalCat')">Cancelar</button>
      <button class="btn btn-primary" onclick="salvarCategoria()">💾 Salvar</button>
    </div>
  </div>
</div>

<!-- TOASTS -->
<div class="toast-container" id="toastContainer"></div>

<script>
// ══════════════════════════════════════════════
// APP STATE
// ══════════════════════════════════════════════
const state = {
  mes: new Date().getMonth() + 1,
  ano: new Date().getFullYear(),
  view: 'dashboard',
  categorias: [],
  chartCat: null,
  chartAnual: null,
};

const MESES = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

// ── INICIALIZAÇÃO ──────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  updateMonthLabel();
  loadCategorias().then(() => {
    loadDashboard();
  });
  fillAnoSelect();
  document.getElementById('filtroPendenteMes').value = state.mes;
});

// ── NAVEGAÇÃO ──────────────────────────────────
const pageTitles = {
  dashboard:   'Dashboard',
  transacoes:  'Transações',
  pendentes:   'Pendências',
  relatorio:   'Relatório Anual',
  categorias:  'Categorias',
};

function showView(name) {
  state.view = name;
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  document.getElementById('view-' + name).classList.add('active');
  document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(i => {
    if (i.textContent.trim().toLowerCase().includes(name === 'dashboard' ? 'dashboard'
      : name === 'transacoes' ? 'transa' : name === 'pendentes' ? 'pend' : name === 'relatorio' ? 'relat' : 'categ')) {
      i.classList.add('active');
    }
  });
  document.getElementById('pageTitle').textContent = pageTitles[name] || '';
  closeSidebar();

  if (name === 'transacoes') loadTransacoes();
  else if (name === 'pendentes') loadPendentes();
  else if (name === 'relatorio') loadRelatorio();
  else if (name === 'categorias') loadCategoriasList();
  else if (name === 'dashboard') loadDashboard();
}

// ── MÊS ───────────────────────────────────────
function updateMonthLabel() {
  document.getElementById('monthLabel').textContent = MESES[state.mes - 1] + ' ' + state.ano;
}
function prevMonth() {
  if (--state.mes < 1) { state.mes = 12; state.ano--; }
  updateMonthLabel(); reloadView();
}
function nextMonth() {
  if (++state.mes > 12) { state.mes = 1; state.ano++; }
  updateMonthLabel(); reloadView();
}
function reloadView() {
  if (state.view === 'dashboard') loadDashboard();
  else if (state.view === 'transacoes') loadTransacoes();
}

// ── API ───────────────────────────────────────
async function api(params, method = 'GET', body = null) {
  const url = 'includes/api.php?' + new URLSearchParams(params);
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(url, opts);
  return res.json();
}
async function apiPost(action, formData) {
  const res = await fetch('includes/api.php?action=' + action, { method: 'POST', body: formData });
  return res.json();
}

// ── TOAST ─────────────────────────────────────
function toast(msg, type = 'success') {
  const c = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = 'toast ' + type;
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

// ── CATEGORIAS ────────────────────────────────
async function loadCategorias() {
  const data = await api({ action: 'categorias' });
  state.categorias = data.categorias || [];
  fillCatSelects();
}
function fillCatSelects() {
  const sel = document.getElementById('categoriaId');
  const filt = document.getElementById('filtroCategoria');
  sel.innerHTML = '<option value="">Sem categoria</option>';
  filt.innerHTML = '<option value="">Todas as categorias</option>';
  state.categorias.forEach(c => {
    sel.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
    filt.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
  });
}

// ── DASHBOARD ─────────────────────────────────
async function loadDashboard() {
  const data = await api({ action: 'dashboard', mes: state.mes, ano: state.ano });
  const t = data.totais || {};
  const receitas  = parseFloat(t.total_receitas || 0);
  const despesas  = parseFloat(t.total_despesas || 0);
  const pendente  = parseFloat(t.total_pendente || 0);
  const saldo     = receitas - despesas;

  document.getElementById('totalReceitas').textContent = fmtMoney(receitas);
  document.getElementById('totalDespesas').textContent = fmtMoney(despesas);
  document.getElementById('totalPendente').textContent = fmtMoney(pendente);
  document.getElementById('totalSaldo').textContent    = fmtMoney(saldo);
  document.getElementById('totalSaldo').className      = 'sc-value ' + (saldo >= 0 ? 'green' : 'red');

  // Chart categorias
  const cats = data.categorias || [];
  buildCatChart(cats);
  buildCatLegend(cats);

  // Vencendo
  buildVencendo(data.vencendo || []);

  // Tabela pendentes do mês
  buildDashTable(data.ultimas || []);
}

function buildCatChart(cats) {
  const ctx = document.getElementById('chartCategorias').getContext('2d');
  if (state.chartCat) state.chartCat.destroy();
  if (!cats.length) return;
  state.chartCat = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: cats.map(c => c.nome || 'Sem cat.'),
      datasets: [{ data: cats.map(c => parseFloat(c.total)), backgroundColor: cats.map(c => c.cor || '#64748b'), borderWidth: 0, hoverOffset: 6 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '65%',
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + fmtMoney(ctx.raw) } } }
    }
  });
}

function buildCatLegend(cats) {
  const el = document.getElementById('catLegend');
  const totalGeral = cats.reduce((sum, c) => sum + parseFloat(c.total), 0);
  el.innerHTML = cats.slice(0, 6).map(c => {
    const pct = totalGeral > 0 ? ((parseFloat(c.total) / totalGeral) * 100).toFixed(1) : '0.0';
    return `
    <div style="display:flex;flex-direction:column;gap:4px">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
        <div style="display:flex;align-items:center;gap:6px;min-width:0">
          <div class="dot" style="background:${c.cor||'#64748b'};flex-shrink:0"></div>
          <span style="font-size:.75rem;color:var(--text2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(c.nome||'Sem cat.')}</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
          <span style="font-size:.72rem;font-weight:700;color:${c.cor||'#64748b'};background:${c.cor||'#64748b'}22;padding:1px 6px;border-radius:99px">${pct}%</span>
          <span style="font-size:.75rem;font-family:'JetBrains Mono',monospace;color:var(--red)">${fmtMoney(c.total)}</span>
        </div>
      </div>
      <div class="progress">
        <div class="progress-bar" style="width:${pct}%;background:${c.cor||'#64748b'}"></div>
      </div>
    </div>`;
  }).join('');
}

function buildVencendo(list) {
  const el = document.getElementById('vencendoList');
  if (!list.length) { el.innerHTML = '<div class="empty"><p>Nenhum vencimento próximo 🎉</p></div>'; return; }
  el.innerHTML = list.map(t => {
    const dias = parseInt(t.dias_para_vencer ?? 0);
    const alerta = dias < 0 ? '🔴 Atrasado' : dias === 0 ? '🟡 Hoje' : `🔵 ${dias}d`;
    return `
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px;background:var(--card2);border-radius:8px;border:1px solid var(--border)">
        <div>
          <div style="font-size:.8rem;font-weight:600">${esc(t.descricao)}</div>
          <div style="font-size:.7rem;color:var(--text3)">${fmtDate(t.data_vencimento)}</div>
        </div>
        <div style="text-align:right">
          <div style="font-size:.8rem;font-family:'JetBrains Mono',monospace;color:var(--red)">${fmtMoney(t.valor)}</div>
          <div style="font-size:.7rem">${alerta}</div>
        </div>
      </div>`;
  }).join('');
}

function buildDashTable(rows) {
  const tb = document.getElementById('dashTransacoes');
  if (!rows.length) {
    tb.innerHTML = `<tr><td colspan="6"><div class="empty"><p>✅ Nenhuma pendência neste mês!</p></div></td></tr>`;
    return;
  }
  tb.innerHTML = rows.map(t => {
    const dias = parseInt(t.dias_para_vencer ?? 0);
    let situacaoBadge;
    if (dias < 0)       situacaoBadge = `<span class="badge badge-red">🔴 ${Math.abs(dias)}d atrasado</span>`;
    else if (dias === 0) situacaoBadge = `<span class="badge badge-yellow">🟡 Vence hoje</span>`;
    else if (dias <= 3)  situacaoBadge = `<span class="badge badge-blue">🔵 ${dias}d</span>`;
    else                 situacaoBadge = `<span class="badge badge-gray">${fmtDate(t.data_vencimento)}</span>`;

    const catDot = t.categoria_cor ? `<div class="dot" style="background:${t.categoria_cor}"></div>` : '';
    const cat = t.categoria_nome ? `<div class="cat-badge">${catDot}${esc(t.categoria_nome)}</div>` : '<span style="color:var(--text3)">–</span>';
    return `<tr>
      <td><span style="font-weight:500">${esc(t.descricao)}</span></td>
      <td>${cat}</td>
      <td>${situacaoBadge}</td>
      <td><span class="chip-despesa">${t.tipo === 'receita' ? '<span class="chip-receita">↑ Receita</span>' : '↓ Despesa'}</span></td>
      <td><span class="valor-mono chip-${t.tipo}">${fmtMoney(t.valor)}</span></td>
      <td><button class="btn btn-primary btn-sm" onclick="openPagar(${t.id},'${esc(t.descricao)}')">✅ Pagar</button></td>
    </tr>`;
  }).join('');
}

// ── TRANSAÇÕES ────────────────────────────────
let debounceTimer;
function debounceLoad() { clearTimeout(debounceTimer); debounceTimer = setTimeout(loadTransacoes, 350); }

async function loadTransacoes() {
  const params = {
    action: 'listar', mes: state.mes, ano: state.ano,
    tipo:       document.getElementById('filtroTipo').value,
    status:     document.getElementById('filtroStatus').value,
    categoria:  document.getElementById('filtroCategoria').value,
    busca:      document.getElementById('filtroBusca').value,
  };
  const data = await api(params);
  const rows = data.transacoes || [];

  const titulo = `${rows.length} transaç${rows.length !== 1 ? 'ões' : 'ão'} em ${MESES[state.mes-1]}/${state.ano}`;
  document.getElementById('transacoesTitle').textContent = titulo;

  const tb = document.getElementById('tabelaTransacoes');
  if (!rows.length) {
    tb.innerHTML = `<tr><td colspan="8"><div class="empty"><p>Nenhuma transação encontrada</p></div></td></tr>`;
    document.getElementById('totaisTransacoes').style.display = 'none';
    return;
  }
  tb.innerHTML = rows.map(t => rowHTML(t, true)).join('');

  // ── Calcular totais com base nos filtros aplicados ──
  let somaReceitas = 0, somaDespesas = 0;
  rows.forEach(t => {
    const v = parseFloat(t.valor) || 0;
    if (t.tipo === 'receita') somaReceitas += v;
    else somaDespesas += v;
  });
  const saldo = somaReceitas - somaDespesas;
  const filtroTipo = document.getElementById('filtroTipo').value;

  document.getElementById('totaisReceita').textContent = fmtMoney(somaReceitas);
  document.getElementById('totaisDespesa').textContent = fmtMoney(somaDespesas);
  document.getElementById('totaisSaldo').textContent   = fmtMoney(Math.abs(saldo));
  document.getElementById('totaisQtd').textContent     = `${rows.length} registro${rows.length !== 1 ? 's' : ''} encontrado${rows.length !== 1 ? 's' : ''}`;

  const saldoPill = document.getElementById('totaisSaldoPill');
  if (saldo < 0) {
    saldoPill.classList.add('negativo');
    document.getElementById('totaisSaldo').textContent = '−' + fmtMoney(Math.abs(saldo));
  } else {
    saldoPill.classList.remove('negativo');
    document.getElementById('totaisSaldo').textContent = fmtMoney(saldo);
  }

  // Ocultar pills irrelevantes quando filtro de tipo está ativo
  const wrapReceita = document.getElementById('totaisReceita').closest('.totais-pill');
  const wrapDespesa = document.getElementById('totaisDespesa').closest('.totais-pill');
  const wrapSaldo   = saldoPill;
  wrapReceita.style.display = filtroTipo === 'despesa'  ? 'none' : '';
  wrapDespesa.style.display = filtroTipo === 'receita'  ? 'none' : '';
  wrapSaldo.style.display   = filtroTipo !== ''         ? 'none' : '';

  document.getElementById('totaisTransacoes').style.display = 'block';
}

function rowHTML(t, showActions = false) {
  const statusBadge = t.status === 'pago'
    ? '<span class="badge badge-green">✓ Pago</span>'
    : '<span class="badge badge-yellow">⏳ Pendente</span>';
  const tipoBadge = t.tipo === 'receita'
    ? '<span class="chip-receita">↑ Receita</span>'
    : '<span class="chip-despesa">↓ Despesa</span>';
  const catDot = t.categoria_cor ? `<div class="dot" style="background:${t.categoria_cor}"></div>` : '';
  const cat = t.categoria_nome ? `<div class="cat-badge">${catDot}${esc(t.categoria_nome)}</div>` : '<span style="color:var(--text3)">–</span>';
  const pgto = t.data_pagamento ? `<span style="color:var(--text3);font-size:.75rem">${fmtDate(t.data_pagamento)}</span>` : '–';

  let actions = '';
  if (showActions) {
    const payBtn = t.status === 'pendente'
      ? `<button class="btn btn-icon btn-sm" title="Marcar como pago" onclick="openPagar(${t.id},'${esc(t.descricao)}')">✅</button>`
      : `<button class="btn btn-icon btn-sm btn-danger" title="Estornar pagamento" onclick="estornar(${t.id})">↩️</button>`;
    actions = `<div class="actions">
      ${payBtn}
      <button class="btn btn-icon btn-sm btn-secondary" title="Editar" onclick="editarTransacao(${t.id})">✏️</button>
      <button class="btn btn-icon btn-sm btn-danger" title="Excluir" onclick="excluirTransacao(${t.id},'${t.grupo_parcelamento||''}')">🗑️</button>
    </div>`;
  }

  return `<tr>
    <td><span style="font-weight:500">${esc(t.descricao)}</span></td>
    <td>${cat}</td>
    <td><span class="valor-mono" style="color:var(--text2)">${fmtDate(t.data_vencimento)}</span></td>
    <td>${tipoBadge}</td>
    <td><span class="valor-mono ${t.tipo === 'receita' ? 'chip-receita' : 'chip-despesa'}">${fmtMoney(t.valor)}</span></td>
    <td>${statusBadge}</td>
    ${showActions ? `<td>${pgto}</td><td>${actions}</td>` : ''}
  </tr>`;
}

// ── PENDENTES ─────────────────────────────────
async function loadPendentes() {
  const mes = document.getElementById('filtroPendenteMes').value;
  const data = await api({ action: 'pendentes', mes, ano: state.ano });
  const rows = data.transacoes || [];
  document.getElementById('totalPendenteBadge').textContent = rows.length + ' pendência' + (rows.length !== 1 ? 's' : '');

  const tb = document.getElementById('tabelaPendentes');
  if (!rows.length) { tb.innerHTML = `<tr><td colspan="6"><div class="empty"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 15 9"/></svg><p>Tudo em dia! Nenhuma pendência 🎉</p></div></td></tr>`; return; }

  tb.innerHTML = rows.map(t => {
    const dias = parseInt(t.dias_para_vencer ?? 0);
    let situacao, vcClass;
    if (dias < 0)       { situacao = `🔴 ${Math.abs(dias)}d atrasado`; vcClass = 'venc-atrasado'; }
    else if (dias === 0){ situacao = '🟡 Vence hoje'; vcClass = 'venc-hoje'; }
    else if (dias <= 3) { situacao = `🔵 ${dias}d restantes`; vcClass = 'venc-proximo'; }
    else                { situacao = `${dias}d restantes`; vcClass = ''; }

    const catDot = t.categoria_cor ? `<div class="dot" style="background:${t.categoria_cor}"></div>` : '';
    const cat = t.categoria_nome ? `<div class="cat-badge">${catDot}${esc(t.categoria_nome)}</div>` : '–';

    return `<tr>
      <td><span style="font-weight:500">${esc(t.descricao)}</span></td>
      <td>${cat}</td>
      <td><span class="${vcClass}">${fmtDate(t.data_vencimento)}</span></td>
      <td><span class="${vcClass}" style="font-size:.8rem">${situacao}</span></td>
      <td><span class="valor-mono chip-despesa">${fmtMoney(t.valor)}</span></td>
      <td><div class="actions">
        <button class="btn btn-primary btn-sm" onclick="openPagar(${t.id},'${esc(t.descricao)}')">✅ Pagar</button>
        <button class="btn btn-secondary btn-sm" onclick="editarTransacao(${t.id})">✏️</button>
      </div></td>
    </tr>`;
  }).join('');
}

// ── RELATÓRIO ANUAL ───────────────────────────
function fillAnoSelect() {
  const sel = document.getElementById('anoRelatorio');
  for (let y = state.ano + 1; y >= state.ano - 3; y--) {
    sel.innerHTML += `<option value="${y}" ${y===state.ano?'selected':''}>${y}</option>`;
  }
}

async function loadRelatorio() {
  const ano = document.getElementById('anoRelatorio').value;
  const data = await api({ action: 'relatorio_anual', ano });
  const mesesData = data.meses || [];

  // Montar arrays completos (12 meses)
  const receitas = Array(12).fill(0);
  const despesas = Array(12).fill(0);
  mesesData.forEach(m => {
    const i = parseInt(m.mes) - 1;
    receitas[i] = parseFloat(m.receitas);
    despesas[i] = parseFloat(m.despesas);
  });

  // Chart
  buildChartAnual(receitas, despesas);

  // Tabela
  const tb = document.getElementById('tabelaAnual');
  tb.innerHTML = MESES.map((nome, i) => {
    const r = receitas[i], d = despesas[i], s = r - d;
    if (!r && !d) return '';
    return `<tr class="annual-row">
      <td>${nome}</td>
      <td class="annual-positive">${fmtMoney(r)}</td>
      <td class="annual-negative">${fmtMoney(d)}</td>
      <td class="${s>=0?'annual-positive':'annual-negative'}">${fmtMoney(s)}</td>
    </tr>`;
  }).join('') || `<tr><td colspan="4"><div class="empty"><p>Sem dados para ${ano}</p></div></td></tr>`;
}

function buildChartAnual(receitas, despesas) {
  const ctx = document.getElementById('chartAnual').getContext('2d');
  if (state.chartAnual) state.chartAnual.destroy();
  state.chartAnual = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: MESES.map(m => m.substring(0,3)),
      datasets: [
        { label: 'Receitas', data: receitas, backgroundColor: 'rgba(16,185,129,.7)', borderRadius: 6, borderSkipped: false },
        { label: 'Despesas', data: despesas, backgroundColor: 'rgba(239,68,68,.7)', borderRadius: 6, borderSkipped: false },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { color: '#94a3b8', font: { family: 'Sora' } } }, tooltip: { callbacks: { label: ctx => ' ' + fmtMoney(ctx.raw) } } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#64748b' } },
        y: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#64748b', callback: v => 'R$' + (v/1000).toFixed(0) + 'k' } }
      }
    }
  });
}

// ── CATEGORIAS LIST ───────────────────────────
async function loadCategoriasList() {
  const data = await api({ action: 'categorias' });
  const cats = data.categorias || [];
  const tipos = { receita: 'Receita', despesa: 'Despesa', ambos: 'Ambos' };
  const tb = document.getElementById('tabelaCategorias');
  tb.innerHTML = cats.map(c => `
    <tr>
      <td><div class="dot" style="background:${c.cor};width:16px;height:16px;border-radius:4px"></div></td>
      <td style="font-weight:500">${esc(c.nome)}</td>
      <td><span class="badge badge-gray">${tipos[c.tipo]||c.tipo}</span></td>
      <td><div class="actions">
        <button class="btn btn-secondary btn-sm btn-icon" onclick="editarCategoria(${c.id})">✏️</button>
        <button class="btn btn-danger btn-sm btn-icon" onclick="excluirCategoria(${c.id})">🗑️</button>
      </div></td>
    </tr>
  `).join('');
}

// ── MODAL TRANSAÇÃO ───────────────────────────
function openModal(tipo = 'despesa') {
  document.getElementById('editId').value = '';
  document.getElementById('modalTitle').textContent = 'Nova Transação';
  document.getElementById('descricao').value = '';
  document.getElementById('valor').value = '';
  document.getElementById('dataVenc').value = new Date().toISOString().split('T')[0];
  document.getElementById('observacao').value = '';
  document.getElementById('parcelas').value = 1;
  document.getElementById('categoriaId').value = '';
  setTipo(tipo);
  document.getElementById('modalOverlay').classList.add('open');
  document.getElementById('descricao').focus();
}

function closeModal(id = 'modalOverlay') {
  document.getElementById(id).classList.remove('open');
}

function setTipo(tipo) {
  document.getElementById('tipoInput').value = tipo;
  document.getElementById('btnReceita').className = 'tipo-btn ' + (tipo === 'receita' ? 'active-receita' : '');
  document.getElementById('btnDespesa').className = 'tipo-btn ' + (tipo === 'despesa' ? 'active-despesa' : '');
  document.getElementById('parcelasGroup').style.display = tipo === 'despesa' ? 'block' : 'none';
  loadCatsByTipo(tipo);
}

async function loadCatsByTipo(tipo) {
  const data = await api({ action: 'categorias', tipo });
  const sel = document.getElementById('categoriaId');
  const prev = sel.value;
  sel.innerHTML = '<option value="">Sem categoria</option>';
  (data.categorias || []).forEach(c => { sel.innerHTML += `<option value="${c.id}">${c.nome}</option>`; });
  if (prev) sel.value = prev;
}

async function editarTransacao(id) {
  const data = await api({ action: 'buscar', id });
  const t = data.transacao;
  if (!t) return;
  document.getElementById('editId').value = t.id;
  document.getElementById('modalTitle').textContent = 'Editar Transação';
  document.getElementById('descricao').value = t.descricao;
  document.getElementById('valor').value = t.valor.replace('.', ',');
  document.getElementById('dataVenc').value = t.data_vencimento;
  document.getElementById('observacao').value = t.observacao || '';
  document.getElementById('parcelas').value = 1;
  setTipo(t.tipo);
  setTimeout(() => { document.getElementById('categoriaId').value = t.categoria_id || ''; }, 100);
  document.getElementById('modalOverlay').classList.add('open');
}

async function salvarTransacao() {
  const body = {
    id:               document.getElementById('editId').value,
    descricao:        document.getElementById('descricao').value.trim(),
    valor:            document.getElementById('valor').value,
    tipo:             document.getElementById('tipoInput').value,
    categoria_id:     document.getElementById('categoriaId').value,
    data_vencimento:  document.getElementById('dataVenc').value,
    observacao:       document.getElementById('observacao').value,
    parcelas:         document.getElementById('parcelas').value,
  };

  if (!body.descricao || !body.valor || !body.data_vencimento) {
    toast('Preencha descrição, valor e data.', 'error'); return;
  }

  const data = await api({ action: 'salvar' }, 'POST', body);
  if (data.erro) { toast(data.erro, 'error'); return; }
  toast(data.mensagem || 'Salvo!');
  closeModal();
  await loadCategorias();
  reloadView();
}

// ── PAGAR ─────────────────────────────────────
function openPagar(id, desc) {
  document.getElementById('pagarId').value = id;
  document.getElementById('pagarDesc').textContent = desc;
  document.getElementById('dataPagamento').value = new Date().toISOString().split('T')[0];
  document.getElementById('modalPagar').classList.add('open');
}

async function confirmarPagamento() {
  const fd = new FormData();
  fd.append('id', document.getElementById('pagarId').value);
  fd.append('data_pagamento', document.getElementById('dataPagamento').value);
  const data = await apiPost('pagar', fd);
  if (data.erro) { toast(data.erro, 'error'); return; }
  toast(data.mensagem || 'Pago!');
  closeModal('modalPagar');
  if (state.view === 'pendentes') loadPendentes();
  else reloadView();
}

async function estornar(id) {
  if (!confirm('Estornar este pagamento?')) return;
  const fd = new FormData(); fd.append('id', id);
  const data = await apiPost('estornar', fd);
  if (data.erro) { toast(data.erro, 'error'); return; }
  toast(data.mensagem || 'Estornado!');
  reloadView();
}

// ── EXCLUIR ───────────────────────────────────
async function excluirTransacao(id, grupo) {
  let msg = 'Excluir esta transação?';
  if (grupo) msg = 'Excluir apenas esta parcela ou TODAS as parcelas?';

  if (grupo) {
    const choice = confirm(msg + '\n\nOK = Excluir TODAS as parcelas\nCancelar = Excluir apenas esta');
    const fd = new FormData();
    fd.append('id', id);
    if (choice) fd.append('grupo', grupo);
    const data = await apiPost('excluir', fd);
    if (data.erro) { toast(data.erro, 'error'); return; }
    toast(data.mensagem || 'Excluído!');
  } else {
    if (!confirm(msg)) return;
    const fd = new FormData(); fd.append('id', id);
    const data = await apiPost('excluir', fd);
    if (data.erro) { toast(data.erro, 'error'); return; }
    toast(data.mensagem || 'Excluído!');
  }
  reloadView();
}

// ── CATEGORIAS CRUD ───────────────────────────
function openCatModal() {
  document.getElementById('catId').value = '';
  document.getElementById('catModalTitle').textContent = 'Nova Categoria';
  document.getElementById('catNome').value = '';
  document.getElementById('catTipo').value = 'ambos';
  document.getElementById('catCor').value = '#6366f1';
  document.getElementById('modalCat').classList.add('open');
}

async function editarCategoria(id) {
  const cat = state.categorias.find(c => c.id == id);
  if (!cat) return;
  document.getElementById('catId').value = cat.id;
  document.getElementById('catModalTitle').textContent = 'Editar Categoria';
  document.getElementById('catNome').value = cat.nome;
  document.getElementById('catTipo').value = cat.tipo;
  document.getElementById('catCor').value = cat.cor;
  document.getElementById('modalCat').classList.add('open');
}

async function salvarCategoria() {
  const body = {
    id:    document.getElementById('catId').value,
    nome:  document.getElementById('catNome').value.trim(),
    tipo:  document.getElementById('catTipo').value,
    cor:   document.getElementById('catCor').value,
    icone: 'tag',
  };
  if (!body.nome) { toast('Nome obrigatório.', 'error'); return; }
  const data = await api({ action: 'salvar_categoria' }, 'POST', body);
  if (data.erro) { toast(data.erro, 'error'); return; }
  toast('Categoria salva!');
  closeModal('modalCat');
  await loadCategorias();
  loadCategoriasList();
}

async function excluirCategoria(id) {
  if (!confirm('Excluir esta categoria?')) return;
  const fd = new FormData(); fd.append('id', id);
  const data = await apiPost('excluir_categoria', fd);
  if (data.erro) { toast(data.erro, 'error'); return; }
  toast('Categoria excluída!');
  await loadCategorias();
  loadCategoriasList();
}

// ── UTILS ─────────────────────────────────────
function fmtMoney(v) {
  return 'R$ ' + parseFloat(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function fmtDate(d) {
  if (!d) return '–';
  const [y,m,dd] = d.split('-');
  return `${dd}/${m}/${y}`;
}
function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function maskMoney(el) {
  let v = el.value.replace(/\D/g,'');
  if (!v) { el.value = ''; return; }
  v = (parseInt(v) / 100).toFixed(2);
  el.value = v.replace('.', ',').replace(/(\d)(?=(\d{3})+,)/g, '$1.');
}

// ── SIDEBAR MOBILE ────────────────────────────
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
}

// Fechar modal ao clicar fora
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});
</script>
</body>
</html>

<?php
// Função auxiliar para ícones SVG inline
function icon($name, $size = 16) {
    $icons = [
        'layout-dashboard' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
        'list'             => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
        'clock'            => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        'bar-chart-2'      => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>',
        'tag'              => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
    ];
    return $icons[$name] ?? '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>';
}
?>
