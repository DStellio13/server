<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/core/config.php'; // db()
session_start();

// Simule une "connexion Steam" pour dev
if (isset($_GET['dev'])) {
  $_SESSION['user'] = ['id' => 42, 'name' => 'DevUser', 'avatar' => null];
  header('Location: index.php'); exit;
}
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: index.php'); exit;
}

$loggedIn = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>CS Inventory — Prototype UI (mock)</title>
<link rel="icon" href="../favicon.ico" />
<!-- Chart.js pour le graphique (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  :root{
    --bg:#0f1115; --panel:#151922; --muted:#8a90a2; --text:#e6e9f2;
    --accent:#4f8cff; --danger:#ff5a5a; --success:#10c46a; --card:#1a1f2b;
    --border:#222835;
  }
  *{box-sizing:border-box}
  html,body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,system-ui,Arial,sans-serif}
  a{color:var(--accent);text-decoration:none}
  a:hover{text-decoration:underline}
  .container{max-width:1200px;margin:auto;padding:16px}
  header{display:flex;align-items:center;justify-content:space-between;margin:6px 0 16px}
  header .user{display:flex;gap:12px;align-items:center;color:var(--muted)}
  header .user .name{font-weight:600;color:var(--text)}
  header .actions{display:flex;gap:8px}
  .btn{
    background:var(--accent);border:none;color:white;font-weight:600;
    padding:10px 14px;border-radius:8px;cursor:pointer
  }
  .btn.secondary{background:#28324a}
  .btn.ghost{background:transparent;border:1px solid var(--border);color:var(--muted)}
  .layout{
    display:grid;grid-template-columns:1fr 320px;gap:16px;
    grid-template-rows:auto auto; /* barre outils + contenu */
  }
  .toolbar{
    grid-column:1/3;background:var(--panel);border:1px solid var(--border);
    padding:12px;border-radius:12px;display:flex;flex-wrap:wrap;gap:10px;align-items:center
  }
  .toolbar .field{display:flex;gap:8px;align-items:center}
  .toolbar input[type="search"], .toolbar select{
    background:#0f1320;border:1px solid var(--border);color:var(--text);
    padding:10px;border-radius:8px;min-width:200px
  }
  .panel{
    background:var(--panel);border:1px solid var(--border);border-radius:12px;padding:12px
  }
  .panel h2{font-size:1rem;margin:0 0 8px 0;color:#cbd3e6}
  .inventory{
    display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px
  }
  .card{
    background:var(--card);border:1px solid var(--border);border-radius:12px;padding:10px;
    display:flex;flex-direction:column;gap:8px
  }
  .card .thumb{
    height:110px;border-radius:10px;background:linear-gradient(135deg,#20283b,#151c2b);
    display:flex;align-items:center;justify-content:center;font-size:32px;opacity:.9
  }
  .card .title{font-weight:600}
  .card .meta{display:flex;justify-content:space-between;font-size:.95rem;color:var(--muted)}
  .badge{
    display:inline-flex;align-items:center;gap:6px;font-size:.85rem;font-weight:700;
    padding:4px 8px;border-radius:999px
  }
  .badge.up{background:rgba(16,196,106,.12);color:var(--success)}
  .badge.down{background:rgba(255,90,90,.12);color:var(--danger)}
  .sidebar .block{margin-bottom:12px}
  .list{
    display:grid;gap:8px;max-height:360px;overflow:auto;padding-right:6px
  }
  .row{
    display:flex;gap:8px;align-items:center;justify-content:space-between;
    background:#0f1320;border:1px solid var(--border);border-radius:10px;padding:8px
  }
  .row .n{color:#cbd3e6;font-weight:600}
  .row small{color:var(--muted)}
  .chart{
    margin-top:12px;background:var(--panel);border:1px solid var(--border);
    border-radius:12px;padding:12px
  }
  /* Login */
  .login-wrap{min-height:100dvh;display:grid;place-items:center;padding:24px}
  .login{
    width:min(420px,92vw);background:var(--panel);border:1px solid var(--border);
    border-radius:16px;padding:20px;display:grid;gap:14px;text-align:center
  }
  .login h1{margin:0 0 6px 0}
  .login p{color:var(--muted);margin:0 0 8px 0}
  .btn.steam{background:#1b2838}
  .hint{font-size:.9rem;color:var(--muted)}
  @media (max-width: 980px){
    .layout{grid-template-columns:1fr}
  }
</style>
</head>
<body>
<?php if(!$loggedIn): ?>
  <!-- ÉCRAN DE CONNEXION (MOCK) -->
  <div class="login-wrap">
    <div class="login" role="dialog" aria-labelledby="login-title" aria-describedby="login-desc">
      <h1 id="login-title">Connexion</h1>
      <p id="login-desc">Connecte-toi pour voir ton inventaire CS.</p>
      <button class="btn steam" onclick="location.href='?dev=1'">Se connecter avec Steam (mock)</button>
      <button class="btn secondary" onclick="location.href='?dev=1'">Connexion locale (dev)</button>
      <p class="hint">Cette page est un prototype : la connexion est simulée.</p>
    </div>
  </div>
<?php else: ?>
  <!-- APPLICATION -->
  <div class="container">
    <header>
      <div class="user">
        <img src="https://avatars.githubusercontent.com/u/9919?s=40" width="36" height="36" alt="" style="border-radius:50%;border:1px solid var(--border)" />
        <div>
          <div class="name"><?=htmlspecialchars($_SESSION['user']['name'] ?? 'Utilisateur')?></div>
          <div style="color:var(--muted);font-size:.9rem">Inventaire CS — Aperçu général</div>
        </div>
      </div>
      <div class="actions">
        <button id="btnRefresh" class="btn ghost" title="Régénérer des données mock">↻ Rafraîchir</button>
        <a class="btn secondary" href="?logout=1">Se déconnecter</a>
      </div>
    </header>

    <div class="layout">
      <!-- BARRE OUTILS -->
      <div class="toolbar" role="region" aria-label="Outils inventaire">
        <div class="field">
          <input id="search" type="search" placeholder="Rechercher un item..." />
        </div>
        <div class="field">
          <label style="color:var(--muted)">Tri</label>
          <select id="sort">
            <option value="name">Nom (A→Z)</option>
            <option value="price_desc">Prix (haut → bas)</option>
            <option value="price_asc">Prix (bas → haut)</option>
            <option value="delta_desc">Variation (haut → bas)</option>
            <option value="delta_asc">Variation (bas → haut)</option>
          </select>
        </div>
        <div class="field">
          <button id="toggleView" class="btn ghost" aria-pressed="false">Vue : Grille</button>
        </div>
      </div>

      <!-- INVENTAIRE (CENTRE) -->
      <section class="panel" aria-label="Inventaire">
        <h2>Inventaire</h2>
        <div id="inventory" class="inventory" aria-live="polite"></div>

        <div class="chart" aria-label="Tendance globale">
          <h2 style="margin-bottom:8px;">Tendance globale (valeur totale)</h2>
          <canvas id="trend" height="110"></canvas>
        </div>
      </section>

      <!-- PANNEAU DROIT -->
      <aside class="panel sidebar" aria-label="Variations">
        <div class="block">
          <h2>📉 En baisse</h2>
          <div id="losers" class="list"></div>
        </div>
        <div class="block">
          <h2>📈 En hausse</h2>
          <div id="gainers" class="list"></div>
        </div>
      </aside>
    </div>
  </div>
<?php endif; ?>

<?php if($loggedIn): ?>
<script>
/* ===========================
   MOCK DATA & HELPERS
   =========================== */
const NAMES = [
  "AK-47 | Redline","AK-47 | Vulcan","M4A4 | Desolate Space","M4A1-S | Hyper Beast",
  "AWP | Asiimov","AWP | Neo-Noir","Desert Eagle | Blaze","Glock-18 | Fade",
  "USP-S | Kill Confirmed","P90 | Emerald Dragon","P250 | See Ya Later","FAMAS | Roll Cage",
  "MP9 | Rose Iron","MAC-10 | Neon Rider","Five-SeveN | Monkey Business","CZ75 | Victoria",
  "XM1014 | Tranquility","Nova | Bloomstick","P2000 | Fire Elemental","Galil AR | Chatterbox",
  "SG 553 | Cyrex","Tec-9 | Avalanche","SSG 08 | Dragonfire","MAG-7 | Bulldog",
  "Dual Berettas | Cobra Strike","CZ75 | Pole Position","AUG | Chameleon","Negev | Power Loader",
  "UMP-45 | Primal Saber","R8 | Crimson Web","SCAR-20 | Cardiac","MP7 | Nemesis",
  "Sawed-Off | Wasteland Princess","AK-47 | Frontside Misty","M4A4 | Howl (mock)","AWP | Medusa (mock)"
];
const rand = (a,b)=>Math.random()*(b-a)+a;
const pick = arr => arr[Math.floor(Math.random()*arr.length)];
function makeMockItems(count=36){
  const used = new Set();
  const items = [];
  while(items.length<count){
    const n = pick(NAMES);
    if(used.has(n)) continue;
    used.add(n);
    const price = +(rand(0.5, 650).toFixed(2));
    const delta = +(rand(-15, 15).toFixed(2)); // variation %
    items.push({
      id: crypto.randomUUID(),
      name: n,
      price,
      delta, // %
      qty: Math.random()<0.85 ? 1 : 2
    });
  }
  return items;
}

/* ===========================
   STATE
   =========================== */
let STATE = {
  items: makeMockItems(),
  viewGrid: true
};

/* ===========================
   RENDER INVENTORY
   =========================== */
const $inv = document.getElementById('inventory');
const $losers = document.getElementById('losers');
const $gainers = document.getElementById('gainers');
const $search = document.getElementById('search');
const $sort = document.getElementById('sort');
const $toggle = document.getElementById('toggleView');
const $refresh = document.getElementById('btnRefresh');

function formatPrice(v){ return v.toLocaleString('fr-FR',{style:'currency',currency:'EUR'}); }

function renderInventory(){
  const q = ($search.value||'').trim().toLowerCase();
  let data = STATE.items.filter(it => it.name.toLowerCase().includes(q));

  switch($sort.value){
    case 'price_desc': data.sort((a,b)=>b.price-a.price); break;
    case 'price_asc': data.sort((a,b)=>a.price-b.price); break;
    case 'delta_desc': data.sort((a,b)=>b.delta-a.delta); break;
    case 'delta_asc': data.sort((a,b)=>a.delta-b.delta); break;
    default: data.sort((a,b)=>a.name.localeCompare(b.name)); break;
  }

  $inv.style.gridTemplateColumns = STATE.viewGrid ? 'repeat(auto-fill,minmax(180px,1fr))' : '1fr';
  $toggle.textContent = 'Vue : ' + (STATE.viewGrid ? 'Grille' : 'Liste');

  $inv.innerHTML = data.map(it => {
    const badge = `<span class="badge ${it.delta>=0?'up':'down'}">${it.delta>=0?'▲':'▼'} ${it.delta}%</span>`;
    return STATE.viewGrid ? `
      <article class="card" aria-label="${it.name}">
        <div class="thumb">🧩</div>
        <div class="title">${it.name}</div>
        <div class="meta">
          <div>${formatPrice(it.price)}</div>
          <div>x${it.qty}</div>
        </div>
        ${badge}
      </article>
    ` : `
      <article class="row" aria-label="${it.name}">
        <div class="n">${it.name}</div>
        <small>x${it.qty}</small>
        <div>${formatPrice(it.price)}</div>
        <div class="badge ${it.delta>=0?'up':'down'}">${it.delta>=0?'▲':'▼'} ${it.delta}%</div>
      </article>
    `;
  }).join('');
}

function renderSide(){
  const sortedUp = [...STATE.items].sort((a,b)=>b.delta-a.delta).slice(0,10);
  const sortedDown = [...STATE.items].sort((a,b)=>a.delta-b.delta).slice(0,10);

  $gainers.innerHTML = sortedUp.map(it=>`
    <div class="row">
      <div class="n">${it.name}</div>
      <div class="badge up">▲ ${it.delta}%</div>
    </div>
  `).join('');
  $losers.innerHTML = sortedDown.map(it=>`
    <div class="row">
      <div class="n">${it.name}</div>
      <div class="badge down">▼ ${it.delta}%</div>
    </div>
  `).join('');
}

/* ===========================
   TREND CHART (VALEUR TOTALE)
   =========================== */
const ctx = document.getElementById('trend').getContext('2d');
let trendData = [];
function portfolioValue(items){
  // valeur totale simulée = somme (prix * qty)
  return items.reduce((s,it)=>s + it.price*it.qty, 0);
}
function seedTrend(){
  trendData = [];
  let val = portfolioValue(STATE.items);
  for(let i=29;i>=0;i--){
    // petite marche aléatoire
    val = Math.max(0, val * (1 + (Math.random()-0.5)*0.01));
    trendData.push({ t: i, v: +val.toFixed(2) });
  }
}
seedTrend();

const chart = new Chart(ctx,{
  type:'line',
  data:{
    labels: trendData.map(p=> ''),
    datasets:[{
      label:'Valeur totale (mock €)',
      data: trendData.map(p=>p.v),
      fill:false,
      borderWidth:2,
    }]
  },
  options:{
    animation:false,
    responsive:true,
    plugins:{ legend:{labels:{color:'#cbd3e6'}} },
    scales:{
      x:{ ticks:{display:false,color:'#8a90a2'}, grid:{display:false} },
      y:{ ticks:{color:'#8a90a2'}, grid:{color:'#1e2535'} }
    }
  }
});

function refreshAll(){
  renderInventory();
  renderSide();
  // met à jour le dernier point du graphe en fonction de la valeur actuelle
  const current = portfolioValue(STATE.items);
  const ds = chart.data.datasets[0].data;
  ds.shift(); ds.push(+current.toFixed(2));
  chart.update();
}

/* ===========================
   EVENTS
   =========================== */
$search.addEventListener('input', renderInventory);
$sort.addEventListener('change', renderInventory);
$toggle.addEventListener('click', ()=>{
  STATE.viewGrid = !STATE.viewGrid;
  $toggle.setAttribute('aria-pressed', String(!STATE.viewGrid));
  renderInventory();
});
$refresh.addEventListener('click', ()=>{
  STATE.items = makeMockItems();
  seedTrend();
  // remplit labels à 30 points
  chart.data.labels = new Array(30).fill('');
  chart.data.datasets[0].data = trendData.map(p=>p.v);
  chart.update();
  refreshAll();
});

// Premier rendu
renderInventory();
renderSide();
refreshAll();
</script>
<?php endif; ?>
</body>
</html>
