<?php
/**
 * Quickbar v3.1 — Scope correct par page
 * - 'full' (par défaut) : tout est accessible, AUCUN verrou projet en session
 * - 'tasks-only' : onglet Tâches uniquement, verrou projectSlug côté UI + API
 * - Pas de ressources externes (CSS/JS inline)
 */
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['qb_token'])) {
  $_SESSION['qb_token'] = bin2hex(random_bytes(16));
}

$__QB_SCOPE   = (isset($quickbar_scope) && in_array($quickbar_scope, ['full','tasks-only'], true)) ? $quickbar_scope : 'full';
$__QB_PROJECT = isset($projectSlug) ? (string)$projectSlug : '';

// Session pour l'API
$_SESSION['qb_scope'] = $__QB_SCOPE;
if ($__QB_SCOPE === 'tasks-only' && $__QB_PROJECT !== '') {
  $_SESSION['qb_project'] = $__QB_PROJECT;   // verrouille
} else {
  unset($_SESSION['qb_project']);            // IMPORTANT : ne pas contaminer d'autres pages
}
?>
<style>
  .qb-fab{position:fixed;right:16px;bottom:16px;z-index:9999;border:1px solid #e5e7eb;background:#fff;padding:.6rem .8rem;border-radius:999px;box-shadow:0 6px 18px rgba(0,0,0,.08);cursor:pointer}
  .qb-fab[aria-expanded="true"]{box-shadow:0 8px 24px rgba(0,0,0,.15)}
  .quickbar{position:fixed;right:16px;bottom:72px;width:360px;max-width:calc(100vw - 32px);max-height:75vh;overflow:auto;background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.12);padding:10px 10px 12px;z-index:9998}
  .quickbar[hidden]{display:none!important}
  .qb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
  .qb-title{display:flex;gap:.5rem;align-items:center}
  .qb-close{border:1px solid #e5e7eb;background:#fff;border-radius:10px;padding:.25rem .5rem;cursor:pointer}
  .qb-tabs{display:flex;gap:6px;margin:8px 0}
  .qb-tab{border:1px solid #e5e7eb;background:#fff;border-radius:10px;padding:.35rem .6rem;cursor:pointer}
  .qb-tab[aria-selected="true"]{border-color:#cbd5e1;box-shadow:inset 0 0 0 1px #cbd5e1}
  .qb-section{margin-top:6px}
  .qb-row{display:flex;gap:6px}
  .qb-field{display:flex;flex-direction:column;gap:4px;margin-bottom:6px}
  .qb-field input,.qb-field select,.qb-field textarea{border:1px solid #e5e7eb;border-radius:8px;padding:.4rem .5rem}
  .qb-actions{display:flex;gap:6px;flex-wrap:wrap;margin-top:6px}
  .qb-btn{border:1px solid #e5e7eb;background:#fff;border-radius:10px;padding:.4rem .6rem;cursor:pointer}
  .qb-list{display:flex;flex-direction:column;gap:4px;margin:6px 0}
  .qb-item{border:1px solid #eef2f7;border-radius:8px;padding:6px}
  .qb-small{font-size:.85rem;color:#555}
  .qb-danger{color:#b91c1c}
  .qb-success{color:#15803d}
  .qb-muted{color:#6b7280}
  .qb-inline{display:inline-flex;gap:6px;align-items:center}
  .qb-link{color:#111;text-decoration:none;border-bottom:1px dotted #cbd5e1}

  /* Scope "tasks-only" */
  .quickbar[data-scope="tasks-only"] .qb-tabs .qb-tab:not(#tab-tasks){ display:none !important; }
  .quickbar[data-scope="tasks-only"] #panel-projects,
  .quickbar[data-scope="tasks-only"] #panel-items{ display:none !important; }
  .quickbar[data-scope="tasks-only"] #qb-task-project,
  .quickbar[data-scope="tasks-only"] label[for="qb-task-project"]{ display:none !important; }
</style>

<button id="quickbar-toggle" type="button" class="qb-fab" aria-controls="quickbar" aria-expanded="false" title="Quickbar">Quickbar</button>

<aside id="quickbar" class="quickbar"
       hidden role="complementary" aria-label="Quick actions"
       data-scope="<?= htmlspecialchars($__QB_SCOPE) ?>"
       data-locked-project="<?= htmlspecialchars($__QB_PROJECT) ?>">
  <header class="qb-header">
    <div class="qb-title">
      <strong>Quickbar</strong>
      <span class="qb-small qb-muted">Projets · Tâches · Items</span>
    </div>
    <button type="button" class="qb-close" aria-label="Fermer">&times;</button>
  </header>

  <div class="qb-tabs" role="tablist" aria-label="Sections">
    <button class="qb-tab" role="tab" id="tab-projects"  aria-controls="panel-projects" aria-selected="true">Projets</button>
    <button class="qb-tab" role="tab" id="tab-tasks"     aria-controls="panel-tasks"    aria-selected="false">Tâches</button>
    <button class="qb-tab" role="tab" id="tab-items"     aria-controls="panel-items"    aria-selected="false">Items</button>
  </div>

  <!-- Projets -->
  <section id="panel-projects" role="tabpanel" aria-labelledby="tab-projects">
    <div class="qb-field">
      <label for="qb-project-select">Projet</label>
      <div class="qb-inline">
        <select id="qb-project-select"></select>
        <button class="qb-btn" id="qb-project-new" type="button">Nouveau</button>
      </div>
      <small class="qb-small qb-muted">Créer, modifier ou archiver un projet.</small>
    </div>

    <div class="qb-row">
      <div class="qb-field" style="flex:1"><label>Slug</label><input id="qb-proj-slug" placeholder="ex: monitor"></div>
      <div class="qb-field" style="flex:2"><label>Nom</label><input id="qb-proj-name" placeholder="Nom lisible"></div>
    </div>

    <div class="qb-row">
      <div class="qb-field" style="flex:.8"><label>Emoji</label><input id="qb-proj-emoji" placeholder="🔥"></div>
      <div class="qb-field" style="flex:1.2"><label>Ordre</label><input id="qb-proj-order" type="number" step="1" value="0"></div>
      <div class="qb-field" style="flex:1"><label>Actif</label><select id="qb-proj-active"><option value="1">Oui</option><option value="0">Non</option></select></div>
    </div>

    <div class="qb-row">
      <div class="qb-field" style="flex:1"><label>Icon (path)</label><input id="qb-proj-icon" placeholder="assets/icons/project-*.svg"></div>
    </div>

    <div class="qb-field"><label>Description</label><textarea id="qb-proj-desc" rows="2" placeholder="But du projet…"></textarea></div>

    <div class="qb-row">
      <div class="qb-field" style="flex:1"><label>Entrée (rel)</label><input id="qb-proj-entry" placeholder="monitor/v2/index.php"></div>
    </div>

    <div class="qb-actions">
      <button class="qb-btn" id="qb-proj-save" type="button">💾 Enregistrer</button>
      <button class="qb-btn qb-danger" id="qb-proj-archive" type="button">🗑️ Archiver</button>
      <a class="qb-btn" href="/index.php?scan=1">↻ Scanner</a>
    </div>

    <div id="qb-proj-msg" class="qb-small qb-muted" style="margin-top:6px"></div>
  </section>

  <!-- Tâches -->
  <section id="panel-tasks" role="tabpanel" aria-labelledby="tab-tasks" hidden>
    <div class="qb-field">
      <label for="qb-task-project">Projet</label>
      <select id="qb-task-project"></select>
    </div>

    <div class="qb-field">
      <label>Nouvelle tâche</label>
      <div class="qb-row">
        <input id="qb-task-title" placeholder="Titre…" style="flex:2">
        <select id="qb-task-status" style="flex:1">
          <option value="todo">À faire</option>
          <option value="in-progress">En cours</option>
          <option value="blocked">Bloquée</option>
        </select>
      </div>
      <div class="qb-row">
        <input id="qb-task-priority" type="number" step="1" value="0" style="flex:1" placeholder="Priorité">
        <input id="qb-task-due" type="date" style="flex:1">
        <button class="qb-btn" id="qb-task-add" type="button" style="flex:.8">➕ Ajouter</button>
      </div>
    </div>

    <div class="qb-section">
      <strong>Liste</strong>
      <div id="qb-task-list" class="qb-list"></div>
      <div id="qb-task-msg" class="qb-small qb-muted"></div>
    </div>
  </section>

  <!-- Items -->
  <section id="panel-items" role="tabpanel" aria-labelledby="tab-items" hidden>
    <div class="qb-field">
      <label for="qb-item-project">Projet</label>
      <select id="qb-item-project"></select>
      <small class="qb-small qb-muted">Si la table <code>items</code> n’existe pas, je te le dirai.</small>
    </div>

    <div class="qb-field">
      <label>Nouvel item</label>
      <div class="qb-row">
        <input id="qb-item-name" placeholder="Nom" style="flex:2">
        <input id="qb-item-qty" type="number" step="1" value="1" style="flex:1" placeholder="Qté">
      </div>
      <div class="qb-row">
        <input id="qb-item-price" type="number" step="0.01" value="0" style="flex:1" placeholder="Prix">
        <input id="qb-item-notes" placeholder="Notes" style="flex:2">
        <button class="qb-btn" id="qb-item-add" type="button" style="flex:.8">➕ Ajouter</button>
      </div>
    </div>

    <div class="qb-section">
      <strong>Liste</strong>
      <div id="qb-item-list" class="qb-list"></div>
      <div id="qb-item-msg" class="qb-small qb-muted"></div>
    </div>
  </section>
</aside>

<script>
(function(){
  if (window.__QB_V31__) return; window.__QB_V31__ = 1;

  const API = '/includes/ui/quickbar/actions.php';
  const SCOPE = <?= json_encode($__QB_SCOPE) ?>;
  const LOCKED_PROJECT = <?= json_encode($__QB_PROJECT) ?>;

  const toggle = document.getElementById('quickbar-toggle');
  const panel  = document.getElementById('quickbar');
  const closeB = panel.querySelector('.qb-close');

  function openQB(){ panel.removeAttribute('hidden'); toggle.setAttribute('aria-expanded','true'); }
  function closeQB(){ panel.setAttribute('hidden',''); toggle.setAttribute('aria-expanded','false'); }
  function isOpen(){ return !panel.hasAttribute('hidden'); }

  toggle.addEventListener('click', ()=> isOpen()?closeQB():openQB());
  closeB.addEventListener('click', closeQB);
  document.addEventListener('keydown', e=>{ if(e.key==='Escape' && isOpen()) closeQB(); });
  document.addEventListener('click', e=>{
    if (!isOpen()) return;
    if (panel.contains(e.target) || toggle.contains(e.target)) return;
    closeQB();
  });

  const tabs = {
    projects: { tab: document.getElementById('tab-projects'),  p: document.getElementById('panel-projects') },
    tasks:    { tab: document.getElementById('tab-tasks'),     p: document.getElementById('panel-tasks') },
    items:    { tab: document.getElementById('tab-items'),     p: document.getElementById('panel-items') },
  };
  Object.values(tabs).forEach(t=>{
    t.tab?.addEventListener('click', ()=>{
      Object.values(tabs).forEach(x=>{ x.tab?.setAttribute('aria-selected','false'); x.p.hidden = true; });
      t.tab.setAttribute('aria-selected','true'); t.p.hidden = false;
    });
  });
  if (SCOPE === 'tasks-only') {
    tabs.tasks.tab.setAttribute('aria-selected','true');
    tabs.tasks.p.hidden = false;
    tabs.projects.p.hidden = true;
    tabs.items.p.hidden = true;
  }

  async function apiGet(params){
    const url = API + '?' + new URLSearchParams(params).toString();
    const r = await fetch(url, {credentials:'same-origin'});
    return r.json();
  }
  async function apiPost(action, payload){
    const r = await fetch(API + '?action=' + encodeURIComponent(action), {
      method:'POST',
      headers: {'Content-Type':'application/json','X-CSRF-Token': <?= json_encode($_SESSION['qb_token']) ?>},
      body: JSON.stringify(payload||{}),
      credentials:'same-origin'
    });
    return r.json();
  }
  function el(tag, cls){ const e = document.createElement(tag); if(cls) e.className = cls; return e; }

  // === Projets ===
  const selProj = document.getElementById('qb-project-select');
  const btnNew  = document.getElementById('qb-project-new');
  const btnSave = document.getElementById('qb-proj-save');
  const btnArch = document.getElementById('qb-proj-archive');
  const msgProj = document.getElementById('qb-proj-msg');

  const fSlug = document.getElementById('qb-proj-slug');
  const fName = document.getElementById('qb-proj-name');
  const fEmoji= document.getElementById('qb-proj-emoji');
  const fOrder= document.getElementById('qb-proj-order');
  const fAct  = document.getElementById('qb-proj-active');
  const fIcon = document.getElementById('qb-proj-icon');
  const fDesc = document.getElementById('qb-proj-desc');
  const fEntry= document.getElementById('qb-proj-entry');

  async function loadProjects(selectSlug){
    if (SCOPE === 'tasks-only') {
      syncProjectSelects(LOCKED_PROJECT);
      return;
    }
    msgProj.textContent = 'Chargement…';
    const data = await apiGet({action:'list_projects'});
    selProj.innerHTML = '';
    (data.rows||[]).forEach(p=>{
      const o = el('option'); o.value = p.slug; o.textContent = p.name + ' ('+p.slug+')'; selProj.appendChild(o);
    });
    msgProj.textContent = '';
    if (selectSlug && [...selProj.options].some(o=>o.value===selectSlug)) selProj.value = selectSlug;
    else if (selProj.options.length) selProj.selectedIndex = 0;
    await loadProjectDetails(selProj.value);
    syncProjectSelects(selProj.value);
  }

  async function loadProjectDetails(slug){
    if (SCOPE === 'tasks-only') return;
    if (!slug) { clearProjectForm(); return; }
    const data = await apiGet({action:'get_project', slug});
    const p = data.project||{};
    fSlug.value = p.slug||'';
    fName.value = p.name||'';
    fEmoji.value = p.emoji||'';
    fOrder.value = p.order_index ?? 0;
    fAct.value = (p.is_active??1) ? '1':'0';
    fIcon.value = p.icon_path||'';
    fDesc.value = p.description||'';
    fEntry.value= p.entry_path||'';
  }
  function clearProjectForm(){
    [fSlug,fName,fEmoji,fIcon,fDesc,fEntry].forEach(i=>{ if(i) i.value=''; });
    if (fOrder) fOrder.value=0; if (fAct) fAct.value='1';
  }
  selProj?.addEventListener('change', ()=> loadProjectDetails(selProj.value));
  btnNew?.addEventListener('click', ()=>{ clearProjectForm(); fSlug?.focus(); });

  btnSave?.addEventListener('click', async ()=>{
    if (SCOPE === 'tasks-only') return;
    const payload = {
      slug: fSlug.value.trim(),
      name: fName.value.trim(),
      emoji: fEmoji.value.trim(),
      order_index: parseInt(fOrder.value||'0',10),
      is_active: fAct.value==='1'?1:0,
      icon_path: fIcon.value.trim(),
      description: fDesc.value.trim(),
      entry_path: fEntry.value.trim(),
    };
    if (!payload.slug || !payload.name) {
      msgProj.textContent = 'Slug et Nom sont requis.'; msgProj.className='qb-small qb-danger'; return;
    }
    const res = await apiPost('save_project', payload);
    msgProj.textContent = res.message || (res.ok?'Enregistré.':'Erreur.');
    msgProj.className = 'qb-small ' + (res.ok?'qb-success':'qb-danger');
    await loadProjects(payload.slug);
  });

  btnArch?.addEventListener('click', async ()=>{
    if (SCOPE === 'tasks-only') return;
    const slug = fSlug.value.trim();
    if (!slug) return;
    const res = await apiPost('archive_project', {slug});
    msgProj.textContent = res.message || (res.ok?'Archivé.':'Erreur.');
    msgProj.className = 'qb-small ' + (res.ok?'qb-success':'qb-danger');
    await loadProjects();
  });

  // === Tâches ===
  const selTaskProj = document.getElementById('qb-task-project');
  const tTitle = document.getElementById('qb-task-title');
  const tStatus= document.getElementById('qb-task-status');
  const tPrio  = document.getElementById('qb-task-priority');
  const tDue   = document.getElementById('qb-task-due');
  const btnAddTask = document.getElementById('qb-task-add');
  const taskList = document.getElementById('qb-task-list');
  const taskMsg  = document.getElementById('qb-task-msg');

  function syncProjectSelects(currentSlug){
    const slug = (SCOPE === 'tasks-only') ? LOCKED_PROJECT : currentSlug;
    if (selTaskProj) {
      if (slug) selTaskProj.value = slug;
      loadTasks(slug);
    }
    if (SCOPE !== 'tasks-only' && typeof selItemProj !== 'undefined' && selItemProj) {
      if (slug) selItemProj.value = slug;
      loadItems(slug);
    }
  }

  async function loadTasks(slug){
    taskMsg.textContent = 'Chargement…';
    taskList.innerHTML = '';
    if (!slug){ taskMsg.textContent='Choisis un projet.'; return; }
    const data = await apiGet({action:'list_tasks', project: slug});
    (data.rows||[]).forEach(t=>{
      const row = el('div','qb-item');
      const inp = el('input'); inp.value = t.title; inp.style.width='100%';
      const inline = el('div','qb-inline');
      const sel = el('select');
      ['todo','in-progress','blocked','done'].forEach(s=>{
        const o = el('option'); o.value=s; o.textContent=s; if (t.status===s) o.selected=true; sel.appendChild(o);
      });
      const pr = el('input'); pr.type='number'; pr.step='1'; pr.value = t.priority ?? 0; pr.style.width='70px';
      const du = el('input'); du.type='date'; du.value = t.due_at || '';
      const save = el('button','qb-btn'); save.textContent='💾'; save.title='Enregistrer';
      const del  = el('button','qb-btn qb-danger'); del.textContent='🗑️'; del.title='Supprimer';
      inline.append('Statut:', sel, 'Priorité:', pr, 'Échéance:', du, save, del);
      row.appendChild(inp); row.appendChild(inline);
      save.addEventListener('click', async ()=>{
        const res = await apiPost('update_task', {id:t.id, title:inp.value, status:sel.value, priority:parseInt(pr.value||'0',10), due_at:du.value||null});
        taskMsg.textContent = res.message || (res.ok?'OK':'Erreur'); taskMsg.className = 'qb-small ' + (res.ok?'qb-success':'qb-danger');
      });
      del.addEventListener('click', async ()=>{
        const res = await apiPost('delete_task', {id:t.id});
        taskMsg.textContent = res.message || (res.ok?'Supprimée':'Erreur'); taskMsg.className = 'qb-small ' + (res.ok?'qb-success':'qb-danger');
        await loadTasks(slug);
      });
      taskList.appendChild(row);
    });
    taskMsg.textContent = (data.rows||[]).length ? '' : 'Aucune tâche.';
    taskMsg.className = 'qb-small qb-muted';
  }
  selTaskProj?.addEventListener('change', ()=>{
    const slug = (SCOPE === 'tasks-only') ? LOCKED_PROJECT : selTaskProj.value;
    loadTasks(slug);
  });
  btnAddTask?.addEventListener('click', async ()=>{
    const slug = (SCOPE === 'tasks-only') ? LOCKED_PROJECT : selTaskProj.value;
    if (!slug) return;
    const res = await apiPost('add_task', {
      project_slug: slug,
      title: tTitle.value.trim(),
      status: tStatus.value,
      priority: parseInt(tPrio.value||'0',10),
      due_at: tDue.value || null,
    });
    taskMsg.textContent = res.message || (res.ok?'Ajoutée':'Erreur'); taskMsg.className = 'qb-small ' + (res.ok?'qb-success':'qb-danger');
    tTitle.value=''; tPrio.value='0'; tDue.value='';
    await loadTasks(slug);
  });

  // === Items ===
  const selItemProj = document.getElementById('qb-item-project');
  const iName = document.getElementById('qb-item-name');
  const iQty  = document.getElementById('qb-item-qty');
  const iPrice= document.getElementById('qb-item-price');
  const iNotes= document.getElementById('qb-item-notes');
  const btnAddItem = document.getElementById('qb-item-add');
  const itemList = document.getElementById('qb-item-list');
  const itemMsg  = document.getElementById('qb-item-msg');

  async function loadItems(slug){
    if (SCOPE === 'tasks-only') return;
    itemMsg.textContent = 'Chargement…';
    itemList.innerHTML = '';
    if (!slug){ itemMsg.textContent='Choisis un projet.'; return; }
    const data = await apiGet({action:'list_items', project: slug});
    if (!data.ok && data.code==='NO_TABLE'){
      itemMsg.innerHTML = 'Table <code>items</code> introuvable. OK si le module n’est pas prêt.';
      itemMsg.className = 'qb-small qb-muted';
      return;
    }
    (data.rows||[]).forEach(it=>{
      const row = el('div','qb-item');
      const l1 = el('div','qb-inline');
      const nm = el('input'); nm.value = it.name||''; nm.style.flex='1';
      const qty = el('input'); qty.type='number'; qty.step='1'; qty.value = it.qty ?? 1; qty.style.width='70px';
      const pr  = el('input'); pr.type='number'; pr.step='0.01'; pr.value = it.price ?? 0; pr.style.width='90px';
      l1.append('Nom:', nm, 'Qté:', qty, 'Prix:', pr);
      const l2 = el('div','qb-inline'); l2.style.marginTop='4px';
      const nt = el('input'); nt.value = it.notes||''; nt.placeholder='Notes'; nt.style.flex='1';
      const save = el('button','qb-btn'); save.textContent='💾';
      const del  = el('button','qb-btn qb-danger'); del.textContent='🗑️';
      l2.append(nt, save, del);
      row.append(l1, l2);
      save.addEventListener('click', async ()=>{
        const res = await apiPost('update_item', {id:it.id, name:nm.value, qty:parseInt(qty.value||'0',10), price:parseFloat(pr.value||'0'), notes:nt.value});
        itemMsg.textContent = res.message || (res.ok?'OK':'Erreur'); itemMsg.className = 'qb-small ' + (res.ok?'qb-success':'qb-danger');
      });
      del.addEventListener('click', async ()=>{
        const res = await apiPost('delete_item', {id:it.id});
        itemMsg.textContent = res.message || (res.ok?'Supprimé':'Erreur'); itemMsg.className = 'qb-small ' + (res.ok?'qb-success':'qb-danger');
        await loadItems(slug);
      });
      itemList.appendChild(row);
    });
    itemMsg.textContent = (data.rows||[]).length ? '' : 'Aucun item.';
    itemMsg.className = 'qb-small qb-muted';
  }

  // Boot
  loadProjects(LOCKED_PROJECT || undefined);
})();
</script>
